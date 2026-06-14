<?php

function cmsSection(array $cms_sections, string $code, string $field, string $default = ''): string
{
    if (!isset($cms_sections[$code][$field])) {
        return $default;
    }
    $value = trim((string) $cms_sections[$code][$field]);
    return $value !== '' ? $value : $default;
}

function cmsPage(array $cms_pages, string $slug, array $defaults = []): array
{
    $page = $cms_pages[$slug] ?? [];
    return array_merge($defaults, array_filter($page, static fn($v) => $v !== null && $v !== ''));
}

function cmsPageBlocks(PDO $db, string $slug): array
{
    $stmt = $db->prepare('SELECT * FROM cms_page_blocks WHERE page_slug = :slug AND is_active = 1 ORDER BY sequence ASC, id ASC');
    $stmt->execute([':slug' => $slug]);
    return $stmt->fetchAll();
}

function cmsSectionItems(PDO $db, string $sectionCode): array
{
    $stmt = $db->prepare('SELECT * FROM cms_section_items WHERE section_code = :code AND is_active = 1 ORDER BY sequence ASC, id ASC');
    $stmt->execute([':code' => $sectionCode]);
    return $stmt->fetchAll();
}

function renderPageHeader(array $page, string $fallbackHeading = '', string $fallbackBreadcrumb = ''): void
{
    global $cms;
    $heading = $page['page_heading'] ?? $fallbackHeading;
    $breadcrumb = $page['breadcrumb_label'] ?? $fallbackBreadcrumb;
    $home_label = cmsSetting($cms, 'breadcrumb_home_label', 'Home');
    ?>
    <div class="page-header page-header-premium">
        <div class="page-header-bg" aria-hidden="true">
            <div class="page-header-orb page-header-orb-1"></div>
            <div class="page-header-orb page-header-orb-2"></div>
            <div class="page-header-grid"></div>
        </div>
        <div class="container page-header-inner reveal">
            <span class="page-header-tag"><i class="fa-solid fa-flask"></i> <?php echo htmlspecialchars(cmsSetting($cms, 'site_name')); ?></span>
            <h1><?php echo htmlspecialchars($heading); ?></h1>
            <div class="breadcrumb">
                <a href="index.php"><i class="fa-solid fa-house"></i> <?php echo htmlspecialchars($home_label); ?></a>
                <span class="breadcrumb-sep">/</span>
                <span><?php echo htmlspecialchars($breadcrumb); ?></span>
            </div>
        </div>
    </div>
    <?php
}

function renderCustomPageHeader(string $heading, array $breadcrumbs): void
{
    global $cms;
    ?>
    <div class="page-header page-header-premium">
        <div class="page-header-bg" aria-hidden="true">
            <div class="page-header-orb page-header-orb-1"></div>
            <div class="page-header-orb page-header-orb-2"></div>
            <div class="page-header-grid"></div>
        </div>
        <div class="container page-header-inner reveal">
            <span class="page-header-tag"><i class="fa-solid fa-flask"></i> <?php echo htmlspecialchars(cmsSetting($cms, 'site_name')); ?></span>
            <h1><?php echo htmlspecialchars($heading); ?></h1>
            <div class="breadcrumb">
                <?php foreach ($breadcrumbs as $i => $crumb): ?>
                    <?php if ($i > 0): ?><span class="breadcrumb-sep">/</span><?php endif; ?>
                    <?php if (!empty($crumb['url'])): ?>
                        <a href="<?php echo htmlspecialchars($crumb['url']); ?>"><?php echo htmlspecialchars($crumb['label']); ?></a>
                    <?php else: ?>
                        <span><?php echo htmlspecialchars($crumb['label']); ?></span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php
}

