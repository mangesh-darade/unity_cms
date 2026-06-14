<?php
include 'includes/db.php';

$active_nav = 'home';
$body_class = 'page-home';
$page_title = cmsSetting($cms, 'seo_home_title', $cms['hero_headline'] ?? '');
$meta_description = cmsSetting($cms, 'seo_home_description', $cms['hero_subheadline'] ?? '');

include 'includes/header.php';

try {
    $home_sections = $db->query('SELECT * FROM cms_sections WHERE is_active = 1 ORDER BY sequence ASC')->fetchAll();
    foreach ($home_sections as $sectionRow) {
        cmsRenderHomeSection($db, $cms_sections, $sectionRow);
    }
} catch (PDOException $e) {
    echo "<div class='container' style='padding:40px;'><div class='alert alert-error'>Failed to load homepage components: " . htmlspecialchars($e->getMessage()) . '</div></div>';
}

try {
    $faqs_for_schema = $db->query('SELECT question, answer FROM cms_faqs ORDER BY sequence ASC')->fetchAll();
    if (!empty($faqs_for_schema)) {
        $schema_items = [];
        foreach ($faqs_for_schema as $faq) {
            $schema_items[] = [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['answer'],
                ],
            ];
        }
        $schema_json = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $schema_items,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        echo "\n<!-- Dynamic FAQ Schema Markup -->\n<script type=\"application/ld+json\">\n" . $schema_json . "\n</script>\n";
    }
} catch (PDOException $e) {
    // ignore
}

include 'includes/footer.php';
