<?php
// navbar.php - Reusable Navigation Bar Component
// This component can be included in any page
?>
<style>
    /* ================= NAVBAR ================= */
    .navbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: rgba(22, 27, 34, 0.95);
        backdrop-filter: blur(10px);
        padding: 20px 40px;
        position: sticky;
        top: 0;
        z-index: 1000;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        transition: all 0.3s ease;
        max-width: 100%;
        box-sizing: border-box;
    }

    .navbar .logo {
        font-size: 28px;
        font-weight: 700;
        color: #ffffff;
        letter-spacing: 2px;
        cursor: pointer;
        transition: all 0.3s ease;
        flex-shrink: 0;
    }

    .navbar .logo:hover {
        color: #238636;
        transform: scale(1.05);
    }

    .nav-links {
        display: flex;
        gap: 30px;
        align-items: center;
        padding: 0;
        flex-grow: 1;
        justify-content: center;
        flex-wrap: wrap;
    }

    .nav-links a {
        color: #c9d1d9;
        text-decoration: none;
        font-weight: 600;
        font-size: 15px;
        position: relative;
        padding: 8px 0;
        transition: all 0.3s ease;
        white-space: nowrap;
    }

    .nav-links a::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 0;
        height: 2px;
        background: #238636;
        transition: width 0.3s ease;
    }

    .nav-links a:hover {
        color: #ffffff;
    }

    .nav-links a:hover::after {
        width: 100%;
    }

    .avatar-container {
        position: relative;
        flex-shrink: 0;
    }

    .avatar {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        border: 3px solid #238636;
        cursor: pointer;
        object-fit: cover;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(35, 134, 54, 0.3);
    }

    .avatar:hover {
        transform: scale(1.1);
        border-color: #2ea043;
        box-shadow: 0 4px 12px rgba(35, 134, 54, 0.5);
    }

    .avatar-menu {
        position: absolute;
        right: 0;
        top: 60px;
        background: rgba(22, 27, 34, 0.98);
        backdrop-filter: blur(10px);
        border-radius: 12px;
        border: 1px solid #30363d;
        display: none;
        flex-direction: column;
        width: 180px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
        opacity: 0;
        transform: translateY(-10px);
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .avatar-menu.show {
        display: flex;
        opacity: 1;
        transform: translateY(0);
    }

    .avatar-menu a {
        padding: 14px 20px;
        display: flex;
        align-items: center;
        color: #c9d1d9;
        text-decoration: none;
        border-bottom: 1px solid #30363d;
        transition: all 0.2s ease;
        font-size: 14px;
        font-weight: 500;
    }

    .avatar-menu a:last-child {
        border-bottom: none;
    }

    .avatar-menu a:hover {
        background: #238636;
        color: #ffffff;
        padding-left: 25px;
    }

    .hamburger {
        display: none;
        flex-direction: column;
        cursor: pointer;
        gap: 4px;
    }

    .hamburger div {
        width: 25px;
        height: 3px;
        background: white;
        transition: all 0.3s ease;
        border-radius: 2px;
    }

    @media (max-width: 1024px) {
        .nav-links {
            gap: 20px;
        }
        
        .navbar {
            padding: 20px 30px;
        }
    }

    @media (max-width: 768px) {
        .navbar {
            padding: 15px 20px;
        }

        .nav-links {
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

        .nav-links.show {
            right: 0;
        }

        .nav-links a {
            width: 100%;
            padding: 15px 30px;
            border-bottom: 1px solid #30363d;
        }

        .nav-links a::after {
            display: none;
        }

        .hamburger {
            display: flex;
        }
    }
</style>

<!-- NAVIGATION -->
<nav class="navbar">
    <div class="logo">ALICE</div>

    <div class="nav-links" id="navLinks">
        <a href="home.php#hero">Home</a>
        <a href="home.php#about">About</a>
        <a href="home.php#events">Events</a>
        <!-- Added Highlights link to navbar -->
        <a href="home.php#highlights">Highlights</a>
        <a href="home.php#members">Members</a>
        <a href="home.php#contact">Contact</a>
        <a href="student_dashboard.php">Dashboard</a>
    </div>

    <div class="avatar-container">
        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Ccircle cx='50' cy='50' r='50' fill='%23238636'/%3E%3Cpath d='M50 45c8.284 0 15-6.716 15-15s-6.716-15-15-15-15 6.716-15 15 6.716 15 15 15zm0 7.5c-10 0-30 5.025-30 15V75h60v-7.5c0-9.975-20-15-30-15z' fill='%23ffffff'/%3E%3C/svg%3E" class="avatar" id="avatarToggle" alt="User Avatar">
        <div class="avatar-menu" id="avatarMenu">
            <a href="edit_profile.php">✏️ Edit Profile</a>
            <a href="logout.php">🚪 Logout</a>
        </div>
    </div>

    <div class="hamburger" id="hamburger">
        <div></div><div></div><div></div>
    </div>
</nav>

<script>
    const avatarToggle = document.getElementById("avatarToggle");
    const avatarMenu = document.getElementById("avatarMenu");
    const hamburger = document.getElementById("hamburger");
    const navLinks = document.getElementById("navLinks");

    if (avatarToggle && avatarMenu) {
        avatarToggle.addEventListener("click", (e) => {
            e.stopPropagation();
            avatarMenu.classList.toggle("show");
        });

        document.addEventListener("click", (event) => {
            if (!avatarToggle.contains(event.target) && !avatarMenu.contains(event.target)) {
                avatarMenu.classList.remove("show");
            }
        });
    }

    if (hamburger && navLinks) {
        hamburger.addEventListener("click", () => {
            navLinks.classList.toggle("show");
        });
    }

    let lastScroll = 0;
    window.addEventListener("scroll", () => {
        const navbar = document.querySelector(".navbar");
        const currentScroll = window.pageYOffset;

        if (currentScroll > 50) {
            navbar.style.padding = "15px 40px";
            navbar.style.boxShadow = "0 4px 20px rgba(0, 0, 0, 0.5)";
        } else {
            navbar.style.padding = "20px 40px";
            navbar.style.boxShadow = "0 2px 10px rgba(0, 0, 0, 0.3)";
        }

        lastScroll = currentScroll;
    });
</script>
