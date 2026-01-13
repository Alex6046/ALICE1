<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$feedbackQuery = "
    SELECT 
        ef.id, 
        ef.rating, 
        ef.feedback_text, 
        ef.display_name, 
        ef.created_at,
        e.title as event_title,
        e.date as event_date,
        u.username,
        u.email
    FROM event_feedback ef
    INNER JOIN events e ON ef.event_id = e.id
    INNER JOIN users u ON ef.user_id = u.id
    ORDER BY ef.created_at DESC
";
$feedbackResult = $conn->query($feedbackQuery);

$statsQuery = "
    SELECT 
        COUNT(*) as total_feedback,
        AVG(rating) as avg_rating,
        COUNT(CASE WHEN rating >= 4 THEN 1 END) as positive_count,
        COUNT(CASE WHEN rating = 3 THEN 1 END) as neutral_count,
        COUNT(CASE WHEN rating <= 2 THEN 1 END) as negative_count
    FROM event_feedback
";
$statsResult = $conn->query($statsQuery);
$stats = $statsResult->fetch_assoc();

$ratingDistQuery = "
    SELECT rating, COUNT(*) as count
    FROM event_feedback
    GROUP BY rating
    ORDER BY rating DESC
";
$ratingDistResult = $conn->query($ratingDistQuery);
$ratingDist = [];
while ($row = $ratingDistResult->fetch_assoc()) {
    $ratingDist[$row['rating']] = $row['count'];
}
for ($i = 1; $i <= 5; $i++) {
    if (!isset($ratingDist[$i])) {
        $ratingDist[$i] = 0;
    }
}
ksort($ratingDist);

$topEventsQuery = "
    SELECT 
        e.title,
        AVG(ef.rating) as avg_rating,
        COUNT(ef.id) as feedback_count
    FROM events e
    INNER JOIN event_feedback ef ON e.id = ef.event_id
    GROUP BY e.id
    HAVING feedback_count > 0
    ORDER BY avg_rating DESC, feedback_count DESC
    LIMIT 5
