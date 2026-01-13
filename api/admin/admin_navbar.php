<?php
// admin_navbar.php - Fixed Navigation Bar for Admin Dashboard
// Check if user is admin
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
?>
<style>
    /* Admin Navigation Bar */
    .admin-navbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: rgba(22, 27, 34, 0.95);
        backdrop-filter: blur(10px);
        padding: 20px 40px;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
        transition: all 0.3s ease;
        animation: slideDown 0.5s ease;
    }

    @keyframes slideDown {
        from {
            transform: translateY(-100%);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    /* Made logo bold and improved positioning */
    .admin-navbar .logo {
        font-size: 28px;
        font-weight: 900;
        color: #ffffff;
        letter-spacing: 2px;
        flex-shrink: 0;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .admin-navbar .logo span {
        color: #7c3aed;
        font-weight: 900;
    }

    .admin-navbar .logo:hover {
        transform: scale(1.05);
    }

    .admin-nav-links {
        display: flex;
        gap: 30px;
        align-items: center;
        flex-grow: 1;
        justify-content: center;
        flex-wrap: wrap;
    }

    .admin-nav-links a {
        color: #c9d1d9;
        text-decoration: none;
        font-weight: 600;
        font-size: 15px;
        position: relative;
        padding: 8px 15px;
        border-radius: 8px;
        transition: all 0.3s ease;
        white-space: nowrap;
    }

    .admin-nav-links a:hover {
        color: #ffffff;
        background: rgba(124, 58, 237, 0.2);
        transform: translateY(-2px);
    }

    .admin-nav-links a.active {
        color: #ffffff;
        background: #7c3aed;
    }

    .admin-profile-container {
        position: relative;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .admin-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #7c3aed;
        color: #fff;
        font-weight: bold;
        font-size: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 3px solid #6d28d9;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);
    }

    .admin-avatar:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 16px rgba(124, 58, 237, 0.5);
    }

    .admin-dropdown {
        position: absolute;
        top: 65px;
        right: 0;
        width: 220px;
        background: rgba(22, 27, 34, 0.98);
        backdrop-filter: blur(10px);
        padding: 15px;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
        border: 1px solid #30363d;
        display: none;
        opacity: 0;
        transform: translateY(-10px);
        transition: all 0.3s ease;
    }

    .admin-dropdown.show {
        display: block;
        opacity: 1;
        transform: translateY(0);
    }

    .admin-dropdown-form {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .admin-dropdown-form label {
        font-size: 14px;
        font-weight: 600;
        color: #c9d1d9;
    }

    .admin-dropdown-form input {
        padding: 10px;
        border: 1px solid #30363d;
        border-radius: 8px;
        background: #0d1117;
        color: #fff;
        font-size: 14px;
    }

    .save-profile-btn {
        padding: 10px;
        background: #7c3aed;
        border: none;
        color: white;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .save-profile-btn:hover {
        background: #6d28d9;
        transform: translateY(-2px);
    }

    .logout-link {
        display: block;
        margin-top: 10px;
        padding: 10px;
        background: #dc3545;
        color: white;
        text-align: center;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .logout-link:hover {
        background: #c82333;
        transform: translateY(-2px);
    }

    /* Mobile Responsive */
    .hamburger-admin {
        display: none;
        flex-direction: column;
        cursor: pointer;
        gap: 4px;
    }

    .hamburger-admin div {
        width: 25px;
        height: 3px;
        background: white;
        transition: all 0.3s ease;
        border-radius: 2px;
    }

    @media (max-width: 768px) {
        .admin-navbar {
            padding: 15px 20px;
        }

        .admin-nav-links {
            position: fixed;
            top: 70px;
            right: -100%;
            flex-direction: column;
            background: rgba(22, 27, 34, 0.98);
            backdrop-filter: blur(10px);
            width: 250px;
            height: calc(100vh - 70px);
            padding: 30px 0;
            gap: 0;
            transition: right 0.3s ease;
            border-left: 1px solid #30363d;
            flex-grow: 0;
            flex-wrap: nowrap;
        }

        .admin-nav-links.show {
            right: 0;
        }

        .admin-nav-links a {
            width: 100%;
            padding: 15px 30px;
            border-bottom: 1px solid #30363d;
            border-radius: 0;
        }

        .hamburger-admin {
            display: flex;
        }
    }

    /* Adjusted page content padding for better header positioning */
    .admin-content {
        padding-top: 100px;
        animation: fadeIn 0.6s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<nav class="admin-navbar">
    <div class="logo">ALICE <span>ADMIN</span></div>

    <div class="admin-nav-links" id="adminNavLinks">
        <a href="dashboard.php">Dashboard</a>
        <a href="events.php">Events</a>
        <a href="members.php">Members</a>
        <a href="highlights.php">Highlights</a>
        <a href="proposals.php">Proposals</a>
        <a href="feedback.php">Feedback</a>
        <a href="profile.php">Profile</a>
    </div>

    <div class="admin-profile-container">
        <div class="admin-avatar" onclick="toggleAdminMenu()">
            <?php 
                $initial = isset($_SESSION['username']) && !empty($_SESSION['username']) 
                    ? strtoupper($_SESSION['username'][0]) 
                    : 'A';
                echo $initial;
            ?>
        </div>

        <div id="adminProfileMenu" class="admin-dropdown">
            <!-- <form method="POST" class="admin-dropdown-form">
                <label>Username</label>
                <input type="text" name="username" 
                       value="<?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : ''; ?>" required>

                <label>Email</label>
                <input type="email" name="email" 
                       value="<?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : ''; ?>" required>

                <button type="submit" name="saveProfile" class="save-profile-btn">Save</button>
            </form> -->

            <a href="../logout.php" class="logout-link">Logout</a>
        </div>
    </div>

    <div class="hamburger-admin" id="hamburgerAdmin">
        <div></div><div></div><div></div>
    </div>
</nav>

<script>
function toggleAdminMenu() {
    const menu = document.getElementById("adminProfileMenu");
    menu.classList.toggle("show");
}

// Close menu when clicking outside
document.addEventListener("click", function(e) {
    const menu = document.getElementById("adminProfileMenu");
    const avatar = document.querySelector(".admin-avatar");

    if (avatar && !avatar.contains(e.target) && !menu.contains(e.target)) {
        menu.classList.remove("show");
    }
});

// Mobile hamburger toggle
const hamburger = document.getElementById("hamburgerAdmin");
const navLinks = document.getElementById("adminNavLinks");

if (hamburger && navLinks) {
    hamburger.addEventListener("click", () => {
        navLinks.classList.toggle("show");
    });
}

// Set active link
const currentPage = window.location.pathname.split('/').pop();
document.querySelectorAll('.admin-nav-links a').forEach(link => {
    if (link.getAttribute('href') === currentPage) {
        link.classList.add('active');
    }
});
</script>
