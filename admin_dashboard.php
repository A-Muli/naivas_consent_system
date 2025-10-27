<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.html");
    exit;
}

include 'db_connect.php';
$result = $conn->query("SELECT * FROM consent_forms ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard - Naivas Consent System</title>
  <style>
    body { font-family: 'Poppins', sans-serif; background: #f8f8f8; }
    table { width: 95%; margin: 30px auto; border-collapse: collapse; }
    th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
    th { background: #ff7f00; color: white; }
    tr:nth-child(even) { background: #f2f2f2; }
    .logout {
      display: block;
      width: fit-content;
      margin: 20px auto;
      padding: 10px 20px;
      background-color: #e56d00;
      color: white;
      text-decoration: none;
      border-radius: 6px;
    }
  </style>
</head>
<body>
  <h2 style="text-align:center;">Admin Dashboard - Naivas Consent Forms</h2>
  <a href="logout.php" class="logout">Logout</a>

  <table>
    <tr>
      <th>ID</th>
      <th>Name</th>
      <th>Date</th>
      <th>Download PDF</th>
    </tr>
    <?php while($row = $result->fetch_assoc()) { ?>
      <tr>
        <td><?= $row['id'] ?></td>
        <td><?= $row['name'] ?></td>
        <td><?= $row['date_submitted'] ?></td>
        <td><a href="download_pdf.php?id=<?= $row['id'] ?>">Download</a></td>
      </tr>
    <?php } ?>
  </table>
</body>
</html>
