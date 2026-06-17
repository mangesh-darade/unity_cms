<?php

/**
 * Apply lab owner, doctors, and equipment content to the database.
 */
function cmsApplyTeamEquipmentSeed(PDO $db): void
{
    $ownerImg = 'images/team/akshay-rakh-owner.png';
    $shubhamImg = 'images/team/dr-shubham-shirke.jpg';
    $akashImg = 'images/team/dr-akash-trimbake.jpg';
    $leishmanImg = 'images/lab/leishman-stain-process.png';

    $upsertTeam = static function (PDO $db, string $title, string $subtitle, string $content, string $imagePath, string $icon, int $sequence, int $active = 1): void {
        $row = $db->prepare('SELECT id FROM cms_page_blocks WHERE page_slug = ? AND block_type = ? AND title = ?');
        $row->execute(['about', 'team', $title]);
        $id = $row->fetchColumn();

        if ($id) {
            $db->prepare('UPDATE cms_page_blocks SET subtitle = ?, content = ?, image_path = ?, icon = ?, sequence = ?, is_active = ? WHERE id = ?')
                ->execute([$subtitle, $content, $imagePath, $icon, $sequence, $active, $id]);
        } else {
            $db->prepare('INSERT INTO cms_page_blocks (page_slug, block_type, title, subtitle, content, image_path, icon, sequence, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)')
                ->execute(['about', 'team', $title, $subtitle, $content, $imagePath, $icon, $sequence, $active]);
        }
    };

    $upsertEquipment = static function (PDO $db, string $title, string $image, string $description, int $sequence): void {
        $row = $db->prepare('SELECT id FROM cms_equipment WHERE title = ?');
        $row->execute([$title]);
        $id = $row->fetchColumn();

        if ($id) {
            $db->prepare('UPDATE cms_equipment SET image_path = ?, description = ?, sequence = ? WHERE id = ?')
                ->execute([$image, $description, $sequence, $id]);
        } else {
            $db->prepare('INSERT INTO cms_equipment (title, image_path, description, sequence) VALUES (?, ?, ?, ?)')
                ->execute([$title, $image, $description, $sequence]);
        }
    };

    $db->exec("UPDATE cms_page_blocks SET is_active = 0 WHERE page_slug = 'about' AND block_type = 'team' AND title IN ('Dr. Sunita Verma', 'Dr. Raman Gupta', 'Akshay Sanjay Rakh')");

    $ownerContent = "Akshay Sanjay Rakh is the founder and lab in-charge of Unity Clinical Laboratory. He holds an Advanced Diploma in Medical Laboratory Technology (ADMLT, MSBTE) with First Class Distinction.\n\n"
        . "He personally oversees sample processing, biochemistry and hematology analyzer operations, staining workflows, and quality checks — ensuring every patient receives accurate, reliable reports.";

    $db->prepare("UPDATE cms_page_blocks SET
        title = 'Akshay Sanjay Rakh',
        subtitle = 'Founder & Laboratory In-Charge',
        content = ?,
        image_path = ?
        WHERE page_slug = 'about' AND block_type = 'intro'")->execute([$ownerContent, $ownerImg]);

    $db->prepare("UPDATE cms_page_blocks SET
        title = 'Medical Leadership',
        subtitle = 'Our Consulting Doctors',
        content = 'Experienced physicians who support clinical correlation, report review, and patient guidance alongside our laboratory team.'
        WHERE page_slug = 'about' AND block_type = 'header' AND sequence >= 9")->execute();

    $upsertTeam(
        $db,
        'Dr. Shubham Shirke',
        'Medical Consultant',
        'Dr. Shubham Shirke provides medical consultation and clinical guidance for diagnostic reports, preventive health screening, and patient follow-up at Unity Clinical Laboratory.',
        $shubhamImg,
        'SS',
        10
    );

    $upsertTeam(
        $db,
        'Dr. Akash Trimbake',
        'Medical Consultant',
        'Dr. Akash Trimbake supports clinical interpretation of laboratory findings and assists patients with understanding their pathology reports and next steps in care.',
        $akashImg,
        'AT',
        11
    );

    $equip = [
        [
            'Sysmex XQ-320 Hematology Analyzer',
            'images/akshay_equip_sysmex_xq320.jpg',
            "Industry-leading hematology system for Complete Blood Count (CBC) and cellular analysis.\n"
            . "• Automated 5-part differential WBC counting\n"
            . "• SecureCore Technology for accurate RBC, platelet & WBC results\n"
            . "• High-throughput workflow for routine and urgent samples\n"
            . "• Trusted globally in clinical pathology laboratories",
            1,
        ],
        [
            'Orbit Smart-7 Biochemistry Analyzer',
            'images/akshay_equip_orbit_smart7.jpg',
            "Fully automated biochemistry platform for clinical chemistry and routine panels.\n"
            . "• Clinical chemistry, immunoturbidimetric assays, electrolytes & coagulation tests\n"
            . "• Photometric method with 6 standard filters (340, 405, 510, 545, 578, 620 nm)\n"
            . "• 12V/20W tungsten halogen lamp light source\n"
            . "• Stores up to 25,000 test results with reaction curves\n"
            . "• Built-in dry block incubator for sample preparation",
            2,
        ],
        [
            'REMI Laboratory Centrifuge',
            'images/akshay_equip_remi_centrifuge.jpg',
            "Remi Elektrotechnik centrifuge — industry standard for sample separation in pathology labs.\n"
            . "• Separates blood into serum/plasma for biochemistry testing\n"
            . "• Essential for urine, biochemistry & clinical sample processing\n"
            . "• Reliable performance for high-volume daily workloads\n"
            . "• Used in medical, pathology & research laboratories across India",
            3,
        ],
        [
            'Labomed Compound Microscope',
            'images/akshay_equip_labomed_microscope.jpg',
            "Precision Labomed microscope for clinical, biological & pathological microscopy.\n"
            . "• Superior optics for peripheral blood smear examination\n"
            . "• Anti-fungal treated lenses for long-term lab use\n"
            . "• Ergonomic design for extended diagnostic sessions\n"
            . "• Trusted in pathology, dentistry & ophthalmology applications",
            4,
        ],
        [
            'Peripheral Smear — Leishman Staining',
            $leishmanImg,
            "Classic Romanowsky-type Leishman stain for blood smear morphology.\n"
            . "• Visualizes WBC differentials, RBC morphology & malaria parasites\n"
            . "• Alcoholic mixture of methylene blue and eosin dyes\n"
            . "• Standard protocol for peripheral blood film examination\n"
            . "• Performed under controlled staining & quality checks",
            5,
        ],
    ];

    foreach ($equip as [$title, $img, $desc, $seq]) {
        $upsertEquipment($db, $title, $img, $desc, $seq);
    }

    $galCheck = $db->prepare('SELECT id FROM cms_gallery WHERE image_path = ?');
    $galCheck->execute([$ownerImg]);
    if (!$galCheck->fetchColumn()) {
        $db->prepare('INSERT INTO cms_gallery (title, image_path, description, sequence) VALUES (?, ?, ?, ?)')
            ->execute(['Lab In-Charge — Biochemistry Section', $ownerImg, 'Our founder overseeing biochemistry testing with calibrated pipettes and quality-controlled workflows.', 15]);
    }

    $galCheck->execute([$leishmanImg]);
    if (!$galCheck->fetchColumn()) {
        $db->prepare('INSERT INTO cms_gallery (title, image_path, description, sequence) VALUES (?, ?, ?, ?)')
            ->execute(['Leishman Stain — Blood Smear Processing', $leishmanImg, 'Peripheral blood smears stained with Leishman dye for cellular morphology and parasite screening.', 16]);
    }

    $db->prepare("UPDATE cms_pages SET
        content_description = 'Meet Akshay Sanjay Rakh, our consulting doctors, and the advanced analyzers behind Unity Clinical Laboratory.',
        meta_description = 'Unity Clinical Laboratory team — Akshay Sanjay Rakh (ADMLT), Dr. Shubham Shirke, Dr. Akash Trimbake. Sysmex, Orbit, Remi & Labomed equipment in Maharashtra.'
        WHERE slug = 'about'")->execute();
}
