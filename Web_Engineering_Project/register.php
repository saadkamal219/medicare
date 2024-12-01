<?php
require 'database_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    try {
        $stmt->execute([$username, $password]);
        header('Location: login.php');
        exit;
    } catch (PDOException $e) {
        $error = "Username already exists. Please log in instead.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Register</title>
</head>
<body>
    <div class="form-container">
        <h2>Register</h2>
        <form method="POST">
            <label for="username">Username</label>
            <input type="text" name="username" placeholder="Enter your username" required>
            
            <label for="password">Password</label>
            <div class="password-container">
                <input type="password" name="password" id="password" placeholder="Enter your password" required>
                <button type="button" id="toggle-password" class="toggle-password">👁️</button>
            </div>
            
            <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
            
            <button type="submit">Register</button>
        </form>

        <div class="login-container">
            <p>Already have an account?</p>
            <a href="login.php" class="login-button">Login</a>
        </div>
    </div>

    <script>
        const togglePassword = document.getElementById('toggle-password');
        const passwordField = document.getElementById('password');

        togglePassword.addEventListener('click', () => {
            // Toggle password visibility
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);

            // Toggle button icon
            togglePassword.textContent = type === 'password' ? '👁️' : '🙈';
        });
    </script>
</body>
</html>