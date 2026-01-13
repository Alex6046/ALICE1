<?php
session_start();
include 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please login to submit an event']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $title = trim($_POST['title']);
    $date = $_POST['date'];
    $time = $_POST['time'];
    $venue = trim($_POST['venue']);
    $capacity = intval($_POST['capacity']);
    $description = trim($_POST['description']);
    $organizer_name = trim($_POST['organizer_name']);
    $organizer_email = trim($_POST['organizer_email']);
    $contact_number = trim($_POST['contact_number'] ?? '');
    
    // Validation
    if (empty($title) || empty($date) || empty($time) || empty($venue) || empty($capacity) || empty($description) || empty($organizer_name) || empty($organizer_email)) {
        echo json_encode(['status' => 'error', 'message' => 'All required fields must be filled']);
        exit;
    }
    
    // Check if date is in the future
    $event_datetime = $date . ' ' . $time;
    if (strtotime($event_datetime) <= time()) {
        echo json_encode(['status' => 'error', 'message' => 'Event must be in the future']);
        exit;
    }
    
    // Insert into proposed_events table
    $stmt = $conn->prepare("INSERT INTO proposed_events (title, date, time, venue, capacity, description, organizer_name, organizer_email, contact_number, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("ssssissss", $title, $date, $time, $venue, $capacity, $description, $organizer_name, $organizer_email, $contact_number);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Event proposal submitted successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}

$conn->close();
?>