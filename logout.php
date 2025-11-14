<?php
/**
 * logout.php - Securely terminates the user session and redirects to the login page.
 * SECURITY MEASURES IMPLEMENTED:
 * 1. session_destroy(): Destroys the server-side session data (CRITICAL).
 * 2. Header Control: Prevents the browser from caching the logout page or previous secure pages (ENHANCED).
 */

// Start the session to be able to access and destroy session data
session_start();

// --- SECURITY MEASURE 1: Anti-Caching Headers (ENHANCED) ---
// These headers instruct the browser and proxies NOT to cache this page.
header("Expires: Tue, 01 Jan 2000 00:00:00 GMT"); // Old date
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // Always updated
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
// --- END SECURITY MEASURE 1

// Unset all of the session variables
$_SESSION = array();

// Destroy the session on the server-side
session_destroy();

// Redirect the user to the login page
header("Location: admin_login.html");
exit;
?>