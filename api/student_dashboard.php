<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Include database connection
include 'db.php';

$user_id = $_SESSION['user_id'];

// Fetch current user's username and email
$userQuery = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$userQuery->bind_param("i", $user_id);
$userQuery->execute();
$userResult = $userQuery->get_result();
$user = $userResult->fetch_assoc();
$username = $user['username'];
$email = $user['email'];

// Fetch user profile info
$profileQuery = $conn->prepare("SELECT * FROM user_profile WHERE user_id = ?");
$profileQuery->bind_param("i", $user_id);
$profileQuery->execute();
$profileResult = $profileQuery->get_result();
$profile = $profileResult->fetch_assoc();

// Display success/error messages
if (isset($_SESSION['success'])) {
    echo '<div style="background: #238636; color: white; padding: 10px; text-align: center; margin-bottom: 20px;">' . $_SESSION['success'] . '</div>';
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    echo '<div style="background: #f85149; color: white; padding: 10px; text-align: center; margin-bottom: 20px;">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ALICE | Student Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), 
                        url('https://images.unsplash.com/photo-1446776811953-b23d57bd21aa?auto=format&fit=crop&w=1600&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: white;
            padding: 0;
            margin: 0;
        }

        /* ================= HERO ================= */
        .hero {
            background: transparent;
            text-align: center;
            padding: 100px 20px;
            color: white;
        }

        .hero h1 {
            font-size: 3rem;
            margin: 0 0 20px 0;
            color: white;
        }

        .hero p {
            font-size: 1.2rem;
            margin: 0;
            color: #e6edf3;
        }

        /* ================= MAIN CONTENT ================= */
        .main-content {
            padding: 40px 20px;
            max-width: 1200px;
            margin: 0 auto;
            background: transparent;
            position: relative;
            z-index: 1;
        }

        /* ================= STATS CARDS ================= */
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: #161b22;
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }

        .stat-card h4 {
            font-size: 1rem;
            color: #94a3b8;
            margin: 0 0 10px 0;
        }

        .stat-card h2 {
            font-size: 2.5rem;
            margin: 0;
            color: #238636;
        }

        /* ================= SECTION ================= */
        .section {
            background: #161b22;
            padding: 20px;
            border-radius: 16px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(35, 134, 54, 0.3);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .section-header h2 {
            margin: 0;
            color: white;
        }

        /* ================= EVENTS GRID ================= */
        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .event-card {
            background: #0d1117;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #30363d;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .event-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(35, 134, 54, 0.2);
        }

        .event-card h3 {
            margin: 0 0 10px 0;
            color: #238636;
        }

        .event-card p {
            margin: 5px 0;
            color: #94a3b8;
            font-size: 0.9rem;
        }

        .event-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-top: 10px;
        }

        .badge-upcoming {
            background: #238636;
            color: white;
        }

        .badge-current {
            background: #1f6feb;
            color: white;
        }

        .badge-passed {
            background: #6e7681;
            color: white;
        }

        /* ================= TABLE ================= */
        table {
            width: 100%;
            border-collapse: collapse;
            color: #fff;
        }

        thead {
            background: #1e293b;
        }

        th, td {
            text-align: left;
            padding: 12px;
        }

        tbody tr {
            background: #0d1117;
            border-bottom: 1px solid #30363d;
        }

        tbody tr:hover {
            background: #161b22;
        }

        /* ================= EMPTY STATE ================= */
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6e7681;
        }

        .empty-state h3 {
            margin: 10px 0;
            color: #94a3b8;
        }

        /* ================= BUTTON ================= */
        .btn-primary {
            background: #238636;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary:hover {
            background: #2ea043;
        }

        /* ================= FEEDBACK MODAL ================= */
        .feedback-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: #161b22;
            padding: 30px;
            border-radius: 16px;
            max-width: 500px;
            width: 90%;
            border: 1px solid #30363d;
        }

        .stars {
            font-size: 2rem;
            color: #6e7681;
            cursor: pointer;
            transition: color 0.2s;
        }

        .stars.active {
            color: #ffd700;
        }
    </style>
</head>
<body>

<!-- NAVIGATION -->
<?php include 'navbar.php'; ?>

<!-- HERO SECTION -->
<section class="hero">
    <h1>Welcome back, <?php echo htmlspecialchars($username); ?>!</h1>
    <p>Your ALICE Student Dashboard - Track your events, explore resources, and stay connected</p>
</section>

