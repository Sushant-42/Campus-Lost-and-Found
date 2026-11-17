# CampusFind – Campus Lost & Found Management System

CampusFind is a web-based lost-and-found management system designed for students and staff. It allows users to report lost and found items, browse reported items, receive potential matches, submit claims, and get notifications about claim activities.

This project is developed as part of the **CS543 – Web Services** course.

Group no-5
## Team Members

| Member | Name | Student ID |
|---|---|---|
| Member 1 | Sushant Prashant Prabhavalkar | 20251651093 |
| Member 2 | Harsh Yadav | 20251651042 |

## Project Overview

CampusFind simplifies the process of recovering lost belongings within a campus environment.

Users can:
- Create an account and log in securely.
- Report lost and found items.
- Browse and filter reported items.
- Receive potential matches between lost and found items.
- Submit claims for found items.
- Approve or reject received claims.
- View and manage notifications.

## Technology Stack

| Component | Technology |
|---|---|
| Frontend | HTML, CSS, JavaScript |
| Backend | Node.js, Express.js |
| Database | MySQL |
| API Architecture | REST APIs |
| Authentication | JWT |
| Password Security | bcryptjs |
| API Gateway | Express + http-proxy-middleware |

## Architecture

CampusFind follows a microservices architecture consisting of three backend services and one API Gateway.

### 1. Auth Service
- Handles user registration and login.
- Hashes passwords using bcryptjs.
- Generates JWT tokens for authentication.

### 2. Item Service
- Handles lost and found item reports.
- Provides item listing and filtering.
- Manages item information and status.

### 3. Matching & Claims Service
- Generates potential matches using rule-based matching.
- Handles claim submission and approval/rejection.
- Manages user notifications.

### 4. API Gateway
- Acts as the single entry point for frontend API requests.
- Routes requests to the appropriate backend service.
- Serves the frontend application.

## Database

CampusFind uses a MySQL database named `campusfind`.

The database contains five main tables:

| Table | Purpose |
|---|---|
| `users` | Stores registered user information |
| `items` | Stores lost and found item reports |
| `matches` | Stores potential matching records |
| `claims` | Stores item claim requests |
| `notifications` | Stores user notifications |

## Project Structure

```text
CampusFind/
│
├── frontend/
│   ├── index.html
│   ├── style.css
│   └── app.js
│
├── api-gateway/
│   └── server.js
│
├── services/
│   ├── auth-service/
│   ├── item-service/
│   └── matching-service/
│
├── database/
│
├── server.js
├── README.md
├── http-log.md
├── network-analysis.md
├── brief.md
└── docs/
```

## Running the Project

### Prerequisites
- Node.js and npm
- MySQL Server
- Required project dependencies installed
- Database configured using the service environment files

### Start CampusFind

From the root project directory, execute:

```bash
node server.js
```

The application starts the three backend services and the API Gateway.

Open the application in your browser:

```text
http://localhost:5000
```

### Service Ports

| Component | Port |
|---|---:|
| API Gateway | 5000 |
| Auth Service | 5001 |
| Item Service | 5002 |
| Matching Service | 5003 |

## Project Documentation

Additional documentation is maintained in the `docs/` directory.

- `docs/architecture.md` – System architecture and service communication.
- `docs/schema.md` – Database schema and relationships.

## Project Status

The core CampusFind application has been implemented, including authentication, item management, matching, claims, notifications, database integration, and API Gateway routing.

The project is being developed and documented as part of the CS543 Web Services coursework.