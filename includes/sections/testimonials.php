<?php
// Fetch testimonials ordered by sequence
$home_testimonials = $db->query("SELECT * FROM cms_testimonials ORDER BY sequence ASC")->fetchAll();

// Avatar helper
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
<section class="testimonials-section section-padding">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Reviews</span>
            <h2 class="section-title">What Our Patients Say</h2>
            <p class="section-desc max-w-md">Read testimonials from patients who experienced our prompt home collection and accurate reporting services.</p>
        </div>
        
        <div class="testimonials-carousel">
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
    </div>
</section>
