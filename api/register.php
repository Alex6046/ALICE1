<?php
// register.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("db.php");
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_BCRYPT);
    $verification_code = rand(100000, 999999);

    // Check if email or username already exists
    $check = $conn->prepare("SELECT * FROM users WHERE email=? OR username=?");
    $check->bind_param("ss", $email, $username);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $error = "Email or username already registered!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (name, username, email, password, verification_code, is_verified) VALUES (?, ?, ?, ?, ?, 0)");
        $stmt->bind_param("sssss", $name, $username, $email, $password, $verification_code);

        if ($stmt->execute()) {
            $_SESSION['email'] = $email;

            // Send verification email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'azrifikriiskandar@gmail.com';
                $mail->Password = 'ejmk soge zjuu rohi';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('azrifikriiskandar@gmail.com', 'ALICE Verification');
                $mail->addAddress($email, $name);
                $mail->isHTML(true);
                $mail->Subject = 'ALICE Email Verification Code';
                $mail->Body = "<h3>Hello $name,<br>Your verification code is: <b>$verification_code</b></h3>";

                $mail->send();
                header("Location: verify.php");
                exit;
            } catch (Exception $e) {
                $error = "Error sending verification email.";
            }
        } else {
            $error = "Database error. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ALICE | Sign Up</title>
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

        .register-container {
            background: #161b22;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.6);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .register-container h2 {
            margin-bottom: 20px;
            color: #238636;
        }

        .register-container input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 8px;
            border: none;
            background: #0d1117;
            color: #fff;
        }

        .register-container input::placeholder {
            color: #888;
        }

        .register-container form {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            width: 100%;
        }

        .register-container input,
        .register-container button {
            width: 100%;
            max-width: 300px;
        }

        .register-container button {
            padding: 12px;
            border: none;
            border-radius: 8px;
            background: #238636;
            color: #fff;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s;
        }

        .register-container button:hover {
            background: #2ea043;
        }

        .error {
            background: #d73a49;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .success {
            background: #238636;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .links {
            margin-top: 15px;
            font-size: 14px;
        }

        .links a {
            color: #58a6ff;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="register-container">
    <h2>📝 Create Account</h2>

    <?php if (!empty($error)) echo "<div class='error'>{$error}</div>"; ?>

    <form action="" method="POST">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Sign Up</button>
    </form>

    <div class="links">
        Already have an account? <a href="login.php">Sign In</a>
    </div>
</div>

</body>
</html>
