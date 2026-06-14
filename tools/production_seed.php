<?php
/**
 * Production seed — fill CMS from Unity Clinical Laboratory image data.
 * Safe to re-run (upserts / idempotent updates).
 */
require_once __DIR__ . '/../includes/db.php';

$root = dirname(__DIR__);
$images = $root . '/images';

function upsert(PDO $db, string $key, string $value): void
{
    $db->prepare('INSERT INTO cms_settings (key, value) VALUES (?, ?) ON CONFLICT(key) DO UPDATE SET value = excluded.value')
        ->execute([$key, $value]);
}

function copyIfMissing(string $src, string $dest): void
{
    if (!is_file($src)) {
        return;
    }
    if (!is_file($dest)) {
        copy($src, $dest);
    }
}

// ── Create missing image aliases from real lab photos ──
$aliases = [
    'hero-lab.jpg' => 'gallery/web/blood-collection.jpg',
    'og-image.jpg' => 'akshay_ucl_logo.jpg',
    'logo.png' => 'akshay_ucl_logo.jpg',
    'eq-biochem.jpg' => 'akshay_equip_orbit_smart7.jpg',
    'eq-hema.jpg' => 'akshay_equip_sysmex_xq320.jpg',
    'gallery-1.jpg' => 'gallery/web/signboard.jpg',
    'gallery-2.jpg' => 'gallery/web/sample-processing.jpg',
    'gallery-3.jpg' => 'gallery/web/serology-reagents.jpg',
    'gallery-4.jpg' => 'gallery/web/hematology-analyzer.jpg',
];
foreach ($aliases as $destName => $srcRel) {
    copyIfMissing("$images/$srcRel", "$images/$destName");
}

$db->beginTransaction();

