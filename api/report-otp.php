<?php
header('Content-Type: application/json');
require_once '../includes/db.php';
require_once '../includes/security.php';
require_once '../includes/captcha.php';
require_once '../includes/otp.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit();
}

requireCsrf();

$action = trim($_POST['action'] ?? '');
$patient_id = trim($_POST['patient_id'] ?? '');
$mobile = trim($_POST['mobile'] ?? '');

if (!preg_match('/^PAT-[0-9]+$/', $patient_id) || !preg_match('/^[0-9]{10}$/', $mobile)) {
    echo json_encode(['success' => false, 'message' => 'Invalid Patient ID or mobile number format.']);
    exit();
}

if ($action === 'send') {
    rateLimitOrFail('report_otp_send', 5, 900, 'Too many OTP requests. Please wait before trying again.');

    if (!validateCaptcha($_POST['captcha_answer'] ?? null, $cms)) {
        captchaErrorResponse();
    }

    if (!reportOtpEnabled($cms)) {
        echo json_encode(['success' => true, 'message' => 'OTP not required.', 'otp_required' => false]);
        exit();
    }

    $result = sendReportOtp($db, $cms, $patient_id, $mobile);
    $result['otp_required'] = true;
    echo json_encode($result);
    exit();
}

if ($action === 'verify') {
    rateLimitOrFail('report_otp_verify', 10, 900, 'Too many verification attempts. Please wait and try again.');

    $otp = trim($_POST['otp'] ?? '');

    if (!reportOtpEnabled($cms)) {
        if (!validateCaptcha($_POST['captcha_answer'] ?? null, $cms)) {
            captchaErrorResponse();
        }
        grantReportAccess($patient_id, $mobile);
        $reports = fetchPatientReports($db, $patient_id, $mobile);
        echo json_encode(['success' => true, 'reports' => formatReports($reports), 'otp_required' => false]);
        exit();
    }

    if ($otp === '' || !preg_match('/^[0-9]{6}$/', $otp)) {
        echo json_encode(['success' => false, 'message' => 'Please enter the 6-digit OTP.']);
        exit();
    }

    if (!verifyReportOtp($db, $patient_id, $mobile, $otp)) {
        echo json_encode(['success' => false, 'message' => 'Invalid or expired OTP. Please request a new code.']);
        exit();
    }

    grantReportAccess($patient_id, $mobile);
    $reports = fetchPatientReports($db, $patient_id, $mobile);

    if (empty($reports)) {
        echo json_encode(['success' => false, 'message' => 'No reports found for this patient.']);
        exit();
    }

    echo json_encode([
        'success' => true,
        'message' => 'Verification successful.',
        'reports' => formatReports($reports),
        'otp_required' => true,
    ]);
    exit();
}

echo json_encode(['success' => false, 'message' => 'Invalid action.']);

function formatReports(array $reports): array
{
    return array_map(static function ($row) {
        return [
            'id' => (int) $row['id'],
            'test_name' => $row['test_name'],
            'uploaded_at' => $row['uploaded_at'],
        ];
    }, $reports);
}
