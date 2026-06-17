<?php

function cmsLocationSlugFromName(string $name): string
{
    $slug = strtolower(trim($name));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    return trim($slug, '-') ?: 'location';
}

function cmsSanitizeLocationSlug(string $slug, string $fallbackName = ''): string
{
    $slug = strtolower(trim($slug));
    $slug = str_replace(' ', '-', $slug);
    $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
    $slug = trim($slug, '-');
    if ($slug === '' && $fallbackName !== '') {
        $slug = cmsLocationSlugFromName($fallbackName);
    }
    return $slug !== '' ? $slug : 'location';
}

/** @return list<string> */
function cmsLocationServicesList(?string $servicesText): array
{
    if ($servicesText === null || trim($servicesText) === '') {
        return [];
    }
    $lines = preg_split('/\r\n|\r|\n/', $servicesText) ?: [];
    return array_values(array_filter(array_map('trim', $lines), static fn($line) => $line !== ''));
}

/** @param array<string, mixed> $row */
function cmsLocationFormatRow(array $row): array
{
    return [
        'id' => (int) ($row['id'] ?? 0),
        'slug' => (string) ($row['slug'] ?? ''),
        'name' => (string) ($row['name'] ?? ''),
        'state' => (string) ($row['state'] ?? 'Maharashtra'),
        'headline' => (string) ($row['headline'] ?? ''),
        'description' => (string) ($row['description'] ?? ''),
        'keywords' => (string) ($row['keywords'] ?? ''),
        'services' => cmsLocationServicesList($row['services_text'] ?? ''),
        'sequence' => (int) ($row['sequence'] ?? 0),
        'is_active' => (int) ($row['is_active'] ?? 1),
    ];
}

/**
 * Active locations keyed by slug (for public pages & sitemap).
 * @return array<string, array>
 */
function cmsLocationAreas(?PDO $db = null): array
{
    if ($db === null) {
        global $db;
    }

    $areas = [];
    if ($db instanceof PDO) {
        try {
            $rows = $db->query('SELECT * FROM cms_locations WHERE is_active = 1 ORDER BY sequence ASC, id ASC')->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $formatted = cmsLocationFormatRow($row);
                $areas[$formatted['slug']] = $formatted;
            }
        } catch (PDOException $e) {
            $areas = [];
        }
    }

    return $areas;
}

/** All locations for admin (including inactive). @return list<array> */
function cmsLocationAreasAll(PDO $db): array
{
    try {
        $rows = $db->query('SELECT * FROM cms_locations ORDER BY sequence ASC, id ASC')->fetchAll(PDO::FETCH_ASSOC);
        return array_map('cmsLocationFormatRow', $rows);
    } catch (PDOException $e) {
        return [];
    }
}

function cmsLocationBySlug(string $slug, ?PDO $db = null): ?array
{
    if ($db === null) {
        global $db;
    }

    $slug = strtolower(trim($slug));
    if ($slug === '' || !($db instanceof PDO)) {
        return null;
    }

    try {
        $stmt = $db->prepare('SELECT * FROM cms_locations WHERE slug = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([$slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? cmsLocationFormatRow($row) : null;
    } catch (PDOException $e) {
        return null;
    }
}

function cmsLocationById(PDO $db, int $id): ?array
{
    if ($id <= 0) {
        return null;
    }
    try {
        $stmt = $db->prepare('SELECT * FROM cms_locations WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? cmsLocationFormatRow($row) : null;
    } catch (PDOException $e) {
        return null;
    }
}
