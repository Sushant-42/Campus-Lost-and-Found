# Assignment 1 - how to finish and submit (Windows)

The folder `campus-lost-found/` is your repository. Four files are required by
the assignment: `README.md`, `http-log.md`, `network-analysis.md`, `brief.md`.

| File | State when you receive it |
|---|---|
| `README.md` | Complete |
| `brief.md` | Complete (read it and put it in your own words where you like) |
| `http-log.md` | **Template - you must run the commands and paste real output** |
| `network-analysis.md` | **Template - you must measure in your browser** |
| `docs/architecture.md`, `docs/schema.md` | Complete (restate your given designs) |

Anything unfinished is marked `<<FILL: ...>>`.

---

## Step 0 - Open the project

1. Unzip, then open the `campus-lost-found` folder in **VS Code** (File -> Open Folder).
2. Open a terminal inside VS Code: **Terminal -> New Terminal**.
   (It opens PowerShell by default. The commands below say which shell to use.)

## Step 1 - Check your tools

In **Command Prompt** or **PowerShell**:

```
git --version
curl.exe --version
```

- If `git` is not found, install it: `winget install --id Git.Git -e`
  (or download from https://git-scm.com), then **close and reopen** the terminal.
- `curl.exe` is built into Windows 10 (version 1803) and Windows 11.
- **PowerShell warning:** in PowerShell, plain `curl` is an alias for a different
  command (`Invoke-WebRequest`). Always type **`curl.exe`**.

Write the first line of `curl.exe --version`, and the output of `ver`
(Command Prompt), into the Setup table of `http-log.md`.

## Step 2 - Task 1: the five curl requests

**Option A (easiest) - Command Prompt, from the repository folder:**

```
scripts\run-http-requests.bat
```

This runs the five requests and saves the raw output of each one in the
`evidence\` folder (`01-get-user.txt` ... `05-not-found-404.txt`).

To switch from PowerShell to Command Prompt inside VS Code, type `cmd`.

**Option B - run them one by one and look at the output:**

```
curl.exe -sS -i -v https://jsonplaceholder.typicode.com/users/1
curl.exe -sS -i -v https://jsonplaceholder.typicode.com/posts/1
curl.exe -sS -i -v "https://jsonplaceholder.typicode.com/posts?userId=1"
curl.exe -sS -i -v https://jsonplaceholder.typicode.com/posts/1/comments
curl.exe -sS -i -v https://jsonplaceholder.typicode.com/posts/99999
```

To save one in **PowerShell**:

```
curl.exe -sS -i -v https://jsonplaceholder.typicode.com/users/1 2>&1 | Out-File -Encoding utf8 evidence\01-get-user.txt
```

(`evidence` must exist first: `mkdir evidence`.)

Flags: `-i` prints the response headers; `-v` also prints the request curl sent
(the `>` lines); `-sS` hides the progress bar but still shows errors.

**Then edit `http-log.md`:** for each request paste the `>` lines under
"Request as sent", the response under "Response received", and write your
one-line note. Request 5 must show a `404`. If a response body is very long
(requests 3 and 4) you may shorten the body, but keep the status line and all headers.

## Step 3 - Task 2: DevTools network analysis

1. Open Chrome or Edge and go to any website.
2. `F12` -> **Network** tab -> tick **Disable cache**.
3. Reload with `Ctrl + R`; wait until it finishes.
4. Read the bottom bar (requests, transferred, resources, load/finish).
5. Click the **Time** column header to find the slowest resource.
6. Filter `status-code:3` and `status-code:4` (or sort by **Status**) for 3xx/4xx.
7. Take a screenshot (`Win + Shift + S`) and save it in `docs\screenshots\`.
8. Fill in `network-analysis.md`.

## Step 4 - Task 3: the Git repository and commit history

The assignment asks you to **commit as you work, not one final commit**. Files
that are not yet `git add`-ed simply stay untracked, so you can make real
commits as you finish each piece. Do not backdate or squash commits.

In **PowerShell or Command Prompt**, inside `campus-lost-found`:

```
git init -b main
git config user.name "Your Name"
git config user.email "you@example.com"
```

(If `git init -b main` is not accepted by an older Git, use `git init` and then
`git branch -M main`.)

Suggested commits (adjust the messages to your own words):

```
git add README.md
git commit -m "Add README with project overview and structure"

git add docs/architecture.md docs/schema.md
git commit -m "Add architecture and schema docs"

git add brief.md
git commit -m "Add project brief with nouns and verbs"

git add scripts/run-http-requests.bat http-log.md
git commit -m "Add http-log template and curl request script"
```

Now do Step 2 for real, fill in `http-log.md`, then:

```
git add evidence http-log.md
git commit -m "Record real curl responses and annotations in http-log"

git add network-analysis.md
git commit -m "Add network-analysis template"
```

Now do Step 3 for real, fill in `network-analysis.md`, then:

```
git add network-analysis.md docs/screenshots
git commit -m "Add DevTools measurements and screenshot"
```

Check the history:

```
git log --oneline
```

**Same thing in VS Code:** open Source Control (`Ctrl + Shift + G`), click `+`
next to the files you want in the commit, type a message, press **Commit**.

## Step 5 - Put it on GitHub and get the link

1. On github.com click **New repository**. Name: `campus-lost-found`.
   Leave "Add a README" **unticked** (you already have one). Create it.
2. Back in the terminal (replace `YOUR-USERNAME`):

```
git remote add origin https://github.com/YOUR-USERNAME/campus-lost-found.git
git push -u origin main
```

   A browser window asks you to sign in the first time.
3. Open the repository page and check that README.md renders and all four
   files are there. Make the repository public, or give your instructor access.
4. Copy the repository URL - that is what you submit.

## Step 6 - Final check before submitting

Search for unfinished markers. **PowerShell:**

```
Get-ChildItem -Recurse -Filter *.md | Select-String "<<FILL"
```

**Command Prompt:**

```
findstr /s /n /c:"<<FILL" *.md
```

No output means nothing is left to fill in. Then go through the checklists at
the bottom of `http-log.md` and `network-analysis.md`, and `CHECKLIST.md`.
