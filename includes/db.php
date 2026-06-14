<?php
// Prevent direct access to includes folder
if (basename($_SERVER['PHP_SELF']) == 'db.php') {
    header("HTTP/1.1 403 Forbidden");
    exit("Access denied");
}

// Set timezone
date_default_timezone_set('Asia/Kolkata');

$db_path = __DIR__ . '/../database.sqlite';
$is_new_db = !file_exists($db_path);

try {
    $db = new PDO("sqlite:" . $db_path);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Enable Foreign Keys
    $db->exec('PRAGMA foreign_keys = ON;');
    
    // 1. Create Core Tables (Patients, Reports, Bookings, Inquiries, Users)
    $db->exec("CREATE TABLE IF NOT EXISTS patients (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        patient_id TEXT UNIQUE NOT NULL,
        name TEXT NOT NULL,
        email TEXT,
        mobile TEXT NOT NULL,
        gender TEXT,
        age INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS reports (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        patient_id TEXT NOT NULL,
        test_name TEXT NOT NULL,
        file_path TEXT NOT NULL,
        uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS bookings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
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
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT,
        mobile TEXT NOT NULL,
        subject TEXT,
        message TEXT NOT NULL,
        status TEXT DEFAULT 'New',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. Create CMS Tables
    $db->exec("CREATE TABLE IF NOT EXISTS cms_settings (
        key TEXT PRIMARY KEY,
        value TEXT NOT NULL
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS cms_sections (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        section_code TEXT UNIQUE NOT NULL,
        section_title TEXT NOT NULL,
        is_active INTEGER DEFAULT 1,
        sequence INTEGER NOT NULL
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS cms_services (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        category TEXT NOT NULL,
        price REAL NOT NULL,
        description TEXT,
        sample_type TEXT,
        prep_instructions TEXT,
        sequence INTEGER DEFAULT 0
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS cms_packages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        price REAL NOT NULL,
        description TEXT,
        features TEXT,
        is_featured INTEGER DEFAULT 0,
        sequence INTEGER DEFAULT 0
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS cms_equipment (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        image_path TEXT NOT NULL,
        description TEXT,
        sequence INTEGER DEFAULT 0
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS cms_gallery (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        image_path TEXT NOT NULL,
        description TEXT,
        sequence INTEGER DEFAULT 0
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS cms_testimonials (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        text TEXT NOT NULL,
        author TEXT NOT NULL,
        designation TEXT,
        sequence INTEGER DEFAULT 0
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS cms_faqs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        question TEXT NOT NULL,
        answer TEXT NOT NULL,
        sequence INTEGER DEFAULT 0
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS cms_blogs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        image_path TEXT NOT NULL,
        author TEXT NOT NULL,
        summary TEXT,
        content TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS cms_menu (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        url TEXT NOT NULL,
        sequence INTEGER NOT NULL,
        is_active INTEGER DEFAULT 1,
        is_cta INTEGER DEFAULT 0
    )");

    // If database is newly created, populate default contents
    if ($is_new_db || count($db->query("SELECT * FROM cms_settings")->fetchAll()) === 0) {
        
        // Seed Admin User (admin / admin_password_123)
        $admin_password_hash = password_hash('admin_password_123', PASSWORD_BCRYPT);
        $db->exec("INSERT OR IGNORE INTO users (username, password) VALUES ('admin', '$admin_password_hash')");

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
        'footer_copyright' => 'Unity Clinical Laboratory. All rights reserved.'
    ];
    $check_stmt = $db->prepare("INSERT OR IGNORE INTO cms_settings (key, value) VALUES (:key, :value)");
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

    // 3. Load settings into global array
    $cms = [];
    $settings_query = $db->query("SELECT * FROM cms_settings");
    while ($row = $settings_query->fetch()) {
        $cms[$row['key']] = $row['value'];
    }

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
?>
