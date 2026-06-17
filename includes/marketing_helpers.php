<?php

function cmsSetting(array $cms, string $key, string $default = ''): string
{
    $value = trim((string) ($cms[$key] ?? ''));
    return $value !== '' ? $value : $default;
}

function cmsCurrentUrl(): string
{
    $path = $_SERVER['REQUEST_URI'] ?? '/';
    if ($path === '' || $path[0] !== '/') {
        $path = '/' . ltrim($path, '/');
    }
    return rtrim(BASE_URL, '/') . $path;
}

function cmsMetaTitle(array $cms, ?string $pageTitle = null): string
{
    if ($pageTitle !== null && trim($pageTitle) !== '') {
        $format = cmsSetting($cms, 'seo_title_format', '{page}');
        if ($format === '{page} - {site}') {
            return trim($pageTitle) . ' - ' . cmsSetting($cms, 'site_name', 'Unity Clinical Laboratory');
        }
        return trim($pageTitle);
    }

    $site = cmsSetting($cms, 'site_name', 'Unity Clinical Laboratory');
    $suffix = cmsSetting($cms, 'seo_default_title_suffix', 'Accurate Diagnostics & Blood Test Center');
    return $suffix !== '' ? $site . ' | ' . $suffix : $site;
}

function cmsMetaDescription(array $cms, ?string $pageDescription = null): string
{
    if ($pageDescription !== null && trim($pageDescription) !== '') {
        return trim($pageDescription);
    }
    return cmsSetting($cms, 'seo_default_description', 'Unity Clinical Laboratory offers NABL aligned pathology services including blood, urine, biochemistry, thyroid, diabetes and full body health checkups with home sample collection in Maharashtra.');
}

function cmsMetaKeywords(array $cms, ?string $pageKeywords = null): string
{
    if ($pageKeywords !== null && trim($pageKeywords) !== '') {
        return trim($pageKeywords);
    }
    return cmsSetting($cms, 'seo_default_keywords', 'laboratory, pathology lab, blood test, urine test, health checkup, home collection, NABL, diagnostic center, Maharashtra');
}

function cmsOgImage(array $cms, ?string $pageOgImage = null): string
{
    $image = trim((string) ($pageOgImage ?? ''));
    if ($image === '') {
        $image = cmsSetting($cms, 'og_image', 'images/akshay_ucl_logo.jpg');
    }
    if (preg_match('#^https?://#i', $image)) {
        return $image;
    }
    return rtrim(BASE_URL, '/') . '/' . ltrim($image, '/');
}

function cmsRobotsMeta(array $cms, bool $pageNoIndex = false): string
{
    if ($pageNoIndex || cmsSetting($cms, 'seo_robots_index', '1') !== '1') {
        return 'noindex, nofollow';
    }
    return 'index, follow';
}

function cmsSocialLinks(array $cms): array
{
    $map = [
        'facebook' => ['key' => 'social_facebook', 'icon' => 'fa-brands fa-facebook-f', 'label' => 'Facebook'],
        'instagram' => ['key' => 'social_instagram', 'icon' => 'fa-brands fa-instagram', 'label' => 'Instagram'],
        'twitter' => ['key' => 'social_twitter', 'icon' => 'fa-brands fa-x-twitter', 'label' => 'Twitter'],
        'youtube' => ['key' => 'social_youtube', 'icon' => 'fa-brands fa-youtube', 'label' => 'YouTube'],
        'linkedin' => ['key' => 'social_linkedin', 'icon' => 'fa-brands fa-linkedin-in', 'label' => 'LinkedIn'],
    ];
    $links = [];
    foreach ($map as $id => $info) {
        $url = cmsSetting($cms, $info['key']);
        if ($url !== '') {
            $links[$id] = array_merge($info, ['url' => $url]);
        }
    }
    return $links;
}

