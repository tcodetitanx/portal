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
    <title>Generate Contract</title>
    <link rel="stylesheet" href="../assets/stylesLight.css">
</head>
<body>
    <div class="container">
        <h1>Generate Contract</h1>
        <form id="form" action="contractV2gen.php" method="POST">
            <label for="name">Recipient Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="address">Recipient Address:</label>
            <input type="text" id="address" name="address" required>

            <label for="phone">Recipient Phone:</label>
            <input type="text" id="phone" name="phone" required>

            <label for="creation_date">Date of contract creation:</label>
            <input type="date" id="creation_date" name="creation_date" required>

            <label>Clause Choice:</label>
            <input type="radio" id="guarantee" name="clause_choice" value="90-day Guarantee" required>
            <label for="guarantee">90 - Day Guarantee</label>

            <input type="radio" id="payment_help" name="clause_choice" value="Payment Help" required>
            <label for="payment_help">Payment Help</label>

            <label for="amount">Amount:</label>
            <input type="number" id="amount" name="amount" value="2499" required>

            <label for="months">Months:</label>
            <input type="number" id="months" name="months" value="1" required>

            <button type="submit">Generate PDF</button>
        </form>
    </div>
</body>
</html>
