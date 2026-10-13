<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$q = trim((string)($_GET['q'] ?? ''));
$searchSql = '';
$searchParams = [];
if ($q !== '') {
    $searchSql = " AND (item_name LIKE :q1 OR category LIKE :q2 OR location LIKE :q3 OR description LIKE :q4)";
    $needle = '%' . $q . '%';
    $searchParams = [':q1' => $needle, ':q2' => $needle, ':q3' => $needle, ':q4' => $needle];
}

$count = static function (string $sql, array $params = []) use ($pdo): int {
    $statement = $pdo->prepare($sql);
    $statement->execute($params);
    return (int)$statement->fetchColumn();
};
$lostCount = $count("SELECT COUNT(*) FROM items WHERE report_type='lost' AND status <> 'closed'");
$foundCount = $count("SELECT COUNT(*) FROM items WHERE report_type='found' AND status <> 'closed'");
$successfulMatches = $count("SELECT COUNT(*) FROM items WHERE status IN ('matched','claimed')");
$pendingMatches = $count("SELECT COUNT(DISTINCT l.id) FROM items l JOIN items f ON LOWER(l.category)=LOWER(f.category) WHERE l.report_type='lost' AND f.report_type='found' AND l.status='searching' AND f.status='available'");

$potentialStatement = $pdo->query("SELECT l.id AS lost_id, l.item_name AS lost_name, l.category AS lost_category, f.id AS found_id, f.item_name AS found_name, f.location AS found_location FROM items l JOIN items f ON LOWER(l.category)=LOWER(f.category) WHERE l.report_type='lost' AND f.report_type='found' AND l.status='searching' AND f.status='available' ORDER BY (LOWER(l.item_name)=LOWER(f.item_name)) DESC, l.created_at DESC LIMIT 1");
$potentialMatch = $potentialStatement->fetch() ?: null;

$itemStatement = $pdo->prepare("SELECT * FROM items WHERE report_type=:type AND status <> 'closed' {$searchSql} ORDER BY created_at DESC LIMIT 3");
$itemStatement->execute(array_merge([':type' => 'lost'], $searchParams));
$recentLost = $itemStatement->fetchAll();
$itemStatement->execute(array_merge([':type' => 'found'], $searchParams));
$recentFound = $itemStatement->fetchAll();

