<?php
require_once __DIR__ . '/includes/config.php';

header('Content-Type: text/plain; charset=UTF-8');

$sitemap = rtrim(BASE_URL, '/') . '/sitemap.php';

echo "User-agent: *\n";
echo "Allow: /\n";
echo "\n";
echo "Disallow: /admin/\n";
echo "Disallow: /api/\n";
echo "Disallow: /includes/\n";
echo "Disallow: /storage/\n";
echo "Disallow: /uploads/\n";
echo "Disallow: /tools/\n";
echo "\n";
echo "Sitemap: {$sitemap}\n";
