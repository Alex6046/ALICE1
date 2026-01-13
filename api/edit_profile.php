<?php
session_start();
include("db.php");

// PHPMailer
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'phpmailer/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// =======================
// CHECK IF USER IS LOGGED IN
// =======================
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection (already included above)



$user_id = $_SESSION['user_id'];

error_log("[v0] === New Page Load ===");
error_log("[v0] Request Method: " . $_SERVER['REQUEST_METHOD']);
error_log("[v0] POST data: " . print_r($_POST, true));
error_log("[v0] User ID from session: " . $user_id);

// Add PHP debug output that will show in browser console
echo "<script>console.log('[v0-PHP] Page load - Request method: " . $_SERVER['REQUEST_METHOD'] . "');</script>";
echo "<script>console.log('[v0-PHP] POST keys: " . implode(', ', array_keys($_POST)) . "');</script>";
echo "<script>console.log('[v0-PHP] isset update_profile: " . (isset($_POST['update_profile']) ? 'YES' : 'NO') . "');</script>";

$message = "";
$message_type = "success";
$user_id = $_SESSION['user_id'];

// =======================
// FETCH USER DATA
// =======================
// Users table (for password and email)
$sql_user = "SELECT email, password FROM users WHERE id=? LIMIT 1";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user = $stmt_user->get_result()->fetch_assoc();
$email = $user['email'];

// User profile table
$sql_profile = "SELECT * FROM user_profile WHERE user_id=? LIMIT 1";
$stmt_profile = $conn->prepare($sql_profile);
$stmt_profile->bind_param("i", $user_id);
$stmt_profile->execute();
$profile = $stmt_profile->get_result()->fetch_assoc();

// =======================
// HANDLE AVATAR UPLOAD
// =======================
if (isset($_POST['upload_avatar']) && isset($_FILES['avatar'])) {
    $target_dir = "uploads/avatars/";
    
    // Create directory if it doesn't exist
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION));
    $new_filename = "avatar_" . $user_id . "_" . time() . "." . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    // Check if image file is actual image
    $check = getimagesize($_FILES["avatar"]["tmp_name"]);
    if($check !== false) {
        // Check file size (5MB max)
        if ($_FILES["avatar"]["size"] <= 5000000) {
            // Allow certain file formats
            if($file_extension == "jpg" || $file_extension == "png" || $file_extension == "jpeg" || $file_extension == "gif") {
                if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {
                    // Update or insert avatar path in database
                    if ($profile) {
                        $sql = "UPDATE user_profile SET avatar=? WHERE user_id=?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("si", $target_file, $user_id);
                        $stmt->execute();
                    } else {
                        $sql = "INSERT INTO user_profile (user_id, avatar) VALUES (?,?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("is", $user_id, $target_file);
                        $stmt->execute();
                    }
                    $message = "Avatar uploaded successfully!";
                    $message_type = "success";
                    
                    // Refresh profile data
                    $stmt_profile->execute();
                    $profile = $stmt_profile->get_result()->fetch_assoc();
                } else {
                    $message = "Error uploading avatar. Please try again.";
                    $message_type = "error";
                }
            } else {
                $message = "Only JPG, JPEG, PNG & GIF files are allowed.";
                $message_type = "error";
            }
        } else {
            $message = "File is too large. Maximum size is 5MB.";
            $message_type = "error";
        }
    } else {
        $message = "File is not an image.";
        $message_type = "error";
    }
}

