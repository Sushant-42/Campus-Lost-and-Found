# HTTP log - five requests by hand with curl

> **STATUS: TEMPLATE. The responses below have NOT been collected yet.**
> Every FILL marker (text that starts with two less-than signs followed by the
> word FILL) must be replaced with real output from your own machine before you
> submit. Do not write expected output here.

## Setup

| Item | Value |
|---|---|
| Public API used | JSONPlaceholder - `https://jsonplaceholder.typicode.com` (free, public, read-only JSON test API) |
| Tool | `curl.exe -i` (response headers + body), plus `-v` so the request headers are shown too |
| Run on | Windows - Command Prompt (a PowerShell version of every command is in `HOW_TO_RUN.md`) |
| Date and time | <<FILL: date/time you ran the commands>> |
| curl version | <<FILL: output of `curl.exe --version`, first line>> |
| Windows version | <<FILL: output of `ver` in Command Prompt>> |

**How to read the captured text:** in `curl -i -v` output, lines starting with
`>` are the **request** curl sent, lines starting with `<` and the block that
follows are the **response**. (With `-i` and `-v` together the response
headers appear twice - that is normal. Paste the request lines and one copy of
the response.)

The public API is not part of Campus Lost & Found. Each request below is
chosen because it is the same *kind* of request that our own services will
handle later; the "Link to our project" line explains the analogy.

## General meaning of the codes used (reference, not observed)

| Item | Meaning |
|---|---|
| `200 OK` | The request worked and the response body contains what was asked for. |
| `404 Not Found` | The server understood the request but there is no resource at that URL. |
| `Content-Type: application/json` | The body is JSON text. A `charset=utf-8` part says it is encoded as UTF-8. |

Your one-line note under each request should still describe **what you
actually saw** for that request.

---

## Request 1 - fetch one user

**Command to run:**
```
curl.exe -sS -i -v https://jsonplaceholder.typicode.com/users/1
```

**Link to our project:** like `getUser()` - fetch one User by its id.

**Request as sent (the `>` lines):**
```
<<FILL: paste the lines starting with ">" from your output>>
```

**Response received (status line, headers, body):**
```
<<FILL: paste the full response>>
```

**Note (one line):** Status `<<FILL: code and reason>>` means <<FILL: your words>>;
`Content-Type: <<FILL: value you saw>>` means <<FILL: your words>>.

---

## Request 2 - fetch one item-like record

**Command to run:**
```
curl.exe -sS -i -v https://jsonplaceholder.typicode.com/posts/1
```

**Link to our project:** like fetching one Lost Item or Found Item by its id.

**Request as sent:**
```
<<FILL: paste the lines starting with ">">>
```

**Response received:**
```
<<FILL: paste the full response>>
```

**Note (one line):** Status `<<FILL>>` means <<FILL>>; `Content-Type: <<FILL>>` means <<FILL>>.

---

## Request 3 - list with a query-string filter

**Command to run:**
```
curl.exe -sS -i -v "https://jsonplaceholder.typicode.com/posts?userId=1"
```

**Link to our project:** like `searchItems()` - list items and narrow the list
by a parameter (here: only the records of user 1).

**Request as sent:**
```
<<FILL: paste the lines starting with ">">>
```

**Response received:**
```
<<FILL: paste the full response (the body is long - you may shorten the body
with a line "... body shortened ..." but keep the status line and all headers)>>
```

**Note (one line):** Status `<<FILL>>` means <<FILL>>; `Content-Type: <<FILL>>` means <<FILL>>.

---

## Request 4 - records that belong to another record

**Command to run:**
```
curl.exe -sS -i -v https://jsonplaceholder.typicode.com/posts/1/comments
```

**Link to our project:** like asking for the Claims made on one Found Item -
a collection that hangs under a single parent record.

**Request as sent:**
```
<<FILL: paste the lines starting with ">">>
```

**Response received:**
```
<<FILL: paste the full response (body may be shortened as in request 3)>>
```

**Note (one line):** Status `<<FILL>>` means <<FILL>>; `Content-Type: <<FILL>>` means <<FILL>>.

---

## Request 5 - deliberate failure (404)

**Command to run:**
```
curl.exe -sS -i -v https://jsonplaceholder.typicode.com/posts/99999
```

**Link to our project:** like asking for a Lost Item id that does not exist.
This request is made to fail on purpose.

**Request as sent:**
```
<<FILL: paste the lines starting with ">">>
```

**Response received (must show the 404):**
```
<<FILL: paste the full response>>
```

**Note (one line):** Status `<<FILL: must be the 404 you saw>>` means <<FILL>>;
`Content-Type: <<FILL>>` means <<FILL>>.

---

## Before you submit

- [ ] All five requests were run by me and pasted from my own terminal.
- [ ] Request 5 shows a real `404`.
- [ ] No FILL markers are left in this file (see `HOW_TO_RUN.md`, step 6, for the search command).
- [ ] Each request has a one-line note about its status code and Content-Type.
