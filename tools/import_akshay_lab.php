<?php
require_once __DIR__ . '/../includes/db.php';

$srcDir = __DIR__ . '/../images/akshay_lab';
if (!is_dir($srcDir)) {
    fwrite(STDERR, "Source folder missing: $srcDir\n");
    exit(1);
}

function copyLabImage(string $srcDir, string $sourceName, string $destName): string
{
    $src = $srcDir . DIRECTORY_SEPARATOR . $sourceName;
    $destRel = 'images/' . $destName;
    $dest = __DIR__ . '/../' . $destRel;
    if (!is_file($src)) {
        throw new RuntimeException("Missing source image: $sourceName");
    }
    if (!copy($src, $dest)) {
        throw new RuntimeException("Failed to copy $sourceName");
    }
    return $destRel;
}

function upsertSetting(PDO $db, string $key, string $value): void
{
    $stmt = $db->prepare('INSERT INTO cms_settings (key, value) VALUES (?, ?) ON CONFLICT(key) DO UPDATE SET value = excluded.value');
    $stmt->execute([$key, $value]);
}

function insertGallery(PDO $db, string $title, string $path, string $desc, int $seq): void
{
    $exists = $db->prepare('SELECT id FROM cms_gallery WHERE image_path = ? LIMIT 1');
    $exists->execute([$path]);
    if ($exists->fetchColumn()) {
        echo "skip gallery exists: $title\n";
        return;
    }
    $db->prepare('INSERT INTO cms_gallery (title, image_path, description, sequence) VALUES (?, ?, ?, ?)')
        ->execute([$title, $path, $desc, $seq]);
    echo "gallery: $title\n";
}

function insertEquipment(PDO $db, string $title, string $path, string $desc, int $seq): void
{
    $exists = $db->prepare('SELECT id FROM cms_equipment WHERE image_path = ? LIMIT 1');
    $exists->execute([$path]);
    if ($exists->fetchColumn()) {
        echo "skip equipment exists: $title\n";
        return;
    }
    $db->prepare('INSERT INTO cms_equipment (title, image_path, description, sequence) VALUES (?, ?, ?, ?)')
        ->execute([$title, $path, $desc, $seq]);
    echo "equipment: $title\n";
}

function insertService(PDO $db, string $title, string $category, int $price, string $desc, int $seq): void
{
    $exists = $db->prepare('SELECT id FROM cms_services WHERE title = ? AND category = ? LIMIT 1');
    $exists->execute([$title, $category]);
    if ($exists->fetchColumn()) {
        return;
    }
    $db->prepare('INSERT INTO cms_services (title, category, price, description, sample_type, prep_instructions, sequence) VALUES (?, ?, ?, ?, ?, ?, ?)')
        ->execute([$title, $category, $price, $desc, 'Blood', '', $seq]);
}

$db->beginTransaction();

