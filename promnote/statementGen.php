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
$delinquency_notice = isset($_POST['delinquency_notice']) && !empty($_POST['delinquency_notice'])
    ? $_POST['delinquency_notice']
    : '';

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
$pdf->SetXY(60, 20); // Position address to the right of the logo
$pdf->MultiCell(55, 10, "BULL AXIOM\n1510 N State Street\nSTE 300\nOrem UT 84057", 0, 'L', 0, 1);

// Statement Date & Account Number (Top Right)
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetXY(140, 20); // Moved down to align with company address
$pdf->Cell(0, 7, 'Statement Date: ' . date("m/d/Y", strtotime($statement_date)), 0, 1, 'R');
$pdf->SetXY(140, 27); // Adjusted to maintain spacing
$pdf->Cell(0, 7, 'Account Number: ' . $account_number, 0, 1, 'R');

// Contact Us Section (Top Right, below account info)
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetXY(140, 37);
$pdf->Cell(0, 5, 'Contact Us:', 0, 1, 'R');
$pdf->SetFont('helvetica', '', 8);
$pdf->SetXY(140, 42);
$pdf->Cell(0, 5, 'Phone: (801) 555-1234', 0, 1, 'R');
$pdf->SetXY(140, 47);
$pdf->Cell(0, 5, 'Email: support@bullaxiom.com', 0, 1, 'R');
$pdf->SetXY(140, 52);
$pdf->Cell(0, 5, 'Website: bullaxiom.com', 0, 1, 'R');

// Recipient Address (Left justified, below logo)
$pdf->SetFont('helvetica', '', 10);
$pdf->SetXY(15, 55); // Left justified, below logo
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

// Save the Y position before the table
$startY = $pdf->GetY();

// Calculate table width and position
$tableWidth = 175; // Total width of the table
$col1Width = 25;
$col2Width = 90;
$col3Width = 30;
$col4Width = 30;

// Table Header - white background
$pdf->SetFillColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetXY(15, $startY);

// Header cells
$pdf->Cell($col1Width, 7, 'Date', 0, 0, 'C', 1);
$pdf->Cell($col2Width, 7, 'Description', 0, 0, 'C', 1);
$pdf->Cell($col3Width, 7, 'Charges', 0, 0, 'C', 1);
$pdf->Cell($col4Width, 7, 'Payments', 0, 1, 'C', 1); // ln=1

// Table Data Rows
$pdf->SetFont('helvetica', '', 9);
$currentY = $startY + 7; // Start after header

foreach ($transactions as $trans) {
    $t_date = date("m/d/y", strtotime($trans['date'] ?? ''));
    $t_desc = $trans['description'] ?? '';
    $t_charges = isset($trans['charges']) ? '$' . number_format((float)$trans['charges'], 2) : '$0.00';
    $t_payments = isset($trans['payments']) ? '$' . number_format((float)$trans['payments'], 2) : '$0.00';

    $pdf->SetXY(15, $currentY);
    $pdf->Cell($col1Width, 6, $t_date, 0, 0, 'C', 0);
    $pdf->Cell($col2Width, 6, $t_desc, 0, 0, 'L', 0);
    $pdf->Cell($col3Width, 6, $t_charges, 0, 0, 'R', 0);
    $pdf->Cell($col4Width, 6, $t_payments, 0, 1, 'R', 0); // ln=1

    $currentY += 6;
}

// Calculate total table height
$tableHeight = $currentY - $startY;

// Draw the 3 required lines:
$pdf->SetDrawColor(0, 0, 0);
// 1. Top border
$pdf->Line(15, $startY, 15 + $tableWidth, $startY);
// 2. Line after header
$pdf->Line(15, $startY + 7, 15 + $tableWidth, $startY + 7);
// 3. Bottom border
$pdf->Line(15, $startY + $tableHeight, 15 + $tableWidth, $startY + $tableHeight);
// Left and right borders
$pdf->Line(15, $startY, 15, $startY + $tableHeight); // Left border
$pdf->Line(15 + $tableWidth, $startY, 15 + $tableWidth, $startY + $tableHeight); // Right border

$pdf->Ln(5);


// Past Payments Breakdown Table
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Past Payments Breakdown', 0, 1, 'L');

// Save the Y position before the table
$startY = $pdf->GetY();

// Calculate table width and position
$tableWidth = 175; // Total width of the table
$col1Width = 65;
$col2Width = 55;
$col3Width = 55;

// Table Header - white background
$pdf->SetFillColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetXY(15, $startY);

// Header cells
$pdf->Cell($col1Width, 7, '', 0, 0, 'C', 1); // Empty top-left cell
$pdf->Cell($col2Width, 7, 'Paid Since Last Statement', 0, 0, 'C', 1);
$pdf->Cell($col3Width, 7, 'Paid Year to Date', 0, 1, 'C', 1); // ln=1

// Table Data Rows
$pdf->SetFont('helvetica', '', 9);
$currentY = $startY + 7; // Start after header

