<!-- Why Choose Us Section -->
<?php
$choose_items = cmsSectionItems($db, 'why_choose_us');
?>
<section class="choose-us-section section-padding">
    <div class="container">
        <?php renderSectionHeader($cms_sections, 'why_choose_us', [
            'tag' => 'Our Strengths',
            'title' => 'Why Patients & Doctors Trust Unity Lab',
            'desc' => 'We offer state-of-the-art diagnostics with a focus on precision, convenience, and affordability.',
        ]); ?>
        <div class="grid-3 reveal-stagger">
            <?php foreach ($choose_items as $item): ?>
            <div class="card choose-card">
                <div class="choose-icon"><i class="<?php echo htmlspecialchars($item['icon'] ?: 'fa-solid fa-circle-check'); ?>"></i></div>
                <div>
                    <h3 class="choose-title"><?php echo htmlspecialchars($item['title']); ?></h3>
                    <p class="choose-desc"><?php echo htmlspecialchars($item['description']); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
