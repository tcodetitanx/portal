<?php
session_start();

// Check if user is authenticated
if (!isset($_SESSION["authenticated"]) || $_SESSION["authenticated"] !== true) {
    header("Location: ../login.php");
    exit();
}

// Check if required parameters are provided
if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['type']) || !in_array($_GET['type'], ['first', 'final'])) {
    echo "Invalid parameters";
    exit();
}

$contact_id = intval($_GET['id']);
$noe_type = $_GET['type']; // 'first' or 'final'

// Include database configuration
$conn = require_once('config/db_config.php');

// Get contact details
$sql = "SELECT c.*, l.lender_name, l.phone_number as lender_phone,
        l.street_address as lender_street_address, l.city as lender_city, l.state as lender_state, l.zip as lender_zip
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

// Get state statute if this is a final NOE
$state_statute = '';
if ($noe_type == 'final' && !empty($contact['state'])) {
    $state_code = strtoupper(substr($contact['state'], 0, 2));
    $statute_sql = "SELECT statute_text FROM state_statutes WHERE state_code = ?";
    $statute_stmt = $conn->prepare($statute_sql);
    $statute_stmt->bind_param("s", $state_code);
    $statute_stmt->execute();
    $statute_result = $statute_stmt->get_result();

    if ($statute_result->num_rows > 0) {
        $statute_row = $statute_result->fetch_assoc();
        $state_statute = $statute_row['statute_text'];
    }
}

// Close the database connection
mysqli_close($conn);

// Require TCPDF library
require_once('../vendor/tecnickcom/tcpdf/tcpdf.php');

// Create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Bull Axiom LLC');
$pdf->SetTitle('Notice of Error');
$pdf->SetSubject('Notice of Error');
$pdf->SetKeywords('Notice of Error, Bull Axiom');

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

// Format lender address
$lender_address = '';
if (!empty($contact['lender_street_address'])) {
    $lender_address = $contact['lender_street_address'];

    // Add city, state, zip on a new line
    $location_parts = [];
    if (!empty($contact['lender_city'])) {
        $location_parts[] = $contact['lender_city'];
    }
    if (!empty($contact['lender_state'])) {
        $location_parts[] = $contact['lender_state'];
    }
    if (!empty($contact['lender_zip'])) {
        $location_parts[] = $contact['lender_zip'];
    }

    if (!empty($location_parts)) {
        $lender_address .= "\n" . implode(', ', $location_parts);
    }
}

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

// Build the NOE content
$content = '';

