<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

include __DIR__ . '/../../includes/db.php';
include __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/ga4_analytics.php';

if (!isAdminLoggedIn()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Unauthorized. Please log in to admin.']);
    exit;
}

$propertyId = trim($cms['ga4_property_id'] ?? '');
if ($propertyId === '' || !ga4CredentialsConfigured()) {
    echo json_encode(['ok' => false, 'error' => 'GA4 not configured. Add Property ID and service account JSON.']);
    exit;
}

echo json_encode(ga4FetchRealtimeData($propertyId));
