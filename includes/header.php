<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Retrieve settings from $cms global array (loaded in db.php)
$site_name = $cms['site_name'] ?? "Unity Clinical Laboratory";
$logo_text = $cms['logo_text'] ?? "UnityLab";
$support_phone = $cms['support_phone'] ?? "+91 98765 43210";
$support_email = $cms['support_email'] ?? "info@unityclinicallab.com";
$whatsapp_num = $cms['whatsapp_number'] ?? "919876543210";

// Split logo text into two parts for styling (e.g. "UnityLab" -> "Unity" and "Lab")
// We look for a capital letter or split in half, or just styled as brand-teal for the second word
$logo_main = $logo_text;
$logo_span = "";
if (preg_match('/^([A-Z][a-z]+)([A-Z][A-Za-z]+)$/', $logo_text, $matches)) {
    $logo_main = $matches[1];
    $logo_span = $matches[2];
} else {
    // If not matching CamelCase, split in half
    $len = strlen($logo_text);
    if ($len > 4) {
        $logo_main = substr($logo_text, 0, $len - 3);
        $logo_span = substr($logo_text, $len - 3);
    }
}

$site_title = $site_name . " | Accurate Diagnostics & Blood Test Center";
$site_description = "Unity Clinical Laboratory offers NABL accredited pathology services including blood, urine, biochemistry, thyroid, diabetes and full body health checkups with home sample collection.";
$site_keywords = "laboratory, pathology lab, blood test, urine test, health checkup, home collection, NABL, diagnostic center";

if (isset($page_title)) {
    $site_title = $page_title . " - " . $site_name;
}
if (isset($meta_description)) {
    $site_description = $meta_description;
}

// Active nav check helper
function is_item_active($item_url, $active_nav) {
    $current_page = basename($_SERVER['PHP_SELF']);
    if ($current_page === $item_url) {
        return 'active';
    }
    // Fallback mapping
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

// Fetch dynamic menu navigation
try {
    $menu_items = $db->query("SELECT * FROM cms_menu WHERE is_active = 1 ORDER BY sequence ASC")->fetchAll();
} catch (PDOException $e) {
    $menu_items = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($site_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($site_description); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($site_keywords); ?>">
    <meta name="author" content="<?php echo htmlspecialchars($site_name); ?>">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="http://localhost/unity/">
    <meta property="og:title" content="<?php echo htmlspecialchars($site_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($site_description); ?>">
    <meta property="og:image" content="http://localhost/unity/images/og-image.jpg">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:title" content="<?php echo htmlspecialchars($site_title); ?>">
    <meta property="twitter:description" content="<?php echo htmlspecialchars($site_description); ?>">

    <!-- Styles & Fonts -->
    <link rel="stylesheet" href="css/style.css">
    
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Local Business Schema Markup -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "MedicalBusiness",
      "name": "<?php echo htmlspecialchars($site_name); ?>",
      "alternateName": "Unity Diagnostics",
      "image": "http://localhost/unity/images/logo.png",
      "logo": "http://localhost/unity/images/logo.png",
      "@id": "http://localhost/unity/#laboratory",
      "url": "http://localhost/unity/",
      "telephone": "<?php echo htmlspecialchars($support_phone); ?>",
      "email": "<?php echo htmlspecialchars($support_email); ?>",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "102 Health Plaza, Sector 15",
        "addressLocality": "Gurugram",
        "addressRegion": "Haryana",
        "postalCode": "122001",
        "addressCountry": "IN"
      },
      "geo": {
        "@type": "GeoCoordinates",
        "latitude": 28.459497,
        "longitude": 77.026638
      },
      "openingHoursSpecification": [
        {
          "@type": "OpeningHoursSpecification",
          "dayOfWeek": [
            "Monday",
            "Tuesday",
            "Wednesday",
            "Thursday",
            "Friday",
            "Saturday"
          ],
          "opens": "07:00",
          "closes": "21:00"
        },
        {
          "@type": "OpeningHoursSpecification",
          "dayOfWeek": "Sunday",
          "opens": "07:00",
          "closes": "14:00"
        }
      ],
      "sameAs": [
        "https://www.facebook.com/unityclinicallab",
        "https://www.instagram.com/unityclinicallab"
      ],
      "priceRange": "$$"
    }
    </script>
</head>
<body>

    <!-- Top Announcement Banner -->
    <?php if (($cms['top_offer_active'] ?? '0') === '1' && !empty($cms['top_offer_text'])): ?>
    <div class="offer-banner" style="background-color: var(--brand-teal); color: #ffffff; text-align: center; padding: 10px 15px; font-weight: 600; font-size: 0.9rem; z-index: 1000; position: relative; border-bottom: 1px solid rgba(255,255,255,0.1);">
        <div class="container" style="display: flex; justify-content: center; align-items: center; gap: 10px; flex-wrap: wrap;">
            <span><i class="fa-solid fa-bullhorn"></i> <?php echo htmlspecialchars($cms['top_offer_text']); ?></span>
            <a href="packages.php" style="background: #ffffff; color: var(--brand-blue); padding: 2px 10px; border-radius: 20px; font-size: 0.75rem; text-decoration: none; font-weight: 700; text-transform: uppercase;">View Offers</a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Top Info Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="top-bar-info">
                <span><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($support_phone); ?></span>
                <span><i class="fa-solid fa-envelope"></i> <?php echo htmlspecialchars($support_email); ?></span>
                <span><i class="fa-solid fa-location-dot"></i> Gurugram, India</span>
            </div>
            <div class="top-bar-social">
                <a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $whatsapp_num); ?>" target="_blank" aria-label="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
            </div>
        </div>
    </div>

    <!-- Main Navigation Bar -->
    <header class="site-header">
        <div class="container">
            <nav class="nav-bar">
                <a href="index.php" class="logo-link">
                    <?php if (($cms['logo_type'] ?? 'text') === 'image' && !empty($cms['logo_image'])): ?>
                        <img src="<?php echo htmlspecialchars($cms['logo_image']); ?>" alt="<?php echo htmlspecialchars($site_name); ?>" class="brand-logo-img" style="max-height: 45px; object-fit: contain;">
                    <?php elseif (($cms['logo_type'] ?? 'text') === 'icon'): ?>
                        <div class="logo-icon"><i class="<?php echo htmlspecialchars($cms['logo_icon'] ?? 'fa-solid fa-flask'); ?>"></i></div>
                        <div class="logo-text"><?php echo htmlspecialchars($logo_main); ?><span><?php echo htmlspecialchars($logo_span); ?></span></div>
                    <?php else: ?>
                        <div class="logo-icon"><i class="fa-solid fa-flask"></i></div>
                        <div class="logo-text"><?php echo htmlspecialchars($logo_main); ?><span><?php echo htmlspecialchars($logo_span); ?></span></div>
                    <?php endif; ?>
                </a>
                
                <button class="menu-toggle" aria-label="Toggle menu">☰</button>
                
                <ul class="nav-menu">
                    <?php foreach ($menu_items as $item): ?>
                        <li class="nav-item <?php echo is_item_active($item['url'], $active_nav ?? ''); ?>">
                            <a href="<?php echo htmlspecialchars($item['url']); ?>" class="<?php echo ((int)$item['is_cta'] === 1) ? 'nav-cta' : ''; ?>">
                                <?php echo htmlspecialchars($item['title']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </div>
    </header>
