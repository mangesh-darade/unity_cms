<?php
include 'includes/db.php';
require_once 'includes/captcha.php';

$page = cmsPage($cms_pages, 'contact', [
    'meta_title' => 'Contact Us | Diagnostic Lab Location & Helpline',
    'meta_description' => 'Get in touch with Unity Clinical Laboratory for bookings, reports, and inquiries.',
]);
$cms_page_context = $page;
$active_nav = 'contact';
$page_title = $page['meta_title'];
$meta_description = $page['meta_description'];
$meta_keywords = $page['meta_keywords'] ?? null;

$support_phone = cmsSetting($cms, 'support_phone');
$support_email = cmsSetting($cms, 'support_email');
$support_address = cmsSetting($cms, 'support_address');
$whatsapp_num = preg_replace('/[^0-9]/', '', cmsSetting($cms, 'whatsapp_number'));
$phone_href = preg_replace('/[^0-9+]/', '', $support_phone);
$maps_url = cmsSetting($cms, 'maps_embed_url');
$home_collection_note = cmsSetting($cms, 'footer_home_collection_note', 'Home collection requests are serviced from 6:00 AM daily.');

include 'includes/header.php';
?>

<?php renderPageHeader($page, 'Contact Us', 'Contact Us'); ?>

<section class="section-padding">
    <div class="container">
        <?php renderSectionHeader($cms_sections, 'contact', [
            'tag' => $page['content_tag'] ?: 'Helpline Support',
            'title' => $page['content_title'] ?: 'We are Here to Assist You',
            'desc' => $page['content_description'] ?: 'Reach out to book a test, query report status, or explore corporate checkup partnerships.',
        ]); ?>

        <div class="contact-grid reveal">
            <div class="contact-info-list reveal-stagger">
                <div class="card contact-card">
                    <h3 class="contact-card-title"><i class="fa-solid fa-address-book"></i> Contact Information</h3>
                    <div class="contact-items-stack">
                        <?php if ($support_address !== ''): ?>
                        <div class="contact-item">
                            <div class="contact-icon"><i class="fa-solid fa-location-dot"></i></div>
                            <div class="contact-detail">
                                <h3>Laboratory Address</h3>
                                <p><?php echo nl2br(htmlspecialchars($support_address)); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($support_phone !== ''): ?>
                        <div class="contact-item">
                            <div class="contact-icon"><i class="fa-solid fa-phone"></i></div>
                            <div class="contact-detail">
                                <h3>Helpline Number</h3>
                                <p><a href="tel:<?php echo htmlspecialchars($phone_href); ?>" class="contact-link"><?php echo htmlspecialchars($support_phone); ?></a></p>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($support_email !== ''): ?>
                        <div class="contact-item">
                            <div class="contact-icon"><i class="fa-solid fa-envelope"></i></div>
                            <div class="contact-detail">
                                <h3>Email Support</h3>
                                <p><a href="mailto:<?php echo htmlspecialchars($support_email); ?>" class="contact-link"><?php echo htmlspecialchars($support_email); ?></a></p>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($whatsapp_num !== ''): ?>
                        <div class="contact-item">
                            <div class="contact-icon contact-icon-whatsapp"><i class="fa-brands fa-whatsapp"></i></div>
                            <div class="contact-detail">
                                <h3>WhatsApp Support</h3>
                                <p><a href="https://wa.me/<?php echo htmlspecialchars($whatsapp_num); ?>?text=Hi,%20I%20have%20a%20query%20about%20my%20lab%20test." target="_blank" rel="noopener noreferrer" class="contact-link contact-link-whatsapp">Chat Live with Us</a></p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card contact-card">
                    <h3 class="contact-card-title"><i class="fa-solid fa-clock"></i> Operating Hours</h3>
                    <ul class="hours-list">
                        <li>
                            <strong><?php echo htmlspecialchars(cmsSetting($cms, 'footer_weekday_label', 'Mon - Sat:')); ?></strong>
                            <span><?php echo htmlspecialchars(cmsSetting($cms, 'working_hours_weekday', '07:00 AM - 09:00 PM')); ?></span>
                        </li>
                        <li>
                            <strong><?php echo htmlspecialchars(cmsSetting($cms, 'footer_sunday_label', 'Sunday:')); ?></strong>
                            <span><?php echo htmlspecialchars(cmsSetting($cms, 'working_hours_sunday', '07:00 AM - 02:00 PM')); ?></span>
                        </li>
                        <?php if ($home_collection_note !== ''): ?>
                        <li class="hours-note">
                            <i class="fa-solid fa-truck-medical"></i> <?php echo htmlspecialchars($home_collection_note); ?>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <div class="booking-form-card premium-form-card reveal reveal-delay-2">
                <div class="form-card-header">
                    <span class="form-card-icon"><i class="fa-solid fa-envelope-open-text"></i></span>
                    <h3>Diagnostic Inquiry Form</h3>
                    <p>We typically respond within 30 minutes during lab hours.</p>
                </div>
                <form id="inquiryForm">
                    <div class="form-group">
                        <label for="name" class="form-label">Your Full Name</label>
                        <input type="text" id="name" name="name" class="form-control" placeholder="Enter name" required>
                    </div>
                    <div class="form-group">
                        <label for="mobile" class="form-label">Mobile Number</label>
                        <input type="tel" id="mobile" name="mobile" class="form-control" placeholder="Enter 10-digit mobile" required>
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="name@example.com" required>
                    </div>
                    <div class="form-group">
                        <label for="subject" class="form-label">Inquiry Subject</label>
                        <input type="text" id="subject" name="subject" class="form-control" placeholder="e.g. Health packages, test discounts, report correction" required>
                    </div>
                    <div class="form-group">
                        <label for="message" class="form-label">Message Details</label>
                        <textarea id="message" name="message" class="form-control" placeholder="Write details about the tests you require, or any questions you have for our pathologists..." required></textarea>
                    </div>
                    <?php if (captchaEnabled($cms)) { echo renderCaptchaField(); } ?>
                    <button type="submit" class="btn btn-primary w-full">
                        <i class="fa-solid fa-paper-plane"></i> Submit Inquiry Details
                    </button>
                </form>
            </div>
        </div>

        <?php if ($maps_url !== ''): ?>
        <div class="map-container map-container-premium reveal">
            <iframe src="<?php echo htmlspecialchars($maps_url); ?>" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Laboratory location map"></iframe>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
