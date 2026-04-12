<?php
declare(strict_types=1);

require_once __DIR__ . '/phpmailer/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/phpmailer/PHPMailer-master/src/SMTP.php';
require_once __DIR__ . '/phpmailer/PHPMailer-master/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function nn_normalize_zip(string $zip): string
{
    return substr(preg_replace('/\D+/', '', trim($zip)), 0, 5);
}

function nn_normalize_email(string $email): string
{
    return trim(strtolower($email));
}

function nn_load_json_array(string $file): array
{
    if (!file_exists($file)) {
        return [];
    }

    $raw = file_get_contents($file);
    $data = json_decode($raw ?: '[]', true);

    return is_array($data) ? $data : [];
}

function nn_save_json_array(string $file, array $data): void
{
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function nn_match_partners(array $partners, string $leadType, string $zip): array
{
    $matches = [];

    foreach ($partners as $partner) {
        $partnerType = strtolower(trim((string)($partner['type'] ?? '')));
        $active = (bool)($partner['active'] ?? false);
        $partnerZips = $partner['zips'] ?? [];

        if (!$active) {
            continue;
        }

        if ($partnerType !== strtolower($leadType)) {
            continue;
        }

        if (!is_array($partnerZips) || !in_array($zip, $partnerZips, true)) {
            continue;
        }

        $matches[] = $partner;
    }

    return $matches;
}

function buildRoofPartnerEmail(array $lead, array $partner): array
{
    $partnerName = trim((string)($partner['name'] ?? 'Partner'));
    $zip = trim((string)($lead['zip'] ?? ''));
    $submittedAt = trim((string)($lead['submitted_at'] ?? date('Y-m-d H:i:s')));
    $consumerName = trim((string)($lead['name'] ?? ''));
    $consumerEmail = trim((string)($lead['email'] ?? ''));
    $consumerPhone = trim((string)($lead['phone'] ?? ''));

    return [
        'subject' => "New Free Roof Inspection Request in {$zip} (Not Assigned)",
        'message' =>
"Hi {$partnerName},

A homeowner in your service area just submitted a request to speak with an expert regarding a:

Free Roof Inspection

Lead Details
Name: {$consumerName}
Email: {$consumerEmail}
Phone: {$consumerPhone}
ZIP: {$zip}
Submitted: {$submittedAt}
Category: Roofing Opportunity

At the moment, this request has not been assigned.

NestNudge helps contractors access homeowner opportunities by service type and ZIP area.

View the NestNudge Partners page:
https://nestnudge.com/partners.html

Apply to become a partner:
https://nestnudge.com/contractor-signup.php

Partner login:
https://nestnudge.com/contractor-login.php

Service areas may be limited to help reduce overcrowding.

- NestNudge
"
    ];
}

function buildLoanPartnerEmail(array $lead, array $partner): array
{
    $partnerName = trim((string)($partner['name'] ?? 'Partner'));
    $zip = trim((string)($lead['zip'] ?? ''));
    $submittedAt = trim((string)($lead['submitted_at'] ?? date('Y-m-d H:i:s')));
    $consumerName = trim((string)($lead['name'] ?? ''));
    $consumerEmail = trim((string)($lead['email'] ?? ''));
    $consumerPhone = trim((string)($lead['phone'] ?? ''));

    return [
        'subject' => "New Personal Loan Request in {$zip} (Not Assigned)",
        'message' =>
"Hi {$partnerName},

A consumer in your service area just submitted a request to speak with an expert regarding:

Personal Loan or Funding Options

Lead Details
Name: {$consumerName}
Email: {$consumerEmail}
Phone: {$consumerPhone}
ZIP: {$zip}
Submitted: {$submittedAt}
Category: Loan Opportunity

At the moment, this request has not been assigned.

NestNudge helps partners access consumer opportunities by category and area.

View the NestNudge Partners page:
https://nestnudge.com/partners.html

Apply to become a partner:
https://nestnudge.com/contractor-signup.php

Partner login:
https://nestnudge.com/contractor-login.php

Availability may be limited by service area.

- NestNudge
"
    ];
}

function buildRealtorPartnerEmail(array $lead, array $partner): array
{
    $partnerName = trim((string)($partner['name'] ?? 'Partner'));
    $zip = trim((string)($lead['zip'] ?? ''));
    $submittedAt = trim((string)($lead['submitted_at'] ?? date('Y-m-d H:i:s')));
    $consumerName = trim((string)($lead['name'] ?? ''));
    $consumerEmail = trim((string)($lead['email'] ?? ''));
    $consumerPhone = trim((string)($lead['phone'] ?? ''));

    return [
        'subject' => "New Realtor Guidance Request in {$zip} (Not Assigned)",
        'message' =>
"Hi {$partnerName},

A consumer in your service area just submitted a request to speak with an expert regarding:

Realtor Guidance

Lead Details
Name: {$consumerName}
Email: {$consumerEmail}
Phone: {$consumerPhone}
ZIP: {$zip}
Submitted: {$submittedAt}
Category: Realtor Opportunity

At the moment, this request has not been assigned.

NestNudge helps real estate partners access location-based opportunities tied to real consumer interest.

View the NestNudge Partners page:
https://nestnudge.com/partners.html

Apply to become a partner:
https://nestnudge.com/contractor-signup.php

Partner login:
https://nestnudge.com/contractor-login.php

Availability may be limited by service area.

- NestNudge
"
    ];
}

function buildContractorPartnerEmail(array $lead, array $partner): array
{
    $partnerName = trim((string)($partner['name'] ?? 'Partner'));
    $zip = trim((string)($lead['zip'] ?? ''));
    $submittedAt = trim((string)($lead['submitted_at'] ?? date('Y-m-d H:i:s')));
    $consumerName = trim((string)($lead['name'] ?? ''));
    $consumerEmail = trim((string)($lead['email'] ?? ''));
    $consumerPhone = trim((string)($lead['phone'] ?? ''));

    return [
        'subject' => "New Home Improvement Request in {$zip} (Not Assigned)",
        'message' =>
"Hi {$partnerName},

A homeowner in your service area just submitted a request regarding a home improvement project.

Lead Details
Name: {$consumerName}
Email: {$consumerEmail}
Phone: {$consumerPhone}
ZIP: {$zip}
Submitted: {$submittedAt}
Category: Contractor Opportunity

At the moment, this request has not been assigned.

View the NestNudge Partners page:
https://nestnudge.com/partners.html

Apply to become a partner:
https://nestnudge.com/contractor-signup.php

Partner login:
https://nestnudge.com/contractor-login.php

- NestNudge
"
    ];
}

function nn_build_email_package(array $lead, array $partner): array
{
    $leadType = strtolower(trim((string)($lead['lead_type'] ?? '')));

    if ($leadType === 'roof') {
        return buildRoofPartnerEmail($lead, $partner);
    }

    if ($leadType === 'loan') {
        return buildLoanPartnerEmail($lead, $partner);
    }

    if ($leadType === 'realtor') {
        return buildRealtorPartnerEmail($lead, $partner);
    }

    if ($leadType === 'contractor') {
        return buildContractorPartnerEmail($lead, $partner);
    }

    return [
        'subject' => 'New Opportunity (Not Assigned)',
        'message' => "A new opportunity was submitted.\nView https://nestnudge.com/partners.html"
    ];
}

function nn_send_email(string $to, string $subject, string $message): array
{
    $config = require __DIR__ . '/smtp-config.php';
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = (string)$config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = (string)$config['username'];
        $mail->Password = (string)$config['password'];
        $mail->Port = (int)$config['port'];
        $mail->Timeout = (int)($config['timeout'] ?? 20);

        $encryption = strtolower((string)($config['encryption'] ?? 'ssl'));
        if ($encryption === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }

        $mail->setFrom((string)$config['from_email'], (string)$config['from_name']);
        $mail->addReplyTo((string)$config['reply_to'], (string)$config['from_name']);
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->Body = $message;
        $mail->isHTML(false);
        $mail->send();

        return [
            'sent' => true,
            'error' => ''
        ];
    } catch (Exception $e) {
        return [
            'sent' => false,
            'error' => $mail->ErrorInfo ?: $e->getMessage()
        ];
    }
}

