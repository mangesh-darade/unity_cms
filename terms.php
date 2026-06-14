<?php
include 'includes/db.php';

$page = cmsPage($cms_pages, 'terms', [
    'meta_title' => 'Terms of Service',
    'meta_description' => 'Terms of service for using the Unity Clinical Laboratory website and online services.',
]);
$cms_page_context = $page;
$active_nav = '';
$page_title = $page['meta_title'];
$meta_description = $page['meta_description'];
$meta_keywords = $page['meta_keywords'] ?? null;

$doc_icon = 'fa-solid fa-file-contract';
$related = [
    'url' => cmsPageFilename($cms_pages, 'privacy', 'privacy.php'),
    'label' => cmsSetting($cms, 'footer_privacy_label', 'Privacy Policy'),
];

include 'includes/header.php';
?>

<?php renderPageHeader($page, 'Terms of Service', 'Terms of Service'); ?>

<?php include 'includes/sections/legal_document.php'; ?>

<?php include 'includes/footer.php'; ?>
