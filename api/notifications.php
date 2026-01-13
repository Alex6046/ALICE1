<?php
date_default_timezone_set('Asia/Kuala_Lumpur');

require_once __DIR__ . '/phpmailer/src/Exception.php';
require_once __DIR__ . '/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function notify_send_mail(string $toEmail, string $toName, string $subject, string $htmlBody): void
{
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
        $mail->addAddress($toEmail, $toName);
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';

        $mail->Subject = $subject;
        $mail->Body = $htmlBody;

        $mail->send();
    } catch (Exception $e) {
        // Do not block the main flow, but log the error for debugging
        error_log('notify_send_mail failed to ' . $toEmail . ' | ' . $e->getMessage());
    }
}

function notify_ip_label(): string
{
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR']
        ?? $_SERVER['HTTP_CLIENT_IP']
        ?? $_SERVER['REMOTE_ADDR']
        ?? 'Unknown';

    // If there are multiple IPs (e.g., via proxy), take the first one
    if (strpos($ip, ',') !== false) {
        $ip = trim(explode(',', $ip)[0]);
    }

    // Friendly label for local development
    if ($ip === '::1' || $ip === '127.0.0.1') {
        return '127.0.0.1 (localhost)';
    }

    return $ip;
}
function notify_verification_code(string $email, string $username, string $code): void
{
    $subject = 'ALICE Email Verification';
    $body = "
        <h2>Hello {$username},</h2>
        <p>Your verification code: <b>{$code}</b></p>
        <p>Thank you,<br>ALICE Team</p>
    ";
    notify_send_mail($email, $username, $subject, $body);
}

function notify_login_alert(string $email, string $username): void
{
    $time = date('Y-m-d H:i:s');
    $tz = date_default_timezone_get();
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    if ($ip === '::1' || $ip === '127.0.0.1') $ip = '127.0.0.1 (localhost)';

    $subject = 'ALICE Login Alert';
    $body = "
        <h3>Hello {$username},</h3>
        <p>✅ A successful login to your ALICE account was detected.</p>
        <p><strong>Time:</strong> {$time} ({$tz})<br>
           <strong>IP Address:</strong> {$ip}</p>
        <p>If this was not you, please reset your password immediately.</p>
        <p>— ALICE Security Team</p>
    ";
    notify_send_mail($email, $username, $subject, $body);
}

function notify_logout_alert(string $email, string $username): void
{
    $time = date('Y-m-d H:i:s');
    $tz = date_default_timezone_get();
    $ip = notify_ip_label();

    $subject = 'ALICE Logout Notification';
    $body = "
        <h3>Hello {$username},</h3>
        <p>You have been successfully logged out of your ALICE account.</p>
        <p><strong>Time:</strong> {$time} ({$tz})<br>
           <strong>IP Address:</strong> {$ip}</p>
        <p>If this was not you, please log in and change your password.</p>
        <p>— ALICE Security Team</p>
    ";

    notify_send_mail($email, $username, $subject, $body);
}

function notify_reset_request(string $email, string $username, string $resetCode): void
{
    $subject = 'ALICE Security Alert: Password Reset Request';
    $body = "
        <h2>Hello {$username},</h2>

        <p>We received a request to reset the password for your ALICE account.</p>

        <p>If you made this request, please use the verification code below to continue the password reset process:</p>

        <p><b>{$resetCode}</b></p>

        <p>This verification code will expire in 15 minutes.</p>

        <p>If you did <strong>not</strong> request a password reset, you may safely ignore this email. For your security, we recommend reviewing your account activity and updating your password if necessary.</p>

        <p>— ALICE Security Team</p>
    ";

    notify_send_mail($email, $username, $subject, $body);
}

function notify_reset_success(string $email, string $username): void
{
    $subject = 'ALICE: Password Reset Successful';
    $body = "
        <h2>Hello {$username},</h2>
        <p>✅ Your ALICE account password has been reset successfully.</p>
        <p>You can now log in here: <a href='http://localhost:8888/aliceweb/login.php'>Login to ALICE</a></p>
        <p>If you did not perform this action, please reset your password again immediately.</p>
        <p>— ALICE Security Team</p>
    ";

    notify_send_mail($email, $username, $subject, $body);
}