try {
    // ── Core branding (signboard + logo) ──
    $settings = [
        'site_name' => 'Unity Clinical Laboratory',
        'logo_text' => 'Unity Clinical Laboratory',
        'logo_type' => 'image',
        'logo_image' => 'images/akshay_ucl_logo.jpg',
        'footer_about' => 'Unity Clinical Laboratory — Your Health, Our Responsibility. Accurate reports, reliable service, and modern machine-based testing in Maharashtra.',
        'hero_tagline' => 'NABL Aligned Laboratory & Diagnostic Center',
        'hero_headline' => 'Accurate Diagnostics. Trusted Results.',
        'hero_subheadline' => 'Advanced Blood, Urine and Health Diagnostic Testing with Fast & Reliable Reports. Correct testing is the first step to correct treatment.',
        'hero_bg_image' => 'images/gallery/web/blood-collection.jpg',
        'og_image' => 'images/og-image.jpg',
        'support_phone' => '+91 98507 00268',
        'whatsapp_number' => '919850700268',
        'support_email' => 'info@unityclinicallab.com',
        'support_address' => "Unity Clinical Laboratory\nMaharashtra, India\nPhone: +91 98507 00268",
        'top_bar_location' => 'Maharashtra, India',
        'mail_from_email' => 'info@unityclinicallab.com',
        'mail_from_name' => 'Unity Clinical Laboratory',
        'notify_admin_email' => 'info@unityclinicallab.com',
        'floating_whatsapp_enabled' => '1',
        'footer_badge_1' => 'NABL ALIGNED',
        'footer_badge_2' => 'ESTD 2026',
        'schema_street' => 'Unity Clinical Laboratory',
        'schema_city' => 'Maharashtra',
        'schema_state' => 'Maharashtra',
        'schema_postal' => '',
        'schema_lat' => '',
        'schema_lng' => '',
        'schema_alternate_name' => 'UCL',
        'seo_default_keywords' => 'Unity Clinical Laboratory, pathology lab, blood test, CBC, thyroid, diabetes, LFT, KFT, lipid profile, home collection, Maharashtra, diagnostic center',
        'seo_default_description' => 'Unity Clinical Laboratory offers 90+ pathology tests including CBC, thyroid, diabetes, liver, kidney and health packages. Home sample collection across Maharashtra. Call +91 98507 00268.',
        'seo_home_description' => 'Book blood tests, health packages and home sample collection at Unity Clinical Laboratory, Maharashtra. Accurate reports with Sysmex & Orbit analyzers.',
        'maps_embed_url' => '',
        'hero_stat_1_value' => '90+',
        'hero_stat_1_label' => 'Pathology Tests',
        'hero_stat_2_value' => '2026',
        'hero_stat_2_label' => 'Established',
        'hero_stat_3_value' => '99.9%',
        'hero_stat_3_label' => 'Accuracy Focus',
        'hero_stat_4_value' => '6–12h',
        'hero_stat_4_label' => 'Report Turnaround',
        'hero_float_1_icon' => 'fa-solid fa-microscope',
        'hero_float_1_title' => 'Sysmex Hematology',
        'hero_float_1_desc' => 'XQ-320 automated CBC analyzer',
        'hero_float_2_icon' => 'fa-solid fa-vials',
        'hero_float_2_title' => 'Orbit Biochemistry',
        'hero_float_2_desc' => 'Smart-7 advance chemistry system',
        'hero_trust_3_text' => 'Reports in 6–12 hrs',
        'hero_trust_4_text' => 'Home Collection Available',
    ];
    foreach ($settings as $k => $v) {
        upsert($db, $k, $v);
    }

    // ── Homepage sections copy (signboard services) ──
    $db->exec("UPDATE cms_sections SET
        section_description = 'Accurate reports, reliable service, and testing with modern Sysmex, Orbit and Labomed equipment.'
        WHERE section_code = 'why_choose_us'");
    $db->exec("UPDATE cms_sections SET
        section_description = 'Blood, urine, thyroid, diabetes, liver, kidney, lipid, vitamin and special tests — priced per our official rate card.'
        WHERE section_code = 'services'");

    // ── Why choose us — from signboard checklist ──
    $whyItems = [
        [1, 'Accurate Reports', 'Double-checked results with strict quality control on every sample.', 'fa-solid fa-bullseye'],
        [2, 'Reliable Service', 'Trusted pathology service with professional sample collection and reporting.', 'fa-solid fa-shield-heart'],
        [3, 'Modern Equipment', 'Sysmex hematology, Orbit biochemistry, REMI centrifuge and Labomed microscopy.', 'fa-solid fa-microscope'],
        [4, 'Fast Reporting', 'Most routine reports available within 6 to 12 hours of sample collection.', 'fa-solid fa-bolt'],
        [5, 'Home Collection', 'Book home sample collection from 6:00 AM across Maharashtra.', 'fa-solid fa-house-medical'],
        [6, 'Affordable Pricing', 'Transparent rate card pricing — CBC from ₹200, packages from ₹530.', 'fa-solid fa-tags'],
    ];
    $wStmt = $db->prepare('UPDATE cms_section_items SET title = ?, description = ?, icon = ? WHERE section_code = ? AND sequence = ?');
    foreach ($whyItems as [$seq, $title, $desc, $icon]) {
        $wStmt->execute([$title, $desc, $icon, 'why_choose_us', $seq]);
    }

    // ── Equipment — keep only verified lab machines ──
    $db->exec("DELETE FROM cms_equipment WHERE image_path IN ('images/eq-biochem.jpg','images/eq-hema.jpg','images/gallery-3.jpg')");

    $equip = [
        [1, 'Sysmex XQ-320 Hematology Analyzer', 'images/akshay_equip_sysmex_xq320.jpg', 'Automated hematology with SecureCore Technology for accurate CBC and differential counts.'],
        [2, 'Orbit Smart-7 Biochemistry Analyzer', 'images/akshay_equip_orbit_smart7.jpg', 'Fully automated biochemistry for glucose, liver, kidney, lipid and routine chemistry panels.'],
        [3, 'REMI Laboratory Centrifuge', 'images/akshay_equip_remi_centrifuge.jpg', 'High-speed centrifuge for serum and plasma separation before analysis.'],
        [4, 'Labomed Compound Microscope', 'images/akshay_equip_labomed_microscope.jpg', 'Professional microscopy for peripheral smear and urine sediment examination.'],
    ];
    $db->exec('DELETE FROM cms_equipment');
    $eStmt = $db->prepare('INSERT INTO cms_equipment (title, image_path, description, sequence) VALUES (?, ?, ?, ?)');
    foreach ($equip as [$seq, $title, $path, $desc]) {
        $eStmt->execute([$title, $path, $desc, $seq]);
    }

    // ── Add signboard-highlighted tests if missing ──
    $extraServices = [
        ['Vitamin D (25-Hydroxy)', 'Special Tests', 800, 'Vitamin D assessment for bone health and immunity.', 100],
        ['Vitamin B12', 'Special Tests', 750, 'Serum Vitamin B12 for anaemia and neurological screening.', 101],
        ['Iron Profile (TIBC)', 'Special Tests', 700, 'Iron, TIBC and transferrin saturation panel.', 102],
        ['Sputum Test', 'Microbiology', 300, 'Sputum routine and AFB smear examination.', 103],
    ];
    $sIns = $db->prepare('INSERT INTO cms_services (title, category, price, description, sample_type, prep_instructions, sequence) SELECT ?, ?, ?, ?, ?, ?, ? WHERE NOT EXISTS (SELECT 1 FROM cms_services WHERE title = ?)');
    foreach ($extraServices as [$title, $cat, $price, $desc, $seq]) {
        $sIns->execute([$title, $cat, $price, $desc, 'Blood', '', $seq, $title]);
    }

    // ── FAQs — remove Gurugram references ──
    $db->exec("UPDATE cms_faqs SET answer = REPLACE(answer, 'Gurugram and surrounding regions', 'Maharashtra and surrounding areas') WHERE answer LIKE '%Gurugram%'");
    $db->exec("UPDATE cms_faqs SET answer = REPLACE(answer, 'across Gurugram', 'across Maharashtra') WHERE answer LIKE '%Gurugram%'");

    // ── Testimonials — update location ──
    $db->exec("UPDATE cms_testimonials SET designation = REPLACE(designation, 'Gurugram', 'Maharashtra') WHERE designation LIKE '%Gurugram%'");

    // ── Blog images → real lab photos ──
    $blogImages = [
        'Understanding Your Complete Blood Count (CBC) Report' => 'images/gallery/web/hematology-analyzer.jpg',
        'Why Fasting is Mandatory for Blood Sugar & Lipid Profiles' => 'images/gallery/web/biochemistry-analyzer.jpg',
        'Thyroid Hormones: Roles in Weight & Metabolism' => 'images/gallery/web/rate-card.jpg',
        'Preventive Health Screenings: When Do You Need Them?' => 'images/gallery/web/signboard.jpg',
        'Understanding Diabetes: Differences in HbA1c and Sugar Tests' => 'images/gallery/web/sample-processing.jpg',
        'Kidney Safety: Key Clinical Markers to Monitor' => 'images/gallery/web/report-workstation.jpg',
    ];
    $bStmt = $db->prepare('UPDATE cms_blogs SET image_path = ?, author = ? WHERE title = ?');
    foreach ($blogImages as $title => $path) {
        $bStmt->execute([$path, 'Unity Clinical Laboratory', $title]);
    }

    // ── Contact page ──
    $db->prepare("UPDATE cms_pages SET
        meta_description = 'Contact Unity Clinical Laboratory, Maharashtra. Call +91 98507 00268 for appointments and home sample collection.',
        content_description = 'Call +91 98507 00268 or WhatsApp us for bookings, report queries, and home collection across Maharashtra.'
        WHERE slug = 'contact'")->execute();

    // ── About badges — only verified claims from signboard/logo ──
    $db->exec("UPDATE cms_page_blocks SET title = 'NABL Aligned', is_active = 1 WHERE page_slug = 'about' AND block_type = 'badge' AND sequence = 2");
    $db->exec("UPDATE cms_page_blocks SET title = 'ESTD 2026', is_active = 1 WHERE page_slug = 'about' AND block_type = 'badge' AND sequence = 3");
    $db->exec("UPDATE cms_page_blocks SET is_active = 0 WHERE page_slug = 'about' AND block_type = 'badge' AND title = 'ISO Certified'");

    // ── Full Body package — remove unverified doctor consultation ──
    $db->exec("UPDATE cms_packages SET features = REPLACE(features, CHAR(10) || 'Free Doctor Consultation online', '') WHERE id = 2");

    $db->commit();
    echo "Production seed completed.\n";
    echo "Services: " . $db->query('SELECT COUNT(*) FROM cms_services')->fetchColumn() . "\n";
    echo "Equipment: " . $db->query('SELECT COUNT(*) FROM cms_equipment')->fetchColumn() . "\n";
    echo "\nACTION REQUIRED: Change admin password at /admin/ (default: admin / admin_password_123)\n";
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, 'Failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
