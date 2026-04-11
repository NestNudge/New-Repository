<?php
declare(strict_types=1);

require_once __DIR__ . '/lead-dispatch.php';

$dataDir = __DIR__ . '/data';
$leadsFile = $dataDir . '/realtor-leads.json';

if (!is_dir($dataDir)) {
    mkdir($dataDir, 0775, true);
}
if (!file_exists($leadsFile)) {
    file_put_contents($leadsFile, json_encode([], JSON_PRETTY_PRINT));
}

function loadLeadFile(string $file): array
{
    if (!file_exists($file)) {
        return [];
    }

    $raw = file_get_contents($file);
    $data = json_decode($raw ?: '[]', true);

    return is_array($data) ? $data : [];
}

function saveLeadFile(string $file, array $data): void
{
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function normalizeZip(string $zip): string
{
    return substr(preg_replace('/\D+/', '', trim($zip)), 0, 5);
}

$name = trim((string)($_POST['name'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$phone = trim((string)($_POST['phone'] ?? ''));
$zip = normalizeZip((string)($_POST['zip'] ?? ''));
$source = trim((string)($_POST['source'] ?? 'Realtor Form'));

$leadRecord = [
    'id' => uniqid('realtor_', true),
    'name' => $name,
    'email' => $email,
    'phone' => $phone,
    'zip' => $zip,
    'project_type' => 'realtor',
    'lead_type' => 'realtor',
    'request_label' => 'Speak with an Expert regarding Realtor Guidance',
    'source' => $source,
    'submitted_at' => date('c')
];

$allLeads = loadLeadFile($leadsFile);
$allLeads[] = $leadRecord;
saveLeadFile($leadsFile, $allLeads);

dispatchLeadOpportunity($leadRecord);

header('Location: /thank-you.html');
exit;
?>
