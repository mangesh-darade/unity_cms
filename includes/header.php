<?php
require_once __DIR__ . '/security.php';
initSecureSession();

$site_name = cmsSetting($cms, 'site_name');
$logo_text = cmsSetting($cms, 'logo_text');
$support_phone = cmsSetting($cms, 'support_phone');
$support_email = cmsSetting($cms, 'support_email');
$whatsapp_num = cmsSetting($cms, 'whatsapp_number');
[$logo_main, $logo_span] = cmsLogoParts($logo_text);

$cms_page_context = $cms_page_context ?? null;
$page_meta = cmsPageMetaFromContext($cms, $cms_page_context, [
    'title' => $page_title ?? null,
    'description' => $meta_description ?? null,
    'keywords' => $meta_keywords ?? null,
    'og_image' => $og_image ?? null,
    'og_type' => $og_type ?? 'website',
    'noindex' => $robots_noindex ?? false,
    'canonical' => $canonical_url ?? null,
]);

function is_item_active($item_url, $active_nav) {
    $current_page = basename($_SERVER['PHP_SELF']);
    if ($current_page === $item_url) {
        return 'active';
    }
    if ($active_nav === 'home' && $item_url === 'index.php') return 'active';
    if ($active_nav === 'about' && $item_url === 'about.php') return 'active';
    if ($active_nav === 'services' && $item_url === 'services.php') return 'active';
    if ($active_nav === 'packages' && $item_url === 'packages.php') return 'active';
    if ($active_nav === 'gallery' && $item_url === 'gallery.php') return 'active';
    if ($active_nav === 'blog' && $item_url === 'blog.php') return 'active';
    if ($active_nav === 'contact' && $item_url === 'contact.php') return 'active';
    if ($active_nav === 'download' && $item_url === 'download.php') return 'active';
    if ($active_nav === 'collection' && $item_url === 'collection.php') return 'active';
    return '';
}

try {
    $menu_items = $db->query('SELECT * FROM cms_menu WHERE is_active = 1 ORDER BY sequence ASC')->fetchAll();
} catch (PDOException $e) {
    $menu_items = [];
}

$social_links = cmsSocialLinks($cms);
$offer_link = cmsSetting($cms, 'top_offer_link');
$offer_link_text = cmsSetting($cms, 'top_offer_link_text');
$top_bar_location = cmsSetting($cms, 'top_bar_location');
$header_logo_url = cmsSetting($cms, 'header_logo_url', 'index.php');
$header_logo_width = max(40, min(400, (int) cmsSetting($cms, 'header_logo_width', '240')));
$header_logo_height = max(30, min(200, (int) cmsSetting($cms, 'header_logo_height', '72')));
$header_logo_display_h = min($header_logo_height, 80);
$header_logo_display_w = min($header_logo_width, 280);
$header_logo_style = sprintf(
    '--header-logo-width:%dpx;--header-logo-height:%dpx;--header-logo-display-width:%dpx;--header-logo-display-height:%dpx;--header-logo-width-scrolled:%dpx;--header-logo-height-scrolled:%dpx;--header-logo-aspect:%s;',
    $header_logo_width,
    $header_logo_height,
    $header_logo_display_w,
    $header_logo_display_h,
    (int) round($header_logo_display_w * 0.85),
    (int) round($header_logo_display_h * 0.85),
    $header_logo_display_h > 0 ? round($header_logo_display_w / $header_logo_display_h, 4) : '2'
);
$menu_toggle_label = cmsSetting($cms, 'header_menu_toggle_label', 'Toggle menu');
$logo_icon = cmsSetting($cms, 'logo_icon', 'fa-solid fa-flask');
$logo_image = cmsSetting($cms, 'logo_image');
$logo_use_image = (($cms['logo_type'] ?? 'text') === 'image' && $logo_image !== '');
if ($logo_use_image) {
    $logo_file = __DIR__ . '/../' . ltrim(str_replace(['\\', '..'], ['/', ''], $logo_image), '/');
    if (!is_file($logo_file)) {
        $logo_use_image = false;
    }
}

$nav_links = [];
$nav_cta = null;
foreach ($menu_items as $item) {
    if ((int) ($item['is_cta'] ?? 0) === 1) {
        $nav_cta = $item;
    } else {
        $nav_links[] = $item;
    }
}

