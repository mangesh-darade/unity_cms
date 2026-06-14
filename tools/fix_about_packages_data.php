<?php
/**
 * Align About, Packages, and contact data with Akshay lab / rate card.
 */
require_once __DIR__ . '/../includes/db.php';

function upsertSetting(PDO $db, string $key, string $value): void
{
    $db->prepare('INSERT INTO cms_settings (key, value) VALUES (?, ?) ON CONFLICT(key) DO UPDATE SET value = excluded.value')
        ->execute([$key, $value]);
}

function servicePrice(PDO $db, string $title): float
{
    $stmt = $db->prepare('SELECT price FROM cms_services WHERE title = ? ORDER BY id ASC LIMIT 1');
    $stmt->execute([$title]);
    return (float) ($stmt->fetchColumn() ?: 0);
}

function sumPrices(PDO $db, array $titles): int
{
    $total = 0;
    foreach ($titles as $title) {
        $total += (int) round(servicePrice($db, $title));
    }
    return $total;
}

function bundlePrice(int $sum, int $discountPercent = 0): int
{
    if ($sum <= 0) {
        return 0;
    }
    if ($discountPercent <= 0) {
        return $sum;
    }
    return (int) (round(($sum * (100 - $discountPercent) / 100) / 10) * 10) - 1;
}

$db->beginTransaction();

try {
    // ── Contact & location ──
    upsertSetting($db, 'support_address', "Unity Clinical Laboratory\nMaharashtra, India");
    upsertSetting($db, 'top_bar_location', 'Maharashtra, India');
    upsertSetting($db, 'schema_street', 'Unity Clinical Laboratory');
    upsertSetting($db, 'schema_city', 'Maharashtra');
    upsertSetting($db, 'schema_state', 'Maharashtra');
    upsertSetting($db, 'schema_postal', '');
    upsertSetting($db, 'schema_lat', '');
    upsertSetting($db, 'schema_lng', '');

    // ── About page meta ──
    $db->prepare("UPDATE cms_pages SET
        content_tag = 'Our Background',
        content_title = 'Dedicated to Precision and Patient Care since 2026',
        content_description = 'Learn about Unity Clinical Laboratory, our equipment, and our qualified laboratory team in Maharashtra.',
        meta_description = 'Unity Clinical Laboratory — established 2026. NABL-aligned pathology lab with modern analyzers and qualified DMLT staff in Maharashtra.'
        WHERE slug = 'about'")->execute();

    // ── About intro block ──
    $introContent = "Unity Clinical Laboratory was established in 2026 with a clear mission: accurate, affordable, and fast diagnostic testing for every patient.\n\n"
        . "Our Maharashtra facility is equipped with Sysmex hematology, Orbit biochemistry, and professional microscopy systems. "
        . "We follow strict sample handling, cold-chain transport for home collection, and digital report delivery.";

    $db->prepare("UPDATE cms_page_blocks SET
        subtitle = 'Dedicated to Precision and Patient Care since 2026',
        content = ?,
        image_path = 'images/gallery/web/blood-collection.jpg'
        WHERE page_slug = 'about' AND block_type = 'intro'")->execute([$introContent]);

    // ── Team section: remove placeholder doctors ──
    $db->exec("UPDATE cms_page_blocks SET is_active = 0 WHERE page_slug = 'about' AND block_type = 'team' AND title IN ('Dr. Sunita Verma', 'Dr. Raman Gupta')");

    $db->prepare("UPDATE cms_page_blocks SET
        title = 'Our Team',
        subtitle = 'Meet Our Laboratory Team',
        content = 'Qualified laboratory professionals operating modern diagnostic equipment with patient safety as the top priority.'
        WHERE page_slug = 'about' AND block_type = 'header' AND sequence = 9")->execute();

    $db->prepare("UPDATE cms_page_blocks SET
        subtitle = 'Lab In-Charge & Senior Technician (ADMLT)',
        content = 'Akshay Sanjay Rakh holds an Advanced Diploma in Medical Laboratory Technology (MSBTE, Winter 2024) with First Class Distinction. He leads daily sample processing, analyzer operations, and quality checks at Unity Clinical Laboratory.',
        icon = 'AR'
        WHERE page_slug = 'about' AND block_type = 'team' AND title = 'Akshay Sanjay Rakh'")->execute();

    // ── Packages page copy ──
    $db->prepare("UPDATE cms_pages SET
        content_tag = 'Wellness Packages',
        content_title = 'Select the Best Health Package for You',
        content_description = 'Package prices are calculated from our official rate card. Book online or call +91 98507 00268.',
        meta_description = 'Preventive health packages at Unity Clinical Laboratory — CBC, diabetes, full body and senior citizen screening. Prices based on official rate card.'
        WHERE slug = 'packages'")->execute();

    // ── Package prices from rate card ──
    $packages = [
        1 => [
            'tests' => ['CBC', 'BSL Random', 'Urine (R)', 'S. Creatinine'],
            'discount' => 0,
            'desc' => 'Essential routine screening: blood count, random sugar, urine routine, and kidney index (creatinine). Priced per official rate card.',
        ],
        2 => [
            'tests' => ['CBC', 'ESR', 'Lipid Profile', 'LFT', 'RFT', 'T3 T4 TSH', 'HBA1C', 'BSL F / PP', 'Urine Microalbumin', 'Urine (R)'],
            'discount' => 10,
            'desc' => 'Comprehensive multi-organ screening including heart, liver, kidney, thyroid, and diabetes markers. Bundle saves ~10% vs individual rate card prices.',
        ],
        3 => [
            'tests' => ['HBA1C', 'BSL F / PP', 'Lipid Profile', 'Urine Microalbumin'],
            'discount' => 10,
            'desc' => 'Diabetes monitoring panel: glycated hemoglobin, fasting/post-prandial sugar, lipid screen, and early kidney damage marker.',
        ],
        4 => [
            'tests' => ['CBC', 'RFT', 'S. G. O. T', 'S. G. P. T', 'Lipid Profile', 'S. Calcium', 'BSL F / PP'],
            'discount' => 10,
            'desc' => 'Senior-focused checkup covering blood count, kidney, liver enzymes, cholesterol, calcium, and blood sugar.',
        ],
        5 => [
            'tests' => ['CBC', 'S. Ferritin', 'T3 T4 TSH', 'S. Calcium', 'HBA1C', 'S. Creatinine', 'S. G. O. T', 'S. G. P. T', 'Urine C/S'],
            'discount' => 10,
            'desc' => 'Women\'s wellness panel: anaemia screen, thyroid, bone mineral, diabetes, kidney/liver enzymes, and urine culture.',
        ],
    ];

    $priceStmt = $db->prepare('UPDATE cms_packages SET price = ?, description = ? WHERE id = ?');

    foreach ($packages as $id => $cfg) {
        $sum = sumPrices($db, $cfg['tests']);
        $price = bundlePrice($sum, $cfg['discount']);
        $desc = $cfg['desc'] . ' (Individual tests total: ₹' . number_format($sum) . '.)';
        $priceStmt->execute([$price, $desc, $id]);
        echo "Package #$id: sum=₹$sum → price=₹$price\n";
    }

    $db->commit();
    echo "\nAbout, packages, and contact data updated successfully.\n";
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, 'Failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
