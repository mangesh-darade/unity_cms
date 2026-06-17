<!-- Hero Section — Premium -->
<?php
$heroBg = $cms['hero_bg_image'] ?? 'images/gallery/web/blood-collection.jpg';
$heroBgFile = dirname(__DIR__, 2) . '/' . ltrim($heroBg, '/');
if (is_file($heroBgFile)) {
    $heroBg .= '?v=' . filemtime($heroBgFile);
}
?>
<section class="hero-section hero-premium">
    <div class="hero-bg" aria-hidden="true">
        <div class="hero-bg-image" style="background-image: url('<?php echo htmlspecialchars($heroBg); ?>');"></div>
        <div class="hero-bg-gradient"></div>
        <div class="hero-orb hero-orb-1"></div>
        <div class="hero-orb hero-orb-2"></div>
        <div class="hero-orb hero-orb-3"></div>
        <div class="hero-grid-pattern"></div>
    </div>

    <div class="container hero-grid">
        <div class="hero-content reveal">
            <span class="hero-tag">
                <span class="hero-tag-dot"></span>
                <?php echo htmlspecialchars($cms['hero_tagline'] ?? 'NABL Aligned Laboratory & Diagnostic Center'); ?>
            </span>
            <h1 class="hero-title"><?php echo nl2br(htmlspecialchars($cms['hero_headline'] ?? 'Accurate Diagnostics. Trusted Results.')); ?></h1>
            <p class="hero-desc"><?php echo htmlspecialchars($cms['hero_subheadline'] ?? 'Advanced Blood, Urine and Health Diagnostic Testing with Fast & Reliable Reports.'); ?></p>

            <div class="hero-cta-block">
                <div class="hero-cta-primary">
                    <a href="<?php echo htmlspecialchars(cmsSetting($cms, 'hero_btn_book_url', 'collection.php')); ?>" class="btn btn-primary btn-lg btn-glow">
                        <i class="fa-solid fa-calendar-check"></i>
                        <?php echo htmlspecialchars(cmsSetting($cms, 'hero_btn_book_text', 'Book Home Test')); ?>
                    </a>
                    <a href="<?php echo htmlspecialchars(cmsSetting($cms, 'hero_btn_download_url', 'download.php')); ?>" class="btn btn-glass btn-lg">
                        <i class="fa-solid fa-file-arrow-down"></i>
                        <?php echo htmlspecialchars(cmsSetting($cms, 'hero_btn_download_text', 'Download Report')); ?>
                    </a>
                </div>
                <?php if (cmsSetting($cms, 'support_phone') !== '' || cmsSetting($cms, 'whatsapp_number') !== ''): ?>
                <div class="hero-cta-quick">
                    <span class="hero-cta-quick-label">Quick contact</span>
                    <div class="hero-cta-quick-links">
                        <?php if (cmsSetting($cms, 'support_phone') !== ''): ?>
                        <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $cms['support_phone']); ?>" class="hero-quick-link">
                            <i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars(cmsSetting($cms, 'hero_btn_call_text', 'Call Now')); ?>
                        </a>
                        <?php endif; ?>
                        <?php if (cmsSetting($cms, 'whatsapp_number') !== ''): ?>
                        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $cms['whatsapp_number']); ?>?text=<?php echo urlencode(cmsSetting($cms, 'hero_whatsapp_message', "Hi, I'd like to book a diagnostic test.")); ?>" target="_blank" rel="noopener noreferrer" class="hero-quick-link hero-quick-link-wa">
                            <i class="fa-brands fa-whatsapp"></i> WhatsApp
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="hero-trust-row">
                <?php if ($b1 = cmsSetting($cms, 'footer_badge_1')): ?><span class="hero-trust-badge"><i class="fa-solid fa-shield-halved"></i> <?php echo htmlspecialchars($b1); ?></span><?php endif; ?>
                <?php if ($b2 = cmsSetting($cms, 'footer_badge_2')): ?><span class="hero-trust-badge"><i class="fa-solid fa-award"></i> <?php echo htmlspecialchars($b2); ?></span><?php endif; ?>
                <?php if ($t3 = cmsSetting($cms, 'hero_trust_3_text')): ?>
                <span class="hero-trust-badge"><i class="<?php echo htmlspecialchars(cmsSetting($cms, 'hero_trust_3_icon', 'fa-solid fa-bolt')); ?>"></i> <?php echo htmlspecialchars($t3); ?></span>
                <?php endif; ?>
                <?php if ($t4 = cmsSetting($cms, 'hero_trust_4_text')): ?>
                <span class="hero-trust-badge"><i class="<?php echo htmlspecialchars(cmsSetting($cms, 'hero_trust_4_icon', 'fa-solid fa-house-medical')); ?>"></i> <?php echo htmlspecialchars($t4); ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="hero-visual reveal reveal-delay-2">
            <div class="hero-glass-panel">
                <div class="hero-panel-header">
                    <span class="hero-panel-label"><i class="fa-solid fa-chart-line"></i> <?php echo htmlspecialchars(cmsSetting($cms, 'hero_panel_label', 'Lab Excellence')); ?></span>
                    <span class="hero-panel-live"><span class="pulse-dot"></span> <?php echo htmlspecialchars(cmsSetting($cms, 'hero_panel_live', 'Open Now')); ?></span>
                </div>
                <div class="hero-stats-grid">
                    <?php for ($s = 1; $s <= 4; $s++):
                        $val = cmsSetting($cms, 'hero_stat_' . $s . '_value');
                        $lbl = cmsSetting($cms, 'hero_stat_' . $s . '_label');
                        if ($val === '' && $lbl === '') continue;
                    ?>
                    <div class="hero-stat">
                        <strong><?php echo htmlspecialchars($val); ?></strong>
                        <span><?php echo htmlspecialchars($lbl); ?></span>
                    </div>
                    <?php endfor; ?>
                </div>
                <div class="hero-panel-footer">
                    <div class="hero-panel-contact">
                        <i class="fa-solid fa-headset"></i>
                        <div>
                            <small><?php echo htmlspecialchars(cmsSetting($cms, 'hero_panel_help', 'Need help booking?')); ?></small>
                            <strong><?php echo htmlspecialchars(cmsSetting($cms, 'support_phone')); ?></strong>
                        </div>
                    </div>
                    <a href="<?php echo htmlspecialchars(cmsSetting($cms, 'hero_btn_book_url', 'collection.php')); ?>" class="btn btn-primary btn-sm">Book Now</a>
                </div>
            </div>

            <?php if ($f1t = cmsSetting($cms, 'hero_float_1_title')): ?>
            <div class="hero-float-card hero-float-card-1">
                <i class="<?php echo htmlspecialchars(cmsSetting($cms, 'hero_float_1_icon', 'fa-solid fa-microscope')); ?>"></i>
                <div>
                    <strong><?php echo htmlspecialchars($f1t); ?></strong>
                    <span><?php echo htmlspecialchars(cmsSetting($cms, 'hero_float_1_desc')); ?></span>
                </div>
            </div>
            <?php endif; ?>
            <?php if ($f2t = cmsSetting($cms, 'hero_float_2_title')): ?>
            <div class="hero-float-card hero-float-card-2">
                <i class="<?php echo htmlspecialchars(cmsSetting($cms, 'hero_float_2_icon', 'fa-solid fa-user-doctor')); ?>"></i>
                <div>
                    <strong><?php echo htmlspecialchars($f2t); ?></strong>
                    <span><?php echo htmlspecialchars(cmsSetting($cms, 'hero_float_2_desc')); ?></span>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="hero-scroll-hint" aria-hidden="true">
        <span>Scroll</span>
        <i class="fa-solid fa-chevron-down"></i>
    </div>
</section>
