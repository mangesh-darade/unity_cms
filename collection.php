<?php
include 'includes/db.php';

$active_nav = 'collection';
$page_title = "Schedule Home Sample Collection | Blood Test Home Visit";
$meta_description = "Book a home blood test or urine collection visit. Qualified phlebotomists from Unity Clinical Laboratory will collect samples safely at your home.";

include 'includes/header.php';

// Check for parameters to pre-select package or test
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

// Fetch services and packages for the booking dropdown
try {
    $dropdown_services = $db->query("SELECT title, price FROM cms_services ORDER BY sequence ASC")->fetchAll();
    $dropdown_packages = $db->query("SELECT name, price FROM cms_packages ORDER BY sequence ASC")->fetchAll();
} catch (PDOException $e) {
    $dropdown_services = [];
    $dropdown_packages = [];
}
?>

<!-- Page Title Header -->
<div class="page-header">
    <div class="container">
        <h1>Home Sample Collection</h1>
        <div class="breadcrumb">
            <a href="index.php">Home</a> &nbsp;/&nbsp; Home Collection Booking
        </div>
    </div>
</div>

<!-- Form layout Section -->
<section class="section-padding">
    <div class="container" style="max-width: 900px;">
        <div class="grid-2" style="grid-template-columns: 1fr 1.3fr; gap: 40px; align-items: start;">
            
            <!-- Side Guidance Info -->
            <div>
                <div style="background-color: var(--bg-light); border: 1px solid var(--border); padding: 30px; border-radius: var(--radius-md);">
                    <h3 style="font-size: 1.3rem; margin-bottom: 20px; color: var(--primary);"><i class="fa-solid fa-circle-question" style="color: var(--brand-teal);"></i> Booking Guidelines</h3>
                    
                    <ul style="list-style: none; display: flex; flex-direction: column; gap: 20px; font-size: 0.95rem; color: var(--text-main);">
                        <li style="display: flex; gap: 10px; align-items: flex-start;">
                            <span style="color: var(--brand-teal); font-weight: bold; font-size: 1.1rem; line-height: 1;">1.</span>
                            <span><strong>Fasting Requirement:</strong> For blood sugar fasting (FBS) or lipid profile checkups, ensure an overnight fast of 10-12 hours prior to the slot. You may drink water.</span>
                        </li>
                        <li style="display: flex; gap: 10px; align-items: flex-start;">
                            <span style="color: var(--brand-teal); font-weight: bold; font-size: 1.1rem; line-height: 1;">2.</span>
                            <span><strong>Verification:</strong> Once you submit, our patient coordinator will call you within 30 minutes to confirm your address and schedule.</span>
                        </li>
                        <li style="display: flex; gap: 10px; align-items: flex-start;">
                            <span style="color: var(--brand-teal); font-weight: bold; font-size: 1.1rem; line-height: 1;">3.</span>
                            <span><strong>Phlebotomist Details:</strong> We will share the phlebotomist's name, phone, and temperature log via WhatsApp 1 hour before their arrival.</span>
                        </li>
                        <li style="display: flex; gap: 10px; align-items: flex-start;">
                            <span style="color: var(--brand-teal); font-weight: bold; font-size: 1.1rem; line-height: 1;">4.</span>
                            <span><strong>Report Access:</strong> Digital reports will be uploaded to the portal and notified to your mobile via WhatsApp within 8-12 hours of draw.</span>
                        </li>
                    </ul>
                    
                    <div style="margin-top: 30px; border-top: 1px solid var(--border); padding-top: 20px; text-align: center;">
                        <p style="font-weight: 600; font-size: 0.9rem; margin-bottom: 10px;">Need Instant Assistance?</p>
                        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $cms['whatsapp_number']); ?>?text=Hi,%20I%20want%20to%20schedule%20a%20home%20collection%20visit." class="btn btn-whatsapp w-full" target="_blank"><i class="fa-brands fa-whatsapp"></i> Chat on WhatsApp</a>
                    </div>
                </div>
            </div>
            
            <!-- Main Form Card -->
            <div class="booking-form-card" style="box-shadow: var(--shadow-md); padding: 35px; border-radius: var(--radius-md);">
                <h2 style="font-size: 1.8rem; margin-bottom: 20px; text-align: center; color: var(--primary);">Schedule Visit</h2>
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
                        <label for="address" class="form-label">Full Address (With landmark & pincode)</label>
                        <textarea id="address" name="address" class="form-control" placeholder="House/Flat number, wing, building name, street, nearby landmark, city pincode" required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-full" style="margin-top: 10px;">
                        <i class="fa-solid fa-paper-plane"></i> Confirm Booking Appointment
                    </button>
                </form>
            </div>
            
        </div>
    </div>
</section>

<?php
include 'includes/footer.php';
?>
