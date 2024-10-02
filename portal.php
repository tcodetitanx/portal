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
    <link rel="stylesheet" href="./promnote/styles.css">
    <title>Portal</title>
</head>
<body>
<div class="container">
    <h2>Welcome to the Portal</h2>
    <h3>Choose a tool:</h3>
    <div class="buttons" style="text-align:left !important; ">
        
    <button><a href="./promnote/admin.php">Pay or Quit</a></button><br><br>
    <button><a href="./promnote/evict.php">Eviction Notice</a></button><br><br>
    <button><a href="./promnote/contract.php">Loan Dissolution Contract</a></button><br><br>
        
    </div>
    <a href="logout.php">Logout</a>
</div>
</body>
</html>