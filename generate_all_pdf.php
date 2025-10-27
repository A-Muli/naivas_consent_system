<?php
require('fpdf/fpdf.php');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "naivas_consent";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Fetch all consent forms
$sql = "SELECT * FROM consent_forms ORDER BY id DESC";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
  die("No records found.");
}

// Helper function to decode base64 signature
function decode_signature($dataURI, $outputFile) {
  if (!$dataURI || trim($dataURI) === '') return false;
  $data = str_replace('data:image/png;base64,', '', $dataURI);
  $data = base64_decode($data);
  file_put_contents($outputFile, $data);
  return true;
}

// Custom FPDF class
class PDF extends FPDF {
  function Header() {
    $this->SetFont('Arial','B',14);
    $this->SetTextColor(255, 102, 0); // Naivas Orange
    $this->Cell(0,10,'Naivas Limited - Consent Forms Summary',0,1,'C');
    $this->Ln(5);
  }

  function Footer() {
    $this->SetY(-15);
    $this->SetFont('Arial','I',8);
    $this->SetTextColor(100,100,100);
    $this->Cell(0,10,'Generated on '.date('d M Y H:i:s'),0,0,'C');
  }
}

$pdf = new PDF();
$pdf->SetTitle("Naivas_Consent_Forms");
$pdf->SetAuthor("Naivas Limited");

// Loop through all records
while ($row = $result->fetch_assoc()) {
  $pdf->AddPage();
  $pdf->SetFont('Arial','B',12);
  $pdf->SetTextColor(34, 139, 34); // Naivas Green
  $pdf->Cell(0,10,"Consent Form ID: {$row['id']}",0,1);
  $pdf->Ln(3);

  $pdf->SetFont('Arial','',11);
  $pdf->SetTextColor(0,0,0);
  $pdf->MultiCell(0,8,"Name: " . $row['name']);
  $pdf->MultiCell(0,8,"Date Submitted: " . $row['date_submitted']);
  $pdf->Ln(3);

  $pdf->SetFont('Arial','B',11);
  $pdf->Cell(0,8,"--- CONSENT DETAILS ---",0,1);
  $pdf->SetFont('Arial','',11);
  $pdf->Cell(0,8,"Photograph Consent: " . ($row['consent_photograph'] ?? 'Yes'),0,1);
  $pdf->Cell(0,8,"Video Consent: " . ($row['consent_video'] ?? 'Yes'),0,1);
  $pdf->Cell(0,8,"Publish on Intranet: " . ($row['publish_intranet'] ?? 'Yes'),0,1);
  $pdf->Cell(0,8,"Publish on Newsletter: " . ($row['publish_newsletter'] ?? 'Yes'),0,1);
  $pdf->Cell(0,8,"Publish on Social Media: " . ($row['publish_social'] ?? 'Yes'),0,1);
  $pdf->Cell(0,8,"Automated Decision: " . ucfirst($row['automated_decision'] ?? 'Yes'),0,1);
  $pdf->Ln(8);

  $pdf->SetFont('Arial','B',11);
  $pdf->Cell(0,8,"--- SIGNATURES ---",0,1);
  $pdf->Ln(3);

  // Temporary files
  $sigFile = "temp_signature_{$row['id']}.png";
  $witSigFile = "temp_witness_signature_{$row['id']}.png";

  // Decode signatures
  $hasSig = decode_signature($row['signature'], $sigFile);
  $hasWitSig = decode_signature($row['witness_signature'], $witSigFile);

  $pdf->SetFont('Arial','',11);
  $pdf->Cell(0,8,"Customer Signature:",0,1);
  if ($hasSig) $pdf->Image($sigFile, 20, $pdf->GetY(), 40);
  $pdf->Ln(30);

  $pdf->Cell(0,8,"Witness Name: " . $row['witness_name'],0,1);
  $pdf->Cell(0,8,"Witness Signature:",0,1);
  if ($hasWitSig) $pdf->Image($witSigFile, 20, $pdf->GetY(), 40);
  $pdf->Ln(30);

  $pdf->SetFont('Arial','I',9);
  $pdf->SetTextColor(120,120,120);
  $pdf->MultiCell(0,6,"Naivas Limited\nP.O. BOX 61600 – 00200, Nairobi\nEmail: dpo@naivas.co.ke\nTelephone: +254111184200");
  $pdf->Ln(10);

  // Cleanup temp files
  if (file_exists($sigFile)) unlink($sigFile);
  if (file_exists($witSigFile)) unlink($witSigFile);
}

// Output all forms as one PDF
$pdf->Output('D', 'Naivas_All_Consent_Forms.pdf');

$conn->close();
?>
