# Campus Lost & Found - Brief

## What it does

Things get lost on campus all the time: ID cards, bottles, chargers, notebooks.
This system gives students and staff one place to deal with that. Someone who
has lost something **reports a lost item**. Someone who has found something
**reports a found item**. The system compares the two kinds of reports and
records a possible **match** with a score. When an owner thinks a found item is
theirs, they submit a **claim** with proof, and the claim is **verified**
before anything is handed over. **Notifications** tell people when something
happens to their items or claims.

## Who uses it

- **Students and staff** - the people who report lost items, report found
  items, search, submit claims and read their notifications. They use it
  through a web app.
- **A staff member who verifies claims** - checks the proof and records the
  result. (In the schema this is the `verified_by` user in `claim_verification`.)

## Nouns (the things and the services)

**Services** - five, each owning its own data:

| Service | Owns these things |
|---|---|
| User Service | Users, Profiles |
| Item Service | Categories, Lost Items, Found Items |
| Matching Service | Matches |
| Claim Service | Claims, Claim Verification |
| Notification Service | Notifications |

**Things (resources)** - nine:

| Noun | Meaning |
|---|---|
| User | A person with an account (name, email, role) |
| Profile | Extra details of a user (phone, department, student/staff id) |
| Category | A kind of item (for example "Electronics"); used to group and search items |
| Lost Item | A report that something was lost: title, description, location, date, status |
| Found Item | A report that something was found: title, description, location, date, status |
| Match | A suggested pairing of one lost item with one found item, with a score |
| Claim | A user's request to get a found item back, with proof and contact details |
| Claim Verification | The review of a claim: who checked it, their notes, and the result |
| Notification | A message for a user, which can be read or unread |

## Verbs (the actions / contracts)

| Service | Verbs |
|---|---|
| User Service | `createUser`, `getUser`, `updateProfile` |
| Item Service | `reportLostItem`, `reportFoundItem`, `searchItems`, `updateItemStatus` |
| Matching Service | `findMatches`, `getMatch`, `updateMatchStatus` |
| Claim Service | `submitClaim`, `getClaim`, `updateClaimStatus` |
| Notification Service | `sendNotification`, `getNotifications`, `markAsRead` |

## Ground rules

1. Each service owns its own tables and no other service touches them.
2. Services refer to each other's data by **identifier only** (such as
   `user_id` or an item id) and ask the owning service through its API for
   anything more.
3. The Matching Service stores match results, but the Item Service stays the
   owner of the lost and found item data.
