<?php

function cmsBookingSlug(string $value): string
{
    return strtolower(preg_replace('/[^a-z0-9]/', '', $value));
}

/**
 * Resolve ?package= or ?test= query slug to a dropdown option label.
 */
function cmsResolveBookingPrefill(PDO $db, ?string $packageSlug, ?string $testSlug): string
{
    $packageSlug = cmsBookingSlug((string) $packageSlug);
    $testSlug = cmsBookingSlug((string) $testSlug);

    if ($packageSlug !== '') {
        try {
            $packages = $db->query('SELECT name FROM cms_packages ORDER BY sequence ASC')->fetchAll(PDO::FETCH_COLUMN);
            foreach ($packages as $name) {
                $slug = cmsBookingSlug((string) $name);
                if ($slug === $packageSlug || str_contains($slug, $packageSlug) || str_contains($packageSlug, $slug)) {
                    return (string) $name;
                }
            }
        } catch (PDOException $e) {
            // ignore
        }
    }

    if ($testSlug !== '') {
        try {
            $services = $db->query('SELECT title FROM cms_services ORDER BY sequence ASC')->fetchAll(PDO::FETCH_COLUMN);
            foreach ($services as $title) {
                $slug = cmsBookingSlug((string) $title);
                if ($slug === $testSlug || str_contains($slug, $testSlug) || str_contains($testSlug, $slug)) {
                    return (string) $title;
                }
            }
        } catch (PDOException $e) {
            // ignore
        }

        $legacy = [
            'cbc' => 'Complete Blood Count (CBC)',
            'thyroid' => 'Thyroid Profile (T3, T4, TSH)',
            'urine' => 'Urine Routine Examination',
        ];
        foreach ($legacy as $key => $label) {
            if ($testSlug === $key || str_contains($testSlug, $key)) {
                return $label;
            }
        }
    }

    return '';
}
