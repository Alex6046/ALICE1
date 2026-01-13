<?php
session_start();
include("db.php");
require_once 'notifications.php';

if (!isset($_SESSION['email'])) {
    header("Location: forgot_password.php");
    exit;
}

$email = $_SESSION['email'];
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $code = trim($_POST['code']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    // Check code and expiry
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND reset_code = ? AND reset_expiry > NOW()");
    $stmt->bind_param("ss", $email, $code);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

        if ($user) {
        if ($password !== $confirm) {
            $error = "Passwords do not match.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET password = ?, reset_code = NULL, reset_expiry = NULL WHERE email = ?");
            $update->bind_param("ss", $hashed, $email);
            $update->execute();
            // ✅ Send password reset success email via centralized notifications
            $username = $user['username'] ?? 'there';
            notify_reset_success($email, $username);

            $success = "Password reset successfully! Redirecting to login page...";
            unset($_SESSION['email']);

            // Auto redirect after 3 seconds
            header("refresh:3;url=login.php");
        }
    } else {
        $error = "Invalid or expired verification code.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password | ALICE</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="form-box">
    <h2>Reset Password</h2>

    <?php
    if (isset($error)) echo "<p class='error'>{$error}</p>";
    if (!empty($success)) echo "<p class='success'>{$success}</p>";
    ?>

    <?php if (empty($success)) : ?>
        <form method="POST" action="">
            <input type="text" name="code" placeholder="Verification Code" required>
            <input type="password" name="password" placeholder="New Password" required>
            <input type="password" name="confirm" placeholder="Confirm Password" required>
            <button type="submit">Reset Password</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
