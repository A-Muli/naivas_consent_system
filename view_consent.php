<?php
/**
 * view_consent.php - Securely displays customer consent records (Admin View).
 * SECURITY MEASURES IMPLEMENTED:
 * 1. Broken Access Control Fix: Requires a valid admin session to view the page (CRITICAL).
 * 2. Output Escaping (XSS Prevention): Uses htmlspecialchars() on ALL user data echoed to HTML.
 * 3. Separation of Concerns: Includes the secure database connection file instead of hardcoding credentials.
 * 4. Prepared Statement: Uses prepared statements for consistency, though not strictly required for a simple SELECT * query.
 */

// --- SECURITY MEASURE 1: ACCESS CONTROL (CRITICAL FIX) ---
session_start();

// Check if the user is NOT logged in as an admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Redirect to login page to prevent unauthorized access to sensitive data
    header("Location: admin_login.html");
    exit;
}
// --- END SECURITY MEASURE 1

// Include the separate, secure database connection file (Fixes Hardcoded Credentials)
include 'db_connect.php';

// Check connection (robustness check, assumed to be handled in db_connect.php)
if ($conn->connect_error) {
    error_log("Connection failed in view_consent.php: " . $conn->connect_error);
    die("An internal database error occurred.");
}

// Prepare and execute query
$sql = "SELECT * FROM consent_forms ORDER BY id DESC";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    error_log("Prepare failed: " . $conn->error);
    die("Query preparation error.");
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Consent Records</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
        table { border-collapse: collapse; width: 100%; background: #fff; box-shadow: 0 0 5px rgba(0,0,0,0.1); }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #007bff; color: white; }
        img { width: 150px; border: 1px solid #ccc; border-radius: 4px; }
    </style>
</head>
<body>
    <h2>Naivas Event Consent Records</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Date</th>
            <th>Photo Consent</th>
            <th>Video Consent</th>
            <th>Signature</th>
            <th>Witness</th>
            <th>Witness Signature</th>
            <th>Actions</th>
        </tr>

        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                
                // --- SECURITY MEASURE 2: OUTPUT ESCAPING (XSS Prevention - CRITICAL FIX) ---
                // Use htmlspecialchars() for all data echoed to HTML/attributes.
                $safe_id              = htmlspecialchars($row['id'] ?? '');
                $safe_name            = htmlspecialchars($row['name'] ?? '');
                $safe_date            = htmlspecialchars($row['date_submitted'] ?? '');
                $safe_witness_name    = htmlspecialchars($row['witness_name'] ?? '');
                $safe_signature       = htmlspecialchars($row['signature'] ?? ''); // Base64 data escaped
                $safe_witness_signature= htmlspecialchars($row['witness_signature'] ?? ''); // Base64 data escaped

                echo "<tr>
                    <td>{$safe_id}</td>
                    <td>{$safe_name}</td>
                    <td>{$safe_date}</td>
                    <td>" . ($row['consent_photograph'] ? 'Yes' : 'No') . "</td>
                    <td>" . ($row['consent_video'] ? 'Yes' : 'No') . "</td>
                    <td><img src='{$safe_signature}' alt='Customer Signature'></td>
                    <td>{$safe_witness_name}</td>
                    <td><img src='{$safe_witness_signature}' alt='Witness Signature'></td>
                    <td><a href='download_pdf.php?id={$safe_id}'>Download PDF</a></td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='9'>No records found</td></tr>";
        }
        
        $stmt->close();
        $conn->close();
        ?>
    </table>
</body>
</html>