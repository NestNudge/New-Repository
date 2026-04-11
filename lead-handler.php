<?php
declare(strict_types=1);

require_once __DIR__ . '/lead-dispatch.php';

$dataDir = __DIR__ . '/data';
$leadsFile = $dataDir . '/homeowner-leads.json';

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

function normalizeLeadType(string $projectType, string $source): string
{
    $value = strtolower(trim($projectType . ' ' . $source));

    if (str_contains($value, 'roof')) return 'roof';
    if (str_contains($value, 'loan')) return 'loan';
    if (str_contains($value, 'fund')) return 'loan';
    if (str_contains($value, 'debt')) return 'loan';
    if (str_contains($value, 'payment')) return 'loan';
    if (str_contains($value, 'realtor')) return 'realtor';
    if (str_contains($value, 'buy')) return 'realtor';
    if (str_contains($value, 'sell')) return 'realtor';

    return 'roof';
}

function requestLabelFromLeadType(string $leadType): string
{
    $map = [
        'roof' => 'Speak with an Expert regarding a Free Roof Inspection',
        'loan' => 'Speak with an Expert regarding Personal Loan Options',
        'realtor' => 'Speak with an Expert regarding Realtor Guidance'
    ];

    return $map[$leadType] ?? 'Speak with an Expert';
}

$name = trim((string)($_POST['name'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$phone = trim((string)($_POST['phone'] ?? ''));
$zip = normalizeZip((string)($_POST['zip'] ?? ''));
$projectType = trim((string)($_POST['project_type'] ?? ''));
$source = trim((string)($_POST['source'] ?? 'Website Form'));

$leadType = normalizeLeadType($projectType, $source);
$requestLabel = requestLabelFromLeadType($leadType);

$leadRecord = [
    'id' => uniqid('lead_', true),
    'name' => $name,
    'email' => $email,
    'phone' => $phone,
    'zip' => $zip,
    'project_type' => $projectType,
    'lead_type' => $leadType,
    'request_label' => $requestLabel,
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
