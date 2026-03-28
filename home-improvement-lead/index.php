<?php
declare(strict_types=1);

// ==========================
// CONNECT TO DATABASE
// ==========================
$servername = "localhost";
$username   = "mikeboggus";
$password   = "@0nly1Madden!@05";
$dbname     = "leadbizo_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    exit("Database connection failed.");
}

// ==========================
// CLEAN INPUT
// ==========================
function clean_input(string $value): string {
    $value = trim($value);
    $value = str_replace(["\r", "\n"], ' ', $value);
    return $value;
}

// ==========================
// GET FORM DATA
// ==========================
$name         = clean_input($_POST['name'] ?? '');
$email        = clean_input($_POST['email'] ?? '');
$phone        = clean_input($_POST['phone'] ?? '');
$zip          = clean_input($_POST['zip'] ?? '');
$address      = clean_input($_POST['address'] ?? '');
$project_type = clean_input($_POST['project_type'] ?? '');
$home_value   = clean_input($_POST['home_value'] ?? '');
$source       = clean_input($_POST['source'] ?? 'NestNudge Website');

if ($name === '' || $zip === '') {
    http_response_code(400);
    exit("Missing required fields.");
}

// ==========================
// LEAD TYPE LOGIC
// ==========================
$lead_type = "planning";

if ($project_type === "roof") {
    $lead_type = "urgent";
}

if ((float)$home_value > 400000) {
    $lead_type = "investment";
}

// ==========================
// SAVE TO DATABASE SAFELY
// ==========================
$stmt = $conn->prepare("
    INSERT INTO leads (name, email, phone, zip, project_type, home_value, lead_type)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

if (!$stmt) {
    http_response_code(500);
    exit("Database prepare failed.");
}

$stmt->bind_param(
    "sssssss",
    $name,
    $email,
    $phone,
    $zip,
    $project_type,
    $home_value,
    $lead_type
);

$stmt->execute();
$stmt->close();

// ==========================
// ROUTING EMAILS
// ==========================
if ($lead_type === "urgent") {
    @mail("contractor@email.com", "New Roofing Lead", "Name: $name\nPhone: $phone\nZip: $zip\nSource: $source");
}

if ($lead_type === "planning" || $lead_type === "investment") {
    @mail("realtor@email.com", "New Homeowner Lead", "Name: $name\nEmail: $email\nZip: $zip\nSource: $source");
}

if ($lead_type === "investment") {
    @mail("loan@email.com", "New Loan Lead", "Name: $name\nPhone: $phone\nZip: $zip\nSource: $source");
}

// ==========================
// OPTIONAL CSV BACKUP
// ==========================
$logDir = __DIR__ . '/logs';
$logFile = $logDir . '/nestnudge-leads.csv';

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
            'address',
            'project_type',
            'home_value',
            'lead_type',
            'source'
        ]);
    }

    fputcsv($fp, [
        date('Y-m-d H:i:s'),
        $name,
        $email,
        $phone,
        $zip,
        $address,
        $project_type,
        $home_value,
        $lead_type,
        $source
    ]);

    fclose($fp);
}

// ==========================
// REDIRECT TO THANK YOU PAGE
// ==========================
header("Location: /thank-you.html");
exit;

$conn->close();
?>
