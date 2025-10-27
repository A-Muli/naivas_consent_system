<?php
include 'db_connect.php';

// Sanitize and collect form inputs
$name = $_POST['name'];
$date = $_POST['date_submitted'];
// Default to YES if not answered
$consent_photo = isset($_POST['consent_photograph']) ? 1 : 1;
$consent_video = isset($_POST['consent_video']) ? 1 : 1;
$publish_intranet = isset($_POST['publish_intranet']) ? 1 : 1;
$publish_newsletter = isset($_POST['publish_newsletter']) ? 1 : 1;
$publish_social = isset($_POST['publish_social']) ? 1 : 1;
$automated = $_POST['automated_decision'];
$signature = $_POST['signature'];
$witness_name = $_POST['witness_name'];
$witness_signature = $_POST['witness_signature'];

$sql = "INSERT INTO consent_forms 
  (name, date_submitted, consent_photograph, consent_video, publish_intranet, publish_newsletter, publish_social, automated_decision, signature, witness_name, witness_signature)
  VALUES 
  ('$name', '$date', '$consent_photo', '$consent_video', '$publish_intranet', '$publish_newsletter', '$publish_social', '$automated', '$signature', '$witness_name', '$witness_signature')";

if ($conn->query($sql) === TRUE) {
  echo "<h2 style='color:green;text-align:center;'>✅ Consent form submitted successfully!</h2>";
  echo "<p style='text-align:center;'><a href='index.html'>Go back</a></p>";
} else {
  echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
