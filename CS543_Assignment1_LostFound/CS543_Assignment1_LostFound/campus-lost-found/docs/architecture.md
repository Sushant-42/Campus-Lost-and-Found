# Architecture - CampusFind

## 1. Architecture Overview

CampusFind follows a microservices-based architecture with three backend services and one API Gateway. Students and other campus users interact with the application through a web frontend.

The frontend sends HTTP requests to the API Gateway, which forwards requests to the appropriate backend service. The services use a shared MySQL database named `campusfind`.

## 2. Main Components

| Component | Port | Responsibility |
|---|---:|---|
| Frontend | 5000 (served by Gateway) | Provides the user interface for registration, login, reporting and browsing items, claims, and notifications. |
| API Gateway | 5000 | Acts as the single entry point and routes requests to backend services. |
| Auth Service | 5001 | Handles user registration, login, password hashing, and JWT generation. |
| Item Service | 5002 | Handles lost and found item creation, listing, searching, filtering, and retrieval. |
| Matching & Claims Service | 5003 | Handles potential matches, claim submission and decisions, and notifications. |

## 3. Service Responsibilities and Operations

### Auth Service
- User registration
- User login
- Password hashing using bcryptjs
- JWT generation for authenticated users

### Item Service
- Create lost or found item reports
- Retrieve item listings
- Filter and search items
- Retrieve individual item details

### Matching & Claims Service
- Generate rule-based match suggestions
- Submit claims for reported items
- View received claims
- Approve or reject claims
- Retrieve notifications
- Mark notifications as read

### API Gateway
- Provides a single entry point for frontend requests.
- Routes `/api/auth` requests to the Auth Service.
- Routes `/api/items` requests to the Item Service.
- Routes `/api/matching` requests to the Matching & Claims Service.
- Serves the frontend application.

## 4. Database Design

CampusFind uses a shared MySQL database named `campusfind`.

The database contains five main tables:

| Table | Purpose |
|---|---|
| `users` | Stores registered user information and password hashes. |
| `items` | Stores lost and found item details. |
| `matches` | Stores potential matches between lost and found items. |
| `claims` | Stores claims submitted by users for items. |
| `notifications` | Stores user notifications and their read status. |

### Important Relationships

- `items.user_id` references `users.id`.
- `claims.item_id` references `items.id`.
- `claims.claimant_id` references `users.id`.
- `matches.lost_item_id` and `matches.found_item_id` reference `items.id`.
- `notifications.user_id` references `users.id`.

## 5. Communication Flow

1. A user interacts with the CampusFind frontend.
2. The frontend sends an HTTP request to the API Gateway.
3. The Gateway forwards the request to the relevant backend service.
4. The service processes the request and interacts with the MySQL database when required.
5. The response is returned through the Gateway to the frontend.

The Matching & Claims Service can also communicate with the Item Service through HTTP when item information is required.

## 6. Matching Logic

CampusFind uses rule-based matching to identify potentially related lost and found reports.

The matching score considers:

- Same category: 40 points
- Shared title word: 30 points
- Same location: 30 points

Potential matches with a score of 30 or higher are stored for further review. Only open items are considered.

## 7. Architectural Rules

1. The API Gateway acts as the main entry point for frontend requests.
2. Each backend service is responsible for its own application logic.
3. All three services use the shared MySQL database.
4. Authentication is handled using JWT.
5. Protected operations require valid authentication.
6. Matching is performed using the implemented rule-based logic.

## 8. Project Structure

```text
CampusFind/
├── api-gateway/
│   └── server.js
├── services/
│   ├── auth-service/
│   │   └── server.js
│   ├── item-service/
│   │   └── server.js
│   └── matching-service/
│       └── server.js
├── frontend/
├── database/
├── server.js
└── README.md
```

## 9. Summary

CampusFind uses a microservices-based design consisting of an API Gateway, Auth Service, Item Service, and Matching & Claims Service. The services communicate through HTTP APIs and use a shared MySQL database. This architecture separates major application responsibilities while providing users with a unified web application.