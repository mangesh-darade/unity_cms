<?php

require_once __DIR__ . '/security.php';

function captchaEnabled(array $cms): bool
{
    return ($cms['captcha_enabled'] ?? '1') === '1';
}

function renderCaptchaField(): string
{
    initSecureSession();
    $a = random_int(1, 9);
    $b = random_int(1, 9);
    $_SESSION['captcha_answer'] = (string) ($a + $b);

    return '
    <div class="form-group captcha-group">
        <label class="form-label">Security Check <span style="color:#ef4444;">*</span></label>
        <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
            <span style="font-weight:600; color:var(--primary); min-width:100px;">What is ' . $a . ' + ' . $b . '?</span>
            <input type="number" name="captcha_answer" class="form-control" placeholder="Enter answer" required style="max-width:140px;">
        </div>
    </div>';
}

function validateCaptcha(?string $answer, array $cms): bool
{
    if (!captchaEnabled($cms)) {
        return true;
    }

    initSecureSession();
    $expected = $_SESSION['captcha_answer'] ?? null;
    unset($_SESSION['captcha_answer']);

    if ($expected === null || $answer === null || $answer === '') {
        return false;
    }

    return (string) (int) $answer === (string) (int) $expected;
}

function captchaErrorResponse(): void
{
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Incorrect security check answer. Please try again.']);
    exit();
}
