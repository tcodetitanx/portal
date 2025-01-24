<?php
require_once('../vendor/tecnickcom/tcpdf/tcpdf.php');

// Create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Axiom Corp');
$pdf->SetTitle('Service Agreement');
$pdf->SetSubject('Solar Loan Dissolution Service Agreement');
$pdf->SetKeywords('Service Agreement, Solar Loan, Dissolution, Legal Document');

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

// Get and sanitize data from POST parameters
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Get data from POST parameters
$name = isset($_POST['name']) ? sanitizeInput($_POST['name']) : 'Client Name';
$address = isset($_POST['address']) ? sanitizeInput($_POST['address']) : 'Client Address';
$phone = isset($_POST['phone']) ? sanitizeInput($_POST['phone']) : 'Client Phone';
$creation_date = isset($_POST['creation_date']) ? sanitizeInput($_POST['creation_date']) : date('Y-m-d');
$amount = isset($_POST['amount']) && is_numeric($_POST['amount']) ? sanitizeInput($_POST['amount']) : 0;
$months = isset($_POST['months']) && is_numeric($_POST['months']) ? (int)sanitizeInput($_POST['months']) : 0;

// Calculate number of payments and installment amount
if ($amount > 0 && $months > 0) {
    $num_of_payments = $months + 1; // Including first payment and installments
    $installment_amount = $amount / $num_of_payments;
    $first_payment = number_format($installment_amount, 2);  // Format to two decimal places
    $remaining_balance = $amount - $installment_amount; // Balance after first payment
} else {
    $first_payment = 0;
    $remaining_balance = 0;
}

// Format the creation date
$agreement_date = date('jS \d\a\y \of F, Y', strtotime($creation_date));

// Create the content with dynamic data
$content = <<<EOD
<h1>Service Agreement</h1>

<p>This Service Agreement ("Agreement") is made and entered into on this {$agreement_date}, by and between:</p>

<p><strong>Axiom Corp</strong><br>
1510 N State Street STE 300, Lindon, UT 84042<br>
("Service Provider")</p>

<p>and</p>

<p><strong>{$name}</strong><br>
{$address}<br>
Phone: {$phone}<br>
("Client")</p>

<p>WHEREAS, Client has a solar loan that they wish to dissolve, and Service Provider has the expertise and ability to assist in this matter;</p>

<p>WHEREAS, Client agrees to retain Service Provider for the purpose of contract validity review and credit repair related to the dissolution of the solar loan;</p>

<p>NOW, THEREFORE, the parties agree as follows:</p>

<h2>1. Scope of Services</h2>
<p>Service Provider agrees to provide the following services:</p>
<ol type="a">
    <li>Review the solar loan contract for validity and potential for dissolution.</li>
    <li>Assist Client in credit repair procedures, as needed, to facilitate loan dissolution.</li>
</ol>

<h2>2. Retainer Fee</h2>
<p>Client agrees to pay Service Provider a non-refundable retainer fee of \${$amount} ("Retainer Fee"). This fee covers the cost of the services specified in Section 1.</p>

<h2>3. Payment Terms</h2>
<p>If the Client has agreed to a payment plan, the first payment of \${$first_payment} is due on the date of execution of this agreement.</p>
<p>The remaining balance of \${$remaining_balance} will be divided into {$months} equal monthly payments of \${$first_payment} each. These payments will be automatically processed monthly using the checking account and routing number provided by the Client.</p>
<p>Payments will be due on the same day of each subsequent month, and all payments are non-refundable, except as outlined in Section 4.</p>

<h2>4. 90-Day Money-Back Guarantee</h2>
<ol type="a">
    <li>If, within 90 days from the date of this Agreement, Service Provider has not secured a resolution which outweighs the fees paid, the Client may be entitled to a refund as outlined in the refund policy.</li>
</ol>

<h2>5. Signature</h2>
<p>By signing this Agreement, Client acknowledges understanding of and agreement to the terms and conditions outlined above.</p>

<p>Client's Signature: ____________________________</p>
<p>Date: ____________________________</p>

EOD;

// Output content
$pdf->writeHTML($content, true, false, true, false, '');

// Close and output PDF document
$pdf->Output('contract.pdf', 'I');
?>
