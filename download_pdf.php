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

$id = $_GET['id'];
$sql = "SELECT * FROM consent_forms WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
  die("Record not found.");
}

$row = $result->fetch_assoc();

// Decode base64 signatures
function decode_signature($dataURI, $outputFile) {
  $data = str_replace('data:image/png;base64,', '', $dataURI);
  $data = base64_decode($data);
  file_put_contents($outputFile, $data);
}

$signature_file = 'temp_signature.png';
$witness_signature_file = 'temp_witness_signature.png';

decode_signature($row['signature'], $signature_file);
decode_signature($row['witness_signature'], $witness_signature_file);

// Create PDF
class PDF extends FPDF {
  function Header() {
    $this->SetFont('Arial','B',14);
    $this->Cell(0,10,'Naivas Limited - Consent Form',0,1,'C');
    $this->Ln(5);
  }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial','',12);

// Content
$pdf->MultiCell(0,8,"CONSENT FORM TO PUBLISH CUSTOMER PHOTOS AND VIDEOS",0,'C');
$pdf->Ln(5);

$pdf->MultiCell(0,8,"Name: " . $row['name'],0,'L');
$pdf->MultiCell(0,8,"Date Submitted: " . $row['date_submitted'],0,'L');
$pdf->Ln(3);

$pdf->Cell(0,8,"--- CONSENT DETAILS ---",0,1,'L');
$pdf->Cell(0,8,"Photograph Consent: " . ($row['consent_photograph'] ? "Yes" : "No"),0,1,'L');
$pdf->Cell(0,8,"Video Consent: " . ($row['consent_video'] ? "Yes" : "No"),0,1,'L');
$pdf->Cell(0,8,"Publish on Intranet: " . ($row['publish_intranet'] ? "Yes" : "No"),0,1,'L');
$pdf->Cell(0,8,"Publish on Newsletter: " . ($row['publish_newsletter'] ? "Yes" : "No"),0,1,'L');
$pdf->Cell(0,8,"Publish on Social Media: " . ($row['publish_social'] ? "Yes" : "No"),0,1,'L');
$pdf->Cell(0,8,"Automated Decision: " . ucfirst($row['automated_decision']),0,1,'L');
$pdf->Ln(10);

$pdf->Cell(0,8,"--- SIGNATURES ---",0,1,'L');
$pdf->Ln(5);

$pdf->Cell(40,10,"Customer Signature:",0,0,'L');
$pdf->Image($signature_file, 60, $pdf->GetY() - 5, 40);
$pdf->Ln(20);

$pdf->Cell(40,10,"Witness Name: " . $row['witness_name'],0,1,'L');
$pdf->Cell(40,10,"Witness Signature:",0,0,'L');
$pdf->Image($witness_signature_file, 60, $pdf->GetY() - 5, 40);
$pdf->Ln(20);

$pdf->Ln(10);
$pdf->MultiCell(0,8,"Naivas Limited\nP.O. BOX 61600 – 00200, Nairobi\nEmail: dpo@naivas.co.ke\nTelephone: +254111184200",0,'L');

$pdf->Output("D", "Consent_Form_{$row['id']}.pdf");

// Clean up temp files
unlink($signature_file);
unlink($witness_signature_file);

$conn->close();
?>
