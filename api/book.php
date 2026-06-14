<?php
header('Content-Type: application/json');
require_once '../includes/db.php';
require_once '../includes/security.php';
require_once '../includes/captcha.php';
require_once '../includes/notifications.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed. Only POST is supported.']);
    exit();
}

requireCsrf();
rateLimitOrFail('book', 8, 900, 'Too many booking attempts. Please wait before trying again.');

if (!validateCaptcha($_POST['captcha_answer'] ?? null, $cms)) {
    captchaErrorResponse();
}

$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$test_type = isset($_POST['test_type']) ? trim($_POST['test_type']) : '';
$preferred_date = isset($_POST['preferred_date']) ? trim($_POST['preferred_date']) : '';
$address = isset($_POST['address']) ? trim($_POST['address']) : '';

if (empty($name) || empty($mobile) || empty($test_type) || empty($preferred_date) || empty($address)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all mandatory fields.']);
    exit();
}

if (!preg_match('/^[0-9]{10}$/', $mobile)) {
    echo json_encode(['success' => false, 'message' => 'Please provide a valid 10-digit mobile number.']);
    exit();
}

if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please provide a valid email address.']);
    exit();
}

try {
    $stmt = $db->prepare("INSERT INTO bookings (name, mobile, email, test_type, preferred_date, address, status) VALUES (:name, :mobile, :email, :test_type, :preferred_date, :address, 'Pending')");
    $result = $stmt->execute([
        ':name' => $name,
        ':mobile' => $mobile,
        ':email' => !empty($email) ? $email : null,
        ':test_type' => $test_type,
        ':preferred_date' => $preferred_date,
        ':address' => $address
    ]);

    if ($result) {
        notifyAdminBooking($db, $cms, [
            'name' => $name,
            'mobile' => $mobile,
            'email' => $email,
            'test_type' => $test_type,
            'preferred_date' => $preferred_date,
            'address' => $address,
        ]);
        echo json_encode([
            'success' => true,
            'message' => 'Booking request registered successfully! Our clinical representative will call you shortly to confirm your slot.'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save booking request. Please try again.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => appErrorMessage($e)]);
}
