<?php
// Fetch gallery items ordered by sequence
$home_gallery = $db->query("SELECT * FROM cms_gallery ORDER BY sequence ASC LIMIT 4")->fetchAll();
?>
<!-- Gallery Section -->
<section class="gallery-section section-padding">
    <div class="container">
        <?php renderSectionHeader($cms_sections, 'gallery', [
            'tag' => 'Inside Our Lab',
            'title' => 'Our Laboratory Gallery',
            'desc' => 'Take a look at our clean diagnostic environment and sample processing areas.',
        ]); ?>
        
        <div class="gallery-grid gallery-grid-home">
            <?php foreach ($home_gallery as $item): ?>
                <a href="gallery.php" class="gallery-item gallery-item-home">
                    <div class="gallery-item-media">
                        <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" loading="lazy" width="960" height="720">
                    </div>
                    <div class="gallery-overlay">
                        <div class="gallery-overlay-inner">
                            <h4><?php echo htmlspecialchars($item['title']); ?></h4>
                            <?php if (!empty($item['description'])): ?>
                            <p><?php echo htmlspecialchars($item['description']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