if ($noe_type == 'first') {
    // First NOE content
    $content = '
    <h1 style="text-align: center;">NOTICE OF ERROR AND DEMAND FOR REMEDY</h1>
    <p>Date: ' . $current_date . '</p>
    <p>To: ' . $lender_name . '<br>' . nl2br($lender_address) . '</p>
    <p>From: Bull Axiom L.L.C<br>1510 N State St. Suite 300<br>Orem, Utah 84057</p>
    <p>Subject: Notice of Error – Malicious Lending Practices and Failure to Provide Itemized Disclosure</p>
    <p>Ref: ' . $client_name . ', ' . $client_address . '</p>

    <p>To Whom It May Concern,</p>

    <p>Let this serve as formal notice of grievous errors and misconduct on your part, including but not limited to malicious lending practices and the deliberate failure to provide an itemized disclosure as required by law. The actions and omissions outlined herein represent a breach of your fiduciary duty, a violation of applicable laws, and a willful attempt to obscure material facts essential to this transaction.</p>

    <h2>Specific Errors and Allegations</h2>

    <h3>Malicious Lending Practices</h3>
    <ul>
        <li>Evidence of predatory or deceptive terms was embedded in the lending agreement, designed to exploit the Borrower\'s financial position and create unjust enrichment for the Lender.</li>
        <li>The loan was structured to impose unconscionable terms and hidden charges, contrary to principles of good faith and fair dealing.</li>
    </ul>

    <h3>Failure to Provide Itemized Disclosure</h3>
    <ul>
        <li>Despite multiple requests, you have failed to provide a complete, itemized breakdown of all charges, fees, interest rates, and other financial terms associated with the loan.</li>
        <li>Such omission constitutes a violation of Federal Statute, Truth in Lending Act, which mandates transparency in financial transactions.</li>
    </ul>

    <h3>Deceptive Representations and Omissions</h3>
    <ul>
        <li>Material misrepresentations were made regarding the terms of the loan and repayment obligations.</li>
        <li>Critical information was withheld at the time of agreement, resulting in an uneven bargaining position and unjust enrichment on your part.</li>
    </ul>

    <h2>DEMAND FOR IMMEDIATE REMEDY</h2>
    <p>In light of the above, We hereby demand:</p>
    <ol>
        <li>A complete and itemized disclosure of all financial terms, charges, fees, and costs associated with the loan within 15 days of the receipt of this notice.</li>
        <li>Immediate rectification of any unlawful, excessive, or undisclosed charges, accompanied by proof of recalculated balances.</li>
        <li>A written explanation addressing the allegations of malicious lending and confirming compliance with all federal and state laws governing lending practices.</li>
    </ol>

    <h2>NOTICE OF INTENT TO PURSUE LEGAL ACTION</h2>
    <p>Should you fail to comply with this demand, We reserve the right to pursue all remedies available under the law, including but not limited to:</p>
    <ul>
        <li>Filing a complaint with the Consumer Financial Protection Bureau (CFPB) and other regulatory bodies;</li>
        <li>Initiating a lawsuit for damages arising from your conduct, including punitive damages for bad faith and malicious intent;</li>
        <li>Seeking injunctive relief to prevent further harm to our client.</li>
    </ul>

    <p>You are hereby advised to preserve all records, communications, and documents related to this matter as they may be subject to discovery in pending or future litigation.</p>

    <p>This Notice of Error is not to be construed as a waiver of any rights, remedies, or defenses available to our client, all of which are expressly reserved.</p>

    <p>Govern yourself accordingly.</p>

    <p>Sincerely,<br>Bull Axiom L.L.C</p>
    ';
} else {
    // Final NOE content
    $content = '
    <h1 style="text-align: center;">BULL AXIOM<br>SECOND NOTICE OF ERROR AND FINAL DEMAND</h1>
    <p>Date: ' . $current_date . '</p>
    <p>To: ' . $lender_name . '<br>' . nl2br($lender_address) . '</p>
    <p>From: Bull Axiom L.L.C<br>1510 N State St. Suite 300<br>Orem, Utah 84057</p>
    <p>Subject: Final Demand to Remedy Errors – Removal of Negative Credit Marks and Immediate Loan Dissolution</p>
    <p>Ref: ' . $client_name . ', ' . $client_address . '<br>who will hereby be known as "the Client".</p>

    <p>To Whom It May Concern,</p>

    <p>This correspondence serves as a Second and Final Notice of Error regarding your egregious and unlawful lending practices, coupled with a deliberate failure to provide itemized disclosures. Your conduct has inflicted substantial harm, both financially and reputationally, upon the Client, necessitating urgent redress.</p>

    <p>Despite our initial notice dated on ' . (!empty($contact['first_noe']) ? date('F j, Y', strtotime($contact['first_noe'])) : '[Date of First NOE]') . ', you have failed to provide an adequate response, resolve the outlined issues, or comply with applicable laws governing lending practices. This letter represents an escalation in the demands and formal notification of your continued noncompliance.</p>

    <h2>DEMAND FOR REMOVAL OF DERAGATORY CREDIT PROFILE MARKS AND LOAN DISSOLUTION</h2>

    <h3>Immediate Removal of Negative Credit Marks</h3>
    <p>Any and all negative marks, entries, or derogatory reports filed against the Client in connection with this loan must be permanently removed from their credit profiles within 10 days of receipt of this letter. Continued reporting of false or inaccurate information to credit bureaus is a violation of the Fair Credit Reporting Act (FCRA) and actionable under federal law.</p>

    <h3>Immediate Dissolution of the Loan Agreement</h3>
    <p>Due to your failure to act in good faith, your noncompliance with legal disclosure requirements, and the malicious lending practices employed, we demand the loan agreement be immediately dissolved due to the following violations.</p>

    <h3>Failure to Provide Required Disclosures</h3>
    <ul>
        <li>Itemization of the amount financed is not clearly articulated nor defined.</li>
        <li>The cost of credit is not clearly outlined, creating confusion within the agreement.</li>
        <li>Definitions and obligations are misleading, contributing to coercion.</li>
        <li>Fraudulent representation of key agreement factors has occurred.</li>
    </ul>

    <h3>Violations of Federal Statutes';

    // Add state name if available
    if (!empty($contact['state'])) {
        $content .= ' and ' . $contact['state'] . ' Statutes';
    }

    $content .= '</h3>
    <p>The amount financed was never itemized, resulting in the violation of:</p>
    <p>15 U.S.C. §1638. Transactions other than under an open end credit plan</p>
    <p>(a) Required disclosures by creditor</p>
    <p>For each consumer credit transaction other than under an open end credit plan, the creditor shall disclose each of the following items, to the extent applicable:</p>
    <p>(2)(A) The "amount financed", using that term, which shall be the amount of credit of which the consumer has actual use. This amount shall be computed as follows, but the computations need not be disclosed and shall not be disclosed with the disclosures conspicuously segregated in accordance with subsection (b)(1):</p>
    <p>(i) take the principal amount of the loan or the cash price less downpayment and trade-in;</p>
    <p>(ii) add any charges which are not part of the finance charge or of the principal amount of the loan and which are financed by the consumer, including the cost of any items excluded from the finance charge pursuant to section 1605 of this title; and</p>
    <p>(iii) subtract any charges which are part of the finance charge but which will be paid by the consumer before or at the time of the consummation of the transaction, or have been withheld from the proceeds of the credit.</p>

    <p>Subsequently, this violation occurred and has done harm to the credit profile of the Client.</p>

    <p>15 U.S. Code § 1681s–2 - Responsibilities of furnishers of information to consumer reporting agencies</p>
    <p>(a)Duty of furnishers of information to provide accurate information</p>
    <p>(1)Prohibition</p>
    <p>(A)Reporting information with actual knowledge of errors</p>
    <p>A person shall not furnish any information relating to a consumer to any consumer reporting agency if the person knows or has reasonable cause to believe that the information is inaccurate.</p>
    <p>(B)Reporting information after notice and confirmation of errors</p>
    <p>A person shall not furnish information relating to a consumer to any consumer reporting agency if—</p>
    <p>(i)the person has been notified by the consumer, at the address specified by the person for such notices, that specific information is inaccurate; and</p>
    <p>(ii)the information is, in fact, inaccurate.</p>
    ';

    // Add state-specific statutes if available
    if (!empty($state_statute)) {
        $content .= nl2br(htmlspecialchars($state_statute));
    }

    $content .= '
    <h2>NOTICE OF LENDER LIABILITY</h2>
    <p>Your refusal to address these demands will constitute further evidence of your willful misconduct. Be advised of the following:</p>
    <ul>
        <li>Regulatory Action: A formal complaint will be filed with the Consumer Financial Protection Bureau (CFPB), the Federal Trade Commission (FTC), and relevant state regulatory authorities to investigate and sanction your actions.</li>
        <li>Litigation: We will initiate legal proceedings to seek damages, including compensation for harm to credit scores, financial loss, and punitive damages for bad faith and malicious intent.</li>
        <li>Public Exposure: Your unethical lending practices and failure to comply with federal and state laws may be publicly disclosed to ensure other consumers are not similarly harmed.</li>
    </ul>

    <h2>FINAL WARNING AND DEADLINE</h2>
    <p>This is your final opportunity to remedy the situation without further escalation. You are required to:</p>
    <ol>
        <li>Confirm in writing the removal of all negative marks from the credit profile of the Client.</li>
        <li>Provide a written acknowledgment of the dissolution of the loan and confirmation that no further obligations exist.</li>
        <li>Refund all payments made and any interest accrued.</li>
        <li>Restitution of damages to the home that incurred during installation.</li>
        <li>Removal of UCC-1 lien from the address of the Client</li>
    </ol>
    <p>Deliver these resolutions no later than 10 days from the date of receipt of this letter.</p>

    <p>Failure to comply will result in immediate legal and regulatory action without further notice. You are hereby reminded to preserve all records, communications, and documents pertaining to this matter, as they may be required in litigation.</p>

    <p>This letter does not constitute a waiver of any rights or remedies available under applicable law, all of which are expressly reserved.</p>

    <p>Govern yourself accordingly.</p>

    <p>Sincerely,<br>Bull Axiom L.L.C</p>
    ';
}

