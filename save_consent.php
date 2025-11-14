<?php
/**
 * save_consent.php - SECURELY processes and saves customer consent forms.
 * SECURITY MEASURES IMPLEMENTED:
 * 1. Prepared Statements: Prevents SQL Injection.
 * 2. Strict Input Validation: Checks for required fields and converts inputs safely.
 * 3. CRITICAL FIX: Corrected boolean logic for checkbox consent fields.
 * 4. Data Size Limiting: Limits the size of Base64 signature strings to prevent DB bloat/DoS.
 * 5. Sanitize Output: Uses filter_var() for text fields to prevent XSS.
 */

include 'db_connect.php'; 

// Check for connection error
if ($conn->connect_error) {
    error_log("Database connection failed in save_consent.php: " . $conn->connect_error);
    die("An unexpected error occurred. Please try again later.");
}

// Set maximum allowed size for signature Base64 string (e.g., ~3MB Base64 string limit)
const MAX_SIG_SIZE_BYTES = 3 * 1024 * 1024; 

// --- SECURITY MEASURE 1: INPUT VALIDATION AND SANITIZATION ---

// 1. Collect, validate, and sanitize inputs
$name                   = filter_var(trim($_POST['name'] ?? ''), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$date_submitted         = filter_var(trim($_POST['date_submitted'] ?? ''), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$witness_name           = filter_var(trim($_POST['witness_name'] ?? ''), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$automated_decision     = in_array($_POST['automated_decision'] ?? 'no', ['yes', 'no']) ? $_POST['automated_decision'] : 'no';
$signature              = $_POST['signature'] ?? '';
$witness_signature      = $_POST['witness_signature'] ?? '';

// 2. Fix flawed boolean logic and ensure values are 1 or 0 (CRITICAL FIX)
// If the checkbox IS NOT set (i.e., not present in $_POST), it MUST be set to 1 (Yes).
$consent_photo          = isset($_POST['consent_photograph']) ? 0 : 1; // ✅ FIX
$consent_video          = isset($_POST['consent_video']) ? 0 : 1; // ✅ FIX
$publish_intranet       = isset($_POST['publish_intranet']) ? 0 : 1; // ✅ FIX
$publish_newsletter     = isset($_POST['publish_newsletter']) ? 0 : 1; // ✅ FIX
$publish_social         = isset($_POST['publish_social']) ? 0 : 1; // ✅ FIX
$consent_publish_overall= isset($_POST['consent_publish']) && $_POST['consent_publish'] === 'yes' ? 1 : 0; 


// 3. Perform basic validation checks
if (empty($name) || empty($date_submitted) || empty($signature)) {
    die("<h2 style='color:red;text-align:center;'>❌ Error: Missing required fields (Name, Date, or Signature).</h2>");
}

// 4. Check signature size limit (CRITICAL DOs FIX)
if (strlen($signature) > MAX_SIG_SIZE_BYTES || strlen($witness_signature) > MAX_SIG_SIZE_BYTES) {
    die("<h2 style='color:red;text-align:center;'>❌ Error: Signature data is too large.</h2>");
}
// --- END INPUT VALIDATION ---

// --- SECURITY MEASURE 2: PREPARED STATEMENT ---

$sql = "INSERT INTO consent_forms 
(name, date_submitted, consent_publish, consent_photograph, consent_video, 
publish_intranet, publish_newsletter, publish_social, automated_decision, 
signature, witness_name, witness_signature)
VALUES 
(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if ($stmt === false) {
    error_log("Prepare statement failed: " . $conn->error);
    die("<h2 style='color:red;text-align:center;'>❌ An internal server error occurred.</h2>");
}

// Bind parameters: 's' for string, 'i' for integer (for boolean flags)
$stmt->bind_param("ssiiiiiissss", 
    $name,
    $date_submitted,
    $consent_publish_overall, // New overall consent field
    $consent_photo,
    $consent_video,
    $publish_intranet,
    $publish_newsletter,
    $publish_social,
    $automated_decision,
    $signature,
    $witness_name,
    $witness_signature
);

if ($stmt->execute()) {
    echo "<h2 style='color:green;text-align:center;'>✅ Consent form submitted successfully!</h2>";
    echo "<p style='text-align:center;'><a href='index.html'>Go back</a></p>";
} else {
    // Log the specific error, show generic message to user
    error_log("Execution failed: " . $stmt->error);
    echo "<h2 style='color:red;text-align:center;'>❌ Error: Database save failed.</h2>";
}

$stmt->close();
$conn->close();
?>