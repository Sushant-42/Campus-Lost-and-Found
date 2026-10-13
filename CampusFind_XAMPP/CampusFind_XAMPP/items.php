<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
$type = strtolower((string)($_GET['type'] ?? 'all'));
if (!in_array($type, ['all','lost','found'], true)) $type = 'all';
$q = trim((string)($_GET['q'] ?? ''));
$where = ["status <> 'closed'"];
$params = [];
if ($type !== 'all') { $where[] = 'report_type = :type'; $params[':type'] = $type; }
if ($q !== '') { $where[] = '(item_name LIKE :q1 OR category LIKE :q2 OR location LIKE :q3 OR description LIKE :q4)'; $like = '%' . $q . '%'; $params += [':q1'=>$like, ':q2'=>$like, ':q3'=>$like, ':q4'=>$like]; }
$sql = 'SELECT * FROM items WHERE ' . implode(' AND ', $where) . ' ORDER BY created_at DESC';
$stmt = $pdo->prepare($sql); $stmt->execute($params); $items = $stmt->fetchAll();
$title = $type === 'all' ? 'All Reported Items' : ($type === 'lost' ? 'Lost Items' : 'Found Items');
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= h($title) ?> | CampusFind</title><link rel="preconnect" href="https://fonts.googleapis.com"><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"><link rel="stylesheet" href="simple.css"></head><body>
<header class="site-header"><a class="brand" href="index.php"><span class="brand-icon">C</span><span><strong>CampusFind</strong><small>Lost &amp; Found</small></span></a><nav><a href="index.php">Dashboard</a><a href="report.php">Report an item</a><a href="admin_login.php">Admin</a></nav></header>
<main class="page-wrap wide"><section class="page-intro"><div><a class="back-link" href="index.php">← Dashboard</a><h1><?= h($title) ?></h1><p class="muted">Search current reports or narrow the list by report type.</p></div><a class="primary-button" href="report.php">+ Report an Item</a></section>
<form class="filter-bar" method="get" action="items.php"><select name="type" aria-label="Report type"><option value="all" <?= $type==='all'?'selected':'' ?>>All items</option><option value="lost" <?= $type==='lost'?'selected':'' ?>>Lost</option><option value="found" <?= $type==='found'?'selected':'' ?>>Found</option></select><input type="search" name="q" value="<?= h($q) ?>" placeholder="Search name, category, location..."><button class="primary-button" type="submit">Search</button></form>
<div class="table-card"><div class="result-count"><?= count($items) ?> report(s) shown</div><?php if (!$items): ?><p class="empty-state">No reports match this search.</p><?php else: ?><div class="table-scroll"><table><thead><tr><th>Item</th><th>Type</th><th>Category</th><th>Location</th><th>Date</th><th>Status</th></tr></thead><tbody><?php foreach ($items as $item): ?><tr><td><strong><?= h($item['item_name']) ?></strong><?php if ($item['description'] !== ''): ?><small><?= h($item['description']) ?></small><?php endif; ?></td><td><span class="pill <?= h($item['report_type']) ?>"><?= h(ucfirst($item['report_type'])) ?></span></td><td><?= h($item['category']) ?></td><td><?= h($item['location']) ?></td><td><?= h($item['item_date']) ?></td><td><span class="pill status-pill"><?= h(statusLabel($item['status'])) ?></span></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></div>
<p class="small-note">To protect claimants, contact details are not displayed publicly. Coordinate with the reporter or campus administration to verify ownership.</p></main></body></html>