";
$topEventsResult = $conn->query($topEventsQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Management - ALICE Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?php include 'admin_styles.php'; ?>
    <style>
        /* Enhanced design with better typography and spacing */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 25px;
        }

        .page-header {
            margin-bottom: 40px;
            padding: 20px 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: rgba(22, 27, 34, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(124, 58, 237, 0.2);
            border-radius: 16px;
            padding: 30px 28px;
            transition: all 0.3s ease;
            text-align: center;
        }

        .stat-card:hover {
            border-color: rgba(124, 58, 237, 0.5);
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(124, 58, 237, 0.2);
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: #7c3aed;
            margin-bottom: 8px;
            letter-spacing: -1px;
        }

        .stat-label {
            font-size: 0.95rem;
            color: #94a3b8;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .charts-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 32px;
            margin-bottom: 40px;
        }

        .chart-card {
            background: rgba(22, 27, 34, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(124, 58, 237, 0.2);
            border-radius: 16px;
            padding: 35px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .chart-title {
            margin: 0 0 28px 0;
            color: #e6edf3;
            font-size: 1.35rem;
            font-weight: 700;
            letter-spacing: -0.3px;
        }

        .feedback-table-card {
            background: rgba(22, 27, 34, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(124, 58, 237, 0.2);
            border-radius: 16px;
            padding: 35px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            margin-bottom: 30px;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(48, 54, 61, 0.5);
            flex-wrap: wrap;
            gap: 20px;
        }

        .filter-group {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-input, .filter-select {
            padding: 12px 18px;
            background: rgba(13, 17, 23, 0.9);
            border: 1.5px solid #30363d;
            border-radius: 10px;
            color: white;
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
        }

        .search-input {
            width: 280px;
        }

        .search-input:focus, .filter-select:focus {
            outline: none;
            border-color: #7c3aed;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.15);
        }

        .feedback-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        .feedback-table thead th {
            color: #94a3b8;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 16px;
            text-align: left;
            border-bottom: 2px solid #30363d;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .feedback-table thead th:hover {
            color: #7c3aed;
        }

        .feedback-table tbody tr {
            background: rgba(13, 17, 23, 0.6);
            transition: all 0.3s ease;
        }

        .feedback-table tbody tr:hover {
            background: rgba(124, 58, 237, 0.1);
            transform: scale(1.01);
        }

        .feedback-table tbody td {
            padding: 22px 18px;
            color: #c9d1d9;
            font-size: 0.95rem;
            border-top: 1px solid rgba(48, 54, 61, 0.5);
            border-bottom: 1px solid rgba(48, 54, 61, 0.5);
        }

        .feedback-table tbody tr td:first-child {
            border-left: 1px solid rgba(48, 54, 61, 0.5);
            border-radius: 10px 0 0 10px;
        }

        .feedback-table tbody tr td:last-child {
            border-right: 1px solid rgba(48, 54, 61, 0.5);
            border-radius: 0 10px 10px 0;
        }

        .rating-stars {
            color: #ffd700;
            font-size: 1.2rem;
            letter-spacing: 2px;
        }

        .feedback-text-box {
            background: rgba(13, 17, 23, 0.8);
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 0.9rem;
            color: #e6edf3;
            line-height: 1.6;
            max-width: 400px;
        }

        @media (max-width: 968px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }

            .search-input {
                width: 100%;
            }

            .table-header {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    <?php include 'admin_navbar.php'; ?>

    <div class="admin-content">
        <div class="container">
            <div class="page-header">
                <h1>Feedback Management</h1>
                <p>Analyze user feedback and event ratings</p>
            </div>

            <!-- Removed private feedback statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($stats['total_feedback']); ?></div>
                    <div class="stat-label">Total Feedback</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($stats['avg_rating'], 2); ?> ★</div>
                    <div class="stat-label">Average Rating</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($stats['positive_count']); ?></div>
                    <div class="stat-label">Positive (4-5★)</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($stats['neutral_count']); ?></div>
                    <div class="stat-label">Neutral (3★)</div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts-grid">
                <div class="chart-card">
                    <h2 class="chart-title">Rating Distribution</h2>
                    <div style="height: 320px; display: flex; align-items: flex-end; justify-content: space-around; gap: 24px; padding: 0 10px;">
                        <?php foreach ($ratingDist as $rating => $count): ?>
                            <?php 
                                $maxCount = max($ratingDist);
                                $height = $maxCount > 0 ? ($count / $maxCount) * 100 : 0;
                            ?>
                            <div style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 12px;">
                                <div style="color: #7c3aed; font-weight: 700; font-size: 1.3rem;"><?php echo $count; ?></div>
                                <div style="width: 100%; height: <?php echo $height; ?>%; min-height: 40px; background: linear-gradient(180deg, #7c3aed, #5b21b6); border-radius: 10px 10px 0 0; transition: all 0.4s ease; box-shadow: 0 4px 20px rgba(124, 58, 237, 0.3);"></div>
                                <div class="rating-stars"><?php echo str_repeat('★', $rating); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="chart-card">
                    <h2 class="chart-title">Top Rated Events</h2>
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <?php if ($topEventsResult->num_rows > 0): ?>
                            <?php $rank = 1; while ($event = $topEventsResult->fetch_assoc()): ?>
                                <div style="display: flex; align-items: center; gap: 18px; padding: 16px; background: rgba(13, 17, 23, 0.6); border-radius: 12px; transition: all 0.3s ease;" onmouseover="this.style.background='rgba(124, 58, 237, 0.1)'" onmouseout="this.style.background='rgba(13, 17, 23, 0.6)'">
                                    <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #7c3aed, #5b21b6); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; font-weight: 700; color: white; box-shadow: 0 4px 15px rgba(124, 58, 237, 0.4);">
                                        <?php echo $rank++; ?>
                                    </div>
                                    <div style="flex: 1;">
                                        <div style="color: #e6edf3; font-weight: 600; margin-bottom: 6px; font-size: 1rem;"><?php echo htmlspecialchars($event['title']); ?></div>
                                        <div style="color: #94a3b8; font-size: 0.85rem;"><?php echo $event['feedback_count']; ?> reviews</div>
                                    </div>
                                    <div style="text-align: right;">
                                        <div style="color: #ffd700; font-size: 1.5rem; font-weight: 700;"><?php echo number_format($event['avg_rating'], 1); ?> ★</div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div style="text-align: center; color: #6e7681; padding: 40px; font-size: 0.95rem;">No feedback data yet</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Removed private/public visibility filter, simplified to rating filter and search only -->
            <div class="feedback-table-card">
                <div class="table-header">
                    <h2 class="chart-title" style="margin: 0;">All Feedback</h2>
                    <div class="filter-group">
                        <input type="text" id="searchFeedback" placeholder="Search feedback..." class="search-input">
                        <select id="filterRating" class="filter-select">
                            <option value="all">All Ratings</option>
                            <option value="5">5 Stars</option>
                            <option value="4">4 Stars</option>
                            <option value="3">3 Stars</option>
                            <option value="2">2 Stars</option>
                            <option value="1">1 Star</option>
                        </select>
                    </div>
                </div>

                <div style="overflow-x: auto;">
                    <table class="feedback-table">
                        <thead>
                            <tr>
                                <th onclick="sortTable(0)">ID ↕</th>
                                <th onclick="sortTable(1)">Event ↕</th>
                                <th onclick="sortTable(2)">User ↕</th>
                                <th onclick="sortTable(3)">Rating ↕</th>
                                <th>Feedback</th>
                                <th onclick="sortTable(5)">Date ↕</th>
                            </tr>
                        </thead>
                        <tbody id="feedbackTableBody">
                            <?php if ($feedbackResult->num_rows > 0): ?>
                                <?php while ($feedback = $feedbackResult->fetch_assoc()): ?>
                                    <tr data-rating="<?php echo $feedback['rating']; ?>">
                                        <td style="font-weight: 600; color: #7c3aed;">#<?php echo $feedback['id']; ?></td>
                                        <td>
                                            <div style="font-weight: 600; color: #e6edf3; margin-bottom: 4px;"><?php echo htmlspecialchars($feedback['event_title']); ?></div>
                                            <div style="font-size: 0.85rem; color: #6e7681;"><?php echo date("M d, Y", strtotime($feedback['event_date'])); ?></div>
                                        </td>
                                        <td>
                                            <div style="font-weight: 600; color: #94a3b8; margin-bottom: 4px;"><?php echo htmlspecialchars($feedback['display_name']); ?></div>
                                            <div style="font-size: 0.85rem; color: #6e7681;"><?php echo htmlspecialchars($feedback['username']); ?></div>
                                        </td>
                                        <td>
                                            <div class="rating-stars">
                                                <?php echo str_repeat('★', $feedback['rating']); echo str_repeat('☆', 5 - $feedback['rating']); ?>
                                            </div>
                                            <div style="font-size: 0.85rem; color: #94a3b8; margin-top: 4px; text-align: center;"><?php echo $feedback['rating']; ?>/5</div>
                                        </td>
                                        <td>
                                            <?php if (!empty($feedback['feedback_text'])): ?>
                                                <div class="feedback-text-box">
                                                    <?php echo htmlspecialchars($feedback['feedback_text']); ?>
                                                </div>
                                            <?php else: ?>
                                                <span style="color: #6e7681; font-style: italic;">No comment</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="color: #94a3b8; font-size: 0.9rem;">
                                            <?php echo date("M d, Y", strtotime($feedback['created_at'])); ?><br>
                                            <span style="font-size: 0.8rem; color: #6e7681;"><?php echo date("g:i A", strtotime($feedback['created_at'])); ?></span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; color: #6e7681; padding: 50px; font-size: 1rem;">
                                        No feedback submitted yet
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        const searchInput = document.getElementById('searchFeedback');
        const ratingFilter = document.getElementById('filterRating');
        const tableBody = document.getElementById('feedbackTableBody');

        function filterTable() {
            const searchTerm = searchInput.value.toLowerCase();
            const ratingValue = ratingFilter.value;
            const rows = tableBody.getElementsByTagName('tr');

            for (let row of rows) {
                const text = row.textContent.toLowerCase();
                const rating = row.getAttribute('data-rating');

                let showRow = true;

                if (searchTerm && !text.includes(searchTerm)) {
                    showRow = false;
                }

                if (ratingValue !== 'all' && rating !== ratingValue) {
                    showRow = false;
                }

                row.style.display = showRow ? '' : 'none';
            }
        }

        searchInput.addEventListener('input', filterTable);
        ratingFilter.addEventListener('change', filterTable);

        let sortDirection = {};

        function sortTable(columnIndex) {
            const table = document.querySelector('.feedback-table');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr')).filter(row => row.style.display !== 'none');
            
            sortDirection[columnIndex] = !sortDirection[columnIndex];
            const ascending = sortDirection[columnIndex];

            rows.sort((a, b) => {
                let aValue = a.cells[columnIndex].textContent.trim();
                let bValue = b.cells[columnIndex].textContent.trim();

                if (!isNaN(aValue) && !isNaN(bValue)) {
                    return ascending ? aValue - bValue : bValue - aValue;
                }

                return ascending ? aValue.localeCompare(bValue) : bValue.localeCompare(aValue);
            });

            rows.forEach(row => tbody.appendChild(row));
        }
    </script>
</body>
</html>
