<?php
include 'includes/db.php';

$page = cmsPage($cms_pages, 'about', [
    'meta_title' => 'About Us | Our Team & Laboratory Equipment',
    'meta_description' => 'Meet Akshay Sanjay Rakh, founder of Unity Clinical Laboratory, consulting doctors Dr. Shubham Shirke & Dr. Akash Trimbake, and our Sysmex, Orbit, Remi & Labomed equipment in Maharashtra.',
]);
$cms_page_context = $page;
$active_nav = 'about';
$page_title = $page['meta_title'];
$meta_description = $page['meta_description'];
$meta_keywords = $page['meta_keywords'] ?? null;

function cmsTeamImageExists(string $path): bool
{
    if ($path === '') {
        return false;
    }
    $full = __DIR__ . '/' . ltrim(str_replace(['\\', '..'], ['/', ''], $path), '/');
    return is_file($full);
}

function cmsTeamImageSrc(string $path): string
{
    if (!cmsTeamImageExists($path)) {
        return $path;
    }
    $full = __DIR__ . '/' . ltrim($path, '/');
    return $path . '?v=' . filemtime($full);
}

function cmsRenderEquipmentSpecs(string $description): void
{
    $lines = array_filter(array_map('trim', explode("\n", $description)));
    if ($lines === []) {
        return;
    }
    $intro = [];
    $bullets = [];
    foreach ($lines as $line) {
        if (str_starts_with($line, '•') || str_starts_with($line, '-')) {
            $bullets[] = ltrim($line, "•-\t ");
        } else {
            $intro[] = $line;
        }
    }
    if (!empty($intro)) {
        echo '<p class="equipment-intro">' . htmlspecialchars(implode(' ', $intro)) . '</p>';
    }
    if (!empty($bullets)) {
        echo '<ul class="equipment-spec-list">';
        foreach ($bullets as $bullet) {
            echo '<li>' . htmlspecialchars($bullet) . '</li>';
        }
        echo '</ul>';
    }
}

include 'includes/header.php';

$blocks = cmsPageBlocks($db, 'about');
$intro = cmsPageBlocksByType($blocks, 'intro')[0] ?? null;
$badges = cmsPageBlocksByType($blocks, 'badge');
$headers = cmsPageBlocksByType($blocks, 'header');
$features = cmsPageBlocksByType($blocks, 'feature');
$team = cmsPageBlocksByType($blocks, 'team');
$valuesHeader = $headers[0] ?? null;
$teamHeader = $headers[1] ?? null;

$introImage = $intro['image_path'] ?? 'images/gallery/web/blood-collection.jpg';
$introIsOwner = cmsTeamImageExists($introImage) && stripos((string) ($intro['title'] ?? ''), 'Akshay') !== false;
$introImageSrc = cmsTeamImageExists($introImage) ? cmsTeamImageSrc($introImage) : $introImage;
?>

<?php renderPageHeader($page, 'About Our Laboratory', 'About Us'); ?>

<section class="section-padding">
    <div class="container">
        <div class="grid-2 align-center reveal owner-spotlight-grid">
            <div>
                <?php if ($intro): ?>
                    <span class="section-tag"><?php echo htmlspecialchars($introIsOwner ? 'Founder & Lab Leadership' : ($intro['subtitle'] ?: 'Our Background')); ?></span>
                    <h2 class="section-title" style="text-align:left;margin-top:12px;"><?php echo htmlspecialchars($intro['title']); ?></h2>
                    <?php if ($introIsOwner && !empty($intro['subtitle'])): ?>
                        <p class="owner-role"><?php echo htmlspecialchars($intro['subtitle']); ?></p>
                    <?php endif; ?>
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
            <div class="owner-photo-col">
                <div class="team-photo-frame owner-photo-frame">
                    <img src="<?php echo htmlspecialchars($introImageSrc); ?>"
                         alt="Akshay Sanjay Rakh — Founder &amp; Laboratory In-Charge, Unity Clinical Laboratory"
                         class="about-intro-img owner-photo-img"
                         loading="eager"
                         width="560"
                         height="700">
                </div>
                <?php if ($introIsOwner): ?>
                <p class="owner-photo-caption"><i class="fa-solid fa-flask"></i> Biochemistry &amp; daily lab operations</p>
                <?php endif; ?>
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
        <div class="grid-3 reveal-stagger team-photo-grid team-photo-grid--doctors">
            <?php foreach ($team as $member): ?>
            <?php
            $memberImage = (string) ($member['image_path'] ?? '');
            $hasPhoto = cmsTeamImageExists($memberImage);
            $memberImageSrc = $hasPhoto ? cmsTeamImageSrc($memberImage) : $memberImage;
            ?>
            <article class="card team-card team-card-photo">
                <?php if ($hasPhoto): ?>
                <div class="team-photo-frame">
                    <img src="<?php echo htmlspecialchars($memberImageSrc); ?>"
                         alt="<?php echo htmlspecialchars($member['title']); ?> — <?php echo htmlspecialchars($member['subtitle'] ?? 'Unity Clinical Laboratory'); ?>"
                         class="team-photo"
                         loading="lazy"
                         width="400"
                         height="500">
                </div>
                <?php else: ?>
                <div class="author-avatar team-avatar"><?php echo htmlspecialchars($member['icon'] ?: substr($member['title'], 0, 2)); ?></div>
                <?php endif; ?>
                <div class="team-card-body">
                    <h3><?php echo htmlspecialchars($member['title']); ?></h3>
                    <p class="team-role"><?php echo htmlspecialchars($member['subtitle']); ?></p>
                    <p class="team-bio"><?php echo htmlspecialchars($member['content']); ?></p>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php
$equipmentAbout = $db->query('SELECT * FROM cms_equipment ORDER BY sequence ASC')->fetchAll();
if (!empty($equipmentAbout)):
?>
<section class="section-padding section-alt equipment-about-section" id="equipment">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Clinical Equipment</span>
            <h2 class="section-title">Precision Instruments Behind Every Report</h2>
            <p class="section-desc max-w-md">Sysmex hematology, Orbit biochemistry, Remi centrifuge, Labomed microscopy, and classical Leishman staining — maintained and operated under strict quality control.</p>
        </div>
        <div class="equipment-grid equipment-grid-detailed reveal-stagger">
            <?php foreach ($equipmentAbout as $eq): ?>
            <article class="equipment-card equipment-card-detailed">
                <div class="equipment-img-wrap">
                    <img src="<?php echo htmlspecialchars($eq['image_path']); ?>"
                         alt="<?php echo htmlspecialchars($eq['title']); ?>"
                         class="equipment-img team-photo-polish"
                         loading="lazy"
                         width="480"
                         height="320">
                </div>
                <div class="equipment-body">
                    <h3><?php echo htmlspecialchars($eq['title']); ?></h3>
                    <?php cmsRenderEquipmentSpecs((string) ($eq['description'] ?? '')); ?>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
