<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
if (isset($_GET['logout'])) { $_SESSION = []; session_destroy(); header('Location: index.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $stmt = $pdo->prepare('SELECT id, username, password_hash FROM admins WHERE username = :username LIMIT 1');
    $stmt->execute([':username' => $username]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($password, $admin['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['campusfind_admin_id'] = (int)$admin['id'];
        $_SESSION['campusfind_admin_name'] = $admin['username'];
        header('Location: manage.php'); exit;
    }
    $error = 'Invalid username or password.';
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Admin Login | CampusFind</title><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"><link rel="stylesheet" href="simple.css"></head><body><header class="site-header"><a class="brand" href="index.php"><span class="brand-icon">C</span><span><strong>CampusFind</strong><small>Administration</small></span></a><nav><a href="index.php">Dashboard</a></nav></header><main class="page-wrap"><section class="form-card compact"><a class="back-link" href="index.php">← Dashboard</a><h1>Administrator login</h1><p class="muted">Sign in to update report statuses.</p><?php if ($error): ?><div class="alert error"><?= h($error) ?></div><?php endif; ?><form method="post" class="report-form"><label for="username">Username</label><input id="username" name="username" required autocomplete="username"><label for="password">Password</label><input type="password" id="password" name="password" required autocomplete="current-password"><button class="primary-button" type="submit">Sign in</button></form><p class="small-note">Initial local-demo login: <strong>admin</strong> / <strong>CampusAdmin@123</strong>. Change or remove this account before any real deployment.</p></section></main></body></html>
