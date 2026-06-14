<?php
require_once __DIR__ . '/../includes/db.php';

echo "=== ALL SETTINGS ===\n";
foreach ($db->query('SELECT key, value FROM cms_settings ORDER BY key') as $r) {
    $v = strlen($r['value']) > 80 ? substr($r['value'], 0, 77) . '...' : $r['value'];
    echo $r['key'] . ' = ' . $v . "\n";
}

echo "\n=== EQUIPMENT ===\n";
foreach ($db->query('SELECT id, title, image_path FROM cms_equipment ORDER BY sequence') as $r) {
    $exists = is_file(__DIR__ . '/../' . $r['image_path']) ? 'OK' : 'MISSING';
    echo "$exists | {$r['title']} | {$r['image_path']}\n";
}

echo "\n=== HERO / SECTIONS ===\n";
foreach ($db->query("SELECT section_code, section_tag, section_heading FROM cms_sections ORDER BY sequence") as $r) {
    echo implode(' | ', $r) . "\n";
}

echo "\n=== ADMIN ===\n";
echo 'admin_password_changed: ' . $db->query("SELECT value FROM cms_settings WHERE key='admin_password_changed'")->fetchColumn() . "\n";
