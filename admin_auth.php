<?php
session_start();
include 'db_connect.php';

$username = $_POST['username'];
$password = md5($_POST['password']); // Match with stored MD5 hash

$sql = "SELECT * FROM admin_users WHERE username='$username' AND password='$password'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $_SESSION['admin_logged_in'] = true;
    header("Location: admin_dashboard.php");
    exit;
} else {
    echo "<h3 style='color:red;text-align:center;'>Invalid login details</h3>";
    echo "<p style='text-align:center;'><a href='admin_login.html'>Try again</a></p>";
}
$conn->close();
?>
