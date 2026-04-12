<?php
declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/lead-dispatch.php';
require_once __DIR__ . '/activity-log.php';

$dataDir = __DIR__ . '/data';
$leadsFile = $dataDir . '/homeowner-leads.json';

if (!is_dir($dataDir)) {
    mkdir($dataDir, 0775, true);
}

if (!file_exists($leadsFile)) {
    file_put_contents($leadsFile, json_encode([], JSON_PRETTY_PRINT));
}

function loadLeadFile(string $file): array {
    $raw = file_get_contents($file);
    $data = json_decode($raw ?: '[]', true);
    return is_array($data) ? $data : [];
}

function saveLeadFile(string $file, array $data): void {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

function normalizeZip(string $zip): string {
    return substr(preg_replace('/\D+/', '', trim($zip)), 0, 5);
}

/* ========================
   CAPTURE FORM DATA
======================== */
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$zip = normalizeZip($_POST['zip'] ?? '');
$projectType = trim($_POST['project_type'] ?? '');
$source = trim($_POST['source'] ?? 'Website Form');

/* ========================
   BUILD LEAD
======================== */
$leadRecord = [
    'id' => uniqid('lead_', true),
    'name' => $name,
    'email' => $email,
    'phone' => $phone,
    'zip' => $zip,
    'project_type' => $projectType,
    'lead_type' => strtolower($projectType),
    'request_label' => 'Speak with an Expert',
    'source' => $source,
    'submitted_at' => date('c')
];

/* ========================
   SAVE LEAD
======================== */
$allLeads = loadLeadFile($leadsFile);
$allLeads[] = $leadRecord;
saveLeadFile($leadsFile, $allLeads);

/* ========================
   ACTIVITY LOG
======================== */
logActivity('lead_submitted', $source, $leadRecord, true);

/* ========================
   DISPATCH EMAILS
======================== */
$dispatchResult = dispatchLeadOpportunity($leadRecord);

/* ========================
   REDIRECT
======================== */
header('Location: /thank-you.html');
exit;
