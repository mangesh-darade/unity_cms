<?php
include 'includes/db.php';

$page = cmsPage($cms_pages, 'gallery', [
    'meta_title' => 'Laboratory Gallery | Diagnostic Facilities & Equipment',
    'meta_description' => 'Take a virtual tour of Unity Clinical Laboratory facilities and equipment.',
]);
$cms_page_context = $page;
$active_nav = 'gallery';
$page_title = $page['meta_title'];
$meta_description = $page['meta_description'];
$meta_keywords = $page['meta_keywords'] ?? null;

include 'includes/header.php';

try {
    $gallery = $db->query("SELECT * FROM cms_gallery ORDER BY sequence ASC")->fetchAll();
} catch (PDOException $e) {
    $gallery = [];
}
?>

<?php renderPageHeader($page, 'Laboratory Gallery', 'Laboratory Gallery'); ?>

<section class="section-padding section-alt">
    <div class="container">
        <?php renderSectionHeader($cms_sections, 'gallery', [
            'tag' => $page['content_tag'] ?: 'Inside Our Lab',
            'title' => $page['content_title'] ?: 'Laboratory Gallery',
            'desc' => $page['content_description'] ?: 'Take a look at our clean diagnostic environment and sample processing areas.',
        ]); ?>

        <div class="gallery-grid gallery-grid-premium reveal-stagger">
            <?php if (empty($gallery)): ?>
                <div class="empty-state"><i class="fa-regular fa-images"></i><p>No gallery items found.</p></div>
            <?php else: ?>
                <?php foreach ($gallery as $item): ?>
                    <?php
                    $seq = (int) ($item['sequence'] ?? 0);
                    $isEquipment = $seq >= 10;
                    $tagLabel = $isEquipment ? 'Lab Equipment' : 'Lab Facility';
                    $tagIcon = $isEquipment ? 'fa-solid fa-vials' : 'fa-solid fa-microscope';
                    ?>
                    <article class="gallery-item gallery-item-premium">
                        <div class="gallery-item-media">
                            <img src="<?php echo htmlspecialchars($item['image_path']); ?>"
                                 alt="<?php echo htmlspecialchars($item['title']); ?>"
                                 loading="lazy"
                                 width="960"
                                 height="720">
                        </div>
                        <div class="gallery-overlay">
                            <div class="gallery-overlay-inner">
                                <span class="gallery-tag"><i class="<?php echo $tagIcon; ?>"></i> <?php echo $tagLabel; ?></span>
                                <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                                <?php if (!empty($item['description'])): ?>
                                <p><?php echo htmlspecialchars($item['description']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
