<?php
require_once '../includes/db.php';
require_once '../includes/security.php';
require_once '../includes/otp.php';

$patient_id = isset($_REQUEST['patient_id']) ? trim($_REQUEST['patient_id']) : '';
$mobile = isset($_REQUEST['mobile']) ? trim($_REQUEST['mobile']) : '';
$report_id = isset($_REQUEST['report_id']) ? (int) $_REQUEST['report_id'] : 0;
$check_only = isset($_GET['check']) && $_GET['check'] === '1';

if (empty($patient_id) || empty($mobile)) {
    if ($check_only) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Missing fields.']);
    } else {
        http_response_code(400);
        die('Invalid access request.');
    }
    exit();
}

if (!preg_match('/^PAT-[0-9]+$/', $patient_id) || !preg_match('/^[0-9]{10}$/', $mobile)) {
    if ($check_only) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid Patient ID or mobile number format.']);
    } else {
        http_response_code(400);
        die('Invalid request.');
    }
    exit();
}

rateLimitOrFail('report_download', 15, 900, 'Too many download attempts. Please wait and try again.');

if (!hasReportAccess($patient_id, $mobile, $cms)) {
    if ($check_only) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Please verify OTP before downloading reports.', 'otp_required' => reportOtpEnabled($cms)]);
    } else {
        http_response_code(403);
        die('Access denied. Please verify OTP first.');
    }
    exit();
}

try {
    if ($check_only) {
        $reports = fetchPatientReports($db, $patient_id, $mobile);

        header('Content-Type: application/json');
        if (empty($reports)) {
            echo json_encode(['success' => false, 'message' => 'No report found matching this Patient ID and Mobile Number.']);
            exit();
        }

        $payload = array_map(static function ($row) {
            return [
                'id' => (int) $row['id'],
                'test_name' => $row['test_name'],
                'uploaded_at' => $row['uploaded_at'],
            ];
        }, $reports);

        echo json_encode(['success' => true, 'reports' => $payload]);
        exit();
    }

    if ($report_id <= 0) {
        http_response_code(400);
        die('Report ID is required.');
    }

    $stmt = $db->prepare("
        SELECT r.file_path, r.test_name
        FROM reports r
        INNER JOIN patients p ON r.patient_id = p.patient_id
        WHERE r.id = :report_id AND r.patient_id = :patient_id AND p.mobile = :mobile
        LIMIT 1
    ");
    $stmt->execute([
        ':report_id' => $report_id,
        ':patient_id' => $patient_id,
        ':mobile' => $mobile
    ]);

    $report = $stmt->fetch();

    if (!$report) {
        http_response_code(404);
        die('Report not found.');
    }

    $file_path = realpath(__DIR__ . '/../' . $report['file_path']);
    $uploads_root = realpath(__DIR__ . '/../uploads');

    if ($file_path === false || $uploads_root === false || !str_starts_with($file_path, $uploads_root)) {
        http_response_code(404);
        die('Report file is missing from the server.');
    }

    if (!is_file($file_path)) {
        http_response_code(404);
        die('Report file is missing from the server.');
    }

    header('Content-Description: File Transfer');
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $patient_id . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $report['test_name']) . '.pdf"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file_path));
    readfile($file_path);
    exit();

} catch (PDOException $e) {
    if ($check_only) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => appErrorMessage($e)]);
    } else {
        http_response_code(500);
        die('Unable to process your request.');
    }
}
