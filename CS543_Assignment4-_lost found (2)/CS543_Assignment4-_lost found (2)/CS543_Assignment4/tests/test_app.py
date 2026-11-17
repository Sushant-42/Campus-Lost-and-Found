
import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parents[1]))

import pytest
import app as application


@pytest.fixture
def client(monkeypatch):
    application.app.config["TESTING"] = True

    # Reset in-memory data before every test.
    application.store.claims.clear()
    application.store.idempotency_records.clear()
    application.store.next_id = 1
    application.idempotency_payloads.clear()

    # Avoid requiring a running Item Service during unit tests.
    monkeypatch.setattr(
        application,
        "call_item_service",
        lambda item_id: True
    )

    with application.app.test_client() as test_client:
        yield test_client


def valid_claim():
    return {
        "item_id": 1,
        "claimant_id": 10,
        "message": "I lost this item near the library."
    }


def test_create_claim_success_and_location(client):
    response = client.post(
        "/claims",
        json=valid_claim(),
        headers={"Idempotency-Key": "claim-key-1"}
    )

    assert response.status_code == 201
    assert response.headers.get("Location") is not None

    result = response.get_json()
    assert result["item_id"] == 1
    assert result["claimant_id"] == 10
    assert result["status"] == "pending"


def test_idempotent_repeat_returns_original_claim(client):
    headers = {"Idempotency-Key": "claim-key-2"}

    first = client.post(
        "/claims", json=valid_claim(), headers=headers
    )
    second = client.post(
        "/claims", json=valid_claim(), headers=headers
    )

    assert first.status_code == 201
    assert second.status_code == 201
    assert first.get_json() == second.get_json()
    assert len(application.store.claims) == 1


def test_malformed_request_returns_400(client):
    response = client.post(
        "/claims",
        json={},
        headers={"Idempotency-Key": "claim-key-3"}
    )

    assert response.status_code == 400
    assert response.get_json()["status"] == 400


def test_unknown_claim_returns_404(client):
    response = client.get("/claims/999")

    assert response.status_code == 404
    assert response.get_json()["status"] == 404


def test_reusing_key_with_different_data_returns_409(client):
    headers = {"Idempotency-Key": "claim-key-4"}

    client.post("/claims", json=valid_claim(), headers=headers)

    different_claim = valid_claim()
    different_claim["item_id"] = 2

    response = client.post(
        "/claims", json=different_claim, headers=headers
    )

    assert response.status_code == 409


def test_cancel_claim_and_reject_second_cancellation(client):
    created = client.post(
        "/claims",
        json=valid_claim(),
        headers={"Idempotency-Key": "claim-key-5"}
    )

    claim_id = created.get_json()["id"]

    cancelled = client.post(
        f"/claims/{claim_id}/cancellation"
    )

    assert cancelled.status_code == 200
    assert cancelled.get_json()["status"] == "cancelled"

    repeated = client.post(
        f"/claims/{claim_id}/cancellation"
    )

    assert repeated.status_code == 409
