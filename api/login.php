<?php
// login.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure timestamps use Malaysia time
date_default_timezone_set('Asia/Kuala_Lumpur');

session_start();
include("db.php");
require_once 'notifications.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $loginInput = trim($_POST['login']); // Email or username
    $password = $_POST['password'];

    // Determine if input is email or username
    $query = filter_var($loginInput, FILTER_VALIDATE_EMAIL)
            ? "SELECT * FROM users WHERE email = ?"
            : "SELECT * FROM users WHERE username = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $loginInput);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            // Check if email is verified
            if ($user['is_verified'] == 0) {
                $verification_code = rand(100000, 999999);
                $update = $conn->prepare("UPDATE users SET verification_code = ? WHERE email = ?");
                $update->bind_param("ss", $verification_code, $user['email']);
                $update->execute();

                // Send verification email
               notify_verification_code($user['email'], $user['username'], $verification_code);

                $_SESSION['email'] = $user['email'];
                header("Location: verify.php?email=" . urlencode($user['email']) . "&resend=1");
                exit;
            }

            // Save session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // ✅ Send login alert (only once)
            notify_login_alert($user['email'], $user['username']);

            // Redirect based on role
                header($user['role'] === "admin" ? "Location: admin.php" : "Location: home.php");
                exit;

        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "No account found with that username or email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ALICE | Login</title>
<link rel="stylesheet" href="style.css">
<style>
    body {
        font-family: Arial, sans-serif;
        background: #0d1117;
        margin: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        color: white;
    }
    .login-container {
        background: #161b22;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 0 20px rgba(0,0,0,0.6);
        width: 100%;
        max-width: 400px;
        text-align: center;
    }
    .login-container h2 {
        margin-bottom: 20px;
        color: #238636;
    }
    .login-container input {
        width: 100%;
        padding: 12px;
        margin: 10px 0;
        border-radius: 8px;
        border: none;
    }
    .login-container form {
    display: flex;
    flex-direction: column;
    align-items: center; /* center children */
    gap: 10px;
    width: 100%;
}

.login-container input,
.login-container button {
    width: 100%;
    max-width: 300px; /* optional to avoid super wide on large screens */
}

.login-container button {
    display: block;
    text-align: center;
}

    .login-container button:hover {
        background: #2ea043;
    }
    .error {
        background: #d73a49;
        padding: 10px;
        border-radius: 8px;
        margin-bottom: 10px;
    }
    .login-container a {
        color: #58a6ff;
        text-decoration: none;
    }
    .login-container .links {
        margin-top: 15px;
        font-size: 14px;
    }
</style>
</head>
<body>

<div class="login-container">
    <h2>🔐 Login</h2>
    <?php if (!empty($error)) echo "<div class='error'>{$error}</div>"; ?>

    <form action="login.php" method="POST">
        <input type="text" name="login" placeholder="Email or Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Log In</button>
    </form>

    <div class="links">
        Don’t have an account? <a href="register.php">Sign Up</a><br>
        <a href="forgot_password.php">Forgot Password?</a>
    </div>
</div>

</body>
</html>
