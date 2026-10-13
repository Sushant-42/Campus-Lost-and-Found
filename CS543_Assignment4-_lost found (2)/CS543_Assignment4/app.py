
import os
import time
import random
import hashlib
import requests

from flask import Flask, request, jsonify, url_for
from store import OrderStore
from errors import (
    APIError,
    NotFoundError,
    ValidationError,
    ConflictError,
    ServiceUnavailableError,
)

app = Flask(__name__)
store = OrderStore()
idempotency_records = {}

CATALOGUE_SERVICE_URL = os.getenv(
    "CATALOGUE_SERVICE_URL",
    "http://localhost:5001/items"
)


@app.errorhandler(APIError)
def handle_api_error(error):
    response = jsonify({
        "type": "about:blank",
        "title": error.message,
        "status": error.status_code,
        "detail": error.message,
    })
    response.status_code = error.status_code
    response.content_type = "application/problem+json"
    return response


def call_catalogue_service(item_id):
    """Look up an item with bounded retries for temporary failures."""
    url = f"{CATALOGUE_SERVICE_URL}/{item_id}"

    for attempt in range(3):
        try:
            response = requests.get(url, timeout=2)

            if response.status_code == 404:
                raise ValidationError("The requested item does not exist")

            if 400 <= response.status_code < 500:
                raise APIError("Catalogue service rejected the request")

            if response.status_code >= 500:
                if attempt < 2:
                    time.sleep(
                        0.2 * (2 ** attempt) + random.uniform(0, 0.1)
                    )
                    continue
                raise ServiceUnavailableError(
                    "Catalogue service is unavailable"
                )

            response.raise_for_status()
            return response.json()

        except (ValidationError, APIError):
            raise
        except requests.RequestException:
            if attempt == 2:
                raise ServiceUnavailableError(
                    "Catalogue service is unavailable"
                )
            time.sleep(
                0.2 * (2 ** attempt) + random.uniform(0, 0.1)
            )

    raise ServiceUnavailableError("Catalogue service is unavailable")


@app.route("/orders", methods=["GET"])
def list_orders():
    return jsonify([order.to_dict() for order in store.get_all()]), 200


@app.route("/orders", methods=["POST"])
def create_order():
    key = request.headers.get("Idempotency-Key")
    if not key or not key.strip():
        raise ValidationError("Idempotency-Key header is required")

    data = request.get_json(silent=True)
    if not isinstance(data, dict):
        raise ValidationError("Request body must be a JSON object")

    item_id = data.get("item_id")
    claimant_name = data.get("claimant_name")

    if (
        not isinstance(item_id, int)
        or isinstance(item_id, bool)
        or item_id <= 0
    ):
        raise ValidationError("item_id must be a positive integer")

    if not isinstance(claimant_name, str) or not claimant_name.strip():
        raise ValidationError("claimant_name must be a non-empty string")

    claimant_name = claimant_name.strip()
    payload_hash = hashlib.sha256(
        f"{item_id}:{claimant_name}".encode("utf-8")
    ).hexdigest()

    if key in idempotency_records:
        saved_hash, saved_result = idempotency_records[key]

        if saved_hash != payload_hash:
            raise ConflictError(
                "Idempotency-Key was already used with a different request"
            )

        return jsonify(saved_result), 200

    # Validate the item through the external Catalogue service.
    call_catalogue_service(item_id)

    order = store.create(
        item_id=item_id,
        claimant_name=claimant_name,
        status="pending",
    )

    result = order.to_dict()
    idempotency_records[key] = (payload_hash, result)

    response = jsonify(result)
    response.status_code = 201
    response.headers["Location"] = url_for(
        "get_order",
        order_id=order.id,
        _external=True,
    )
    return response


@app.route("/orders/<int:order_id>", methods=["GET"])
def get_order(order_id):
    order = store.get(order_id)

    if order is None:
        raise NotFoundError("Order not found")

    return jsonify(order.to_dict()), 200


@app.route("/orders/<int:order_id>", methods=["DELETE"])
def cancel_order(order_id):
    order = store.get(order_id)

    if order is None:
        raise NotFoundError("Order not found")

    order.status = "rejected"
    return jsonify(order.to_dict()), 200


if __name__ == "__main__":
    app.run(debug=True)
