<?php
header('Content-Type: application/json');
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed. Only POST is supported.']);
    exit();
}

$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$test_type = isset($_POST['test_type']) ? trim($_POST['test_type']) : '';
$preferred_date = isset($_POST['preferred_date']) ? trim($_POST['preferred_date']) : '';
$address = isset($_POST['address']) ? trim($_POST['address']) : '';

// Validation
if (empty($name) || empty($mobile) || empty($test_type) || empty($preferred_date) || empty($address)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all mandatory fields.']);
    exit();
}

if (!preg_match('/^[0-9]{10}$/', $mobile)) {
    echo json_encode(['success' => false, 'message' => 'Please provide a valid 10-digit mobile number.']);
    exit();
}

try {
    $stmt = $db->prepare("INSERT INTO bookings (name, mobile, email, test_type, preferred_date, address, status) VALUES (:name, :mobile, :email, :test_type, :preferred_date, :address, 'Pending')");
    $result = $stmt->execute([
        ':name' => htmlspecialchars($name),
        ':mobile' => htmlspecialchars($mobile),
        ':email' => !empty($email) ? htmlspecialchars($email) : null,
        ':test_type' => htmlspecialchars($test_type),
        ':preferred_date' => htmlspecialchars($preferred_date),
        ':address' => htmlspecialchars($address)
    ]);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Booking request registered successfully! Our clinical representative will call you shortly to confirm your slot.'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save booking request. Please try again.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
