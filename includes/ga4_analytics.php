<?php

/**
 * Google Analytics 4 Data API (read-only) for admin dashboard.
 * Requires a GCP service account JSON with Analytics Viewer on the GA4 property.
 */

function ga4ServiceAccountPath(): string
{
    return dirname(__DIR__) . '/storage/ga4/service-account.json';
}

function ga4CredentialsConfigured(): bool
{
    return is_readable(ga4ServiceAccountPath());
}

function ga4LoadServiceAccount(): ?array
{
    if (!ga4CredentialsConfigured()) {
        return null;
    }
    $data = json_decode((string) file_get_contents(ga4ServiceAccountPath()), true);
    return is_array($data) ? $data : null;
}

function ga4Base64UrlEncode(string $data): string
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function ga4FetchAccessToken(array $serviceAccount): ?string
{
    $cacheFile = dirname(__DIR__) . '/storage/ga4/token-cache.json';
    if (is_readable($cacheFile)) {
        $cached = json_decode((string) file_get_contents($cacheFile), true);
        if (is_array($cached) && ($cached['expires_at'] ?? 0) > time() + 60 && !empty($cached['access_token'])) {
            return $cached['access_token'];
        }
    }

    $now = time();
    $header = ga4Base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
    $claim = ga4Base64UrlEncode(json_encode([
        'iss' => $serviceAccount['client_email'],
        'scope' => 'https://www.googleapis.com/auth/analytics.readonly',
        'aud' => 'https://oauth2.googleapis.com/token',
        'iat' => $now,
        'exp' => $now + 3600,
    ]));
    $unsigned = $header . '.' . $claim;

    $privateKey = openssl_pkey_get_private($serviceAccount['private_key'] ?? '');
    if ($privateKey === false) {
        return null;
    }

    $signature = '';
    if (!openssl_sign($unsigned, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
        return null;
    }

    $jwt = $unsigned . '.' . ga4Base64UrlEncode($signature);

    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_POSTFIELDS => http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]),
        CURLOPT_TIMEOUT => 20,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    $json = json_decode((string) $response, true);
    if (empty($json['access_token'])) {
        return null;
    }

    $dir = dirname($cacheFile);
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    file_put_contents($cacheFile, json_encode([
        'access_token' => $json['access_token'],
        'expires_at' => $now + (int) ($json['expires_in'] ?? 3600),
    ]));

    return $json['access_token'];
}

function ga4RunReport(string $propertyId, string $accessToken, array $body): ?array
{
    return ga4ApiRequest($propertyId, $accessToken, 'runReport', $body);
}

function ga4RunRealtimeReport(string $propertyId, string $accessToken, array $body): ?array
{
    return ga4ApiRequest($propertyId, $accessToken, 'runRealtimeReport', $body);
}

function ga4ApiRequest(string $propertyId, string $accessToken, string $method, array $body): ?array
{
    if (!function_exists('curl_init')) {
        return ['error' => 'PHP curl extension is required for GA4 API.'];
    }

    $propertyId = preg_replace('/[^0-9]/', '', $propertyId);
    if ($propertyId === '') {
        return null;
    }

    $url = 'https://analyticsdata.googleapis.com/v1beta/properties/' . $propertyId . ':' . $method;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode($body),
        CURLOPT_TIMEOUT => 20,
    ]);
    $response = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code < 200 || $code >= 300) {
        return ['error' => 'GA4 API HTTP ' . $code, 'raw' => $response];
    }

    return json_decode((string) $response, true);
}

function ga4DashboardCachePath(): string
{
    return dirname(__DIR__) . '/storage/ga4/dashboard-cache.json';
}

