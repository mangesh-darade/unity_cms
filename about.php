<?php
include 'includes/db.php';

$page = cmsPage($cms_pages, 'about', [
    'meta_title' => 'About Us | Accreditations & Quality Standards',
    'meta_description' => 'Unity Clinical Laboratory is an ISO 9001:2015 and NABL aligned diagnostic pathology lab committed to accurate diagnostics, run by certified pathologists.',
]);
$cms_page_context = $page;
$active_nav = 'about';
$page_title = $page['meta_title'];
$meta_description = $page['meta_description'];
$meta_keywords = $page['meta_keywords'] ?? null;

include 'includes/header.php';

$blocks = cmsPageBlocks($db, 'about');
$intro = cmsPageBlocksByType($blocks, 'intro')[0] ?? null;
$badges = cmsPageBlocksByType($blocks, 'badge');
$headers = cmsPageBlocksByType($blocks, 'header');
$features = cmsPageBlocksByType($blocks, 'feature');
$team = cmsPageBlocksByType($blocks, 'team');
$valuesHeader = $headers[0] ?? null;
$teamHeader = $headers[1] ?? null;
?>

<?php renderPageHeader($page, 'About Our Laboratory', 'About Us'); ?>

<section class="section-padding">
    <div class="container">
        <div class="grid-2 align-center reveal">
            <div>
                <?php if ($intro): ?>
                    <?php if (!empty($intro['subtitle'])): ?><span class="section-tag"><?php echo htmlspecialchars($intro['subtitle']); ?></span><?php endif; ?>
                    <h2 class="section-title" style="text-align:left;margin-top:12px;"><?php echo htmlspecialchars($intro['title']); ?></h2>
                    <?php foreach (explode("\n\n", (string) $intro['content']) as $para): ?>
                        <?php if (trim($para) !== ''): ?><p class="section-desc" style="text-align:left;margin:16px 0;"><?php echo htmlspecialchars(trim($para)); ?></p><?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if (!empty($badges)): ?>
                <div class="reveal-stagger" style="display:flex;gap:15px;margin-top:30px;flex-wrap:wrap;">
                    <?php foreach ($badges as $badge): ?>
                    <div class="about-badge-card">
                        <h4><?php echo htmlspecialchars($badge['title']); ?></h4>
                        <p><?php echo htmlspecialchars($badge['content']); ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <div>
                <img src="<?php echo htmlspecialchars($intro['image_path'] ?? 'images/gallery/web/blood-collection.jpg'); ?>" alt="Clinical Laboratory Facility" class="about-intro-img">
            </div>
        </div>
    </div>
</section>

<?php if ($valuesHeader || !empty($features)): ?>
<section class="section-padding section-alt">
    <div class="container">
        <div class="section-header">
            <?php if ($valuesHeader): ?>
                <span class="section-tag"><?php echo htmlspecialchars($valuesHeader['title']); ?></span>
                <h2 class="section-title"><?php echo htmlspecialchars($valuesHeader['subtitle']); ?></h2>
                <p class="section-desc max-w-md"><?php echo htmlspecialchars($valuesHeader['content']); ?></p>
            <?php endif; ?>
        </div>
        <div class="grid-4 reveal-stagger">
            <?php foreach ($features as $feature): ?>
            <div class="card" style="text-align:center;padding:30px 20px;">
                <div class="service-icon"><i class="<?php echo htmlspecialchars($feature['icon'] ?: 'fa-solid fa-circle-check'); ?>"></i></div>
                <h3 style="font-size:1.2rem;margin:16px 0 10px;"><?php echo htmlspecialchars($feature['title']); ?></h3>
                <p style="font-size:0.9rem;color:var(--text-muted);"><?php echo htmlspecialchars($feature['content']); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($teamHeader || !empty($team)): ?>
<section class="section-padding">
    <div class="container">
        <div class="section-header">
            <?php if ($teamHeader): ?>
                <span class="section-tag"><?php echo htmlspecialchars($teamHeader['title']); ?></span>
                <h2 class="section-title"><?php echo htmlspecialchars($teamHeader['subtitle']); ?></h2>
                <p class="section-desc max-w-md"><?php echo htmlspecialchars($teamHeader['content']); ?></p>
            <?php endif; ?>
        </div>
        <div class="grid-3 reveal-stagger">
            <?php foreach ($team as $member): ?>
            <div class="card team-card">
                <div class="author-avatar team-avatar"><?php echo htmlspecialchars($member['icon'] ?: substr($member['title'], 0, 2)); ?></div>
                <div>
                    <h3 style="font-size:1.3rem;margin-bottom:4px;"><?php echo htmlspecialchars($member['title']); ?></h3>
                    <p style="color:var(--brand-teal);font-weight:600;font-size:0.9rem;margin-bottom:10px;"><?php echo htmlspecialchars($member['subtitle']); ?></p>
                    <p style="color:var(--text-muted);font-size:0.9rem;line-height:1.6;"><?php echo htmlspecialchars($member['content']); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
