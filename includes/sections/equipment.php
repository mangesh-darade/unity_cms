<?php
// Fetch laboratory equipment ordered by sequence
$home_equipment = $db->query("SELECT * FROM cms_equipment ORDER BY sequence ASC")->fetchAll();
?>
<!-- Laboratory Equipment Section -->
<section class="equipment-section section-padding">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Clinical Equipment</span>
            <h2 class="section-title">Our Advanced Clinical Laboratory Infrastructure</h2>
            <p class="section-desc max-w-md">We process patient samples using state-of-the-art automated diagnostic machinery for high speed and accuracy.</p>
        </div>
        
        <div class="equipment-grid">
            <?php foreach ($home_equipment as $eq): ?>
                <div class="equipment-card">
                    <img src="<?php echo htmlspecialchars($eq['image_path']); ?>" alt="<?php echo htmlspecialchars($eq['title']); ?>" class="equipment-img">
                    <div class="equipment-body">
                        <h3><?php echo htmlspecialchars($eq['title']); ?></h3>
                        <p><?php echo htmlspecialchars($eq['description'] ?? ''); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