function cmsLocalBusinessSchema(array $cms): array
{
    $site = cmsSetting($cms, 'site_name', 'Unity Clinical Laboratory');
    $logo = cmsSetting($cms, 'logo_image', 'images/logo.png');
    if ($logo !== '' && !preg_match('#^https?://#i', $logo)) {
        $logo = rtrim(BASE_URL, '/') . '/' . ltrim($logo, '/');
    }

    $sameAs = array_values(array_filter(array_map(static function ($key) use ($cms) {
        return cmsSetting($cms, $key);
    }, ['social_facebook', 'social_instagram', 'social_twitter', 'social_youtube', 'social_linkedin'])));

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'MedicalBusiness',
        'name' => $site,
        'alternateName' => cmsSetting($cms, 'schema_alternate_name', 'Unity Diagnostics'),
        'image' => cmsOgImage($cms),
        'logo' => $logo !== '' ? $logo : cmsOgImage($cms),
        '@id' => rtrim(BASE_URL, '/') . '/#laboratory',
        'url' => rtrim(BASE_URL, '/'),
        'telephone' => cmsSetting($cms, 'support_phone'),
        'email' => cmsSetting($cms, 'support_email'),
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => cmsSetting($cms, 'schema_street', '102 Health Plaza, Sector 15'),
            'addressLocality' => cmsSetting($cms, 'schema_city', 'Maharashtra'),
            'addressRegion' => cmsSetting($cms, 'schema_state', 'Maharashtra'),
            'postalCode' => cmsSetting($cms, 'schema_postal', '122001'),
            'addressCountry' => cmsSetting($cms, 'schema_country', 'IN'),
        ],
        'priceRange' => cmsSetting($cms, 'schema_price_range', '$$'),
    ];

    $lat = cmsSetting($cms, 'schema_lat');
    $lng = cmsSetting($cms, 'schema_lng');
    if ($lat !== '' && $lng !== '') {
        $schema['geo'] = [
            '@type' => 'GeoCoordinates',
            'latitude' => (float) $lat,
            'longitude' => (float) $lng,
        ];
    }

    $opensWeekday = cmsSetting($cms, 'schema_opens_weekday', '07:00');
    $closesWeekday = cmsSetting($cms, 'schema_closes_weekday', '21:00');
    $opensSunday = cmsSetting($cms, 'schema_opens_sunday', '07:00');
    $closesSunday = cmsSetting($cms, 'schema_closes_sunday', '14:00');

    $schema['openingHoursSpecification'] = [
        [
            '@type' => 'OpeningHoursSpecification',
            'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
            'opens' => $opensWeekday,
            'closes' => $closesWeekday,
        ],
        [
            '@type' => 'OpeningHoursSpecification',
            'dayOfWeek' => 'Sunday',
            'opens' => $opensSunday,
            'closes' => $closesSunday,
        ],
    ];

    if (!empty($sameAs)) {
        $schema['sameAs'] = $sameAs;
    }

    return $schema;
}

function cmsWebSiteSchema(array $cms): array
{
    $siteUrl = rtrim(BASE_URL, '/');
    return [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => cmsSetting($cms, 'site_name'),
        'url' => $siteUrl,
        'description' => cmsSetting($cms, 'seo_default_description'),
        'publisher' => [
            '@type' => 'MedicalBusiness',
            'name' => cmsSetting($cms, 'site_name'),
            'logo' => cmsOgImage($cms),
        ],
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => $siteUrl . '/services.php?q={search_term_string}',
            'query-input' => 'required name=search_term_string',
        ],
    ];
}

function cmsBreadcrumbSchema(array $items): array
{
    $elements = [];
    $pos = 1;
    foreach ($items as $item) {
        if (empty($item['label'])) {
            continue;
        }
        $entry = [
            '@type' => 'ListItem',
            'position' => $pos++,
            'name' => $item['label'],
        ];
        if (!empty($item['url'])) {
            $entry['item'] = rtrim(BASE_URL, '/') . '/' . ltrim($item['url'], '/');
        }
        $elements[] = $entry;
    }
    return [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $elements,
    ];
}

function cmsArticleSchema(array $cms, array $post): array
{
    $siteUrl = rtrim(BASE_URL, '/');
    $image = $post['image_path'] ?? '';
    if ($image !== '' && !preg_match('#^https?://#i', $image)) {
        $image = $siteUrl . '/' . ltrim($image, '/');
    }
    return [
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $post['title'],
        'description' => $post['summary'] ?? '',
        'image' => $image !== '' ? [$image] : [cmsOgImage($cms)],
        'author' => [
            '@type' => 'Organization',
            'name' => $post['author'] ?? cmsSetting($cms, 'site_name'),
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => cmsSetting($cms, 'site_name'),
            'logo' => [
                '@type' => 'ImageObject',
                'url' => cmsOgImage($cms),
            ],
        ],
        'datePublished' => date('c', strtotime($post['created_at'])),
        'dateModified' => date('c', strtotime($post['created_at'])),
        'mainEntityOfPage' => $siteUrl . '/blog-post.php?id=' . (int) $post['id'],
    ];
}

