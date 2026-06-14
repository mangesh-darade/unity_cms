<?php
include '../includes/db.php';

$patient_id = isset($_GET['patient_id']) ? trim($_GET['patient_id']) : '';
$mobile = isset($_GET['mobile']) ? trim($_GET['mobile']) : '';
$check_only = isset($_GET['check']) && $_GET['check'] === '1';

if (empty($patient_id) || empty($mobile)) {
    if ($check_only) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Missing fields.']);
    } else {
        die("Invalid access request.");
    }
    exit();
}

try {
    // Join reports and patients to verify mobile number match
    $stmt = $db->prepare("
        SELECT r.file_path, r.test_name 
        FROM reports r 
        INNER JOIN patients p ON r.patient_id = p.patient_id 
        WHERE r.patient_id = :patient_id AND p.mobile = :mobile
        ORDER BY r.id DESC 
        LIMIT 1
    ");
    $stmt->execute([
        ':patient_id' => $patient_id,
        ':mobile' => $mobile
    ]);
    
    $report = $stmt->fetch();
    
    if (!$report) {
        if ($check_only) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'No report found matching this Patient ID and Mobile Number.']);
        } else {
            die("Report not found.");
        }
        exit();
    }
    
    // Check if file actually exists on server
    // Note: stored file path will be relative or absolute, let's resolve it relative to uploads folder
    $file_path = __DIR__ . '/../' . $report['file_path'];
    
    if (!file_exists($file_path)) {
        if ($check_only) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Report file missing on server. Please contact support.']);
        } else {
            die("Report file is missing from the server.");
        }
        exit();
    }
    
    if ($check_only) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    } else {
        // Stream the file for download
        header('Content-Description: File Transfer');
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $patient_id . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $report['test_name']) . '.pdf"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_path));
        readfile($file_path);
        exit();
    }
    
} catch (PDOException $e) {
    if ($check_only) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    } else {
        die("Database error: " . $e->getMessage());
    }
}
?>
