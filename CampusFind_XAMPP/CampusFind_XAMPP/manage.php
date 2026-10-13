<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
if (empty($_SESSION['campusfind_admin_id'])) { header('Location: admin_login.php'); exit; }
$allowedStatuses = ['searching','available','matched','claimed','closed'];
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT);
    $status = (string)($_POST['status'] ?? '');
    if ($id && in_array($status, $allowedStatuses, true)) {
        $update = $pdo->prepare('UPDATE items SET status = :status WHERE id = :id');
        $update->execute([':status' => $status, ':id' => $id]);
        $message = 'Item status updated.';
    } else $message = 'Could not update that item.';
}
$items = $pdo->query('SELECT id, report_type, item_name, category, location, status, created_at FROM items ORDER BY created_at DESC')->fetchAll();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Manage Reports | CampusFind</title><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"><link rel="stylesheet" href="simple.css"></head><body><header class="site-header"><a class="brand" href="index.php"><span class="brand-icon">C</span><span><strong>CampusFind</strong><small>Administration</small></span></a><nav><a href="index.php">Dashboard</a><a href="admin_login.php?logout=1">Log out</a></nav></header><main class="page-wrap wide"><a class="back-link" href="index.php">← Dashboard</a><h1>Manage reports</h1><p class="muted intro-copy">Signed in as <?= h($_SESSION['campusfind_admin_name'] ?? 'admin') ?>. Change a report's status after verifying its details.</p><?php if ($message): ?><div class="alert success"><?= h($message) ?></div><?php endif; ?><div class="table-card"><div class="table-scroll"><table><thead><tr><th>Item</th><th>Type</th><th>Location</th><th>Current status</th><th>Update</th></tr></thead><tbody><?php foreach ($items as $item): ?><tr><td><strong><?= h($item['item_name']) ?></strong><small><?= h($item['category']) ?> · <?= h(relativeTime($item['created_at'])) ?></small></td><td><?= h(ucfirst($item['report_type'])) ?></td><td><?= h($item['location']) ?></td><td><?= h(statusLabel($item['status'])) ?></td><td><form method="post" class="inline-form"><input type="hidden" name="item_id" value="<?= (int)$item['id'] ?>"><select name="status" aria-label="New status for <?= h($item['item_name']) ?>"><?php foreach ($allowedStatuses as $status): ?><option value="<?= h($status) ?>" <?= $item['status']===$status?'selected':'' ?>><?= h(statusLabel($status)) ?></option><?php endforeach; ?></select><button class="small-button" type="submit">Save</button></form></td></tr><?php endforeach; ?></tbody></table><?php if (!$items): ?><p class="empty-state">No item reports yet.</p><?php endif; ?></div></div><p class="small-note">For a found report choose Available; for a verified pairing choose Matched; after return choose Claimed. Closed reports are hidden from the public dashboard but remain here for administration.</p></main></body></html>
