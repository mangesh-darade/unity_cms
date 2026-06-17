<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db_helpers.php';
require_once __DIR__ . '/includes/db.php';

header('Content-Type: application/xml; charset=UTF-8');

$lastmod = date('Y-m-d');
$urls = [];
$urls[] = [
    'loc' => 'index.php',
    'changefreq' => 'weekly',
    'priority' => '1.0',
];

try {
    $pages = $db->query("SELECT filename, sitemap_changefreq, sitemap_priority, include_in_sitemap, is_active FROM cms_pages WHERE is_active = 1 ORDER BY sequence ASC, id ASC")->fetchAll();
    foreach ($pages as $page) {
        if ((int) ($page['include_in_sitemap'] ?? 1) !== 1) {
            continue;
        }
        $urls[] = [
            'loc' => $page['filename'],
            'changefreq' => $page['sitemap_changefreq'] ?: 'monthly',
            'priority' => $page['sitemap_priority'] ?: '0.5',
        ];
    }

    $blogs = $db->query('SELECT id, created_at FROM cms_blogs ORDER BY id DESC')->fetchAll();
    foreach ($blogs as $blog) {
        $urls[] = [
            'loc' => 'blog-post.php?id=' . (int) $blog['id'],
            'changefreq' => 'monthly',
            'priority' => '0.6',
            'lastmod' => date('Y-m-d', strtotime($blog['created_at'])),
        ];
    }

    require_once __DIR__ . '/includes/locations_data.php';
    $urls[] = ['loc' => 'locations.php', 'changefreq' => 'monthly', 'priority' => '0.85'];
    foreach (array_keys(cmsLocationAreas()) as $citySlug) {
        $urls[] = [
            'loc' => 'location.php?city=' . rawurlencode($citySlug),
            'changefreq' => 'monthly',
            'priority' => '0.75',
        ];
    }
    $urls[] = ['loc' => 'feed.php', 'changefreq' => 'weekly', 'priority' => '0.4'];
} catch (PDOException $e) {
    $urls = [
        ['loc' => 'index.php', 'changefreq' => 'weekly', 'priority' => '1.0'],
        ['loc' => 'about.php', 'changefreq' => 'monthly', 'priority' => '0.8'],
        ['loc' => 'services.php', 'changefreq' => 'weekly', 'priority' => '0.9'],
        ['loc' => 'contact.php', 'changefreq' => 'monthly', 'priority' => '0.8'],
    ];
}

if (empty($urls)) {
    $urls[] = ['loc' => 'index.php', 'changefreq' => 'weekly', 'priority' => '1.0'];
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $page): ?>
    <url>
        <loc><?php echo htmlspecialchars(rtrim(BASE_URL, '/') . '/' . ltrim($page['loc'], '/')); ?></loc>
        <lastmod><?php echo htmlspecialchars($page['lastmod'] ?? $lastmod); ?></lastmod>
        <changefreq><?php echo htmlspecialchars($page['changefreq']); ?></changefreq>
        <priority><?php echo htmlspecialchars($page['priority']); ?></priority>
    </url>
<?php endforeach; ?>
</urlset>
