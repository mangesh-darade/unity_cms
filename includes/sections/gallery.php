<?php
// Fetch gallery items ordered by sequence
$home_gallery = $db->query("SELECT * FROM cms_gallery ORDER BY sequence ASC LIMIT 4")->fetchAll();
?>
<!-- Gallery Section -->
<section class="gallery-section section-padding">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Inside Unity Lab</span>
            <h2 class="section-title">Our Laboratory Gallery</h2>
            <p class="section-desc max-w-md">Take a look inside our clean, professional, and accredited diagnostic environment.</p>
        </div>
        
        <div class="gallery-grid">
            <?php foreach ($home_gallery as $item): ?>
                <div class="gallery-item">
                    <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                    <div class="gallery-overlay">
                        <div>
                            <h4 style="font-size: 1.1rem; font-weight: 600;"><?php echo htmlspecialchars($item['title']); ?></h4>
                            <p style="font-size: 0.85rem;"><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
