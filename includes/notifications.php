<?php

require_once __DIR__ . '/mail.php';

function notificationsEnabled(array $cms, string $key): bool
{
    return ($cms[$key] ?? '1') === '1';
}

function adminNotifyEmail(array $cms): string
{
    $email = trim($cms['notify_admin_email'] ?? ($cms['support_email'] ?? ''));
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
}

function notifyAdminBooking(PDO $db, array $cms, array $booking): void
{
    if (!notificationsEnabled($cms, 'notify_on_booking')) {
        return;
    }

    $to = adminNotifyEmail($cms);
    if ($to === '') {
        return;
    }

    $site = $cms['site_name'] ?? 'Unity Clinical Laboratory';
    $subject = "[{$site}] New Home Collection Booking";
    $body = "A new home collection booking was received.\n\n"
        . "Name: {$booking['name']}\n"
        . "Mobile: {$booking['mobile']}\n"
        . "Email: " . ($booking['email'] ?? 'N/A') . "\n"
        . "Test/Package: {$booking['test_type']}\n"
        . "Preferred Date: {$booking['preferred_date']}\n"
        . "Address: {$booking['address']}\n\n"
        . 'Review in admin: ' . BASE_URL . 'admin/bookings.php';

    sendAppMail($to, $subject, $body, $cms);
}

function notifyAdminInquiry(PDO $db, array $cms, array $inquiry): void
{
    if (!notificationsEnabled($cms, 'notify_on_inquiry')) {
        return;
    }

    $to = adminNotifyEmail($cms);
    if ($to === '') {
        return;
    }

    $site = $cms['site_name'] ?? 'Unity Clinical Laboratory';
    $subject = "[{$site}] New Patient Inquiry";
    $body = "A new inquiry was submitted.\n\n"
        . "Name: {$inquiry['name']}\n"
        . "Mobile: {$inquiry['mobile']}\n"
        . "Email: {$inquiry['email']}\n"
        . "Subject: {$inquiry['subject']}\n\n"
        . "Message:\n{$inquiry['message']}\n\n"
        . 'Review in admin: ' . BASE_URL . 'admin/inquiries.php';

    sendAppMail($to, $subject, $body, $cms);
}

function notifyCustomerBooking(array $cms, array $booking): void
{
    if (!notificationsEnabled($cms, 'notify_customer_on_booking')) {
        return;
    }

    $email = trim($booking['email'] ?? '');
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return;
    }

    $site = $cms['site_name'] ?? 'Unity Clinical Laboratory';
    $subject = "[{$site}] Home Collection Booking Received";
    $body = "Dear {$booking['name']},\n\n"
        . "Thank you for booking a home sample collection with {$site}.\n\n"
        . "Booking details:\n"
        . "Test/Package: {$booking['test_type']}\n"
        . "Preferred Date: {$booking['preferred_date']}\n"
        . "Address: {$booking['address']}\n\n"
        . "Our coordinator will call you within 30 minutes to confirm your slot.\n"
        . "For urgent queries, call " . ($cms['support_phone'] ?? '') . ".\n\n"
        . "Regards,\n{$site} Team";

    sendAppMail($email, $subject, $body, $cms);
}

function notifyAdminReview(PDO $db, array $cms, array $review): void
{
    if (!notificationsEnabled($cms, 'notify_on_review')) {
        return;
    }

    $to = adminNotifyEmail($cms);
    if ($to === '') {
        return;
    }

    $site = $cms['site_name'] ?? 'Unity Clinical Laboratory';
    $subject = "[{$site}] New Patient Review (Pending Approval)";
    $body = "A new patient review was submitted from the website.\n\n"
        . "Name: {$review['name']}\n"
        . "Email: {$review['email']}\n"
        . "Mobile: {$review['mobile']}\n"
        . "Tag: " . ($review['designation'] ?? 'Patient') . "\n\n"
        . "Review:\n{$review['review']}\n\n"
        . 'Approve or manage in admin: ' . BASE_URL . 'admin/cms.php#tab-testimonials';

    sendAppMail($to, $subject, $body, $cms);
}

function notifyCustomerReviewReceived(array $cms, array $review): void
{
    $email = trim($review['email'] ?? '');
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return;
    }

    $site = $cms['site_name'] ?? 'Unity Clinical Laboratory';
    $subject = "[{$site}] We Received Your Review";
    $body = "Dear {$review['name']},\n\n"
        . "Thank you for sharing your experience with {$site}.\n\n"
        . "We have received your review and our team will verify it shortly. "
        . "Once approved, it will appear on our website homepage.\n\n"
        . "If you have any urgent concerns, please call us at "
        . ($cms['support_phone'] ?? '') . ".\n\n"
        . "Regards,\n{$site} Team";

    sendAppMail($email, $subject, $body, $cms);
}

function notifyPatientReviewApproved(array $cms, array $review): void
{
    $email = trim($review['email'] ?? '');
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return;
    }

    $site = $cms['site_name'] ?? 'Unity Clinical Laboratory';
    $subject = "[{$site}] Your Review Is Now Live";
    $body = "Dear {$review['author']},\n\n"
        . "Good news! Your review has been approved and is now published on our website.\n\n"
        . "Thank you for trusting {$site}.\n\n"
        . 'Visit: ' . BASE_URL . "\n\n"
        . "Regards,\n{$site} Team";

    sendAppMail($email, $subject, $body, $cms);
}

function notifyPatientReport(PDO $db, array $cms, string $patientId, string $testName): void
{
    if (!notificationsEnabled($cms, 'notify_on_report')) {
        return;
    }

    $stmt = $db->prepare('SELECT name, email, mobile FROM patients WHERE patient_id = :patient_id');
    $stmt->execute([':patient_id' => $patientId]);
    $patient = $stmt->fetch();

    if (!$patient) {
        return;
    }

    $site = $cms['site_name'] ?? 'Unity Clinical Laboratory';
    $downloadUrl = BASE_URL . 'download.php';
    $message = "Dear {$patient['name']}, your lab report ({$testName}) from {$site} is ready. Download securely at: {$downloadUrl} using Patient ID {$patientId} and your registered mobile number.";

    $sent = false;
    if (!empty($patient['email']) && filter_var($patient['email'], FILTER_VALIDATE_EMAIL)) {
        $sent = sendAppMail(
            $patient['email'],
            "[{$site}] Your Lab Report Is Ready",
            $message,
            $cms
        );
    }

    if (!$sent) {
        sendSmsMessage($patient['mobile'], $message, $cms);
    }
}
