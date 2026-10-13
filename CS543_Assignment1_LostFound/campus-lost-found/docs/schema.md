# Schema design - service-owned data

This page restates the project's schema design. Columns that point at another
service's table (for example `user_id` in `lost_items`) are **identifiers
only**; there is no cross-service database access.

## User Service

**users**

| Column | Type |
|---|---|
| user_id | INT (PK) |
| name | VARCHAR(100) |
| email | VARCHAR(150) |
| password_hash | VARCHAR(255) |
| role | VARCHAR(20) |
| created_at | DATETIME |

**profiles**

| Column | Type |
|---|---|
| profile_id | INT (PK) |
| user_id | INT |
| phone | VARCHAR(20) |
| department | VARCHAR(100) |
| student_or_staff_id | VARCHAR(50) |
| updated_at | DATETIME |

## Item Service

**categories**

| Column | Type |
|---|---|
| category_id | INT (PK) |
| name | VARCHAR(100) |
| description | VARCHAR(255) |

**lost_items**

| Column | Type |
|---|---|
| lost_item_id | INT (PK) |
| user_id | INT |
| category_id | INT |
| title | VARCHAR(150) |
| description | TEXT |
| location | VARCHAR(150) |
| lost_date | DATE |
| status | VARCHAR(30) |

**found_items**

| Column | Type |
|---|---|
| found_item_id | INT (PK) |
| user_id | INT |
| category_id | INT |
| title | VARCHAR(150) |
| description | TEXT |
| location | VARCHAR(150) |
| found_date | DATE |
| status | VARCHAR(30) |

## Matching Service

**matches**

| Column | Type |
|---|---|
| match_id | INT (PK) |
| lost_item_id | INT |
| found_item_id | INT |
| score | DECIMAL(5,2) |
| status | VARCHAR(30) |
| created_at | DATETIME |

## Claim Service

**claims**

| Column | Type |
|---|---|
| claim_id | INT (PK) |
| user_id | INT |
| found_item_id | INT |
| proof_details | TEXT |
| contact_details | VARCHAR(255) |
| status | VARCHAR(30) |
| created_at | DATETIME |

**claim_verification**

| Column | Type |
|---|---|
| verification_id | INT (PK) |
| claim_id | INT |
| verified_by | INT |
| verification_notes | TEXT |
| verified_at | DATETIME |
| result | VARCHAR(30) |

## Notification Service

**notifications**

| Column | Type |
|---|---|
| notification_id | INT (PK) |
| user_id | INT |
| type | VARCHAR(50) |
| message | TEXT |
| is_read | BOOLEAN |
| created_at | DATETIME |
