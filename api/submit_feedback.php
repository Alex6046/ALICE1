<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $event_id = intval($_POST['event_id']);
    $rating = intval($_POST['rating']);
    $feedback_text = trim($_POST['feedback_text']);
    $display_name = trim($_POST['display_name']) ?: 'Anonymous';
    $is_public = isset($_POST['is_public']) ? 1 : 0;

    // Validate rating
    if ($rating < 1 || $rating > 5) {
        $_SESSION['error'] = "Please provide a valid rating (1-5).";
        header("Location: student_dashboard.php");
        exit;
    }

    // Check if user is registered for this event
    $checkQuery = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
    $checkQuery->bind_param("i", $user_id);
    $checkQuery->execute();
    $userResult = $checkQuery->get_result();
    $user = $userResult->fetch_assoc();
    
    $registrationQuery = $conn->prepare("SELECT id FROM event_registrations WHERE username = ? AND email = ? AND event_id = ?");
    $registrationQuery->bind_param("ssi", $user['username'], $user['email'], $event_id);
    $registrationQuery->execute();
    $registrationResult = $registrationQuery->get_result();
    
    if ($registrationResult->num_rows == 0) {
        $_SESSION['error'] = "You are not registered for this event.";
        header("Location: student_dashboard.php");
        exit;
    }

    // Check if feedback already exists
    $existingQuery = $conn->prepare("SELECT id FROM event_feedback WHERE user_id = ? AND event_id = ?");
    $existingQuery->bind_param("ii", $user_id, $event_id);
    $existingQuery->execute();
    $existingResult = $existingQuery->get_result();
    
    if ($existingResult->num_rows > 0) {
        // Update existing feedback
        $updateQuery = $conn->prepare("UPDATE event_feedback SET rating = ?, feedback_text = ?, display_name = ?, is_public = ? WHERE user_id = ? AND event_id = ?");
        $updateQuery->bind_param("issiii", $rating, $feedback_text, $display_name, $is_public, $user_id, $event_id);
    } else {
        // Insert new feedback
        $updateQuery = $conn->prepare("INSERT INTO event_feedback (user_id, event_id, rating, feedback_text, display_name, is_public) VALUES (?, ?, ?, ?, ?, ?)");
        $updateQuery->bind_param("iiissi", $user_id, $event_id, $rating, $feedback_text, $display_name, $is_public);
    }

    if ($updateQuery->execute()) {
        $_SESSION['success'] = "Thank you for your feedback! Your review has been " . ($is_public ? "published publicly." : "saved privately.");
    } else {
        $_SESSION['error'] = "There was an error submitting your feedback. Please try again.";
    }

    header("Location: student_dashboard.php");
    exit;
}
?>