# CampusFind – Project Brief

## 1. What it does

CampusFind is a web-based lost-and-found management system designed for a college campus. It provides a common platform where students can report lost or found belongings and search for items reported by others.

Users can register and log in, report lost and found items, browse and filter item listings, and receive potential matches between lost and found reports. Users can also submit claims for found items, while item owners can approve or reject received claims. Notifications keep users informed about claim-related activities.

The system uses a rule-based matching mechanism that compares item categories, title keywords and locations to identify possible matches.

## 2. Who uses it

- **Students and staff:** Users who create accounts, report lost or found items, browse listings, submit claims and receive notifications. They interact with the system through the web application.
- **Item owners:** Users who receive claims for their reported items and can approve or reject those claims.

The application provides a common interface for users to manage lost-and-found activities within the campus.

## 3. Nouns (Resources and Services)

CampusFind uses three backend microservices and an API Gateway.

### Services

| Service | Responsibility |
|---|---|
| Auth Service | User registration, login and authentication |
| Item Service | Lost and found item reporting, listing and filtering |
| Matching & Claims Service | Matching suggestions, claim management and notifications |
| API Gateway | Routes frontend requests to the appropriate backend service |

### Things (Resources)

The application uses five main database resources:

| Resource | Meaning |
|---|---|
| User | A registered person with account information such as name, email and password hash |
| Item | A reported lost or found object containing its title, description, category, location, date and status |
| Match | A potential pairing between a lost item and a found item, along with a matching score |
| Claim | A user's request to claim a found item, including a message and claim status |
| Notification | A message associated with a user, which can be read or unread |

Lost and found items are represented in the same `items` table and distinguished using the `item_type` field.

## 4. Verbs (Actions and Contracts)

| Service | Actions |
|---|---|
| Auth Service | `register`, `login` |
| Item Service | `createItem`, `getItems`, `getItemById`, `filterItems` |
| Matching & Claims Service | `getMatchSuggestions`, `submitClaim`, `getReceivedClaims`, `approveClaim`, `rejectClaim`, `getNotifications`, `markNotificationAsRead` |
| API Gateway | Route requests to the appropriate backend service |

These operations are exposed through REST API endpoints and use HTTP methods such as GET, POST and PATCH.

## 5. Database and Communication

CampusFind uses a shared MySQL database named `campusfind`, containing the following tables:

- `users`
- `items`
- `matches`
- `claims`
- `notifications`

The frontend communicates with the backend through the API Gateway. The Gateway forwards requests to the appropriate microservice. Backend services can also communicate directly through HTTP requests when required.

All three services use the shared MySQL database to access the application data.

## 6. Project Objective

The main objective of CampusFind is to simplify the process of recovering lost belongings within a campus by providing an organized, accessible and centralized lost-and-found platform.
