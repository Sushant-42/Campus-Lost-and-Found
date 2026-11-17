@echo off
REM Runs the five curl requests from http-log.md and saves the RAW output
REM of each one in the evidence folder. Run from Command Prompt:
REM     scripts\run-http-requests.bat
REM Requires Windows 10 (1803) or newer, which includes curl.exe.

cd /d "%~dp0.."
if not exist evidence mkdir evidence

echo Request 1 of 5: GET /users/1
curl.exe -sS -i -v https://jsonplaceholder.typicode.com/users/1 > evidence\01-get-user.txt 2>&1

echo Request 2 of 5: GET /posts/1
curl.exe -sS -i -v https://jsonplaceholder.typicode.com/posts/1 > evidence\02-get-post.txt 2>&1

echo Request 3 of 5: GET /posts?userId=1
curl.exe -sS -i -v "https://jsonplaceholder.typicode.com/posts?userId=1" > evidence\03-list-filtered.txt 2>&1

echo Request 4 of 5: GET /posts/1/comments
curl.exe -sS -i -v https://jsonplaceholder.typicode.com/posts/1/comments > evidence\04-nested-comments.txt 2>&1

echo Request 5 of 5: GET /posts/99999 (deliberate 404)
curl.exe -sS -i -v https://jsonplaceholder.typicode.com/posts/99999 > evidence\05-not-found-404.txt 2>&1

echo.
echo Done. Raw output saved in the evidence folder.
echo Now copy each file's request (> lines) and response into http-log.md.
