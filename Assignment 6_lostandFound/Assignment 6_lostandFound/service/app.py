import hashlib
import json
import time
import uuid
import xml.etree.ElementTree as ET

from flask import Flask, request, Response

from .errors import APIError, problem_response
from .models import Item
from .store import get_all_items, get_item, save_item

app = Flask(__name__)

idempotency_store = {}

RATE_LIMIT_MAX = 5
RATE_LIMIT_WINDOW_SECONDS = 60
_rate_limit_buckets = {}

VALID_TRANSITIONS = {
    "reported": ["claimed", "resolved", "closed"],
    "claimed": ["resolved", "closed"],
    "resolved": [],
    "closed": [],
}

def item_to_xml(item: Item):
    root = ET.Element("Item")
    ET.SubElement(root, "item_id").text = str(item.item_id)
    ET.SubElement(root, "title").text = str(item.title)
    ET.SubElement(root, "description").text = str(item.description)
    ET.SubElement(root, "category").text = str(item.category)
    ET.SubElement(root, "location").text = str(item.location)
    ET.SubElement(root, "item_type").text = str(item.item_type)
    ET.SubElement(root, "status").text = str(item.status)
    ET.SubElement(root, "student_id").text = str(item.student_id)
    return ET.tostring(root, encoding="utf-8", xml_declaration=True)

def generate_etag(data_dict):
    canonical = json.dumps(data_dict, sort_keys=True)
    return '"' + hashlib.md5(canonical.encode('utf-8')).hexdigest() + '"'

def negotiate_response(data, status_code=200, is_problem=False, etag=None):
    accept = request.headers.get("Accept", "application/json")
    if "*/*" in accept or "application/json" in accept:
        mime = "application/problem+json" if is_problem else "application/json"
        body = json.dumps(data)
        resp = Response(body, status=status_code, content_type=f"{mime}; charset=utf-8")
    elif "application/xml" in accept:
        if is_problem:
            root = ET.Element("Problem")
            for k, v in data.items():
                if k != "errors":
                    ET.SubElement(root, k).text = str(v)
            body = ET.tostring(root, encoding="utf-8", xml_declaration=True)
            resp = Response(body, status=status_code, content_type="application/xml; charset=utf-8")
        else:
            item_obj = Item(
                item_id=data.get("item_id"),
                title=data.get("title", ""),
                description=data.get("description", ""),
                category=data.get("category", ""),
                location=data.get("location", ""),
                item_type=data.get("item_type", "lost"),
                status=data.get("status", "reported"),
                student_id=data.get("student_id", "")
            )
            body = item_to_xml(item_obj)
            resp = Response(body, status=status_code, content_type="application/xml; charset=utf-8")
    else:
        problem = {
            "type": "https://campuslostandfound.local/errors/not-acceptable",
            "title": "Not Acceptable",
            "status": 406,
            "detail": f"Requested Accept header '{accept}' is not supported."
        }
        return Response(json.dumps(problem), status=406, content_type="application/problem+json; charset=utf-8")

    if etag and not is_problem:
        resp.headers["ETag"] = etag
    return resp

@app.errorhandler(APIError)
def handle_api_error(error):
    prob = problem_response(error)
    resp = negotiate_response(prob, status_code=error.status, is_problem=True)
    if error.status in (429, 503) and error.retry_after is not None:
        resp.headers["Retry-After"] = str(error.retry_after)
    return resp

