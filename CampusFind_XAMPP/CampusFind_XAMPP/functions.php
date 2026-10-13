<?php
function h(mixed $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function relativeTime(?string $datetime): string {
    if (!$datetime) return 'Date not provided';
    try {
        $time = new DateTimeImmutable($datetime);
        $now = new DateTimeImmutable('now');
        $seconds = max(0, $now->getTimestamp() - $time->getTimestamp());
        if ($seconds < 60) return 'Just now';
        if ($seconds < 3600) return floor($seconds / 60) . ' min ago';
        if ($seconds < 86400) return floor($seconds / 3600) . ' hours ago';
        if ($seconds < 172800) return 'Yesterday';
        return floor($seconds / 86400) . ' days ago';
    } catch (Throwable $e) {
        return h($datetime);
    }
}

function itemIcon(array $item): string {
    $haystack = strtolower(($item['item_name'] ?? '') . ' ' . ($item['category'] ?? ''));
    foreach ([
        'wallet' => '👛', 'key' => '🔑', 'bottle' => '🥤', 'phone' => '📱',
        'iphone' => '📱', 'bag' => '🎒', 'backpack' => '🎒', 'watch' => '⌚',
        'laptop' => '💻', 'book' => '📚', 'card' => '💳', 'umbrella' => '☂️',
    ] as $keyword => $icon) {
        if (str_contains($haystack, $keyword)) return $icon;
    }
    return '📦';
}

function statusClass(string $status): string {
    return match ($status) {
        'searching' => 'searching',
        'matched' => 'matched',
        'available' => 'available',
        'claimed' => 'matched',
        default => 'pending',
    };
}

function statusLabel(string $status): string {
    return match ($status) {
        'searching' => 'Searching',
        'available' => 'Available',
        'matched' => 'Matched',
        'claimed' => 'Claimed',
        'closed' => 'Closed',
        default => ucfirst($status),
    };
}

function renderItemCard(array $item): void {
    $type = $item['report_type'];
    $isLost = $type === 'lost';
    $status = $item['status'];
    ?>
    <div class="item-card">
        <div class="item-image <?= h(strtolower($item['category'] ?? '')) ?>"><?= h(itemIcon($item)) ?></div>
        <div class="item-details">
            <h4><?= h($item['item_name']) ?></h4>
            <div class="item-meta">
                <span>📍 <?= h($item['location']) ?></span>
                <span>🕐 <?= h(relativeTime($item['created_at'])) ?></span>
            </div>
            <div class="tag-row">
                <span class="tag <?= $isLost ? 'lost' : 'found' ?>"><?= $isLost ? 'Lost' : 'Found' ?></span>
                <span class="tag category"><?= h($item['category']) ?></span>
            </div>
        </div>
        <span class="status <?= h(statusClass($status)) ?>"><?= h(statusLabel($status)) ?></span>
    </div>
    <?php
}
