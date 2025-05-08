<?php
session_start();

// Check if user is authenticated
if (!isset($_SESSION["authenticated"]) || $_SESSION["authenticated"] !== true) {
    header("Location: ../login.php");
    exit();
}

// Check if required parameters are provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid parameters";
    exit();
}

$contact_id = intval($_GET['id']);

// Include database configuration
$conn = require_once('config/db_config.php');

// Get contact details
$sql = "SELECT c.*, l.lender_name, l.phone_number as lender_phone, l.address as lender_address 
        FROM contacts c 
        LEFT JOIN lenders l ON c.lender_id = l.id 
        WHERE c.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $contact_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Contact not found";
    exit();
}

$contact = $result->fetch_assoc();

// Close the database connection
mysqli_close($conn);

// Require TCPDF library
require_once('../vendor/tecnickcom/tcpdf/tcpdf.php');

// Create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Bull Axiom LLC');
$pdf->SetTitle('Lawsuit Document');
$pdf->SetSubject('Lawsuit Document');
$pdf->SetKeywords('Lawsuit, Bull Axiom');

// Remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// Set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', '', 12);

// Current date
$current_date = date('F j, Y');

// Prepare lender information
$lender_name = !empty($contact['lender_name']) ? $contact['lender_name'] : $contact['loan_institution'];
$lender_address = !empty($contact['lender_address']) ? $contact['lender_address'] : '';
$lender_phone = !empty($contact['lender_phone']) ? $contact['lender_phone'] : '';

// Prepare client information
$client_name = $contact['name'];
$client_address = $contact['address'];
if (!empty($contact['city'])) {
    $client_address .= ', ' . $contact['city'];
}
if (!empty($contact['state'])) {
    $client_address .= ', ' . $contact['state'];
}
if (!empty($contact['zip'])) {
    $client_address .= ' ' . $contact['zip'];
}

// Build the lawsuit content (placeholder)
$content = '
<h1 style="text-align: center;">LAWSUIT DOCUMENT PLACEHOLDER</h1>
<p>Date: ' . $current_date . '</p>
<p>To: ' . $lender_name . '<br>' . nl2br($lender_address) . '</p>
<p>From: Bull Axiom L.L.C<br>1510 N State St. Suite 300<br>Orem, Utah 84057</p>
<p>Subject: Legal Action Notification</p>
<p>Ref: ' . $client_name . ', ' . $client_address . '</p>

<p>To Whom It May Concern,</p>

<p>This is a placeholder for the lawsuit document. The actual lawsuit document will be implemented in a future update.</p>

<p>This document would typically contain:</p>
<ul>
    <li>Legal claims against the lender</li>
    <li>Specific violations of law</li>
    <li>Damages sought</li>
    <li>Legal remedies requested</li>
    <li>Court information</li>
</ul>

<p>Sincerely,<br>Bull Axiom L.L.C</p>
';

// Write the HTML content to the PDF
$pdf->writeHTML($content, true, false, true, false, '');

// Generate a unique filename based on client name and timestamp
$timestamp = date('YmdHis');
$filename = 'lawsuit_' . preg_replace('/[^a-zA-Z0-9]/', '_', $client_name) . '_' . $timestamp . '.pdf';
$filepath = '../documents/' . $filename;

// Create documents directory if it doesn't exist
if (!file_exists('../documents/')) {
    mkdir('../documents/', 0777, true);
}

// Save the PDF
$pdf->Output($filepath, 'F');

// Update the contact's lawsuit date in the database
$conn = require_once('config/db_config.php');
$update_sql = "UPDATE contacts SET suit_filed = CURDATE() WHERE id = ?";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("i", $contact_id);
$update_stmt->execute();

// Save document reference in the documents table
$doc_sql = "INSERT INTO documents (contact_id, document_type, file_path) VALUES (?, 'lawsuit', ?)";
$doc_stmt = $conn->prepare($doc_sql);
$doc_stmt->bind_param("is", $contact_id, $filename);
$doc_stmt->execute();

// Close the database connection
mysqli_close($conn);

// Output the PDF to the browser
$pdf->Output($filename, 'I');
?>
