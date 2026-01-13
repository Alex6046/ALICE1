<?php
session_start();
include("../db.php");


// Role check
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$roleQuery = $conn->prepare("SELECT role, username, email FROM users WHERE id = ?");
$roleQuery->bind_param("i", $user_id);
$roleQuery->execute();
$roleResult = $roleQuery->get_result();
$userData = $roleResult->fetch_assoc();

if (!$userData || $userData['role'] !== 'admin') {
    header("Location: ../home.php");
    exit;
}

$_SESSION['username'] = $userData['username'];
$_SESSION['email'] = $userData['email'];

// Dashboard statistics
$registered_members = 0;
$upcoming_events = 0;
$pending_approvals = 0;
$active_highlights = 0;

$mQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users");
$mRow = mysqli_fetch_assoc($mQuery);
$registered_members = $mRow['total'];

$eQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM events");
$eRow = mysqli_fetch_assoc($eQuery);
$upcoming_events = $eRow['total'];

$pQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM proposed_events WHERE status = 'pending'");
$pRow = mysqli_fetch_assoc($pQuery);
$pending_approvals = $pRow['total'];

$hQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM highlights WHERE status='approved'");
$hRow = mysqli_fetch_assoc($hQuery);
$active_highlights = $hRow['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ALICE</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?php include 'admin_styles.php'; ?>
</head>
<body>

<?php include 'admin_navbar.php'; ?>

<div class="admin-content">
    <div class="container">
        <div class="page-header">
            <h1>Dashboard Overview</h1>
            <p>Welcome back, <?php echo htmlspecialchars($userData['username']); ?>! Here's what's happening with ALICE today.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h4>Total Events</h4>
                <h1><?= $upcoming_events ?></h1>
            </div>
            <div class="stat-card">
                <h4>Registered Members</h4>
                <h1><?= $registered_members ?></h1>
            </div>
            <div class="stat-card">
                <h4>Pending Proposals</h4>
                <h1><?= $pending_approvals ?></h1>
            </div>
            <div class="stat-card">
                <h4>Active Highlights</h4>
                <h1><?= $active_highlights ?></h1>
            </div>
        </div>

        <div class="section">
            <div class="section-header">
                <h2>Quick Actions</h2>
            </div>
            <div class="button-group">
                <button class="btn-purple" onclick="window.location.href='events.php'">Manage Events</button>
                <button class="btn-purple" onclick="window.location.href='members.php'">View Members</button>
                <button class="btn-purple" onclick="window.location.href='highlights.php'">Manage Highlights</button>
                <button class="btn-purple" onclick="window.location.href='proposals.php'">Review Proposals</button>
            </div>
        </div>

        <div class="section">
            <div class="section-header">
                <h2>Recent Activity</h2>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Activity</th>
                        <th>Type</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Get recent highlights
                    $recentQuery = mysqli_query($conn, "
                        SELECT 'Highlight' as type, title, created_at FROM highlights 
                        WHERE status='pending' 
                        UNION 
                        SELECT 'Event Proposal' as type, title, submitted_at as created_at FROM proposed_events 
                        WHERE status='pending'
                        ORDER BY created_at DESC LIMIT 10
                    ");
                    
                    if ($recentQuery && mysqli_num_rows($recentQuery) > 0) {
                        while ($activity = mysqli_fetch_assoc($recentQuery)) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($activity['title']) . "</td>";
                            echo "<td>" . htmlspecialchars($activity['type']) . "</td>";
                            echo "<td>" . date('M d, Y', strtotime($activity['created_at'])) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3' style='text-align:center;color:#94a3b8;'>No recent activity</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
