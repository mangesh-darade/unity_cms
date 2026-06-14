<?php
require_once __DIR__ . '/../includes/db.php';
foreach ($db->query('SELECT id, title, image_path, sequence FROM cms_gallery ORDER BY sequence') as $r) {
    echo implode(' | ', $r) . PHP_EOL;
}