$recentActivity = $pdo->query("SELECT report_type,item_name,created_at FROM items WHERE status <> 'closed' ORDER BY created_at DESC LIMIT 3")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>CampusFind | Lost & Found</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <style>

        /* ==============================
           GLOBAL
        ============================== */

        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;

            --background: #f6f7fb;
            --sidebar: #111827;
            --card: #ffffff;

            --text: #172033;
            --muted: #737b8c;

            --border: #e6e8ef;

            --green: #16a34a;
            --red: #dc2626;
            --orange: #ea580c;
            --blue: #2563eb;

            --shadow: 0 8px 25px rgba(15, 23, 42, 0.05);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Inter", sans-serif;
            background: var(--background);
            color: var(--text);
        }

        button,
        input {
            font-family: inherit;
        }

        button {
            cursor: pointer;
        }

        a {
            text-decoration: none;
            color: inherit;
        }


        /* ==============================
           MAIN APP
        ============================== */

        .app {
            display: flex;
            min-height: 100vh;
        }


        /* ==============================
           SIDEBAR
        ============================== */

        .sidebar {
            width: 265px;
            background: var(--sidebar);
            color: white;

            padding: 25px 18px;

            display: flex;
            flex-direction: column;

            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;

            z-index: 10;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;

            padding: 3px 10px 30px;
        }

        .brand-icon {
            width: 42px;
            height: 42px;

            border-radius: 12px;

            background: linear-gradient(
                135deg,
                #6366f1,
                #8b5cf6
            );

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 20px;
            font-weight: 800;

            box-shadow: 0 5px 20px rgba(99, 102, 241, 0.35);
        }

        .brand h2 {
            font-size: 17px;
        }

        .brand span {
            display: block;
            font-size: 11px;
            color: #8f98aa;
            margin-top: 2px;
        }


        /* ==============================
           NAVIGATION
        ============================== */

        .navigation {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .nav-item {
            min-height: 46px;

            display: flex;
            align-items: center;
            gap: 12px;

            padding: 0 13px;

            color: #aeb6c5;

            border-radius: 9px;

            font-size: 13px;
            font-weight: 500;

            transition: 0.2s;
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, 0.06);
            color: white;
        }

        .nav-item.active {
            background: var(--primary);
            color: white;

            box-shadow:
                0 5px 18px rgba(79, 70, 229, 0.25);
        }

        .nav-icon {
            width: 20px;
            text-align: center;
            font-size: 15px;
        }

        .notification-count {
            width: 20px;
            height: 20px;

            margin-left: auto;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 50%;

            background: #ef4444;
            color: white;

            font-size: 10px;
            font-weight: 700;
        }

        .nav-divider {
            height: 1px;

            background: rgba(255, 255, 255, 0.08);

            margin: 15px 8px;
        }


        /* ==============================
           SIDEBAR BOTTOM
        ============================== */

        .sidebar-bottom {
            margin-top: auto;
        }

        .help-box {
            background: rgba(255, 255, 255, 0.06);

            border: 1px solid rgba(255, 255, 255, 0.05);

            border-radius: 10px;

            padding: 14px;

            display: flex;
            align-items: center;
            gap: 10px;

            margin-bottom: 20px;
        }

        .help-icon {
            width: 30px;
            height: 30px;

            border-radius: 50%;

            background: rgba(99, 102, 241, 0.2);

            display: flex;
            align-items: center;
            justify-content: center;

            color: #a5b4fc;
            font-weight: 700;
        }

        .help-box strong {
            font-size: 12px;
        }

        .help-box p {
            font-size: 10px;
            color: #858fa2;
            margin-top: 3px;
        }

        .profile {
            display: flex;
            align-items: center;
            gap: 10px;

            padding: 13px 6px 5px;

            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        .avatar {
            width: 38px;
            height: 38px;

            flex-shrink: 0;

            border-radius: 50%;

            background: #e0e7ff;
            color: #4338ca;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 12px;
            font-weight: 700;
        }

        .profile-info {
            display: flex;
            flex-direction: column;

            min-width: 0;
        }

        .profile-info strong {
            font-size: 11px;

            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .profile-info span {
            font-size: 10px;
            color: #8c95a7;
            margin-top: 3px;
        }

        .profile-menu {
            margin-left: auto;
            color: #7c8494;
        }


        /* ==============================
           MAIN CONTENT
        ============================== */

        .main-content {
            margin-left: 265px;
            width: calc(100% - 265px);
        }


        /* ==============================
           TOP BAR
        ============================== */

        .topbar {
            height: 76px;

            background: rgba(255, 255, 255, 0.96);

            border-bottom: 1px solid var(--border);

            display: flex;
            align-items: center;

            gap: 25px;

            padding: 0 38px;

            position: sticky;
            top: 0;

            z-index: 5;
        }

        .search-container {
            width: min(480px, 60%);
            height: 40px;

            display: flex;
            align-items: center;

            padding: 0 13px;

            background: #f5f6fa;
            border: 1px solid #eaebf0;

            border-radius: 9px;
        }

        .search-icon {
            color: #9299a9;
            font-size: 20px;
            margin-right: 8px;
        }

        .search-container input {
            width: 100%;

            border: none;
            outline: none;

            background: transparent;

            color: var(--text);

            font-size: 12px;
        }

        .search-container input::placeholder {
            color: #9aa1b0;
        }

        .top-actions {
            margin-left: auto;

            display: flex;
            align-items: center;
            gap: 22px;
        }

        .icon-button {
            width: 38px;
            height: 38px;

            position: relative;

            border: 1px solid var(--border);
            border-radius: 9px;

            background: white;

            font-size: 15px;
        }

        .top-notification {
            position: absolute;

            top: 7px;
            right: 7px;

            width: 6px;
            height: 6px;

            border-radius: 50%;

            background: #ef4444;
        }

        .top-profile {
            display: flex;
            align-items: center;
            gap: 9px;

            font-size: 12px;
        }

        .top-profile > div:nth-child(2) {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .top-profile strong {
            font-size: 11px;
        }

        .top-profile span {
            color: #9299a9;
            font-size: 10px;
        }

        .avatar.small {
            width: 34px;
            height: 34px;
        }

        .mobile-brand {
            display: none;
        }


        /* ==============================
           CONTENT
        ============================== */

        .content {
            max-width: 1450px;

            margin: auto;

            padding: 34px 38px 50px;
        }

        .welcome {
            color: #8b93a3;
            font-size: 11px;
            margin-bottom: 7px;
        }

        .page-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;

            margin-bottom: 28px;
        }

        .page-heading h1 {
            font-size: 27px;
            letter-spacing: -0.7px;
        }

        .page-heading p {
            margin-top: 7px;

            color: var(--muted);

            font-size: 12px;
        }


        /* ==============================
           BUTTONS
        ============================== */

        .primary-button {
            padding: 12px 17px;

            border: none;
            border-radius: 8px;

            background: var(--primary);
            color: white;

            font-size: 12px;
            font-weight: 600;

            box-shadow:
                0 5px 15px rgba(79, 70, 229, 0.22);

            transition: 0.2s;
        }

        .primary-button:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .primary-button {
            display: inline-block;
            text-decoration: none;
        }

        .empty-state {
            color: var(--muted);
            font-size: 12px;
            line-height: 1.6;
            padding: 18px 0;
        }

        .outline-button {
            display: inline-block;
            text-decoration: none;
            padding: 9px 13px;

            border: 1px solid #b7b9ed;
            border-radius: 7px;

            background: white;
            color: var(--primary);

            font-size: 10px;
            font-weight: 600;
        }


        /* ==============================
           STATISTICS
        ============================== */

        .stats-grid {
            display: grid;

            grid-template-columns: repeat(4, 1fr);

            gap: 15px;

            margin-bottom: 20px;
        }

        .stat-card {
            padding: 18px;

            background: white;

            border: 1px solid var(--border);

            border-radius: 12px;

            box-shadow: var(--shadow);
        }

        .stat-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stat-label {
            color: var(--muted);

            font-size: 11px;
            font-weight: 600;
        }

        .stat-icon {
            width: 32px;
            height: 32px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 8px;

            font-size: 13px;
        }

        .stat-icon.red {
            background: #fee2e2;
        }

        .stat-icon.blue {
            background: #dbeafe;
        }

        .stat-icon.green {
            background: #dcfce7;
        }

        .stat-icon.orange {
            background: #ffedd5;
        }

        .stat-card h2 {
            margin-top: 13px;

            font-size: 25px;
            letter-spacing: -0.5px;
        }

        .stat-card p {
            margin-top: 4px;

            color: #9aa1b0;

            font-size: 9px;
        }

        .up {
            color: var(--green);
            font-weight: 600;
        }


        /* ==============================
           MATCH ALERT
        ============================== */

        .match-alert {
            display: flex;
            align-items: center;
            gap: 14px;

            margin-bottom: 20px;

            padding: 15px 18px;

            border: 1px solid #dddffb;
            border-radius: 12px;

            background:
                linear-gradient(
                    90deg,
                    #eef2ff,
                    #f8f7ff
                );
        }

        .match-alert-icon {
            width: 39px;
            height: 39px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 10px;

            background: #e0e7ff;
        }

        .match-alert-content {
            flex: 1;
        }

        .match-alert-content strong {
            font-size: 12px;
        }

        .match-alert-content p {
            margin-top: 4px;

            color: #70788a;

            font-size: 10px;
        }


        /* ==============================
           DASHBOARD PANELS
        ============================== */

        .dashboard-grid {
            display: grid;

            grid-template-columns: 1fr 1fr;

            gap: 18px;

            margin-bottom: 18px;
        }

        .panel {
            padding: 20px;

            background: white;

            border: 1px solid var(--border);
            border-radius: 12px;

            box-shadow: var(--shadow);
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;

            padding-bottom: 15px;
        }

        .panel-header h3 {
            font-size: 14px;
        }

        .panel-header p {
            color: #9299a9;

            font-size: 9px;

            margin-top: 4px;
        }

        .panel-header > a {
            color: var(--primary);

            font-size: 10px;
            font-weight: 600;
        }


        /* ==============================
           ITEM CARDS
        ============================== */

        .item-list {
            display: flex;
            flex-direction: column;
        }

        .item-card {
            display: flex;
            align-items: center;
            gap: 12px;

            padding: 13px 0;

            border-top: 1px solid #f0f1f4;
        }

        .item-card:first-child {
            border-top: none;
        }

        .item-image {
            width: 49px;
            height: 49px;

            flex-shrink: 0;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 9px;

            font-size: 22px;
        }

        .item-image.wallet {
            background: #fef3c7;
        }

        .item-image.keys {
            background: #e0f2fe;
        }

        .item-image.bottle {
            background: #dbeafe;
        }

        .item-image.phone {
            background: #ede9fe;
        }

        .item-image.bag {
            background: #dcfce7;
        }

        .item-image.watch {
            background: #f3e8ff;
        }

        .item-details {
            min-width: 0;
            flex: 1;
        }

        .item-details h4 {
            font-size: 11px;

            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .item-meta {
            display: flex;
            gap: 12px;

            margin-top: 5px;

            color: #8d95a5;

            font-size: 8px;
        }

        .tag-row {
            display: flex;
            gap: 5px;

            margin-top: 7px;
        }

        .tag {
            padding: 3px 6px;

            border-radius: 4px;

            font-size: 7px;
            font-weight: 600;
        }

        .tag.lost {
            color: #b91c1c;
            background: #fee2e2;
        }

        .tag.found {
            color: #15803d;
            background: #dcfce7;
        }

        .tag.category {
            color: #6b7280;
            background: #f3f4f6;
        }


        /* ==============================
           ITEM STATUS
        ============================== */

        .status {
            padding: 5px 7px;

            border-radius: 5px;

            white-space: nowrap;

            font-size: 7px;
            font-weight: 600;
        }

        .status.pending {
            color: #c2410c;
            background: #fff7ed;
        }

        .status.searching {
            color: #1d4ed8;
            background: #eff6ff;
        }

        .status.matched {
            color: #15803d;
            background: #dcfce7;
        }

        .status.available {
            color: #047857;
            background: #ecfdf5;
        }

        .status.reserved {
            color: #7e22ce;
            background: #f3e8ff;
        }


        /* ==============================
           BOTTOM GRID
        ============================== */

        .bottom-grid {
            display: grid;

            grid-template-columns: 1.35fr 1fr;

            gap: 18px;
        }


        /* ==============================
           MATCHING FLOW
        ============================== */

        .matching-flow {
            display: flex;
            align-items: center;

            padding: 16px 4px 5px;
        }

        .flow-step {
            display: flex;
            align-items: flex-start;

            gap: 9px;

            flex: 1;
        }

        .flow-number {
            width: 27px;
            height: 27px;

            flex-shrink: 0;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 50%;

            background: #eef2ff;
            color: var(--primary);

            font-size: 10px;
            font-weight: 700;
        }

        .flow-step strong {
            font-size: 10px;
        }

        .flow-step p {
            color: #9098a8;

            font-size: 8px;
            line-height: 1.4;

            margin-top: 3px;
        }

        .flow-line {
            width: 25px;
            height: 1px;

            flex-shrink: 0;

            background: #dddfe7;

            margin: 0 7px;
        }


        /* ==============================
           NOTIFICATIONS
        ============================== */

        .notification {
            display: flex;
            gap: 10px;

            padding: 12px 0;

            border-top: 1px solid #f0f1f4;
        }

        .notification:first-of-type {
            border-top: none;
        }

        .notification-dot {
            width: 7px;
            height: 7px;

            flex-shrink: 0;

            border-radius: 50%;

            margin-top: 5px;
        }

        .green-dot {
            background: #22c55e;
        }

        .blue-dot {
            background: #3b82f6;
        }

        .orange-dot {
            background: #f97316;
        }

        .notification strong {
            font-size: 10px;
        }

        .notification p {
            color: #7e8798;

            font-size: 9px;

            margin-top: 3px;
        }

        .notification span {
            display: block;

            color: #afb5c0;

            font-size: 8px;

            margin-top: 5px;
        }


        /* ==============================
           TABLET
        ============================== */

        @media (max-width: 1100px) {

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .bottom-grid {
                grid-template-columns: 1fr;
            }
        }


        /* ==============================
           SMALL TABLET
        ============================== */

        @media (max-width: 800px) {

            .sidebar {
                width: 215px;
            }

            .main-content {
                margin-left: 215px;
                width: calc(100% - 215px);
            }

            .topbar {
                padding: 0 20px;
            }

            .content {
                padding: 25px 20px 40px;
            }

            .top-profile > div:nth-child(2),
            .top-profile > span {
                display: none;
            }
        }


        /* ==============================
           MOBILE
        ============================== */

        @media (max-width: 650px) {

            .sidebar {
                display: none;
            }

            .main-content {
                width: 100%;
                margin-left: 0;
            }

            .mobile-brand {
                display: flex;
                align-items: center;
                gap: 8px;

                white-space: nowrap;
            }

            .mobile-brand .brand-icon {
                width: 34px;
                height: 34px;

                font-size: 15px;
            }

            .topbar {
                gap: 12px;
                padding: 0 15px;
            }

            .search-container {
                width: 100%;
                max-width: 260px;
            }

            .top-actions {
                gap: 8px;
            }

            .top-profile {
                display: none;
            }

            .page-heading {
                align-items: flex-start;

                flex-direction: column;

                gap: 15px;
            }

            .page-heading h1 {
                font-size: 23px;
            }

            .stats-grid {
                grid-template-columns: 1fr 1fr;
                gap: 10px;
            }

            .match-alert {
                align-items: flex-start;
                flex-wrap: wrap;
            }

            .match-alert .outline-button {
                margin-left: 53px;
            }

            .matching-flow {
                flex-direction: column;
                align-items: stretch;

                gap: 10px;
            }

            .flow-line {
                width: 1px;
                height: 12px;

                margin-left: 13px;
            }

            .content {
                padding: 25px 15px;
            }
        }


        /* ==============================
           VERY SMALL MOBILE
        ============================== */

        @media (max-width: 430px) {

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .search-container {
                max-width: 175px;
            }

            .item-meta {
                flex-direction: column;
                gap: 2px;
            }

            .status {
                display: none;
            }
        }

    </style>
</head>


<body>

<div class="app">

    <!-- ==============================
         SIDEBAR
    ============================== -->

    <aside class="sidebar">

        <div class="brand">

            <div class="brand-icon">
                C
            </div>

            <div>
                <h2>CampusFind</h2>
                <span>Lost & Found</span>
            </div>

        </div>


        <nav class="navigation">

            <a href="index.php" class="nav-item active">
                <span class="nav-icon">⌂</span>
                Dashboard
            </a>

            <a href="items.php?type=lost" class="nav-item">
                <span class="nav-icon">🔍</span>
                Lost Items
            </a>

            <a href="items.php?type=found" class="nav-item">
                <span class="nav-icon">📦</span>
                Found Items
            </a>

            <a href="matches.php" class="nav-item">
                <span class="nav-icon">🔗</span>
                Matches
            </a>

            <a href="index.php#notifications" class="nav-item">

                <span class="nav-icon">🔔</span>

                Notifications

                <span class="notification-count">
                    <?= count($recentActivity) ?>
                </span>

            </a>

            <div class="nav-divider"></div>

            <a href="report.php?type=lost" class="nav-item">
                <span class="nav-icon">📝</span>
                Report Lost Item
            </a>

            <a href="report.php?type=found" class="nav-item">
                <span class="nav-icon">➕</span>
                Report Found Item
            </a>

        </nav>


        <div class="sidebar-bottom">

            <div class="help-box">

                <div class="help-icon">
                    ?
                </div>

                <div>
                    <strong>Need Help?</strong>
                    <p>Contact campus support</p>
                </div>

            </div>


            <div class="profile">

                <div class="avatar">
                    SP
                </div>

                <div class="profile-info">

                    <strong>
                        Sushant Prabhavalkar
                    </strong>

                    <span>
                        Student
                    </span>

                </div>

                <span class="profile-menu">
                    •••
                </span>

            </div>

        </div>

    </aside>


    <!-- ==============================
         MAIN CONTENT
    ============================== -->

    <main class="main-content">


        <!-- TOP BAR -->

        <header class="topbar">

            <div class="mobile-brand">

                <div class="brand-icon">
                    C
                </div>

                <strong>
                    CampusFind
                </strong>

            </div>


            <form class="search-container" action="index.php" method="get" role="search">
                <span class="search-icon">⌕</span>
                <input type="text" name="q" value="<?= h($q) ?>" placeholder="Search for items, locations or categories..." aria-label="Search items">
            </form>


            <div class="top-actions">

                <button class="icon-button">

                    🔔

                    <span class="top-notification"></span>

                </button>


                <div class="top-profile">

                    <div class="avatar small">
                        SP
                    </div>

                    <div>

                        <strong>
                            Sushant
                        </strong>

                        <span>
                            Student
                        </span>

                    </div>

                    <span>
                        ⌄
                    </span>

                </div>

            </div>

        </header>


        <!-- ==============================
             PAGE CONTENT
        ============================== -->

        <section class="content">


            <!-- PAGE HEADING -->

            <div class="page-heading">

                <div>

                    <div class="welcome">
                        <?= h(date('l, F j, Y')) ?>
                    </div>

                    <h1>
                        Campus Lost & Found
                    </h1>

                    <p>
                        Find what you've lost or help someone recover their belongings.
                    </p>

                </div>


                <a class="primary-button" href="report.php">+ Report an Item</a>

            </div>
            <?php if (isset($_GET['submitted'])): ?>
                <div class="match-alert"><div class="match-alert-icon">✓</div><div class="match-alert-content"><strong>Report submitted successfully</strong><p>Your report has been saved in the CampusFind database.</p></div></div>
            <?php endif; ?>

            <!-- ==============================
                 STATISTICS
            ============================== -->

            <div class="stats-grid">
                <div class="stat-card"><div class="stat-top"><span class="stat-label">Lost Items</span><div class="stat-icon red">🔍</div></div><h2><?= $lostCount ?></h2><p>Active lost reports</p></div>
                <div class="stat-card"><div class="stat-top"><span class="stat-label">Found Items</span><div class="stat-icon blue">📦</div></div><h2><?= $foundCount ?></h2><p>Items reported found</p></div>
                <div class="stat-card"><div class="stat-top"><span class="stat-label">Resolved Items</span><div class="stat-icon green">✓</div></div><h2><?= $successfulMatches ?></h2><p>Items marked matched or claimed</p></div>
                <div class="stat-card"><div class="stat-top"><span class="stat-label">Potential Matches</span><div class="stat-icon orange">⏳</div></div><h2><?= $pendingMatches ?></h2><p>Lost reports with a found item in the same category</p></div>
            </div>


            <!-- ==============================
                 MATCH ALERT
            ============================== -->

            <div class="match-alert">
                <div class="match-alert-icon">✨</div>
                <div class="match-alert-content">
                    <?php if ($potentialMatch): ?>
                        <strong>Potential Match Found!</strong>
                        <p>Lost item <b><?= h($potentialMatch['lost_name']) ?></b> and found item <b><?= h($potentialMatch['found_name']) ?></b> are in the same category (<?= h($potentialMatch['lost_category']) ?>). Please verify the details before confirming a match.</p>
                    <?php else: ?>
                        <strong>No potential matches yet</strong>
                        <p>When a lost and found report share the same category, the system will list them as a potential match for manual verification.</p>
                    <?php endif; ?>
                </div>
                <a class="outline-button" href="matches.php">View Matches</a>
            </div>


            <!-- ==============================
                 LOST + FOUND
            ============================== -->

            <div class="dashboard-grid">
                <section class="panel">
                    <div class="panel-header"><div><h3>Recently Lost</h3><p>Items reported missing on campus</p></div><a href="items.php?type=lost">View all →</a></div>
                    <div class="item-list">
                        <?php if (!$recentLost): ?><p class="empty-state">No active lost reports<?= $q !== '' ? ' matching your search' : ' yet' ?>.</p><?php else: ?>
                            <?php foreach ($recentLost as $item) renderItemCard($item); ?>
                        <?php endif; ?>
                    </div>
                </section>
                <section class="panel">
                    <div class="panel-header"><div><h3>Recently Found</h3><p>Items waiting to be claimed</p></div><a href="items.php?type=found">View all →</a></div>
                    <div class="item-list">
                        <?php if (!$recentFound): ?><p class="empty-state">No active found reports<?= $q !== '' ? ' matching your search' : ' yet' ?>.</p><?php else: ?>
                            <?php foreach ($recentFound as $item) renderItemCard($item); ?>
                        <?php endif; ?>
                    </div>
                </section>
            </div>


            <!-- ==============================
                 BOTTOM SECTION
            ============================== -->

            <div class="bottom-grid">


                <!-- HOW MATCHING WORKS -->

                <section class="panel">

                    <div class="panel-header">

                        <div>

                            <h3>
                                How Matching Works
                            </h3>

                            <p>
                                The system suggests possible matches by category
                            </p>

                        </div>

                    </div>


                    <div class="matching-flow">


                        <div class="flow-step">

                            <div class="flow-number">
                                1
                            </div>

                            <div>

                                <strong>
                                    Report
                                </strong>

                                <p>
                                    Student reports a lost or found item.
                                </p>

                            </div>

                        </div>


                        <div class="flow-line"></div>


                        <div class="flow-step">

                            <div class="flow-number">
                                2
                            </div>

                            <div>

                                <strong>
                                    Analyze
                                </strong>

                                <p>
                                    System checks for another report in the same category.
                                </p>

                            </div>

                        </div>


                        <div class="flow-line"></div>


                        <div class="flow-step">

                            <div class="flow-number">
                                3
                            </div>

                            <div>

                                <strong>
                                    Match
                                </strong>

                                <p>
                                    Potential matches are identified.
                                </p>

                            </div>

                        </div>


                        <div class="flow-line"></div>


                        <div class="flow-step">

                            <div class="flow-number">
                                4
                            </div>

                            <div>

                                <strong>
                                    Notify
                                </strong>

                                <p>
                                    Administration verifies possible matches.
                                </p>

                            </div>

                        </div>

                    </div>

                </section>


                <!-- NOTIFICATIONS -->

                <section class="panel" id="notifications">
                    <div class="panel-header"><div><h3>Recent Activity</h3><p>Latest reports submitted to CampusFind</p></div><a href="items.php">See all</a></div>
                    <?php if (!$recentActivity): ?><p class="empty-state">Activity will appear here after the first item is reported.</p><?php else: ?>
                        <?php foreach ($recentActivity as $activity): ?>
                            <div class="notification">
                                <div class="notification-dot <?= $activity['report_type'] === 'lost' ? 'orange-dot' : 'green-dot' ?>"></div>
                                <div><strong><?= $activity['report_type'] === 'lost' ? 'Lost item reported' : 'Found item reported' ?></strong><p><?= h($activity['item_name']) ?></p><span><?= h(relativeTime($activity['created_at'])) ?></span></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </section>

            </div>

        </section>

    </main>

</div>

</body>
</html>