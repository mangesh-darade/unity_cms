<?php
require_once __DIR__ . '/../includes/db.php';

$galleryOrder = [
    'images/akshay_gallery_rate_card.jpg',
    'images/akshay_gallery_signboard.jpg',
    'images/akshay_gallery_blood_collection.jpg',
    'images/akshay_gallery_collection_desk.jpg',
    'images/akshay_gallery_sample_processing.jpg',
    'images/akshay_gallery_report_workstation.jpg',
    'images/akshay_gallery_serology_reagents.jpg',
    'images/akshay_gallery_stem_cell.jpg',
    'images/akshay_gallery_staff_certificate.jpg',
    'images/gallery-1.jpg',
    'images/gallery-2.jpg',
    'images/gallery-3.jpg',
    'images/gallery-4.jpg',
];

$stmt = $db->prepare('UPDATE cms_gallery SET sequence = ? WHERE image_path = ?');
foreach ($galleryOrder as $i => $path) {
    $stmt->execute([$i + 1, $path]);
}

$equipOrder = [
    'images/akshay_equip_sysmex_xq320.jpg',
    'images/akshay_equip_orbit_smart7.jpg',
    'images/akshay_equip_remi_centrifuge.jpg',
    'images/akshay_equip_labomed_microscope.jpg',
    'images/eq-biochem.jpg',
    'images/eq-hema.jpg',
    'images/gallery-3.jpg',
];

$stmt2 = $db->prepare('UPDATE cms_equipment SET sequence = ? WHERE image_path = ?');
foreach ($equipOrder as $i => $path) {
    $stmt2->execute([$i + 1, $path]);
}

$db->prepare("INSERT INTO cms_settings (key, value) VALUES ('hero_bg_image', 'images/akshay_gallery_blood_collection.jpg') ON CONFLICT(key) DO UPDATE SET value = excluded.value")->execute();

echo "Reordered gallery and equipment; hero background updated.\n";
