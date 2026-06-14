<!-- Hero Section -->
<section class="hero-section" style="background: linear-gradient(135deg, rgba(15, 23, 42, 0.95), rgba(15, 23, 42, 0.8)), url('<?php echo htmlspecialchars($cms['hero_bg_image'] ?? 'images/hero-lab.jpg'); ?>') no-repeat center center / cover;">
    <div class="container">
        <div class="hero-content">
            <span class="hero-tag"><i class="fa-solid fa-square-check"></i> <?php echo htmlspecialchars($cms['hero_tagline'] ?? 'NABL Certified Laboratory & Diagnostic Center'); ?></span>
            <h1 class="hero-title"><?php echo nl2br(htmlspecialchars($cms['hero_headline'] ?? 'Accurate Diagnostics. Trusted Results.')); ?></h1>
            <p class="hero-desc"><?php echo htmlspecialchars($cms['hero_subheadline'] ?? 'Advanced Blood, Urine and Health Diagnostic Testing with Fast & Reliable Reports.'); ?></p>
            <div class="hero-actions">
                <a href="collection.php" class="btn btn-primary"><i class="fa-solid fa-calendar-check"></i> Book Home Test</a>
                <a href="download.php" class="btn btn-teal"><i class="fa-solid fa-file-arrow-down"></i> Download Report</a>
                <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $cms['support_phone']); ?>" class="btn btn-secondary"><i class="fa-solid fa-phone"></i> Call Now</a>
                <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $cms['whatsapp_number']); ?>?text=Hi,%20I'd%20like%20to%20book%20a%20diagnostic%20test." target="_blank" class="btn btn-whatsapp"><i class="fa-brands fa-whatsapp"></i> WhatsApp</a>
            </div>
        </div>
    </div>
</section>
