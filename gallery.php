<?php
include 'includes/db.php';

$active_nav = 'gallery';
$page_title = "Laboratory Gallery | Diagnostic Facilities & Equipment";
$meta_description = "Take a virtual tour of Unity Clinical Laboratory. Explore our sterile diagnostics space, NABL collection desk, and advanced blood analyzers.";

include 'includes/header.php';

// Fetch all gallery items ordered by sequence
try {
    $gallery = $db->query("SELECT * FROM cms_gallery ORDER BY sequence ASC")->fetchAll();
} catch (PDOException $e) {
    $gallery = [];
}
?>

<!-- Page Title Header -->
<div class="page-header">
    <div class="container">
        <h1>Laboratory Gallery</h1>
        <div class="breadcrumb">
            <a href="index.php">Home</a> &nbsp;/&nbsp; Laboratory Gallery
        </div>
    </div>
</div>

<!-- Gallery Section -->
<section class="section-padding">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Facilities Showcase</span>
            <h2 class="section-title">Tour Our Advanced Clinical Laboratory</h2>
            <p class="section-desc max-w-md">We maintain pristine sterility and premium technology across all diagnostic sample departments.</p>
        </div>
        
        <div class="gallery-grid" style="grid-template-columns: repeat(3, 1fr); gap: 24px;">
            <?php if (empty($gallery)): ?>
                <div class="text-center" style="grid-column: 1 / -1; padding: 40px;">No gallery items found.</div>
            <?php else: ?>
                <?php foreach ($gallery as $item): ?>
                    <div class="gallery-item" style="height: 280px;">
                        <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                        <div class="gallery-overlay">
                            <div>
                                <h3 style="font-size: 1.25rem; font-weight: 700;"><?php echo htmlspecialchars($item['title']); ?></h3>
                                <p style="font-size: 0.85rem; color: var(--border);"><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php
include 'includes/footer.php';
?>
