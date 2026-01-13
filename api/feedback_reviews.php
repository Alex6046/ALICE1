<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Include database connection
include 'db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Reviews - ALICE</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #0d1117;
            color: #fff;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 50px;
            animation: fadeInDown 0.8s ease;
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

        .page-header h1 {
            font-size: 3rem;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #238636 0%, #2ea043 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .page-header p {
            font-size: 1.2rem;
            color: #94a3b8;
        }

        .filter-bar {
            background: rgba(22, 27, 34, 0.8);
            backdrop-filter: blur(10px);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
            animation: fadeInUp 0.8s ease 0.2s both;
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

        .filter-bar input,
        .filter-bar select {
            flex: 1;
            min-width: 200px;
            padding: 12px 18px;
            background: #0d1117;
            border: 1px solid #30363d;
            border-radius: 8px;
            color: #fff;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .filter-bar input:focus,
        .filter-bar select:focus {
            outline: none;
            border-color: #238636;
            box-shadow: 0 0 0 3px rgba(35, 134, 54, 0.2);
        }

        .reviews-grid {
            display: grid;
            gap: 25px;
            animation: fadeInUp 0.8s ease 0.4s both;
        }

        .review-card {
            background: rgba(22, 27, 34, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 25px;
            border: 1px solid #30363d;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            animation: slideIn 0.6s ease forwards;
            opacity: 0;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .review-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(35, 134, 54, 0.2);
            border-color: #238636;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .event-title {
            font-size: 1.3rem;
            color: #238636;
            font-weight: 600;
            margin: 0;
        }

        .rating-stars {
            display: flex;
            gap: 3px;
            color: #ffd700;
            font-size: 1.3rem;
        }

        .rating-value {
            font-size: 1rem;
            color: #94a3b8;
            margin-left: 8px;
        }

        .feedback-text {
            background: rgba(13, 17, 23, 0.6);
            padding: 15px;
            border-radius: 10px;
            margin: 15px 0;
            color: #e6edf3;
            font-style: italic;
            line-height: 1.6;
            border-left: 3px solid #238636;
        }

        .review-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #30363d;
        }

        .reviewer-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .reviewer-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: #238636;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }

        .reviewer-name {
            color: #94a3b8;
            font-size: 0.95rem;
        }

        .review-date {
            color: #6e7681;
            font-size: 0.9rem;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: #238636;
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-bottom: 30px;
        }

        .back-btn:hover {
            background: #2ea043;
            transform: translateX(-5px);
            box-shadow: 0 5px 15px rgba(35, 134, 54, 0.4);
        }

        .no-reviews {
            text-align: center;
            padding: 60px 20px;
            background: rgba(22, 27, 34, 0.6);
            border-radius: 16px;
            animation: fadeIn 0.8s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .no-reviews h3 {
            font-size: 1.5rem;
            color: #94a3b8;
            margin-bottom: 10px;
        }

        .no-reviews p {
            color: #6e7681;
        }

        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 2rem;
            }

            .filter-bar {
                flex-direction: column;
            }

            .filter-bar input,
            .filter-bar select {
                width: 100%;
                min-width: 100%;
            }

            .review-header {
                flex-direction: column;
                gap: 10px;
            }

            .review-footer {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }

        /* Stagger animation for cards */
        .review-card:nth-child(1) { animation-delay: 0s; }
        .review-card:nth-child(2) { animation-delay: 0.1s; }
        .review-card:nth-child(3) { animation-delay: 0.2s; }
        .review-card:nth-child(4) { animation-delay: 0.3s; }
        .review-card:nth-child(5) { animation-delay: 0.4s; }
        .review-card:nth-child(n+6) { animation-delay: 0.5s; }
    </style>
</head>
<body>

<!-- NAVIGATION -->
<?php include 'navbar.php'; ?>

<div class="container">
    <a href="student_dashboard.php" class="back-btn">
        ← Back to Dashboard
    </a>

    <div class="page-header">
        <h1>Event Reviews</h1>
        <p>See what students are saying about ALICE events</p>
    </div>

    <div class="filter-bar">
        <input type="text" id="searchInput" placeholder="🔍 Search reviews..." onkeyup="filterReviews()">
        <select id="ratingFilter" onchange="filterReviews()">
            <option value="">All Ratings</option>
            <option value="5">5 Stars</option>
            <option value="4">4 Stars</option>
            <option value="3">3 Stars</option>
            <option value="2">2 Stars</option>
            <option value="1">1 Star</option>
        </select>
        <select id="sortBy" onchange="filterReviews()">
            <option value="newest">Newest First</option>
            <option value="oldest">Oldest First</option>
            <option value="highest">Highest Rated</option>
            <option value="lowest">Lowest Rated</option>
        </select>
    </div>

    <div class="reviews-grid" id="reviewsContainer">
        <?php
        // Get all public feedback with event and user information
        $query = "
            SELECT ef.*, e.title as event_title, e.date as event_date,
                   COALESCE(ef.display_name, u.username) as display_name
            FROM event_feedback ef
            INNER JOIN events e ON ef.event_id = e.id
            INNER JOIN users u ON ef.user_id = u.id
            WHERE ef.is_public = 1
            ORDER BY ef.created_at DESC
        ";
        
        $result = $conn->query($query);
        
        if ($result && $result->num_rows > 0):
            while ($review = $result->fetch_assoc()):
                $initial = strtoupper(substr($review['display_name'], 0, 1));
        ?>
                <div class="review-card" 
                     data-rating="<?php echo $review['rating']; ?>"
                     data-date="<?php echo strtotime($review['created_at']); ?>"
                     data-event="<?php echo htmlspecialchars($review['event_title']); ?>"
                     data-feedback="<?php echo htmlspecialchars($review['feedback_text'] ?? ''); ?>">
                    
                    <div class="review-header">
                        <h3 class="event-title"><?php echo htmlspecialchars($review['event_title']); ?></h3>
                        <div style="display: flex; align-items: center;">
                            <div class="rating-stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php echo $i <= $review['rating'] ? '★' : '☆'; ?>
                                <?php endfor; ?>
                            </div>
                            <span class="rating-value">(<?php echo $review['rating']; ?>/5)</span>
                        </div>
                    </div>

                    <?php if (!empty($review['feedback_text'])): ?>
                        <div class="feedback-text">
                            "<?php echo htmlspecialchars($review['feedback_text']); ?>"
                        </div>
                    <?php endif; ?>

                    <div class="review-footer">
                        <div class="reviewer-info">
                            <div class="reviewer-avatar"><?php echo $initial; ?></div>
                            <span class="reviewer-name"><?php echo htmlspecialchars($review['display_name']); ?></span>
                        </div>
                        <span class="review-date">
                            <?php echo date("M d, Y", strtotime($review['created_at'])); ?>
                        </span>
                    </div>
                </div>
        <?php 
            endwhile;
        else:
        ?>
            <div class="no-reviews">
                <h3>No Reviews Yet</h3>
                <p>Be the first to share your experience about ALICE events!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function filterReviews() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const ratingFilter = document.getElementById('ratingFilter').value;
        const sortBy = document.getElementById('sortBy').value;
        const container = document.getElementById('reviewsContainer');
        const cards = Array.from(container.getElementsByClassName('review-card'));

        // Filter cards
        let visibleCards = cards.filter(card => {
            const eventTitle = card.dataset.event.toLowerCase();
            const feedbackText = card.dataset.feedback.toLowerCase();
            const rating = card.dataset.rating;

            const matchesSearch = eventTitle.includes(searchTerm) || feedbackText.includes(searchTerm);
            const matchesRating = !ratingFilter || rating === ratingFilter;

            return matchesSearch && matchesRating;
        });

        // Sort cards
        visibleCards.sort((a, b) => {
            switch(sortBy) {
                case 'newest':
                    return parseInt(b.dataset.date) - parseInt(a.dataset.date);
                case 'oldest':
                    return parseInt(a.dataset.date) - parseInt(b.dataset.date);
                case 'highest':
                    return parseInt(b.dataset.rating) - parseInt(a.dataset.rating);
                case 'lowest':
                    return parseInt(a.dataset.rating) - parseInt(b.dataset.rating);
                default:
                    return 0;
            }
        });

        // Hide all cards
        cards.forEach(card => card.style.display = 'none');

        // Show and reorder visible cards
        visibleCards.forEach((card, index) => {
            card.style.display = 'block';
            card.style.animationDelay = `${index * 0.1}s`;
            container.appendChild(card);
        });

        // Show no results message
        if (visibleCards.length === 0 && !document.querySelector('.no-reviews')) {
            container.innerHTML = `
                <div class="no-reviews">
                    <h3>No Reviews Found</h3>
                    <p>Try adjusting your filters or search term.</p>
                </div>
            `;
        }
    }
</script>

</body>
</html>
