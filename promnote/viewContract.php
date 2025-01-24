<?php
$issuer_name = 'Axiom Corp';
$issuer_address = '1510 N State Street STE 300, Lindon, UT 84042';
$issuer_phone = '888 8';
$name = isset($_GET['name']) ? urldecode($_GET['name']) : '';
$address = isset($_GET['address']) ? urldecode($_GET['address']) : '';
$phone = isset($_GET['phone']) ? urldecode($_GET['phone']) : '';
$months = isset($_GET['months']) ? intval($_GET['months']) : 1; // Ensure months is an integer
$amount = isset($_GET['amount']) ? floatval($_GET['amount']) : 0; // Ensure amount is a float
$retainer_fee = $amount;
$agreement_date = date('jS \d\a\y \of F, Y');

// Payment calculations
$total_payments = $months + 1; // Includes initial payment
$monthly_payment = $months > 1 ? number_format($amount / $total_payments, 2) : number_format($amount, 2);
$first_payment = number_format($amount / $total_payments, 2);
$payment_day = date('jS', strtotime("+1 month")); // Next month's day of the month
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Axiom Corp Service Agreement</title>
    <link rel="stylesheet" href="../assets/stylesLight.css">
    <style>
        .preview {
            white-space: pre-line;
        }
        .signature-input {
            font-style: italic;
            border: none;
            border-bottom: 1px solid #000;
            width: 100%;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Axiom Corp Service Agreement</h1>
        <div class="preview">
This Service Agreement ("Agreement") is made and entered into on this <?php echo $agreement_date; ?>, by and between:

Axiom Corp
<?php echo $issuer_address; ?>
("Service Provider")

and

<?php echo $name; ?><br>
<?php echo $address; ?>
("Client")

WHEREAS, Client has a solar loan that they wish to dissolve, and Service Provider has the expertise and ability to assist in this matter;

WHEREAS, Client agrees to retain Service Provider for the purpose of contract validity review and credit repair related to the dissolution of the solar loan;

NOW, THEREFORE, the parties agree as follows:

1. Scope of Services
Service Provider agrees to provide the following services:
a. Review the solar loan contract for validity and potential for dissolution.
b. Assist Client in credit repair procedures, as needed, to facilitate loan dissolution.

2. Retainer Fee
Client agrees to pay Service Provider a non-refundable retainer fee of $<?php echo $retainer_fee; ?> ("Retainer Fee"). This fee covers the cost of the services specified in Section 1.

3. Payment Terms
<?php if ($months == 1): ?>
    Client agrees to pay the Retainer Fee in full upon signing this Agreement.
<?php else: ?>
    Client agrees to pay the Retainer Fee in <?php echo $total_payments; ?> payments:
    - The first payment of $<?php echo $first_payment; ?> is due upon signing this Agreement.
    - <?php echo $months; ?> subsequent payments of $<?php echo $monthly_payment; ?> each, due on the <?php echo $payment_day; ?> of each month.
<?php endif; ?>

b. The Retainer Fee will be applied toward the services outlined in Section 1.

4. 90-Day Money-Back Guarantee
a. If, within 90 days from the date of this Agreement, Service Provider has not secured a resolution which outweighs the fee, the Client may request a refund of the Retainer Fee.
b. To be eligible for the refund, Client must provide a written request to execute this clause no later than the 90th day following the execution of this Agreement.
c. Upon receipt of such notice, Service Provider will issue a refund of the full $<?php echo $retainer_fee; ?> Retainer Fee within 30 days, provided no acceptable resolution has been reached.
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

Client
By: <input type="text" id="signature" class="signature-input" placeholder="Type your full name to sign">
Name: <?php echo $name; ?><br>
Date: <input type="date" id="signatureDate" class="signature-input">

        </div>
        <div class="buttons">
            <button onclick="generatePDF()" id="generatePdfBtn" disabled>Download PDF</button>
        </div>
    </div>

    <script>
        const signatureInput = document.getElementById('signature');
        const signatureDateInput = document.getElementById('signatureDate');
        const generatePdfBtn = document.getElementById('generatePdfBtn');

        function checkFields() {
            if (signatureInput.value.trim() !== '' && signatureDateInput.value !== '') {
                generatePdfBtn.disabled = false;
            } else {
                generatePdfBtn.disabled = true;
            }
        }

        signatureInput.addEventListener('input', checkFields);
        signatureDateInput.addEventListener('input', checkFields);

        function generatePDF() {
            const signature = encodeURIComponent(signatureInput.value);
            const signatureDate = encodeURIComponent(signatureDateInput.value);
            const url = `gen_pdf_contract.php?<?php echo $_SERVER['QUERY_STRING']; ?>&signature=${signature}&signatureDate=${signatureDate}`;
            window.open(url, '_blank');
        }
    </script>
</body>
</html>
