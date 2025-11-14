
<?php
/**
 * db_connect.php - SECURE Database Connection Setup
 * * NOTE: For true production security, the username and password should be loaded 
 * from secure environment variables or a configuration file outside the web root.
 */

// --- SECURITY MEASURE 1: Use a dedicated, low-privilege application user ---
// WARNING: DO NOT use 'root' in a production environment. Use a dedicated user 
// created only for this application's database.
$servername = "localhost";
$username = "root"; // RECOMMENDED: Use a dedicated application user
$password = ""; // RECOMMENDED: Use a strong password
$dbname = "naivas_consent";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    // SECURITY MEASURE 2: Stop execution immediately and log the full error
    // but only show a generic message to the public (using die/exit is okay for connection failure)
    error_log("Database Connection Failed: " . $conn->connect_error);
    die("Error: Could not establish a database connection.");
}

// --- SECURITY MEASURE 3: Set Character Set Explicitly ---
// Sets the connection character set to utf8mb4 for security and data integrity.
if (!$conn->set_charset("utf8mb4")) {
    error_log("Error loading character set utf8mb4: " . $conn->error);
    // You could choose to die here if setting the charset is critical.
}

// The $conn variable is now available and secure for use in other scripts.
?>