function cmsTestimonialsReviewSchema(PDO $db, array $cms): ?array
{
    try {
        $rows = $db->query("SELECT text, author FROM cms_testimonials WHERE status = 'approved' OR status IS NULL OR status = '' ORDER BY sequence ASC LIMIT 10")->fetchAll();
    } catch (PDOException $e) {
        return null;
    }
    if (count($rows) < 1) {
        return null;
    }
    $reviews = [];
    foreach ($rows as $row) {
        $reviews[] = [
            '@type' => 'Review',
            'reviewBody' => $row['text'],
            'author' => [
                '@type' => 'Person',
                'name' => $row['author'],
            ],
            'reviewRating' => [
                '@type' => 'Rating',
                'ratingValue' => '5',
                'bestRating' => '5',
            ],
        ];
    }
    return [
        '@context' => 'https://schema.org',
        '@type' => 'MedicalBusiness',
        'name' => cmsSetting($cms, 'site_name'),
        'aggregateRating' => [
            '@type' => 'AggregateRating',
            'ratingValue' => '4.9',
            'reviewCount' => (string) count($rows),
            'bestRating' => '5',
        ],
        'review' => $reviews,
    ];
}

function renderJsonLd(array $schema): void
{
    echo "\n<script type=\"application/ld+json\">" . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "</script>\n";
}

