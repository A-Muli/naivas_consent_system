<?php
/**
 * generate_all_pdf.php - Securely generates a PDF containing all consent forms.
 * SECURITY MEASURES:
 * 1. Access Control: Requires admin session authentication.
 * 2. Uses Secure DB: Includes db_connect.php for safe connection.
 * 3. Output Escaping: Uses htmlspecialchars() for all user-entered data.
 * 4. File System Safety: Uses unique temporary files with .png extension for FPDF,
 * and immediately unlinks them to prevent TOCTOU/abuse risks.
 */

// --- SECURITY MEASURE 1: ACCESS CONTROL (Authentication Check) ---
session_start();

// Check if the user is NOT logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Prevent unauthorized access to sensitive bulk data download
    header("HTTP/1.1 403 Forbidden");
    die("Access Denied: You must be logged in to download this file.");
}
// --- END SECURITY MEASURE 1

require('fpdf/fpdf.php');

// --- SECURITY MEASURE 2: USE SECURE db_connect.php ---
include 'db_connect.php'; 

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch all consent forms using a Prepared Statement for robustness
$sql = "SELECT * FROM consent_forms ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $stmt->close();
    $conn->close();
    die("No records found.");
}

// Helper function to decode base64 signature and return the raw content
function get_signature_content($dataURI) {
    if (!$dataURI || trim($dataURI) === '') return false;
    // Strip the data URI header
    $data = str_replace('data:image/png;base64,', '', $dataURI);
    // Return the raw binary data
    return base64_decode($data);
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
    
    // --- SECURITY MEASURE 3: OUTPUT ESCAPING (XSS Prevention) ---
    // Ensure all user-entered text is escaped before being put into the PDF
    $safe_name = htmlspecialchars($row['name'] ?? '');
    $safe_date = htmlspecialchars($row['date_submitted'] ?? '');
    $safe_witness_name = htmlspecialchars($row['witness_name'] ?? '');

    $pdf->AddPage();
    $pdf->SetFont('Arial','B',12);
    $pdf->SetTextColor(34, 139, 34); // Naivas Green
    $pdf->Cell(0,10,"Consent Form ID: {$row['id']}",0,1);
    $pdf->Ln(3);

    $pdf->SetFont('Arial','',11);
    $pdf->SetTextColor(0,0,0);
    $pdf->MultiCell(0,8,"Name: " . $safe_name);
    $pdf->MultiCell(0,8,"Date Submitted: " . $safe_date);
    $pdf->Ln(3);

    $pdf->SetFont('Arial','B',11);
    $pdf->Cell(0,8,"--- CONSENT DETAILS ---",0,1);
    $pdf->SetFont('Arial','',11);
    $pdf->Cell(0,8,"Photograph Consent: " . ($row['consent_photograph'] ? 'Yes' : 'No'),0,1);
    $pdf->Cell(0,8,"Video Consent: " . ($row['consent_video'] ? 'Yes' : 'No'),0,1);
    $pdf->Cell(0,8,"Publish on Intranet: " . ($row['publish_intranet'] ? 'Yes' : 'No'),0,1);
    $pdf->Cell(0,8,"Publish on Newsletter: " . ($row['publish_newsletter'] ? 'Yes' : 'No'),0,1);
    $pdf->Cell(0,8,"Publish on Social Media: " . ($row['publish_social'] ? 'Yes' : 'No'),0,1);
    $pdf->Cell(0,8,"Automated Decision: " . ucfirst($row['automated_decision'] ?? 'No'),0,1);
    $pdf->Ln(8);

    $pdf->SetFont('Arial','B',11);
    $pdf->Cell(0,8,"--- SIGNATURES ---",0,1);
    $pdf->Ln(3);

    // --- SECURITY MEASURE 4: Secure File Handling (Fixes FPDF Error & TOCTOU) ---
    $sigContent = get_signature_content($row['signature']);
    $witSigContent = get_signature_content($row['witness_signature']);

    $pdf->SetFont('Arial','',11);
    $pdf->Cell(0,8,"Customer Signature:",0,1);

    if ($sigContent) {
        // Create a unique temporary file path that ends with .png
        $sigFileTemp = tempnam(sys_get_temp_dir(), 'sig') . '.png';
        file_put_contents($sigFileTemp, $sigContent);
        $pdf->Image($sigFileTemp, 20, $pdf->GetY(), 40);
        // Immediately delete the file to prevent abuse
        unlink($sigFileTemp);
    }
    $pdf->Ln(30);

    $pdf->Cell(0,8,"Witness Name: " . $safe_witness_name,0,1);
    $pdf->Cell(0,8,"Witness Signature:",0,1);

    if ($witSigContent) {
        // Create a unique temporary file path that ends with .png
        $witSigFileTemp = tempnam(sys_get_temp_dir(), 'witsig') . '.png';
        file_put_contents($witSigFileTemp, $witSigContent);
        $pdf->Image($witSigFileTemp, 20, $pdf->GetY(), 40);
        // Immediately delete the file
        unlink($witSigFileTemp);
    }
    $pdf->Ln(30);
    // --- END SECURITY MEASURE 4

    $pdf->SetFont('Arial','I',9);
    $pdf->SetTextColor(120,120,120);
    $pdf->MultiCell(0,6,"Naivas Limited\nP.O. BOX 61600 – 00200, Nairobi\nEmail: dpo@naivas.co.ke\nTelephone: +254111184200");
    $pdf->Ln(10);
}

// Output all forms as one PDF
$pdf->Output('D', 'Naivas_All_Consent_Forms.pdf');

$stmt->close();
$conn->close();
?>