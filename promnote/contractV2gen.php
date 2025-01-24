<?php
require_once('tcpdf_include.php');  // Include the TCPDF library

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Receive the POST data from the first page
    $amount = $_POST['amount'];
    $months = $_POST['months'];

    // Payment calculations based on the received data
    if ($months == 1) {
        $total_payments = 1;  // Only 1 payment
        $monthly_payment = number_format($amount, 2);  // Full amount as monthly payment
        $first_payment = number_format($amount, 2);  // Full amount as first payment
        $payment_day = date('jS \o\f F, Y');  // Payment due today
    } else {
        $total_payments = $months + 1; // Includes initial payment
        $monthly_payment = number_format($amount / $total_payments, 2);
        $first_payment = number_format($amount / $total_payments, 2);
        $payment_day = date('jS', strtotime("+1 month")); // Next month's day of the month
    }

    // Create new PDF document
    $pdf = new TCPDF();

    // Set document information
    $pdf->SetCreator('Axiom Corp');
    $pdf->SetAuthor('Axiom Corp');
    $pdf->SetTitle('Axiom Corp Service Agreement');

    // Add a page
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('helvetica', '', 12);

    // Write the content to the PDF
    $content = "
    This Service Agreement (\"Agreement\") is made and entered into on this " . date('jS \o\f F, Y') . ", by and between:

    Axiom Corp
    [Address Placeholder]
    (\"Service Provider\")

    and

    [Client Name Placeholder]
    [Client Address Placeholder]
    (\"Client\")

    WHEREAS, Client has a solar loan that they wish to dissolve, and Service Provider has the expertise and ability to assist in this matter;

    WHEREAS, Client agrees to retain Service Provider for the purpose of contract validity review and credit repair related to the dissolution of the solar loan;

    NOW, THEREFORE, the parties agree as follows:

    1. Scope of Services
    Service Provider agrees to provide the following services:
    a. Review the solar loan contract for validity and potential for dissolution.
    b. Assist Client in credit repair procedures, as needed, to facilitate loan dissolution.

    2. Retainer Fee
    Client agrees to pay Service Provider a non-refundable retainer fee of \$" . $retainer_fee . " (\"Retainer Fee\"). This fee covers the cost of the services specified in Section 1.

    3. Payment Terms
    " . ($months == 1 ? "Client agrees to pay the Retainer Fee in full upon signing this Agreement." : "Client agrees to pay the Retainer Fee in $total_payments payments:
    - The first payment of \$" . $first_payment . " is due upon signing this Agreement.
    - $months subsequent payments of \$" . $monthly_payment . " each, due on the $payment_day of each month.") . "

    b. The Retainer Fee will be applied toward the services outlined in Section 1.

    4. 90-Day Money-Back Guarantee
    a. If, within 90 days from the date of this Agreement, Service Provider has not secured a resolution which outweighs the fee, the Client may request a refund of the Retainer Fee.
    b. To be eligible for the refund, Client must provide a written request to execute this clause no later than the 90th day following the execution of this Agreement.
    c. Upon receipt of such notice, Service Provider will issue a refund of the full \$" . $retainer_fee . " Retainer Fee within 30 days, provided no acceptable resolution has been reached.
    d. This clause cannot be executed if the case is currently in litigation or if the case is in docket.

    5. Client Responsibilities
    Client agrees to:
    a. Provide all necessary documentation regarding their solar loan and credit history.
    b. Cooperate with Service Provider to facilitate the loan dissolution process.

    6. Termination of Agreement
    This Agreement may be terminated by either party upon written notice:
    a. By the Client: If Client demonstrates that the services or outcomes are not consistent with the given plan within the 90-day period, as outlined in Section 4.
    b. By the Service Provider: If Client fails to provide necessary documentation or cooperate with the process.

    7. No Guarantee of Outcome
    While Service Provider will use its expertise to assist in dissolving the Client's solar loan, no specific outcome is guaranteed except for the return of the Retainer Fee if conditions outlined in Section 4 are met.

    8. Governing Law
    This Agreement shall be governed by and construed in accordance with the laws of the State of Utah.

    9. Entire Agreement
    This Agreement constitutes the entire understanding between the parties and supersedes all prior discussions, agreements, or understandings of any kind. Any modifications to this Agreement must be made in writing and signed by both parties.

    IN WITNESS WHEREOF, the parties hereto have executed this Service Agreement as of the day and year first above written.

    Axiom Corp
    ";

    // Output the PDF
    $pdf->Write(0, $content);

    // Close and output PDF document
    $pdf->Output('service_agreement.pdf', 'I');
}
?>
