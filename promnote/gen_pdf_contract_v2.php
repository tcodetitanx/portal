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
$issuer_name = 'Axiom Corp';
$issuer_address = '1510 N State Street STE 300, Lindon, UT 84042';
$issuer_phone = '888 982 8947';
$name = urldecode($_POST['name']);
$address = urldecode($_POST['address']);
$phone = urldecode($_POST['phone']);
$retainer_fee = sanitizeInput($_POST['retainer_fee']); // Total fee
$months = (int)sanitizeInput($_POST['months']); // Number of months
$agreement_date = date('jS \d\a\y \of F, Y');
$signature = urldecode($_POST['signature']);
$signatureDate = urldecode($_POST['signatureDate']);

// Calculate number of payments and installment amount
$num_of_payments = $months + 1; // Including first payment and installments
$installment_amount = $retainer_fee / $num_of_payments;
$first_payment = number_format($installment_amount, 2);  // Format to two decimal places
$remaining_balance = $retainer_fee - $installment_amount; // Balance after first payment

// Create the content
$content = <<<EOD
<h1>Service Agreement</h1>

<p>This Service Agreement ("Agreement") is made and entered into on this {$agreement_date}, by and between:</p>

<p><strong>Axiom Corp</strong><br>
{$issuer_address}<br>
("Service Provider")</p>

<p>and</p>

<p><strong>{$name}</strong><br>
{$address}<br>
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
<p>Client agrees to pay Service Provider a non-refundable retainer fee of \${$retainer_fee} ("Retainer Fee"). This fee covers the cost of the services specified in Section 1.</p>

<h2>3. Payment Terms</h2>
<p>If the Client has agreed to a payment plan, the first payment of \${$first_payment} is due on the date of execution of this agreement.</p>
<p>The remaining balance of \${$remaining_balance} will be divided into {$months} equal monthly payments of \${$first_payment} each. These payments will be automatically processed monthly using the checking account and routing number provided by the Client.</p>
<p>Payments will be due on the same day of each subsequent month, and all payments are non-refundable, except as outlined in Section 4.</p>

<h2>4. 90-Day Money-Back Guarantee</h2>
<ol type="a">
    <li>If, within 90 days from the date of this Agreement, Service Provider has not secured a resolution which outweighs the fee, the Client may request a refund of the Retainer Fee.</li>
    <li>To be eligible for the refund, Client must provide a written request to execute this clause no later than the 90th day following the execution of this Agreement.</li>
    <li>Upon receipt of such notice, Service Provider will issue a refund of the full \${$retainer_fee} Retainer Fee within 30 days, provided no acceptable resolution has been reached.</li>
    <li>This clause cannot be executed if the case is currently in litigation or if the case is on docket.</li>
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

<p>IN WITNESS WHEREOF, the parties hereto have executed this Service Agreement as of the day and year first above written.</p>

<p><strong>Axiom Corp</strong><br>
<p><strong>Client</strong><br>

Name: {$name}<br>
Signature:<br>
Date:<br> 

Name: Axiom Corp<br>
Signature:<br>
Date:<br> 
EOD;

// Write the content
$pdf->writeHTML($content, true, false, true, false, '');

// Output the PDF
$pdf->Output('service_agreement.pdf', 'I');
?>