// Function to add a row without borders
function addTableRow($pdf, $x, $y, $label, $val1, $val2, $col1Width, $col2Width, $col3Width, $isBold = false, $addAsterisk = false) {
    if ($isBold) {
        $pdf->SetFont('helvetica', 'B', 9);
    } else {
        $pdf->SetFont('helvetica', '', 9);
    }

    $pdf->SetXY($x, $y);
    $displayLabel = $label;
    if ($addAsterisk) {
        $displayLabel = $label . '*';
    }
    $pdf->Cell($col1Width, 6, $displayLabel, 0, 0, 'L', 0);
    $pdf->Cell($col2Width, 6, $val1, 0, 0, 'R', 0);
    $pdf->Cell($col3Width, 6, $val2, 0, 1, 'R', 0);

    return $y + 6; // Return the new Y position
}

// Add data rows
$currentY = addTableRow($pdf, 15, $currentY, 'Principal',
    isset($payments_breakdown['principal']['last_statement']) ? '$' . number_format((float)$payments_breakdown['principal']['last_statement'], 2) : '$0.00',
    isset($payments_breakdown['principal']['ytd']) ? '$' . number_format((float)$payments_breakdown['principal']['ytd'], 2) : '$0.00',
    $col1Width, $col2Width, $col3Width);

$currentY = addTableRow($pdf, 15, $currentY, 'Interest',
    isset($payments_breakdown['interest']['last_statement']) ? '$' . number_format((float)$payments_breakdown['interest']['last_statement'], 2) : '$0.00',
    isset($payments_breakdown['interest']['ytd']) ? '$' . number_format((float)$payments_breakdown['interest']['ytd'], 2) : '$0.00',
    $col1Width, $col2Width, $col3Width);

$currentY = addTableRow($pdf, 15, $currentY, 'Other',
    isset($payments_breakdown['other']['last_statement']) ? '$' . number_format((float)$payments_breakdown['other']['last_statement'], 2) : '$0.00',
    isset($payments_breakdown['other']['ytd']) ? '$' . number_format((float)$payments_breakdown['other']['ytd'], 2) : '$0.00',
    $col1Width, $col2Width, $col3Width);

$currentY = addTableRow($pdf, 15, $currentY, 'Fees',
    isset($payments_breakdown['fees']['last_statement']) ? '$' . number_format((float)$payments_breakdown['fees']['last_statement'], 2) : '$0.00',
    isset($payments_breakdown['fees']['ytd']) ? '$' . number_format((float)$payments_breakdown['fees']['ytd'], 2) : '$0.00',
    $col1Width, $col2Width, $col3Width);

$currentY = addTableRow($pdf, 15, $currentY, 'Unapplied Funds',
    isset($payments_breakdown['unapplied']['last_statement']) ? '$' . number_format((float)$payments_breakdown['unapplied']['last_statement'], 2) : '$0.00',
    isset($payments_breakdown['unapplied']['ytd']) ? '$' . number_format((float)$payments_breakdown['unapplied']['ytd'], 2) : '$0.00',
    $col1Width, $col2Width, $col3Width, false, true); // Add asterisk

// Total Row - bold
$currentY = addTableRow($pdf, 15, $currentY, 'Total',
    isset($payments_breakdown['total']['last_statement']) ? '$' . number_format((float)$payments_breakdown['total']['last_statement'], 2) : '$0.00',
    isset($payments_breakdown['total']['ytd']) ? '$' . number_format((float)$payments_breakdown['total']['ytd'], 2) : '$0.00',
    $col1Width, $col2Width, $col3Width, true);

// Calculate total table height
$tableHeight = $currentY - $startY;

// Draw the 3 required lines:
$pdf->SetDrawColor(0, 0, 0);
// 1. Top border
$pdf->Line(15, $startY, 15 + $tableWidth, $startY);
// 2. Line after header
$pdf->Line(15, $startY + 7, 15 + $tableWidth, $startY + 7);
// 3. Bottom border
$pdf->Line(15, $startY + $tableHeight, 15 + $tableWidth, $startY + $tableHeight);
// Left and right borders
$pdf->Line(15, $startY, 15, $startY + $tableHeight); // Left border
$pdf->Line(15 + $tableWidth, $startY, 15 + $tableWidth, $startY + $tableHeight); // Right border

// Add the fine print text below the table
$pdf->SetXY(15, $currentY + 2);
$pdf->SetFont('helvetica', '', 8);
$unapplied_note = "*Unapplied funds represent funds that are held in suspense waiting final application. If this amount represents a partial payment, your payment will be applied upon receipt of the amount required to complete your payment.";
$pdf->MultiCell($tableWidth, 4, $unapplied_note, 0, 'L', 0, 1);

// Reset Y position after the table
$pdf->SetY($currentY + 10);

// Delinquency Notice Section
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Delinquency Notice', 0, 1, 'L');

// Create a bordered box for the delinquency notice
$startY = $pdf->GetY();
$boxWidth = 175;
$boxHeight = 40;
$pdf->SetDrawColor(0, 0, 0);
$pdf->Rect(15, $startY, $boxWidth, $boxHeight); // Draw a rectangle (x, y, width, height)

// If delinquency notice is provided, add it to the box
if (!empty($delinquency_notice)) {
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetXY(20, $startY + 5); // Add some padding inside the box
    $pdf->MultiCell($boxWidth - 10, 5, $delinquency_notice, 0, 'L', 0, 1);
}

// Move position after the box
$pdf->SetY($startY + $boxHeight + 5);

// --- Output the PDF ---
// Close and output PDF document
// I: Inline in browser, D: Download, F: Save to file, S: String
$pdf->Output('lender_statement.pdf', 'I');

?>