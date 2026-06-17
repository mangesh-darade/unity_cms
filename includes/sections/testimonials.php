<?php
require_once __DIR__ . '/../captcha.php';

// Only show approved reviews on the public site
$home_testimonials = $db->query(
    "SELECT * FROM cms_testimonials WHERE status = 'approved' OR status IS NULL OR status = '' ORDER BY sequence ASC"
)->fetchAll();

function getInitials($name) {
    $words = explode(" ", $name);
    $initials = "";
    foreach ($words as $w) {
        $initials .= strtoupper(substr($w, 0, 1));
    }
    return substr($initials, 0, 2);
}
?>
<!-- Patient Testimonials Section -->
<section class="testimonials-section section-padding" id="reviews">
    <div class="container">
        <?php renderSectionHeader($cms_sections, 'testimonials', [
            'tag' => 'Reviews',
            'title' => 'What Our Patients Say',
            'desc' => 'Read testimonials from patients who experienced our prompt home collection and accurate reporting services.',
        ]); ?>
        
        <?php if (!empty($home_testimonials)): ?>
        <div class="testimonials-carousel reveal-stagger">
            <?php foreach ($home_testimonials as $test): ?>
                <div class="testimonial-card">
                    <p class="testimonial-text">"<?php echo htmlspecialchars($test['text']); ?>"</p>
                    <div class="testimonial-author">
                        <div class="author-avatar"><?php echo getInitials($test['author']); ?></div>
                        <div class="author-info">
                            <h4><?php echo htmlspecialchars($test['author']); ?></h4>
                            <span><?php echo htmlspecialchars($test['designation'] ?? 'Patient'); ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="review-submit-box reveal">
            <div class="review-submit-header">
                <h3>Share Your Experience</h3>
                <p>Tell us about your visit or home collection experience. Your review will appear on this page after our team verifies it.</p>
            </div>
            <form id="reviewForm" class="review-form">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Your Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="Full name" maxlength="120">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email <span class="required">*</span></label>
                        <input type="email" name="email" class="form-control" required placeholder="name@example.com" maxlength="180">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Mobile <span class="required">*</span></label>
                        <input type="tel" name="mobile" class="form-control" required placeholder="10-digit mobile" pattern="[0-9]{10}" maxlength="10">
                    </div>
                    <div class="form-group">
                        <label class="form-label">You are a</label>
                        <input type="text" name="designation" class="form-control" placeholder="e.g. Patient, Family member" maxlength="80">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Your Review <span class="required">*</span></label>
                    <textarea name="review" class="form-control" rows="4" required minlength="20" maxlength="2000" placeholder="Share your experience with our lab services, report quality, home collection, or staff..."></textarea>
                </div>
                <?php if (captchaEnabled($cms)) { echo renderCaptchaField(); } ?>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Submit Review</button>
            </form>
        </div>
    </div>
</section>
