<?php
include 'includes/db.php';

$active_nav = 'home';
$page_title = $cms['hero_headline'] ?? "Accurate Diagnostics. Trusted Results.";
$meta_description = $cms['hero_subheadline'] ?? "Unity Clinical Laboratory is a premium pathology and diagnostic center offering NABL certified blood tests, urine tests, biochemistry, and health packages.";

include 'includes/header.php';

// 1. Fetch all active sections ordered by sequence index
try {
    $sections = $db->query("SELECT section_code FROM cms_sections WHERE is_active = 1 ORDER BY sequence ASC")->fetchAll();
    
    foreach ($sections as $sec) {
        $section_file = __DIR__ . '/includes/sections/' . $sec['section_code'] . '.php';
        if (file_exists($section_file)) {
            include $section_file;
        }
    }
} catch (PDOException $e) {
    echo "<div class='container' style='padding:40px;'><div class='alert alert-error'>Failed to load homepage components: " . htmlspecialchars($e->getMessage()) . "</div></div>";
}

// 2. Output FAQ Schema dynamically
try {
    $faqs_for_schema = $db->query("SELECT question, answer FROM cms_faqs ORDER BY sequence ASC")->fetchAll();
    if (!empty($faqs_for_schema)) {
        $schema_items = [];
        foreach ($faqs_for_schema as $faq) {
            $schema_items[] = [
                "@type" => "Question",
                "name" => $faq['question'],
                "acceptedAnswer" => [
                    "@type" => "Answer",
                    "text" => $faq['answer']
                ]
            ];
        }
        $schema_json = json_encode([
            "@context" => "https://schema.org",
            "@type" => "FAQPage",
            "mainEntity" => $schema_items
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        
        echo "\n<!-- Dynamic FAQ Schema Markup -->\n<script type=\"application/ld+json\">\n" . $schema_json . "\n</script>\n";
    }
} catch (PDOException $e) {
    // Ignore schema errors
}

include 'includes/footer.php';
?>
