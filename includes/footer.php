<?php
// Prevent direct access to includes folder
if (basename($_SERVER['PHP_SELF']) == 'footer.php') {
    header("HTTP/1.1 403 Forbidden");
    exit("Access denied");
}

$site_name = $cms['site_name'] ?? "Unity Clinical Laboratory";
$logo_text = $cms['logo_text'] ?? "UnityLab";
$support_phone = $cms['support_phone'] ?? "+91 98765 43210";
$support_email = $cms['support_email'] ?? "info@unityclinicallab.com";
$support_address = $cms['support_address'] ?? "102 Health Plaza, Sector 15, Gurugram, Haryana - 122001";
$whatsapp_num = $cms['whatsapp_number'] ?? "919876543210";

// Split logo text into two parts for styling
$logo_main = $logo_text;
$logo_span = "";
if (preg_match('/^([A-Z][a-z]+)([A-Z][A-Za-z]+)$/', $logo_text, $matches)) {
    $logo_main = $matches[1];
    $logo_span = $matches[2];
} else {
    $len = strlen($logo_text);
    if ($len > 4) {
        $logo_main = substr($logo_text, 0, $len - 3);
        $logo_span = substr($logo_text, $len - 3);
    }
}
?>
    <!-- Footer Section -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <!-- Column 1: Brand -->
                <div class="footer-brand">
                    <h2><?php echo htmlspecialchars($logo_main); ?><span><?php echo htmlspecialchars($logo_span); ?></span></h2>
                    <p><?php echo htmlspecialchars($cms['footer_about'] ?? 'Unity Clinical Laboratory is a leading pathology lab offering state-of-the-art diagnostic testing, trusted by thousands of patients and clinics.'); ?></p>
                    <div class="accreditations" style="margin-top: 15px; display: flex; gap: 10px;">
                        <span style="font-size: 0.8rem; background-color: rgba(255,255,255,0.1); padding: 4px 10px; border-radius: 4px; border: 1px solid rgba(255,255,255,0.2);">NABL ACCREDITED</span>
                        <span style="font-size: 0.8rem; background-color: rgba(255,255,255,0.1); padding: 4px 10px; border-radius: 4px; border: 1px solid rgba(255,255,255,0.2);">ISO 9001:2015</span>
                    </div>
                </div>

                <!-- Column 2: Quick Links -->
                <div class="footer-col">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="services.php">Tests & Services</a></li>
                        <li><a href="packages.php">Health Packages</a></li>
                        <li><a href="gallery.php">Gallery</a></li>
                        <li><a href="blog.php">Health Blog</a></li>
                        <li><a href="contact.php">Contact Us</a></li>
                    </ul>
                </div>

                <!-- Column 3: Contacts -->
                <div class="footer-col">
                    <h3>Contact Us</h3>
                    <div class="footer-contact-item">
                        <span><i class="fa-solid fa-location-dot"></i></span>
                        <p><?php echo nl2br(htmlspecialchars($support_address)); ?></p>
                    </div>
                    <div class="footer-contact-item">
                        <span><i class="fa-solid fa-phone"></i></span>
                        <p><?php echo htmlspecialchars($support_phone); ?></p>
                    </div>
                    <div class="footer-contact-item">
                        <span><i class="fa-solid fa-envelope"></i></span>
                        <p><?php echo htmlspecialchars($support_email); ?></p>
                    </div>
                    <div class="footer-contact-item">
                        <span><i class="fa-brands fa-whatsapp"></i></span>
                        <p><a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $whatsapp_num); ?>" target="_blank" style="color: #25d366; font-weight: bold;">Chat with Us</a></p>
                    </div>
                </div>

                <!-- Column 4: Timing -->
                <div class="footer-col">
                    <h3>Working Hours</h3>
                    <ul class="footer-links" style="color: #cbd5e1;">
                        <li style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span>Mon - Sat:</span>
                            <span><?php echo htmlspecialchars($cms['working_hours_weekday'] ?? '07:00 AM - 09:00 PM'); ?></span>
                        </li>
                        <li style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span>Sunday:</span>
                            <span><?php echo htmlspecialchars($cms['working_hours_sunday'] ?? '07:00 AM - 02:00 PM'); ?></span>
                        </li>
                        <li style="margin-top: 15px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 10px; font-size: 0.85rem; color: #94a3b8;">
                            <i class="fa-solid fa-truck-medical"></i> Home Sample Collection starts from 6:00 AM.
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Copyright Bar -->
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($cms['footer_copyright'] ?? $site_name . '. All rights reserved.'); ?></p>
                <div class="footer-bottom-links">
                    <a href="admin/index.php" style="font-weight: bold; color: var(--brand-teal-light);"><i class="fa-solid fa-lock"></i> Staff Login</a>
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Main JavaScript Logic -->
    <script src="js/main.js"></script>
</body>
</html>
