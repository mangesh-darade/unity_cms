<?php
require_once __DIR__ . '/../includes/db.php';

$checks = [
    "SELECT key, value FROM cms_settings WHERE key IN ('logo_type','logo_image','support_phone','whatsapp_number')",
    "SELECT title, price, category FROM cms_services WHERE category = 'Hematology' ORDER BY sequence LIMIT 5",
    "SELECT title, image_path, sequence FROM cms_gallery ORDER BY sequence",
    "SELECT title, image_path, sequence FROM cms_equipment ORDER BY sequence",
];

foreach ($checks as $sql) {
    echo "\n--- $sql ---\n";
    foreach ($db->query($sql) as $row) {
        echo json_encode($row, JSON_UNESCAPED_SLASHES) . PHP_EOL;
    }
}
