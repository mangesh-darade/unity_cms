<?php
include 'includes/db.php';

$page = cmsPage($cms_pages, 'blog', [
    'meta_title' => 'Health Blog & Diagnostics Articles | Pathologists Advice',
    'meta_description' => 'Read diagnostic medical insights and preventative healthcare articles from our pathologists.',
]);
$cms_page_context = $page;
$active_nav = 'blog';
$page_title = $page['meta_title'];
$meta_description = $page['meta_description'];
$meta_keywords = $page['meta_keywords'] ?? null;

include 'includes/header.php';

try {
    $blogs = $db->query("SELECT * FROM cms_blogs ORDER BY id DESC")->fetchAll();
} catch (PDOException $e) {
    $blogs = [];
}
?>

<?php renderPageHeader($page, 'Health & Diagnostics Blog', 'Health Blog'); ?>

<section class="section-padding">
    <div class="container">
        <?php renderSectionHeader($cms_sections, 'blog', [
            'tag' => $page['content_tag'] ?: 'Health Education',
            'title' => $page['content_title'] ?: 'Latest Medical & Diagnostic Articles',
            'desc' => $page['content_description'] ?: "Understand your body better with certified pathologists' medical tips and guides.",
        ]); ?>

        <div class="grid-3 reveal-stagger blog-grid">
            <?php if (empty($blogs)): ?>
                <div class="empty-state"><i class="fa-regular fa-newspaper"></i><p>No articles published yet.</p></div>
            <?php else: ?>
                <?php foreach ($blogs as $post): ?>
                    <article class="blog-card blog-card-premium">
                        <div class="blog-img-wrap">
                            <img src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="blog-img" loading="lazy">
                        </div>
                        <div class="blog-content">
                            <div class="blog-meta">
                                <span><i class="fa-regular fa-calendar"></i> <?php echo date('d M Y', strtotime($post['created_at'])); ?></span>
                                <span><i class="fa-regular fa-user"></i> <?php echo htmlspecialchars($post['author']); ?></span>
                            </div>
                            <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                            <p><?php echo htmlspecialchars($post['summary'] ?? ''); ?></p>
                            <a href="blog-post.php?id=<?php echo (int) $post['id']; ?>" class="blog-more">Read Article <i class="fa-solid fa-arrow-right-long"></i></a>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