// Write the HTML content to the PDF
$pdf->writeHTML($content, true, false, true, false, '');

// Generate a unique filename based on client name and timestamp
$timestamp = date('YmdHis');
$filename = ($noe_type == 'first' ? 'first_noe_' : 'final_noe_') . preg_replace('/[^a-zA-Z0-9]/', '_', $client_name) . '_' . $timestamp . '.pdf';

// Use absolute paths to avoid file:// protocol issues
$documents_dir = realpath(__DIR__ . '/../documents/');
if ($documents_dir === false) {
    // If the directory doesn't exist yet, get the parent directory and append /documents
    $parent_dir = realpath(__DIR__ . '/..');
    $documents_dir = $parent_dir . DIRECTORY_SEPARATOR . 'documents';
}

// Ensure directory path ends with a directory separator
if (substr($documents_dir, -1) !== DIRECTORY_SEPARATOR) {
    $documents_dir .= DIRECTORY_SEPARATOR;
}

$filepath = $documents_dir . $filename;
$copy_filepath = $documents_dir . ($noe_type == 'first' ? 'first_noe_' : 'final_noe_') . 'copy_view_' . preg_replace('/[^a-zA-Z0-9]/', '_', $client_name) . '_' . $timestamp . '.pdf';

// Create documents directory if it doesn't exist
$documents_parent_dir = dirname($documents_dir);
if (!file_exists($documents_dir)) {
    if (!file_exists($documents_parent_dir)) {
        mkdir($documents_parent_dir, 0777, true);
    }
    mkdir($documents_dir, 0777, true);
}