$_body_class = trim($body_class ?? '');
if (basename($_SERVER['PHP_SELF']) !== 'index.php' && stripos($_body_class, 'page-home') === false) {
    $_body_class = trim($_body_class . ' page-inner');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(csrfToken()); ?>">
    <?php renderMarketingHead($cms, $page_meta); ?>
    <script>
    (function(){try{var t=localStorage.getItem('unity-theme');if(t==='dark'){document.documentElement.setAttribute('data-theme','dark');}}catch(e){}})();
    </script>

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/premium.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="<?php echo htmlspecialchars($_body_class); ?>">
    <a href="#main-content" class="skip-link">Skip to main content</a>
    <?php renderTrackingScripts($cms, 'body'); ?>

    <?php if (($cms['top_offer_active'] ?? '0') === '1' && cmsSetting($cms, 'top_offer_text') !== ''): ?>
    <div class="offer-banner">
        <div class="container offer-banner-inner">
            <span class="offer-banner-text"><i class="fa-solid fa-bullhorn"></i> <?php echo htmlspecialchars($cms['top_offer_text']); ?></span>
            <?php if ($offer_link !== ''): ?>
            <a href="<?php echo htmlspecialchars($offer_link); ?>" class="offer-banner-cta"><?php echo htmlspecialchars($offer_link_text); ?></a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (($cms['header_show_top_bar'] ?? '1') === '1'): ?>
    <div class="top-bar">
        <div class="container">
            <div class="top-bar-info">
                <?php if (($cms['header_show_phone'] ?? '1') === '1' && $support_phone !== ''): ?>
                <span><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($support_phone); ?></span>
                <?php endif; ?>
                <?php if (($cms['header_show_email'] ?? '1') === '1' && $support_email !== ''): ?>
                <span><i class="fa-solid fa-envelope"></i> <?php echo htmlspecialchars($support_email); ?></span>
                <?php endif; ?>
                <?php if (($cms['header_show_location'] ?? '1') === '1' && $top_bar_location !== ''): ?>
                <span><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($top_bar_location); ?></span>
                <?php endif; ?>
            </div>
            <?php if (($cms['header_show_social'] ?? '1') === '1'): ?>
            <div class="top-bar-social">
                <?php foreach ($social_links as $social): ?>
                    <a href="<?php echo htmlspecialchars($social['url']); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo htmlspecialchars($social['label']); ?>"><i class="<?php echo htmlspecialchars($social['icon']); ?>"></i></a>
                <?php endforeach; ?>
                <?php if (($cms['header_show_whatsapp_icon'] ?? '1') === '1' && $whatsapp_num !== ''): ?>
                <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $whatsapp_num); ?>" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <header class="site-header" style="<?php echo htmlspecialchars($header_logo_style); ?>">
        <div class="container">
            <nav class="nav-bar" aria-label="Main navigation">
                <a href="<?php echo htmlspecialchars($header_logo_url); ?>" class="logo-link<?php echo $logo_use_image ? ' logo-link--image' : ''; ?>">
                    <?php if ($logo_use_image): ?>
                        <img src="<?php echo htmlspecialchars($logo_image); ?>" alt="<?php echo htmlspecialchars($site_name); ?>" class="brand-logo-img" width="<?php echo $header_logo_display_w; ?>" height="<?php echo $header_logo_display_h; ?>">
                    <?php elseif (($cms['logo_type'] ?? 'text') === 'icon'): ?>
                        <div class="logo-icon"><i class="<?php echo htmlspecialchars($logo_icon); ?>"></i></div>
                        <div class="logo-text">
                            <span class="logo-text-line"><?php echo htmlspecialchars($logo_main); ?></span>
                            <?php if ($logo_span !== ''): ?><span class="logo-text-accent"><?php echo htmlspecialchars($logo_span); ?></span><?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="logo-icon"><i class="<?php echo htmlspecialchars($logo_icon); ?>"></i></div>
                        <div class="logo-text">
                            <span class="logo-text-line"><?php echo htmlspecialchars($logo_main); ?></span>
                            <?php if ($logo_span !== ''): ?><span class="logo-text-accent"><?php echo htmlspecialchars($logo_span); ?></span><?php endif; ?>
                        </div>
                    <?php endif; ?>
                </a>

                <div class="nav-bar-center">
                    <ul class="nav-menu" id="navMenu">
                        <?php foreach ($nav_links as $item): ?>
                            <li class="nav-item <?php echo is_item_active($item['url'], $active_nav ?? ''); ?>">
                                <a href="<?php echo htmlspecialchars($item['url']); ?>"><?php echo htmlspecialchars($item['title']); ?></a>
                            </li>
                        <?php endforeach; ?>
                        <?php if ($nav_cta): ?>
                            <li class="nav-item nav-item-cta-mobile">
                                <a href="<?php echo htmlspecialchars($nav_cta['url']); ?>" class="btn btn-primary nav-cta-mobile"><?php echo htmlspecialchars($nav_cta['title']); ?></a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="nav-bar-actions">
                    <?php if ($nav_cta): ?>
                    <a href="<?php echo htmlspecialchars($nav_cta['url']); ?>" class="btn btn-primary nav-header-cta"><?php echo htmlspecialchars($nav_cta['title']); ?></a>
                    <?php endif; ?>
                    <button type="button" class="theme-toggle" aria-label="Toggle dark mode" title="Toggle theme">
                        <i class="fa-solid fa-moon theme-icon-dark"></i>
                        <i class="fa-solid fa-sun theme-icon-light"></i>
                    </button>
                    <button class="menu-toggle" aria-label="<?php echo htmlspecialchars($menu_toggle_label); ?>" aria-expanded="false">☰</button>
                </div>
            </nav>
        </div>
    </header>

    <?php if (($cms['floating_whatsapp_enabled'] ?? '0') === '1' && $whatsapp_num !== ''): ?>
    <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $whatsapp_num); ?>" class="floating-whatsapp" target="_blank" rel="noopener noreferrer" aria-label="Chat on WhatsApp">
        <i class="fa-brands fa-whatsapp"></i>
    </a>
    <?php endif; ?>

    <main id="main-content">
