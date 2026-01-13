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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Proposals - ALICE Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?php include 'admin_styles.php'; ?>
</head>
<body>

<?php include 'admin_navbar.php'; ?>

<div class="admin-content">
    <div class="container">
        <div class="page-header">
            <h1>Event Proposals</h1>
            <p>Review and approve pending event submissions</p>
        </div>

        <div class="section">
            <div class="section-header">
                <h2>Pending Proposals</h2>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Event Title</th>
                        <th>Date & Time</th>
                        <th>Venue</th>
                        <th>Capacity</th>
                        <th>Organizer</th>
                        <th>Contact</th>
                        <th>Submitted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $proposedQuery = mysqli_query($conn, "SELECT * FROM proposed_events WHERE status = 'pending' ORDER BY submitted_at DESC");
                    if ($proposedQuery && mysqli_num_rows($proposedQuery) > 0) {
                        while ($proposal = mysqli_fetch_assoc($proposedQuery)) {
                            echo "<tr id='proposal-{$proposal['id']}'>";
                            echo "<td><strong>" . htmlspecialchars($proposal['title']) . "</strong><br><small style='color:#94a3b8;'>" . htmlspecialchars($proposal['description']) . "</small></td>";
                            echo "<td>" . htmlspecialchars($proposal['date']) . "<br>" . htmlspecialchars($proposal['time']) . "</td>";
                            echo "<td>" . htmlspecialchars($proposal['venue']) . "</td>";
                            echo "<td>" . htmlspecialchars($proposal['capacity']) . "</td>";
                            echo "<td>" . htmlspecialchars($proposal['organizer_name']) . "<br>" . htmlspecialchars($proposal['organizer_email']) . "</td>";
                            echo "<td>" . htmlspecialchars($proposal['contact_number']) . "</td>";
                            echo "<td>" . date('M d, Y', strtotime($proposal['submitted_at'])) . "</td>";
                            echo "<td>";
                            echo "<button class='btn-purple' onclick='approveProposal({$proposal['id']})'>Approve</button> ";
                            echo "<button class='btn-black' onclick='rejectProposal({$proposal['id']})'>Reject</button>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8' style='text-align:center;color:#94a3b8;'>No pending event proposals.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="toastMessage" class="toast"></div>

<script>
    function showToast(message, success=true) {
        const toast = document.getElementById("toastMessage");
        toast.textContent = message;
        toast.style.background = success ? "#38a169" : "#e53e3e";
        toast.classList.add("show");
        setTimeout(() => toast.classList.remove("show"), 3000);
    }

    async function approveProposal(id) {
        if (!confirm("Approve this event proposal? It will be added to the main events list.")) return;
        
        let res = await fetch("../manage_proposed_events.php", {
            method: "POST",
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: "action=approve&id=" + id
        });
        
        let data = await res.json();
        showToast(data.message, data.status === "success");
        if (data.status === "success") {
            document.getElementById("proposal-" + id).remove();
        }
    }

    async function rejectProposal(id) {
        let reason = prompt("Please provide a reason for rejection (optional):");
        
        let res = await fetch("../manage_proposed_events.php", {
            method: "POST",
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: "action=reject&id=" + id + "&reason=" + encodeURIComponent(reason || '')
        });
        
        let data = await res.json();
        showToast(data.message, data.status === "success");
        if (data.status === "success") {
            document.getElementById("proposal-" + id).remove();
        }
    }
</script>

</body>
</html>
