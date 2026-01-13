<?php
session_start();
include("db.php");

// PHPMailer
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'phpmailer/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// =======================
// CHECK IF USER IS LOGGED IN
// =======================
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";
$user_id = $_SESSION['user_id'];

// =======================
// FETCH USER DATA
// =======================
// Users table (for password)
$sql_user = "SELECT email, password FROM users WHERE id=? LIMIT 1";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user = $stmt_user->get_result()->fetch_assoc();
$email = $user['email'];

// User profile table
$sql_profile = "SELECT * FROM user_profile WHERE user_id=? LIMIT 1";
$stmt_profile = $conn->prepare($sql_profile);
$stmt_profile->bind_param("i", $user_id);
$stmt_profile->execute();
$profile = $stmt_profile->get_result()->fetch_assoc();

// =======================
// HANDLE PROFILE UPDATE
// =======================
if (isset($_POST['update_profile'])) {
    $full_name = $_POST['full_name'];
    $matric = $_POST['matric_number'];
    $gender = $_POST['gender'];
    $year = $_POST['year_of_study'];
    $course = $_POST['course'];

    if ($profile) {
        // Update existing profile
        $sql = "UPDATE user_profile SET full_name=?, matric_number=?, gender=?, year_of_study=?, course=? WHERE user_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $full_name, $matric, $gender, $year, $course, $user_id);
        $stmt->execute();
    } else {
        // Insert new profile
        $sql = "INSERT INTO user_profile (user_id, full_name, matric_number, gender, year_of_study, course) VALUES (?,?,?,?,?,?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssis", $user_id, $full_name, $matric, $gender, $year, $course);
        $stmt->execute();
    }
    $message = "Profile updated successfully!";
}

// =======================
// HANDLE PASSWORD CHANGE REQUEST
// =======================
if (isset($_POST['next'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];

    if (password_verify($old_password, $user['password'])) {
        // Generate verification code
        $code = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789"), 0, 6);

        // Store new password and verification code in session
        $_SESSION['new_password'] = password_hash($new_password, PASSWORD_DEFAULT);
        $_SESSION['verification_code'] = $code;

        // Send verification code via email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'azrifikriiskandar@gmail.com';
            $mail->Password   = 'ejmk soge zjuu rohi';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('azrifikriiskandar@gmail.com', 'ALICE Verification');
            $mail->addAddress($email);

            $mail->Subject = "Password Change Verification Code";
            $mail->Body    = "Your verification code is: $code";

            $mail->send();

            // Redirect to verification page
            header("Location: verify_password.php");
            exit();
        } catch (Exception $e) {
            $message = "Email could not be sent. Mailer Error: " . $mail->ErrorInfo;
        }
    } else {
        $message = "Old password is incorrect!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password & Edit Profile</title>
    <style>
        body { font-family: Arial; background: #0d1117; color: white; padding: 20px; }
        .container { max-width: 600px; margin: auto; background: #161b22; padding: 20px; border-radius: 10px; }
        input, select, button { width: 100%; padding: 10px; margin: 8px 0; border-radius: 5px; border: none; }
        button { background: #238636; color: white; cursor: pointer; }
        .msg { color: lightgreen; margin-bottom: 15px; }
        h2 { margin-bottom: 10px; }
        hr { border-color: #30363d; margin: 20px 0; }
    </style>
</head>
<body>
<div class="container">
    <h2>Edit Profile</h2>
    <?php if($message!="") echo "<p class='msg'>$message</p>"; ?>
    <form method="POST">
        <input type="text" name="full_name" placeholder="Full Name" value="<?= $profile['full_name'] ?? '' ?>">
        <input type="text" name="matric_number" placeholder="Matric Number" value="<?= $profile['matric_number'] ?? '' ?>">
        <select name="gender">
            <option value="">Select Gender</option>
            <option value="Male" <?= (isset($profile['gender']) && $profile['gender']=="Male")?"selected":"" ?>>Male</option>
            <option value="Female" <?= (isset($profile['gender']) && $profile['gender']=="Female")?"selected":"" ?>>Female</option>
            <option value="Other" <?= (isset($profile['gender']) && $profile['gender']=="Other")?"selected":"" ?>>Other</option>
        </select>
        <input type="number" name="year_of_study" placeholder="Year of Study" value="<?= $profile['year_of_study'] ?? '' ?>">
        <input type="text" name="course" placeholder="Course" value="<?= $profile['course'] ?? '' ?>">
        <button type="submit" name="update_profile">Update Profile</button>
    </form>

    <hr>

    <h2>Change Password</h2>
    <form method="POST">
        <input type="password" name="old_password" placeholder="Old Password" required>
        <input type="password" name="new_password" placeholder="New Password" required>
        <button type="submit" name="next">Next</button>
    </form>
</div>
</body>
</html>
