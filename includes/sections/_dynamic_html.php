<?php
/** Custom HTML homepage section (CMS-managed). Expects $current_home_section. */
$code = $current_home_section['section_code'] ?? '';
?>
<section class="section-padding cms-dynamic-section">
    <div class="container">
        <?php renderSectionHeader($cms_sections, $code, ['tag' => '', 'title' => '', 'desc' => '']); ?>
        <?php if (!empty($current_home_section['section_body'])): ?>
        <div class="cms-custom-section card" style="padding: 30px; line-height: 1.8;">
            <?php echo $current_home_section['section_body']; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
