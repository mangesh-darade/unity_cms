<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: private, max-age=60');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/services_catalog.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

try {
    $result = cmsServicesCatalog($db, [
        'q' => $_GET['q'] ?? '',
        'category' => $_GET['category'] ?? '',
        'page' => $_GET['page'] ?? 1,
        'per_page' => $_GET['per_page'] ?? 24,
    ]);

    $items = array_map(static function (array $row): array {
        return [
            'id' => (int) $row['id'],
            'title' => $row['title'],
            'category' => $row['category'],
            'price' => (float) $row['price'],
            'description' => $row['description'] ?? '',
            'sample_type' => $row['sample_type'] ?? 'Blood',
            'prep_instructions' => $row['prep_instructions'] ?? 'No fasting required',
            'book_slug' => cmsServiceBookSlug((string) $row['title']),
        ];
    }, $result['items']);

    echo json_encode([
        'success' => true,
        'total' => $result['total'],
        'page' => $result['page'],
        'per_page' => $result['per_page'],
        'pages' => $result['pages'],
        'items' => $items,
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to load services.']);
}
