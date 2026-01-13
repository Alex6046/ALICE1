<?php
session_start();
include 'db.php'; // Make sure $conn is defined here

// Use a consistent timezone for all date/time formatting and calculations
date_default_timezone_set('Asia/Kuala_Lumpur');

// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Prevent "Cannot declare class PHPMailer..." when this file is included multiple times
if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
    require_once 'phpmailer/src/Exception.php';
    require_once 'phpmailer/src/PHPMailer.php';
    require_once 'phpmailer/src/SMTP.php';
}

// Redirect if user not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check if event_id is provided
if (isset($_POST['event_id'])) {
    $event_id = intval($_POST['event_id']); // Sanitize input
    $user_id = $_SESSION['user_id'];

    // Fetch current user's username and email from users table
    $userQuery = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
    $userQuery->bind_param("i", $user_id);
    $userQuery->execute();
    $userResult = $userQuery->get_result();

    if ($userResult->num_rows === 0) {
        $_SESSION['message'] = "User not found!";
        header("Location: home.php#events");
        exit;
    }

    $user = $userResult->fetch_assoc();
    $username = $user['username'];
    $email = $user['email'];

    // Optional: Check if event exists
    $checkEvent = $conn->prepare("SELECT `id`, `title`, `date`, `time`, `venue` FROM `events` WHERE `id` = ?");
    if (!$checkEvent) {
        error_log("Prepare failed in apply_event.php (checkEvent): " . $conn->error);
        $_SESSION['message'] = "System error: Unable to check event.";
        header("Location: home.php#events");
        exit;
    }
    $checkEvent->bind_param("i", $event_id);
    $checkEvent->execute();
    $result = $checkEvent->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['message'] = "Event not found!";
        header("Location: home.php#events");
        exit;
    }

    $event = $result->fetch_assoc();
    $event_title = $event['title'];
    $event_date = $event['date'] ?? null;
    $event_time = $event['time'] ?? null;
    $event_venue = $event['venue'] ?? '';

    // Optional: Check if user already applied
    $checkAlready = $conn->prepare("SELECT `id` FROM `event_registrations` WHERE `event_id` = ? AND `username` = ? AND `email` = ? LIMIT 1");
    if (!$checkAlready) {
        error_log("Prepare failed in apply_event.php (checkAlready): " . $conn->error);
        $_SESSION['message'] = "System error: Unable to check registration.";
        header("Location: home.php#events");
        exit;
    }
    $checkAlready->bind_param("iss", $event_id, $username, $email);
    $checkAlready->execute();
    $alreadyResult = $checkAlready->get_result();

    if ($alreadyResult->num_rows > 0) {
        $_SESSION['message'] = "You have already applied for this event!";
        header("Location: home.php#events");
        exit;
    }

    // Insert registration with username and email
    $insert = $conn->prepare("INSERT INTO `event_registrations` (`event_id`, `username`, `email`, `created_at`) VALUES (?, ?, ?, NOW())");
    if (!$insert) {
        error_log("Prepare failed in apply_event.php (insert): " . $conn->error);
        $_SESSION['message'] = "System error: Unable to register.";
        header("Location: home.php#events");
        exit;
    }
    $insert->bind_param("iss", $event_id, $username, $email);

    if ($insert->execute()) {
        $_SESSION['message'] = "Successfully applied for the event!";

        // ============================
        // CREATE 30-MINUTE REMINDER (DB)
        // ============================

        $reminder_minutes = 30;

        // Only schedule if the event has both date and time
        if (!empty($event_date) && !empty($event_time)) {
            // Avoid duplicates (same user/event/minutes)
            $chkRem = $conn->prepare("SELECT id FROM event_reminders WHERE user_id = ? AND event_id = ? AND reminder_minutes = ? LIMIT 1");
            if (!$chkRem) {
                error_log('[apply_event] Prepare failed for event_reminders duplicate check: ' . $conn->error);
            } else {
                $chkRem->bind_param("iii", $user_id, $event_id, $reminder_minutes);
                $chkRem->execute();
                $chkRes = $chkRem->get_result();

                if ($chkRes->num_rows === 0) {
                    // Compute send_at using Asia/Kuala_Lumpur timezone (avoid server default timezone issues)
                    $tz = new DateTimeZone('Asia/Kuala_Lumpur');
                    $dtEvent = DateTime::createFromFormat('Y-m-d H:i:s', $event_date . ' ' . $event_time, $tz);

                    if ($dtEvent instanceof DateTime) {
                        $dtEvent->modify("-{$reminder_minutes} minutes");
                        $send_at = $dtEvent->format('Y-m-d H:i:s');

                        // Insert reminder (schema uses send_at only)
                        $insRem = $conn->prepare(
                            "INSERT INTO event_reminders (user_id, event_id, reminder_minutes, send_at, status, created_at) \
                             VALUES (?, ?, ?, ?, 'pending', NOW())"
                        );

                        if ($insRem) {
                            $insRem->bind_param("iiis", $user_id, $event_id, $reminder_minutes, $send_at);
                            $insRem->execute();
                        } else {
                            error_log('[apply_event] Prepare failed for event_reminders insert: ' . $conn->error);
                        }
                    } else {
                        error_log("[apply_event] Invalid event datetime; skipping reminder scheduling for event_id={$event_id}");
                    }
                }
            }
        } else {
            // If time is missing, we can't schedule a 30-min reminder
            error_log("[apply_event] Event time missing; skipping reminder scheduling for event_id={$event_id}");
        }
        // ============================

        // ============================
        // SEND CONFIRMATION EMAIL
        // ============================
        // NOTE: If email fails, we log detailed debug output to PHP error log.
        $mail = new PHPMailer(true);
        try {
            // Enable debug output only when testing (add ?maildebug=1 to URL)
            $mailDebug = isset($_GET['maildebug']) && $_GET['maildebug'] == '1';
            if ($mailDebug) {
                $mail->SMTPDebug = 2; // 0=off, 2=client+server messages
                $mail->Debugoutput = function ($str, $level) {
                    error_log("[apply_event][PHPMailer][L{$level}] {$str}");
                };
            }

            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'azrifikriiskandar@gmail.com';
            $mail->Password = 'ejmk soge zjuu rohi';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';

            $mail->setFrom('azrifikriiskandar@gmail.com', 'ALICE Support');
            $mail->addReplyTo('azrifikriiskandar@gmail.com', 'ALICE Support');
            $mail->addAddress($email, $username);
            $mail->isHTML(true);
            $mail->Subject = "ALICE Event Registration - {$event_title}";

            $eventInfoLines = "";
            if (!empty($event_date)) {
                $eventInfoLines .= "<p><b>Date:</b> " . htmlspecialchars(date('F j, Y', strtotime($event_date))) . "</p>";
            }
            if (!empty($event_time)) {
                $eventInfoLines .= "<p><b>Time:</b> " . htmlspecialchars(date('g:i A', strtotime($event_time))) . "</p>";
            }
            if (!empty($event_venue)) {
                $eventInfoLines .= "<p><b>Venue:</b> " . htmlspecialchars($event_venue) . "</p>";
            }

            $mail->Body = "
                <h2>Hello " . htmlspecialchars($username) . ",</h2>
                <p>Thank you for registering for the event: <b>" . htmlspecialchars($event_title) . "</b>.</p>
                {$eventInfoLines}
                <p>We look forward to your participation!</p>
                <p>ALICE Team</p>
            ";

            // Also set a plain-text alternative for better deliverability
            $plainDate = !empty($event_date) ? date('F j, Y', strtotime($event_date)) : '';
            $plainTime = !empty($event_time) ? date('g:i A', strtotime($event_time)) : '';
            $plainVenue = !empty($event_venue) ? $event_venue : '';
            $mail->AltBody = "Hello {$username},\n\n" .
                "Thank you for registering for the event: {$event_title}.\n" .
                (!empty($plainDate) ? "Date: {$plainDate}\n" : "") .
                (!empty($plainTime) ? "Time: {$plainTime}\n" : "") .
                (!empty($plainVenue) ? "Venue: {$plainVenue}\n" : "") .
                "\nWe look forward to your participation!\n\nALICE Team";

            $mail->send();
            error_log("[apply_event] Confirmation email sent to {$email} for event_id={$event_id}");
        } catch (Exception $e) {
            error_log("[apply_event] Confirmation email FAILED to {$email} for event_id={$event_id}. Error: " . $mail->ErrorInfo);
            // Keep the registration successful even if email fails.
        }
        // ============================

    } else {
        $_SESSION['message'] = "Failed to apply. Please try again.";
    }

} else {
    $_SESSION['message'] = "Invalid request.";
}

// Redirect back to events section
header("Location: home.php#events");
exit;
?>
