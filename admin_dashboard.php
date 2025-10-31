<?php
include 'db_connect.php';
$result = $conn->query("SELECT * FROM consent_forms ORDER BY date_submitted DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Naivas Consent Admin Panel</title>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">

  <style>
    body {
      font-family: 'Open Sans', sans-serif;
      background-color: #f4f6f9;
      margin: 0;
      padding: 0;
    }
    header {
      background-color: #080808ff; /* Naivas Black */
      color: #F58220; /* Naivas Orange */
      padding: 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    header h1 {
      font-size: 22px;
      margin: 0;
      font-weight: 600;
    }
    .logout-btn {
      background: #F58220;
      color: #fff;
      padding: 8px 16px;
      text-decoration: none;
      border-radius: 5px;
      font-weight: 600;
    }
    .logout-btn:hover {
      background: #78BE20;
    }
    .container {
      width: 94%;
      margin: 30px auto;
      background: #fff;
      padding: 25px;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    h2 {
      margin-bottom: 10px;
      color: #333;
      font-weight: 600;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
      font-size: 14px;
    }
    th, td {
      padding: 10px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }
    th {
      background: #78BE20;
      color: white;
      font-weight: 600;
      text-transform: uppercase;
      font-size: 13px;
    }
    tr:hover {
      background: #f1f1f1;
    }
    .btn {
      background-color: #F58220;
      color: #fff;
      padding: 10px 16px;
      border: none;
      border-radius: 5px;
      font-weight: bold;
      cursor: pointer;
      margin-top: 10px;
      transition: 0.3s;
    }
    .btn:hover {
      background-color: #78BE20;
    }
    /* Badge styles */
    .badge-yes {
      background: #78BE20; 
      color:white;
      padding:4px 10px;
      border-radius:4px;
      font-size:12px;
      font-weight:600;
    }
    .badge-no {
      background: #d9534f;
      color:white;
      padding:4px 10px;
      border-radius:4px;
      font-size:12px;
      font-weight:600;
    }
    img.signature {
      border:1px solid #aaa;
      padding:3px;
      border-radius:5px;
      width:80px;
    }
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
          echo "<tr>
                  <td>".$count++."</td>
                  <td>".$row['name']."</td>
                  <td>".$row['date_submitted']."</td>
                  <td>".badge($row['consent_photograph'])."</td>
                  <td>".badge($row['consent_video'])."</td>
                  <td>".badge($row['publish_intranet'])."</td>
                  <td>".badge($row['publish_newsletter'])."</td>
                  <td>".badge($row['publish_social'])."</td>
                  <td><img src='".$row['signature']."' class='signature' alt='Signature'></td>
                  <td>".$row['witness_name']."</td>
                </tr>";
      }
      ?>
    </tbody>
  </table>
</div>

</body>
</html>
