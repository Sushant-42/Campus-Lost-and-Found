# Campus / Student Lost & Found — CS543 Assignment 2

## Files
- `design.pdf`: capabilities, architecture, contracts, full `submitClaim` specification, schema notes, validation table.
- `services.drawio` and `services.png`: editable service architecture and overview image.
- `schema.drawio` and `schema.png`: editable ER diagram and overview image.
- `schema.sql`: PostgreSQL 14+ schema sketch.
- `README.md`: this file.

## Services and ownership
- User Service: `users`, `profiles`
- Item Report Service: `categories`, `lost_items`, `found_items`
- Matching Service: `matches`
- Claims & Verification Service: `claims`, `claim_verification`
- Notification Service: `notifications`, `handovers`

Each table has exactly one owner. Cross-service IDs are external references; no direct cross-service foreign keys are used. The unified SQL is for review; production deployment should isolate service data and credentials.

## Diagrams and SQL
Open `.drawio` files at https://app.diagrams.net/. SQL targets PostgreSQL 14+. Review before executing in a disposable development database. The schema does not create a database or configure production credentials.

## Assumptions
Matching results are suggestions, not proof. Authorized reviewers decide claims. Role labels are proposed implementation responsibilities, not actual team member names. Runtime deployment and behavior are not claimed to have been tested.
