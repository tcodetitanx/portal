<?php
session_start();

if (!isset($_SESSION["authenticated"]) || $_SESSION["authenticated"] !== true) {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Promissory Note URL</title>
    <link rel="stylesheet" href="../assets/stylesLight.css">
</head>
<body>
    <div class="container">
        <h1>Generate Promissory Note URL</h1>
        <form id="form">
            <label for="issuer_name">Issuer Name:</label>
            <input type="text" id="issuer_name" name="issuer_name" required>

            <label for="issuer_address">Issuer Address:</label>
            <input type="text" id="issuer_address" name="issuer_address" required>

            <label for="issuer_phone">Issuer Phone:</label>
            <input type="text" id="issuer_phone" name="issuer_phone" required>

            <label for="name">Recipient Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="address">Recipient Address:</label>
            <input type="text" id="address" name="address" required>

            <label for="phone">Recipient Phone:</label>
            <input type="text" id="phone" name="phone" required>

            <label for="amount_due">Amount Due:</label>
            <input type="number" id="amount_due" name="amount_due" required>

            <label for="late_fees">Late Fees:</label>
            <input type="number" id="late_fees" name="late_fees" required>

            <label for="misc_fees">Miscellaneous Fees:</label>
            <input type="number" id="misc_fees" name="misc_fees" required>

            <label for="misc_fees_description">Misc Fees Description:</label>
            <textarea id="misc_fees_description" name="misc_fees_description"></textarea>

            <label for="notice_date">Date of Notice:</label>
            <input type="date" id="notice_date" name="notice_date" required>

            <label for="eviction_date">Date of Eviction:</label>
            <input type="date" id="eviction_date" name="eviction_date" required>

            <button type="button" onclick="generateUrl()">Generate URL</button>
        </form>

        <div id="generatedUrl"></div>
    </div>

    <script>
        function generateUrl() {
            const form = document.getElementById('form');
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);
            
            // Calculate total
            const amountDue = parseFloat(formData.get('amount_due')) || 0;
            const lateFees = parseFloat(formData.get('late_fees')) || 0;
            const miscFees = parseFloat(formData.get('misc_fees')) || 0;
            const total = amountDue + lateFees + miscFees;
            
            params.append('total', total.toFixed(2));

            const url = `promnote/?${params.toString()}`;
            
            document.getElementById('generatedUrl').innerHTML = `<p>Generated URL: <a href="${url}" target="_blank">${url}</a></p>`;
        }
    </script>
</body>
</html>