function nn_build_internal_message(array $lead): array
{
    $leadType = trim((string)($lead['lead_type'] ?? 'unknown'));
    $requestLabel = trim((string)($lead['request_label'] ?? 'Speak with an Expert'));

    $subject = "New NestNudge Lead: {$requestLabel} [{$leadType}]";

    $message =
"New lead received

Name: " . trim((string)($lead['name'] ?? '')) . "
Email: " . trim((string)($lead['email'] ?? '')) . "
Phone: " . trim((string)($lead['phone'] ?? '')) . "
ZIP: " . trim((string)($lead['zip'] ?? '')) . "
Lead Type: " . trim((string)($lead['lead_type'] ?? '')) . "
Request: " . trim((string)($lead['request_label'] ?? '')) . "
Source: " . trim((string)($lead['source'] ?? '')) . "
Submitted: " . trim((string)($lead['submitted_at'] ?? '')) . "
";

    return [
        'subject' => $subject,
        'message' => $message
    ];
}

function dispatchLeadOpportunity(array $lead): array
{
    $baseDir = __DIR__;
    $dataDir = $baseDir . '/data';
    $partnersFile = $dataDir . '/partners.json';
    $dispatchLogFile = $dataDir . '/lead-dispatch-log.json';

    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0775, true);
    }

    if (!file_exists($dispatchLogFile)) {
        file_put_contents($dispatchLogFile, json_encode([], JSON_PRETTY_PRINT));
    }

    $normalizedLead = [
        'name' => trim((string)($lead['name'] ?? '')),
        'email' => nn_normalize_email((string)($lead['email'] ?? '')),
        'phone' => trim((string)($lead['phone'] ?? '')),
        'zip' => nn_normalize_zip((string)($lead['zip'] ?? '')),
        'lead_type' => strtolower(trim((string)($lead['lead_type'] ?? ''))),
        'request_label' => trim((string)($lead['request_label'] ?? 'Speak with an Expert')),
        'source' => trim((string)($lead['source'] ?? 'Unknown Source')),
        'submitted_at' => date('Y-m-d H:i:s')
    ];

    if ($normalizedLead['zip'] === '' || $normalizedLead['lead_type'] === '') {
        return [
            'success' => false,
            'message' => 'Lead dispatch skipped because lead_type or zip is missing.',
            'matched_count' => 0,
            'sent_count' => 0
        ];
    }

    $partners = nn_load_json_array($partnersFile);
    $matchedPartners = nn_match_partners($partners, $normalizedLead['lead_type'], $normalizedLead['zip']);

    $sentCount = 0;
    $dispatchEntries = nn_load_json_array($dispatchLogFile);

    $internalEmail = nn_build_internal_message($normalizedLead);
    $internalSend = nn_send_email('leads@nestnudge.com', $internalEmail['subject'], $internalEmail['message']);

    $dispatchEntries[] = [
        'lead' => $normalizedLead,
        'partner_name' => 'NestNudge Internal',
        'partner_email' => 'leads@nestnudge.com',
        'sent' => $internalSend['sent'],
        'error' => $internalSend['error'],
        'sent_at' => date('c')
    ];

    if ($internalSend['sent']) {
        $sentCount++;
    }

    foreach ($matchedPartners as $partner) {
        $to = trim((string)($partner['email'] ?? ''));
        if ($to === '') {
            continue;
        }

        $emailPackage = nn_build_email_package($normalizedLead, $partner);
        $sendResult = nn_send_email($to, $emailPackage['subject'], $emailPackage['message']);

        $dispatchEntries[] = [
            'lead' => $normalizedLead,
            'partner_name' => (string)($partner['name'] ?? ''),
            'partner_email' => $to,
            'sent' => $sendResult['sent'],
            'error' => $sendResult['error'],
            'sent_at' => date('c')
        ];

        if ($sendResult['sent']) {
            $sentCount++;
        }
    }

    nn_save_json_array($dispatchLogFile, $dispatchEntries);

    return [
        'success' => true,
        'message' => 'Lead dispatch processed.',
        'matched_count' => count($matchedPartners),
        'sent_count' => $sentCount
    ];
}
