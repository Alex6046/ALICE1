<?php
session_start();
date_default_timezone_set('Asia/Kuala_Lumpur');

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Include database connection
include 'db.php'; // Make sure this file defines $conn as your MySQLi connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - ALICE Club</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(to bottom, #0d1117, #161b22);
            color: #c9d1d9;
            line-height: 1.6;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('https://images.unsplash.com/photo-1446776811953-b23d57bd21aa?auto=format&fit=crop&w=1600&q=80') no-repeat center center;
            background-size: cover;
            opacity: 0.15;
            z-index: -1;
        }

        /* Improved hero section with gradient text and animations */
        .hero {
            text-align: center;
            padding: 120px 40px;
            background: linear-gradient(180deg, rgba(35, 134, 54, 0.1) 0%, rgba(13, 17, 23, 0) 100%);
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(35, 134, 54, 0.15) 0%, transparent 70%);
            top: -250px;
            left: 50%;
            transform: translateX(-50%);
            animation: pulse 4s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: translateX(-50%) scale(1);
                opacity: 0.5;
            }
            50% {
                transform: translateX(-50%) scale(1.2);
                opacity: 0.8;
            }
        }

        /* Removed text-shadow from hero welcome text */
        .hero .welcome {
            font-size: 4rem;
            font-weight: 700;
            background: linear-gradient(135deg, #fff 0%, #238636 50%, #2ea043 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 20px;
            animation: fadeInDown 1s ease;
            position: relative;
            z-index: 1;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero p {
            font-size: 1.3rem;
            color: #94a3b8;
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.8;
            animation: fadeInUp 1s ease 0.3s both;
            position: relative;
            z-index: 1;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        section {
            padding: 80px 40px;
            max-width: 1400px;
            margin: 0 auto;
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease;
        }

        section.visible {
            opacity: 1;
            transform: translateY(0);
        }

        h1 {
            font-size: 3rem;
            font-weight: 700;
            background: linear-gradient(135deg, #ffffff 0%, #238636 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 20px;
            text-align: center;
            letter-spacing: -1px;
        }

        h2 {
            font-size: 2rem;
            color: #238636;
            margin: 50px 0 30px 0;
            text-align: center;
            font-weight: 600;
        }

        p {
            font-size: 1.1rem;
            color: #94a3b8;
            text-align: center;
            margin-bottom: 40px;
            max-width: 900px;
            margin-left: auto;
            margin-right: auto;
        }

        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .card {
            background: rgba(22, 27, 34, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid #30363d;
            border-radius: 16px;
            padding: 30px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            animation: fadeInStagger 0.6s ease backwards;
        }

        @keyframes fadeInStagger {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .card:nth-child(1) { animation-delay: 0.1s; }
        .card:nth-child(2) { animation-delay: 0.2s; }
        .card:nth-child(3) { animation-delay: 0.3s; }
        .card:nth-child(4) { animation-delay: 0.4s; }

        .card:hover {
            transform: translateY(-10px) scale(1.02);
            border-color: #238636;
            box-shadow: 0 12px 35px rgba(35, 134, 54, 0.3);
        }

        .card h3 {
            color: #238636;
            margin-bottom: 15px;
            font-size: 1.4rem;
            font-weight: 600;
        }

        .card p {
            color: #94a3b8;
            line-height: 1.8;
            text-align: left;
            font-size: 1rem;
        }

        /* Event Cards */
        .event-card {
            background: rgba(22, 27, 34, 0.9);
            border: 1px solid #30363d;
            border-radius: 16px;
            padding: 30px;
            transition: all 0.4s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
        }

        .event-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #238636, #2ea043);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .event-card:hover {
            transform: translateY(-8px);
            border-color: #238636;
            box-shadow: 0 12px 35px rgba(35, 134, 54, 0.3);
        }

        .event-card:hover::before {
            transform: scaleX(1);
        }

        .event-card h3 {
            color: #238636;
            margin-bottom: 15px;
            font-size: 1.4rem;
            font-weight: 600;
        }

        .event-card p {
            color: #94a3b8;
            margin: 10px 0;
            text-align: left;
            font-size: 0.95rem;
        }

        .register-btn {
            background: linear-gradient(135deg, #238636, #2ea043);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            margin-top: 15px;
            transition: all 0.3s ease;
            display: inline-block;
            text-decoration: none;
        }

        .register-btn:hover {
            background: linear-gradient(135deg, #2ea043, #238636);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(35, 134, 54, 0.4);
        }

        /* Member Cards */
        .member-card {
            text-align: center;
            background: rgba(22, 27, 34, 0.9);
            border: 1px solid #30363d;
            border-radius: 16px;
            padding: 30px;
            transition: all 0.4s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .member-card:hover {
            transform: translateY(-10px) rotate(1deg);
            border-color: #238636;
            box-shadow: 0 12px 35px rgba(35, 134, 54, 0.3);
        }

        .member-card img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin-bottom: 20px;
            border: 4px solid #238636;
            object-fit: cover;
            transition: all 0.3s ease;
        }

        .member-card:hover img {
            transform: scale(1.1);
            box-shadow: 0 8px 20px rgba(35, 134, 54, 0.5);
        }

        .member-card h3 {
            color: #ffffff;
            margin-bottom: 8px;
            font-size: 1.3rem;
            font-weight: 600;
        }

        .member-card p {
            color: #238636;
            font-weight: 600;
            font-size: 0.95rem;
        }

        /* Added Contact Us section styling */
        .contact-form {
            max-width: 600px;
            margin: 0 auto;
            background: rgba(22, 27, 34, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid #30363d;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #e6edf3;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .form-group input,
        .form-group textarea {
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

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #238636;
            box-shadow: 0 0 0 3px rgba(35, 134, 54, 0.15);
        }

        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }

        .submit-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #238636, #2ea043);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            background: linear-gradient(135deg, #2ea043, #238636);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(35, 134, 54, 0.4);
        }

        footer {
            text-align: center;
            padding: 40px 20px;
            background: rgba(13, 17, 23, 0.9);
            border-top: 1px solid #30363d;
            margin-top: 60px;
        }

        footer p {
            color: #6e7681;
            font-size: 0.9rem;
        }

        /* Highlights Section Styling */
        .highlights-container {
            position: relative;
            text-align: center;
        }

        .upload-highlight-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, #238636, #2ea043);
            color: white;
            padding: 14px 28px;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 20px auto;
            box-shadow: 0 4px 15px rgba(35, 134, 54, 0.3);
        }

        .upload-highlight-btn:hover {
            background: linear-gradient(135deg, #2ea043, #238636);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(35, 134, 54, 0.5);
        }

        .upload-highlight-btn svg {
            width: 20px;
            height: 20px;
        }

        .highlight-card {
            background: rgba(22, 27, 34, 0.9);
            border: 1px solid #30363d;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            position: relative;
            animation: fadeInStagger 0.6s ease backwards;
        }

        .highlight-card:hover {
            transform: translateY(-8px) scale(1.02);
            border-color: #238636;
            box-shadow: 0 12px 35px rgba(35, 134, 54, 0.3);
        }

        .highlight-card .highlight-image {
            width: 100%;
            height: 280px;
            object-fit: cover;
            display: block;
            transition: transform 0.4s ease;
        }

        .highlight-card:hover .highlight-image {
            transform: scale(1.05);
        }

        .highlight-card-content {
            padding: 20px;
        }

        .highlight-date {
            font-size: 0.85rem;
            color: #6e7681;
            margin-top: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .highlight-date::before {
            content: '📅';
            font-size: 0.9rem;
        }

        /* Upload Modal */
        .upload-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(5px);
            animation: fadeIn 0.3s ease;
        }

        .upload-modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .upload-modal-content {
            background: rgba(22, 27, 34, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid #30363d;
            border-radius: 20px;
            padding: 40px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            animation: slideUp 0.4s ease;
            position: relative;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .upload-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .upload-modal-header h2 {
            color: #ffffff;
            font-size: 1.8rem;
            margin: 0;
            background: linear-gradient(135deg, #ffffff 0%, #238636 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .close-modal {
            background: transparent;
            border: none;
            color: #94a3b8;
            font-size: 28px;
            cursor: pointer;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .close-modal:hover {
            background: rgba(244, 63, 94, 0.1);
            color: #f43f5e;
        }

        .upload-form-group {
            margin-bottom: 25px;
        }

        .upload-form-group label {
            display: block;
            margin-bottom: 10px;
            color: #e6edf3;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .file-upload-area {
            border: 2px dashed #30363d;
            border-radius: 12px;
            padding: 40px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: rgba(13, 17, 23, 0.5);
        }

        .file-upload-area:hover {
            border-color: #238636;
            background: rgba(35, 134, 54, 0.05);
        }

        .file-upload-area.dragover {
            border-color: #238636;
            background: rgba(35, 134, 54, 0.1);
        }

        .file-upload-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }

        .file-upload-text {
            color: #94a3b8;
            font-size: 1rem;
            margin-bottom: 8px;
        }

        .file-upload-hint {
            color: #6e7681;
            font-size: 0.85rem;
        }

        .file-input {
            display: none;
        }

        .preview-image {
            max-width: 100%;
            max-height: 300px;
            border-radius: 12px;
            margin-top: 15px;
            display: none;
            border: 2px solid #30363d;
        }

        .preview-image.show {
            display: block;
        }

        .upload-submit-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #238636, #2ea043);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .upload-submit-btn:hover:not(:disabled) {
            background: linear-gradient(135deg, #2ea043, #238636);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(35, 134, 54, 0.4);
        }

        .upload-submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Success/Error Messages */
        .message-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 24px;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            z-index: 2000;
            animation: slideInRight 0.4s ease;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            max-width: 400px;
        }

.ai-circle {
    position: fixed;
    bottom: 120px;
    right: 30px;
    width: 70px;
    height: 70px;
    background: #7c3aed;
    color: white;
    font-size: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    cursor: pointer;
    box-shadow: 0 12px 30px rgba(0,0,0,0.5);
    z-index: 10001;
    transition: transform 0.3s ease;
}

.ai-circle:hover {
    transform: scale(1.15);
}

.ai-chat-panel {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 330px;
    height: 420px;
    background: #0f172a;
    border-radius: 18px;
    padding: 15px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.6);
    z-index: 10000;
    display: none;
    flex-direction: column;
}

#ai-toggle:checked ~ .ai-chat-panel {
    display: flex;
}

.ai-close {
    position: absolute;
    top: 10px;
    right: 14px;
    cursor: pointer;
    color: #94a3b8;
}

.ai-chat-panel h4 {
    text-align: center;
    color: #7c3aed;
    margin-bottom: 10px;
}

.ai-messages {
    flex: 1;
    overflow-y: auto;
    padding: 10px;
    background: #020617;
    border-radius: 10px;
    font-size: 14px;
}

.ai-hint {
    color: #94a3b8;
    text-align: center;
    margin-top: 30px;
}

.ai-input-area {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-top: 10px;
}

.ai-input-area input {
    padding: 14px;
    font-size: 16px;
    border-radius: 10px;
    border: none;
    background: #020617;
    color: white;
}

.ai-input-area button {
    background: #7c3aed;
    border: none;
    color: white;
    padding: 10px 14px;
    border-radius: 8px;
    cursor: pointer;
}

.ai-send-btn {
    width: 100%;
    padding: 12px;
    font-size: 15px;
    font-weight: 600;
    background: #7c3aed;
    border: none;
    color: white;
    border-radius: 10px;
    cursor: pointer;
}

.ai-send-btn:hover {
    background: #6d28d9;
}

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .message-toast.success {
            background: linear-gradient(135deg, #238636, #2ea043);
        }

        .message-toast.error {
            background: linear-gradient(135deg, #f85149, #da3633);
        }

        .message-toast.info {
            background: linear-gradient(135deg, #1f6feb, #0969da);
        }

        @media (max-width: 768px) {
            .hero .welcome {
                font-size: 2.5rem;
            }

            h1 {
                font-size: 2rem;
            }

            h2 {
                font-size: 1.5rem;
            }

            section {
                padding: 60px 20px;
            }

            .highlight-card .highlight-image {
                height: 220px;
            }

            .upload-modal-content {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>

<!--NAVIGATION -->
<?php include 'navbar.php'; ?>

<!-- HERO -->
<section id="hero" class="hero visible">
    <h1 class="welcome">WELCOME TO ALICE</h1>
    <p>Exploring the frontiers of Artificial Intelligence, Machine Learning, and Data Science. Join us in shaping the future of technology.</p>
</section>

<!-- ABOUT -->
<section id="about">
    <h1>About ALICE</h1>
    <p>ALICE (Artificial Learning & Intelligence Club of Engineers) empowers students to explore AI through collaboration, projects, and community engagement.</p>
    <div class="card-grid">
        <div class="card"><h3>Our Vision</h3><p>To inspire a new generation of AI innovators and researchers.</p></div>
        <div class="card"><h3>Our Mission</h3><p>Hands-on experience, mentorship, and real AI projects to push creativity and innovation.</p></div>
    </div>
</section>

<!-- EVENTS -->
<section id="events">
    <h1>Our Events</h1>
    <p>From AI bootcamps to innovation fairs, ALICE offers engaging activities for learning and fun!</p>

    <h2>Upcoming Events</h2>
    <div class="card-grid">
        <?php
        $today = date('Y-m-d');
        $upcomingEvents = $conn->query("SELECT * FROM events WHERE (date > '$today') OR (date = '$today' AND time >= CURTIME()) ORDER BY date ASC, time ASC LIMIT 4");
        if ($upcomingEvents && $upcomingEvents->num_rows > 0) {
            while ($event = $upcomingEvents->fetch_assoc()) {
                echo "<div class='event-card'>";
                echo "<h3>" . htmlspecialchars($event['title']) . "</h3>";
                echo "<p><strong>Date:</strong> " . date("F j, Y", strtotime($event['date'])) . "</p>";
                if (isset($event['time']) && !empty($event['time'])) {
                    echo "<p><strong>Time:</strong> " . date("g:i A", strtotime($event['time'])) . "</p>";
                } else {
                    echo "<p><strong>Time:</strong> TBA</p>";
                }
                echo "<p><strong>Venue:</strong> " . htmlspecialchars($event['venue']) . "</p>";
                if (isset($event['description']) && !empty($event['description'])) {
                    echo "<p>" . htmlspecialchars($event['description']) . "</p>";
                }
                echo "<form action='apply_event.php' method='POST' style='display:inline;'>";
                echo "<input type='hidden' name='event_id' value='" . $event['id'] . "'>";
                echo "<button type='submit' class='register-btn'>Register Now</button>";
                echo "</form>";
                echo "</div>";
            }
        } else {
            echo "<p style='text-align: center; color: #6e7681; grid-column: 1/-1;'>No upcoming events at the moment.</p>";
        }
        ?>
    </div>

    <h2>Past Events</h2>
    <div class="card-grid">
        <?php
        $pastEvents = $conn->query("SELECT * FROM events WHERE (date < '$today') OR (date = '$today' AND time < CURTIME()) ORDER BY date DESC, time DESC LIMIT 4");
        if ($pastEvents && $pastEvents->num_rows > 0) {
            while ($event = $pastEvents->fetch_assoc()) {
                echo "<div class='event-card'>";
                echo "<h3>" . htmlspecialchars($event['title']) . "</h3>";
                echo "<p><strong>Date:</strong> " . date("F j, Y", strtotime($event['date'])) . "</p>";
                if (isset($event['time']) && !empty($event['time'])) {
                    echo "<p><strong>Time:</strong> " . date("g:i A", strtotime($event['time'])) . "</p>";
                } else {
                    echo "<p><strong>Time:</strong> TBA</p>";
                }
                echo "<p><strong>Venue:</strong> " . htmlspecialchars($event['venue']) . "</p>";
                if (isset($event['description']) && !empty($event['description'])) {
                    echo "<p>" . htmlspecialchars($event['description']) . "</p>";
                }
                echo "</div>";
            }
        } else {
            echo "<p style='text-align: center; color: #6e7681; grid-column: 1/-1;'>No past events.</p>";
        }
        ?>
    </div>
</section>

<!-- HIGHLIGHTS -->
<section id="highlights">
    <h1>Club Highlights</h1>
    <p>Celebrating our achievements, milestones, and memorable moments!</p>

    <div class="highlights-container">
        <button class="upload-highlight-btn" onclick="openUploadModal()">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Upload Highlight
        </button>

        <div class="card-grid">
            <?php
            $highlights = $conn->query("SELECT * FROM highlights WHERE status='approved' ORDER BY created_at DESC");
            if ($highlights && $highlights->num_rows > 0) {
                while ($row = $highlights->fetch_assoc()) {
                    $imagePath = !empty($row['image']) ? 'uploads/' . htmlspecialchars($row['image']) : '';
                    echo "<div class='highlight-card'>";
                    if ($imagePath && file_exists($imagePath)) {
                        echo "<img src='" . $imagePath . "' alt='Club Highlight' class='highlight-image'>";
                    }
                    echo "<div class='highlight-card-content'>";
                    if (!empty($row['title'])) {
                        echo "<h3 style='color: #238636; margin-bottom: 10px; font-size: 1.2rem;'>" . htmlspecialchars($row['title']) . "</h3>";
                    }
                    echo "<div class='highlight-date'>" . date("F j, Y", strtotime($row['created_at'])) . "</div>";
                    echo "</div>";
                    echo "</div>";
                }
            } else {
                echo "<div style='text-align: center; color: #6e7681; grid-column: 1/-1; padding: 40px; background: rgba(22, 27, 34, 0.5); border-radius: 16px; border: 2px dashed #30363d;'>";
                echo "<p style='font-size: 1.1rem; margin-bottom: 10px;'>No highlights yet.</p>";
                echo "<p style='font-size: 0.9rem; color: #6e7681;'>Be the first to share a memorable moment!</p>";
                echo "</div>";
            }
            ?>
        </div>
    </div>
</section>

<!-- Upload Highlight Modal -->
<div id="uploadModal" class="upload-modal">
    <div class="upload-modal-content">
        <div class="upload-modal-header">
            <h2>Upload Highlight</h2>
            <button class="close-modal" onclick="closeUploadModal()">&times;</button>
        </div>
        <form id="uploadHighlightForm" enctype="multipart/form-data">
            <div class="upload-form-group">
                <label for="highlight_image">Select Image</label>
                <div class="file-upload-area" id="fileUploadArea" onclick="document.getElementById('highlight_image').click()">
                    <div class="file-upload-icon">📸</div>
                    <div class="file-upload-text">Click to upload or drag and drop</div>
                    <div class="file-upload-hint">PNG, JPG up to 10MB</div>
                    <input type="file" id="highlight_image" name="highlight_image" class="file-input" accept="image/jpeg,image/png,image/jpg" required>
                </div>
                <img id="previewImage" class="preview-image" alt="Preview">
            </div>
            <button type="submit" class="upload-submit-btn" id="submitBtn">
                Upload Highlight
            </button>
        </form>
    </div>
</div>

<!-- MEMBERS -->
<section id="members">
    <h1>Our Members</h1>
    <p>Meet the brilliant minds driving ALICE’s AI success.</p>
    <div class="card-grid">
        <div class="card">
            <h3>Azry Fikri</h3>
            <p>President — Oversees club activities and overall strategy.</p>
        </div>
        <div class="card">
            <h3>Hakim</h3>
            <p>Vice President — Manages events, collaborations, and member engagement.</p>
        </div>
        <div class="card">
            <h3>Muhammad</h3>
            <p>Tech Lead — Supervises technical projects, AI, and programming initiatives.</p>
        </div>
    </div>

    <div class="card-grid">
        <div class="card">
            <h3>Abu Talib</h3>
            <p>Secretary — Handles documentation, meeting notes, and communication.</p>
        </div>
        <div class="card">
            <h3>Sarah Lim</h3>
            <p>Event Coordinator — Plans events, manages logistics, and coordinates volunteers.</p>
        </div>
        <div class="card">
            <h3>Daniel Wong</h3>
            <p>Design Lead — Oversees visual design, branding, and creative content.</p>
        </div>
    </div>

</section>


<!-- Added Contact Us section -->
<section id="contact">
    <h1>Contact Us</h1>
    <p>Have questions or want to get involved? We'd love to hear from you!</p>

    <div class="contact-form">
        <?php
        // Display success/error messages based on URL parameters
        if (isset($_GET['sent']) && $_GET['sent'] == '1') {
            echo '<div class="message-toast success" style="position: relative; top: 0; right: 0; margin-bottom: 20px;">✅ Your message has been sent successfully! We will get back to you soon.</div>';
        }
        if (isset($_GET['err'])) {
            $errorMsg = '';
            if ($_GET['err'] == 'empty') {
                $errorMsg = '❌ Please fill in all fields.';
            } elseif ($_GET['err'] == 'email') {
                $errorMsg = '❌ Please enter a valid email address.';
            }
            if ($errorMsg) {
                echo '<div class="message-toast error" style="position: relative; top: 0; right: 0; margin-bottom: 20px;">' . htmlspecialchars($errorMsg) . '</div>';
            }
        }
        ?>
        <form method="POST" action="submit_contact.php">
            <div class="form-group">
                <label>Your Name</label>
                <input type="text" name="name" required placeholder="Enter your full name">
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required placeholder="your.email@example.com">
            </div>

            <div class="form-group">
                <label>Subject</label>
                <input type="text" name="subject" required placeholder="What is this about?">
            </div>

            <div class="form-group">
                <label>Message</label>
                <textarea name="message" required placeholder="Write your message here..."></textarea>
            </div>

            <button type="submit" class="submit-btn">Send Message</button>
        </form>
    </div>
</section>

<!-- FOOTER -->
<footer>
    <p>&copy; 2025 ALICE Club. All rights reserved. | Empowering the future of AI</p>
</footer>

<script>
    // Intersection Observer for smooth scroll animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, observerOptions);

    document.querySelectorAll('section').forEach(section => {
        observer.observe(section);
    });

    // Smooth scroll for navigation links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Upload Highlight Modal Functions
    function openUploadModal() {
        document.getElementById('uploadModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeUploadModal() {
        document.getElementById('uploadModal').classList.remove('active');
        document.body.style.overflow = 'auto';
        document.getElementById('uploadHighlightForm').reset();
        document.getElementById('previewImage').classList.remove('show');
        document.getElementById('previewImage').src = '';
    }

    // Close modal when clicking outside
    document.getElementById('uploadModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeUploadModal();
        }
    });

    // File upload preview
    document.getElementById('highlight_image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('previewImage');
                preview.src = e.target.result;
                preview.classList.add('show');
            };
            reader.readAsDataURL(file);
        }
    });

    // Drag and drop functionality
    const fileUploadArea = document.getElementById('fileUploadArea');
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        fileUploadArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        fileUploadArea.addEventListener(eventName, () => {
            fileUploadArea.classList.add('dragover');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        fileUploadArea.addEventListener(eventName, () => {
            fileUploadArea.classList.remove('dragover');
        }, false);
    });

    fileUploadArea.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files.length > 0) {
            document.getElementById('highlight_image').files = files;
            const file = files[0];
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('previewImage');
                preview.src = e.target.result;
                preview.classList.add('show');
            };
            reader.readAsDataURL(file);
        }
    }

    // Form submission
    document.getElementById('uploadHighlightForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = document.getElementById('submitBtn');
        const originalText = submitBtn.textContent;
        
        submitBtn.disabled = true;
        submitBtn.textContent = 'Uploading...';

        try {
            const response = await fetch('upload_highlight.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            
            if (data.status === 'success') {
                showMessage('Your highlight has been submitted and is waiting for admin approval!', 'success');
                closeUploadModal();
                // Optionally reload highlights after a delay
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                showMessage(data.message || 'Upload failed. Please try again.', 'error');
            }
        } catch (error) {
            showMessage('An error occurred. Please try again.', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    });

    // Show message toast
    function showMessage(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `message-toast ${type}`;
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.animation = 'slideInRight 0.4s ease reverse';
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 400);
        }, 4000);
    }

    <?php if (isset($_SESSION['message'])): ?>
    document.addEventListener('DOMContentLoaded', function() {
        showMessage("<?php echo addslashes($_SESSION['message']); ?>", "<?php echo (strpos(strtolower($_SESSION['message']), 'success') !== false) ? 'success' : 'info'; ?>");
    });
    <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
</script>

    <input type="checkbox" id="ai-toggle" hidden>

    <label for="ai-toggle" class="ai-circle">🤖</label>

    <div class="ai-chat-panel">
        <label for="ai-toggle" class="ai-close">✕</label>

        <h4>ALICE AI Assistant</h4>

        <div class="ai-messages" id="aiMessages">
            <div class="ai-hint"></div>
        </div>

        <form id="aiForm" class="ai-input-area">
            <input type="text" id="aiInput" placeholder="Type a message..." required>
            <button type="submit" class="ai-send-btn">Send</button>
        </form>
    </div>

    <script src="ai_chat.js"></script>
</body>
</html>
