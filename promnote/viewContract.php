<?php
session_start();

if (!isset($_SESSION["authenticated"]) || $_SESSION["authenticated"] !== true) {
    header("Location: ../login.php");
    exit();
}

$issuer_name = 'Axiom Corp';
$issuer_address = '1510 N State Street STE 300 Orem UT 84042 ';
$issuer_phone = '888 982 8947';
$name = $_GET['name'] ?? '';
$address = $_GET['address'] ?? '';
$phone = $_GET['phone'] ?? '';
$amount_due = $_GET['amount_due'] ?? '';
$notice_date = $_GET['creation_date'] ?? '';
$pay_url = "https://buy.stripe.com/eVa9AQ4WU1UEg5q6oo";
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Axiom Corp Retainer Contract</title>
    <link rel="stylesheet" href="styles.css">
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
        <h1>Axiom Corp Retainer Contract</h1>
        <div class="preview">
<h2>Notice of Eviction - Notice to Pay or Quit</h2>

This following contract is presented to:
Name: <?php echo $name . "<br>"; ?>
Address: <?php echo $address . "<br>"; ?>
Phone: <?php echo $phone . "<br>"; ?>

This Contract is prsented by Axiom Corp:
Name: <?php echo $issuer_name . "<br>"; ?>
Address: <?php echo $issuer_address . "<br>"; ?>
Phone: <?php echo $issuer_phone . "<br>"; ?>

You are hereby given notice that you are behind on your rent or other amounts due. You are required to either pay everything owed or vacate the premises.

Within three (3) business days (or the appropriate cure period as stated in the lease) you are required to:

1. Pay the following:
   a. Rent due: $<?php echo $amount_due . "<br>"; ?>
   b. Late fees: $<?php echo $late_fees . "<br>"; ?>
   c. Miscellaneous fees: $<?php echo $misc_fees . "<br>"; ?>
   Total: $<?php echo $total . "<br>"; ?>

OR

2. All occupants must vacate the premises.

If you do not comply with this notice, you will be served with a Summons and Complaint for unlawful detainer. Unlawful detainer is when you remain in possession of rental property after the owner serves you with a lawful notice to leave, such as this eviction notice. If you are found by the court to be in unlawful detainer, you will be evicted and found liable for all amounts owed under the lease and/or Utah law, plus attorney fees, court costs, and three times those damages allowed to be trebled under Utah Code Ann. §78B-6-811.

If your lease requires mediation, you must alert us in writing within three days of your willingness to participate in mediation. Mediation shall take place within seven days of receipt of your written notification. If you fail to provide this written notification within three days and/or you fail to participate in mediation within seven days, be advised that your landlord intends to proceed with legal or equitable relief.

<h2>Promissory Note</h2>
This Promissory Note is issued on <strong><?php echo $notice_date; ?>.</strong>

<strong>Issuer: <?php echo $issuer_name . "<br>"; ?></strong>
Address: <?php echo $issuer_address. "<br>"; ?>
Phone: <?php echo $issuer_phone . "<br>"; ?>

The undersigned:
<strong>Name: <?php echo $name . "<br>"; ?></strong>
Address: <?php echo $address . "<br>"; ?>
Phone: <?php echo $phone . "<br>"; ?>

<strong>Agrees to pay a total of $<?php echo $total; ?>.</strong>

This amount includes:
- Rent/Monies due: $<?php echo $amount_due . "<br>"; ?>
- Late fees: $<?php echo $late_fees . "<br>"; ?>
- Miscellaneous fees: $<?php echo $misc_fees . "<br>"; ?>

<?php if ($misc_fees_description): ?>Description of miscellaneous fees: <?php echo $misc_fees_description; ?><?php endif; ?>

<strong>The total amount is due by <?php echo $eviction_date; ?>.</strong>

If not paid in full, the undersigned agrees to vacate immediately.

Failure to comply will result in legal action.

The undersigned understands that failure to pay or vacate will result in:
- A Summons and Complaint for unlawful detainer
- Possible eviction
- Liability for all amounts owed
- Attorney fees
- Court costs
- Other damages as allowed by law

By signing below, the undersigned acknowledges they were served with an eviction notice digitally<br> and accepts the terms outlined in the promissory note.<br>
They agree to pay the total amount of <strong>$<?php echo $total; ?>. </strong>

Signature: <input type="text" id="signature" class="signature-input" placeholder="Type your full name to sign">
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
            const url = `generate_pdf.php?<?php echo $_SERVER['QUERY_STRING']; ?>&signature=${signature}&signatureDate=${signatureDate}`;
            window.open(url, '_blank');
        }
    </script>
</body>
</html>