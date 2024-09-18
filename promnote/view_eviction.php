<?php
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eviction Notice</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .preview {
            white-space: pre-line;
        }
        .signature {
            font-style: italic;
            margin-bottom: 10px;
        }
        .landlord-info {
            margin-top: 20px;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><em>Eviction Notice</em></h1>
        <div class="preview">
<h2>Eviction Notice</h2>

<?php echo $eviction_date; ?>
Name: <?php echo $name . "<br>"; ?>

Phone: <?php echo $phone . "<br>"; ?><br>
TO THE TENANT(S) AND ANY AND ALL OTHERS IN POSSESSION OF THE PREMISES LOCATED AT THE AFOREMENTIONED ADDRESS, THIS NOTICE HAS BEEN SENT TO YOU PURSUANT TO UTAH STATE LAWS AS A RESULT OF YOUR BREACH OF THE LEASE AND/OR YOUR FAILURE TO PAY RENT, LATE FEES AND/OR OTHER ASSOCIATED COSTS AND/OR FEES.
<strong><em>BE IT KNOWN</em></strong> that pursuant to your signed Lease Agreement dated <?php echo $leaseDate; ?> and where you are in possession of the premises located at <?php echo $completeAddress . "<br>"; ?>

THE LANDLORD RESERVES THE RIGHTS AND REMEDIES AFFORDED TO THEM PURSUANT TO THE SIGNED LEASE/RENTAL AGREEMENT AND IN ACCORDANCE WITH APPLICABLE LAWS OF THE STATE OF UTAH INCLUDING, BUT NOT LIMITED TO, UNPAID RENT AND/OR PROPERTY DAMAGES, AND NOTHING IN THIS NOTICE MAY BE INTERPRETED AS A RELINQUISHMENT OF SUCH RIGHTS AND REMEDIES

By: <?php echo $signature; ?>

Date: <?php echo $eviction_date; ?>

<?php echo $issuer_address; ?>
<?php echo $issuer_phone; ?>

<h2>CERTIFICATE OF SERVICE</h2>

<strong><em>BE IT KNOWN</em></strong> that I, <?php echo $issuer_name; ?>, hereby certify that on the date of <?php echo $eviction_date ?>, I served copies of the Eviction Notice on <?php echo $completeAddress; ?> by way of <?php echo $means_of_service . "." ?><br>

Signature: <?php echo $signature; ?>
Date: <?php echo $eviction_date; ?>

<?php echo $issuer_address; ?>
<?php echo $issuer_phone; ?>

        </div>
        <div class="buttons">
            <button onclick="generatePDF()">Download PDF</button>
        </div>
    </div>

    <script>
        function generatePDF() {
            const url = `generate_pdf_eviction.php?<?php echo $_SERVER['QUERY_STRING']; ?>`;
            window.open(url, '_blank');
        }
    </script>
</body>
</html>
