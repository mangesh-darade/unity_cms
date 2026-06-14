<?php
// Fetch top packages ordered by sequence
$home_packages = $db->query("SELECT * FROM cms_packages ORDER BY sequence ASC LIMIT 3")->fetchAll();
?>
<!-- Health Packages Section -->
<section class="packages-section section-padding">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Value Health Plans</span>
            <h2 class="section-title">Affordable Health Checkup Packages</h2>
            <p class="section-desc max-w-md">Select from our specially curated health packages for comprehensive wellness monitoring.</p>
        </div>
        <div class="grid-3">
            <?php foreach ($home_packages as $package): 
                $is_featured = (int)$package['is_featured'] === 1;
                // Parse features list separated by newlines
                $features = array_filter(explode("\n", $package['features'] ?? ''));
            ?>
                <div class="package-card <?php echo $is_featured ? 'featured' : ''; ?>">
                    <?php if ($is_featured): ?>
                        <div class="package-badge">POPULAR</div>
                    <?php endif; ?>
                    <h3 class="package-name"><?php echo htmlspecialchars($package['name']); ?></h3>
                    <div class="package-price">₹<?php echo number_format($package['price']); ?> <span>/ Only</span></div>
                    <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 20px;"><?php echo htmlspecialchars($package['description'] ?? ''); ?></p>
                    
                    <ul class="package-features">
                        <?php foreach ($features as $feature): ?>
                            <li><?php echo htmlspecialchars(trim($feature)); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="collection.php?package=<?php echo urlencode(strtolower(str_replace([' ', "'", '"'], '', $package['name']))); ?>" class="btn <?php echo $is_featured ? 'btn-primary' : 'btn-secondary'; ?> text-center">Book Now</a>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center" style="margin-top: 40px;">
            <a href="packages.php" class="btn btn-teal">Explore All Packages <i class="fa-solid fa-arrow-right"></i></a>
        </div>
    </div>
</section>
