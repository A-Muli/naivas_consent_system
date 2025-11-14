<?php
// Start the session at the very beginning
session_start();

/**
 * admin_auth.php - Admin Login Authentication Script
 * * SECURITY FEATURES:
 * 1. Uses prepared statements to prevent SQL Injection
 * 2. Verifies hashed passwords using password_verify()
 * 3. Regenerates session ID upon successful login to prevent Session Fixation
 * 4. Provides generic error messages to avoid leaking sensitive info
 * 5. NEW: Implements a time delay on login failure to mitigate brute-force attacks.
 */

// --- SECURE DATABASE CONNECTION ---
include 'db_connect.php'; // Ensure this file sets $conn

// Check if the connection was successful
if ($conn->connect_error) {
    // Log the error internally and die gracefully
    error_log("Connection failed: " . $conn->connect_error);
    die("An internal server error prevents login. Please try again later.");
}

// --- CHECK IF LOGIN FORM DATA EXISTS ---
if (!isset($_POST['username'], $_POST['password'])) {
    // Redirect or handle error if required data is missing
    header("Location: admin_login.html?error=missing_data");
    exit;
}

// --- SANITIZE INPUT ---
// The username is trimmed but not filtered yet, as we need the raw input for the lookup.
// We rely on the prepared statement to handle any malicious characters during the query.
$username = trim($_POST['username']);
$password = $_POST['password'];

// --- PREPARED STATEMENT TO SELECT ADMIN USER ---
$sql = "SELECT id, password FROM admin_users WHERE username = ?";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    // Log internal error, but show generic message to user
    error_log("Prepare failed: " . $conn->error);
    echo "<h3 style='color:red;text-align:center;'>An internal error occurred.</h3>";
    $conn->close();
    exit;
}

// Bind username parameter
$stmt->bind_param("s", $username);

// Execute the query
$stmt->execute();

// --- STORE RESULT TO ALLOW num_rows USAGE ---
$stmt->store_result();

// Check if a user with that username exists
if ($stmt->num_rows === 1) {

    // Bind the result columns to variables
    $stmt->bind_result($user_id, $hashed_password_from_db);
    $stmt->fetch();

    // --- VERIFY PASSWORD ---
    // password_verify is inherently resistant to timing attacks, which is secure.
    if (password_verify($password, $hashed_password_from_db)) {

        // --- SUCCESSFUL LOGIN ---
        session_regenerate_id(true); // Prevent session fixation

        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user_id'] = $user_id; // Optional: store user ID

        // Redirect to admin dashboard
        header("Location: admin_dashboard.php");
        exit;
    }
}

// --- IF LOGIN FAILS ---

// 🛡️ NEW SECURITY ENHANCEMENT: Time Delay for Brute-Force Mitigation
// This adds a mandatory delay for ALL login failures (whether username not found or password incorrect).
$min_delay = 2; // Minimum delay in seconds
$max_delay = 4; // Maximum delay in seconds
$delay = rand($min_delay, $max_delay);
sleep($delay); // Pause script execution for a random period.

// Generic error message is crucial to prevent leaking whether the username or password was correct.
echo "<h3 style='color:red;text-align:center;'>Invalid username or password.</h3>"; 
echo "<p style='text-align:center;'><a href='admin_login.html'>Try again</a></p>";

// --- CLOSE STATEMENT AND CONNECTION ---
$stmt->close();
$conn->close();
?>