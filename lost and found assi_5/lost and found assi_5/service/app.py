import functools
import gzip
import hashlib
import json
import time
import uuid
import xml.etree.ElementTree as ET

from flask import Flask, g, jsonify, request, Response

from .errors import APIError, problem_response
from .gateway import check_item
from .models import Order, OrderItem
from .store import get_all_orders, get_order, save_order

app = Flask(__name__)

idempotency_store = {}

RATE_LIMIT_MAX = 5
RATE_LIMIT_WINDOW_SECONDS = 60
_rate_limit_buckets = {}

ALLOWED_STATUSES = [
    "pending",
    "confirmed",
    "preparing",
    "ready",
    "delivered",
    "cancelled",
]

VALID_TRANSITIONS = {
    "pending": ["confirmed", "cancelled"],
    "confirmed": ["preparing", "cancelled"],
    "preparing": ["ready"],
    "ready": ["delivered"],
    "delivered": [],
    "cancelled": [],
}

CORS_ALLOWED_HEADERS = (
    "Content-Type, Authorization, Idempotency-Key, If-Match, "
    "If-None-Match, X-HTTP-Method-Override"
)

class MethodOverrideMiddleware:
    def __init__(self, wrapped_app):
        self.wrapped_app = wrapped_app

    def __call__(self, environ, start_response):
        override = environ.get("HTTP_X_HTTP_METHOD_OVERRIDE")
        if override and environ.get("REQUEST_METHOD", "").upper() == "POST":
            environ["REQUEST_METHOD"] = override.strip().upper()
        return self.wrapped_app(environ, start_response)

app.wsgi_app = MethodOverrideMiddleware(app.wsgi_app)

def order_to_xml(order):
    root = ET.Element("Order")
    ET.SubElement(root, "order_id").text = str(order.order_id)
    ET.SubElement(root, "student_id").text = str(order.student_id)
    ET.SubElement(root, "restaurant_id").text = str(order.restaurant_id)
    ET.SubElement(root, "status").text = str(order.status)
    ET.SubElement(root, "total").text = str(order.total)
    items_elem = ET.SubElement(root, "items")
    for item in order.items:
        i_elem = ET.SubElement(items_elem, "Item")
        ET.SubElement(i_elem, "item_id").text = str(item.item_id)
        ET.SubElement(i_elem, "quantity").text = str(item.quantity)
    return ET.tostring(root, encoding="utf-8", xml_declaration=True)

def negotiate_response(data, status_code=200, is_problem=False):
    accept = request.headers.get("Accept", "application/json")
    if "*/*" in accept or "application/json" in accept:
        mime = "application/problem+json" if is_problem else "application/json"
        body = json.dumps(data)
        resp = Response(body, status=status_code, content_type=f"{mime}; charset=utf-8")
        return resp
    elif "application/xml" in accept:
        if is_problem:
            root = ET.Element("Problem")
            for k, v in data.items():
                if k != "errors":
                    ET.SubElement(root, k).text = str(v)
            body = ET.tostring(root, encoding="utf-8", xml_declaration=True)
            return Response(body, status=status_code, content_type="application/xml; charset=utf-8")
        else:
            class DummyOrder:
                pass
            d = DummyOrder()
            d.order_id = data.get("order_id")
            d.student_id = data.get("student_id")
            d.restaurant_id = data.get("restaurant_id")
            d.status = data.get("status")
            d.total = data.get("total")
            d.items = [OrderItem(i["item_id"], i["quantity"]) for i in data.get("items", [])]
            body = order_to_xml(d)
            return Response(body, status=status_code, content_type="application/xml; charset=utf-8")
    else:
        problem = {
            "type": "https://campuseats.local/errors/not-acceptable",
            "title": "Not Acceptable",
            "status": 406,
            "detail": f"Requested Accept header '{accept}' is not supported."
        }
        return Response(json.dumps(problem), status=406, content_type="application/problem+json; charset=utf-8")

@app.errorhandler(APIError)
def handle_api_error(error):
    prob = problem_response(error)
    resp = negotiate_response(prob, status_code=error.status, is_problem=True)
    if error.status in (429, 503) and error.retry_after is not None:
        resp.headers["Retry-After"] = str(error.retry_after)
    return resp

