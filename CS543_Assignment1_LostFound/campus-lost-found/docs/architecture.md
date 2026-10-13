# Architecture - Campus Lost & Found Management System

Microservices architecture. Clients (students and staff, through a web app)
send API requests to the services. This page restates the project's
architecture diagram; it adds nothing to it.

## Services and their operations

| Service | Operations (contracts) |
|---|---|
| **User Service** | `createUser()`, `getUser()`, `updateProfile()` |
| **Item Service** | `reportLostItem()`, `reportFoundItem()`, `searchItems()`, `updateItemStatus()` |
| **Matching Service** | `findMatches()`, `getMatch()`, `updateMatchStatus()` |
| **Claim Service** | `submitClaim()`, `getClaim()`, `updateClaimStatus()` |
| **Notification Service** | `sendNotification()`, `getNotifications()`, `markAsRead()` |

## Data ownership

| Service | Owns |
|---|---|
| User | `users`, `profiles` |
| Item | `categories`, `lost_items`, `found_items` |
| Matching | `matches` |
| Claim | `claims`, `claim_verification` |
| Notification | `notifications` |

## Architectural rules

1. Each service owns and controls its own database tables.
2. Cross-service references such as `user_id` or item IDs are identifiers
   only. Services do not directly query another service's database.
3. Services get the information they need through their defined APIs/contracts.
4. Matching stores match results; the Item Service remains the owner of
   lost/found item data.

## Information passed between services (from the diagram)

The diagram labels four kinds of data passed between services:
**user data**, **item data**, **match** data and **notification** data.
Which service calls which is not fixed by the diagram; that is decided in
later assignments.
