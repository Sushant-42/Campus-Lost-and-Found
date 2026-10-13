<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$errors = [];
$type = strtolower((string)($_GET['type'] ?? 'lost'));
if (!in_array($type, ['lost', 'found'], true)) $type = 'lost';
$categories = ['Accessories', 'Bags', 'Books', 'Electronics', 'ID / Cards', 'Keys', 'Personal', 'Stationery', 'Clothing', 'Other'];
$values = ['item_name'=>'', 'category'=>'Other', 'description'=>'', 'location'=>'', 'item_date'=>date('Y-m-d'), 'contact_name'=>'', 'contact_email'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = strtolower((string)($_POST['report_type'] ?? 'lost'));
    if (!in_array($type, ['lost', 'found'], true)) $type = 'lost';
    foreach ($values as $key => $default) $values[$key] = trim((string)($_POST[$key] ?? $default));
    if ($values['item_name'] === '' || mb_strlen($values['item_name']) > 150) $errors[] = 'Enter an item name (up to 150 characters).';
    if (!in_array($values['category'], $categories, true)) $errors[] = 'Choose a valid category.';
    if ($values['location'] === '' || mb_strlen($values['location']) > 150) $errors[] = 'Enter a campus location (up to 150 characters).';
    if ($values['contact_name'] === '' || mb_strlen($values['contact_name']) > 120) $errors[] = 'Enter the contact person name.';
    if (!filter_var($values['contact_email'], FILTER_VALIDATE_EMAIL) || mb_strlen($values['contact_email']) > 190) $errors[] = 'Enter a valid contact email address.';
    $date = DateTime::createFromFormat('Y-m-d', $values['item_date']);
    if (!$date || $date->format('Y-m-d') !== $values['item_date']) $errors[] = 'Choose a valid date.';
    if (mb_strlen($values['description']) > 5000) $errors[] = 'Description must be 5000 characters or fewer.';

    if (!$errors) {
        $initialStatus = $type === 'lost' ? 'searching' : 'available';
        $statement = $pdo->prepare('INSERT INTO items (report_type,item_name,category,description,location,item_date,contact_name,contact_email,status) VALUES (:type,:name,:category,:description,:location,:item_date,:contact_name,:contact_email,:status)');
        $statement->execute([
            ':type' => $type, ':name' => $values['item_name'], ':category' => $values['category'],
            ':description' => $values['description'], ':location' => $values['location'], ':item_date' => $values['item_date'],
            ':contact_name' => $values['contact_name'], ':contact_email' => $values['contact_email'], ':status' => $initialStatus,
        ]);
        header('Location: index.php?submitted=1');
        exit;
    }
}
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Report an Item | CampusFind</title><link rel="preconnect" href="https://fonts.googleapis.com"><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"><link rel="stylesheet" href="simple.css"></head>
<body><header class="site-header"><a class="brand" href="index.php"><span class="brand-icon">C</span><span><strong>CampusFind</strong><small>Lost &amp; Found</small></span></a><nav><a href="index.php">Dashboard</a><a href="items.php">Browse items</a><a href="admin_login.php">Admin</a></nav></header>
<main class="page-wrap"><section class="form-card"><a class="back-link" href="index.php">← Back to dashboard</a><h1>Report an Item</h1><p class="muted">Add accurate details so the campus community can help reunite items with their owners.</p>
<?php if ($errors): ?><div class="alert error"><strong>Please correct the following:</strong><ul><?php foreach ($errors as $error): ?><li><?= h($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="post" action="report.php" class="report-form">
<label for="report_type">Report type</label><select id="report_type" name="report_type" required><option value="lost" <?= $type === 'lost' ? 'selected' : '' ?>>I lost an item</option><option value="found" <?= $type === 'found' ? 'selected' : '' ?>>I found an item</option></select>
<label for="item_name">Item name *</label><input id="item_name" name="item_name" maxlength="150" required value="<?= h($values['item_name']) ?>" placeholder="e.g. Black leather wallet">
<label for="category">Category *</label><select id="category" name="category" required><?php foreach ($categories as $category): ?><option value="<?= h($category) ?>" <?= $values['category'] === $category ? 'selected' : '' ?>><?= h($category) ?></option><?php endforeach; ?></select>
<label for="description">Description</label><textarea id="description" name="description" rows="4" maxlength="5000" placeholder="Colour, brand, distinguishing marks, contents (avoid sharing sensitive details)"><?= h($values['description']) ?></textarea>
<label for="location">Campus location *</label><input id="location" name="location" maxlength="150" required value="<?= h($values['location']) ?>" placeholder="e.g. Central Library, first floor">
<label for="item_date">Date lost/found *</label><input type="date" id="item_date" name="item_date" required max="<?= h(date('Y-m-d')) ?>" value="<?= h($values['item_date']) ?>">
<div class="form-row"><div><label for="contact_name">Contact name *</label><input id="contact_name" name="contact_name" maxlength="120" required value="<?= h($values['contact_name']) ?>"></div><div><label for="contact_email">Contact email *</label><input type="email" id="contact_email" name="contact_email" maxlength="190" required value="<?= h($values['contact_email']) ?>" placeholder="you@institute.edu"></div></div>
<button class="primary-button" type="submit">Submit report</button><p class="small-note">This classroom demo stores reports in your local MySQL database. Use an institute email where possible.</p>
</form></section></main></body></html>
