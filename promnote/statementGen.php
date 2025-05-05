<?php
session_start();

// Check if user is authenticated
if (!isset($_SESSION["authenticated"]) || $_SESSION["authenticated"] !== true) {
    header("Location: ../login.php");
    exit();
}

require_once('../vendor/tecnickcom/tcpdf/tcpdf.php');

// --- Get Data from Form ---
// Basic data
$statement_date = $_POST['statement_date'] ?? 'N/A';
$account_number = $_POST['account_number'] ?? 'N/A';
$recipient_name = $_POST['recipient_name'] ?? 'N/A';
$recipient_address1 = $_POST['recipient_address1'] ?? '';
$recipient_address2 = $_POST['recipient_address2'] ?? '';
$account_info_name = $_POST['account_info_name'] ?? 'N/A';

// Transaction items (handle array)
$transactions = $_POST['transactions'] ?? [];

// Past payments breakdown (handle array)
$payments_breakdown = $_POST['payments_breakdown'] ?? [];

// --- Create new PDF document ---
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your Company Name');
$pdf->SetTitle('Lender Statement');
$pdf->SetSubject('Account Statement');

// Remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, 15, PDF_MARGIN_RIGHT); // Left, Top, Right
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Add a page
$pdf->AddPage();

// --- PDF Content ---

// Logo and Company Address (Top Left)
$image_file = '../assets/images/bullaxiom.png'; // Updated path relative to this file
// Use @ to suppress errors if the image doesn't exist, handle appropriately
@$pdf->Image($image_file, 15, 15, 40, 0, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
$pdf->SetFont('helvetica', '', 10);
$pdf->SetXY(15, 35); // Adjust Y based on logo height
$pdf->MultiCell(55, 10, "BULL AXIOM\n1510 N State Street\nSTE 300\nOrem UT 84057", 0, 'L', 0, 1);

// Statement Date & Account Number (Top Right)
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetXY(140, 15);
$pdf->Cell(0, 7, 'Statement Date: ' . date("m/d/Y", strtotime($statement_date)), 0, 1, 'R');
$pdf->SetXY(140, 22);
$pdf->Cell(0, 7, 'Account Number: ' . $account_number, 0, 1, 'R');

// Recipient Address (Below company address, maybe slight indent)
$pdf->SetFont('helvetica', '', 10);
$pdf->SetXY(75, 55); // Adjust X, Y as needed
$pdf->MultiCell(80, 10, $recipient_name . "\n" . $recipient_address1 . "\n" . $recipient_address2, 0, 'L', 0, 1);

// Account Information Section
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetY(85); // Adjust Y
$pdf->Cell(0, 10, 'Account Information', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 7, $account_info_name, 0, 1, 'L');
$pdf->Ln(5); // Line break

// Transaction Activity Table
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Transaction Activity', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 9);
// Table Header
$pdf->SetFillColor(220, 220, 220);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(25, 7, 'Date', 1, 0, 'C', 1);
$pdf->Cell(80, 7, 'Description', 1, 0, 'C', 1);
$pdf->Cell(30, 7, 'Charges', 1, 0, 'C', 1);
$pdf->Cell(30, 7, 'Payments', 1, 1, 'C', 1); // Last cell uses ln=1
// Table Data Rows
$pdf->SetFont('helvetica', '', 9);
$pdf->SetFillColor(255, 255, 255); // White background for data
foreach ($transactions as $trans) {
    $t_date = date("m/d/y", strtotime($trans['date'] ?? ''));
    $t_desc = $trans['description'] ?? '';
    $t_charges = isset($trans['charges']) ? '$' . number_format((float)$trans['charges'], 2) : '$0.00';
    $t_payments = isset($trans['payments']) ? '$' . number_format((float)$trans['payments'], 2) : '$0.00';

    $pdf->Cell(25, 6, $t_date, 1, 0, 'L', 1);
    $pdf->Cell(80, 6, $t_desc, 1, 0, 'L', 1);
    $pdf->Cell(30, 6, $t_charges, 1, 0, 'R', 1);
    $pdf->Cell(30, 6, $t_payments, 1, 1, 'R', 1); // ln=1
}
$pdf->Ln(5);

// Contact Us Section
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Contact Us', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 7, 'Customer Service: 844-402-9466', 0, 1, 'L');
$pdf->Cell(0, 7, 'Website: https://bullaxiom.com', 0, 1, 'L');
$pdf->Ln(5);


// Past Payments Breakdown Table
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Past Payments Breakdown', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 9);
// Table Header
$pdf->SetFillColor(220, 220, 220);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(65, 7, '', 1, 0, 'C', 1); // Empty top-left cell
$pdf->Cell(55, 7, 'Paid Since Last Statement', 1, 0, 'C', 1);
$pdf->Cell(55, 7, 'Paid Year to Date', 1, 1, 'C', 1); // ln=1
// Table Data Rows
$pdf->SetFont('helvetica', '', 9);
$pdf->SetFillColor(255, 255, 255);

// Helper function for breakdown row
function addBreakdownRow($pdf, $label, $data, $key) {
     $val_last = isset($data[$key]['last_statement']) ? '$' . number_format((float)$data[$key]['last_statement'], 2) : '$0.00';
     $val_ytd = isset($data[$key]['ytd']) ? '$' . number_format((float)$data[$key]['ytd'], 2) : '$0.00';
     $pdf->Cell(65, 6, $label, 1, 0, 'L', 1);
     $pdf->Cell(55, 6, $val_last, 1, 0, 'R', 1);
     $pdf->Cell(55, 6, $val_ytd, 1, 1, 'R', 1); // ln=1
}

addBreakdownRow($pdf, 'Principal', $payments_breakdown, 'principal');
addBreakdownRow($pdf, 'Interest', $payments_breakdown, 'interest');
addBreakdownRow($pdf, 'Escrow (Taxes and Insurance)', $payments_breakdown, 'escrow');
addBreakdownRow($pdf, 'Other', $payments_breakdown, 'other');
addBreakdownRow($pdf, 'Fees', $payments_breakdown, 'fees');
addBreakdownRow($pdf, 'Unapplied Funds', $payments_breakdown, 'unapplied');
// Total Row - maybe bold
$pdf->SetFont('helvetica', 'B', 9);
addBreakdownRow($pdf, 'Total', $payments_breakdown, 'total');
$pdf->SetFont('helvetica', '', 9); // Reset font

$pdf->Ln(5);

// Delinquency Notice (optional, based on PDF)
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 7, 'Delinquency Notice', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 8);
$delinquency_text = "Unapplied funds represent funds that are held in suspense waiting final application. If this amount represents a partial payment, your payment will be applied upon receipt of the amount required to complete your payment.";
$pdf->MultiCell(0, 5, $delinquency_text, 0, 'L', 0, 1);


// --- Output the PDF ---
// Close and output PDF document
// I: Inline in browser, D: Download, F: Save to file, S: String
$pdf->Output('lender_statement.pdf', 'I');

?>