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
rateLimitOrFail('review', 5, 900, 'Too many review submissions. Please wait before trying again.');

if (!validateCaptcha($_POST['captcha_answer'] ?? null, $cms)) {
    captchaErrorResponse();
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$mobile = trim($_POST['mobile'] ?? '');
$designation = trim($_POST['designation'] ?? 'Patient');
$review = trim($_POST['review'] ?? '');

if ($name === '' || $email === '' || $mobile === '' || $review === '') {
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

if (strlen($review) < 20) {
    echo json_encode(['success' => false, 'message' => 'Please write at least 20 characters in your review.']);
    exit();
}

if (strlen($review) > 2000) {
    echo json_encode(['success' => false, 'message' => 'Review is too long. Please keep it under 2000 characters.']);
    exit();
}

if ($designation === '') {
    $designation = 'Patient';
}

try {
    $nextSeq = (int) $db->query("SELECT COALESCE(MAX(sequence), 0) + 1 FROM cms_testimonials")->fetchColumn();

    $stmt = $db->prepare(
        "INSERT INTO cms_testimonials (text, author, designation, sequence, status, email, mobile, source)
         VALUES (:text, :author, :designation, :sequence, 'pending', :email, :mobile, 'website')"
    );
    $result = $stmt->execute([
        ':text' => $review,
        ':author' => $name,
        ':designation' => $designation,
        ':sequence' => $nextSeq,
        ':email' => $email,
        ':mobile' => $mobile,
    ]);

    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'Failed to save your review. Please try again.']);
        exit();
    }

    $reviewId = (int) $db->lastInsertId();
    $reviewData = [
        'id' => $reviewId,
        'name' => $name,
        'email' => $email,
        'mobile' => $mobile,
        'designation' => $designation,
        'review' => $review,
    ];

    notifyAdminReview($db, $cms, $reviewData);
    notifyCustomerReviewReceived($cms, $reviewData);

    echo json_encode([
        'success' => true,
        'message' => 'Thank you! Your review was submitted successfully. We will publish it on our website after a quick verification.',
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => appErrorMessage($e)]);
}
