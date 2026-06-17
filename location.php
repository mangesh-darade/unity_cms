<?php
include 'includes/db.php';
require_once __DIR__ . '/includes/locations_data.php';

$slug = isset($_GET['city']) ? strtolower(trim($_GET['city'])) : '';
$area = cmsLocationBySlug($slug, $db);

if (!$area) {
    header('Location: locations.php');
    exit();
}

$active_nav = 'locations';
$page_title = $area['headline'] . ' | ' . cmsSetting($cms, 'site_name');
$meta_description = $area['description'];
$meta_keywords = $area['keywords'];
$canonical_url = rtrim(BASE_URL, '/') . '/location.php?city=' . rawurlencode($slug);

include 'includes/header.php';
?>

<?php renderCustomPageHeader($area['headline'], [
    ['label' => cmsSetting($cms, 'breadcrumb_home_label', 'Home'), 'url' => 'index.php'],
    ['label' => 'Service Areas', 'url' => 'locations.php'],
    ['label' => $area['name'], 'url' => ''],
]); ?>

<section class="section-padding">
    <div class="container container-narrow">
        <article class="card reveal" style="padding:32px;">
            <p class="location-lead"><?php echo htmlspecialchars($area['description']); ?></p>
            <h3 style="margin:24px 0 12px;">Popular tests in <?php echo htmlspecialchars($area['name']); ?></h3>
            <ul class="location-services">
                <?php foreach ($area['services'] as $service): ?>
                    <li><i class="fa-solid fa-vial" aria-hidden="true"></i> <?php echo htmlspecialchars($service); ?></li>
                <?php endforeach; ?>
            </ul>
            <div class="admin-form-row" style="margin-top:28px; gap:12px; flex-wrap:wrap;">
                <a href="collection.php" class="btn btn-primary"><i class="fa-solid fa-house-medical"></i> Book Home Collection in <?php echo htmlspecialchars($area['name']); ?></a>
                <a href="services.php" class="btn btn-secondary">Browse All Tests</a>
                <a href="packages.php" class="btn btn-secondary">Health Packages</a>
                <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', cmsSetting($cms, 'support_phone')); ?>" class="btn btn-teal"><i class="fa-solid fa-phone"></i> Call Lab</a>
            </div>
        </article>
    </div>
</section>

<?php
$localSchema = cmsLocalBusinessSchema($cms);
$localSchema['areaServed'] = [
    '@type' => 'City',
    'name' => $area['name'],
    'containedInPlace' => [
        '@type' => 'State',
        'name' => $area['state'],
    ],
];
echo "\n<script type=\"application/ld+json\">" . json_encode($localSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "</script>\n";

include 'includes/footer.php';
