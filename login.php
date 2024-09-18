<?php
session_start();

$correct_password = "yatengoelpoder"; // Replace with your actual password

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST["password"];
    
    if ($password === $correct_password) {
        $_SESSION["authenticated"] = true;
        header("Location: portal.php");
        exit();
    } else {
        $error = "Incorrect password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./promnote/styles.css">
    <title>Login</title>
    
</head>

<body>
    <div class="container">
        <h2>Login</h2>
        <?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?>
        <form method="post">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>