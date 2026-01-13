<file name=admin/events.php path=/Applications/MAMP/htdocs/aliceweb/admin><?php
// ... other code ...

?>

<!-- CREATE EVENT MODAL -->
<div id="createEventModal" class="modal">
  <div class="modal-content">
    <span class="close">&times;</span>
    <h2>Create Event</h2>
    <input id="cName" placeholder="Event Name">
    <input id="cDate" type="date">
    <input id="cTime" type="time">
    <input id="cVenue" placeholder="Venue">
    <textarea id="cDescription" placeholder="Description"></textarea>
    <select id="cStatus">
      <option value="upcoming">Upcoming</option>
      <option value="cancelled">Cancelled</option>
      <option value="completed">Completed</option>
    </select>
    <button onclick="handleCreateEvent()">Create</button>
    <p id="createError" style="color:red;display:none;">All fields are required.</p>
  </div>
</div>

<script>
function handleCreateEvent() {
    const name = document.getElementById('cName').value.trim();
    const date = document.getElementById('cDate').value.trim();
    const time = document.getElementById('cTime').value.trim();
    const venue = document.getElementById('cVenue').value.trim();
    const description = document.getElementById('cDescription').value.trim();
    const status = document.getElementById('cStatus').value;

    const errorEl = document.getElementById('createError');
    if (!name || !date || !time || !venue) {
        errorEl.style.display = 'block';
        return;
    } else {
        errorEl.style.display = 'none';
    }

    fetch('event_process.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
            action: 'create',
            name: name,
            date: date,
            time: time,
            venue: venue,
            description: description,
            status: status
        })
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            // handle success, e.g. close modal and refresh event list
        } else {
            errorEl.textContent = data.message || 'Error creating event.';
            errorEl.style.display = 'block';
        }
    })
    .catch(() => {
        errorEl.textContent = 'Error creating event.';
        errorEl.style.display = 'block';
    });
}
</script>

<?php
// ... other code ...
?></file>

<file name=event_process.php path=/Applications/MAMP/htdocs/aliceweb><?php
session_start();
include 'db.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'create') {
    $name = trim($_POST['name'] ?? '');
    $date = trim($_POST['date'] ?? '');
    $time = trim($_POST['time'] ?? '');
    $venue = trim($_POST['venue'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = trim($_POST['status'] ?? 'upcoming');

    if (!$name || !$date || !$time || !$venue) {
        echo json_encode(['status'=>'error','message'=>'All fields are required.']);
        exit;
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO events (title, date, time, venue, description, status) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssssss", $name, $date, $time, $venue, $description, $status);
    if (mysqli_stmt_execute($stmt)) {
        $id = mysqli_insert_id($conn);
        echo json_encode(['status'=>'success','message'=>'Event created successfully.','data'=>[
            'id'=>$id,
            'name'=>$name,
            'date'=>$date,
            'time'=>$time,
            'venue'=>$venue,
            'description'=>$description,
            'status'=>$status
        ]]);
    } else {
        echo json_encode(['status'=>'error','message'=>'Failed to create event.']);
    }
    exit;
}

// ... other actions ...
?></file>