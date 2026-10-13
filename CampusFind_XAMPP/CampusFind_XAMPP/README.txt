# CampusFind — XAMPP + PHP + MySQL

This is a local/demo implementation of the attached CampusFind dashboard. It keeps the original dashboard styling and connects its counts, lost/found lists, search, recent activity, and potential-match suggestions to MySQL.

## Files

- `index.php` — dashboard with live database counts and latest lost/found reports.
- `config.php` — PDO database connection settings.
- `database.sql` — creates the database/tables, an administrator account, and six demo reports.
- `report.php` — form to submit a lost or found report.
- `items.php` — searchable list of reported items.
- `matches.php` — possible matches based on matching categories; staff should verify manually.
- `admin_login.php` and `manage.php` — administrator login and report status management.
- `functions.php`, `simple.css` — common helpers and styling for the supporting pages.

## Run in XAMPP (Windows)

1. Install/start XAMPP. Open the XAMPP Control Panel and click **Start** beside **Apache** and **MySQL**.
2. Copy the entire `CampusFind_XAMPP` folder into `C:\xampp\htdocs\`. The folder should be `C:\xampp\htdocs\CampusFind_XAMPP\index.php` and the other files should sit beside it.
3. Open `http://localhost/phpmyadmin` in your browser.
4. Select **Import**, choose `database.sql` from the `CampusFind_XAMPP` folder, and click **Import** / **Go**. The SQL creates a database called `campusfind`.
5. Check `config.php`. Default XAMPP settings are host `127.0.0.1`, database `campusfind`, username `root`, and an empty password. If your MySQL root account has a password, set `$dbPass` there.
6. Open `http://localhost/CampusFind_XAMPP/` or `http://localhost/CampusFind_XAMPP/index.php`.
7. Click **Report an Item** to submit a lost/found report. Dashboard counters and lists should update after submission.
8. For management, open **Admin** and sign in using username `admin` and password `CampusAdmin@123`. Use the status dropdowns in the management page to mark reports Matched, Claimed, Closed, etc.

## Notes

- Six sample reports are inserted so that you can see the dashboard populated immediately. Delete the demo rows from the `items` table in phpMyAdmin if you want a clean start.
- The potential-match page suggests pairs when a lost and found item have the same category. This is only a basic rule, not AI or proof that they are the same item. Verify item details before returning anything.
- This is intended for a classroom/local project. Before deploying on a public server, change/remove the seeded admin account, use a strong unique administrator password, add CSRF protection/rate limiting and appropriate access controls, enable HTTPS, and establish a process for protecting and deleting contact data.
- The project uses PHP PDO prepared statements for database operations. Do not open the PHP files directly from File Explorer; access them through `http://localhost/...` so PHP executes.

## Troubleshooting

- **Database connection error:** ensure MySQL is running, import `database.sql`, and check `config.php` credentials.
- **404 Not Found:** verify the folder is inside `C:\xampp\htdocs\CampusFind_XAMPP\` and the URL folder name matches exactly.
- **Apache will not start:** another application may be using port 80/443. Check XAMPP's log and resolve the port conflict.
- **PDO driver error:** use a standard XAMPP PHP distribution that has `pdo_mysql` enabled.
