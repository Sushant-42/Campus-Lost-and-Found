# Campus Lost & Found Management System

A microservices-based system that helps students and staff report lost items,
report found items, get matched with each other, and claim items back.

This repository is the project workspace for the **CS543 Web Services** course.

- **Course:** CS543 - Web Services
- **Team ID:** 5
- **Team members:**
  - Sushant Prashant Prabhavalakar - 20251651093
  - Harsh Yadav - 20251651042

## Project status

| Assignment | Topic | Status |
|---|---|---|
| 1 | HTTP by hand and project setup | In this repository |
| 2 - 6 | (later assignments) | Not started |

## What the system does (short version)

People who lose something report a **lost item**. People who find something
report a **found item**. The system compares the two kinds of reports and
stores possible **matches**. The owner then submits a **claim** with proof,
the claim is **verified**, and **notifications** keep everyone informed.

The full description is in [`brief.md`](brief.md).

## Architecture in one paragraph

Five services - **User, Item, Matching, Claim, Notification** - each own
their own database tables. A service never reads another service's tables;
it only keeps identifiers (such as `user_id`) and asks the other service
through its API when it needs more. Details: [`docs/architecture.md`](docs/architecture.md)
and [`docs/schema.md`](docs/schema.md).

## Repository structure

```
campus-lost-found/
|-- README.md              this file
|-- http-log.md            Task 1: five curl requests, annotated
|-- network-analysis.md    Task 2: browser DevTools -> Network analysis
|-- brief.md               Task 4: what / who / nouns / verbs
|-- docs/
|   |-- architecture.md    the five services and their operations
|   |-- schema.md          tables owned by each service
|   `-- screenshots/       DevTools screenshot(s) for network-analysis.md
|-- scripts/
|   `-- run-http-requests.bat   runs the five curl commands and saves raw output
`-- evidence/              raw curl output saved by the script (created when it runs)
```

## Assignment 1 files

| File | What it contains |
|---|---|
| [`http-log.md`](http-log.md) | Five `curl -i` request/response pairs against a public JSON API, one deliberate 404 |
| [`network-analysis.md`](network-analysis.md) | Request count, total size, slowest resource and any 3xx/4xx seen in the browser |
| [`brief.md`](brief.md) | One-page brief with the nouns and verbs listed explicitly |

## How to reproduce the HTTP log (Windows)

Open **Command Prompt** in the repository folder and run:

```
scripts\run-http-requests.bat
```

The raw output of each request is saved in `evidence/`. The commands are also
listed one by one in `http-log.md`.
