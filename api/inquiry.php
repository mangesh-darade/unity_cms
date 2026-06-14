<?php
header('Content-Type: application/json');
require_once '../includes/db.php';
require_once '../includes/security.php';
require_once '../includes/captcha.php';
require_once '../includes/notifications.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit();
}

requireCsrf();
rateLimitOrFail('inquiry', 8, 900, 'Too many inquiry submissions. Please wait before trying again.');

if (!validateCaptcha($_POST['captcha_answer'] ?? null, $cms)) {
    captchaErrorResponse();
}

$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

if (empty($name) || empty($mobile) || empty($email) || empty($subject) || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please provide a valid email address.']);
    exit();
}

if (!preg_match('/^[0-9]{10}$/', $mobile)) {
    echo json_encode(['success' => false, 'message' => 'Please provide a valid 10-digit mobile number.']);
    exit();
}

try {
    $stmt = $db->prepare("INSERT INTO inquiries (name, mobile, email, subject, message, status) VALUES (:name, :mobile, :email, :subject, :message, 'New')");
    $result = $stmt->execute([
        ':name' => $name,
        ':mobile' => $mobile,
        ':email' => $email,
        ':subject' => $subject,
        ':message' => $message
    ]);

    if ($result) {
        notifyAdminInquiry($db, $cms, [
            'name' => $name,
            'mobile' => $mobile,
            'email' => $email,
            'subject' => $subject,
            'message' => $message,
        ]);
        echo json_encode([
            'success' => true,
            'message' => 'Your inquiry has been submitted successfully! Our helpdesk will contact you shortly.'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save your inquiry. Please try again.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => appErrorMessage($e)]);
}
