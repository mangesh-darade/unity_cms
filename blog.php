<?php
include 'includes/db.php';

$active_nav = 'blog';
$page_title = "Health Blog & Diagnostics Articles | Pathologists Advice";
$meta_description = "Read diagnostic medical insights, blood test advice, and preventative healthcare articles compiled by our expert clinical pathologists.";

include 'includes/header.php';

// Fetch all blogs
try {
    $blogs = $db->query("SELECT * FROM cms_blogs ORDER BY id DESC")->fetchAll();
} catch (PDOException $e) {
    $blogs = [];
}
?>

<!-- Page Title Header -->
<div class="page-header">
    <div class="container">
        <h1>Health & Diagnostics Blog</h1>
        <div class="breadcrumb">
            <a href="index.php">Home</a> &nbsp;/&nbsp; Health Blog
        </div>
    </div>
</div>

<!-- Blog Listing Grid -->
<section class="section-padding">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Health Education</span>
            <h2 class="section-title">Latest Medical & Diagnostic Articles</h2>
            <p class="section-desc max-w-md">Understand your body better by reading our certified pathologists' medical tips and guides.</p>
        </div>
        
        <div class="grid-3">
            <?php if (empty($blogs)): ?>
                <div class="text-center" style="grid-column: 1 / -1; padding: 40px;">No articles published yet.</div>
            <?php else: ?>
                <?php foreach ($blogs as $post): ?>
                    <article class="blog-card">
                        <img src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="blog-img">
                        <div class="blog-content">
                            <div class="blog-meta">
                                <span><i class="fa-regular fa-calendar"></i> <?php echo date('d M Y', strtotime($post['created_at'])); ?></span>
                                <span><i class="fa-regular fa-user"></i> <?php echo htmlspecialchars($post['author']); ?></span>
                            </div>
                            <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                            <p><?php echo htmlspecialchars($post['summary'] ?? ''); ?></p>
                            <a href="#" class="blog-more" style="margin-top: auto; display: inline-flex; align-items: center; gap: 5px;">Read Article <i class="fa-solid fa-arrow-right-long"></i></a>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php
include 'includes/footer.php';
?>