@app.route("/orders", methods=["POST"])
def create_order():
    token = request.headers.get("Authorization", "").replace("Bearer ", "")
    if not token:
        raise APIError(401, "unauthorized", "Missing or invalid bearer token.", "Authentication required.")
    
    now = time.time()
    bucket = _rate_limit_buckets.setdefault(token, [])
    bucket[:] = [t for t in bucket if now - t < RATE_LIMIT_WINDOW_SECONDS]
    if len(bucket) >= RATE_LIMIT_MAX:
        raise APIError(429, "rate-limit-exceeded", "Too Many Requests", "Rate limit of 5 requests per 60 seconds exceeded.", retry_after=60)
    bucket.append(now)

    data = request.get_json(silent=True)
    if data is None or not isinstance(data, dict):
        raise APIError(400, "malformed-request", "Bad Request", "Request body must be valid JSON object.")

    errors = []
    student_id = data.get("student_id")
    restaurant_id = data.get("restaurant_id")
    address = data.get("address")
    items = data.get("items")

    if not student_id:
        errors.append({"field": "student_id", "reason": "Field is required."})
    if not restaurant_id:
        errors.append({"field": "restaurant_id", "reason": "Field is required."})
    if not address or not isinstance(address, str) or len(address.strip()) < 5:
        errors.append({"field": "address", "reason": "Address is required and must be at least 5 characters long."})

    if items is None or not isinstance(items, list) or len(items) == 0:
        errors.append({"field": "items", "reason": "Cart must be non-empty list of items."})
    else:
        for idx, item in enumerate(items):
            if not isinstance(item, dict):
                errors.append({"field": f"items[{idx}]", "reason": "Item must be an object."})
                continue
            iid = item.get("item_id")
            qty = item.get("quantity")
            if not iid:
                errors.append({"field": f"items[{idx}].item_id", "reason": "item_id is required."})
            if qty is None or not isinstance(qty, int) or isinstance(qty, bool) or qty < 1:
                errors.append({"field": f"items[{idx}].quantity", "reason": "quantity must be an integer >= 1."})
            else:
                try:
                    catalog_item = check_item(iid)
                    if catalog_item is None:
                        errors.append({"field": f"items[{idx}].item_id", "reason": f"Item {iid} is unavailable."})
                except Exception as e:
                    raise APIError(503, "service-unavailable", "Service Unavailable", "Upstream catalog service unavailable.", retry_after=10)

    if errors:
        raise APIError(422, "validation-error", "Unprocessable Entity", "Request validation failed.", errors=errors)

    order_id = str(uuid.uuid4())[:8]
    order_items = [OrderItem(i["item_id"], i["quantity"]) for i in items]
    total = sum(i.quantity * 10.0 for i in order_items)
    order = Order(order_id=order_id, student_id=student_id, restaurant_id=restaurant_id, items=order_items, status="pending", total=total)
    save_order(order)

    resp_data = {
        "order_id": order.order_id,
        "student_id": order.student_id,
        "restaurant_id": order.restaurant_id,
        "items": [{"item_id": i.item_id, "quantity": i.quantity} for i in order.items],
        "status": order.status,
        "total": order.total
    }
    resp = negotiate_response(resp_data, status_code=201)
    resp.headers["Location"] = f"/orders/{order_id}"
    return resp

@app.route("/orders/<order_id>", methods=["GET"])
def get_order_route(order_id):
    order = get_order(order_id)
    if not order:
        raise APIError(404, "order-not-found", "Not Found", f"Order {order_id} does not exist.")
    
    resp_data = {
        "order_id": order.order_id,
        "student_id": order.student_id,
        "restaurant_id": order.restaurant_id,
        "items": [{"item_id": i.item_id, "quantity": i.quantity} for i in order.items],
        "status": order.status,
        "total": order.total
    }
    return negotiate_response(resp_data, status_code=200)

@app.route("/orders/<order_id>", methods=["PATCH"])
def patch_order(order_id):
    order = get_order(order_id)
    if not order:
        raise APIError(404, "order-not-found", "Not Found", f"Order {order_id} does not exist.")
    
    data = request.get_json(silent=True) or {}
    new_status = data.get("status")
    if new_status and new_status not in VALID_TRANSITIONS.get(order.status, []):
        raise APIError(409, "illegal-transition", "Conflict", f"Illegal state transition from {order.status} to {new_status}.")
    
    if new_status:
        order.status = new_status
        save_order(order)

    resp_data = {
        "order_id": order.order_id,
        "student_id": order.student_id,
        "restaurant_id": order.restaurant_id,
        "items": [{"item_id": i.item_id, "quantity": i.quantity} for i in order.items],
        "status": order.status,
        "total": order.total
    }
    return negotiate_response(resp_data, status_code=200)

if __name__ == "__main__":
    app.run(debug=True, port=8000)