<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Redirect to new admin folder structure
header("Location: admin/dashboard.php");
exit;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family:'Poppins',sans-serif;background:#0a0f1c;color:#fff;margin:0;padding:20px;}
        .top-bar { display:flex;justify-content:space-between;align-items:center;background:#0f172a;padding:12px 24px;border-radius:12px;margin-bottom:30px;}
        .top-bar input { width:60%;padding:10px;border-radius:10px;border:none;background:#1e293b;color:#fff;}
        .admin-info { display:flex;align-items:center;gap:10px;}
        .profile-container {position: relative;display: inline-block;}
        .avatar {width: 50px;height: 50px;border-radius: 50%;background: #007bff;color: #fff;font-weight: bold;font-size: 22px;display: flex;align-items: center;justify-content: center;border: 3px solid #0056b3; /* modern border */cursor: pointer;user-select: none;transition: transform 0.2s, box-shadow 0.2s;}
        .avatar img {width: 45px;height: 45px;border-radius: 50%;cursor: pointer;border: 2px solid #ddd;object-fit: cover;transition: 0.3s;}
        .avatar:hover {transform: scale(1.1);box-shadow: 0 4px 12px rgba(0,0,0,0.2);}
        .dropdown-menu {display: none;position: absolute;top: 60px;right: 0;width: 220px;background: #fff;padding: 15px;border-radius: 10px;box-shadow: 0 4px 12px rgba(0,0,0,0.15);z-index: 20;color: #222;}
        .profile-form {display: flex;flex-direction: column;gap: 8px;}
        .profile-form label {font-size: 14px;font-weight: 600;color:#222;}
        .profile-form input {padding: 8px;border: 1px solid #aaa;border-radius: 6px;}
        .save-btn {margin-top: 8px;padding: 8px;background: #007bff;border: none;color: white;border-radius: 6px;cursor: pointer;}
        .save-btn:hover {background: #0056b3;}
        .logout-btn {display: block;margin-top: 10px;padding: 10px;background: #dc3545;color: white;text-align: center;border-radius: 6px;text-decoration: none;}
        .logout-btn:hover {background: #c82333;}
        .stats { display:flex;gap:20px;margin-bottom:40px;}
        .card { background:#0f172a;flex:1;padding:20px;border-radius:16px;box-shadow:0 2px 8px rgba(0,0,0,0.3);}
        .card h4 { font-size:1rem;color:#94a3b8;}
        .card h1 { font-size:2rem;margin:5px 0;}
        .section { background:#0f172a;padding:20px;border-radius:16px;margin-bottom:20px;}
        .section-header { display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;}
        .btn-purple { background:#7c3aed;color:white;border:none;border-radius:8px;padding:8px 14px;cursor:pointer;}
        .btn-black { background:#1e293b;color:white;border:none;border-radius:8px;padding:8px 14px;cursor:pointer;}
        table { width:100%;border-collapse:collapse;color:#fff;}
        thead { background:#1e293b;}
        th,td { text-align:left;padding:10px;}
        tbody tr:nth-child(even) { background:#111827;}
        .modal { position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.7);display:none;justify-content:center;align-items:center;z-index:1000;}
        .modal-content { background:#0f172a;padding:20px;border-radius:12px;width:350px;}
        .modal input,.modal textarea,.modal select { width:100%;padding:10px;margin:8px 0;border-radius:8px;border:none;background:#1e293b;color:#fff;}
        .toast { visibility:hidden;min-width:250px;background:#38a169;color:#fff;text-align:center;border-radius:8px;padding:16px;position:fixed;left:50%;bottom:30px;font-size:17px;transform:translateX(-50%);opacity:0;transition:0.5s;}
        .toast.show { visibility:visible;opacity:1;}
        .profile-container {position: relative;display: inline-block;}
    </style>
</head>

<body>

<!-- TOP BAR -->
<div class="top-bar">
    <form method="get" action="admin.php" style="width:60%;">
        <input
            type="text"
            name="search"
            placeholder="Search events, members, IDs..."
            value="<?php echo htmlspecialchars($search); ?>"
            style="width:100%;padding:10px;border-radius:10px;border:none;background:#1e293b;color:#fff;"
        >
    </form>
    <div class="admin-info">
        <div class="profile-container">

        <!-- Avatar -->
        <div class="profile-container">

        <!-- Avatar with First Letter of Username -->
        <div class="avatar" onclick="toggleMenu();">
            <?php 
                // Use first letter of username
                $initial = isset($admin_name) && !empty($admin_name) ? strtoupper($admin_name[0]) : 'A';
                echo $initial;
            ?>
        </div>

        <!-- Dropdown Menu -->
        <div id="profileMenu" class="dropdown-menu">

        <form method="POST" class="profile-form" enctype="multipart/form-data">
            <!-- Avatar Upload -->
            <label>Change Avatar:</label>
            <input type="file" name="avatar" accept="image/*">

            <!-- Username -->
            <label>Username</label>
            <input type="text" name="username" 
                   value="<?php echo isset($admin_name) ? htmlspecialchars($admin_name) : ''; ?>" required>

            <!-- Email -->
            <label>Email</label>
            <input type="email" name="email" 
                   value="<?php echo isset($admin_email) ? htmlspecialchars($admin_email) : ''; ?>" required>

            <button type="submit" name="saveProfile" class="save-btn">Save</button>
        </form>

        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
    </div>
    <script>
    function toggleMenu() {
        const menu = document.getElementById("profileMenu");
        menu.style.display = (menu.style.display === "block") ? "none" : "block";
    }

    // Close menu when clicking outside
    document.addEventListener("click", function(e) {
        const menu = document.getElementById("profileMenu");
        const avatar = document.querySelector(".avatar");

        if (!avatar.contains(e.target) && !menu.contains(e.target)) {
            menu.style.display = "none";
        }
    });
    </script>
    </div>
</div>

<!-- STAT CARDS -->
<div class="stats">
    <div class="card">
        <h4>Upcoming Events</h4>
        <h1><?= $upcoming_events ?></h1>
    </div>
    <div class="card">
        <h4>Registered Members</h4>
        <h1><?= $registered_members ?></h1>
    </div>
</div>

<!-- EVENTS SECTION -->
<section class="section">
    <div class="section-header">
        <h2>Events</h2>
        <div>
            <button class="btn-purple" onclick="openCreateModal()">+ Create Event</button>
            <button class="btn-black">Export</button>
        </div>
    </div>

    <table>
        <thead>
        <tr>
            <th>Event</th><th>Date</th><th>Time</th><th>Venue</th><th>Description</th><th>Status</th><th>Actions</th>
        </tr>
        </thead>
        <tbody id="eventTable">

        <?php
        // ====== EVENTS query (supports title + date + venue + description + status search) ======
        $eventsSql = "SELECT id, title, date, time, venue, description, status FROM events";

        if ($search !== '') {
            $safeSearch = '%' . $conn->real_escape_string($search) . '%';

            $eventsSql .= " WHERE
                title LIKE '$safeSearch'
                OR venue LIKE '$safeSearch'
                OR description LIKE '$safeSearch'
                OR status LIKE '$safeSearch'
                -- Match against raw database date format (e.g., 2025-11-20)
                OR DATE_FORMAT(date, '%Y-%m-%d') LIKE '$safeSearch'
                -- Match against display date format (e.g., Nov 20)
                OR DATE_FORMAT(date, '%b %d') LIKE '$safeSearch'
            ";
        }
        $eventsSql .= " ORDER BY date ASC";

        $result = mysqli_query($conn, $eventsSql);

        if ($result && mysqli_num_rows($result) > 0) {
            while ($event = mysqli_fetch_assoc($result)) {
                ?>
                <tr id="event-<?php echo $event['id']; ?>">
                    <td><?= htmlspecialchars($event['title']); ?></td>
                    <td><?= htmlspecialchars(date("M d", strtotime($event['date']))); ?></td>
                    <td><?= htmlspecialchars(!empty($event['time']) ? date("g:i A", strtotime($event['time'])) : '-'); ?></td>
                    <td><?= htmlspecialchars($event['venue']); ?></td>
                    <td><?= htmlspecialchars($event['description']); ?></td>
                    <td><?= htmlspecialchars($event['status']); ?></td>
                    <td>
                        <button class="btn-purple" onclick="openUpdateModal(<?= $event['id']; ?>,'<?= htmlspecialchars($event['title']); ?>','<?= $event['date']; ?>','<?= $event['time'] ?? ''; ?>','<?= htmlspecialchars($event['venue']); ?>','<?= htmlspecialchars($event['description']); ?>','<?= $event['status']; ?>')">Update</button>
                        <button class="btn-black" onclick="deleteEvent(<?= $event['id']; ?>)">Delete</button>
                    </td>
                </tr>
                <?php
            }
        } else {
            echo "<tr><td colspan='7' style='color:#94a3b8;'>No events found.</td></tr>";
        }
        ?>
        </tbody>
    </table>
</section>

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

<!-- ADMIN USER PROFILE LIST -->
<section id="admin-user-list" style="padding: 60px 20px;">
    <h1>User Profiles</h1>
    <p>Complete list of all users registered in ALICE.</p>

    <?php
    // Fetch all user profiles + email
   
    // ====== USER PROFILES query (global search supported) ======
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
            WHERE user_profile.full_name      LIKE '$safeSearch'
               OR user_profile.matric_number  LIKE '$safeSearch'
               OR user_profile.gender         LIKE '$safeSearch'
               OR user_profile.year_of_study  LIKE '$safeSearch'
               OR user_profile.course         LIKE '$safeSearch'
               OR users.email                 LIKE '$safeSearch'
        ";
    }

    $profileSql .= " ORDER BY user_profile.full_name ASC";

    $profileQuery = $conn->query($profileSql);
    ?>

    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; margin-top:20px;">
            <tr style="background:#238636; color:white;">
                <th style="padding:10px; border:1px solid #333;">Full Name</th>
                <th style="padding:10px; border:1px solid #333;">Matric Number</th>
                <th style="padding:10px; border:1px solid #333;">Gender</th>
                <th style="padding:10px; border:1px solid #333;">Year of Study</th>
                <th style="padding:10px; border:1px solid #333;">Course</th>
                <th style="padding:10px; border:1px solid #333;">Email</th>
            </tr>

            <?php
            if ($profileQuery && $profileQuery->num_rows > 0) {
                while ($row = $profileQuery->fetch_assoc()) {
                    echo "<tr style='background:#161b22; color:white;'>";
                    echo "<td style='padding:10px; border:1px solid #333;'>{$row['full_name']}</td>";
                    echo "<td style='padding:10px; border:1px solid #333;'>{$row['matric_number']}</td>";
                    echo "<td style='padding:10px; border:1px solid #333;'>{$row['gender']}</td>";
                    echo "<td style='padding:10px; border:1px solid #333;'>{$row['year_of_study']}</td>";
                    echo "<td style='padding:10px; border:1px solid #333;'>{$row['course']}</td>";
                    echo "<td style='padding:10px; border:1px solid #333;'>{$row['email']}</td>";
                    echo $conn->error;
                    echo "<script>console.log(" . json_encode($row) . ");</script>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6' style='padding:10px; text-align:center;'>No user profiles found.</td></tr>";
            }
            ?>
        </table>
    </div>
</section>

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

        let res = await fetch("event_process.php", { method: "POST", body: form });
        let data = await res.json();

        showToast(data.message, data.status === "success");
        if (data.status === "success") location.reload();
    }

    function openUpdateModal(id,name,date,time,venue,description,status){
        document.getElementById("update_id").value=id;
        document.getElementById("update_name").value=name;
        document.getElementById("update_date").value=date;
        document.getElementById("update_time").value=time || '';
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

        let res = await fetch("event_process.php", { method:"POST", body:form });
        let data = await res.json();

        showToast(data.message, data.status === "success");
        if (data.status === "success") location.reload();
    }

    async function deleteEvent(id){
        if(!confirm("Delete this event?")) return;
        let form = new FormData();
        form.append("action","delete");
        form.append("id",id);

        let res = await fetch("event_process.php", { method:"POST", body:form });
        let data = await res.json();

        showToast(data.message, data.status === "success");
        if(data.status === "success") document.getElementById("event-"+id).remove();
    }
</script>

<!-- ================= HIGHLIGHTS SECTION ================= -->
<section class="section">
    <div class="section-header">
        <h2>Manage Highlights</h2>
    </div>

    <table>
        <thead>
        <tr>
            <th>Image</th>
            <th>Title</th>
            <th>Status</th>
            <th>Uploaded At</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php
        // ====== HIGHLIGHTS query (with search support) ======
        $hlSql = "SELECT * FROM highlights";

        if ($search !== '') {
            $safeSearch = '%' . $conn->real_escape_string($search) . '%';
            // Use title + status for a fuzzy search
            $hlSql .= " WHERE title LIKE '$safeSearch'
                        OR status LIKE '$safeSearch'";
        }

        $hlSql .= " ORDER BY created_at DESC";

        $hlQuery = mysqli_query($conn, $hlSql);

        if($hlQuery && mysqli_num_rows($hlQuery)>0){
            while($hl = mysqli_fetch_assoc($hlQuery)){
                echo "<tr id='highlight-{$hl['id']}'>";
                echo "<td><img src='uploads/".htmlspecialchars($hl['image'])."' class='highlight-img'></td>";
                echo "<td>".htmlspecialchars($hl['title'] ?? '')."</td>";
                echo "<td>".htmlspecialchars($hl['status'])."</td>";
                echo "<td>".htmlspecialchars($hl['created_at'])."</td>";
                echo "<td>";
                if($hl['status']=='pending'){
                    echo "<button class='btn-purple' onclick='approveHighlight({$hl['id']})'>Approve</button> ";
                    echo "<button class='btn-black' onclick='rejectHighlight({$hl['id']})'>Reject</button>";
                } elseif($hl['status']=='approved'){
                    echo "<button class='btn-black' onclick='deleteHighlight({$hl['id']})'>Delete</button>";
                }
                echo "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5' style='text-align:center;color:#94a3b8;'>No highlights found.</td></tr>";
        }
        ?>
        </tbody>
    </table>
</section>

<script>
    async function approveHighlight(id){
        if(!confirm("Approve this highlight?")) return;
        let res = await fetch("highlight_approve.php", {
            method:"POST",
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:"action=approve&id="+id
        });
        let data = await res.json();
        showToast(data.message, data.status==="success");
        if(data.status==="success"){
            let row = document.getElementById("highlight-"+id);
            row.querySelector("td:nth-child(3)").textContent = "approved";
            row.querySelector("td:last-child").innerHTML = "<button class='btn-black' onclick='deleteHighlight("+id+")'>Delete</button>";
        }
    }

    async function rejectHighlight(id){
        if(!confirm("Reject this highlight?")) return;
        let res = await fetch("highlight_approve.php", {
            method:"POST",
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:"action=reject&id="+id
        });
        let data = await res.json();
        showToast(data.message, data.status==="success");
        if(data.status==="success") document.getElementById("highlight-"+id).remove();
    }

    async function deleteHighlight(id){
        if(!confirm("Delete this highlight?")) return;
        let res = await fetch("highlight_approve.php", {
            method:"POST",
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:"action=delete&id="+id
        });
        let data = await res.json();
        showToast(data.message, data.status==="success");
        if(data.status==="success") document.getElementById("highlight-"+id).remove();
    }
</script>

<!-- PROPOSED EVENTS SECTION -->
<section class="section">
    <div class="section-header">
        <h2>Proposed Events (Pending Approval)</h2>
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
</section>

<script>
async function approveProposal(id) {
    if (!confirm("Approve this event proposal? It will be added to the main events list.")) return;
    
    let res = await fetch("manage_proposed_events.php", {
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
    
    let res = await fetch("manage_proposed_events.php", {
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
