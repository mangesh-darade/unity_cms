<?php
/**
 * Application configuration.
 * Override BASE_URL in production if auto-detection is incorrect.
 */
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    $docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '');
    $appRoot = str_replace('\\', '/', realpath(__DIR__ . '/..') ?: '');

    $basePath = '';
    if ($docRoot !== '' && $appRoot !== '' && str_starts_with($appRoot, $docRoot)) {
        $basePath = substr($appRoot, strlen($docRoot));
    }

    define('BASE_URL', rtrim($protocol . '://' . $host . $basePath, '/') . '/');
}

if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', false);
}

if (file_exists(__DIR__ . '/config.local.php')) {
    require_once __DIR__ . '/config.local.php';
}

if (!defined('DB_DRIVER')) {
    define('DB_DRIVER', 'sqlite');
}
