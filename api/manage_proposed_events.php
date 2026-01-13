<?php
session_start();
include 'db.php';
header('Content-Type: application/json');

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? '';
$id = intval($_POST['id'] ?? 0);
$reason = $_POST['reason'] ?? '';

if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
    exit;
}

if ($action === 'approve') {
    // Get the proposed event
    $stmt = $conn->prepare("SELECT * FROM proposed_events WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $proposal = $stmt->get_result()->fetch_assoc();
    
    if ($proposal) {
        // Insert into main events table
        $insertStmt = $conn->prepare("INSERT INTO events (title, date, venue, description, status) VALUES (?, ?, ?, ?, 'Upcoming')");
        $insertStmt->bind_param("ssss", $proposal['title'], $proposal['date'], $proposal['venue'], $proposal['description']);
        
        if ($insertStmt->execute()) {
            // Update proposal status
            $updateStmt = $conn->prepare("UPDATE proposed_events SET status = 'approved' WHERE id = ?");
            $updateStmt->bind_param("i", $id);
            $updateStmt->execute();
            
            echo json_encode(['status' => 'success', 'message' => 'Event approved and published!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to create event']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Proposal not found']);
    }
} elseif ($action === 'reject') {
    $stmt = $conn->prepare("UPDATE proposed_events SET status = 'rejected', admin_notes = ? WHERE id = ?");
    $stmt->bind_param("si", $reason, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Event proposal rejected']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to reject proposal']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}

$conn->close();
?>