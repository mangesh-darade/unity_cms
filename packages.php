<?php
include 'includes/db.php';

$active_nav = 'packages';
$page_title = "Preventive Health Packages | Comprehensive Health Checkups";
$meta_description = "Choose from our 5+ preventive health packages. Comprehensive blood screening, lipid checks, liver & kidney tests at Unity Lab starting from ₹499.";

include 'includes/header.php';

// Fetch all packages ordered by sequence
try {
    $packages = $db->query("SELECT * FROM cms_packages ORDER BY sequence ASC")->fetchAll();
} catch (PDOException $e) {
    $packages = [];
}
?>

<!-- Page Title Header -->
<div class="page-header">
    <div class="container">
        <h1>Preventive Health Packages</h1>
        <div class="breadcrumb">
            <a href="index.php">Home</a> &nbsp;/&nbsp; Health Packages
        </div>
    </div>
</div>

<!-- Health Packages Section -->
<section class="section-padding">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Wellness Packages</span>
            <h2 class="section-title">Select the Best Health Package for You</h2>
            <p class="section-desc max-w-md">Regular clinical screenings are the foundation of healthy living. Select a package and schedule a home collection appointment today.</p>
        </div>
        
        <div class="grid-3" style="align-items: stretch; gap: 30px;">
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
                        <div class="package-price">₹<?php echo number_format($package['price']); ?> <span>/ Only</span></div>
                        
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
