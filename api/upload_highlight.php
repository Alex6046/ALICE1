<?php
session_start();
include 'db.php';
header('Content-Type: application/json');
require_once 'notifications.php';

if(!isset($_SESSION['user_id'])){
    echo json_encode(['status'=>'error','message'=>'Login required']);
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['highlight_image'])) {
    $file = $_FILES['highlight_image'];
    $filename = time().'_'.basename($file['name']);
    $targetDir = 'uploads/';
    if(!is_dir($targetDir)) mkdir($targetDir,0777,true);
    $targetFile = $targetDir.$filename;

    $allowed = ['image/jpeg','image/png','image/jpg'];
    if(!in_array($file['type'],$allowed)){
        echo json_encode(['status'=>'error','message'=>'Only JPG/PNG allowed']); exit;
    }

    if(move_uploaded_file($file['tmp_name'],$targetFile)){
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("INSERT INTO highlights (title,image,status) VALUES (NULL,?, 'pending')");
        $stmt->bind_param("s",$filename);
        if($stmt->execute()){
            // ✅ Send email notification (do not block API response)
            $toEmail = $_SESSION['email'] ?? null;
            $toName  = $_SESSION['username'] ?? 'there';

            if (!$toEmail) {
                $u = $conn->prepare("SELECT email, username FROM users WHERE id = ? LIMIT 1");
                $u->bind_param("i", $user_id);
                $u->execute();
                $ur = $u->get_result();
                if ($row = $ur->fetch_assoc()) {
                    $toEmail = $row['email'] ?? null;
                    $toName  = $row['username'] ?? $toName;
                }
            }

            if ($toEmail) {
                notify_highlight_submitted($toEmail, $toName, $filename);
            }

            echo json_encode(['status'=>'success','message'=>'Highlight submitted']);
        } else {
            echo json_encode(['status'=>'error','message'=>'DB insert failed']);
        }
    } else {
        echo json_encode(['status'=>'error','message'=>'File upload failed']);
    }
} else {
    echo json_encode(['status'=>'error','message'=>'No file uploaded']);
}
?>
