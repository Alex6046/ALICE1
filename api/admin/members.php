<?php
session_start();
include("../db.php");


// Role check
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$roleQuery = $conn->prepare("SELECT role FROM users WHERE id = ?");
$roleQuery->bind_param("i", $user_id);
$roleQuery->execute();
$roleResult = $roleQuery->get_result();
$userData = $roleResult->fetch_assoc();

if (!$userData || $userData['role'] !== 'admin') {
    header("Location: ../home.php");
    exit;
}

$search = $_GET['search'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Members - ALICE Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?php include 'admin_styles.php'; ?>
</head>
<body>

<?php include 'admin_navbar.php'; ?>

<div class="admin-content">
    <div class="container">
        <div class="page-header">
            <h1>Member Profiles</h1>
            <p>Complete list of all users registered in ALICE</p>
        </div>

        <div class="section">
            <div class="section-header">
                <h2>All Members</h2>
            </div>

            <form method="get" style="margin-bottom:20px;">
                <input type="text" name="search" class="search-bar" placeholder="Search members by name, matric, course, email..." value="<?php echo htmlspecialchars($search); ?>">
            </form>

            <table>
                <thead>
                    <tr>
                        <th>Full Name</th>
                        <th>Matric Number</th>
                        <th>Gender</th>
                        <th>Year of Study</th>
                        <th>Course</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $profileSql = "
                        SELECT 
                            user_profile.full_name,
                            user_profile.matric_number,
                            user_profile.gender,
                            user_profile.year_of_study,
                            user_profile.course,
                            users.email
                        FROM user_profile
                        INNER JOIN users ON user_profile.user_id = users.id
                    ";

                    if ($search !== '') {
                        $safeSearch = '%' . $conn->real_escape_string($search) . '%';
                        $profileSql .= "
                            WHERE user_profile.full_name LIKE '$safeSearch'
                               OR user_profile.matric_number LIKE '$safeSearch'
                               OR user_profile.gender LIKE '$safeSearch'
                               OR user_profile.year_of_study LIKE '$safeSearch'
                               OR user_profile.course LIKE '$safeSearch'
                               OR users.email LIKE '$safeSearch'
                        ";
                    }

                    $profileSql .= " ORDER BY user_profile.full_name ASC";

                    $profileQuery = $conn->query($profileSql);
                    
                    if ($profileQuery && $profileQuery->num_rows > 0) {
                        while ($row = $profileQuery->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['matric_number']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['gender']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['year_of_study']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['course']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' style='padding:10px; text-align:center;color:#94a3b8;'>No user profiles found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
