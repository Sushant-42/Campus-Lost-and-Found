<?php
declare(strict_types=1);

// XAMPP defaults. If your MySQL root account has a password, enter it below.
$dbHost = '127.0.0.1';
$dbName = 'campusfind';
$dbUser = 'root';
$dbPass = '';

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $exception) {
    http_response_code(500);
    echo '<!doctype html><html lang="en"><meta charset="utf-8"><title>Database connection error</title>';
    echo '<style>body{font:16px Arial,sans-serif;max-width:760px;margin:60px auto;padding:0 20px;color:#172033}code{background:#f1f5f9;padding:3px 6px;border-radius:4px}</style>';
    echo '<h1>CampusFind cannot connect to MySQL</h1>';
    echo '<p>Start Apache and MySQL in XAMPP, then import <code>database.sql</code> using phpMyAdmin.</p>';
    echo '<p>Check the database name and credentials at the top of <code>config.php</code>.</p>';
    echo '</html>';
    exit;
}