function renderSectionHeader(array $cms_sections, string $code, array $defaults): void
{
    $tag = cmsSection($cms_sections, $code, 'section_tag', $defaults['tag'] ?? '');
    $title = cmsSection($cms_sections, $code, 'section_heading', $defaults['title'] ?? '');
    $desc = cmsSection($cms_sections, $code, 'section_description', $defaults['desc'] ?? '');
    ?>
    <div class="section-header reveal">
        <?php if ($tag !== ''): ?><span class="section-tag"><?php echo htmlspecialchars($tag); ?></span><?php endif; ?>
        <?php if ($title !== ''): ?><h2 class="section-title"><?php echo htmlspecialchars($title); ?></h2><?php endif; ?>
        <?php if ($desc !== ''): ?><p class="section-desc max-w-md"><?php echo htmlspecialchars($desc); ?></p><?php endif; ?>
    </div>
    <?php
}

/** Section header for inner pages — prefers cms_pages content over homepage section defaults. */
function renderPageSectionHeader(array $page, array $defaults = []): void
{
    $tag = trim((string) ($page['content_tag'] ?? '')) ?: ($defaults['tag'] ?? '');
    $title = trim((string) ($page['content_title'] ?? '')) ?: ($defaults['title'] ?? '');
    $desc = trim((string) ($page['content_description'] ?? '')) ?: ($defaults['desc'] ?? '');
    ?>
    <div class="section-header reveal">
        <?php if ($tag !== ''): ?><span class="section-tag"><?php echo htmlspecialchars($tag); ?></span><?php endif; ?>
        <?php if ($title !== ''): ?><h2 class="section-title"><?php echo htmlspecialchars($title); ?></h2><?php endif; ?>
        <?php if ($desc !== ''): ?><p class="section-desc max-w-md"><?php echo htmlspecialchars($desc); ?></p><?php endif; ?>
    </div>
    <?php
}

function cmsPageBlocksByType(array $blocks, string $type): array
{
    return array_values(array_filter($blocks, static fn($b) => ($b['block_type'] ?? '') === $type));
}

function cmsPageFilename(array $cms_pages, string $slug, string $default = ''): string
{
    $page = $cms_pages[$slug] ?? [];
    $file = trim((string) ($page['filename'] ?? ''));
    return $file !== '' ? $file : $default;
}

function cmsLogoParts(string $logo_text): array
{
    $logo_text = trim($logo_text);
    if ($logo_text === '') {
        return ['', ''];
    }

    $compact = str_replace(' ', '', $logo_text);
    if (preg_match('/^([A-Z][a-z]+)([A-Z][A-Za-z]+)$/', $compact, $matches)) {
        return [$matches[1], $matches[2]];
    }

    $words = preg_split('/\s+/', $logo_text);
    if (count($words) >= 2) {
        $span = array_pop($words);
        return [implode(' ', $words), $span];
    }

    $len = strlen($logo_text);
    if ($len > 8) {
        return [substr($logo_text, 0, $len - 4), substr($logo_text, -4)];
    }

    return [$logo_text, ''];
}

function cmsAvailableSectionTemplates(): array
{
    $templates = [];
    $dir = __DIR__ . '/sections';
    if (!is_dir($dir)) {
        return $templates;
    }
    foreach (glob($dir . '/*.php') as $file) {
        $base = basename($file, '.php');
        if ($base !== '' && $base[0] !== '_') {
            $templates[] = $base;
        }
    }
    sort($templates);
    return $templates;
}

function cmsRenderHomeSection(PDO $db, array $cms_sections, array $sectionRow): void
{
    global $cms;

    $code = $sectionRow['section_code'] ?? '';
    $type = $sectionRow['section_type'] ?? 'builtin';

    if ($type === 'custom_html') {
        $current_home_section = $sectionRow;
        include __DIR__ . '/sections/_dynamic_html.php';
        return;
    }

    if ($type === 'items_grid') {
        $current_home_section = $sectionRow;
        include __DIR__ . '/sections/_dynamic_items.php';
        return;
    }

    $file = __DIR__ . '/sections/' . $code . '.php';
    if (is_file($file)) {
        include $file;
    }
}
