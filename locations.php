<?php
include 'includes/db.php';
require_once __DIR__ . '/includes/locations_data.php';

$page = cmsPage($cms_pages, 'locations', [
    'meta_title' => 'Pathology Lab Service Areas Maharashtra | Home Collection',
    'meta_description' => 'Unity Clinical Laboratory serves Pune, Mumbai, Nagpur, Nashik, Aurangabad, Kolhapur and across Maharashtra with blood tests, health packages and home sample collection.',
    'meta_keywords' => 'pathology lab Maharashtra, blood test home collection, diagnostic center Pune Mumbai Nagpur',
]);
$cms_page_context = $page;
$active_nav = 'locations';
$page_title = $page['meta_title'];
$meta_description = $page['meta_description'];
$meta_keywords = $page['meta_keywords'] ?? null;

$areas = cmsLocationAreas($db);

include 'includes/header.php';
?>

<?php renderPageHeader($page, 'Service Areas', 'Locations We Serve'); ?>

<section class="section-padding">
    <div class="container">
        <?php renderPageSectionHeader($page, [
            'tag' => 'Local SEO',
            'title' => 'Pathology & Home Collection Across Maharashtra',
            'desc' => 'Book blood tests, health packages and home sample collection in your city. Accurate reports with modern Sysmex & Orbit analyzers.',
        ]); ?>

        <div class="locations-grid reveal-stagger">
            <?php foreach ($areas as $slug => $area): ?>
                <article class="card location-card">
                    <h3><?php echo htmlspecialchars($area['name']); ?></h3>
                    <p><?php echo htmlspecialchars($area['description']); ?></p>
                    <ul class="location-services">
                        <?php foreach ($area['services'] as $service): ?>
                            <li><i class="fa-solid fa-check" aria-hidden="true"></i> <?php echo htmlspecialchars($service); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="location-card-actions">
                        <a href="location.php?city=<?php echo urlencode($slug); ?>" class="btn btn-secondary">View <?php echo htmlspecialchars($area['name']); ?> Services</a>
                        <a href="collection.php" class="btn btn-primary">Book Home Collection</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="card location-cta reveal" style="margin-top:40px; text-align:center; padding:32px;">
            <h3>Need a test in another area of Maharashtra?</h3>
            <p style="color:var(--text-muted); margin:12px 0 20px;">Call <?php echo htmlspecialchars(cmsSetting($cms, 'support_phone')); ?> or WhatsApp us — we arrange home collection across the state.</p>
            <a href="contact.php" class="btn btn-teal">Contact Unity Lab</a>
        </div>
    </div>
</section>

<?php
$locationListSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'ItemList',
    'name' => 'Unity Clinical Laboratory Service Areas',
    'itemListElement' => [],
];
$i = 1;
foreach ($areas as $slug => $area) {
    $locationListSchema['itemListElement'][] = [
        '@type' => 'ListItem',
        'position' => $i++,
        'url' => rtrim(BASE_URL, '/') . '/location.php?city=' . rawurlencode($slug),
        'name' => $area['headline'],
    ];
}
echo "\n<script type=\"application/ld+json\">" . json_encode($locationListSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "</script>\n";

include 'includes/footer.php';