function ga4FetchDashboardData(string $propertyId, bool $forceRefresh = false): array
{
    $cacheFile = ga4DashboardCachePath();
    if (!$forceRefresh && is_readable($cacheFile)) {
        $cached = json_decode((string) file_get_contents($cacheFile), true);
        if (is_array($cached) && ($cached['cached_at'] ?? 0) > time() - 900) {
            return $cached;
        }
    }

    $serviceAccount = ga4LoadServiceAccount();
    if (!$serviceAccount) {
        return ['ok' => false, 'error' => 'Service account JSON not uploaded.'];
    }

    $token = ga4FetchAccessToken($serviceAccount);
    if ($token === null) {
        return ['ok' => false, 'error' => 'Could not authenticate with Google. Check service account JSON.'];
    }

    $summary7 = ga4RunReport($propertyId, $token, [
        'dateRanges' => [['startDate' => '7daysAgo', 'endDate' => 'today']],
        'metrics' => [
            ['name' => 'activeUsers'],
            ['name' => 'newUsers'],
            ['name' => 'sessions'],
            ['name' => 'screenPageViews'],
            ['name' => 'eventCount'],
        ],
    ]);

    $summary30 = ga4RunReport($propertyId, $token, [
        'dateRanges' => [['startDate' => '30daysAgo', 'endDate' => 'today']],
        'metrics' => [
            ['name' => 'activeUsers'],
            ['name' => 'sessions'],
            ['name' => 'screenPageViews'],
        ],
    ]);

    $topPages = ga4RunReport($propertyId, $token, [
        'dateRanges' => [['startDate' => '7daysAgo', 'endDate' => 'today']],
        'dimensions' => [['name' => 'pagePath']],
        'metrics' => [['name' => 'screenPageViews']],
        'orderBys' => [['desc' => true, 'metric' => ['metricName' => 'screenPageViews']]],
        'limit' => 10,
    ]);

    $channels = ga4RunReport($propertyId, $token, [
        'dateRanges' => [['startDate' => '7daysAgo', 'endDate' => 'today']],
        'dimensions' => [['name' => 'sessionDefaultChannelGroup']],
        'metrics' => [['name' => 'sessions']],
        'orderBys' => [['desc' => true, 'metric' => ['metricName' => 'sessions']]],
        'limit' => 8,
    ]);

    $events = ga4RunReport($propertyId, $token, [
        'dateRanges' => [['startDate' => '30daysAgo', 'endDate' => 'today']],
        'dimensions' => [['name' => 'eventName']],
        'metrics' => [['name' => 'eventCount']],
        'dimensionFilter' => [
            'filter' => [
                'fieldName' => 'eventName',
                'inListFilter' => [
                    'values' => ['booking_submit', 'inquiry_submit', 'review_submit', 'page_view'],
                ],
            ],
        ],
        'limit' => 10,
    ]);

    $daily = ga4RunReport($propertyId, $token, [
        'dateRanges' => [['startDate' => '14daysAgo', 'endDate' => 'today']],
        'dimensions' => [['name' => 'date']],
        'metrics' => [['name' => 'activeUsers'], ['name' => 'sessions']],
        'orderBys' => [['dimension' => ['dimensionName' => 'date']]],
    ]);

    if (isset($summary7['error'])) {
        return ['ok' => false, 'error' => 'GA4 API error. Verify Property ID and service account access.', 'detail' => $summary7];
    }

    $result = [
        'ok' => true,
        'cached_at' => time(),
        'summary7' => ga4ParseMetricRow($summary7),
        'summary30' => ga4ParseMetricRow($summary30),
        'top_pages' => ga4ParseDimensionReport($topPages, 'pagePath', 'screenPageViews'),
        'channels' => ga4ParseDimensionReport($channels, 'sessionDefaultChannelGroup', 'sessions'),
        'events' => ga4ParseDimensionReport($events, 'eventName', 'eventCount'),
        'daily' => ga4ParseDailyReport($daily),
    ];

    $dir = dirname($cacheFile);
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    file_put_contents($cacheFile, json_encode($result));

    return $result;
}

