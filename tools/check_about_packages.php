<?php
require_once __DIR__ . '/../includes/db.php';

echo "=== FULL ABOUT INTRO ===\n";
$row = $db->query("SELECT * FROM cms_page_blocks WHERE page_slug='about' AND block_type='intro'")->fetch(PDO::FETCH_ASSOC);
print_r($row);

echo "\n=== TEAM BLOCKS ===\n";
foreach ($db->query("SELECT id,title,subtitle,content FROM cms_page_blocks WHERE page_slug='about' AND block_type='team'") as $r) {
    print_r($r);
}

echo "\n=== PACKAGES FULL ===\n";
foreach ($db->query('SELECT * FROM cms_packages ORDER BY sequence') as $r) {
    echo "---\n";
    print_r($r);
}

echo "\n=== RATE CARD SAMPLE (for comparison) ===\n";
foreach ($db->query("SELECT title, price, category FROM cms_services WHERE title IN ('CBC','LFT','RFT','Lipid Profile','HBA1C','T3 T4 TSH','Urine (R)','S. Creatinine','BSL Random')") as $r) {
    echo $r['title'] . ' | Rs ' . $r['price'] . ' | ' . $r['category'] . "\n";
}
