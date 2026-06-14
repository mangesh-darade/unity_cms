<?php
/**
 * Rebuild cms_gallery with regenerated web images and remove stock placeholders.
 */
require_once __DIR__ . '/../includes/db.php';

$galleryItems = [
    [1, 'Health Test Rate Card', 'images/gallery/web/rate-card.jpg', 'Official Unity Clinical Laboratory rate card with hematology, biochemistry, serology and special test pricing.', 'Documents'],
    [2, 'Laboratory Services Signboard', 'images/gallery/web/signboard.jpg', 'Marathi and English signage listing available pathology tests and home collection contact.', 'Facility'],
    [3, 'Professional Blood Collection', 'images/gallery/web/blood-collection.jpg', 'Safe, hygienic blood sample collection by trained phlebotomist at Unity Lab.', 'Phlebotomy'],
    [4, 'Patient Sample Collection Desk', 'images/gallery/web/collection-desk.jpg', 'Comfortable in-clinic blood draw station with home collection service available.', 'Phlebotomy'],
    [5, 'Sample Processing Station', 'images/gallery/web/sample-processing.jpg', 'Organized vacutainer racks and micropipettes for accurate sample handling.', 'Laboratory'],
    [6, 'Digital Report Analysis', 'images/gallery/web/report-workstation.jpg', 'Lab technician reviewing and authorizing diagnostic reports on LIS workstation.', 'Laboratory'],
    [7, 'Serology & Rapid Test Reagents', 'images/gallery/web/serology-reagents.jpg', 'Quality serology kits including HIV, HBsAg, HCV, Syphilis and dengue rapid tests.', 'Serology'],
    [8, 'Stem Cell Education Display', 'images/gallery/web/stem-cell-display.jpg', 'Educational display explaining stem cell differentiation at Unity Clinical Laboratory.', 'Education'],
    [9, 'Staff Qualification Certificate', 'images/gallery/web/staff-certificate.jpg', 'Advanced Diploma in Medical Laboratory Technology — First Class (MSBTE Winter 2024).', 'Team'],
    [10, 'Sysmex XQ-320 Hematology Analyzer', 'images/gallery/web/hematology-analyzer.jpg', 'Automated hematology analyzer with SecureCore Technology for accurate CBC counts.', 'Equipment'],
    [11, 'Orbit Smart-7 Biochemistry Analyzer', 'images/gallery/web/biochemistry-analyzer.jpg', 'Fully automated biochemistry analyzer for glucose, liver, kidney and lipid panels.', 'Equipment'],
    [12, 'REMI Laboratory Centrifuge', 'images/gallery/web/centrifuge.jpg', 'High-speed centrifuge for proper serum and plasma separation before analysis.', 'Equipment'],
    [13, 'Labomed Compound Microscope', 'images/gallery/web/microscope.jpg', 'Professional microscope for peripheral smear, urine sediment and microscopy studies.', 'Equipment'],
];

$db->beginTransaction();
try {
    $db->exec('DELETE FROM cms_gallery');

    $stmt = $db->prepare('INSERT INTO cms_gallery (title, image_path, description, sequence) VALUES (?, ?, ?, ?)');
    foreach ($galleryItems as [$seq, $title, $path, $desc, $category]) {
        if (!is_file(__DIR__ . '/../' . $path)) {
            throw new RuntimeException("Missing regenerated image: $path");
        }
        $stmt->execute([$title, $path, $desc, $seq]);
    }

    // Gallery page + section copy
    $db->prepare("UPDATE cms_pages SET
        page_heading = 'Laboratory Gallery',
        breadcrumb_label = 'Laboratory Gallery',
        content_tag = 'Inside Our Lab',
        content_title = 'Laboratory Gallery',
        content_description = 'Take a look at our clean diagnostic environment and sample processing areas.'
        WHERE slug = 'gallery'")->execute();

    $db->prepare("UPDATE cms_sections SET
        section_tag = 'Inside Our Lab',
        section_heading = 'Laboratory Gallery',
        section_description = 'Take a look at our clean diagnostic environment and sample processing areas.'
        WHERE section_code = 'gallery'")->execute();

    $db->commit();
    echo 'Gallery rebuilt with ' . count($galleryItems) . " items.\n";
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
}
