<?php
include 'includes/db.php';

$page = cmsPage($cms_pages, 'packages', [
    'meta_title' => 'Preventive Health Packages | Comprehensive Health Checkups',
    'meta_description' => 'Choose from our preventive health packages. Comprehensive blood screening at Unity Lab.',
]);
$cms_page_context = $page;
$active_nav = 'packages';
$page_title = $page['meta_title'];
$meta_description = $page['meta_description'];
$meta_keywords = $page['meta_keywords'] ?? null;

include 'includes/header.php';

// Fetch all packages ordered by sequence
try {
    $packages = $db->query("SELECT * FROM cms_packages ORDER BY sequence ASC")->fetchAll();
} catch (PDOException $e) {
    $packages = [];
}
?>

<?php renderPageHeader($page, 'Preventive Health Packages', 'Health Packages'); ?>

<!-- Health Packages Section -->
<section class="section-padding">
    <div class="container">
        <?php renderPageSectionHeader($page, [
            'tag' => 'Wellness Packages',
            'title' => 'Select the Best Health Package for You',
            'desc' => 'Package prices are based on our official rate card. Book online or call us for home collection.',
        ]); ?>
        
        <div class="grid-3 reveal-stagger" style="align-items: stretch; gap: 30px;">
            <?php if (empty($packages)): ?>
                <div class="text-center" style="grid-column: 1 / -1; padding: 40px;">No health packages found.</div>
            <?php else: ?>
                <?php foreach ($packages as $package): 
                    $is_featured = (int)$package['is_featured'] === 1;
                    $features = array_filter(explode("\n", $package['features'] ?? ''));
                ?>
                    <div class="package-card <?php echo $is_featured ? 'featured' : ''; ?>">
                        <?php if ($is_featured): ?>
                            <div class="package-badge">RECOMMENDED</div>
                        <?php endif; ?>
                        <h3 class="package-name"><?php echo htmlspecialchars($package['name']); ?></h3>
                        <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 15px;"><?php echo htmlspecialchars($package['description'] ?? ''); ?></p>
                        <div class="package-price">₹<?php echo number_format($package['price']); ?> <span>/ package</span></div>
                        
                        <h4 style="font-size: 0.95rem; margin-bottom: 12px; color: var(--primary-light);"><i class="fa-solid fa-list-check"></i> Includes tests:</h4>
                        <ul class="package-features" style="margin-bottom: 30px;">
                            <?php foreach ($features as $feat): ?>
                                <li><?php echo htmlspecialchars(trim($feat)); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <a href="collection.php?package=<?php echo urlencode(strtolower(str_replace([' ', "'", '"'], '', $package['name']))); ?>" class="btn <?php echo $is_featured ? 'btn-primary' : 'btn-secondary'; ?> w-full text-center" style="margin-top: auto;">Book Appointment</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php
include 'includes/footer.php';
?>
