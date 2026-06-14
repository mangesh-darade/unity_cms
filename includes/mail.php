<?php

function sendAppMail(string $to, string $subject, string $body, array $cms): bool
{
    $to = trim($to);
    if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $fromEmail = trim($cms['mail_from_email'] ?? ($cms['support_email'] ?? ''));
    $fromName = trim($cms['mail_from_name'] ?? ($cms['site_name'] ?? 'Unity Clinical Laboratory'));

    if ($fromEmail === '' || !filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/plain; charset=UTF-8',
        'From: ' . $fromName . ' <' . $fromEmail . '>',
        'Reply-To: ' . $fromEmail,
        'X-Mailer: PHP/' . phpversion(),
    ];

    return @mail($to, $subject, $body, implode("\r\n", $headers));
}

function sendSmsMessage(string $mobile, string $message, array $cms): bool
{
    $provider = $cms['sms_provider'] ?? 'none';
    $mobile = preg_replace('/[^0-9]/', '', $mobile);

    if ($provider !== 'msg91' || strlen($mobile) !== 10) {
        return false;
    }

    $apiKey = trim($cms['msg91_api_key'] ?? '');
    if ($apiKey === '') {
        return false;
    }

    $sender = trim($cms['msg91_sender_id'] ?? 'UNITY');
    $url = 'https://control.msg91.com/api/sendhttp.php?' . http_build_query([
        'authkey' => $apiKey,
        'mobiles' => '91' . $mobile,
        'message' => $message,
        'sender' => $sender,
        'route' => '4',
        'country' => '91',
    ]);

    $context = stream_context_create(['http' => ['timeout' => 10]]);
    $response = @file_get_contents($url, false, $context);

    return $response !== false;
}
