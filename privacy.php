<?php
include 'includes/db.php';

$page = cmsPage($cms_pages, 'privacy', [
    'meta_title' => 'Privacy Policy',
    'meta_description' => 'Privacy policy for Unity Clinical Laboratory covering patient data and report access.',
]);
$cms_page_context = $page;
$active_nav = '';
$page_title = $page['meta_title'];
$meta_description = $page['meta_description'];
$meta_keywords = $page['meta_keywords'] ?? null;

$doc_icon = 'fa-solid fa-shield-halved';
$related = [
    'url' => cmsPageFilename($cms_pages, 'terms', 'terms.php'),
    'label' => cmsSetting($cms, 'footer_terms_label', 'Terms of Service'),
];

include 'includes/header.php';
?>

<?php renderPageHeader($page, 'Privacy Policy', 'Privacy Policy'); ?>

<?php include 'includes/sections/legal_document.php'; ?>

<?php include 'includes/footer.php'; ?>
