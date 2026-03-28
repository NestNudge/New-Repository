<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {

  $name = trim($_POST["name"] ?? "Unknown");
  $address = trim($_POST["address"] ?? "");
  $zip = trim($_POST["zip"] ?? "");
  $email = trim($_POST["email"] ?? "");
  $phone = trim($_POST["phone"] ?? "");
  $project = trim($_POST["project_type"] ?? "");
  $source = trim($_POST["source"] ?? "NestNudge");

  // Validation by form type
  if ($project === "estimator") {
    if (empty($address) || empty($zip)) {
      http_response_code(400);
      echo "Missing required fields for estimator form";
      exit;
    }
  } else {
    // For funding and other non-address forms
    if (empty($email) || empty($zip)) {
      http_response_code(400);
      echo "Missing required fields for funding form";
      exit;
    }
  }

  // Save lead
  $entry = date("Y-m-d H:i:s") . " | " .
           $name . " | " .
           $address . " | " .
           $zip . " | " .
           $email . " | " .
           $phone . " | " .
           $project . " | " .
           $source . "\n";

  file_put_contents("leads.txt", $entry, FILE_APPEND);

  // Redirect after success
  header("Location: /thank-you.html");
  exit;
}
?>