function renderMarketingHead(array $cms, array $meta = []): void
{
    $title = $meta['title'] ?? cmsMetaTitle($cms, null);
    $description = $meta['description'] ?? cmsMetaDescription($cms, null);
    $keywords = $meta['keywords'] ?? cmsMetaKeywords($cms, null);
    $canonical = $meta['canonical'] ?? cmsCurrentUrl();
    $ogImage = cmsOgImage($cms, $meta['og_image'] ?? null);
    $ogType = $meta['og_type'] ?? 'website';
    $robots = cmsRobotsMeta($cms, !empty($meta['noindex']));
    $twitterCard = cmsSetting($cms, 'twitter_card', 'summary_large_image');
    $twitterSite = cmsSetting($cms, 'twitter_site');
    $siteName = cmsSetting($cms, 'site_name', 'Unity Clinical Laboratory');
    ?>
    <title><?php echo htmlspecialchars($title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($description); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($keywords); ?>">
    <meta name="author" content="<?php echo htmlspecialchars($siteName); ?>">
    <meta name="robots" content="<?php echo htmlspecialchars($robots); ?>">
    <link rel="canonical" href="<?php echo htmlspecialchars($canonical); ?>">
    <link rel="alternate" type="application/rss+xml" title="<?php echo htmlspecialchars($siteName); ?> Blog" href="<?php echo htmlspecialchars(rtrim(BASE_URL, '/') . '/feed.php'); ?>">
    <?php
    $favicon = cmsSetting($cms, 'favicon_path', 'images/akshay_ucl_logo.jpg');
    $appleIcon = cmsSetting($cms, 'apple_touch_icon', $favicon);
    if ($favicon !== ''):
        $faviconUrl = preg_match('#^https?://#i', $favicon) ? $favicon : rtrim(BASE_URL, '/') . '/' . ltrim($favicon, '/');
        $appleUrl = preg_match('#^https?://#i', $appleIcon) ? $appleIcon : rtrim(BASE_URL, '/') . '/' . ltrim($appleIcon, '/');
        $faviconType = preg_match('/\.png$/i', $favicon) ? 'image/png' : (preg_match('/\.svg$/i', $favicon) ? 'image/svg+xml' : 'image/jpeg');
    ?>
    <link rel="icon" href="<?php echo htmlspecialchars($faviconUrl); ?>" type="<?php echo htmlspecialchars($faviconType); ?>">
    <link rel="apple-touch-icon" href="<?php echo htmlspecialchars($appleUrl); ?>">
    <?php endif; ?>

    <?php
    $geoRegionCode = cmsSetting($cms, 'geo_region_code', 'IN-MH');
    $geoPlace = cmsSetting($cms, 'schema_city');
    if ($geoRegionCode !== ''): ?>
    <meta name="geo.region" content="<?php echo htmlspecialchars($geoRegionCode); ?>">
    <?php endif; ?>
    <?php if ($geoPlace !== ''): ?>
    <meta name="geo.placename" content="<?php echo htmlspecialchars($geoPlace . ', India'); ?>">
    <?php endif; ?>
    <?php if (($lat = cmsSetting($cms, 'schema_lat')) !== '' && ($lng = cmsSetting($cms, 'schema_lng')) !== ''): ?>
    <meta name="geo.position" content="<?php echo htmlspecialchars($lat . ';' . $lng); ?>">
    <meta name="ICBM" content="<?php echo htmlspecialchars($lat . ', ' . $lng); ?>">
    <?php endif; ?>

    <?php if ($verification = cmsSetting($cms, 'google_site_verification')): ?>
    <meta name="google-site-verification" content="<?php echo htmlspecialchars($verification); ?>">
    <?php endif; ?>
    <?php if ($verification = cmsSetting($cms, 'bing_site_verification')): ?>
    <meta name="msvalidate.01" content="<?php echo htmlspecialchars($verification); ?>">
    <?php endif; ?>

    <meta property="og:type" content="<?php echo htmlspecialchars($ogType); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($canonical); ?>">
    <meta property="og:site_name" content="<?php echo htmlspecialchars(cmsSetting($cms, 'og_site_name', $siteName)); ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($description); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($ogImage); ?>">

    <meta name="twitter:card" content="<?php echo htmlspecialchars($twitterCard); ?>">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($title); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($description); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($ogImage); ?>">
    <?php if ($twitterSite !== ''): ?>
    <meta name="twitter:site" content="<?php echo htmlspecialchars($twitterSite); ?>">
    <?php endif; ?>

    <script type="application/ld+json"><?php echo json_encode(cmsLocalBusinessSchema($cms), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
    <script type="application/ld+json"><?php echo json_encode(cmsWebSiteSchema($cms), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
    <?php
    renderTrackingScripts($cms, 'head');
}

function renderTrackingScripts(array $cms, string $position = 'head'): void
{
    $gtm = cmsSetting($cms, 'google_tag_manager_id');
    $ga = cmsSetting($cms, 'google_analytics_id');
    $pixel = cmsSetting($cms, 'facebook_pixel_id');
    $custom = cmsSetting($cms, $position === 'head' ? 'marketing_head_scripts' : 'marketing_body_scripts');

    if ($position === 'head') {
        if ($gtm !== ''): ?>
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','<?php echo htmlspecialchars($gtm, ENT_QUOTES); ?>');</script>
        <?php endif;
        // Direct GA4 only when GTM is not configured (avoid duplicate pageviews)
        if ($ga !== '' && $gtm === ''): ?>
<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo htmlspecialchars($ga); ?>"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?php echo htmlspecialchars($ga, ENT_QUOTES); ?>');</script>
        <?php endif;
        if ($pixel !== ''): ?>
<!-- Meta Pixel -->
<script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init','<?php echo htmlspecialchars($pixel, ENT_QUOTES); ?>');fbq('track','PageView');</script>
        <?php endif;
        if ($custom !== '') {
            echo "\n" . $custom . "\n";
        }
        return;
    }

    if ($gtm !== ''): ?>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo htmlspecialchars($gtm); ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <?php endif;
    if ($pixel !== ''): ?>
<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?php echo urlencode($pixel); ?>&ev=PageView&noscript=1" alt=""></noscript>
    <?php endif;
    if ($custom !== '') {
        echo "\n" . $custom . "\n";
    }
}

function renderPageBodyContent(string $html, string $fallbackHtml = ''): void
{
    $content = trim($html);
    if ($content === '') {
        echo $fallbackHtml;
        return;
    }
    echo $content;
}

function cmsPageMetaFromContext(array $cms, ?array $page = null, array $overrides = []): array
{
    $meta = [
        'title' => cmsMetaTitle($cms, $overrides['title'] ?? ($page['meta_title'] ?? null)),
        'description' => cmsMetaDescription($cms, $overrides['description'] ?? ($page['meta_description'] ?? null)),
        'keywords' => cmsMetaKeywords($cms, $overrides['keywords'] ?? ($page['meta_keywords'] ?? null)),
        'og_image' => $overrides['og_image'] ?? ($page['og_image'] ?? null),
        'og_type' => $overrides['og_type'] ?? 'website',
        'noindex' => !empty($overrides['noindex']) || !empty($page['robots_noindex']),
        'canonical' => $overrides['canonical'] ?? null,
    ];
    if ($meta['canonical'] === null) {
        $meta['canonical'] = cmsCurrentUrl();
    }
    return $meta;
}
