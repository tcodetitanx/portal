<?php
session_start();

if (!isset($_SESSION["authenticated"]) || $_SESSION["authenticated"] !== true) {
    header("Location: ../login.php");
    exit();
}

if (!empty($_GET['months'])) {
    $months = $_GET['months'];
} else {
    $months = "1";
}

if (!empty($_GET['amount'])) {
    $amount = $_GET['amount'];
} else {
    $amount = "2499";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Contract URL</title>
    <link rel="stylesheet" href="../assets/stylesLight.css">
</head>
<body>
    <div class="container">
        <h1>Generate Contract URL</h1>
        <form id="form">
            <label for="name">Recipient Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="address">Recipient Address:</label>
            <input type="text" id="address" name="address" required>

            <label for="phone">Recipient Phone:</label>
            <input type="text" id="phone" name="phone" required>

            <label for="creation_date">Date of contract creation:</label>
            <input type="date" id="creation_date" name="creation_date" required>

            <!-- Hidden inputs to include 'amount' and 'months' -->
            <input type="hidden" id="months" name="months" value="<?php echo htmlspecialchars($months, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" id="amount" name="amount" value="<?php echo htmlspecialchars($amount, ENT_QUOTES, 'UTF-8'); ?>">

            <button type="button" onclick="generateUrl()">Generate URL</button>
        </form>

        <div id="generatedUrl"></div>
    </div>

    <script>
        function generateUrl() {
            const form = document.getElementById('form');
            const formData = new FormData(form);

            let params = new URLSearchParams();
            formData.forEach((value, key) => {
                params.append(key, encodeURIComponent(value)); // Only encode values, not keys
            });
            const url = `viewContract.php?${params.toString()}`;
            
            document.getElementById('generatedUrl').innerHTML = `<p>Generated URL: <a href="${url}" target="_blank">${url}</a></p>`;
        }
    </script>
</body>
</html>
