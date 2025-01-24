<?php
session_start();

// Check if user is authenticated
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
    <title>Generate Contract PDF</title>
    <link rel="stylesheet" href="../assets/stylesLight.css">
</head>
<body>
    <div class="container">
        <h1>Generate Contract PDF</h1>
        <form id="form">
            <label for="name">Recipient Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="address">Recipient Address:</label>
            <input type="text" id="address" name="address" required>

            <label for="phone">Recipient Phone:</label>
            <input type="text" id="phone" name="phone" required>

            <label for="creation_date">Date of contract creation:</label>
            <input type="date" id="creation_date" name="creation_date" required>

            <label for="amount">Amount:</label>
            <input type="number" id="amount" name="amount" value="2499" required>

            <label for="months">Months:</label>
            <input type="number" id="months" name="months" value="1" required>

            <button type="button" onclick="generatePdf()">Generate PDF</button>
        </form>
    </div>

    <script>
        function generatePdf() {
            const form = document.getElementById('form');
            const formData = new FormData(form);

            let params = new URLSearchParams();
            formData.forEach((value, key) => {
                params.append(key, encodeURIComponent(value)); // Only encode values, not keys
            });
            const url = `contractV2gen.php?${params.toString()}`;
            window.location.href = url; // Redirect the user to the new contract PDF generation page
        }
    </script>
</body>
</html>
