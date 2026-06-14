<?php
/** Icon cards grid homepage section. Expects $current_home_section. */
$code = $current_home_section['section_code'] ?? '';
$items = cmsSectionItems($db, $code);
if (empty($items)) {
    return;
}
?>
<section class="choose-us-section section-padding cms-dynamic-section">
    <div class="container">
        <?php renderSectionHeader($cms_sections, $code, ['tag' => '', 'title' => '', 'desc' => '']); ?>
        <div class="grid-3">
            <?php foreach ($items as $item): ?>
            <div class="card choose-card">
                <div class="choose-icon"><i class="<?php echo htmlspecialchars($item['icon'] ?: 'fa-solid fa-circle-check'); ?>"></i></div>
                <div>
                    <h3 class="choose-title"><?php echo htmlspecialchars($item['title']); ?></h3>
                    <?php if (!empty($item['subtitle'])): ?>
                    <p style="font-size:0.85rem;color:var(--brand-teal);margin-bottom:6px;"><?php echo htmlspecialchars($item['subtitle']); ?></p>
                    <?php endif; ?>
                    <p class="choose-desc"><?php echo htmlspecialchars($item['description']); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
