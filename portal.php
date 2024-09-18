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
    <title>Portal</title>
</head>
<body>
<div class="container">
    <h2>Welcome to the Portal</h2>
    <h3>Choose a tool:</h3>
    <ul>
        <li><a href="./promnote/admin.php">Pay or Quit</a></li>
        <li><a href="./promnote/evict.php">Eviction Notice</a></li>
        <li><a href="./promnote/contract.php">Loan Dissolution Contract</a></li>
    </ul>
    <a href="logout.php">Logout</a>
</div>
</body>
</html>