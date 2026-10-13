import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parents[1]))

import pytest
from app import app, store


@pytest.fixture
def client():
    app.config["TESTING"] = True
    store.orders.clear()
    store.next_id = 1

    with app.test_client() as test_client:
        yield test_client


def test_get_orders_empty(client):
    response = client.get("/orders")
    assert response.status_code == 200
    assert response.get_json() == []


def test_create_order(client):
    data = {
        "item_id": 1,
        "claimant_name": "Rahul",
        "status": "pending"
    }

    response = client.post("/orders", json=data)

    assert response.status_code == 201
    result = response.get_json()
    assert result["item_id"] == 1
    assert result["claimant_name"] == "Rahul"
    assert result["status"] == "pending"


def test_get_order(client):
    created = client.post("/orders", json={
        "item_id": 1,
        "claimant_name": "Rahul",
        "status": "pending"
    })

    order_id = created.get_json()["id"]
    response = client.get(f"/orders/{order_id}")

    assert response.status_code == 200
    assert response.get_json()["id"] == order_id


def test_get_nonexistent_order(client):
    response = client.get("/orders/999")
    assert response.status_code == 404


def test_create_order_invalid_data(client):
    response = client.post("/orders", json={})
    assert response.status_code == 400


def test_delete_order(client):
    created = client.post("/orders", json={
        "item_id": 1,
        "claimant_name": "Rahul",
        "status": "pending"
    })

    order_id = created.get_json()["id"]
    response = client.delete(f"/orders/{order_id}")

    assert response.status_code == 200

    response = client.get(f"/orders/{order_id}")
    assert response.status_code == 404
