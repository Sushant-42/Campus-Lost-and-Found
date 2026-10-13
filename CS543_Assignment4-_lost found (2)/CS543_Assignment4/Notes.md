# CS543 Web Services — Assignment 4
## Campus Lost & Found API

### 1. Project Overview
This project implements a REST API for managing campus lost-and-found orders/claims using Flask. It supports creating records, retrieving records, listing records, and changing a record's status.

### 2. API Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/orders` | Retrieve all records |
| POST | `/orders` | Create a record |
| GET | `/orders/{order_id}` | Retrieve a record by ID |
| DELETE | `/orders/{order_id}` | Mark a record as rejected/cancelled |

### 3. Request Validation
The API validates the request body before creating a record. The `item_id` must be a positive integer, and `claimant_name` must be a non-empty string. Invalid requests return an error response.

### 4. Error Handling
Errors follow a consistent JSON structure containing `type`, `title`, `status`, and `detail`.

- `400 Bad Request`: Invalid input or missing required header.
- `404 Not Found`: Record or item not found.
- `409 Conflict`: An idempotency key is reused with a different request.
- `503 Service Unavailable`: The external Catalogue service is unavailable.

### 5. Idempotency
Creating a record requires an `Idempotency-Key` header. Repeating the same request with the same key returns the previously created result instead of creating a duplicate. Reusing the key with a different request returns a conflict.

Idempotency records are stored in memory and are lost when the application restarts.

### 6. External Service Integration
Before creating a record, the API sends an HTTP request to the Catalogue service to check the requested item. The service URL can be configured using the `CATALOGUE_SERVICE_URL` environment variable.

### 7. Retry Strategy
The API uses request timeouts, bounded retries, exponential backoff, and random jitter for temporary failures. These measures reduce the impact of temporary network errors and service outages.

### 8. Limitations
- Data is stored in memory rather than a persistent database.
- Records and idempotency keys are lost on restart.
- The external Catalogue service must be running for record creation to succeed.
- The project is intended for educational demonstration.

### 9. Running the Application
Install dependencies:

```bash
python -m pip install flask requests
```

Run the API:

```bash
python app.py
```

The API is available at `http://localhost:5000` by default.