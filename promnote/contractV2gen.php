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

// Function to sanitize input and decode URL encoding
function sanitizeInput($input) {
    return htmlspecialchars(trim(urldecode($input)), ENT_QUOTES, 'UTF-8');
}

// Get and sanitize data from GET parameters
$issuer_name = 'Axiom Corp';
$issuer_address = '1510 N State Street STE 300, Lindon, UT 84042';
$issuer_phone = '888 982 8947';
$name = sanitizeInput($_GET['name']);
$address = sanitizeInput($_GET['address']);
$phone = sanitizeInput($_GET['phone']);
$retainer_fee = sanitizeInput($_GET['retainer_fee']);
$initial_payment = sanitizeInput($_GET['initial_payment']);
$remaining_balance = $retainer_fee - $initial_payment;
$monthly_payment = sanitizeInput($_GET['monthly_payment']);
$months = sanitizeInput($_GET['months']);
$agreement_date = date('jS \d\a\y \of F, Y');

// Sanitize the new variables passed for payments
$total_payments = sanitizeInput($_GET['total_payments']);
$first_payment = sanitizeInput($_GET['first_payment']);
$payment_day = sanitizeInput($_GET['payment_day']);

// Define the payment terms based on months
$payment_terms = '';
if ($months > 1) {
    $payment_terms = "<li>The remaining balance of \${$remaining_balance} will be divided into {$months} equal monthly payments of \${$monthly_payment} each. These payments will be automatically processed monthly using the checking account and routing number provided by the Client.</li>";
}

// Calculate total payments over the course of the agreement
$total_amount_due = $remaining_balance + $first_payment;
$total_amount_due_text = number_format($total_amount_due, 2);

// Create the dynamic content
$content = <<<EOD
<h1>Service Agreement</h1>

<p>This Service Agreement ("Agreement") is made and entered into on this {$agreement_date}, by and between:</p>

<p><strong>{$issuer_name}</strong><br>
{$issuer_address}<br>
("Service Provider")</p>

<p>and</p>

<p><strong>{$name}</strong><br>
{$address}<br>
("Client")</p>

<p>WHEREAS, Client has a solar loan that they wish to dissolve, and Service Provider has the expertise and ability to assist in this matter;</p>

<p>NOW, THEREFORE, the parties agree as follows:</p>

<h2>1. Scope of Services</h2>
<p>Service Provider agrees to provide the following services:</p>
<ol type="a">
    <li>Review the solar loan contract for validity and potential for dissolution.</li>
    <li>Assist Client in credit repair procedures, as needed, to facilitate loan dissolution.</li>
</ol>

<h2>2. Retainer Fee</h2>
<p>Client agrees to pay Service Provider a non-refundable retainer fee of \${$retainer_fee}. This fee covers the cost of the services specified in Section 1.</p>

<h2>3. Payment Terms</h2>
<ol type="a">
    <li>An initial payment of \${$initial_payment} shall be made on the date of the execution of this agreement.</li>
    {$payment_terms}
    <li>Payments may take 2–3 business days to reflect, depending on the financial institution.</li>
    <li>All payments are non-refundable, except as outlined in Section 4.</li>
</ol>

<h2>4. 90-Day Money-Back Guarantee</h2>
<ol type="a">
    <li>If, within 90 days from the date of this Agreement, Service Provider has not secured a resolution which outweighs the fee, the Client may request a refund of the Retainer Fee.</li>
    <li>To be eligible for the refund, Client must provide written request to execute this clause no later than the 90th day following the execution of this Agreement.</li>
    <li>Upon receipt of such notice, Service Provider will issue a refund of the full \${$retainer_fee} Retainer Fee within 30 days, provided no acceptable resolution has been reached.</li>
    <li>This clause cannot be executed if the case is currently in litigation or if the case is in docket.</li>
</ol>

<h2>5. Client Responsibilities</h2>
<p>Client agrees to:</p>
<ol type="a">
    <li>Provide all necessary documentation regarding their solar loan and credit history.</li>
    <li>Cooperate with Service Provider to facilitate the loan dissolution process.</li>
</ol>

<h2>6. Termination of Agreement</h2>
<p>This Agreement may be terminated by either party upon written notice:</p>
<ol type="a">
    <li>By the Client: If Client demonstrates that the services or outcomes are not consistent with the given plan within the 90-day period, as outlined in Section 4.</li>
    <li>By the Service Provider: If Client fails to provide necessary documentation or cooperate with the process.</li>
</ol>

<h2>7. No Guarantee of Outcome</h2>
<p>While Service Provider will use its expertise to assist in dissolving the Client's solar loan, no specific outcome is guaranteed except for the return of the Retainer Fee if conditions outlined in Section 4 are met.</p>

<h2>8. Governing Law</h2>
<p>This Agreement shall be governed by and construed in accordance with the laws of the State of Utah.</p>

<h2>9. Entire Agreement</h2>
<p>This Agreement constitutes the entire understanding between the parties and supersedes all prior discussions, agreements, or understandings of any kind. Any modifications to this Agreement must be made in writing and signed by both parties.</p>

<h2>10. Payment Breakdown</h2>
<p>The total amount due for services provided is \${$total_amount_due_text}, which includes the initial payment and the remaining balance. The Client agrees to adhere to the payment terms as outlined in this agreement.</p>

<p>IN WITNESS WHEREOF, the parties hereto have executed this Service Agreement as of the day and year first above written.</p>
EOD;

// Write the content to the PDF
$pdf->writeHTML($content, true, false, true, false, '');

// Output the PDF
$pdf->Output('service_agreement.pdf', 'I');
?>
