<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {

  $name = $_POST["name"] ?? "Unknown";
  $address = $_POST["address"] ?? "";
  $zip = $_POST["zip"] ?? "";
  $email = $_POST["email"] ?? "";
  $project = $_POST["project_type"] ?? "";
  $source = $_POST["source"] ?? "NestNudge";

  // Basic validation
  if (empty($email) || empty($address)) {
    http_response_code(400);
    echo "Missing required fields";
    exit;
  }

  // Save to file (simple logging)
  $entry = date("Y-m-d H:i:s") . " | $name | $address | $zip | $email | $project | $source\n";
  file_put_contents("leads.txt", $entry, FILE_APPEND);

  // Success response
 header("Location: /thank-you.html");
exit;
}
?>
