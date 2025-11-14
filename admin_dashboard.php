<?php
// --- SECURITY MEASURE 1: ACCESS CONTROL (Authentication Check)
// Start the session to access login variables
session_start();

// Check if the user is NOT logged in or the session variable is unset
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // If not logged in, redirect them immediately to the login page.
    // This prevents unauthorized public access to sensitive data.
    header("Location: admin_login.html");
    exit;
}


// Include the database connection file
include 'db_connect.php';

// Check for connection error
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// --- SECURITY MEASURE 2: PREPARED STATEMENT (Optional but good practice for queries)
// While not strictly necessary for a simple SELECT * without user input in the WHERE clause,
// using prepared statements here ensures consistency and avoids potential issues
// if the ORDER BY column name were ever derived from input.

// Define the SQL query
$sql = "SELECT * FROM consent_forms ORDER BY date_submitted DESC";

// Prepare the statement
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    error_log("Prepare failed: " . $conn->error);
    die("<h3 style='color:red;text-align:center;'>Database query error.</h3>");
}

// Execute the statement
$stmt->execute();

// Get the result set
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Naivas Consent Admin Panel</title>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
  <style>
    /* ... (Your existing CSS styles remain here) ... */
    body { font-family: 'Open Sans', sans-serif; background-color: #f4f6f9; margin: 0; padding: 0; }
    header { background-color: #080808ff; color: #F58220; padding: 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    header h1 { font-size: 22px; margin: 0; font-weight: 600; }
    .logout-btn { background: #F58220; color: #fff; padding: 8px 16px; text-decoration: none; border-radius: 5px; font-weight: 600; }
    .logout-btn:hover { background: #78BE20; }
    .container { width: 94%; margin: 30px auto; background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    h2 { margin-bottom: 10px; color: #333; font-weight: 600; }
    table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 14px; }
    th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
    th { background: #78BE20; color: white; font-weight: 600; text-transform: uppercase; font-size: 13px; }
    tr:hover { background: #f1f1f1; }
    .btn { background-color: #F58220; color: #fff; padding: 10px 16px; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; margin-top: 10px; transition: 0.3s; }
    .btn:hover { background-color: #78BE20; }
    .badge-yes { background: #78BE20; color:white; padding:4px 10px; border-radius:4px; font-size:12px; font-weight:600; }
    .badge-no { background: #d9534f; color:white; padding:4px 10px; border-radius:4px; font-size:12px; font-weight:600; }
    img.signature { border:1px solid #aaa; padding:3px; border-radius:5px; width:80px; }
  </style>
</head>

<body>

<header>
  <h1>Naivas Consent Management</h1>
  <a href="logout.php" class="logout-btn">Logout</a>
</header>

<div class="container">
  <h2>Submitted Consent Forms</h2>

  <form method="POST" action="generate_all_pdf.php">
    <button type="submit" class="btn">📄 Download All as PDF</button>
  </form>

  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Name</th>
        <th>Date</th>
        <th>Consent (Photo)</th>
        <th>Consent (Video)</th>
        <th>Publish Intranet</th>
        <th>Newsletter</th>
        <th>Social Media</th>
        <th>Signature</th>
        <th>Witness</th>
      </tr>
    </thead>

    <tbody>
      <?php
      function badge($value) {
        return $value 
            ? "<span class='badge-yes'>Yes</span>"
            : "<span class='badge-no'>No</span>";
      }

      $count = 1;
      while($row = $result->fetch_assoc()) {
          
          // --- SECURITY MEASURE 3: OUTPUT ESCAPING (To prevent Stored XSS)
          // Use htmlspecialchars() for ALL data coming from the database that is outputted to HTML.
          // This converts characters like < and > into &lt; and &gt; so they are displayed safely
          // as text instead of being executed as HTML/scripts.
          $safe_name           = htmlspecialchars($row['name']);
          $safe_date           = htmlspecialchars($row['date_submitted']);
          $safe_signature_src  = htmlspecialchars($row['signature']);
          $safe_witness_name   = htmlspecialchars($row['witness_name']);
          
          echo "<tr>
                    <td>".$count++."</td>
                    <td>".$safe_name."</td>
                    <td>".$safe_date."</td>
                    <td>".badge($row['consent_photograph'])."</td>
                    <td>".badge($row['consent_video'])."</td>
                    <td>".badge($row['publish_intranet'])."</td>
                    <td>".badge($row['publish_newsletter'])."</td>
                    <td>".badge($row['publish_social'])."</td>
                    <td><img src='".$safe_signature_src."' class='signature' alt='Signature'></td>
                    <td>".$safe_witness_name."</td>
                </tr>";
      }
      
      // Close the statement and connection after use
      $stmt->close();
      $conn->close();
      ?>
    </tbody>
  </table>
</div>

</body>
</html>