// =======================
// HANDLE PROFILE UPDATE
// =======================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    echo "<script>console.log('[v0-PHP] Profile update detected!');</script>";
    error_log("[v0] Profile update form received");
    
    $full_name = trim($_POST['full_name']);
    $matric = trim($_POST['matric_number']);
    $gender = $_POST['gender'];
    $year = $_POST['year_of_study'];
    $course = trim($_POST['course']);
    
    // Debug: Log received values
    echo "<script>console.log('[v0] Received values:', {
        full_name: '" . addslashes($full_name) . "',
        matric: '" . addslashes($matric) . "',
        gender: '" . addslashes($gender) . "',
        year: " . $year . ",
        course: '" . addslashes($course) . "',
        user_id: " . $user_id . "
    });</script>";
    
    if (empty($full_name) || empty($matric) || empty($gender) || empty($year) || empty($course)) {
        $message = "Please fill in all required fields.";
        $message_type = "error";
        echo "<script>console.log('[v0] Validation failed: Empty fields detected');</script>";
    } else {
        echo "<script>console.log('[v0] Validation passed');</script>";
        
        if ($profile) {
            // Update existing profile
            echo "<script>console.log('[v0] Updating existing profile with ID: " . $profile['id'] . "');</script>";
            
            $sql = "UPDATE user_profile SET full_name=?, matric_number=?, gender=?, year_of_study=?, course=? WHERE user_id=?";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                echo "<script>console.error('[v0] SQL Prepare Error: " . addslashes($conn->error) . "');</script>";
                $message = "Database error: " . $conn->error;
                $message_type = "error";
            } else {
                echo "<script>console.log('[v0] SQL prepared successfully');</script>";
                
                $stmt->bind_param("sssisi", $full_name, $matric, $gender, $year, $course, $user_id);
                
                echo "<script>console.log('[v0] Parameters bound, executing query...');</script>";
                
                if ($stmt->execute()) {
                    echo "<script>console.log('[v0] Query executed successfully. Rows affected: " . $stmt->affected_rows . "');</script>";
                    
                    $message = "Profile updated successfully!";
                    $message_type = "success";
                    
                    // Refresh profile data
                    $stmt_profile = $conn->prepare($sql_profile);
                    $stmt_profile->bind_param("i", $user_id);
                    $stmt_profile->execute();
                    $profile = $stmt_profile->get_result()->fetch_assoc();
                    
                    echo "<script>console.log('[v0] Profile data refreshed');</script>";
                } else {
                    echo "<script>console.error('[v0] Execute Error: " . addslashes($stmt->error) . "');</script>";
                    $message = "Error updating profile: " . $stmt->error;
                    $message_type = "error";
                }
            }
        } else {
            // Insert new profile
            echo "<script>console.log('[v0] Creating new profile for user_id: " . $user_id . "');</script>";
            
            $sql = "INSERT INTO user_profile (user_id, full_name, matric_number, gender, year_of_study, course) VALUES (?,?,?,?,?,?)";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                echo "<script>console.error('[v0] SQL Prepare Error: " . addslashes($conn->error) . "');</script>";
                $message = "Database error: " . $conn->error;
                $message_type = "error";
            } else {
                echo "<script>console.log('[v0] SQL prepared successfully');</script>";
                
                $stmt->bind_param("isssis", $user_id, $full_name, $matric, $gender, $year, $course);
                
                echo "<script>console.log('[v0] Parameters bound, executing insert...');</script>";
                
                if ($stmt->execute()) {
                    echo "<script>console.log('[v0] Insert successful. Insert ID: " . $stmt->insert_id . "');</script>";
                    
                    $message = "Profile created successfully!";
                    $message_type = "success";
                    
                    // Refresh profile data
                    $stmt_profile = $conn->prepare($sql_profile);
                    $stmt_profile->bind_param("i", $user_id);
                    $stmt_profile->execute();
                    $profile = $stmt_profile->get_result()->fetch_assoc();
                    
                    echo "<script>console.log('[v0] Profile data refreshed');</script>";
                } else {
                    echo "<script>console.error('[v0] Execute Error: " . addslashes($stmt->error) . "');</script>";
                    $message = "Error creating profile: " . $stmt->error;
                    $message_type = "error";
                }
            }
        }
    }
} else {
    echo "<script>console.log('[v0] No profile update form submission detected');</script>";
}

