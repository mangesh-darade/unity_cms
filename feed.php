<?php
require_once __DIR__ . '/includes/db.php';

header('Content-Type: application/rss+xml; charset=UTF-8');

$site = cmsSetting($cms, 'site_name');
$siteUrl = rtrim(BASE_URL, '/');
$description = cmsSetting($cms, 'seo_default_description');

$posts = [];
try {
    $posts = $db->query('SELECT id, title, summary, content, created_at FROM cms_blogs ORDER BY created_at DESC LIMIT 20')->fetchAll();
} catch (PDOException $e) {
    $posts = [];
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
    <title><?php echo htmlspecialchars($site); ?> — Health Blog</title>
    <link><?php echo htmlspecialchars($siteUrl . '/blog.php'); ?></link>
    <description><?php echo htmlspecialchars($description); ?></description>
    <language>en-in</language>
    <atom:link href="<?php echo htmlspecialchars($siteUrl . '/feed.php'); ?>" rel="self" type="application/rss+xml"/>
    <?php foreach ($posts as $post): ?>
    <item>
        <title><?php echo htmlspecialchars($post['title']); ?></title>
        <link><?php echo htmlspecialchars($siteUrl . '/blog-post.php?id=' . (int) $post['id']); ?></link>
        <guid><?php echo htmlspecialchars($siteUrl . '/blog-post.php?id=' . (int) $post['id']); ?></guid>
        <pubDate><?php echo date(DATE_RSS, strtotime($post['created_at'])); ?></pubDate>
        <description><?php echo htmlspecialchars($post['summary'] ?: substr(strip_tags($post['content'] ?? ''), 0, 300)); ?></description>
    </item>
    <?php endforeach; ?>
</channel>
</rss>
