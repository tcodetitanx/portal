<?php
session_start();

$correct_password = "yatengoelpoder"; // Replace with your actual password

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = htmlspecialchars(trim($_POST["password"]));
    
    if ($password === $correct_password) {
        $_SESSION["authenticated"] = true;
        header("Location: portal.php");
        exit();
    } else {
        $error = "Incorrect password";
    }
}
?>

<link rel="stylesheet" href="assets/styles.css">

<div class="login-container">
    <h2>Login</h2>
    <?php if (isset($error)) echo "<p style='color: red; text-align: center;'>$error</p>"; ?>
    <form method="post" action="">
        <label for="password">Password</label>
        <div style="position: relative;">
            <input type="password" id="password" name="password" required>
            <span class="toggle-password" onclick="togglePasswordVisibility()">●</span>
        </div>

        <input type="submit" value="Login">
    </form>
</div>

<script>
    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.querySelector('.toggle-password');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.textContent = '○';
        } else {
            passwordInput.type = 'password';
            toggleIcon.textContent = '●';
        }
    }
</script>