<!-- MAIN CONTENT -->
<div class="main-content">

    <!-- STATS CARDS -->
    <div class="stats">
        <?php
        // Count registered events
        $registeredQuery = $conn->prepare("SELECT COUNT(*) as total FROM event_registrations WHERE username = ? AND email = ?");
        $registeredQuery->bind_param("ss", $username, $email);
        $registeredQuery->execute();
        $registeredResult = $registeredQuery->get_result();
        $registeredCount = $registeredResult->fetch_assoc()['total'];

        // Count upcoming registered events
        $upcomingQuery = $conn->prepare("
            SELECT COUNT(*) as total 
            FROM event_registrations er
            INNER JOIN events e ON er.event_id = e.id
            WHERE er.username = ? AND er.email = ? AND e.status = 'Upcoming'
        ");
        $upcomingQuery->bind_param("ss", $username, $email);
        $upcomingQuery->execute();
        $upcomingResult = $upcomingQuery->get_result();
        $upcomingCount = $upcomingResult->fetch_assoc()['total'];
        ?>
        
        <div class="stat-card">
            <h4>Total Registered Events</h4>
            <h2><?php echo $registeredCount; ?></h2>
        </div>
        
        <div class="stat-card">
            <h4>Upcoming Events</h4>
            <h2><?php echo $upcomingCount; ?></h2>
        </div>

        <div class="stat-card">
            <h4>Profile Completion</h4>
            <h2><?php echo ($profile ? '100%' : '0%'); ?></h2>
        </div>
    </div>

    <!-- REGISTERED EVENTS SECTION -->
    <section class="section">
        <div class="section-header">
            <h2>My Registered Events</h2>
            <div>
                <button onclick="openProposalModal()" class="btn-primary" style="margin-right: 10px; background: #6e7681;">Propose Event</button>
                <a href="home.php#events" class="btn-primary">Browse Events</a>
            </div>
        </div>

        <?php
        // Fetch all registered events for the current user
        $eventsQuery = $conn->prepare("
            SELECT e.id, e.title, e.description, e.date, e.venue, e.status, er.created_at as registered_at
            FROM event_registrations er
            INNER JOIN events e ON er.event_id = e.id
            WHERE er.username = ? AND er.email = ?
            ORDER BY e.date ASC
        ");
        $eventsQuery->bind_param("ss", $username, $email);
        $eventsQuery->execute();
        $eventsResult = $eventsQuery->get_result();

        if ($eventsResult->num_rows > 0) {
            echo '<div class="events-grid">';
            while ($event = $eventsResult->fetch_assoc()) {
                $badgeClass = '';
                switch($event['status']) {
                    case 'Upcoming': $badgeClass = 'badge-upcoming'; break;
                    case 'Current': $badgeClass = 'badge-current'; break;
                    case 'Passed': $badgeClass = 'badge-passed'; break;
                    default: $badgeClass = 'badge-upcoming';
                }

                echo '<div class="event-card">';
                echo '<h3>' . htmlspecialchars($event['title']) . '</h3>';
                echo '<p><strong>Date:</strong> ' . date("F j, Y", strtotime($event['date'])) . '</p>';
                echo '<p><strong>Venue:</strong> ' . htmlspecialchars($event['venue']) . '</p>';
                echo '<p>' . htmlspecialchars($event['description']) . '</p>';
                echo '<p style="font-size: 0.8rem; color: #6e7681;"><strong>Registered:</strong> ' . date("M d, Y", strtotime($event['registered_at'])) . '</p>';
                echo '<span class="event-badge ' . $badgeClass . '">' . htmlspecialchars($event['status']) . '</span>';
                echo '</div>';
            }
            echo '</div>';
        } else {
            echo '<div class="empty-state">';
            echo '<h3>📅 No Registered Events Yet</h3>';
            echo '<p>You haven\'t registered for any events yet. Browse available events and join the community!</p>';
            echo '<a href="home.php#events" class="btn-primary" style="margin-top: 20px;">Browse Events</a>';
            echo '</div>';
        }
        ?>
    </section>

    <!-- FEEDBACK SECTION -->
    <section class="section">
        <div class="section-header">
            <h2>Event Feedback & Ratings</h2>
            <div>
                <button onclick="openFeedbackModal()" class="btn-primary">Submit New Feedback</button>
                <a href="feedback_reviews.php" class="btn-primary" style="margin-left: 10px; background: #6e7681;">View All Reviews</a>
            </div>
        </div>

        <?php
        // Get events the user is registered for (passed events only)
        $feedbackEventsQuery = $conn->prepare("
            SELECT e.id, e.title, e.date, e.venue
            FROM event_registrations er
            INNER JOIN events e ON er.event_id = e.id
            WHERE er.username = ? AND er.email = ? AND e.status = 'Passed'
            AND e.id NOT IN (
                SELECT event_id FROM event_feedback WHERE user_id = ?
            )
            ORDER BY e.date DESC
            LIMIT 5
        ");
        $feedbackEventsQuery->bind_param("ssi", $username, $email, $user_id);
        $feedbackEventsQuery->execute();
        $feedbackEventsResult = $feedbackEventsQuery->get_result();
        
        // Get recent public feedback
        $publicFeedbackQuery = $conn->prepare("
            SELECT ef.*, e.title as event_title, u.username, 
                   COALESCE(ef.display_name, u.username) as display_name
            FROM event_feedback ef
            INNER JOIN events e ON ef.event_id = e.id
            INNER JOIN users u ON ef.user_id = u.id
            WHERE ef.is_public = 1
            ORDER BY ef.created_at DESC
            LIMIT 5
        ");
        $publicFeedbackQuery->execute();
        $publicFeedbackResult = $publicFeedbackQuery->get_result();
        ?>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
            
            <!-- Left Column: Events needing feedback -->
            <div>
                <h3 style="color: white; margin-bottom: 15px;">Events Awaiting Your Feedback</h3>
                <?php if ($feedbackEventsResult->num_rows > 0): ?>
                    <div class="events-grid" style="grid-template-columns: 1fr;">
                        <?php while ($event = $feedbackEventsResult->fetch_assoc()): ?>
                            <div class="event-card">
                                <h4 style="margin: 0 0 10px 0; color: #238636;"><?php echo htmlspecialchars($event['title']); ?></h4>
                                <p style="margin: 5px 0; font-size: 0.9rem; color: #94a3b8;">
                                    <strong>Date:</strong> <?php echo date("M d, Y", strtotime($event['date'])); ?>
                                </p>
                                <p style="margin: 5px 0; font-size: 0.9rem; color: #94a3b8;">
                                    <strong>Venue:</strong> <?php echo htmlspecialchars($event['venue']); ?>
                                </p>
                                <button onclick="openFeedbackForEvent(<?php echo $event['id']; ?>)" class="btn-primary" style="margin-top: 10px; width: 100%;">Give Feedback</button>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div style="background: #0d1117; padding: 20px; border-radius: 8px; text-align: center;">
                        <p style="color: #94a3b8; margin: 0;">No events awaiting feedback.</p>
                        <p style="color: #6e7681; font-size: 0.9rem; margin-top: 5px;">All your passed events have been reviewed.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Right Column: Recent public feedback -->
            <div>
                <h3 style="color: white; margin-bottom: 15px;">Recent Reviews</h3>
                <?php if ($publicFeedbackResult->num_rows > 0): ?>
                    <div style="max-height: 400px; overflow-y: auto;">
                        <?php while ($feedback = $publicFeedbackResult->fetch_assoc()): ?>
                            <div class="event-card" style="margin-bottom: 15px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                    <h4 style="margin: 0; color: #238636; font-size: 1rem;">
                                        <?php echo htmlspecialchars($feedback['event_title']); ?>
                                    </h4>
                                    <div style="color: #ffd700; font-size: 1.2rem;">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?php if ($i <= $feedback['rating']): ?>
                                                ★
                                            <?php else: ?>
                                                ☆
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                        <span style="font-size: 0.9rem; color: #94a3b8; margin-left: 5px;">
                                            (<?php echo $feedback['rating']; ?>/5)
                                        </span>
                                    </div>
                                </div>
                                
                                <?php if (!empty($feedback['feedback_text'])): ?>
                                    <div style="background: #161b22; padding: 10px; border-radius: 6px; margin-bottom: 10px;">
                                        <p style="margin: 0; color: #e6edf3; font-style: italic;">
                                            "<?php echo htmlspecialchars($feedback['feedback_text']); ?>"
                                        </p>
                                    </div>
                                <?php endif; ?>
                                
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="color: #94a3b8; font-size: 0.85rem;">
                                        By <?php echo htmlspecialchars($feedback['display_name']); ?>
                                    </span>
                                    <span style="color: #6e7681; font-size: 0.85rem;">
                                        <?php echo date("M d, Y", strtotime($feedback['created_at'])); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div style="background: #0d1117; padding: 20px; border-radius: 8px; text-align: center;">
                        <p style="color: #94a3b8; margin: 0;">No reviews yet.</p>
                        <p style="color: #6e7681; font-size: 0.9rem; margin-top: 5px;">Be the first to share your experience!</p>
                    </div>
                <?php endif; ?>
            </div>
            
        </div>
    </section>

    <!-- RESOURCES SECTION - SIMPLIFIED TO AVOID ERRORS -->
    <section class="section">
        <div class="section-header">
            <h2>Club Resources</h2>
        </div>
        <div class="empty-state">
            <h3>📚 Resources Section</h3>
            <p>This section will be available soon with tutorials, guides, and learning materials.</p>
        </div>
    </section>

</div>

<!-- FEEDBACK MODAL -->
<div id="feedbackModal" class="feedback-modal">
    <div class="modal-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="margin: 0; color: white;">Submit Event Feedback</h2>
            <button onclick="closeFeedbackModal()" style="background: none; border: none; color: #94a3b8; font-size: 1.5rem; cursor: pointer;">×</button>
        </div>
        
        <form id="feedbackForm" method="POST" action="submit_feedback.php">
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; color: #94a3b8;">Select Event to Review</label>
                <select id="eventSelect" name="event_id" style="width: 100%; padding: 10px; background: #0d1117; border: 1px solid #30363d; border-radius: 8px; color: white; margin-bottom: 20px;" required>
                    <option value="">-- Select an event --</option>
                    <?php
                    // Get all passed events the user is registered for and hasn't reviewed
                    $allEventsQuery = $conn->prepare("
                        SELECT e.id, e.title, e.date
                        FROM event_registrations er
                        INNER JOIN events e ON er.event_id = e.id
                        WHERE er.username = ? AND er.email = ? AND e.status = 'Passed'
                        AND e.id NOT IN (
                            SELECT event_id FROM event_feedback WHERE user_id = ?
                        )
                        ORDER BY e.date DESC
                    ");
                    $allEventsQuery->bind_param("ssi", $username, $email, $user_id);
                    $allEventsQuery->execute();
                    $allEventsResult = $allEventsQuery->get_result();
                    
                    while ($event = $allEventsResult->fetch_assoc()) {
                        echo '<option value="' . $event['id'] . '">' 
                             . htmlspecialchars($event['title']) 
                             . ' (' . date("M d, Y", strtotime($event['date'])) . ')' 
                             . '</option>';
                    }
                    ?>
                </select>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; color: #94a3b8;">Rating (1-5 stars)</label>
                <div style="display: flex; justify-content: center; gap: 10px; margin-bottom: 10px;">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <span class="stars" onclick="setRating(<?php echo $i; ?>)" id="star<?php echo $i; ?>">☆</span>
                    <?php endfor; ?>
                </div>
                <input type="hidden" name="rating" id="ratingInput" required>
                <div id="ratingText" style="text-align: center; color: #94a3b8; margin-top: 5px;">Click stars to rate</div>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; color: #94a3b8;">Your Feedback (Optional)</label>
                <textarea name="feedback_text" style="width: 100%; height: 100px; padding: 10px; background: #0d1117; border: 1px solid #30363d; border-radius: 8px; color: white; resize: vertical;" placeholder="Share your thoughts about the event..."></textarea>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; color: #94a3b8;">Display Name</label>
                <input type="text" name="display_name" style="width: 100%; padding: 10px; background: #0d1117; border: 1px solid #30363d; border-radius: 8px; color: white;" placeholder="Anonymous" value="<?php echo htmlspecialchars($username); ?>">
                <small style="color: #6e7681;">This name will be shown with your review</small>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: flex; align-items: center; gap: 10px; color: #94a3b8; cursor: pointer;">
                    <input type="checkbox" name="is_public" checked style="width: 18px; height: 18px;">
                    Make this review public
                </label>
                <small style="color: #6e7681; display: block; margin-top: 5px;">
                    If checked, other students can see your review
                </small>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn-primary" style="flex: 1;">Submit Feedback</button>
                <button type="button" onclick="closeFeedbackModal()" style="flex: 1; background: #6e7681; color: white; border: none; border-radius: 8px; padding: 10px; cursor: pointer;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- EVENT PROPOSAL MODAL -->
<div id="proposalModal" class="feedback-modal">
    <div class="modal-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="margin: 0; color: white;">Propose New Event</h2>
            <button onclick="closeProposalModal()" style="background: none; border: none; color: #94a3b8; font-size: 1.5rem; cursor: pointer;">×</button>
        </div>
        
        <form id="proposalForm" method="POST" action="submit_event_proposal.php">
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; color: #94a3b8;">Event Title</label>
                <input type="text" name="title" required style="width: 100%; padding: 10px; background: #0d1117; border: 1px solid #30363d; border-radius: 8px; color: white;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; color: #94a3b8;">Date</label>
                    <input type="date" name="date" required style="width: 100%; padding: 10px; background: #0d1117; border: 1px solid #30363d; border-radius: 8px; color: white;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; color: #94a3b8;">Time</label>
                    <input type="time" name="time" required style="width: 100%; padding: 10px; background: #0d1117; border: 1px solid #30363d; border-radius: 8px; color: white;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; color: #94a3b8;">Venue</label>
                    <input type="text" name="venue" required style="width: 100%; padding: 10px; background: #0d1117; border: 1px solid #30363d; border-radius: 8px; color: white;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; color: #94a3b8;">Capacity</label>
                    <input type="number" name="capacity" required style="width: 100%; padding: 10px; background: #0d1117; border: 1px solid #30363d; border-radius: 8px; color: white;">
                </div>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; color: #94a3b8;">Description</label>
                <textarea name="description" required style="width: 100%; height: 80px; padding: 10px; background: #0d1117; border: 1px solid #30363d; border-radius: 8px; color: white; resize: vertical;"></textarea>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; color: #94a3b8;">Organizer Name</label>
                <input type="text" name="organizer_name" value="<?php echo htmlspecialchars($username); ?>" required style="width: 100%; padding: 10px; background: #0d1117; border: 1px solid #30363d; border-radius: 8px; color: white;">
            </div>

             <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; color: #94a3b8;">Email</label>
                    <input type="email" name="organizer_email" value="<?php echo htmlspecialchars($email); ?>" required style="width: 100%; padding: 10px; background: #0d1117; border: 1px solid #30363d; border-radius: 8px; color: white;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; color: #94a3b8;">Contact Number</label>
                    <input type="tel" name="contact_number" required style="width: 100%; padding: 10px; background: #0d1117; border: 1px solid #30363d; border-radius: 8px; color: white;">
                </div>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn-primary" style="flex: 1;">Submit Proposal</button>
                <button type="button" onclick="closeProposalModal()" style="flex: 1; background: #6e7681; color: white; border: none; border-radius: 8px; padding: 10px; cursor: pointer;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
// Proposal Modal Functions
function openProposalModal() {
    document.getElementById('proposalModal').style.display = 'flex';
}

function closeProposalModal() {
    document.getElementById('proposalModal').style.display = 'none';
}

// Handle Proposal Form Submission
document.getElementById('proposalForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('submit_event_proposal.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Success: ' + data.message);
            closeProposalModal();
            this.reset();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while submitting the proposal');
    });
});

// Close modal when clicking outside
document.getElementById('proposalModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeProposalModal();
    }
});
</script>

