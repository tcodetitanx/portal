<?php
session_start();

if (!isset($_SESSION["authenticated"]) || $_SESSION["authenticated"] !== true) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/styles.css">
    <title>Portal</title>
</head>
<body>
<div class="login-container">
    <h2>Loan Dissolution Choices</h2>
    <h3>Choose an option:</h3>
    <div class="buttons" style="text-align:left;">

    <a href="./promnote/contract.php?amount=2499&months=1" style="text-decoration:none;">
        <button class="tool-button">One Time Payment</button>
    </a><br><br>

    <a href="./promnote/contract.php?amount=2699&months=3" style="text-decoration:none;">
        <button class="tool-button">3 Month Plan</button>
    </a><br><br>

    <a href="./promnote/contract.php?amount=2999&months=6" style="text-decoration:none;">
        <button class="tool-button">6 Month Plan</button>
    </a><br><br>
</div>
</body>
</html>