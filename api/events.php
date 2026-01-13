<?php
session_start();
include("../db.php");

// Profile update handling
if(isset($_POST['saveProfile'])) {
    $name = $_POST['username'];
    $email = $_POST['email'];
    $id = $_SESSION['user_id'];
    mysqli_query($conn,"UPDATE users SET username='$name', email='$email' WHERE id='$id'");
    echo "<script>alert('Profile Updated'); window.location='events.php';</script>";
}

// Enable PHP error echoing
ini_set('display_errors', 1);
error_reporting(E_ALL);

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

// Search functionality
$search = $_GET['search'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events - ALICE Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?php include 'admin_styles.php'; ?>
</head>
<body>

<?php include 'admin_navbar.php'; ?>

<div class="admin-content">
    <div class="container">
        <div class="page-header">
            <h1>Manage Events</h1>
            <p>Create, update, and manage all ALICE events</p>
        </div>

        <div class="section">
            <div class="section-header">
                <h2>Events</h2>
                <div class="button-group">
                    <button class="btn-purple" onclick="openCreateModal()">+ Create Event</button>
                    <button class="btn-black">Export</button>
                </div>
            </div>

            <form method="get" style="margin-bottom:20px;">
                <input type="text" name="search" class="search-bar" placeholder="Search events by title, venue, date..." value="<?php echo htmlspecialchars($search); ?>">
            </form>

            <table>
                <thead>
                    <tr>
                        <th>Event</th><th>Date</th><th>Time</th><th>Venue</th><th>Description</th><th>Status</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody id="eventTable">
                    <?php
                    $today = date('Y-m-d');
                    $eventsSql = "SELECT id, title, date, time, venue, description, status FROM events";
                    
                    if ($search !== '') {
                        $safeSearch = '%' . $conn->real_escape_string($search) . '%';
                        $eventsSql .= " WHERE title LIKE '$safeSearch' OR venue LIKE '$safeSearch' OR description LIKE '$safeSearch' OR status LIKE '$safeSearch' OR DATE_FORMAT(date, '%Y-%m-%d') LIKE '$safeSearch' OR DATE_FORMAT(date, '%b %d') LIKE '$safeSearch'";
                    }
                    $eventsSql .= " ORDER BY date ASC";
                    
                    $result = mysqli_query($conn, $eventsSql);
                    
                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($event = mysqli_fetch_assoc($result)) {
                            // Determine if event is past based on date comparison
                            $eventDate = $event['date'];
                            $isPast = $eventDate < $today;
                            $displayStatus = $isPast ? 'Past' : $event['status'];
                            $statusColor = $isPast ? '#6e7681' : ($event['status'] === 'Upcoming' ? '#238636' : '#7c3aed');
                            ?>
                            <tr id="event-<?php echo $event['id']; ?>">
                                <td><?= htmlspecialchars($event['title']); ?></td>
                                <td><?= htmlspecialchars(date("M d, Y", strtotime($event['date']))); ?></td>
                                <td><?= htmlspecialchars(!empty($event['time']) ? date("g:i A", strtotime($event['time'])) : '-'); ?></td>
                                <td><?= htmlspecialchars($event['venue']); ?></td>
                                <td><?= htmlspecialchars($event['description']); ?></td>
                                <td><span style="background: <?= $statusColor ?>; color: white; padding: 5px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600;"><?= $displayStatus; ?></span></td>
                                <td>
                                    <button class="btn-purple" onclick="openUpdateModal(<?= $event['id']; ?>,'<?= htmlspecialchars($event['title']); ?>','<?= $event['date']; ?>','<?= $event['time']; ?>','<?= htmlspecialchars($event['venue']); ?>','<?= htmlspecialchars($event['description']); ?>','<?= $event['status']; ?>')">Update</button>
                                    <button class="btn-black" onclick="deleteEvent(<?= $event['id']; ?>)">Delete</button>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo "<tr><td colspan='7' style='color:#94a3b8;text-align:center;'>No events found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- CREATE EVENT MODAL -->
<div id="createModal" class="modal">
    <div class="modal-content">
        <h3>Create Event</h3>
        <input id="cName" placeholder="Event Name">
        <input id="cDate" type="date">
        <input id="cTime" type="time">
        <input id="cVenue" placeholder="Venue">
        <textarea id="cDescription" placeholder="Description"></textarea>
        <select id="cStatus">
            <option value="Upcoming">Upcoming</option>
            <option value="Current">Current</option>
            <option value="Passed">Passed</option>
        </select>
        <button class="btn-purple" onclick="handleCreateEvent()">Create</button>
        <button class="btn-black" onclick="closeCreateModal()">Cancel</button>
    </div>
</div>

<!-- UPDATE EVENT MODAL -->
<div id="updateEventModal" class="modal">
    <div class="modal-content">
        <h3>Update Event</h3>
        <input type="hidden" id="update_id">
        <input type="text" id="update_name" placeholder="Event Name">
        <input type="date" id="update_date">
        <input type="time" id="update_time">
        <input type="text" id="update_venue" placeholder="Venue">
        <textarea id="update_description" placeholder="Description"></textarea>
        <select id="update_status">
            <option value="Upcoming">Upcoming</option>
            <option value="Current">Current</option>
            <option value="Passed">Passed</option>
        </select>
        <button class="btn-purple" onclick="handleUpdateEvent()">Update Event</button>
        <button class="btn-black" onclick="closeUpdateModal()">Cancel</button>
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

    function openCreateModal(){ document.getElementById("createModal").style.display="flex"; }
    function closeCreateModal(){ document.getElementById("createModal").style.display="none"; }

    async function handleCreateEvent(){
        let form = new FormData();
        form.append('name', document.getElementById("cName").value);
        form.append('date', document.getElementById("cDate").value);
        form.append('time', document.getElementById("cTime").value);
        form.append('venue', document.getElementById("cVenue").value);
        form.append('description', document.getElementById("cDescription").value);
        form.append('status', document.getElementById("cStatus").value);

        let res = await fetch("../event_process.php", { method: "POST", body: form });
        let data = await res.json();

        showToast(data.message, data.status === "success");
        if (data.status === "success") location.reload();
    }

    function openUpdateModal(id,name,date,time,venue,description,status){
        document.getElementById("update_id").value=id;
        document.getElementById("update_name").value=name;
        document.getElementById("update_date").value=date;
        document.getElementById("update_time").value = time || "";
        document.getElementById("update_venue").value=venue;
        document.getElementById("update_description").value=description;
        document.getElementById("update_status").value=status;
        document.getElementById("updateEventModal").style.display="flex";
    }

    function closeUpdateModal(){ document.getElementById("updateEventModal").style.display="none"; }

    async function handleUpdateEvent(){
        let form = new FormData();
        form.append("update", "1");
        form.append("id", document.getElementById("update_id").value);
        form.append("name", document.getElementById("update_name").value);
        form.append("date", document.getElementById("update_date").value);
        form.append("time", document.getElementById("update_time").value);
        form.append("venue", document.getElementById("update_venue").value);
        form.append("description", document.getElementById("update_description").value);
        form.append("status", document.getElementById("update_status").value);

        let res = await fetch("../event_process.php", { method:"POST", body:form });
        let data = await res.json();

        showToast(data.message, data.status === "success");
        if (data.status === "success") location.reload();
    }

    async function deleteEvent(id){
        if(!confirm("Delete this event?")) return;
        let form = new FormData();
        form.append("action","delete");
        form.append("id",id);

        let res = await fetch("../event_process.php", { method:"POST", body:form });
        let data = await res.json();

        showToast(data.message, data.status === "success");
        if(data.status === "success") document.getElementById("event-"+id).remove();
    }
</script>

</body>
</html>
