# Campus Lost and Found API - Assignment 5 & 6 Notes

## Team Information
* **Team ID**: 5
* **Team Members**: 
  * [20251651093 & Sushant prashant Prabhvalkar]
  * [20251651042 & Harsh Yadav]

---

## Part A.1: Campus Lost and Found Method Map

| Action / Endpoint | HTTP Method | URL | Safe? | Idempotent? |
| :--- | :--- | :--- | :--- | :--- |
| Report Lost/Found Item | `POST` | `/items` | No | No |
| List / Filter Items | `GET` | `/items` | Yes | Yes |
| Get Item Details | `GET` | `/items/{item_id}` | Yes | Yes |
| Update Item Status | `PATCH` | `/items/{item_id}` | No | Yes (natural state transition) |
| Claim Item (Sub-resource) | `POST` | `/items/{item_id}/claim` | No | Yes |
| Options / Preflight | `OPTIONS` | `/items`, `/items/{item_id}` | Yes | Yes |

---

## Part D.2: Headers Table

| Endpoint | Request Headers Needed | Response Headers Set |
| :--- | :--- | :--- |
| `POST /items` | `Authorization`, `Idempotency-Key` (optional), `Content-Type`, `Accept` | `Content-Type`, `Location`, `Access-Control-Allow-Origin`, `X-Content-Type-Options`, `X-RateLimit-Limit`, `X-RateLimit-Remaining` |
| `GET /items` | `Accept` | `Content-Type`, `Cache-Control`, `Access-Control-Allow-Origin`, `X-Content-Type-Options` |
| `GET /items/{item_id}` | `Accept`, `If-None-Match` (optional) | `Content-Type`, `ETag`, `Cache-Control`, `Access-Control-Allow-Origin`, `X-Content-Type-Options` |
| `PATCH /items/{item_id}` | `Authorization`, `If-Match` (optional), `Content-Type`, `X-HTTP-Method-Override` (optional) | `Content-Type`, `Access-Control-Allow-Origin`, `X-Content-Type-Options` |
| `POST /items/{item_id}/claim` | `Authorization` | `Content-Type`, `Access-Control-Allow-Origin`, `X-Content-Type-Options` |
| `OPTIONS /items` | `Origin`, `Access-Control-Request-Method` | `Allow`, `Access-Control-Allow-Origin`, `Access-Control-Allow-Methods`, `Access-Control-Allow-Headers` |

---

## Part C.4: Safe-Retry Plan

| Risky Endpoint | Mechanism Applied | Why / How It Works |
| :--- | :--- | :--- |
| `POST /items` | **Idempotency-Key** | Prevents duplicate item reports if a network timeout occurs mid-request. |
| `PATCH /items/{item_id}` | **If-Match (ETag validation)** | Prevents concurrent overwrite conflicts if two administrators modify item statuses simultaneously. |
| `GET /items/{item_id}` | **If-None-Match (Conditional GET)** | Avoids redundant data transfer by returning `304 Not Modified` when the client already holds the latest version. |

---

## Answers to Assignment Questions

### 1. For three of your endpoints, give the method, the success status, and the single response header that matters most and why.
* **`POST /items`**: Success status **201 Created**. The most important header is **`Location`** because it directs the client to the URI of the newly created item report.
* **`GET /items/{item_id}`**: Success status **200 OK**. The most important header is **`ETag`** because it uniquely identifies resource versions to enable conditional caching and safe retries.
* **`OPTIONS /items`**: Success status **204 No Content**. The most important header is **`Allow`** because it explicitly communicates the allowed HTTP methods supported by the resource.

### 2. Which of your endpoints are safe, and which are idempotent? Which one is neither, and how did you make it retry-safe?
* **Safe endpoints**: `GET /items`, `GET /items/{item_id}`, `OPTIONS /items` (they perform read-only operations and change no server state).
* **Idempotent endpoints**: `PATCH /items/{item_id}` and `POST /items/{item_id}/claim` (repeating the action yields the same terminal state).
* **Neither (Risky)**: `POST /items` is neither safe nor idempotent because each call creates a new item record. We made it retry-safe by implementing the **`Idempotency-Key`** header pattern.

### 3. Show one ETag from your service, the request that returns 304, and the write that returns 412. What does each save or prevent?
* **Example ETag**: `ETag: "e75184928d3bf7"`
* **304 Request**: `GET /items/item_123` with header `If-None-Match: "e75184928d3bf7"`. This saves network bandwidth by avoiding re-transferring unchanged payloads.
* **412 Write**: `PATCH /items/item_123` with header `If-Match: "stale-etag"`. This prevents the "lost update" problem where concurrent editors overwrite each other's changes.

### 4. You return 422 for one case and 400 for another. Give the exact request that triggers each, and explain the difference.
* **400 Bad Request**: Triggered by a syntactically invalid JSON structure (e.g., `POST /items` with body `{ invalid json }`). This means the server cannot parse the payload at all.
* **422 Unprocessable Entity**: Triggered by semantic validation failures (e.g., `POST /items` with body `{"title": ""}`) where the JSON parses correctly, but violates domain validation rules.

### 5. A browser page on another origin calls your API and is blocked yet your server logs show a 200. Who blocked it, and which response header fixes it?
* The **browser's Same-Origin Policy (CORS enforcement)** blocked it after receiving the response. The response header that fixes this is **`Access-Control-Allow-Origin: *`** (or specifying the permitted origin).

### 6. Name one response where you set Cache-Control to allow caching and one where you must use no-store. Why each?
* **Allow caching**: `GET /items/{item_id}` uses `private, max-age=60` to reduce unnecessary repeated read requests for static item descriptions.
* **No-store**: Sensitive user contact or private claim details routes use `no-store` to prevent caching sensitive personal information on intermediary proxies or browser history.

### 7. Your search endpoint is a GET. When would POST be the right choice instead, and what do you give up by switching?
* **POST** would be right if the query filters/parameters are extremely large/complex (exceeding URL length limits) or contain sensitive payload data. By switching to `POST`, you give up **safe and idempotent caching, bookmarking, and link-sharing** capabilities inherent to GET requests.

### 8. Location appears on a 201 and on a 3xx. What does it point to in each case?
* On a **201 Created** response, `Location` points to the **URI of the newly created resource** (e.g., `/items/abc123`). On a **3xx Redirection** response, it points to the **target URI where the client must redirect** to continue processing.