// =======================
// HANDLE PASSWORD CHANGE REQUEST
// =======================
if (isset($_POST['next'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];

    if (strlen($new_password) < 8) {
        $message = "New password must be at least 8 characters long.";
        $message_type = "error";
    } elseif ($old_password === $new_password) {
        $message = "New password must be different from current password.";
        $message_type = "error";
    } elseif (password_verify($old_password, $user['password'])) {
        // Generate verification code
        $code = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 6);

        // Store new password and verification code in session
        $_SESSION['new_password'] = password_hash($new_password, PASSWORD_DEFAULT);
        $_SESSION['verification_code'] = $code;

        // Send verification code via email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'azrifikriiskandar@gmail.com';
            $mail->Password   = 'ejmk soge zjuu rohi';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('azrifikriiskandar@gmail.com', 'ALICE Verification');
            $mail->addAddress($email);

            $mail->Subject = "Password Change Verification Code";
            $mail->Body    = "Your verification code is: $code\n\nThis code will expire in 15 minutes.";

            $mail->send();

            // Redirect to verification page
            header("Location: verify_password.php");
            exit();
        } catch (Exception $e) {
            $message = "Email could not be sent. Mailer Error: " . $mail->ErrorInfo;
            $message_type = "error";
        }
    } else {
        $message = "Current password is incorrect!";
        $message_type = "error";
    }
}

