<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['updateProfile'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    
    $updateQuery = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
    $updateQuery->bind_param("ssi", $username, $email, $user_id);
    
    if ($updateQuery->execute()) {
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        $success_message = "Profile updated successfully!";
        // Refresh user data
        $userQuery = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $userQuery->bind_param("i", $user_id);
        $userQuery->execute();
        $userData = $userQuery->get_result()->fetch_assoc();
    } else {
        $error_message = "Failed to update profile.";
    }
} else {
    $userQuery = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $userQuery->bind_param("i", $user_id);
    $userQuery->execute();
    $userData = $userQuery->get_result()->fetch_assoc();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['avatar'])) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 2 * 1024 * 1024;
    
    if (in_array($_FILES['avatar']['type'], $allowed_types)) {
        if ($_FILES['avatar']['size'] <= $max_size) {
            $upload_dir = '../uploads/avatars/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $new_filename = 'avatar_' . $user_id . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_path)) {
                $avatar_path = 'uploads/avatars/' . $new_filename;
                $updateAvatarQuery = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                $updateAvatarQuery->bind_param("si", $avatar_path, $user_id);
                
                if ($updateAvatarQuery->execute()) {
                    $success_message = "Avatar updated successfully!";
                    $userData['avatar'] = $avatar_path;
                } else {
                    $error_message = "Failed to save avatar.";
                }
            } else {
                $error_message = "Failed to upload avatar.";
            }
        } else {
            $error_message = "Avatar file size must be less than 2MB.";
        }
    } else {
        $error_message = "Invalid file type. Only JPEG, PNG, and GIF are allowed.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Management - ALICE Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?php include 'admin_styles.php'; ?>
    <style>
        /* Enhanced styling for better typography and spacing */
        .profile-grid {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 35px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .profile-card {
            background: rgba(22, 27, 34, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(124, 58, 237, 0.2);
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }

        .profile-card:hover {
            border-color: rgba(124, 58, 237, 0.4);
            box-shadow: 0 12px 40px rgba(124, 58, 237, 0.15);
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            margin-bottom: 10px;
            color: #e6edf3;
            font-weight: 600;
            font-size: 0.95rem;
            letter-spacing: 0.3px;
        }

        .form-input {
            width: 100%;
            padding: 14px 18px;
            background: rgba(13, 17, 23, 0.9);
            border: 1.5px solid #30363d;
            border-radius: 10px;
            color: white;
            font-size: 1rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: #7c3aed;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.15);
        }

        .form-input::placeholder {
            color: #6e7681;
        }

        .avatar-circle {
            width: 180px;
            height: 180px;
            margin: 0 auto 28px;
            border-radius: 50%;
            background: linear-gradient(135deg, #7c3aed, #5b21b6);
            color: white;
            font-size: 5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 5px solid rgba(124, 58, 237, 0.3);
            box-shadow: 0 12px 30px rgba(124, 58, 237, 0.4);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .avatar-circle:hover {
            transform: scale(1.05);
            box-shadow: 0 16px 40px rgba(124, 58, 237, 0.5);
        }

        .avatar-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .btn-primary {
            padding: 13px 28px;
            background: linear-gradient(135deg, #7c3aed, #6d28d9);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
            letter-spacing: 0.3px;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #6d28d9, #5b21b6);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(124, 58, 237, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #b91c1c, #991b1b);
        }

        .info-grid {
            display: flex;
            flex-direction: column;
            gap: 16px;
            padding: 24px;
            background: rgba(13, 17, 23, 0.5);
            border-radius: 12px;
            border: 1px solid #30363d;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid rgba(48, 54, 61, 0.5);
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #94a3b8;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .info-value {
            color: #e6edf3;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .badge {
            background: linear-gradient(135deg, #7c3aed, #6d28d9);
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .alert {
            padding: 18px 24px;
            border-radius: 12px;
            margin-bottom: 28px;
            font-weight: 600;
            font-size: 0.95rem;
            animation: slideDown 0.4s ease;
            letter-spacing: 0.2px;
        }

        .alert-success {
            background: rgba(35, 134, 54, 0.15);
            border: 1.5px solid #238636;
            color: #3fb950;
        }

        .alert-error {
            background: rgba(248, 81, 73, 0.15);
            border: 1.5px solid #f85149;
            color: #ff7b72;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .section-title {
            margin: 0 0 24px 0;
            color: #e6edf3;
            font-size: 1.4rem;
            font-weight: 700;
            letter-spacing: -0.3px;
        }

        .help-text {
            color: #6e7681;
            font-size: 0.85rem;
            margin-top: 8px;
            line-height: 1.5;
        }

        @media (max-width: 968px) {
            .profile-grid {
                grid-template-columns: 1fr;
                gap: 25px;
            }
        }
    </style>
</head>
<body>
    <?php include 'admin_navbar.php'; ?>

    <div class="admin-content">
        <div class="content-wrapper">
            <div class="page-header">
                <h1>Profile Management</h1>
                <p>Update your account details and preferences</p>
            </div>

            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    ✓ <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    ✗ <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <div class="profile-grid">
                <!-- Avatar Section -->
                <div class="profile-card">
                    <h2 class="section-title">Profile Avatar</h2>
                    
                    <div style="text-align: center;">
                        <div class="avatar-circle">
                            <?php if (!empty($userData['avatar']) && file_exists('../' . $userData['avatar'])): ?>
                                <img src="../<?php echo htmlspecialchars($userData['avatar']); ?>" alt="Avatar">
                            <?php else: ?>
                                <?php echo strtoupper(substr($userData['username'], 0, 1)); ?>
                            <?php endif; ?>
                        </div>

                        <form method="POST" enctype="multipart/form-data">
                            <input type="file" name="avatar" id="avatarInput" accept="image/*" style="display: none;" onchange="this.form.submit()">
                            <label for="avatarInput" class="btn-primary" style="display: inline-block; cursor: pointer; text-align: center;">
                                Upload New Avatar
                            </label>
                            <p class="help-text" style="margin-top: 12px;">
                                Max size: 2MB<br>
                                Formats: JPEG, PNG, GIF
                            </p>
                        </form>
                    </div>

                    <div style="margin-top: 35px; padding-top: 30px; border-top: 1px solid #30363d;">
                        <h3 style="color: #e6edf3; font-size: 1.1rem; margin-bottom: 20px; font-weight: 600;">Account Info</h3>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">User ID</span>
                                <span class="info-value">#<?php echo $userData['id']; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Role</span>
                                <span class="badge"><?php echo ucfirst($userData['role']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Member Since</span>
                                <span class="info-value"><?php echo date("M d, Y", strtotime($userData['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Form Section -->
                <div style="display: flex; flex-direction: column; gap: 28px;">
                    <!-- Removed full name and phone number fields, only email and username -->
                    <div class="profile-card">
                        <h2 class="section-title">Basic Information</h2>
                        
                        <form method="POST">
                            <div class="form-group">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" value="<?php echo htmlspecialchars($userData['username']); ?>" required class="form-input">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($userData['email']); ?>" required class="form-input">
                            </div>

                            <button type="submit" name="updateProfile" class="btn-primary">
                                Save Changes
                            </button>
                        </form>
                    </div>

                    <!-- Change Password -->
                    <div class="profile-card">
                        <h2 class="section-title">Change Password</h2>
                        
                        <form method="POST">
                            <div class="form-group">
                                <label class="form-label">Current Password</label>
                                <input type="password" name="current_password" required class="form-input">
                            </div>

                            <div class="form-group">
                                <label class="form-label">New Password</label>
                                <input type="password" name="new_password" required minlength="6" class="form-input">
                                <small class="help-text">Minimum 6 characters</small>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="confirm_password" required minlength="6" class="form-input">
                            </div>

                            <button type="submit" name="changePassword" class="btn-primary btn-danger">
                                Change Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