@app.route("/items", methods=["POST"])
def create_item():
    token = request.headers.get("Authorization", "").replace("Bearer ", "")
    if not token:
        raise APIError(401, "unauthorized", "Unauthorized", "Missing or invalid bearer token.")

    idem_key = request.headers.get("Idempotency-Key")
    if idem_key and idem_key in idempotency_store:
        cached = idempotency_store[idem_key]
        return negotiate_response(cached["data"], status_code=cached["status"])

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
    title = data.get("title")
    description = data.get("description")
    category = data.get("category")
    location = data.get("location")
    item_type = data.get("item_type")
    student_id = data.get("student_id", "anonymous")

    if not title or not isinstance(title, str) or len(title.strip()) == 0:
        errors.append({"field": "title", "reason": "Title is required and must be non-empty."})
    if not description or not isinstance(description, str) or len(description.strip()) == 0:
        errors.append({"field": "description", "reason": "Description is required."})
    if not category or not isinstance(category, str):
        errors.append({"field": "category", "reason": "Category is required."})
    if not location or not isinstance(location, str):
        errors.append({"field": "location", "reason": "Location is required."})
    if item_type not in ["lost", "found"]:
        errors.append({"field": "item_type", "reason": "item_type must be either 'lost' or 'found'."})

    if errors:
        raise APIError(422, "validation-error", "Unprocessable Entity", "Request validation failed.", errors=errors)

    item_id = str(uuid.uuid4())[:8]
    item = Item(
        item_id=item_id,
        title=title,
        description=description,
        category=category,
        location=location,
        item_type=item_type,
        status="reported",
        student_id=student_id
    )
    save_item(item)

    resp_data = {
        "item_id": item.item_id,
        "title": item.title,
        "description": item.description,
        "category": item.category,
        "location": item.location,
        "item_type": item.item_type,
        "status": item.status,
        "student_id": item.student_id
    }
    etag = generate_etag(resp_data)

    if idem_key:
        idempotency_store[idem_key] = {"data": resp_data, "status": 201}

    resp = negotiate_response(resp_data, status_code=201, etag=etag)
    resp.headers["Location"] = f"/items/{item_id}"
    return resp

@app.route("/items", methods=["GET"])
def list_items():
    all_items = get_all_items()
    result = [{
        "item_id": i.item_id,
        "title": i.title,
        "category": i.category,
        "status": i.status
    } for i in all_items]
    return negotiate_response({"items": result}, status_code=200)

@app.route("/items/<item_id>", methods=["GET"])
def get_item_route(item_id):
    item = get_item(item_id)
    if not item:
        raise APIError(404, "item-not-found", "Not Found", f"Item {item_id} does not exist.")

    resp_data = {
        "item_id": item.item_id,
        "title": item.title,
        "description": item.description,
        "category": item.category,
        "location": item.location,
        "item_type": item.item_type,
        "status": item.status,
        "student_id": item.student_id
    }
    etag = generate_etag(resp_data)

    if_none_match = request.headers.get("If-None-Match")
    if if_none_match and if_none_match == etag:
        return Response(status=304)

    return negotiate_response(resp_data, status_code=200, etag=etag)

@app.route("/items/<item_id>", methods=["PATCH"])
def patch_item(item_id):
    item = get_item(item_id)
    if not item:
        raise APIError(404, "item-not-found", "Not Found", f"Item {item_id} does not exist.")

    current_resp_data = {
        "item_id": item.item_id,
        "title": item.title,
        "description": item.description,
        "category": item.category,
        "location": item.location,
        "item_type": item.item_type,
        "status": item.status,
        "student_id": item.student_id
    }
    current_etag = generate_etag(current_resp_data)

    if_match = request.headers.get("If-Match")
    if if_match and if_match != current_etag:
        raise APIError(412, "precondition-failed", "Precondition Failed", "ETag mismatch; resource has been modified.")

    data = request.get_json(silent=True) or {}
    new_status = data.get("status")
    if new_status and new_status not in VALID_TRANSITIONS.get(item.status, []):
        raise APIError(409, "illegal-transition", "Conflict", f"Illegal state transition from {item.status} to {new_status}.")

    if new_status:
        item.status = new_status
        save_item(item)

    updated_resp_data = {
        "item_id": item.item_id,
        "title": item.title,
        "description": item.description,
        "category": item.category,
        "location": item.location,
        "item_type": item.item_type,
        "status": item.status,
        "student_id": item.student_id
    }
    new_etag = generate_etag(updated_resp_data)
    return negotiate_response(updated_resp_data, status_code=200, etag=new_etag)

@app.route("/items/<item_id>/claim", methods=["POSTSubmission"]) # or POST
@app.route("/items/<item_id>/claim", methods=["POST"])
def claim_item(item_id):
    item = get_item(item_id)
    if not item:
        raise APIError(404, "item-not-found", "Not Found", f"Item {item_id} does not exist.")
    if item.status != "reported":
        raise APIError(409, "item-already-claimed", "Conflict", f"Item {item_id} is already {item.status}.")

    item.status = "claimed"
    save_item(item)

    resp_data = {
        "item_id": item.item_id,
        "status": item.status,
        "message": "Item successfully claimed."
    }
    return negotiate_response(resp_data, status_code=200)

if __name__ == "__main__":
    app.run(debug=True, port=8000)