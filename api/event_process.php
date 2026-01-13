<?php
session_start();
include("db.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function json_response(array $payload, int $statusCode = 200): void {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // -----------------------------------------------
    // DELETE EVENT
    // -----------------------------------------------
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {

        if (!isset($_POST['id'])) {
            json_response(['status'=>'error','message'=>'Missing event ID'], 400);
        }

        $id = intval($_POST['id']);

        $stmt = mysqli_prepare($conn, "DELETE FROM events WHERE id = ?");
        if (!$stmt) {
            json_response(['status' => 'error', 'message' => 'Prepare failed: ' . mysqli_error($conn)], 500);
        }
        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {
            json_response(['status' => 'success', 'message' => 'Event deleted successfully.']);
        } else {
            json_response(['status' => 'error', 'message' => 'Database delete error: ' . mysqli_stmt_error($stmt)], 500);
        }
    }

    // -----------------------------------------------
    // UPDATE EVENT
    // -----------------------------------------------
    if (isset($_POST['id']) && isset($_POST['update'])) {

        $id = intval($_POST['id']);
        $name = trim($_POST['name'] ?? '');
        $date = trim($_POST['date'] ?? '');
        $time = trim($_POST['time'] ?? '');
        $venue = trim($_POST['venue'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = trim($_POST['status'] ?? '');

        if (!$id || !$name || !$date || !$venue || !$status) {
            json_response(['status'=>'error','message'=>'All fields are required for update.'], 400);
        }

        $stmt = mysqli_prepare($conn, "UPDATE events SET title=?, date=?, time=?, venue=?, description=?, status=? WHERE id=?");
        if ($stmt) {
            $timeParam = ($time === '') ? null : $time;
            mysqli_stmt_bind_param($stmt, "ssssssi", $name, $date, $timeParam, $venue, $description, $status, $id);
            if (mysqli_stmt_execute($stmt)) {
                json_response([
                    'status'=>'success',
                    'message'=>'Event updated successfully!',
                    'data'=>[
                        'id'=>$id,
                        'name'=>$name,
                        'date'=>$date,
                        'time'=>$time,
                        'venue'=>$venue,
                        'description'=>$description,
                        'status'=>$status
                    ]
                ]);
            } else {
                json_response(['status'=>'error','message'=>'Database update error: '.mysqli_stmt_error($stmt)], 500);
            }
            mysqli_stmt_close($stmt);
        } else {
            json_response(['status'=>'error','message'=>'Prepare failed: '.mysqli_error($conn)], 500);
        }
    }

    // -----------------------------------------------
    // CREATE EVENT
    // -----------------------------------------------
    $name = trim($_POST['name'] ?? '');
    $date = trim($_POST['date'] ?? '');
    $time = trim($_POST['time'] ?? '');
    $venue = trim($_POST['venue'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = trim($_POST['status'] ?? 'upcoming');

    if (!$name || !$date || !$venue) {
        json_response(['status'=>'error','message'=>'All fields are required.'], 400);
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO events (title, date, time, venue, description, status) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $timeParam = ($time === '') ? null : $time;
        mysqli_stmt_bind_param($stmt, "ssssss", $name, $date, $timeParam, $venue, $description, $status);
        if (mysqli_stmt_execute($stmt)) {
            $event_id = mysqli_insert_id($conn);
            json_response([
                'status'=>'success',
                'message'=>'Event created successfully!',
                'data'=>[
                    'id'=>$event_id,
                    'name'=>$name,
                    'date'=>$date,
                    'time'=>$time,
                    'venue'=>$venue,
                    'description'=>$description,
                    'status'=>$status
                ]
            ]);
        } else {
            json_response(['status'=>'error','message'=>'Database error: '.mysqli_stmt_error($stmt)], 500);
        }
        mysqli_stmt_close($stmt);
    } else {
        json_response(['status'=>'error','message'=>'Prepare failed: '.mysqli_error($conn)], 500);
    }
}
?>
