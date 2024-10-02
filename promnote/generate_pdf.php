<?php
require_once('../vendor/tecnickcom/tcpdf/tcpdf.php');

// Create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your Company Name');
$pdf->SetTitle('Promissory Note and Eviction Notice');
$pdf->SetSubject('Promissory Note and Eviction Notice Agreement');
$pdf->SetKeywords('Promissory Note, Eviction Notice, Agreement, Legal Document');

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
$issuer_name = $_GET['issuer_name'];
$issuer_address = $_GET['issuer_address'];
$issuer_phone = $_GET['issuer_phone'];
$name = $_GET['name'];
$address = $_GET['address'];
$phone = $_GET['phone'];
$amount_due = $_GET['amount_due'];
$late_fees = $_GET['late_fees'];
$misc_fees = $_GET['misc_fees'];
$misc_fees_description = $_GET['misc_fees_description'];
$total = $_GET['total'];
$notice_date = $_GET['notice_date'];
$eviction_date = $_GET['eviction_date'];
$signature = $_GET['signature'];
$signatureDate = $_GET['signatureDate'];

// Create the content
$content = <<<EOD
<h1>Promissory Note and Eviction Notice</h1>

<h2>Promissory Note</h2>

<p>This Promissory Note is issued on {$notice_date}.</p>

<p><strong>Issuer:</strong> {$issuer_name}<br>
<strong>Address:</strong> {$issuer_address}<br>
<strong>Phone:</strong> {$issuer_phone}</p>

<p><strong>The undersigned:</strong><br>
<strong>Name:</strong> {$name}<br>
<strong>Address:</strong> {$address}<br>
<strong>Phone:</strong> {$phone}</p>

<p>Agrees to pay a total of \${$total}.</p>

<p>This amount includes:</p>
<ul>
    <li>Amount due: \${$amount_due}</li>
    <li>Late fees: \${$late_fees}</li>
    <li>Miscellaneous fees: \${$misc_fees}</li>
</ul>
EOD;

if ($misc_fees_description) {
    $content .= "<p>Description of miscellaneous fees: {$misc_fees_description}</p>";
}

$content .= <<<EOD
<p>The total amount is due by {$eviction_date}.</p>

<p>If not paid in full, the undersigned agrees to vacate immediately.</p>

<p>Failure to comply will result in legal action.</p>

<p>The undersigned understands that failure to pay or vacate will result in:</p>
<ul>
    <li>A Summons and Complaint for unlawful detainer</li>
    <li>Possible eviction</li>
    <li>Liability for all amounts owed</li>
    <li>Attorney fees</li>
    <li>Court costs</li>
    <li>Other damages as allowed by law</li>
</ul>

<p>By signing below, the undersigned acknowledges the terms and agrees to pay the total amount of \${$total}.</p>

<p><strong>Signature:</strong> <em>{$signature}</em></p>
<p><strong>Date:</strong> <em>{$signatureDate}</em></p>

<h2>Notice of Eviction - Notice to Pay or Quit</h2>

<p><strong>This Notice is Given to Tenant:</strong><br>
Name: {$name}<br>
Address: {$address}<br>
Phone: {$phone}</p>

<p><strong>This Notice is Given by Landlord:</strong><br>
Name: {$issuer_name}<br>
Address: {$issuer_address}<br>
Phone: {$issuer_phone}</p>

<p>You are hereby given notice that you are behind on your rent or other amounts due. You are required to either pay everything owed or vacate the premises.</p>

<p>Within three (3) business days (or the appropriate cure period as stated in the lease) you are required to:</p>

<p>1. Pay the following:</p>
<ul>
    <li>Rent due: \${$amount_due}</li>
    <li>Late fees: \${$late_fees}</li>
    <li>Miscellaneous fees: \${$misc_fees}</li>
    <li><strong>Total: \${$total}</strong></li>
</ul>

<p>OR</p>

<p>2. All occupants must vacate the premises.</p>

<p>If you do not comply with this notice, you will be served with a Summons and Complaint for unlawful detainer. Unlawful detainer is when you remain in possession of rental property after the owner serves you with a lawful notice to leave, such as this eviction notice. If you are found by the court to be in unlawful detainer, you will be evicted and found liable for all amounts owed under the lease and/or Utah law, plus attorney fees, court costs, and three times those damages allowed to be trebled under Utah Code Ann. §78B-6-811.</p>

<p>If your lease requires mediation, you must alert us in writing within three days of your willingness to participate in mediation. Mediation shall take place within seven days of receipt of your written notification. If you fail to provide this written notification within three days and/or you fail to participate in mediation within seven days, be advised that your landlord intends to proceed with legal or equitable relief.</p>
EOD;

// Write the content
$pdf->writeHTML($content, true, false, true, false, '');

// Add footer with legal verbiage
$pdf->SetY(-15);
$pdf->SetFont('helvetica', '', 8);
$pdf->MultiCell(0, 10, 'Copyright 2010-2020. This form verbiage provided by the Law Offices of Jeremy M. Shorts, LLC and may be used by landlords within the state of Utah. Use of this form shall not constitute legal representation by this firm. Visit www.utahevictionlaw.com for more landlord forms and materials. Phone: 801-610-9879. Rev 5/12/2020', 0, 'C');

// Output the PDF
$pdf->Output('promissory_note_and_eviction_notice.pdf', 'I');