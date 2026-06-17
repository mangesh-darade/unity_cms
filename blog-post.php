<?php
include 'includes/db.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header('Location: blog.php');
    exit();
}

try {
    $stmt = $db->prepare("SELECT * FROM cms_blogs WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $post = $stmt->fetch();
} catch (PDOException $e) {
    $post = false;
}

if (!$post) {
    header('Location: blog.php');
    exit();
}

$active_nav = 'blog';
$page_title = $post['title'] . ' | Health Blog';
$meta_description = $post['summary'] ?? substr(strip_tags($post['content'] ?? ''), 0, 160);
$og_image = $post['image_path'] ?? null;
$og_type = 'article';
$canonical_url = rtrim(BASE_URL, '/') . '/blog-post.php?id=' . (int) $post['id'];

include 'includes/header.php';
?>

<?php renderCustomPageHeader($post['title'], [
    ['label' => cmsSetting($cms, 'breadcrumb_home_label', 'Home'), 'url' => 'index.php'],
    ['label' => 'Health Blog', 'url' => 'blog.php'],
    ['label' => 'Article', 'url' => ''],
]); ?>

<section class="section-padding">
    <div class="container container-article">
        <article class="card blog-article-card reveal">
            <div class="blog-article-hero">
                <img src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
            </div>
            <div class="blog-article-body">
                <div class="blog-meta blog-meta-article">
                    <span><i class="fa-regular fa-calendar"></i> <?php echo date('d M Y', strtotime($post['created_at'])); ?></span>
                    <span><i class="fa-regular fa-user"></i> <?php echo htmlspecialchars($post['author']); ?></span>
                </div>
                <?php if (!empty($post['summary'])): ?>
                    <p class="blog-article-summary"><?php echo htmlspecialchars($post['summary']); ?></p>
                <?php endif; ?>
                <div class="blog-article-content"><?php echo nl2br(htmlspecialchars($post['content'] ?? '')); ?></div>
            </div>
        </article>

        <div class="blog-article-back reveal">
            <a href="blog.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back to All Articles</a>
        </div>
    </div>
</section>

<?php
renderJsonLd(cmsArticleSchema($cms, $post));
renderJsonLd(cmsBreadcrumbSchema([
    ['label' => cmsSetting($cms, 'breadcrumb_home_label', 'Home'), 'url' => 'index.php'],
    ['label' => 'Health Blog', 'url' => 'blog.php'],
    ['label' => $post['title'], 'url' => ''],
]));

include 'includes/footer.php';
