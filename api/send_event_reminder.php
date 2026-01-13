<?php
// send_event_reminder.php
// Sends reminder emails to users who registered for events starting within the next 30 minutes.

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Use Malaysia time
date_default_timezone_set('Asia/Kuala_Lumpur');

require_once 'db.php';

// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once 'phpmailer/src/Exception.php';
require_once 'phpmailer/src/PHPMailer.php';
require_once 'phpmailer/src/SMTP.php';

// -------------------------------
// 1) Calculate the 30-minute window using PHP time (Asia/Kuala_Lumpur)
// This avoids MySQL timezone mismatch issues (e.g., MySQL NOW() in UTC).
// -------------------------------
$windowStart = date('Y-m-d H:i:s');
$windowEnd   = date('Y-m-d H:i:s', strtotime('+30 minutes'));

// Debug (visible in browser)
echo "<pre>";
echo "[DEBUG] PHP Timezone: " . date_default_timezone_get() . "\n";
echo "[DEBUG] Window Start: {$windowStart}\n";
echo "[DEBUG] Window End  : {$windowEnd}\n";

// Also show MySQL NOW() for comparison (helps diagnose timezone mismatch)
if ($mysqlNowRes = $conn->query("SELECT NOW() AS mysql_now, @@session.time_zone AS session_tz, @@global.time_zone AS global_tz")) {
    $mysqlNowRow = $mysqlNowRes->fetch_assoc();
    echo "[DEBUG] MySQL NOW(): " . ($mysqlNowRow['mysql_now'] ?? '-') . "\n";
    echo "[DEBUG] MySQL session.time_zone: " . ($mysqlNowRow['session_tz'] ?? '-') . "\n";
    echo "[DEBUG] MySQL global.time_zone : " . ($mysqlNowRow['global_tz'] ?? '-') . "\n";
}

// -------------------------------
// 2) Fetch events starting within the next 30 minutes
// Assumes events.date is YYYY-MM-DD and events.time is HH:MM:SS (or TIME).
// -------------------------------
$query = "
    SELECT e.id, e.title, e.date, e.time, e.venue, e.description
    FROM events e
    WHERE TIMESTAMP(e.date, e.time) BETWEEN ? AND ?
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    echo "[ERROR] Prepare failed: {$conn->error}\n</pre>";
    exit;
}

$stmt->bind_param('ss', $windowStart, $windowEnd);
$stmt->execute();
$events = $stmt->get_result();

echo "[DEBUG] Events found in window: " . ($events ? $events->num_rows : 0) . "\n";

$remindersSent = 0;
$attempted = 0;

if ($events && $events->num_rows > 0) {
    while ($event = $events->fetch_assoc()) {
        // -------------------------------
        // 3) Fetch users who registered for this event
        // -------------------------------
        $registrationsQuery = "
            SELECT er.username, er.email
            FROM event_registrations er
            WHERE er.event_id = ?
        ";

        $regStmt = $conn->prepare($registrationsQuery);
        if (!$regStmt) {
            echo "[ERROR] Registration query prepare failed: {$conn->error}\n";
            continue;
        }

        $regStmt->bind_param('i', $event['id']);
        $regStmt->execute();
        $registrations = $regStmt->get_result();

        echo "[DEBUG] Event #{$event['id']} - {$event['title']} registrants: " . ($registrations ? $registrations->num_rows : 0) . "\n";

        if ($registrations && $registrations->num_rows > 0) {
            while ($user = $registrations->fetch_assoc()) {
                $attempted++;
                if (sendReminderEmail($user, $event)) {
                    $remindersSent++;
                }
            }
        }

        $regStmt->close();
    }

    echo "\n[RESULT] Reminder emails attempted: {$attempted}\n";
    echo "[RESULT] Reminder emails sent     : {$remindersSent}\n";
} else {
    echo "\n[RESULT] No events found starting in the next 30 minutes.\n";
}

echo "</pre>";

$stmt->close();
$conn->close();

// -------------------------------
// Email sender
// -------------------------------
function sendReminderEmail($user, $event) {
    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'azrifikriiskandar@gmail.com';
        $mail->Password = 'ejmk soge zjuu rohi';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('azrifikriiskandar@gmail.com', 'ALICE Event Reminder');
        $mail->addAddress($user['email'], $user['username']);
        $mail->isHTML(true);

        $eventDate = date('F j, Y', strtotime($event['date']));
        $eventTime = !empty($event['time']) ? date('g:i A', strtotime($event['time'])) : 'TBA';

        // Subject should match the 30-minute logic
        $mail->Subject = "ALICE Reminder: {$event['title']} starts soon";

        $mail->Body = "
            <h2>Hello " . htmlspecialchars($user['username']) . ",</h2>
            <p>This is a friendly reminder that your registered event will start in about <strong>30 minutes</strong>:</p>

            <div style='background:#f8f9fa; padding:15px; border-radius:8px; border-left:4px solid #238636;'>
                <h3 style='margin-top:0; color:#238636;'>" . htmlspecialchars($event['title']) . "</h3>
                <p><strong>📅 Date:</strong> {$eventDate}</p>
                <p><strong>⏰ Time:</strong> {$eventTime}</p>
                <p><strong>📍 Venue:</strong> " . htmlspecialchars($event['venue']) . "</p>
                <p><strong>📝 Description:</strong> " . htmlspecialchars($event['description']) . "</p>
            </div>

            <p>We look forward to seeing you there!</p>
            <p>Best regards,<br>ALICE Team</p>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("[send_event_reminder] Email Error for {$user['email']}: " . $mail->ErrorInfo);
        return false;
    }
}