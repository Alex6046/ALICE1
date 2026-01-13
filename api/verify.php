<?php
// verify.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/phpmailer/src/SMTP.php';
require_once __DIR__ . '/phpmailer/src/Exception.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("db.php");
session_start();

// Check if email exists in session
if (!isset($_SESSION['email'])) {
    echo "<script>alert('Session expired. Please register or log in again.'); window.location='login.php';</script>";
    exit;
}

$email = $_SESSION['email'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code = trim($_POST['verification_code']);

    if (empty($code)) {
        $error = "Please enter your verification code.";
    } else {
        $query = "SELECT * FROM users WHERE email = ? AND verification_code = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $email, $code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            // Get user info for the welcome email
            $userRow = $result->fetch_assoc();
            $displayName = $userRow['name'] ?? $userRow['full_name'] ?? $userRow['username'] ?? 'there';

            $update = "UPDATE users SET is_verified = 1, verification_code = NULL WHERE email = ?";
            $stmt = $conn->prepare($update);
            $stmt->bind_param("s", $email);
            $stmt->execute();

            // ✅ Send "Registration Successful" (Welcome) email after verification
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'azrifikriiskandar@gmail.com';
                    $mail->Password = 'ejmk soge zjuu rohi';
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;

                $mail->setFrom($mail->Username, 'ALICE');
                $mail->addAddress($email, $displayName);
                $mail->isHTML(true);
// <p > line3 need to change to the real address when successful
                $mail->Subject = 'Welcome to ALICE - Registration Successful';
                $mail->Body = "
                    <h3>Hello {$displayName},</h3>
                    <p>✅ Your email has been verified successfully. Your account is now active.</p>
                    <p>You can login here: <a href='http://localhost:8888/aliceweb/login.php'>Login to ALICE</a></p>
                    <p>Thank you,<br>ALICE Team</p>
                ";

                $mail->send();
            } catch (Exception $e) {
                // Don't block login if email fails; optionally log error for debugging
                // error_log('Welcome email failed: ' . $e->getMessage());
            }

            echo "<script>alert('✅ Email verified! You can now log in.'); window.location='login.php';</script>";
            exit;
        } else {
            $error = "❌ Invalid verification code. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ALICE | Email Verification</title>
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

        .verify-container {
            background: #161b22;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.6);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .verify-container h2 {
            margin-bottom: 20px;
            color: #238636;
        }

        .verify-container input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 8px;
            border: none;
            background: #0d1117;
            color: #fff;
            font-size: 16px;
        }

        .verify-container input::placeholder {
            color: #888;
        }

        .verify-container form {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            width: 100%;
        }

        .verify-container input,
        .verify-container button {
            width: 100%;
            max-width: 300px;
        }

        .verify-container button {
            padding: 12px;
            border: none;
            border-radius: 8px;
            background: #238636;
            color: #fff;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s;
        }

        .verify-container button:hover {
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

<div class="verify-container">
    <h2>📧 Verify Your Email</h2>

    <?php
    if (!empty($error)) echo "<div class='error'>{$error}</div>";
    if (isset($_GET['resend'])) echo "<div class='success'>A new code has been sent to your email.</div>";
    ?>

    <form method="POST">
        <input type="text" name="verification_code" placeholder="Enter verification code" required>
        <button type="submit">Verify</button>
    </form>

    <div class="links">
        Didn’t receive the code? <a href="resend_code.php">Resend Verification Code</a>
    </div>
</div>

</body>
</html>
