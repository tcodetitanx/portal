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
    <h2>Welcome to the Portal</h2>
    <h3>Choose a tool:</h3>
    <div class="buttons" style="text-align:left;">
    <a href="./promnote/admin.php" style="text-decoration:none;">
        <button class="tool-button">Pay or Quit</button>
    </a><br><br>

    <a href="./promnote/evict.php" style="text-decoration:none;">
        <button class="tool-button">Eviction Notice</button>
    </a><br><br>

    <a href="./promnote/contractv2.php" style="text-decoration:none;">
        <button class="tool-button">Loan Dissolution Contract</button>
    </a><br><br>

    <a href="./clean/" style="text-decoration:none;">
        <button class="tool-button">Cleaning Contract</button>
    </a><br><br>

    <a href="./mlpa/mlpa.php" style="text-decoration:none;">
        <button class="tool-button">MLPA Page</button>
    </a><br><br>

    <a href="./crm/index.php" style="text-decoration:none;">
        <button class="tool-button">CRM System</button>
    </a><br><br>

    <a href="./promnote/statementGen_form.php" style="text-decoration:none;">
        <button class="tool-button">Statement Generation</button>
    </a><br><br>
</div>
</body>
</html>