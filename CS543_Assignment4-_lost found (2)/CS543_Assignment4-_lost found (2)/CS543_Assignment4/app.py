
import os
import time
import random
import requests

from flask import Flask, request, jsonify, url_for
from store import store
from errors import problem

app = Flask(__name__)

# Actual CampusFind Item Service runs on port 5002.
# Its individual item endpoint is /<id>.
ITEM_SERVICE_URL = os.getenv(
    "ITEM_SERVICE_URL",
    "http://localhost:5002"
)

# Store the original payload associated with each idempotency key.
idempotency_payloads = {}

VALID_STATUSES = {
    "pending",
    "approved",
    "rejected",
    "cancelled"
}


def validate(data):
    """Validate the complete request before accessing its fields."""
    if not isinstance(data, dict):
        return None, "Request body must be a JSON object"

    item_id = data.get("item_id")
    claimant_id = data.get("claimant_id")
    message = data.get("message")

    if (
        not isinstance(item_id, int)
        or isinstance(item_id, bool)
        or item_id <= 0
    ):
        return None, "item_id must be a positive integer"

    if (
        not isinstance(claimant_id, int)
        or isinstance(claimant_id, bool)
        or claimant_id <= 0
    ):
        return None, "claimant_id must be a positive integer"

    if not isinstance(message, str) or not message.strip():
        return None, "message must be a non-empty string"

    return {
        "item_id": item_id,
        "claimant_id": claimant_id,
        "message": message.strip()
    }, None


def call_item_service(item_id):
    """
    Verify an item through a safe GET request.
    Uses bounded exponential backoff with jitter.
    """
    url = f"{ITEM_SERVICE_URL.rstrip('/')}/{item_id}"

    for attempt in range(3):
        try:
            response = requests.get(url, timeout=2)

            # Item does not exist.
            if response.status_code == 404:
                return False

            # Do not retry client errors.
            if 400 <= response.status_code < 500:
                return False

            # Retry server errors.
            if response.status_code >= 500:
                if attempt < 2:
                    delay = (
                        0.2 * (2 ** attempt)
                        + random.uniform(0, 0.1)
                    )
                    time.sleep(delay)
                    continue

                raise requests.RequestException(
                    "Item Service server error"
                )

            response.raise_for_status()
            return True

        except requests.RequestException:
            if attempt == 2:
                raise

            delay = (
                0.2 * (2 ** attempt)
                + random.uniform(0, 0.1)
            )
            time.sleep(delay)

    raise requests.RequestException("Item Service unavailable")


@app.route("/claims", methods=["GET"])
def list_claims():
    status = request.args.get("status")

    if status and status not in VALID_STATUSES:
        return problem(
            400,
            "Bad Request",
            "Invalid claim status filter"
        )

    claims = store.list(status)

    return jsonify([
        claim.as_json() for claim in claims
    ]), 200


@app.route("/claims", methods=["POST"])
def create_claim():
    # Require an idempotency key.
    key = request.headers.get("Idempotency-Key")

    if not key or not key.strip():
        return problem(
            400,
            "Bad Request",
            "Idempotency-Key header is required"
        )

    key = key.strip()

    # Validate before accessing request fields.
    data, error = validate(request.get_json(silent=True))

    if error:
        return problem(400, "Bad Request", error)

    payload = (
        data["item_id"],
        data["claimant_id"],
        data["message"]
    )

    # Return the original result for duplicate requests.
    if key in store.idempotency_records:
        if idempotency_payloads.get(key) != payload:
            return problem(
                409,
                "Conflict",
                "Idempotency-Key was used with a different request"
            )

        claim = store.get(store.idempotency_records[key])

        response = jsonify(claim.as_json())
        response.status_code = 201
        response.headers["Location"] = url_for(
            "get_claim",
            claim_id=claim.id,
            _external=True
        )

        return response

    # Verify that the referenced item exists.
    try:
        item_exists = call_item_service(data["item_id"])

    except requests.RequestException:
        return problem(
            503,
            "Service Unavailable",
            "Item Service is currently unavailable; "
            "claim creation cannot be verified"
        )

    if not item_exists:
        return problem(
            422,
            "Unprocessable Entity",
            "The referenced item does not exist "
            "or was rejected by Item Service"
        )

    # Create the claim only after successful verification.
    claim, created = store.create(
        item_id=data["item_id"],
        claimant_id=data["claimant_id"],
        message=data["message"],
        idempotency_key=key
    )

    idempotency_payloads[key] = payload

    response = jsonify(claim.as_json())
    response.status_code = 201
    response.headers["Location"] = url_for(
        "get_claim",
        claim_id=claim.id,
        _external=True
    )

    return response


@app.route("/claims/<int:claim_id>", methods=["GET"])
def get_claim(claim_id):
    claim = store.get(claim_id)

    if claim is None:
        return problem(
            404,
            "Not Found",
            "Claim not found"
        )

    return jsonify(claim.as_json()), 200


@app.route("/claims/<int:claim_id>/cancellation", methods=["POST"])
def cancel_claim(claim_id):
    result = store.cancel(claim_id)

    if result is None:
        return problem(
            404,
            "Not Found",
            "Claim not found"
        )

    if result is False:
        return problem(
            409,
            "Conflict",
            "Only pending claims can be cancelled"
        )

    return jsonify(result.as_json()), 200


if __name__ == "__main__":
    app.run(
        host="0.0.0.0",
        port=5004,
        debug=True
    )
