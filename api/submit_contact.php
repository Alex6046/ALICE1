<?php
// submit_contact.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure server uses Malaysia time
date_default_timezone_set('Asia/Kuala_Lumpur');

session_start();
require_once 'notifications.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: home.php#contact");
    exit;
}

// Retrieve and sanitize form input
$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

// Basic validation
if ($name === '' || $email === '' || $subject === '' || $message === '') {
    header("Location: home.php#contact?err=empty");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: home.php#contact?err=email");
    exit;
}

// Contact administrator configuration
$adminEmail = 'maliwei@graduate.utm.my';
$adminName  = 'ALICE Support';

// Send notification emails (do not block user if email fails)
try {
    // Send message to admin
    notify_contact_admin(
        $adminEmail,
        $adminName,
        $name,
        $email,
        $subject,
        $message
    );

    // Send confirmation receipt to user
    notify_contact_receipt(
        $email,
        $name,
        $subject
    );

} catch (Throwable $e) {
    // Optional: log error without interrupting user experience
    // error_log("Contact form email error: " . $e->getMessage());
}

// Redirect back to contact section with success indicator
header("Location: home.php#contact?sent=1");
exit;