<script>
// SIMPLE JAVASCRIPT THAT WILL DEFINITELY WORK
function openFeedbackModal() {
    document.getElementById('feedbackModal').style.display = 'flex';
    resetForm();
}

function openFeedbackForEvent(eventId) {
    // Set the event ID in the dropdown
    document.getElementById('eventSelect').value = eventId;
    // Show the modal
    openFeedbackModal();
}

function closeFeedbackModal() {
    document.getElementById('feedbackModal').style.display = 'none';
}

function setRating(rating) {
    // Set the rating value
    document.getElementById('ratingInput').value = rating;
    
    // Update all stars
    for (let i = 1; i <= 5; i++) {
        const star = document.getElementById('star' + i);
        if (i <= rating) {
            star.textContent = '★';
            star.style.color = '#ffd700';
        } else {
            star.textContent = '☆';
            star.style.color = '#6e7681';
        }
    }
    
    // Update rating text
    const texts = ['Click stars to rate', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];
    document.getElementById('ratingText').textContent = texts[rating];
}

function resetForm() {
    // Reset the form
    document.getElementById('feedbackForm').reset();
    document.getElementById('ratingInput').value = '';
    
    // Reset stars
    for (let i = 1; i <= 5; i++) {
        const star = document.getElementById('star' + i);
        star.textContent = '☆';
        star.style.color = '#6e7681';
    }
    
    // Reset rating text
    document.getElementById('ratingText').textContent = 'Click stars to rate';
}

// Close modal when clicking outside
document.getElementById('feedbackModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeFeedbackModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeFeedbackModal();
    }
});
</script>

</body>
</html>