function ga4FetchRealtimeData(string $propertyId): array
{
    $serviceAccount = ga4LoadServiceAccount();
    if (!$serviceAccount) {
        return ['ok' => false, 'error' => 'Service account JSON not uploaded.'];
    }

    $token = ga4FetchAccessToken($serviceAccount);
    if ($token === null) {
        return ['ok' => false, 'error' => 'Could not authenticate with Google. Check service account JSON.'];
    }

    $summary = ga4RunRealtimeReport($propertyId, $token, [
        'metrics' => [
            ['name' => 'activeUsers'],
            ['name' => 'eventCount'],
        ],
    ]);

    $pages = ga4RunRealtimeReport($propertyId, $token, [
        'dimensions' => [['name' => 'unifiedScreenName']],
        'metrics' => [['name' => 'activeUsers']],
        'orderBys' => [['desc' => true, 'metric' => ['metricName' => 'activeUsers']]],
        'limit' => 8,
    ]);

    $pagePaths = ga4RunRealtimeReport($propertyId, $token, [
        'dimensions' => [['name' => 'pagePath']],
        'metrics' => [['name' => 'activeUsers']],
        'orderBys' => [['desc' => true, 'metric' => ['metricName' => 'activeUsers']]],
        'limit' => 8,
    ]);

    $sources = ga4RunRealtimeReport($propertyId, $token, [
        'dimensions' => [['name' => 'sessionSource']],
        'metrics' => [['name' => 'activeUsers']],
        'orderBys' => [['desc' => true, 'metric' => ['metricName' => 'activeUsers']]],
        'limit' => 6,
    ]);

    $countries = ga4RunRealtimeReport($propertyId, $token, [
        'dimensions' => [['name' => 'country']],
        'metrics' => [['name' => 'activeUsers']],
        'orderBys' => [['desc' => true, 'metric' => ['metricName' => 'activeUsers']]],
        'limit' => 6,
    ]);

    $devices = ga4RunRealtimeReport($propertyId, $token, [
        'dimensions' => [['name' => 'deviceCategory']],
        'metrics' => [['name' => 'activeUsers']],
        'orderBys' => [['desc' => true, 'metric' => ['metricName' => 'activeUsers']]],
        'limit' => 4,
    ]);

    $events = ga4RunRealtimeReport($propertyId, $token, [
        'dimensions' => [['name' => 'eventName']],
        'metrics' => [['name' => 'eventCount']],
        'orderBys' => [['desc' => true, 'metric' => ['metricName' => 'eventCount']]],
        'limit' => 12,
    ]);

    if (isset($summary['error'])) {
        return [
            'ok' => false,
            'error' => 'GA4 Realtime API error. Verify Property ID and service account access.',
            'detail' => $summary,
        ];
    }

    $metrics = ga4ParseMetricRow($summary);

    return [
        'ok' => true,
        'fetched_at' => time(),
        'active_users' => ga4MetricValue($metrics, 'activeUsers'),
        'event_count' => ga4MetricValue($metrics, 'eventCount'),
        'pages' => ga4ParseDimensionReport($pages, 'unifiedScreenName', 'activeUsers'),
        'page_paths' => ga4ParseDimensionReport($pagePaths, 'pagePath', 'activeUsers'),
        'sources' => ga4ParseDimensionReport($sources, 'sessionSource', 'activeUsers'),
        'countries' => ga4ParseDimensionReport($countries, 'country', 'activeUsers'),
        'devices' => ga4ParseDimensionReport($devices, 'deviceCategory', 'activeUsers'),
        'events' => ga4ParseDimensionReport($events, 'eventName', 'eventCount'),
    ];
}

function ga4ParseMetricRow(?array $report): array
{
    if (!$report || empty($report['rows'][0]['metricValues'])) {
        return [];
    }
    $names = array_column($report['metricHeaders'] ?? [], 'name');
    $values = $report['rows'][0]['metricValues'];
    $out = [];
    foreach ($names as $i => $name) {
        $out[$name] = (int) ($values[$i]['value'] ?? 0);
    }
    return $out;
}

function ga4ParseDimensionReport(?array $report, string $dimName, string $metricName): array
{
    if (!$report || empty($report['rows'])) {
        return [];
    }
    $rows = [];
    foreach ($report['rows'] as $row) {
        $label = $row['dimensionValues'][0]['value'] ?? '';
        $value = (int) ($row['metricValues'][0]['value'] ?? 0);
        if ($label !== '') {
            $rows[] = ['label' => $label, 'value' => $value];
        }
    }
    return $rows;
}

function ga4ParseDailyReport(?array $report): array
{
    if (!$report || empty($report['rows'])) {
        return [];
    }
    $rows = [];
    foreach ($report['rows'] as $row) {
        $date = $row['dimensionValues'][0]['value'] ?? '';
        $users = (int) ($row['metricValues'][0]['value'] ?? 0);
        $sessions = (int) ($row['metricValues'][1]['value'] ?? 0);
        if ($date !== '') {
            $rows[] = [
                'date' => substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2),
                'users' => $users,
                'sessions' => $sessions,
            ];
        }
    }
    return $rows;
}

function ga4MetricValue(array $metrics, string $key): int
{
    return (int) ($metrics[$key] ?? 0);
}
