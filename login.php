<style>
    @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');

    body {
        margin: 0;
        padding: 0;
        background: linear-gradient(to right, #1e1e1e, #3a3a3a);
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        font-family: 'Roboto', sans-serif;
        color: #f0f0f0;
    }

    .login-container {
        background: rgba(30, 30, 30, 0.9);
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
        max-width: 400px;
        width: 100%;
    }

    .login-container h2 {
        text-align: center;
        margin-bottom: 24px;
        color: #f0f0f0;
    }

    .login-container label {
        display: block;
        margin-bottom: 8px;
        font-weight: 700;
        color: #ccc;
    }

    .login-container input {
        width: calc(100% - 20px);
        padding: 10px;
        margin-bottom: 16px;
        border: 1px solid #555;
        border-radius: 6px;
        font-size: 16px;
        background: #2a2a2a;
        color: #f0f0f0;
    }

    .login-container input[type="submit"] {
        width: calc(100% - 20px);
        padding: 10px;
        background: none;
        border: 2px solid #f0f0f0;
        color: #f0f0f0;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.3s ease, color 0.3s ease, border 0.3s ease;
    }

    .login-container input[type="submit"]:hover {
        background: #ff8c00;
        color: #fff;
        border: 2px solid #1e1e1e;
    }

    .toggle-password {
        cursor: pointer;
        position: absolute;
        right: 25px;
        top: 47%;
        transform: translateY(-50%);
        color: #ccc;
        font-size: 20px;
    }
</style>

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