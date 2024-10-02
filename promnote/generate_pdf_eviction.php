<?php


require_once('../vendor/tecnickcom/tcpdf/tcpdf.php');

// Create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your Company Name');
$pdf->SetTitle('Eviction Notice');
$pdf->SetSubject('Eviction Notice');
$pdf->SetKeywords('Eviction Notice, Legal Document');

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

// Get data from GET parameters
$issuer_name = $_GET['issuer_name'] ?? '';
$issuer_address = $_GET['issuer_address'] ?? '';
$issuer_phone = $_GET['issuer_phone'] ?? '';
$name = $_GET['name'] ?? '';
$streetAddress = $_GET['address'] ?? '';
$phone = $_GET['phone'] ?? '';
$eviction_date = $_GET['eviction_date'] ?? '';
$leaseDate = $_GET['lease_date'] ?? '';
$city = $_GET['city'] ?? '';
$state = $_GET['state'] ?? '';
$zip = $_GET['zip'] ?? '';
$means_of_service = $_GET['means_of_service'] ?? '';
$landlord_name = $_GET['issuer_name'] ?? '';

$completeAddress = $streetAddress . ", " . $city . ", " . $state . ", " . $zip;
$signature = "<em>" . $landlord_name . "</em>";

// Create the content
$content = <<<EOD
<h1><em>Eviction Notice</em></h1>

<p>{$eviction_date}</p>
<p><strong>Name:</strong> {$name}</p>
<p><strong>Phone:</strong> {$phone}</p>

<p>TO THE TENANT(S) AND ANY AND ALL OTHERS IN POSSESSION OF THE PREMISES LOCATED AT THE AFOREMENTIONED ADDRESS, THIS NOTICE HAS BEEN SENT TO YOU PURSUANT TO UTAH STATE LAWS AS A RESULT OF YOUR BREACH OF THE LEASE AND/OR YOUR FAILURE TO PAY RENT, LATE FEES AND/OR OTHER ASSOCIATED COSTS AND/OR FEES.</p>

<p><strong><em>BE IT KNOWN</em></strong> that pursuant to your signed Lease Agreement dated {$leaseDate} and where you are in possession of the premises located at {$completeAddress}</p>

<p>THE LANDLORD RESERVES THE RIGHTS AND REMEDIES AFFORDED TO THEM PURSUANT TO THE SIGNED LEASE/RENTAL AGREEMENT AND IN ACCORDANCE WITH APPLICABLE LAWS OF THE STATE OF UTAH INCLUDING, BUT NOT LIMITED TO, UNPAID RENT AND/OR PROPERTY DAMAGES, AND NOTHING IN THIS NOTICE MAY BE INTERPRETED AS A RELINQUISHMENT OF SUCH RIGHTS AND REMEDIES</p>

<p>By: {$signature}</p>
<p>Date: {$eviction_date}</p>
<p>{$issuer_address}</p>
<p>{$issuer_phone}</p>

<h3>CERTIFICATE OF SERVICE</h3>
<p><strong><em>BE IT KNOWN</em></strong> that I, {$issuer_name}, hereby certify that on the date of {$eviction_date}, I served copies of the Eviction Notice on {$completeAddress} by way of {$means_of_service}.</p>

<p><strong>Signature:</strong> {$signature}</p>
<p><strong>Date:</strong> {$eviction_date}</p>
<p>{$issuer_address}</p>
<p>{$issuer_phone}</p>
EOD;

// Write the content
$pdf->writeHTML($content, true, false, true, false, '');

// Output the PDF
$pdf->Output('eviction_notice.pdf', 'I');
