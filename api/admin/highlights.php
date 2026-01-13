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
    <title>Manage Highlights - ALICE Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?php include 'admin_styles.php'; ?>
</head>
<body>

<?php include 'admin_navbar.php'; ?>

<div class="admin-content">
    <div class="container">
        <div class="page-header">
            <h1>Manage Highlights</h1>
            <p>Approve, reject, or delete club highlight photos</p>
        </div>

        <div class="section">
            <div class="section-header">
                <h2>Highlights</h2>
            </div>

            <form method="get" style="margin-bottom:20px;">
                <input type="text" name="search" class="search-bar" placeholder="Search highlights by title or status..." value="<?php echo htmlspecialchars($search); ?>">
            </form>

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
                $hlSql = "SELECT * FROM highlights";

                if ($search !== '') {
                    $safeSearch = '%' . $conn->real_escape_string($search) . '%';
                    $hlSql .= " WHERE title LIKE '$safeSearch' OR status LIKE '$safeSearch'";
                }

                $hlSql .= " ORDER BY created_at DESC";

                $hlQuery = mysqli_query($conn, $hlSql);

                if($hlQuery && mysqli_num_rows($hlQuery)>0){
                    while($hl = mysqli_fetch_assoc($hlQuery)){
                        echo "<tr id='highlight-{$hl['id']}'>";
                        echo "<td><img src='../uploads/".htmlspecialchars($hl['image'])."' class='highlight-img'></td>";
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

    async function approveHighlight(id){
        if(!confirm("Approve this highlight?")) return;
        let res = await fetch("../highlight_approve.php", {
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
        let res = await fetch("../highlight_approve.php", {
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
        let res = await fetch("../highlight_approve.php", {
            method:"POST",
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:"action=delete&id="+id
        });
        let data = await res.json();
        showToast(data.message, data.status==="success");
        if(data.status==="success") document.getElementById("highlight-"+id).remove();
    }
</script>

</body>
</html>
