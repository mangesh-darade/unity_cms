<?php
require __DIR__ . '/../includes/db.php';
echo "DB: " . (defined('CMS_DB_DRIVER') ? CMS_DB_DRIVER : 'sqlite') . "\n\n";
echo "TEAM (active):\n";
foreach ($db->query("SELECT title, image_path FROM cms_page_blocks WHERE page_slug='about' AND block_type='team' AND is_active=1 ORDER BY sequence") as $r) {
    $exists = is_file(__DIR__ . '/../' . $r['image_path']) ? 'OK' : 'MISSING';
    echo "  {$r['title']}\n    DB path: {$r['image_path']} [{$exists}]\n";
}
$intro = $db->query("SELECT title, image_path FROM cms_page_blocks WHERE page_slug='about' AND block_type='intro'")->fetch();
echo "\nOWNER (intro):\n  {$intro['title']}\n    DB path: {$intro['image_path']}\n";
