<?php
require_once __DIR__ . '/config.php';

function initSecureSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

function csrfToken(): string
{
    initSecureSession();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken()) . '">';
}

function validateCsrf(?string $token = null): bool
{
    initSecureSession();
    $token = $token ?? ($_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    return is_string($token) && $token !== '' && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function requireCsrf(): void
{
    if (!validateCsrf()) {
        http_response_code(403);
        if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') || str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid or expired security token. Please refresh and try again.']);
        } else {
            echo 'Invalid or expired security token. Please go back and try again.';
        }
        exit();
    }
}

function clientIp(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function rateLimit(string $action, int $maxAttempts = 10, int $windowSeconds = 900): bool
{
    initSecureSession();
    $key = hash('sha256', clientIp() . '|' . $action);
    $storageDir = __DIR__ . '/../storage/rate_limits';
    if (!is_dir($storageDir)) {
        mkdir($storageDir, 0755, true);
    }

    $file = $storageDir . '/' . $key . '.json';
    $now = time();
    $data = ['attempts' => [], 'blocked_until' => 0];

    if (file_exists($file)) {
        $decoded = json_decode((string) file_get_contents($file), true);
        if (is_array($decoded)) {
            $data = $decoded;
        }
    }

    if (($data['blocked_until'] ?? 0) > $now) {
        return false;
    }

    $data['attempts'] = array_values(array_filter(
        $data['attempts'] ?? [],
        static fn($ts) => ($now - (int) $ts) < $windowSeconds
    ));

    if (count($data['attempts']) >= $maxAttempts) {
        $data['blocked_until'] = $now + $windowSeconds;
        file_put_contents($file, json_encode($data));
        return false;
    }

    $data['attempts'][] = $now;
    $data['blocked_until'] = 0;
    file_put_contents($file, json_encode($data));
    return true;
}

function rateLimitOrFail(string $action, int $maxAttempts = 10, int $windowSeconds = 900, string $message = 'Too many requests. Please try again later.'): void
{
    if (!rateLimit($action, $maxAttempts, $windowSeconds)) {
        http_response_code(429);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message]);
        exit();
    }
}

function appErrorMessage(Throwable $e): string
{
    if (APP_DEBUG) {
        return $e->getMessage();
    }
    return 'An unexpected error occurred. Please try again later.';
}
