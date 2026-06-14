<?php
include 'includes/db.php';
require_once 'includes/captcha.php';

$page = cmsPage($cms_pages, 'collection', [
    'meta_title' => 'Schedule Home Sample Collection | Blood Test Home Visit',
    'meta_description' => 'Book a home blood test visit with qualified phlebotomists from Unity Clinical Laboratory.',
]);
$cms_page_context = $page;
$active_nav = 'collection';
$page_title = $page['meta_title'];
$meta_description = $page['meta_description'];
$meta_keywords = $page['meta_keywords'] ?? null;

include 'includes/header.php';

$selected_pkg = isset($_GET['package']) ? trim($_GET['package']) : '';
$selected_tst = isset($_GET['test']) ? trim($_GET['test']) : '';

$preselected = '';
if ($selected_pkg === 'basic') {
    $preselected = 'Basic Health Package';
} elseif ($selected_pkg === 'fullbody') {
    $preselected = 'Full Body Checkup Package';
} elseif ($selected_pkg === 'diabetes') {
    $preselected = 'Diabetes Package';
} elseif ($selected_pkg === 'senior') {
    $preselected = 'Senior Citizen Package';
} elseif ($selected_pkg === 'women') {
    $preselected = 'Women\'s Health Package';
} elseif ($selected_tst === 'cbc') {
    $preselected = 'Blood Routine Test';
} elseif ($selected_tst === 'thyroid') {
    $preselected = 'Thyroid Test';
} elseif ($selected_tst === 'urine') {
    $preselected = 'Urine Routine Examination';
}

try {
    $dropdown_services = $db->query("SELECT title, price FROM cms_services ORDER BY sequence ASC")->fetchAll();
    $dropdown_packages = $db->query("SELECT name, price FROM cms_packages ORDER BY sequence ASC")->fetchAll();
} catch (PDOException $e) {
    $dropdown_services = [];
    $dropdown_packages = [];
}

$whatsapp_num = preg_replace('/[^0-9]/', '', cmsSetting($cms, 'whatsapp_number'));
?>

<?php renderPageHeader($page, 'Home Sample Collection', 'Home Collection Booking'); ?>

<section class="section-padding portal-section">
    <div class="container container-narrow">
        <?php renderSectionHeader($cms_sections, 'home_collection', [
            'tag' => $page['content_tag'] ?: 'Book Visit',
            'title' => $page['content_title'] ?: 'Schedule Home Sample Collection',
            'desc' => $page['content_description'] ?: 'Fill in the form and our team will confirm your home collection slot.',
        ]); ?>

        <div class="collection-layout reveal">
            <div class="info-panel card reveal reveal-delay-1">
                <h3 class="info-panel-title"><i class="fa-solid fa-circle-question"></i> Booking Guidelines</h3>
                <ol class="guidance-steps">
                    <li>
                        <span class="guidance-num">1</span>
                        <div><strong>Fasting Requirement:</strong> For blood sugar fasting (FBS) or lipid profile checkups, ensure an overnight fast of 10–12 hours prior to the slot. You may drink water.</div>
                    </li>
                    <li>
                        <span class="guidance-num">2</span>
                        <div><strong>Verification:</strong> Once you submit, our patient coordinator will call you within 30 minutes to confirm your address and schedule.</div>
                    </li>
                    <li>
                        <span class="guidance-num">3</span>
                        <div><strong>Phlebotomist Details:</strong> We will share the phlebotomist's name, phone, and temperature log via WhatsApp 1 hour before their arrival.</div>
                    </li>
                    <li>
                        <span class="guidance-num">4</span>
                        <div><strong>Report Access:</strong> Digital reports will be uploaded to the portal and notified to your mobile via WhatsApp within 8–12 hours of draw.</div>
                    </li>
                </ol>
                <?php if ($whatsapp_num !== ''): ?>
                <div class="info-panel-cta">
                    <p>Need Instant Assistance?</p>
                    <a href="https://wa.me/<?php echo htmlspecialchars($whatsapp_num); ?>?text=Hi,%20I%20want%20to%20schedule%20a%20home%20collection%20visit." class="btn btn-whatsapp w-full" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-whatsapp"></i> Chat on WhatsApp</a>
                </div>
                <?php endif; ?>
            </div>

            <div class="booking-form-card premium-form-card reveal reveal-delay-2">
                <div class="form-card-header">
                    <span class="form-card-icon form-card-icon-book"><i class="fa-solid fa-house-medical"></i></span>
                    <h3>Schedule Visit</h3>
                    <p>Book a certified phlebotomist for home sample collection.</p>
                </div>
                <form id="bookingForm">
                    <div class="form-group">
                        <label for="name" class="form-label">Patient's Full Name</label>
                        <input type="text" id="name" name="name" class="form-control" placeholder="Enter patient's name" required>
                    </div>
                    <div class="form-group">
                        <label for="mobile" class="form-label">Registered Mobile Number</label>
                        <input type="tel" id="mobile" name="mobile" class="form-control" placeholder="Enter 10-digit mobile number" required>
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address (For reports delivery)</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="patient@example.com">
                    </div>
                    <div class="form-group">
                        <label for="test_type" class="form-label">Select Required Test/Package</label>
                        <select id="test_type" name="test_type" class="form-control" required>
                            <option value="">-- Choose Test or Package --</option>
                            <optgroup label="Health Packages">
                                <?php foreach ($dropdown_packages as $pkg):
                                    $is_sel = ($preselected === $pkg['name']) || (strtolower(str_replace(' ', '', $pkg['name'])) === strtolower(str_replace(' ', '', $selected_pkg)));
                                ?>
                                    <option value="<?php echo htmlspecialchars($pkg['name']); ?>" <?php echo $is_sel ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($pkg['name']); ?> (₹<?php echo number_format($pkg['price']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                            <optgroup label="Individual Pathology Tests">
                                <?php foreach ($dropdown_services as $srv):
                                    $is_sel = ($preselected === $srv['title']) || (strtolower(str_replace(' ', '', $srv['title'])) === strtolower(str_replace(' ', '', $selected_tst)));
                                ?>
                                    <option value="<?php echo htmlspecialchars($srv['title']); ?>" <?php echo $is_sel ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($srv['title']); ?> (₹<?php echo number_format($srv['price']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                            <option value="Others">Others (Specify in Address)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="preferred_date" class="form-label">Preferred Collection Date</label>
                        <input type="date" id="preferred_date" name="preferred_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="address" class="form-label">Full Address (With landmark &amp; pincode)</label>
                        <textarea id="address" name="address" class="form-control" placeholder="House/Flat number, wing, building name, street, nearby landmark, city pincode" required></textarea>
                    </div>
                    <?php if (captchaEnabled($cms)) { echo renderCaptchaField(); } ?>
                    <button type="submit" class="btn btn-primary w-full">
                        <i class="fa-solid fa-paper-plane"></i> Confirm Booking Appointment
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
