<?php
include "db.php";
require_once 'notifications.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$id = intval($_POST['id'] ?? 0);

if(!$id) exit(json_encode(['status'=>'error','message'=>'Invalid highlight ID']));

// Best-effort lookup for submitter info (so we can email the user on approve/reject)
$toEmail = null;
$toName  = 'there';
$label   = '';

// This query assumes you have added submitted_email/submitted_username in `highlights`.
// If those columns do not exist yet, the query will fail silently and no email will be sent.
$infoSql = "SELECT title, image, submitted_email, submitted_username FROM highlights WHERE id = $id LIMIT 1";
if ($infoRes = mysqli_query($conn, $infoSql)) {
    if ($row = mysqli_fetch_assoc($infoRes)) {
        $toEmail = $row['submitted_email'] ?? null;
        $toName  = $row['submitted_username'] ?? $toName;
        $label   = !empty($row['title']) ? $row['title'] : (!empty($row['image']) ? $row['image'] : '');
    }
}

if($action=='approve'){
    $res = mysqli_query($conn,"UPDATE highlights SET status='approved' WHERE id=$id");

    // Send approval email (do not block main flow)
    if ($res && $toEmail) {
        try {
            notify_highlight_status($toEmail, $toName, 'approved', $label ?: 'your highlight');
        } catch (Throwable $e) {
            // Optional: error_log('Highlight approved email failed: ' . $e->getMessage());
        }
    }

    echo json_encode(['status'=>$res?'success':'error','message'=>$res?'Highlight approved':'Failed to approve']);
} elseif($action=='reject'){
    $res = mysqli_query($conn,"DELETE FROM highlights WHERE id=$id");

    // Send rejection email (do not block main flow)
    if ($res && $toEmail) {
        try {
            notify_highlight_status($toEmail, $toName, 'rejected', $label ?: 'your highlight');
        } catch (Throwable $e) {
            // Optional: error_log('Highlight rejected email failed: ' . $e->getMessage());
        }
    }

    echo json_encode(['status'=>$res?'success':'error','message'=>$res?'Highlight rejected':'Failed to reject']);
} elseif($action=='delete'){
    $res = mysqli_query($conn,"DELETE FROM highlights WHERE id=$id");
    echo json_encode(['status'=>$res?'success':'error','message'=>$res?'Highlight deleted':'Failed to delete']);
} else {
    echo json_encode(['status'=>'error','message'=>'Invalid action']);
}
?>
