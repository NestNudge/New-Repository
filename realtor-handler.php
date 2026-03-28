<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {

  $name = trim($_POST["name"] ?? "Unknown");
  $email = trim($_POST["email"] ?? "");
  $phone = trim($_POST["phone"] ?? "");
  $zip = trim($_POST["zip"] ?? "");
  $leadType = trim($_POST["lead_type"] ?? "realtor");
  $source = trim($_POST["source"] ?? "NestNudge Realtor Form");

  if (empty($name) || empty($email) || empty($phone) || empty($zip)) {
    http_response_code(400);
    echo "Missing required fields for realtor form";
    exit;
  }

  $entry = date("Y-m-d H:i:s") . " | " .
           $name . " | " .
           $email . " | " .
           $phone . " | " .
           $zip . " | " .
           $leadType . " | " .
           $source . "\n";

  file_put_contents("realtor-leads.txt", $entry, FILE_APPEND);

  header("Location: /thank-you.html");
  exit;
}
?>
