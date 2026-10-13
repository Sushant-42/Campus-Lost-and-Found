# Assignment 1 - requirement checklist

Legend: **Done** = complete in the files you received. **YOU** = you must run
something or collect evidence yourself; the file is a marked template until you do.

| # | Assignment 1 requirement | File / evidence that satisfies it | State |
|---|---|---|---|
| 1a | At least 5 requests to a public read-only JSON API using `curl -i` | `http-log.md` - requests 1-5 (commands ready; JSONPlaceholder) | **YOU** run them (`scripts\run-http-requests.bat`) |
| 1b | Full request and full response captured each time | `http-log.md` sections "Request as sent" / "Response received"; raw copies in `evidence/` | **YOU** paste real output |
| 1c | At least one deliberate failure showing a 404 | `http-log.md` request 5 (`/posts/99999`); `evidence/05-not-found-404.txt` | **YOU** confirm the 404 appears |
| 1d | One-line note on what each status code and Content-Type means | `http-log.md` "Note (one line)" under each request; reference table at top | **YOU** write from what you saw |
| 1e | Submit `http-log.md` with five annotated pairs, one a 404 | `http-log.md` | **YOU** (template provided) |
| 2a | Open a website, DevTools -> Network, tick "Disable cache", reload | `network-analysis.md` "Method" + "Disable cache ticked?" row; screenshot in `docs/screenshots/` | **YOU** |
| 2b | Request count | `network-analysis.md` Results table | **YOU** |
| 2c | Total page size | `network-analysis.md` Results table (states which size is used) | **YOU** |
| 2d | Single slowest resource | `network-analysis.md` "Single slowest resource" | **YOU** |
| 2e | Any 3xx/4xx seen | `network-analysis.md` "3xx and 4xx responses" | **YOU** |
| 3a | Git repository with a clear structure (README, docs/ folder) | `campus-lost-found/` : `README.md`, `docs/architecture.md`, `docs/schema.md`, `scripts/` | Done (structure); **YOU** create the repo |
| 3b | Commit as you work, readable history | `HOW_TO_RUN.md` step 4 (planned commit sequence) -> `git log --oneline` | **YOU** make the real commits |
| 3c | Submit the repository link with a real README | `README.md` + the GitHub URL after step 5 | README Done; **YOU** push and copy the link |
| 4a | One-page brief in own words: what the system does | `brief.md` - "What it does" | Done (adjust wording if you wish) |
| 4b | Who uses it | `brief.md` - "Who uses it" | Done |
| 4c | Nouns (things / services) listed explicitly | `brief.md` - "Nouns" (5 services, 9 resources) | Done |
| 4d | Verbs (actions / contracts) listed explicitly | `brief.md` - "Verbs" (16 operations from the architecture diagram) | Done |
| S | Submit the repo link containing README.md, http-log.md, network-analysis.md, brief.md | the four files in the repo root | **YOU** after filling templates |

## Project-specific adaptation

- Repository name is `campus-lost-found` instead of `campuseats` (the PDF's name
  belongs to the original project). **Confirm with your instructor** that this is accepted.
- The nine project resources (Users, Profiles, Categories, Lost Items, Found Items,
  Matches, Claims, Claim Verification, Notifications) appear in `brief.md` and
  `docs/schema.md`. The five services and their 16 operations come from your
  architecture diagram; nothing was added.
- The public API in Task 1 is not part of the project; `http-log.md` explains how each
  request mirrors something our services will do later (`getUser`, fetching an item,
  `searchItems`, claims under an item, a missing item id).

## Things I did NOT do (by design)

- I did not run any `curl` request, take any browser measurement or create any Git
  history. This environment has no internet access and no browser, so everything that
  needs observation is a marked template.
- No Assignment 2-6 work (no API, code, OpenAPI or tests).

## Assumptions to double-check

- Team ID 5 and the two team members in `README.md` are carried over from your earlier
  assignments. Change them if this project's team is different.
- `brief.md` says a staff member verifies claims. The schema supports this
  (`claim_verification.verified_by`), but the architecture diagram has no separate
  "verify" operation, so none is listed among the verbs.
