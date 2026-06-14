<?php
// Prevent direct access to includes folder
if (basename($_SERVER['PHP_SELF']) == 'db.php') {
    header("HTTP/1.1 403 Forbidden");
    exit("Access denied");
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_helpers.php';

// Set timezone
date_default_timezone_set('Asia/Kolkata');

$is_new_db = false;

try {
    $db = dbConnect();
    $SQL_ID = dbSqlId($db);
    $is_new_db = isFreshDatabase($db);
    
    // 1. Create Core Tables (Patients, Reports, Bookings, Inquiries, Users)
    $db->exec("CREATE TABLE IF NOT EXISTS patients (
        {$SQL_ID},
        patient_id TEXT UNIQUE NOT NULL,
        name TEXT NOT NULL,
        email TEXT,
        mobile TEXT NOT NULL,
        gender TEXT,
        age INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS reports (
        {$SQL_ID},
        patient_id TEXT NOT NULL,
        test_name TEXT NOT NULL,
        file_path TEXT NOT NULL,
        uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS bookings (
        {$SQL_ID},
        name TEXT NOT NULL,
        mobile TEXT NOT NULL,
        email TEXT,
        address TEXT NOT NULL,
        preferred_date TEXT NOT NULL,
        test_type TEXT NOT NULL,
        status TEXT DEFAULT 'Pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS inquiries (
        {$SQL_ID},
        name TEXT NOT NULL,
        email TEXT,
        mobile TEXT NOT NULL,
        subject TEXT,
        message TEXT NOT NULL,
        status TEXT DEFAULT 'New',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS users (
        {$SQL_ID},
        username TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. Create CMS Tables
    $settingsKeyCol = dbDriver($db) === 'mysql' ? '`key`' : 'key';
    $db->exec("CREATE TABLE IF NOT EXISTS cms_settings (
        {$settingsKeyCol} TEXT PRIMARY KEY,
        value TEXT NOT NULL
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS cms_sections (
        {$SQL_ID},
        section_code TEXT UNIQUE NOT NULL,
        section_title TEXT NOT NULL,
        is_active INTEGER DEFAULT 1,
        sequence INTEGER NOT NULL
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS cms_services (
        {$SQL_ID},
        title TEXT NOT NULL,
        category TEXT NOT NULL,
        price REAL NOT NULL,
        description TEXT,
        sample_type TEXT,
        prep_instructions TEXT,
        sequence INTEGER DEFAULT 0
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS cms_packages (
        {$SQL_ID},
        name TEXT NOT NULL,
        price REAL NOT NULL,
        description TEXT,
        features TEXT,
        is_featured INTEGER DEFAULT 0,
        sequence INTEGER DEFAULT 0
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS cms_equipment (
        {$SQL_ID},
        title TEXT NOT NULL,
        image_path TEXT NOT NULL,
        description TEXT,
        sequence INTEGER DEFAULT 0
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS cms_gallery (
        {$SQL_ID},
        title TEXT NOT NULL,
        image_path TEXT NOT NULL,
        description TEXT,
        sequence INTEGER DEFAULT 0
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS cms_testimonials (
        {$SQL_ID},
        text TEXT NOT NULL,
        author TEXT NOT NULL,
        designation TEXT,
        sequence INTEGER DEFAULT 0
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS cms_faqs (
        {$SQL_ID},
        question TEXT NOT NULL,
        answer TEXT NOT NULL,
        sequence INTEGER DEFAULT 0
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS cms_blogs (
        {$SQL_ID},
        title TEXT NOT NULL,
        image_path TEXT NOT NULL,
        author TEXT NOT NULL,
        summary TEXT,
        content TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS cms_menu (
        {$SQL_ID},
        title TEXT NOT NULL,
        url TEXT NOT NULL,
        sequence INTEGER NOT NULL,
        is_active INTEGER DEFAULT 1,
        is_cta INTEGER DEFAULT 0
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS cms_pages (
        {$SQL_ID},
        slug TEXT UNIQUE NOT NULL,
        filename TEXT NOT NULL,
        nav_key TEXT,
        page_heading TEXT NOT NULL,
        breadcrumb_label TEXT NOT NULL,
        meta_title TEXT,
        meta_description TEXT,
        content_tag TEXT,
        content_title TEXT,
        content_description TEXT,
        is_active INTEGER DEFAULT 1,
        sequence INTEGER DEFAULT 0
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS cms_page_blocks (
        {$SQL_ID},
        page_slug TEXT NOT NULL,
        block_type TEXT NOT NULL,
        title TEXT,
        subtitle TEXT,
        content TEXT,
        image_path TEXT,
        icon TEXT,
        sequence INTEGER DEFAULT 0,
        is_active INTEGER DEFAULT 1
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS cms_section_items (
        {$SQL_ID},
        section_code TEXT NOT NULL,
        title TEXT NOT NULL,
        subtitle TEXT,
        description TEXT,
        icon TEXT,
        image_path TEXT,
        sequence INTEGER DEFAULT 0,
        is_active INTEGER DEFAULT 1
    )");

    ensureColumn($db, 'cms_sections', 'section_tag', 'TEXT');
    ensureColumn($db, 'cms_sections', 'section_heading', 'TEXT');
    ensureColumn($db, 'cms_sections', 'section_description', 'TEXT');
    ensureColumn($db, 'cms_sections', 'section_type', "TEXT DEFAULT 'builtin'");
    ensureColumn($db, 'cms_sections', 'section_body', 'TEXT');
    ensureColumn($db, 'cms_pages', 'meta_keywords', 'TEXT');
    ensureColumn($db, 'cms_pages', 'og_image', 'TEXT');
    ensureColumn($db, 'cms_pages', 'page_body', 'TEXT');
    ensureColumn($db, 'cms_pages', 'robots_noindex', dbDriver($db) === 'mysql' ? 'TINYINT DEFAULT 0' : 'INTEGER DEFAULT 0');
    ensureColumn($db, 'cms_pages', 'sitemap_changefreq', "TEXT DEFAULT 'monthly'");
    ensureColumn($db, 'cms_pages', 'sitemap_priority', "TEXT DEFAULT '0.5'");
    ensureColumn($db, 'cms_pages', 'include_in_sitemap', dbDriver($db) === 'mysql' ? 'TINYINT DEFAULT 1' : 'INTEGER DEFAULT 1');

    cmsSeedPagesAndBlocks($db, $SQL_ID);
    $section_meta_updates = [
        'hero' => ['', '', ''],
        'why_choose_us' => ['Our Strengths', 'Why Patients & Doctors Trust Unity Lab', 'We offer state-of-the-art diagnostics with a focus on precision, convenience, and affordability.'],
        'services' => ['Specializations', 'Our Pathology & Diagnostic Services', 'Comprehensive laboratory services ranging from basic blood checks to advanced organic biochemistry panels.'],
        'packages' => ['Wellness Packages', 'Popular Health Checkup Packages', 'Affordable preventive health screening bundles designed for individuals, seniors, and families.'],
        'download_report' => ['Patient Portal', 'Download Lab Report Online', 'Enter your Patient ID and Mobile Number below to download your clinical laboratory report PDF securely.'],
        'home_collection' => ['Convenient Diagnostics', 'Book a Home Sample Collection', 'Avoid long queues. Our certified phlebotomists visit your home or workplace safely.'],
        'equipment' => ['Advanced Technology', 'Our Laboratory Equipment', 'Fully automated analyzers ensuring precision, speed, and reproducibility.'],
        'gallery' => ['Inside Our Lab', 'Laboratory Gallery', 'Take a look at our clean diagnostic environment and sample processing areas.'],
        'testimonials' => ['Patient Reviews', 'What Our Patients Say', 'Real feedback from patients who trust Unity Clinical Laboratory.'],
        'faqs' => ['Help Center', 'Frequently Asked Questions', 'Answers to common questions about bookings, reports, and home collection.'],
        'contact' => ['Reach Us', 'Get In Touch With Unity Lab', 'Find our location details or send an immediate diagnostic inquiry through the contact form.'],
    ];
    $meta_stmt = $db->prepare('UPDATE cms_sections SET section_tag = :tag, section_heading = :heading, section_description = :desc WHERE section_code = :code AND (section_tag IS NULL OR section_tag = "")');
    foreach ($section_meta_updates as $code => $meta) {
        $meta_stmt->execute([':tag' => $meta[0], ':heading' => $meta[1], ':desc' => $meta[2], ':code' => $code]);
    }
    $db->exec("UPDATE cms_sections SET section_type = 'builtin' WHERE section_type IS NULL OR section_type = ''");

    $db->exec("CREATE TABLE IF NOT EXISTS report_otps (
        {$SQL_ID},
        patient_id TEXT NOT NULL,
        mobile TEXT NOT NULL,
        otp_code TEXT NOT NULL,
        expires_at DATETIME NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // If database is newly created, populate default contents
    if ($is_new_db || count($db->query("SELECT * FROM cms_settings")->fetchAll()) === 0) {
        
        // Default admin — change password immediately after first login (Admin → Settings)
        $admin_password_hash = password_hash('admin_password_123', PASSWORD_BCRYPT);
        $db->exec("INSERT OR IGNORE INTO users (username, password) VALUES ('admin', '$admin_password_hash')");
        $db->exec("INSERT OR IGNORE INTO cms_settings (key, value) VALUES ('admin_password_changed', '0')");

        // Seed Patient Mock
        $db->exec("INSERT OR IGNORE INTO patients (patient_id, name, email, mobile, gender, age) VALUES ('PAT-1001', 'John Doe', 'johndoe@example.com', '9876543210', 'Male', 45)");

        // Seed General Settings
        $settings_seeds = [
            'site_name' => 'Unity Clinical Laboratory',
            'logo_text' => 'UnityLab',
            'support_phone' => '+91 98765 43210',
            'support_email' => 'info@unityclinicallab.com',
            'support_address' => '102 Health Plaza, Sector 15, Gurugram, Haryana - 122001',
            'whatsapp_number' => '919876543210',
            'hero_tagline' => 'NABL Certified Laboratory & Diagnostic Center',
            'hero_headline' => 'Accurate Diagnostics. Trusted Results.',
            'hero_subheadline' => 'Advanced Blood, Urine and Health Diagnostic Testing with Fast & Reliable Reports.',
            'hero_bg_image' => 'images/hero-lab.jpg',
            'maps_embed_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m18!1m2!1s0x390d19d6d5555555%3A0x8e82efc6ff6222b6!2sSector%2015%2C%20Gurugram%2C%20Haryana!5e0!3m2!1sen!2sin!4v1680000000000!5m2!1sen!2sin'
        ];
        $stmt = $db->prepare("INSERT INTO cms_settings (key, value) VALUES (:key, :value)");
        foreach ($settings_seeds as $k => $v) {
            $stmt->execute([':key' => $k, ':value' => $v]);
        }

        // Seed Homepage Section Ordering
        $sections_seeds = [
            ['hero', 'Hero Banner Section', 1],
            ['why_choose_us', 'Why Choose Us', 2],
            ['services', 'Our Services', 3],
            ['packages', 'Health Packages', 4],
            ['download_report', 'Online Report Download', 5],
            ['home_collection', 'Home Sample Collection Form', 6],
            ['equipment', 'Laboratory Equipment', 7],
            ['gallery', 'Laboratory Gallery', 8],
            ['testimonials', 'Patient Testimonials', 9],
            ['faqs', 'Frequently Asked Questions (FAQ)', 10],
            ['contact', 'Contact Details & Map', 11]
        ];
        $stmt = $db->prepare("INSERT INTO cms_sections (section_code, section_title, sequence) VALUES (:code, :title, :seq)");
        foreach ($sections_seeds as $s) {
            $stmt->execute([':code' => $s[0], ':title' => $s[1], ':seq' => $s[2]]);
        }

        // Seed Services
        $services_seeds = [
            ['Complete Blood Count (CBC)', 'HEMATOLOGY', 299, 'Includes Hemoglobin, WBC count, RBC count, Platelet counts, PCV, MCV, MCH, and differential cell count screening.', 'Blood (EDTA Vacutainer)', 'No fasting required', 1],
            ['Lipid Profile (Cholesterol)', 'BIOCHEMISTRY', 499, 'Measures Total Cholesterol, HDL (good) Cholesterol, LDL (bad) Cholesterol, VLDL, and Triglyceride biomarkers.', 'Blood (Serum Vacutainer)', '10-12 hours overnight fasting mandatory', 2],
            ['Thyroid Profile (T3, T4, TSH)', 'HORMONE ASSAY', 599, 'Evaluates general thyroid gland function. Analyzes free or total Triiodothyronine, Thyroxine, and TSH hormones.', 'Blood (Serum Vacutainer)', 'No fasting required', 3],
            ['HbA1c (Glycated Hemoglobin)', 'DIABETES PANEL', 399, 'Evaluates average blood sugar control over the past 90 days. Used for diagnosing and managing type-2 diabetes.', 'Blood (EDTA Vacutainer)', 'No fasting required', 4],
            ['Liver Function Test (LFT)', 'BIOCHEMISTRY', 699, 'Includes SGOT, SGPT, Bilirubin, Alkaline Phosphatase, Total Protein, Albumin, and Globulin markers.', 'Blood (Serum Vacutainer)', 'No fasting required', 5],
            ['Kidney Function Test (KFT)', 'BIOCHEMISTRY', 599, 'Evaluates renal filtration efficacy. Includes Serum Creatinine, Urea, Uric Acid, Calcium, and electrolytes.', 'Blood (Serum Vacutainer)', 'No fasting required', 6],
            ['Urine Routine Analysis', 'CLINICAL PATHOLOGY', 199, 'Physical examination (color, specific gravity), chemical tests (protein, sugar, ketones), and microscopic examination.', 'Mid-stream Urine Container', 'Morning first catch sample preferred', 7],
            ['Fasting Blood Sugar (FBS)', 'DIABETES PANEL', 99, 'Quantitative assessment of blood sugar glucose levels in a fasting state to verify glycemic levels.', 'Blood (Fluoride Vacutainer)', '8-10 hours overnight fasting mandatory', 8],
            ['Vitamin D (25-Hydroxy)', 'VITAMIN SCAN', 799, 'Assesses overall calcium metabolism, bone density, and immune wellness status by monitoring Vitamin D concentrations.', 'Blood (Serum Vacutainer)', 'No fasting required', 9]
        ];
        $stmt = $db->prepare("INSERT INTO cms_services (title, category, price, description, sample_type, prep_instructions, sequence) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($services_seeds as $s) {
            $stmt->execute($s);
        }

        // Seed Health Packages
        $packages_seeds = [
            ['Basic Health Package', 499, 'A foundational checkup designed for routine monitoring of general health markers.', "Complete Blood Count (CBC - 24 parameters)\nRandom Blood Sugar (Glycemia check)\nUrine Routine Examination\nSerum Creatinine (Kidney index)", 0, 1],
            ['Full Body Checkup Package', 1499, 'Our most complete diagnostic screening. Checks functioning of multiple vital organs.', "All CBC & ESR markers (24 Parameters)\nComplete Lipid Profile (Heart health)\nLiver Function Test (LFT - 11 markers)\nKidney Function Test (KFT - 6 markers)\nThyroid Profile (T3, T4, TSH)\nHbA1c & Fasting Glucose\nUrine Microalbumin & Routine\nFree Doctor Consultation online", 1, 2],
            ['Diabetes Package', 799, 'Ideal for diagnosed diabetics and those monitoring borderline pre-diabetic symptoms.', "HbA1c Glycosylated Hemoglobin\nFasting Blood Sugar (FBS)\nPost-Prandial Blood Sugar (PPBS)\nLipid Profile screen\nUrine Microalbumin (Early renal damage)", 0, 3],
            ['Senior Citizen Package', 999, 'Tailored for men and women aged 60+, focusing on joint, kidney, heart and bone health markers.', "CBC & Hematocrit Profile\nKidney Function Profile (Urea, Creatinine)\nLiver Enzyme Screen (SGOT, SGPT)\nLipid Cholesterol Screen\nBone Joint Markers (Calcium)\nFasting Glucose Test", 0, 4],
            ['Women\'s Health Package', 1299, 'Customized to screen hormonal imbalances, bone density indicators, iron deficiency, and thyroid wellness.', "Anaemia Screen (CBC + Iron)\nThyroid Profile (TSH, T3, T4)\nCalcium & Vitamin D3 (Bone strength)\nHbA1c Glycosylated Hemoglobin\nKidney & Liver basic enzymes\nUrine Culture & microscopic screen", 0, 5]
        ];
        $stmt = $db->prepare("INSERT INTO cms_packages (name, price, description, features, is_featured, sequence) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($packages_seeds as $p) {
            $stmt->execute($p);
        }

        // Seed Equipment
        $eq_seeds = [
            ['Biochemistry Analyzer', 'images/eq-biochem.jpg', 'Fully automated chemistry analyzer system handling up to 400 photometric tests per hour with precise spectrophotometric control.', 1],
            ['Hematology Counter', 'images/eq-hema.jpg', 'Advanced 5-part differential analyzer providing deep microscopic cell distribution metrics utilizing semiconductor laser scatter logic.', 2],
            ['Immunoassay System', 'images/gallery-3.jpg', 'Fully automated Chemiluminescence platform for ultra-sensitive hormones, cardiac markers, and specialized tumor antigen profiles.', 3]
        ];
        $stmt = $db->prepare("INSERT INTO cms_equipment (title, image_path, description, sequence) VALUES (?, ?, ?, ?)");
        foreach ($eq_seeds as $e) {
            $stmt->execute($e);
        }

        // Seed Gallery
        $gal_seeds = [
            ['Patient Reception Area', 'images/gallery-1.jpg', 'Clean, safe, and comfortable lobby designed to provide hassle-free registrations.', 1],
            ['Biochemistry Department', 'images/gallery-2.jpg', 'High-throughput robotic testing tracks running validation checks on serum samples.', 2],
            ['Blood Collection Desk', 'images/gallery-3.jpg', 'Comfortable blood draw stations operating under strict sanitised protocols.', 3],
            ['Diagnostic Equipment Room', 'images/gallery-4.jpg', 'Advanced immunoassay diagnostic tools ensuring precise thyroid and hormone readings.', 4]
        ];
        $stmt = $db->prepare("INSERT INTO cms_gallery (title, image_path, description, sequence) VALUES (?, ?, ?, ?)");
        foreach ($gal_seeds as $g) {
            $stmt->execute($g);
        }

        // Seed Testimonials
        $test_seeds = [
            ['Extremely satisfied with their home sample collection. The phlebotomist was professional, punctual, and drew blood painlessly. I got my lipid and thyroid reports online within 8 hours. High recommended!', 'Rajesh Kumar', 'Patient, Gurugram', 1],
            ['I book the Senior Citizen Package for my parents every 6 months. It covers all major health parameters and is very affordable. The download report section is simple to use even for older people.', 'Priya Sharma', 'Corporate client', 2],
            ['As a practicing doctor, I need reports I can trust. Unity Clinical Laboratory provides consistent accuracy, NABL compliant validation, and fast turnaround. They are my go-to choice for diagnostic referrals.', 'Dr. Amit Verma', 'MD, General Physician', 3]
        ];
        $stmt = $db->prepare("INSERT INTO cms_testimonials (text, author, designation, sequence) VALUES (?, ?, ?, ?)");
        foreach ($test_seeds as $t) {
            $stmt->execute($t);
        }

        // Seed FAQs
        $faq_seeds = [
            ['How do I book a home sample collection test?', 'You can book easily by filling out the Home Sample Collection booking form on our homepage or clicking the \'Book Home Test\' button in the navigation. Select your preferred date, packages or tests, and provide your home address. Alternatively, you can directly message us on WhatsApp or call us.', 1],
            ['How do I download my diagnostic laboratory reports?', 'Go to the \'Download Report\' page or fill in the \'Download Lab Report Online\' card on our homepage. Enter your unique Patient ID (printed on the billing receipt, e.g. PAT-1001) and your registered 10-digit mobile number. Click \'Download Report PDF\' to instantly save it to your device.', 2],
            ['Is Home Sample Collection available in my area?', 'Yes, we offer home sample collection services across Gurugram and surrounding regions. Our collectors visit homes and offices starting from 6:00 AM, allowing you to easily provide fasting-required samples before breakfast.', 3],
            ['What is the average turnaround time for standard test reports?', 'Most routine blood and urine analyses (such as CBC, Thyroid panel, Sugar tests, Lipid screen) are authorized and available for download within 6 to 12 hours of sample collection. Specialized biochemical cultures or immunological profiles may take 24 to 48 hours depending on incubation cycles.', 4]
        ];
        $stmt = $db->prepare("INSERT INTO cms_faqs (question, answer, sequence) VALUES (?, ?, ?)");
        foreach ($faq_seeds as $f) {
            $stmt->execute($f);
        }

        // Seed Blogs
        $blog_seeds = [
            ['Understanding Your Complete Blood Count (CBC) Report', 'images/gallery-2.jpg', 'Dr. Sunita Verma', 'What do Hemoglobin, Red Blood Cells, White Blood Cells, and Platelet levels mean? Learn to decipher your routine hemogram results...', 'Full article contents describing complete blood count metrics.'],
            ['Why Fasting is Mandatory for Blood Sugar & Lipid Profiles', 'images/eq-biochem.jpg', 'Dr. Sunita Verma', 'Eating or drinking before testing can significantly skew fasting glucose and triglyceride levels. Learn the logic behind the 12-hour overnight fast rule.', 'Full article detailing chemical changes in fasting vs fed blood markers.'],
            ['Thyroid Hormones: Roles in Weight & Metabolism', 'images/gallery-4.jpg', 'Dr. Raman Gupta', 'An overview of Hypothyroidism and Hyperthyroidism. Learn how T3, T4, and Thyroid Stimulating Hormone (TSH) regulate daily energy levels and heat.', 'Full article discussing endocrine regulations.'],
            ['Preventive Health Screenings: When Do You Need Them?', 'images/gallery-1.jpg', 'Dr. Sunita Verma', 'Detect asymptomatic health threats early. An annual checkup schedule recommendation based on age brackets, family history, and lifestyles.', 'Full article discussing wellness screenings.'],
            ['Understanding Diabetes: Differences in HbA1c and Sugar Tests', 'images/eq-hema.jpg', 'Dr. Raman Gupta', 'HbA1c acts as a long-term glycemic marker, unlike instant sugar values. Learn why doctors evaluate both during standard diabetic reviews.', 'Full article explaining average glycation indices.'],
            ['Kidney Safety: Key Clinical Markers to Monitor', 'images/hero-lab.jpg', 'Dr. Sunita Verma', 'How creatinine and urea levels reveal kidney health. Easy tips to maintain healthy renal filtration through hydration and controlled sodium.', 'Full article on glomerular filtration and hydration.']
        ];
        $stmt = $db->prepare("INSERT INTO cms_blogs (title, image_path, author, summary, content) VALUES (?, ?, ?, ?, ?)");
        foreach ($blog_seeds as $b) {
            $stmt->execute($b);
        }
    }

    // Ensure all required default settings keys are present in cms_settings
    $default_settings = [
        'logo_type' => 'text',
        'logo_image' => '',
        'logo_icon' => 'fa-solid fa-flask',
        'top_offer_active' => '1',
        'top_offer_text' => 'Get 10% Off on All Home Sample Collections today! | Use Code: LAB10',
        'footer_about' => 'Unity Clinical Laboratory is a leading pathology lab offering state-of-the-art diagnostic testing, trusted by thousands of patients and clinics.',
        'working_hours_weekday' => '07:00 AM - 09:00 PM',
        'working_hours_sunday' => '07:00 AM - 02:00 PM',
        'footer_copyright' => 'Unity Clinical Laboratory. All rights reserved.',
        'admin_password_changed' => '0',
        'notify_admin_email' => '',
        'mail_from_email' => '',
        'mail_from_name' => 'Unity Clinical Laboratory',
        'notify_on_booking' => '1',
        'notify_on_inquiry' => '1',
        'notify_on_report' => '1',
        'report_otp_enabled' => '1',
        'captcha_enabled' => '1',
        'sms_provider' => 'none',
        'msg91_api_key' => '',
        'msg91_sender_id' => 'UNITY',
        'seo_default_title_suffix' => 'Accurate Diagnostics & Blood Test Center',
        'seo_default_description' => 'Unity Clinical Laboratory offers NABL accredited pathology services including blood, urine, biochemistry, thyroid, diabetes and full body health checkups with home sample collection.',
        'seo_default_keywords' => 'laboratory, pathology lab, blood test, urine test, health checkup, home collection, NABL, diagnostic center, Gurugram',
        'seo_title_format' => '{page}',
        'seo_home_title' => 'Accurate Diagnostics. Trusted Results.',
        'seo_home_description' => 'Unity Clinical Laboratory is a premium pathology and diagnostic center offering NABL certified blood tests, urine tests, biochemistry, and health packages.',
        'seo_robots_index' => '1',
        'og_image' => 'images/og-image.jpg',
        'og_site_name' => 'Unity Clinical Laboratory',
        'twitter_card' => 'summary_large_image',
        'twitter_site' => '',
        'google_analytics_id' => '',
        'google_tag_manager_id' => '',
        'facebook_pixel_id' => '',
        'google_site_verification' => '',
        'bing_site_verification' => '',
        'schema_alternate_name' => 'Unity Diagnostics',
        'schema_street' => '102 Health Plaza, Sector 15',
        'schema_city' => 'Gurugram',
        'schema_state' => 'Haryana',
        'schema_postal' => '122001',
        'schema_country' => 'IN',
        'schema_lat' => '28.459497',
        'schema_lng' => '77.026638',
        'schema_price_range' => '$$',
        'schema_opens_weekday' => '07:00',
        'schema_closes_weekday' => '21:00',
        'schema_opens_sunday' => '07:00',
        'schema_closes_sunday' => '14:00',
        'social_facebook' => 'https://www.facebook.com/unityclinicallab',
        'social_instagram' => 'https://www.instagram.com/unityclinicallab',
        'social_twitter' => '',
        'social_youtube' => '',
        'social_linkedin' => '',
        'top_offer_link' => 'packages.php',
        'top_offer_link_text' => 'View Offers',
        'top_bar_location' => 'Gurugram, India',
        'footer_badge_1' => 'NABL ACCREDITED',
        'footer_badge_2' => 'ISO 9001:2015',
        'footer_home_collection_note' => 'Home Sample Collection starts from 6:00 AM.',
        'hero_btn_book_text' => 'Book Home Test',
        'hero_btn_book_url' => 'collection.php',
        'hero_btn_download_text' => 'Download Report',
        'hero_btn_download_url' => 'download.php',
        'hero_btn_call_text' => 'Call Now',
        'hero_whatsapp_message' => "Hi, I'd like to book a diagnostic test.",
        'floating_whatsapp_enabled' => '0',
        'marketing_head_scripts' => '',
        'marketing_body_scripts' => '',
        'header_show_top_bar' => '1',
        'header_show_phone' => '1',
        'header_show_email' => '1',
        'header_show_location' => '1',
        'header_show_social' => '1',
        'header_show_whatsapp_icon' => '1',
        'header_logo_url' => 'index.php',
        'header_logo_width' => '240',
        'header_logo_height' => '72',
        'header_menu_toggle_label' => 'Toggle menu',
        'footer_col_links_title' => 'Quick Links',
        'footer_col_contact_title' => 'Contact Us',
        'footer_col_hours_title' => 'Working Hours',
        'footer_weekday_label' => 'Mon - Sat:',
        'footer_sunday_label' => 'Sunday:',
        'footer_whatsapp_label' => 'Chat with Us',
        'footer_privacy_label' => 'Privacy Policy',
        'footer_terms_label' => 'Terms of Service',
        'footer_badge_3' => '',
        'footer_show_whatsapp' => '1',
        'footer_show_badges' => '1',
        'breadcrumb_home_label' => 'Home',
        'hero_panel_label' => 'Lab Excellence',
        'hero_panel_live' => 'Open Now',
        'hero_panel_help' => 'Need help booking?',
        'hero_stat_1_value' => '50+',
        'hero_stat_1_label' => 'Pathology Tests',
        'hero_stat_2_value' => '10K+',
        'hero_stat_2_label' => 'Happy Patients',
        'hero_stat_3_value' => '99.9%',
        'hero_stat_3_label' => 'Accuracy Rate',
        'hero_stat_4_value' => '6–12h',
        'hero_stat_4_label' => 'Report Turnaround',
        'hero_float_1_icon' => 'fa-solid fa-microscope',
        'hero_float_1_title' => 'Advanced Analyzers',
        'hero_float_1_desc' => 'Automated biochemistry & hematology',
        'hero_float_2_icon' => 'fa-solid fa-user-doctor',
        'hero_float_2_title' => 'Expert Pathologists',
        'hero_float_2_desc' => 'MD verified reports',
        'hero_trust_3_text' => 'Reports in 6–12 hrs',
        'hero_trust_3_icon' => 'fa-solid fa-bolt',
        'hero_trust_4_text' => 'Home Collection',
        'hero_trust_4_icon' => 'fa-solid fa-house-medical',
    ];
    $check_stmt = $db->prepare(
        dbDriver($db) === 'mysql'
            ? 'INSERT IGNORE INTO cms_settings (`key`, value) VALUES (:key, :value)'
            : 'INSERT OR IGNORE INTO cms_settings (key, value) VALUES (:key, :value)'
    );
    foreach ($default_settings as $k => $v) {
        $check_stmt->execute([':key' => $k, ':value' => $v]);
    }

    // Ensure cms_menu has default items if empty
    $menu_count = $db->query("SELECT COUNT(*) FROM cms_menu")->fetchColumn();
    if ($menu_count == 0) {
        $menu_seeds = [
            ['Home', 'index.php', 1, 1, 0],
            ['About', 'about.php', 2, 1, 0],
            ['Tests', 'services.php', 3, 1, 0],
            ['Packages', 'packages.php', 4, 1, 0],
            ['Gallery', 'gallery.php', 5, 1, 0],
            ['Blog', 'blog.php', 6, 1, 0],
            ['Contact', 'contact.php', 7, 1, 0],
            ['Download Report', 'download.php', 8, 1, 0],
            ['Book Home Test', 'collection.php', 9, 1, 1]
        ];
        $menu_stmt = $db->prepare("INSERT INTO cms_menu (title, url, sequence, is_active, is_cta) VALUES (?, ?, ?, ?, ?)");
        foreach ($menu_seeds as $m) {
            $menu_stmt->execute($m);
        }
    }

    cmsSeedLegalPageBodies($db);

    // 3. Load settings into global array
    $cms = [];
    $settings_query = $db->query("SELECT * FROM cms_settings");
    while ($row = $settings_query->fetch()) {
        $cms[$row['key']] = $row['value'];
    }

    $cms_sections = [];
    foreach ($db->query('SELECT * FROM cms_sections')->fetchAll() as $row) {
        $cms_sections[$row['section_code']] = $row;
    }

    $cms_pages = [];
    foreach ($db->query('SELECT * FROM cms_pages')->fetchAll() as $row) {
        $cms_pages[$row['slug']] = $row;
    }

    require_once __DIR__ . '/cms_helpers.php';
    require_once __DIR__ . '/marketing_helpers.php';

} catch (PDOException $e) {
    die("Database Connection failed: " . $e->getMessage());
}

// Helper to generate unique Patient ID
function generatePatientID($db) {
    $stmt = $db->query("SELECT patient_id FROM patients ORDER BY id DESC LIMIT 1");
    $last = $stmt->fetch();
    
    if (!$last) {
        return 'PAT-1001';
    }
    
    $last_id = $last['patient_id'];
    $number = (int)str_replace('PAT-', '', $last_id);
    $next_number = $number + 1;
    return 'PAT-' . $next_number;
}

function cmsSeedPagesAndBlocks(PDO $db, string $SQL_ID): void
{
    $page_count = (int) $db->query('SELECT COUNT(*) FROM cms_pages')->fetchColumn();
    if ($page_count > 0) {
        cmsSeedSectionItemsIfEmpty($db);
        return;
    }

    $pages = [
        ['about', 'about.php', 'about', 'About Our Laboratory', 'About Us', 'About Us | Accreditations & Quality Standards', 'Unity Clinical Laboratory is an ISO 9001:2015 and NABL aligned diagnostic pathology lab.', 'Our Background', 'Dedicated to Precision and Patient Care since 2012', 'Learn about our accreditations, values, and expert pathology team.'],
        ['services', 'services.php', 'services', 'Pathology Tests & Services', 'Tests & Services', 'Pathology Tests & Services | Accurate Blood & Urine Tests', 'Browse our NABL-standard diagnostic tests catalog including blood, urine, thyroid, and diabetes panels.', 'Diagnostics Catalog', 'Search Diagnostic Pathology Tests', 'Find pricing, patient pre-test guidelines, and sample requirements for our clinical lab tests.'],
        ['packages', 'packages.php', 'packages', 'Preventive Health Packages', 'Health Packages', 'Preventive Health Packages | Comprehensive Health Checkups', 'Choose from our preventive health packages with comprehensive blood screening starting from ₹499.', 'Wellness Packages', 'Select the Best Health Package for You', 'Regular clinical screenings are the foundation of healthy living.'],
        ['gallery', 'gallery.php', 'gallery', 'Laboratory Gallery', 'Laboratory Gallery', 'Laboratory Gallery | Diagnostic Facilities & Equipment', 'Take a virtual tour of Unity Clinical Laboratory facilities and equipment.', 'Facilities Showcase', 'Tour Our Advanced Clinical Laboratory', 'We maintain pristine sterility and premium technology across all diagnostic departments.'],
        ['contact', 'contact.php', 'contact', 'Contact Us', 'Contact Us', 'Contact Us | Diagnostic Lab Location & Helpline', 'Get in touch with Unity Clinical Laboratory for bookings, reports, and inquiries.', 'Helpline Support', 'We are Here to Assist You', 'Reach out to book a test, query report status, or explore corporate checkup partnerships.'],
        ['collection', 'collection.php', 'collection', 'Home Sample Collection', 'Home Collection Booking', 'Schedule Home Sample Collection | Blood Test Home Visit', 'Book a home blood test visit with qualified phlebotomists from Unity Clinical Laboratory.', 'Book Visit', 'Schedule Home Sample Collection', 'Fill in the form and our team will confirm your home collection slot.'],
        ['download', 'download.php', 'download', 'Patient Portal', 'Download Lab Report', 'Download Laboratory Reports Online | Patient Portal', 'Access your digital pathology reports online using Patient ID and mobile number.', 'Patient Portal', 'Download Lab Report Online', 'Securely verify and download your authorized laboratory PDF reports.'],
        ['blog', 'blog.php', 'blog', 'Health & Diagnostics Blog', 'Health Blog', 'Health Blog & Diagnostics Articles | Pathologists Advice', 'Read diagnostic medical insights and preventative healthcare articles from our pathologists.', 'Health Education', 'Latest Medical & Diagnostic Articles', 'Understand your body better with certified pathologists medical tips and guides.'],
        ['privacy', 'privacy.php', '', 'Privacy Policy', 'Privacy Policy', 'Privacy Policy', 'Privacy policy for Unity Clinical Laboratory covering patient data and report access.', '', '', ''],
        ['terms', 'terms.php', '', 'Terms of Service', 'Terms of Service', 'Terms of Service', 'Terms of service for using the Unity Clinical Laboratory website and online services.', '', '', ''],
    ];

    $stmt = $db->prepare('INSERT INTO cms_pages (slug, filename, nav_key, page_heading, breadcrumb_label, meta_title, meta_description, content_tag, content_title, content_description, sequence) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $seq = 1;
    foreach ($pages as $p) {
        $stmt->execute([$p[0], $p[1], $p[2], $p[3], $p[4], $p[5], $p[6], $p[7], $p[8], $p[9], $seq++]);
    }

    $about_blocks = [
        ['intro', 'Our Background', 'Dedicated to Precision and Patient Care since 2012', "Unity Clinical Laboratory was founded with a single mission: to deliver accurate, reproducible, and fast diagnostic results. We understand that behind every blood vial is a patient awaiting critical answers.\n\nWe adhere strictly to NABL guidelines. Our facility uses automated barcoding systems, ensuring flawless sample tracking from draw to digital signature.", 'images/hero-lab.jpg', '', 1],
        ['badge', 'NABL Aligned', '', 'Complies with ISO 15189 standards for diagnostic competence.', '', '', 2],
        ['badge', 'ISO Certified', '', 'ISO 9001:2015 certified quality management systems.', '', '', 3],
        ['header', 'Principles', 'Our Laboratory Core Values', 'Our daily clinical operations are built upon four fundamental cornerstones of modern healthcare.', '', '', 4],
        ['feature', 'Absolute Integrity', '', 'Uncompromised quality standards with strict internal control assays and zero tolerance for report errors.', '', 'fa-solid fa-clipboard-check', 5],
        ['feature', 'Patient First', '', 'Dedicated to safety, gentle sample collection technique, responsive counseling, and patient confidentiality.', '', 'fa-solid fa-user-doctor', 6],
        ['feature', 'Advanced Tech', '', 'Investing continuously in the latest chemistry, hematology, and immunodiagnostic analyzer modules.', '', 'fa-solid fa-microscope', 7],
        ['feature', 'Safe Cold-Chain', '', 'Strict temperature control protocol during home collection transport to protect sample degradation.', '', 'fa-solid fa-shield-halved', 8],
        ['header', 'Medical Directors', 'Meet Our Diagnostic Experts', 'Our laboratory reports are verified and signed by board-certified clinical pathologists.', '', '', 9],
        ['team', 'Dr. Sunita Verma', 'Chief Pathologist & Medical Director', 'Dr. Sunita holds an MD in Pathology from AIIMS Delhi with over 15 years of diagnostic laboratory experience.', '', 'SV', 10],
        ['team', 'Dr. Raman Gupta', 'Senior Microbiologist', 'Dr. Raman completed his MD in Medical Microbiology and specializes in immunology and culture analytics.', '', 'RG', 11],
        ['team', 'Akshay Sanjay Rakh', 'Senior Lab Technician (ADMLT)', 'Akshay holds an Advanced Diploma in Medical Laboratory Technology with First Class Distinction.', '', 'AR', 12],
    ];
    $bstmt = $db->prepare('INSERT INTO cms_page_blocks (page_slug, block_type, title, subtitle, content, image_path, icon, sequence) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    foreach ($about_blocks as $b) {
        $bstmt->execute(['about', $b[0], $b[1], $b[2], $b[3], $b[4], $b[5], $b[6]]);
    }

    cmsSeedSectionItemsIfEmpty($db);
}

function cmsSeedSectionItemsIfEmpty(PDO $db): void
{
    $count = (int) $db->query("SELECT COUNT(*) FROM cms_section_items WHERE section_code = 'why_choose_us'")->fetchColumn();
    if ($count > 0) {
        return;
    }

    $items = [
        ['why_choose_us', 'Accurate Results', '', 'Double-checked results processed with the highest quality clinical control standards for complete reliability.', 'fa-solid fa-bullseye', 1],
        ['why_choose_us', 'Modern Equipment', '', 'Equipped with advanced fully automated biochemistry and hematology analyzers from global medical leaders.', 'fa-solid fa-microscope', 2],
        ['why_choose_us', 'Experienced Staff', '', 'Our highly qualified pathologists and professional phlebotomists ensure standard clinical guidelines are met.', 'fa-solid fa-user-doctor', 3],
        ['why_choose_us', 'Fast Reporting', '', 'Most standard routine test reports are compiled, authorized, and uploaded online within 6 to 12 hours.', 'fa-solid fa-bolt', 4],
        ['why_choose_us', 'Home Collection', '', 'Schedule a qualified home sample collector to visit your home or office at your preferred time slot.', 'fa-solid fa-house-medical', 5],
        ['why_choose_us', 'Affordable Pricing', '', 'Premium diagnostic testing services packaged at accessible rates with no hidden processing fees.', 'fa-solid fa-tags', 6],
    ];
    $stmt = $db->prepare('INSERT INTO cms_section_items (section_code, title, subtitle, description, icon, sequence) VALUES (?, ?, ?, ?, ?, ?)');
    foreach ($items as $item) {
        $stmt->execute($item);
    }
}

function cmsSeedLegalPageBodies(PDO $db): void
{
    $bodies = [
        'privacy' => '<p class="legal-updated"><strong>Last updated:</strong> {date}</p>
<p>{site_name} ("we", "our", "the laboratory") respects your privacy and is committed to protecting personal and medical information shared with us.</p>
<h2>Information We Collect</h2>
<ul><li>Contact details when you book home collection or submit inquiries.</li><li>Patient identifiers and demographic details when samples are registered.</li><li>Diagnostic reports uploaded by authorized laboratory staff.</li></ul>
<h2>How We Use Information</h2>
<ul><li>To process bookings, perform diagnostic testing, and deliver reports.</li><li>To verify identity before report download.</li><li>To respond to support requests and operational communications.</li></ul>
<h2>Data Security</h2>
<p>We restrict access to patient records and reports to authorized staff. Report files are served only after verification through our secure download portal.</p>
<h2>Contact</h2>
<p>For privacy-related questions, contact us at {email} or {phone}.</p>',
        'terms' => '<p class="legal-updated"><strong>Last updated:</strong> {date}</p>
<p>By using the {site_name} website and online services, you agree to these terms.</p>
<h2>Website Use</h2>
<p>This website provides general laboratory information, online booking requests, inquiry submission, and secure report download for registered patients.</p>
<h2>Bookings &amp; Appointments</h2>
<p>Home collection bookings submitted online are subject to slot confirmation by our team. Fasting and pre-test instructions must be followed as advised.</p>
<h2>Reports &amp; Medical Disclaimer</h2>
<p>Laboratory reports are for clinical use under qualified medical supervision. Online content is informational and not a substitute for professional medical advice.</p>
<h2>Contact</h2>
<p>Questions about these terms may be sent to {email}.</p>',
    ];

    $site = $db->query("SELECT value FROM cms_settings WHERE key = 'site_name' LIMIT 1")->fetchColumn() ?: 'Unity Clinical Laboratory';
    $email = $db->query("SELECT value FROM cms_settings WHERE key = 'support_email' LIMIT 1")->fetchColumn() ?: '';
    $phone = $db->query("SELECT value FROM cms_settings WHERE key = 'support_phone' LIMIT 1")->fetchColumn() ?: '';
    $date = date('F j, Y');

    $stmt = $db->prepare('UPDATE cms_pages SET page_body = :body WHERE slug = :slug AND (page_body IS NULL OR page_body = "")');
    foreach ($bodies as $slug => $template) {
        $body = str_replace(
            ['{date}', '{site_name}', '{email}', '{phone}'],
            [$date, htmlspecialchars((string) $site, ENT_QUOTES, 'UTF-8'), htmlspecialchars((string) $email, ENT_QUOTES, 'UTF-8'), htmlspecialchars((string) $phone, ENT_QUOTES, 'UTF-8')],
            $template
        );
        $stmt->execute([':body' => $body, ':slug' => $slug]);
    }
}
?>
