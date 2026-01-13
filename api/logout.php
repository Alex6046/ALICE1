<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set('Asia/Kuala_Lumpur');

session_start();
include("db.php");
require_once 'notifications.php';

$userEmail = $_SESSION['email'] ?? null;
$userName  = $_SESSION['username'] ?? 'there';

if (!$userEmail && isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT email, username FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $userEmail = $row['email'] ?? null;
        $userName  = $row['username'] ?? $userName;
    }
}

// ✅ unified notification
if ($userEmail) {
    notify_logout_alert($userEmail, $userName);
}

session_destroy();
header("Location: login.php");
exit;
?>