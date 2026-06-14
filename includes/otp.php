<?php

require_once __DIR__ . '/security.php';
require_once __DIR__ . '/mail.php';

function reportOtpEnabled(array $cms): bool
{
    return ($cms['report_otp_enabled'] ?? '1') === '1';
}

function createReportOtp(PDO $db, string $patientId, string $mobile): string
{
    $otp = (string) random_int(100000, 999999);
    $expires = date('Y-m-d H:i:s', time() + 600);

    $db->prepare('DELETE FROM report_otps WHERE patient_id = :patient_id AND mobile = :mobile')
        ->execute([':patient_id' => $patientId, ':mobile' => $mobile]);

    $db->prepare('INSERT INTO report_otps (patient_id, mobile, otp_code, expires_at) VALUES (:patient_id, :mobile, :otp, :expires)')
        ->execute([
            ':patient_id' => $patientId,
            ':mobile' => $mobile,
            ':otp' => password_hash($otp, PASSWORD_DEFAULT),
            ':expires' => $expires,
        ]);

    return $otp;
}

function sendReportOtp(PDO $db, array $cms, string $patientId, string $mobile): array
{
    $stmt = $db->prepare('SELECT name, email, mobile FROM patients WHERE patient_id = :patient_id AND mobile = :mobile');
    $stmt->execute([':patient_id' => $patientId, ':mobile' => $mobile]);
    $patient = $stmt->fetch();

    if (!$patient) {
        return ['success' => false, 'message' => 'No patient found matching this Patient ID and Mobile Number.'];
    }

    $countStmt = $db->prepare('SELECT COUNT(*) FROM reports WHERE patient_id = :patient_id');
    $countStmt->execute([':patient_id' => $patientId]);
    if ((int) $countStmt->fetchColumn() === 0) {
        return ['success' => false, 'message' => 'No reports are available yet for this patient.'];
    }

    $otp = createReportOtp($db, $patientId, $mobile);
    $site = $cms['site_name'] ?? 'Unity Clinical Laboratory';
    $message = "Your {$site} report download OTP is {$otp}. Valid for 10 minutes. Do not share this code.";

    $channel = 'none';
    if (!empty($patient['email']) && filter_var($patient['email'], FILTER_VALIDATE_EMAIL)) {
        if (sendAppMail($patient['email'], "[{$site}] Report Download OTP", $message, $cms)) {
            $channel = 'email';
        }
    }

    if ($channel === 'none' && sendSmsMessage($patient['mobile'], $message, $cms)) {
        $channel = 'sms';
    }

    if ($channel === 'none') {
        return [
            'success' => false,
            'message' => 'Unable to send OTP. Please ensure your email is registered with the lab, or contact support.',
        ];
    }

    $masked = $channel === 'email'
        ? maskEmail($patient['email'])
        : maskMobile($patient['mobile']);

    return [
        'success' => true,
        'message' => "OTP sent to your registered {$channel} ({$masked}).",
        'channel' => $channel,
    ];
}

function verifyReportOtp(PDO $db, string $patientId, string $mobile, string $otp): bool
{
    $stmt = $db->prepare('SELECT id, otp_code, expires_at FROM report_otps WHERE patient_id = :patient_id AND mobile = :mobile ORDER BY id DESC LIMIT 1');
    $stmt->execute([':patient_id' => $patientId, ':mobile' => $mobile]);
    $row = $stmt->fetch();

    if (!$row || strtotime($row['expires_at']) < time()) {
        return false;
    }

    if (!password_verify($otp, $row['otp_code'])) {
        return false;
    }

    $db->prepare('DELETE FROM report_otps WHERE id = :id')->execute([':id' => $row['id']]);
    return true;
}

function grantReportAccess(string $patientId, string $mobile): void
{
    initSecureSession();
    $_SESSION['report_access'] = [
        'patient_id' => $patientId,
        'mobile' => $mobile,
        'expires' => time() + 900,
    ];
}

function hasReportAccess(string $patientId, string $mobile, array $cms): bool
{
    if (!reportOtpEnabled($cms)) {
        return true;
    }

    initSecureSession();
    $access = $_SESSION['report_access'] ?? null;

    if (!is_array($access)) {
        return false;
    }

    if (($access['expires'] ?? 0) < time()) {
        unset($_SESSION['report_access']);
        return false;
    }

    return ($access['patient_id'] ?? '') === $patientId && ($access['mobile'] ?? '') === $mobile;
}

function maskEmail(string $email): string
{
    [$user, $domain] = explode('@', $email, 2);
    $visible = substr($user, 0, min(2, strlen($user)));
    return $visible . str_repeat('*', max(1, strlen($user) - 2)) . '@' . $domain;
}

function maskMobile(string $mobile): string
{
    return substr($mobile, 0, 2) . '******' . substr($mobile, -2);
}

function fetchPatientReports(PDO $db, string $patientId, string $mobile): array
{
    $stmt = $db->prepare('
        SELECT r.id, r.test_name, r.uploaded_at
        FROM reports r
        INNER JOIN patients p ON r.patient_id = p.patient_id
        WHERE r.patient_id = :patient_id AND p.mobile = :mobile
        ORDER BY r.id DESC
    ');
    $stmt->execute([':patient_id' => $patientId, ':mobile' => $mobile]);
    return $stmt->fetchAll();
}
