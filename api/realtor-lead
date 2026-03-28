<?php
declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

function clean_input(string $value): string {
    $value = trim($value);
    $value = str_replace(["\r", "\n"], ' ', $value);
    return $value;
}

$name     = clean_input($_POST['name'] ?? '');
$email    = clean_input($_POST['email'] ?? '');
$phone    = clean_input($_POST['phone'] ?? '');
$zip      = clean_input($_POST['zip'] ?? '');
$goal     = clean_input($_POST['goal'] ?? '');
$source   = clean_input($_POST['source'] ?? 'NestNudge Website');
$leadType = clean_input($_POST['lead_type'] ?? 'realtor');

if ($name === '' || $email === '' || $phone === '' || $zip === '') {
    http_response_code(400);
    exit('Missing required fields');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    exit('Invalid email address');
}

$to = 'mike@leadbizo.com';
$subject = 'New Realtor Lead from NestNudge';

$messageLines = [
    'New realtor lead received from NestNudge.',
    '',
    'Name: ' . $name,
    'Email: ' . $email,
    'Phone: ' . $phone,
    'Zip: ' . $zip,
    'Goal: ' . ($goal !== '' ? $goal : 'Not provided'),
    'Lead Type: ' . $leadType,
    'Source: ' . $source,
    'Date: ' . date('Y-m-d H:i:s'),
    'IP Address: ' . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown'),
    'User Agent: ' . ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'),
];

$message = implode("\n", $messageLines);

$headers = [];
$headers[] = 'From: NestNudge <noreply@leadbizo.com>';
$headers[] = 'Reply-To: ' . $email;
$headers[] = 'MIME-Version: 1.0';
$headers[] = 'Content-Type: text/plain; charset=UTF-8';

$mailSuccess = mail($to, $subject, $message, implode("\r\n", $headers));

$logDir = __DIR__ . '/logs';
$logFile = $logDir . '/realtor-leads.csv';

if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

$fileExists = file_exists($logFile);
$fp = fopen($logFile, 'a');

if ($fp !== false) {
    if (!$fileExists) {
        fputcsv($fp, [
            'date',
            'name',
            'email',
            'phone',
            'zip',
            'goal',
            'lead_type',
            'source',
            'ip_address',
            'user_agent'
        ]);
    }

    fputcsv($fp, [
        date('Y-m-d H:i:s'),
        $name,
        $email,
        $phone,
        $zip,
        $goal,
        $leadType,
        $source,
        $_SERVER['REMOTE_ADDR'] ?? '',
        $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);

    fclose($fp);
}

if ($mailSuccess) {
    header('Location: /thank-you.html');
    exit;
} else {
    header('Location: /thank-you.html?status=email-issue');
    exit;
}
?>