$avatar_path = $profile['avatar'] ?? 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 100\'%3E%3Ccircle cx=\'50\' cy=\'50\' r=\'50\' fill=\'%23238636\'/%3E%3Cpath d=\'M50 45c8.284 0 15-6.716 15-15s-6.716-15-15-15-15 6.716-15 15 6.716 15 15 15zm0 7.5c-10 0-30 5-30 15V75h60v-7.5c0-10-20-15-30-15z\' fill=\'%23ffffff\'/%3E%3C/svg%3E';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - ALICE</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: url('https://images.unsplash.com/photo-1446776811953-b23d57bd21aa?auto=format&fit=crop&w=1600&q=80') no-repeat center center fixed;
            background-size: cover;
            color: #ffffff;
            min-height: 100vh;
            position: relative;
        }

        /* Dark overlay for better readability */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(13, 17, 23, 0.85);
            z-index: -1;
        }

        /* Main container with grid layout */
        .profile-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Success/Error message styling */
        .message {
            background: linear-gradient(135deg, #238636, #2ea043);
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 4px 20px rgba(35, 134, 54, 0.3);
            animation: slideIn 0.4s ease-out;
        }

        .message.error {
            background: linear-gradient(135deg, #da3633, #f85149);
        }

        @keyframes slideIn {
            from {
                transform: translateX(-100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Profile header with avatar */
        .profile-header {
            background: rgba(22, 27, 34, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(48, 54, 61, 0.5);
            border-radius: 16px;
            padding: 40px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
        }

        .avatar-upload {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto 20px;
        }

        .avatar-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 4px solid #238636;
            object-fit: cover;
            transition: all 0.3s ease;
            box-shadow: 0 0 0 4px rgba(35, 134, 54, 0.2);
        }

        .avatar-upload:hover .avatar-preview {
            transform: scale(1.05);
            box-shadow: 0 0 0 6px rgba(35, 134, 54, 0.3);
        }

        .avatar-upload input[type="file"] {
            display: none;
        }

        .avatar-upload-label {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: #238636;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 3px solid #161b22;
        }

        .avatar-upload-label:hover {
            background: #2ea043;
            transform: scale(1.1);
        }

        .profile-header h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
            background: linear-gradient(135deg, #ffffff, #a8b5c4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .profile-header .email {
            color: #8b949e;
            font-size: 16px;
        }

        /* Grid layout for forms */
        .forms-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 30px;
        }

        @media (min-width: 768px) {
            .forms-grid {
                grid-template-columns: 2fr 1fr;
            }
        }

        /* Card styling for sections */
        .card {
            background: rgba(22, 27, 34, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(48, 54, 61, 0.5);
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.5);
            border-color: rgba(35, 134, 54, 0.3);
        }

        .card h2 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #ffffff;
        }

        .card h2::before {
            content: '';
            width: 4px;
            height: 24px;
            background: linear-gradient(180deg, #238636, #2ea043);
            border-radius: 2px;
        }

        /* Form styling with better inputs */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #c9d1d9;
            font-weight: 500;
            font-size: 14px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 14px 16px;
            background: rgba(13, 17, 23, 0.6);
            border: 1px solid rgba(48, 54, 61, 0.8);
            border-radius: 10px;
            color: #ffffff;
            font-size: 15px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #238636;
            background: rgba(13, 17, 23, 0.8);
            box-shadow: 0 0 0 3px rgba(35, 134, 54, 0.2);
        }

        .form-group input::placeholder,
        .form-group select::placeholder,
        .form-group textarea::placeholder {
            color: #6e7681;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        /* Button styling with animations */
        .btn {
            padding: 14px 28px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #238636, #2ea043);
            color: white;
            box-shadow: 0 4px 16px rgba(35, 134, 54, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(35, 134, 54, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-secondary {
            background: rgba(48, 54, 61, 0.8);
            color: #c9d1d9;
            border: 1px solid rgba(48, 54, 61, 0.8);
        }

        .btn-secondary:hover {
            background: rgba(48, 54, 61, 1);
            border-color: #6e7681;
        }

        .btn-danger {
            background: linear-gradient(135deg, #da3633, #f85149);
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(218, 54, 51, 0.4);
        }

        .button-group {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }

        .button-group .btn {
            flex: 1;
        }

        /* Back button */
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #8b949e;
            text-decoration: none;
            margin-bottom: 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            color: #c9d1d9;
            transform: translateX(-4px);
        }

        /* Icon styling */
        .icon {
            font-size: 20px;
        }

        /* Loading state */
        .loading {
            pointer-events: none;
            opacity: 0.7;
        }

        .loading::after {
            content: '...';
            animation: dots 1.5s infinite;
        }

        @keyframes dots {
            0%, 20% { content: '.'; }
            40% { content: '..'; }
            60%, 100% { content: '...'; }
        }

        /* Divider */
        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(48, 54, 61, 0.8), transparent);
            margin: 30px 0;
        }

        /* Info box */
        .info-box {
            background: rgba(35, 134, 54, 0.1);
            border: 1px solid rgba(35, 134, 54, 0.3);
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 20px;
            display: flex;
            gap: 12px;
            align-items: start;
        }

        .info-box .icon {
            color: #238636;
            flex-shrink: 0;
        }

        .info-box p {
            margin: 0;
            color: #c9d1d9;
            font-size: 14px;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <?php include('navbar.php'); ?>

    <div class="profile-container">
        <!-- Back button -->
        <a href="student_dashboard.php" class="back-btn">
            <span class="icon">←</span>
            Back to Dashboard
        </a>

        <!-- Success/Error messages -->
        <?php if(!empty($message)): ?>
            <div class="message <?php echo $message_type === 'error' ? 'error' : ''; ?>">
                <span class="icon"><?php echo $message_type === 'success' ? '✓' : '⚠'; ?></span>
                <span><?php echo htmlspecialchars($message); ?></span>
            </div>
        <?php endif; ?>

        <!-- Profile header with avatar -->
        <div class="profile-header">
            <form method="POST" enctype="multipart/form-data" id="avatarForm">
                <div class="avatar-upload">
                    <!-- Display user's actual avatar or placeholder -->
                    <img src="<?php echo htmlspecialchars($avatar_path); ?>" 
                         alt="Profile Avatar" 
                         class="avatar-preview" 
                         id="avatarPreview">
                    <label for="avatarInput" class="avatar-upload-label" title="Upload new avatar">
                        <span class="icon">📷</span>
                    </label>
                    <input type="file" 
                           id="avatarInput" 
                           name="avatar" 
                           accept="image/*"
                           onchange="uploadAvatar(this)">
                </div>
                <input type="hidden" name="upload_avatar" value="1">
            </form>
            <h1><?php echo htmlspecialchars($profile['full_name'] ?? 'Student'); ?></h1>
            <p class="email"><?php echo htmlspecialchars($email); ?></p>
        </div>

        <!-- Forms grid layout -->
        <div class="forms-grid">
            <!-- Profile Information Form -->
            <div class="card">
                <h2>Profile Information</h2>
                
                <div class="info-box">
                    <span class="icon">ℹ️</span>
                    <p>Keep your profile information up to date to ensure smooth event registrations and communications.</p>
                </div>

                <form method="POST" id="profileForm">
                    <!-- Hidden input to ensure update_profile is sent even if button is disabled by JS -->
                    <input type="hidden" name="update_profile" value="1">
                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text" 
                               id="full_name" 
                               name="full_name" 
                               placeholder="Enter your full name" 
                               value="<?= htmlspecialchars($profile['full_name'] ?? '') ?>"
                               required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="matric_number">Matric Number *</label>
                            <input type="text" 
                                   id="matric_number" 
                                   name="matric_number" 
                                   placeholder="e.g., U2005123" 
                                   value="<?= htmlspecialchars($profile['matric_number'] ?? '') ?>"
                                   required>
                        </div>

                        <div class="form-group">
                            <label for="gender">Gender *</label>
                            <select id="gender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="Male" <?= (isset($profile['gender']) && $profile['gender']=="Male")?"selected":"" ?>>Male</option>
                                <option value="Female" <?= (isset($profile['gender']) && $profile['gender']=="Female")?"selected":"" ?>>Female</option>
                                <option value="Other" <?= (isset($profile['gender']) && $profile['gender']=="Other")?"selected":"" ?>>Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="year_of_study">Year of Study *</label>
                            <input type="number" 
                                   id="year_of_study" 
                                   name="year_of_study" 
                                   placeholder="e.g., 3" 
                                   min="1" 
                                   max="7"
                                   value="<?= htmlspecialchars($profile['year_of_study'] ?? '') ?>"
                                   required>
                        </div>

                        <div class="form-group">
                            <label for="course">Course *</label>
                            <input type="text" 
                                   id="course" 
                                   name="course" 
                                   placeholder="e.g., Computer Science" 
                                   value="<?= htmlspecialchars($profile['course'] ?? '') ?>"
                                   required>
                        </div>
                    </div>

                    <div class="button-group">
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <span class="icon">💾</span>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>

            <!-- Password Change Form -->
            <div class="card">
                <h2>Security</h2>

                <div class="info-box">
                    <span class="icon">🔒</span>
                    <p>A verification code will be sent to your email to confirm the password change.</p>
                </div>

                <form method="POST" id="passwordForm">
                    <!-- Hidden input to ensure 'next' is sent even if button is disabled by JS -->
                    <input type="hidden" name="next" value="1">
                    <div class="form-group">
                        <label for="old_password">Current Password *</label>
                        <input type="password" 
                               id="old_password" 
                               name="old_password" 
                               placeholder="Enter current password"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password *</label>
                        <input type="password" 
                               id="new_password" 
                               name="new_password" 
                               placeholder="Enter new password (min. 8 characters)"
                               minlength="8"
                               required>
                        <!-- Added password strength indicator -->
                        <div id="passwordStrength" style="margin-top: 8px; font-size: 12px; color: #6e7681;"></div>
                    </div>

                    <div class="button-group">
                        <button type="submit" name="next" class="btn btn-danger">
                            <span class="icon">🔑</span>
                            Change Password
                        </button>
                    </div>
                </form>

                <div class="divider"></div>

                <div class="button-group">
                    <a href="student_dashboard.php" class="btn btn-secondary">
                        <span class="icon">←</span>
                        Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        console.log('[v0] Profile page loaded');
        console.log('[v0] Current user ID:', <?php echo $user_id; ?>);
        
        // Debug profile form submission
        const profileForm = document.getElementById('profileForm');
        profileForm.addEventListener('submit', function(e) {
            console.log('[v0] Profile form submit event triggered');
            
            // Get form data
            const formData = new FormData(this);
            console.log('[v0] Form data being submitted:');
            for (let [key, value] of formData.entries()) {
                console.log(`  ${key}: ${value}`);
            }
            
            // Check if update_profile button exists
            const submitButton = this.querySelector('button[name="update_profile"]');
            console.log('[v0] Submit button found:', submitButton !== null);
            
            const btn = this.querySelector('button[type="submit"]');
            btn.classList.add('loading');
            btn.disabled = true;
            btn.innerHTML = '<span class="icon">⏳</span> Saving...';
        });

        // Debug password form submission
        const passwordForm = document.getElementById('passwordForm');
        passwordForm.addEventListener('submit', function(e) {
            console.log('[v0] Password form submit event triggered');
            
            const formData = new FormData(this);
            console.log('[v0] Password form data:');
            for (let [key, value] of formData.entries()) {
                console.log(`  ${key}: ${value === '' ? '(empty)' : '***'}`);
            }
            
            const btn = this.querySelector('button[type="submit"]');
            const oldPass = document.getElementById('old_password').value;
            const newPass = document.getElementById('new_password').value;
            
            if (oldPass === newPass) {
                e.preventDefault();
                alert('New password must be different from current password.');
                return;
            }
            
            btn.classList.add('loading');
            btn.disabled = true;
            btn.innerHTML = '<span class="icon">⏳</span> Processing...';
        });

        function uploadAvatar(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                
                // Validate file size (5MB)
                if (file.size > 5000000) {
                    alert('File is too large. Maximum size is 5MB.');
                    input.value = '';
                    return;
                }
                
                // Validate file type
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!validTypes.includes(file.type)) {
                    alert('Only JPG, JPEG, PNG & GIF files are allowed.');
                    input.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatarPreview').src = e.target.result;
                };
                reader.readAsDataURL(file);
                
                // Auto-submit form
                setTimeout(() => {
                    document.getElementById('avatarForm').submit();
                }, 500);
            }
        }

        const newPasswordInput = document.getElementById('new_password');
        const strengthDiv = document.getElementById('passwordStrength');
        
        if (newPasswordInput && strengthDiv) {
            newPasswordInput.addEventListener('input', function() {
                const value = this.value;
                const hasLength = value.length >= 8;
                const hasNumber = /\d/.test(value);
                const hasLower = /[a-z]/.test(value);
                const hasUpper = /[A-Z]/.test(value);
                const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(value);
                
                let strength = 0;
                let feedback = [];
                
                if (hasLength) strength++;
                if (hasNumber) strength++;
                if (hasLower && hasUpper) strength++;
                if (hasSpecial) strength++;
                
                if (value.length === 0) {
                    strengthDiv.innerHTML = '';
                    return;
                }
                
                if (strength <= 2) {
                    strengthDiv.innerHTML = '<span style="color: #f85149;">⚠ Weak password</span>';
                } else if (strength === 3) {
                    strengthDiv.innerHTML = '<span style="color: #f0883e;">⚡ Medium strength</span>';
                } else {
                    strengthDiv.innerHTML = '<span style="color: #238636;">✓ Strong password</span>';
                }
            });
        }

        const messageDiv = document.querySelector('.message');
        if (messageDiv && !messageDiv.classList.contains('error')) {
            setTimeout(() => {
                messageDiv.style.transition = 'opacity 0.5s ease';
                messageDiv.style.opacity = '0';
                setTimeout(() => messageDiv.remove(), 500);
            }, 5000);
        }
    </script>
</body>
</html>
