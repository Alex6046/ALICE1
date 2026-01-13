<?php
session_start();
include("db.php");

// =======================
// CHECK SESSION VARIABLES
// =======================
if (!isset($_SESSION['user_id']) || !isset($_SESSION['new_password']) || !isset($_SESSION['verification_code'])) {
    header("Location: change_password.php");
    exit();
}

$message = "";
$user_id = $_SESSION['user_id'];

if (isset($_POST['verify'])) {
    $entered_code = $_POST['verify_code'];

    if ($entered_code === $_SESSION['verification_code']) {
        // Update password in database
        $sql = "UPDATE users SET password=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $_SESSION['new_password'], $user_id);
        $stmt->execute();

        // Clear session variables
        unset($_SESSION['new_password']);
        unset($_SESSION['verification_code']);

        // Auto redirect to login page after 3 seconds
        $success = "Password successfully updated! Redirecting to login...";
        header("refresh:3; url=login.php");
    } else {
        $message = "❌ Invalid verification code!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ALICE | Verify Password Change</title>
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
    </style>
</head>
<body>

<div class="verify-container">
    <h2>🔑 Verify Password Change</h2>

    <?php
    if(!empty($message)) echo "<div class='error'>{$message}</div>";
    if(!empty($success)) echo "<div class='success'>{$success}</div>";
    ?>

    <form method="POST">
        <input type="text" name="verify_code" placeholder="Enter Verification Code" required>
        <button type="submit" name="verify">Verify & Update Password</button>
    </form>
</div>

</body>
</html>
