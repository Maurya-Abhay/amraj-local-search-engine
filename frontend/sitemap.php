<?php
require_once __DIR__ . '/../backend/config.php';
require_once __DIR__ . '/public_layout.php';

header('Content-Type: application/xml; charset=utf-8');

$base_url = amraj_get_base_url();

$static_pages = [
    ['loc' => 'index.php', 'priority' => '1.0'],
    ['loc' => 'categories.php', 'priority' => '0.8'],
    ['loc' => 'search.php', 'priority' => '0.7'],
    ['loc' => 'about.php', 'priority' => '0.5'],
    ['loc' => 'contact.php', 'priority' => '0.5'],
    ['loc' => 'terms.php', 'priority' => '0.3'],
    ['loc' => 'privacy-policy.php', 'priority' => '0.3'],
];

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

foreach ($static_pages as $page) {
    echo '  <url>' . "\n";
    echo '    <loc>' . htmlspecialchars($base_url . $page['loc'], ENT_QUOTES, 'UTF-8') . '</loc>' . "\n";
    echo '    <priority>' . $page['priority'] . '</priority>' . "\n";
    echo '  </url>' . "\n";
}

// Approved businesses — each listing is its own indexable page.
$result = amraj_query($conn, "SELECT id, created_at FROM businesses WHERE status = 'approved'");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo '  <url>' . "\n";
        echo '    <loc>' . htmlspecialchars($base_url . 'shop-details.php?id=' . (int)$row['id'], ENT_QUOTES, 'UTF-8') . '</loc>' . "\n";
        if (!empty($row['created_at'])) {
            echo '    <lastmod>' . date('c', strtotime($row['created_at'])) . '</lastmod>' . "\n";
        }
        echo '    <priority>0.6</priority>' . "\n";
        echo '  </url>' . "\n";
    }
}

echo '</urlset>' . "\n";
