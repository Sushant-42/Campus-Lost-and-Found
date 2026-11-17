# Database Schema - CampusFind

## 1. Database Overview

CampusFind uses a shared MySQL database named `campusfind`.

The database contains five main tables:

1. `users`
2. `items`
3. `matches`
4. `claims`
5. `notifications`

The tables are connected using primary keys and foreign key constraints to maintain data consistency.

## 2. Users Table

Stores information about registered users.

| Column | Description |
|---|---|
| `id` | INT, Primary Key |
| `name` | User's full name |
| `email` | User's email address |
| `password` | Hashed password |
| `phone` | User's phone number |
| `created_at` | Account creation timestamp |

## 3. Items Table

Stores both lost and found item reports.

| Column | Description |
|---|---|
| `id` | INT, Primary Key |
| `user_id` | INT, Foreign Key referencing `users.id` |
| `title` | Item title |
| `description` | Item description |
| `category` | Item category |
| `item_type` | ENUM: `lost` or `found` |
| `location` | Location where the item was lost or found |
| `item_date` | Date associated with the item |
| `image_url` | Image reference, if available |
| `status` | ENUM: `open`, `claimed`, or `closed` |
| `created_at` | Report creation timestamp |

## 4. Matches Table

Stores potential matches between lost and found items.

| Column | Description |
|---|---|
| `id` | INT, Primary Key |
| `lost_item_id` | INT, Foreign Key referencing `items.id` |
| `found_item_id` | INT, Foreign Key referencing `items.id` |
| `match_score` | Matching score calculated by the matching logic |
| `status` | Match status |
| `created_at` | Match creation timestamp |

## 5. Claims Table

Stores claims submitted by users for reported items.

| Column | Description |
|---|---|
| `id` | INT, Primary Key |
| `item_id` | INT, Foreign Key referencing `items.id` |
| `claimant_id` | INT, Foreign Key referencing `users.id` |
| `message` | Claim message or supporting details |
| `status` | ENUM: `pending`, `approved`, or `rejected` |
| `created_at` | Claim creation timestamp |

## 6. Notifications Table

Stores notifications associated with users.

| Column | Description |
|---|---|
| `id` | INT, Primary Key |
| `user_id` | INT, Foreign Key referencing `users.id` |
| `message` | Notification message |
| `is_read` | Boolean indicating whether the notification has been read |
| `created_at` | Notification creation timestamp |

## 7. Database Relationships

The database contains the following foreign key relationships:

| Child Table | Foreign Key | Referenced Table |
|---|---|---|
| `items` | `user_id` | `users.id` |
| `claims` | `item_id` | `items.id` |
| `claims` | `claimant_id` | `users.id` |
| `matches` | `lost_item_id` | `items.id` |
| `matches` | `found_item_id` | `items.id` |
| `notifications` | `user_id` | `users.id` |

There are six foreign key constraints in total.

## 8. Design Notes

- Lost and found reports are stored in a single `items` table. The `item_type` column distinguishes between them.
- Passwords are stored as hashes rather than plain text.
- Claims connect users with the items they want to claim.
- Matches connect a lost item with a potentially corresponding found item.
- Notifications are associated with individual users.
- All five tables are part of the shared `campusfind` database.

## 9. Summary

The CampusFind database uses a relational design with five tables and six foreign key constraints. It supports user authentication, lost and found item management, potential matching, claim processing, and notifications.