function notify_highlight_submitted(string $email, string $username, string $filename): void
{
    $subject = 'ALICE: Highlight Upload Submitted';
    $body = "
        <h2>Hello {$username},</h2>
        <p>✅ Your club highlight upload has been submitted successfully.</p>
        <p>Status: <b>pending</b> (waiting for admin review)</p>
        <p><b>File:</b> {$filename}</p>
        <p>Thank you,<br>ALICE Team</p>
    ";

    notify_send_mail($email, $username, $subject, $body);
}



/**
 * Send contact message notification to admin/support
 */
function notify_contact_admin(
    string $adminEmail,
    string $adminName,
    string $fromName,
    string $fromEmail,
    string $subject,
    string $message
): void {
    $mailSubject = "ALICE Support - Contact Message: " . $subject;

    // Sanitize user input to prevent HTML injection in email
    $safeMessage   = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
    $safeFromName  = htmlspecialchars($fromName, ENT_QUOTES, 'UTF-8');
    $safeFromEmail = htmlspecialchars($fromEmail, ENT_QUOTES, 'UTF-8');
    $safeSubject   = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');

    $body = "
        <h2>New Contact Form Submission</h2>
        <p><b>Name:</b> {$safeFromName}</p>
        <p><b>Email:</b> {$safeFromEmail}</p>
        <p><b>Subject:</b> {$safeSubject}</p>
        <p><b>Message:</b><br>{$safeMessage}</p>
        <p>— ALICE System</p>
    ";

    // Send email using the centralized mail sender
    notify_send_mail($adminEmail, $adminName, $mailSubject, $body);
}

/**
 * Send confirmation email to the user after contact form submission
 */
function notify_contact_receipt(string $toEmail, string $toName, string $subject): void
{
    $safeToName  = htmlspecialchars($toName, ENT_QUOTES, 'UTF-8');
    $safeSubject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');

    $mailSubject = "Thank you for contacting ALICE Support";
    $body = "
        <h2>Hello {$safeToName},</h2>
        <p>✅ We have received your message. Our team will respond as soon as possible.</p>
        <p><b>Subject:</b> {$safeSubject}</p>
        <p>Thank you,<br>ALICE Support Team</p>
    ";

    notify_send_mail($toEmail, $toName, $mailSubject, $body);
}

if (!function_exists('notify_highlight_status')) {
    /**
     * Notify a user that their highlight was approved or rejected.
     *
     * Note: Your `highlights` table currently does NOT store a user_id.
     * This function expects you to pass the submitter's email/name (e.g., stored in highlights as submitted_email/submitted_username).
     */
    function notify_highlight_status(string $toEmail, string $toName, string $status, string $highlightLabel = 'your highlight'): void
    {
        $safeName  = htmlspecialchars($toName, ENT_QUOTES, 'UTF-8');
        $safeLabel = htmlspecialchars($highlightLabel, ENT_QUOTES, 'UTF-8');

        $statusLower = strtolower($status);
        $isApproved  = ($statusLower === 'approved');

        $mailSubject = $isApproved
            ? 'ALICE Support - Highlight Approved'
            : 'ALICE Support - Highlight Rejected';

        $body = $isApproved
            ? "
                <h2>Hello {$safeName},</h2>
                <p>✅ Your highlight ({$safeLabel}) has been <b>approved</b> and is now visible.</p>
                <p>Thank you for contributing to ALICE.</p>
                <p>— ALICE Team</p>
              "
            : "
                <h2>Hello {$safeName},</h2>
                <p>❌ Unfortunately, your highlight ({$safeLabel}) has been <b>rejected</b>.</p>
                <p>You may submit a new highlight that meets the guidelines.</p>
                <p>— ALICE Team</p>
              ";

        notify_send_mail($toEmail, $toName, $mailSubject, $body);
    }
}
