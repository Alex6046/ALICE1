<?php
include("db.php");
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


$email = $_GET['email'] ?? '';

if ($email) {
    $verification_code = rand(100000, 999999);
    $update = $conn->prepare("UPDATE users SET verification_code=? WHERE email=?");
    $update->bind_param("ss", $verification_code, $email);
    $update->execute();

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
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Resent ALICE Verification Code';
        $mail->Body = "<h3>Your new verification code is: <b>$verification_code</b></h3>";

        $mail->send();
        header("Location: verify.php?email=" . urlencode($email));
        exit;
    } catch (Exception $e) {
        echo "Failed to resend code.";
    }
} else {
    echo "Invalid request.";
}
?>
