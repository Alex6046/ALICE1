<?php
// forgot_password.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure timestamps use Malaysia time
date_default_timezone_set('Asia/Kuala_Lumpur');

session_start();
include("db.php");
require_once 'notifications.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Generate 6-digit code
            $reset_code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

            $update = $conn->prepare("UPDATE users SET reset_code = ?, reset_expiry = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE email = ?");
            $update->bind_param("ss", $reset_code, $email);
            $update->execute();

            // ✅ Send reset request email via centralized notifications
            notify_reset_request($email, $user['username'], $reset_code);

            $_SESSION['email'] = $email;
            $_SESSION['message'] = "Verification code sent to your email.";
            header("Location: reset_password.php");
            exit;

        } else {
            $error = "No account found with that email.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ALICE | Forgot Password</title>
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

        .forgot-container {
            background: #161b22;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.6);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .forgot-container h2 {
            margin-bottom: 20px;
            color: #238636;
        }

        .forgot-container input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 8px;
            border: none;
            background: #0d1117;
            color: #fff;
        }

        .forgot-container input::placeholder {
            color: #888;
        }

        .forgot-container form {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            width: 100%;
        }

        .forgot-container input,
        .forgot-container button {
            width: 100%;
            max-width: 300px;
        }

        .forgot-container button {
            padding: 12px;
            border: none;
            border-radius: 8px;
            background: #238636;
            color: #fff;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s;
        }

        .forgot-container button:hover {
            background: #2ea043;
        }

        .error {
            background: #d73a49;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .forgot-container a {
            color: #58a6ff;
            text-decoration: none;
        }

        .links {
            margin-top: 15px;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="forgot-container">
    <h2>🔑 Forgot Password</h2>

    <?php if (!empty($error)) echo "<div class='error'>{$error}</div>"; ?>
    <?php if (!empty($_SESSION['message'])) {
        echo "<div class='error' style='background:#238636'>{$_SESSION['message']}</div>";
        unset($_SESSION['message']);
    } ?>

    <form action="" method="POST">
        <input type="email" name="email" placeholder="Enter your email" required>
        <button type="submit">Send Verification Code</button>
    </form>

    <div class="links">
        Remembered your password? <a href="login.php">Log In</a>
    </div>
</div>

</body>
</html>
