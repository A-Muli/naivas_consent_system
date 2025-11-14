<?php
// admin_create.php

// 1. Database Connection (Use your secure connection file)
include 'db_connect.php';

// Define the new admin's credentials
$new_username = 'NaivasAdmin';
$plain_password = 'Admin@123#'; // 

// 2. Hash the password securely
// SECURITY CRITICAL: This uses bcrypt (the default for PASSWORD_DEFAULT), which is highly secure.
$hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

// 3. Prepare the secure insertion query (Prevents SQL Injection)
$sql = "INSERT INTO admin_users (username, password) VALUES (?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

// Bind the parameters (s = string)
$stmt->bind_param("ss", $new_username, $hashed_password);

// 4. Execute and report
if ($stmt->execute()) {
    echo "✅ Success! Admin user '{$new_username}' created successfully.";
} else {
    echo "❌ Error creating user: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>