try {
    // 1. Logo
    $logoPath = copyLabImage($srcDir, 'IMG-20260425-WA0007.jpg', 'akshay_ucl_logo.jpg');
    upsertSetting($db, 'logo_type', 'image');
    upsertSetting($db, 'logo_image', $logoPath);
    upsertSetting($db, 'header_logo_width', '240');
    upsertSetting($db, 'header_logo_height', '72');
    echo "settings: logo updated\n";

    // Contact from signage
    upsertSetting($db, 'support_phone', '+91 98507 00268');
    upsertSetting($db, 'whatsapp_number', '919850700268');
    upsertSetting($db, 'top_bar_location', 'Maharashtra, India');
    echo "settings: contact updated\n";

    // 2. Equipment (real lab machines)
    $equipment = [
        ['Sysmex XQ-320 Hematology Analyzer', 'IMG-20260614-WA0001.jpg', 'akshay_equip_sysmex_xq320.jpg', 'Automated hematology analyzer with SecureCore Technology for accurate CBC and differential counts.', 1],
        ['Orbit Smart-7 Advance Biochemistry Analyzer', 'IMG-20260614-WA0004.jpg', 'akshay_equip_orbit_smart7.jpg', 'Fully automated biochemistry analyzer for glucose, liver, kidney, lipid and routine chemistry panels.', 2],
        ['REMI Laboratory Centrifuge', 'IMG-20260614-WA0005.jpg', 'akshay_equip_remi_centrifuge.jpg', 'High-speed centrifuge for proper serum and plasma separation before analysis.', 3],
        ['Labomed Compound Microscope', 'IMG-20260614-WA0006.jpg', 'akshay_equip_labomed_microscope.jpg', 'Professional microscope for peripheral smear, urine sediment and manual microscopy studies.', 4],
    ];
    foreach ($equipment as [$title, $src, $dest, $desc, $seq]) {
        $path = copyLabImage($srcDir, $src, $dest);
        insertEquipment($db, $title, $path, $desc, $seq);
    }

    // 3. Gallery photos
    $gallery = [
        ['Health Test Rate Card', 'IMG-20260227-WA0001.jpg', 'akshay_gallery_rate_card.jpg', 'Official Unity Clinical Laboratory rate card covering hematology, biochemistry, serology and special tests.', 1],
        ['Laboratory Services Signboard', 'IMG-20260614-WA0000.jpg', 'akshay_gallery_signboard.jpg', 'Marathi and English signage listing available pathology tests and contact number 9850700268.', 2],
        ['Stem Cell Education Display', 'IMG-20260614-WA0002.jpg', 'akshay_gallery_stem_cell.jpg', 'Educational display explaining stem cell differentiation at Unity Clinical Laboratory.', 3],
        ['Serology & Rapid Test Reagents', 'IMG-20260614-WA0003.jpg', 'akshay_gallery_serology_reagents.jpg', 'Quality serology kits including HIV, HBsAg, HCV, Syphilis and dengue rapid tests.', 4],
        ['Professional Blood Collection', 'IMG-20260614-WA0008.jpg', 'akshay_gallery_blood_collection.jpg', 'Safe and hygienic blood sample collection by trained phlebotomist at Unity Lab.', 5],
        ['Digital Report Analysis', 'IMG-20260614-WA0009.jpg', 'akshay_gallery_report_workstation.jpg', 'Lab technician reviewing and authorizing diagnostic reports on advanced LIS workstation.', 6],
        ['Patient Sample Collection Desk', 'IMG-20260614-WA0010.jpg', 'akshay_gallery_collection_desk.jpg', 'Comfortable in-clinic blood draw station with home collection service available.', 7],
        ['Sample Processing Station', 'IMG-20260614-WA0011.jpg', 'akshay_gallery_sample_processing.jpg', 'Organized vacutainer racks and micropipettes for accurate sample handling.', 8],
        ['Staff Qualification Certificate', 'IMG-20260614-WA0012.jpg', 'akshay_gallery_staff_certificate.jpg', 'Advanced Diploma in Medical Laboratory Technology — First Class (MSBTE Winter 2024).', 9],
    ];
    foreach ($gallery as [$title, $src, $dest, $desc, $seq]) {
        $path = copyLabImage($srcDir, $src, $dest);
        insertGallery($db, $title, $path, $desc, $seq);
    }

    // 4. Rate card tests -> cms_services
    $rateCard = [
        ['Hematology', [
            ['CBC', 200, 'Complete Blood Count'],
            ['Hb%', 100, 'Hemoglobin estimation'],
            ['ESR', 100, 'Erythrocyte Sedimentation Rate'],
            ['Platelet Count', 100, 'Platelet count test'],
            ['Blood Group', 50, 'ABO and Rh blood grouping'],
            ['PBS, MP', 100, 'Peripheral blood smear for malaria parasite'],
            ['Rapid MP', 350, 'Rapid malaria antigen test'],
            ['B.T / C.T', 100, 'Bleeding and clotting time'],
            ['PT INR', 200, 'Prothrombin time / INR'],
            ['APTT / PTTK', 500, 'Activated partial thromboplastin time'],
            ['Reticulocyte Count', 250, 'Reticulocyte count'],
            ['HB Electrophoresis', 1200, 'Hemoglobin electrophoresis'],
            ['G6PD', 600, 'Glucose-6-phosphate dehydrogenase test'],
        ]],
        ['Serology', [
            ['HIV I & II', 350, 'HIV screening test'],
            ['HBs Ag', 200, 'Hepatitis B surface antigen'],
            ['HCV (Rapid)', 600, 'Hepatitis C rapid antibody test'],
            ['VDRL', 150, 'Syphilis screening test'],
            ['Widal Slide Method', 150, 'Typhoid Widal slide test'],
            ['Widal Tube Method', 250, 'Typhoid Widal tube test'],
            ['Typhoid IgM & IgG', 500, 'Typhoid antibody test'],
            ['RA Factor', 500, 'Rheumatoid factor'],
            ['A.S.O.', 500, 'Anti-streptolysin O'],
            ['CRP', 500, 'C-reactive protein'],
            ['Dengue NS1', 700, 'Dengue NS1 antigen'],
            ['Dengue IgG & IgM', 800, 'Dengue antibody panel'],
            ['Dengue Combo NS1, IgG IgM', 1200, 'Complete dengue panel'],
            ['Tuberculin Test', 150, 'Mantoux tuberculin test'],
            ['Chikungunya IgG & IgM', 850, 'Chikungunya antibody test'],
        ]],
        ['Biochemistry', [
            ['BSL F / PP', 100, 'Fasting / post-prandial blood sugar'],
            ['BSL Random', 50, 'Random blood sugar'],
            ['HBA1C', 500, 'Glycated hemoglobin for diabetes monitoring'],
            ['S. Creatinine', 180, 'Serum creatinine for kidney function'],
            ['S. Urea', 180, 'Serum urea for kidney function'],
            ['LFT', 600, 'Liver function test panel'],
            ['RFT', 800, 'Renal function test panel'],
            ['Lipid Profile', 600, 'Cholesterol and lipid panel'],
            ['Electrolytes', 400, 'Sodium, potassium and chloride panel'],
            ['S. Bilirubin', 200, 'Serum bilirubin'],
            ['S. G. P. T', 180, 'SGPT / ALT'],
            ['S. G. O. T', 180, 'SGOT / AST'],
            ['Alkaline Phosphatase', 180, 'Alkaline phosphatase'],
            ['S. Protein', 300, 'Total serum protein'],
            ['S. Albumin', 180, 'Serum albumin'],
            ['S. Cholesterol', 250, 'Total cholesterol'],
            ['S. Triglycerides', 250, 'Serum triglycerides'],
            ['Direct HDL', 300, 'HDL cholesterol'],
            ['S. Uric Acid', 200, 'Serum uric acid'],
            ['S. Calcium', 150, 'Serum calcium'],
            ['CPK - MB', 500, 'Creatine kinase-MB'],
            ['CPK - Total', 500, 'Total creatine kinase'],
            ['Amylase', 400, 'Serum amylase'],
            ['Lipase', 400, 'Serum lipase'],
            ['LDH', 400, 'Lactate dehydrogenase'],
        ]],
        ['Microbiology', [
            ['AFB (Sputum)', 300, 'Acid-fast bacilli smear'],
            ['Gram (Sputum)', 500, 'Gram stain of sputum'],
            ['Urine C/S', 700, 'Urine culture and sensitivity'],
            ['Pus C/S', 700, 'Pus culture and sensitivity'],
            ['Sputum C/S', 1500, 'Sputum culture and sensitivity'],
            ['Blood C/S', 1500, 'Blood culture and sensitivity'],
            ['Stool C/S', 950, 'Stool culture and sensitivity'],
            ['CSF C/S', 1500, 'CSF culture and sensitivity'],
        ]],
        ['Special Tests', [
            ['T3 T4 TSH', 500, 'Thyroid profile'],
            ['TSH', 250, 'Thyroid stimulating hormone'],
            ['FT3 FT4 TSH', 650, 'Free thyroid hormone panel'],
            ['FSH', 500, 'Follicle stimulating hormone'],
            ['LH', 500, 'Luteinizing hormone'],
            ['Prolactin', 500, 'Prolactin hormone'],
            ['FSH, LH, Prolactin', 1200, 'Reproductive hormone panel'],
            ['Beta HCG', 750, 'Pregnancy hormone test'],
            ['CA.125', 800, 'Ovarian cancer marker'],
            ['PSA', 800, 'Prostate specific antigen'],
            ['ANA', 1200, 'Antinuclear antibody'],
            ['TORCH 8 Para', 2500, 'TORCH infection panel'],
            ['S. Ferritin', 800, 'Serum ferritin / iron stores'],
            ['IRDN TIBC', 700, 'Iron and TIBC panel'],
            ['S. Vit B12', 750, 'Vitamin B12'],
            ['Ant/ CCP', 1500, 'Anti-CCP for rheumatoid arthritis'],
            ['D - Dimer', 1300, 'D-dimer clotting marker'],
            ['Homocysteine', 1200, 'Homocysteine cardiovascular marker'],
            ['Sr Cortisol', 1000, 'Serum cortisol'],
            ['E2', 800, 'Estradiol hormone'],
            ['Progesterone', 600, 'Progesterone hormone'],
            ['Folic Acid', 800, 'Serum folic acid'],
        ]],
        ['Clinical Pathology', [
            ['Urine (R)', 100, 'Urine routine examination'],
            ['Stool (R)', 150, 'Stool routine examination'],
            ['Occult Blood', 100, 'Stool occult blood test'],
            ['Semen Analysis', 250, 'Semen analysis'],
            ['Pregnancy Test', 150, 'Urine pregnancy test'],
            ['24 hrs. Urinary Protein', 700, '24-hour urinary protein'],
            ['Urine Microalbumin', 650, 'Urine microalbumin for kidney screening'],
        ]],
    ];

    $serviceSeq = (int) $db->query('SELECT COALESCE(MAX(sequence), 0) FROM cms_services')->fetchColumn();
    $added = 0;
    foreach ($rateCard as [$category, $tests]) {
        foreach ($tests as [$title, $price, $desc]) {
            $serviceSeq++;
            insertService($db, $title, $category, $price, $desc, $serviceSeq);
            $added++;
        }
    }
    echo "services: attempted $added rate-card entries\n";

    $db->commit();
    echo "\nImport completed successfully.\n";
    echo 'Gallery total: ' . $db->query('SELECT COUNT(*) FROM cms_gallery')->fetchColumn() . PHP_EOL;
    echo 'Equipment total: ' . $db->query('SELECT COUNT(*) FROM cms_equipment')->fetchColumn() . PHP_EOL;
    echo 'Services total: ' . $db->query('SELECT COUNT(*) FROM cms_services')->fetchColumn() . PHP_EOL;
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, 'Import failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
