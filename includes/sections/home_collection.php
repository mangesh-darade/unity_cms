<?php
// Fetch services and packages for the booking dropdown
require_once __DIR__ . '/../captcha.php';
$dropdown_services = $db->query("SELECT title, price FROM cms_services ORDER BY sequence ASC")->fetchAll();
$dropdown_packages = $db->query("SELECT name, price FROM cms_packages ORDER BY sequence ASC")->fetchAll();
?>
<!-- Home Sample Collection Section -->
<section id="book-collection" class="booking-section section-padding">
    <div class="container">
        <div class="booking-wrapper">
            <!-- Booking Information -->
            <div class="booking-info">
                <?php
                $hc_tag = cmsSection($cms_sections, 'home_collection', 'section_tag', 'Convenient Diagnostics');
                $hc_title = cmsSection($cms_sections, 'home_collection', 'section_heading', 'Book a Home Sample Collection');
                $hc_desc = cmsSection($cms_sections, 'home_collection', 'section_description', 'Avoid long queues and diagnostic delays. Our certified and vaccinated phlebotomists will visit your home or workplace to collect blood or urine samples safely.');
                ?>
                <span class="section-tag"><?php echo htmlspecialchars($hc_tag); ?></span>
                <h2><?php echo htmlspecialchars($hc_title); ?></h2>
                <p style="color: var(--text-muted); margin-bottom: 30px;"><?php echo htmlspecialchars($hc_desc); ?></p>
                
                <div class="booking-info-list">
                    <div class="booking-info-item">
                        <div class="booking-info-icon"><i class="fa-solid fa-user-shield"></i></div>
                        <div>
                            <h4 style="font-size: 1.1rem; margin-bottom: 4px;">Standard Safe Hygiene</h4>
                            <p style="color: var(--text-muted); font-size: 0.9rem;">Fresh, single-use vacuum tube needles opened directly in front of the patient.</p>
                        </div>
                    </div>
                    <div class="booking-info-item">
                        <div class="booking-info-icon"><i class="fa-solid fa-temperature-arrow-down"></i></div>
                        <div>
                            <h4 style="font-size: 1.1rem; margin-bottom: 4px;">Cold-Chain Sample Transport</h4>
                            <p style="color: var(--text-muted); font-size: 0.9rem;">Samples are packed in dynamic temperature-controlled boxes to preserve cell integrity.</p>
                        </div>
                    </div>
                    <div class="booking-info-item">
                        <div class="booking-info-icon"><i class="fa-solid fa-clock"></i></div>
                        <div>
                            <h4 style="font-size: 1.1rem; margin-bottom: 4px;">Flexibility</h4>
                            <p style="color: var(--text-muted); font-size: 0.9rem;">Book slots from 6:00 AM onwards to facilitate fasting blood samples.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Booking Form -->
            <div class="booking-form-card" id="booking-form-anchor">
                <h3 style="font-size: 1.5rem; margin-bottom: 24px; text-align: center;"><i class="fa-solid fa-calendar-days" style="color: var(--brand-teal);"></i> Request Appointment</h3>
                <form id="bookingForm">
                    <div class="form-group">
                        <label for="booking_name" class="form-label">Full Name</label>
                        <input type="text" id="booking_name" name="name" class="form-control" placeholder="Enter your full name" required>
                    </div>
                    <div class="form-group">
                        <label for="booking_mobile" class="form-label">Mobile Number</label>
                        <input type="tel" id="booking_mobile" name="mobile" class="form-control" placeholder="Enter 10-digit mobile number" required>
                    </div>
                    <div class="form-group">
                        <label for="booking_email" class="form-label">Email Address (Optional)</label>
                        <input type="email" id="booking_email" name="email" class="form-control" placeholder="name@example.com">
                    </div>
                    <div class="form-group">
                        <label for="booking_test" class="form-label">Select Test or Package</label>
                        <select id="booking_test" name="test_type" class="form-control" required>
                            <option value="">-- Choose Test or Package --</option>
                            <optgroup label="Health Packages">
                                <?php foreach ($dropdown_packages as $pkg): ?>
                                    <option value="<?php echo htmlspecialchars($pkg['name']); ?>"><?php echo htmlspecialchars($pkg['name']); ?> (₹<?php echo number_format($pkg['price']); ?>)</option>
                                <?php endforeach; ?>
                            </optgroup>
                            <optgroup label="Individual Pathology Tests">
                                <?php foreach ($dropdown_services as $srv): ?>
                                    <option value="<?php echo htmlspecialchars($srv['title']); ?>"><?php echo htmlspecialchars($srv['title']); ?> (₹<?php echo number_format($srv['price']); ?>)</option>
                                <?php endforeach; ?>
                            </optgroup>
                            <option value="Others">Others (Specify in Address)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="booking_date" class="form-label">Preferred Collection Date</label>
                        <input type="date" id="booking_date" name="preferred_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="booking_address" class="form-label">Full Collection Address</label>
                        <textarea id="booking_address" name="address" class="form-control" placeholder="House number, flat, street, landmark, area pin-code" required></textarea>
                    </div>
                    <?php if (captchaEnabled($cms)) { echo renderCaptchaField(); } ?>
                    <button type="submit" class="btn btn-primary w-full"><i class="fa-solid fa-paper-plane"></i> Confirm Booking Request</button>
                </form>
            </div>
        </div>
    </div>
</section>