// Save the original PDF
$pdf->Output($filepath, 'F');

// Create a copy with watermark for email
// Instead of cloning, create a new PDF instance to avoid the numpages error
$watermark_pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$watermark_pdf->SetCreator(PDF_CREATOR);
$watermark_pdf->SetAuthor('Bull Axiom LLC');
$watermark_pdf->SetTitle('Notice of Error - Copy');
$watermark_pdf->SetSubject('Notice of Error - Copy');
$watermark_pdf->SetKeywords('Notice of Error, Bull Axiom, Copy');

// Remove default header/footer
$watermark_pdf->setPrintHeader(false);
$watermark_pdf->setPrintFooter(false);

// Set default monospaced font
$watermark_pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// Set margins
$watermark_pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

// Set auto page breaks
$watermark_pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Set image scale factor
$watermark_pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// Add a page
$watermark_pdf->AddPage();

// Set font
$watermark_pdf->SetFont('helvetica', '', 12);

// Write the same HTML content to the watermarked PDF
$watermark_pdf->writeHTML($content, true, false, true, false, '');

// Add watermark to each page
$watermark_image = 'images/copyView.png';
// Check if watermark image exists, if not use a text watermark
if (!file_exists($watermark_image)) {
    // Create a simple text watermark
    $num_pages = $watermark_pdf->getNumPages();
    for ($i = 1; $i <= $num_pages; $i++) {
        $watermark_pdf->setPage($i);
        // Add a diagonal text watermark
        $watermark_pdf->SetFont('helvetica', 'B', 60);
        $watermark_pdf->SetTextColor(200, 200, 200);
        $watermark_pdf->StartTransform();
        $watermark_pdf->Rotate(45, 105, 148);
        $watermark_pdf->Text(105, 148, 'COPY VIEW');
        $watermark_pdf->StopTransform();
    }
} else {
    // Use the image watermark
    $num_pages = $watermark_pdf->getNumPages();
    for ($i = 1; $i <= $num_pages; $i++) {
        $watermark_pdf->setPage($i);
        $watermark_pdf->Image($watermark_image, 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0, false, false, false);
    }
}

// Save the watermarked copy
$watermark_pdf->Output($copy_filepath, 'F');

// Update the contact's NOE date in the database
try {
    // Create a direct database connection instead of using require_once
    $conn = mysqli_connect('localhost', 'root', '', 'portal_crm');

    if (!$conn) {
        error_log("Database connection failed after generating PDF: " . mysqli_connect_error());
    } else {
        // Update the contact's NOE date
        $update_field = ($noe_type == 'first') ? 'first_noe' : 'final_noe';
        $update_sql = "UPDATE contacts SET $update_field = CURDATE() WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);

        if ($update_stmt) {
            $update_stmt->bind_param("i", $contact_id);
            $update_stmt->execute();

            // Save document reference in the documents table
            $document_type = ($noe_type == 'first') ? 'first_noe' : 'final_noe';
            $doc_sql = "INSERT INTO documents (contact_id, document_type, file_path) VALUES (?, ?, ?)";
            $doc_stmt = $conn->prepare($doc_sql);

            if ($doc_stmt) {
                // Store only the filename, not the full path, in the database
                $doc_stmt->bind_param("iss", $contact_id, $document_type, $filename);
                $doc_stmt->execute();
            } else {
                error_log("Failed to prepare document insert statement: " . $conn->error);
            }
        } else {
            error_log("Failed to prepare update statement: " . $conn->error);
        }

        // Close the database connection
        mysqli_close($conn);
    }
} catch (Exception $e) {
    error_log("Exception in database operations: " . $e->getMessage());
}

// Output the PDF to the browser with the correct filename (not path)
$pdf->Output($filename, 'I');
?>
