<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "naivas_consent";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM consent_forms ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
  <title>View Consent Records</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 20px;
      background: #f8f9fa;
    }
    table {
      border-collapse: collapse;
      width: 100%;
      background: #fff;
      box-shadow: 0 0 5px rgba(0,0,0,0.1);
    }
    th, td {
      border: 1px solid #ddd;
      padding: 10px;
      text-align: left;
    }
    th {
      background: #007bff;
      color: white;
    }
    img {
      width: 150px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }
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
        echo "<tr>
          <td>{$row['id']}</td>
          <td>{$row['name']}</td>
          <td>{$row['date_submitted']}</td>
          <td>" . ($row['consent_photograph'] ? 'Yes' : 'No') . "</td>
          <td>" . ($row['consent_video'] ? 'Yes' : 'No') . "</td>
          <td><img src='{$row['signature']}'></td>
          <td>{$row['witness_name']}</td>
          <td><img src='{$row['witness_signature']}'></td>
          <td><a href='download_pdf.php?id={$row['id']}'>Download PDF</a></td>
        </tr>";
      }
    } else {
      echo "<tr><td colspan='9'>No records found</td></tr>";
    }
    $conn->close();
    ?>
  </table>
</body>
</html>
