<?php
require_once 'admin_auth_check.php'; // Secure this page
require '../db.php';

// Fetch all feedback, joining with users table to get username
$feedback_result = $conn->query("
    SELECT f.*, u.username 
    FROM feedback f
    JOIN users u ON f.user_id = u.id
    ORDER BY f.created_at DESC
");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - View Feedback</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root{--bg:#0f1724;--card:#0b1220;--muted:#9aa4b2;--accent:#1d9bf0;--glass:rgba(255,255,255,0.03)}
        body{margin:0;font-family:'Montserrat',sans-serif;background:linear-gradient(180deg,var(--bg) 0%,#071027 60%);color:#e6eef8;transition:padding-left .3s ease}
        .sidebar{position:fixed;top:0;left:0;height:100%;width:260px;background:var(--card);padding:30px 20px;border-right:1px solid rgba(255,255,255,.07);display:flex;flex-direction:column;z-index:200;transform:translateX(-100%);transition:transform .3s ease;box-sizing:border-box}
        .sidebar h3{font-size:22px;text-align:center;margin:0 0 30px 0;color:#fff;font-weight:bold}
        .sidebar ul{list-style:none;padding:0;margin:0;flex-grow:1}
        .sidebar li{margin-bottom:8px}
        .sidebar a{display:block;padding:12px 20px;color:var(--muted);text-decoration:none;border-radius:8px;transition:background-color .3s ease,color .3s ease;font-weight:500}
        .sidebar a:hover{background-color:rgba(29,155,240,.1);color:#fff}
        .sidebar a.active{background-color:var(--accent);color:#0b1220;font-weight:700;box-shadow:0 2px 10px rgba(29,155,240,.4)}
        body.sidebar-open .sidebar{transform:translateX(0)}
        body.sidebar-open{padding-left:260px}
        .admin-header{display:flex;align-items:center;padding:15px 30px;background:var(--card);border-bottom:1px solid rgba(255,255,255,.07);position:sticky;top:0;z-index:100}
        .burger-icon{cursor:pointer;margin-right:20px}
        .burger-icon svg{width:24px;height:24px;stroke:var(--muted);transition:stroke .3s}
        .burger-icon:hover svg{stroke:#fff}
        .page-wrapper{min-height:100vh;transition:padding-left .3s ease}
        .main-content{padding:40px}
        h1,h2{font-size:28px;margin-top:0;margin-bottom:20px;border-left:4px solid var(--accent);padding-left:15px;color:#fff}
        .table-container{background:var(--card);padding:30px;border-radius:12px;border:1px solid rgba(255,255,255,.07);margin-bottom:40px;overflow-x:auto}
        table{width:100%;border-collapse:collapse;color:#e6eef8}
        th,td{padding:12px 15px;text-align:left;border-bottom:1px solid var(--glass);font-size:14px}
        th{background-color:rgba(255,255,255,.05);font-weight:700;color:#fff}
        tr:hover{background-color:var(--glass)}
        .star{color:#f5b32a}
        .comment-cell{min-width:250px;white-space:normal}
    </style>
</head>
<body>
    <div class="sidebar">
        <h3>Travel Tales Admin</h3>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="destinations.php">Manage Destinations</a></li>
            <li><a href="articles.php">Manage Articles</a></li>
            <li><a href="blog.php">Manage Blogs</a></li>
            <li><a href="manage_users.php">Manage Users</a></li>
            <li><a href="quiz.php">Manage Quiz</a></li>
            <li><a href="posts.php">Manage Forum</a></li>
            <li><a href="notifications.php">Manage Notifications</a></li>
            <li><a href="feedback.php" class="active">View Feedback</a></li>
            <li><a href="carousel.php">Manage Carousel</a></li>
            <li><a href="logout.php" style="color: #f0ad4e;">Logout</a></li>
            <li><a href="settings.php">Settings</a></li>
        </ul>
        <div><a href="../index.php" style="text-align:center;font-size:14px;">&larr; Back to Main Site</a></div>
    </div>

    <div class="page-wrapper">
        <header class="admin-header">
            <div class="burger-icon" id="burger-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
            </div>
            <h1>User Feedback</h1>
        </header>

        <main class="main-content">
            <div class="table-container">
                <h2>All Feedback Submissions</h2>
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Date</th>
                            <th>Rating</th>
                            <th>Q1: Nav</th>
                            <th>Q2: Design</th>
                            <th>Q3: Perf</th>
                            <th>Q4: Content</th>
                            <th>Q5: Features</th>
                            <th>Q6: Found Info?</th>
                            <th>Q7: Recommend</th>
                            <th class="comment-cell">Q8: Liked Most</th>
                            <th class="comment-cell">Q9: Improvements</th>
                            <th class="comment-cell">Q10: Missing</th>
                            <th class="comment-cell">Comments</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($feedback_result && $feedback_result->num_rows > 0): ?>
                            <?php while($row = $feedback_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                <td><span class="star"><?php echo str_repeat('&#9733;', $row['rating']); ?></span></td>
                                <td><?php echo htmlspecialchars($row['q1_navigation']); ?></td>
                                <td><?php echo htmlspecialchars($row['q2_design']); ?></td>
                                <td><?php echo htmlspecialchars($row['q3_performance']); ?></td>
                                <td><?php echo htmlspecialchars($row['q4_content_quality']); ?></td>
                                <td><?php echo htmlspecialchars($row['q5_feature_satisfaction']); ?></td>
                                <td><?php echo htmlspecialchars($row['q6_found_info']); ?></td>
                                <td><?php echo htmlspecialchars($row['q7_recommend']); ?>/10</td>
                                <td class="comment-cell"><?php echo htmlspecialchars($row['q8_most_liked']); ?></td>
                                <td class="comment-cell"><?php echo htmlspecialchars($row['q9_improvement_suggestion']); ?></td>
                                <td class="comment-cell"><?php echo htmlspecialchars($row['q10_missing_features']); ?></td>
                                <td class="comment-cell"><?php echo htmlspecialchars($row['comments']); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="14" style="text-align: center; color: var(--muted);">No feedback has been submitted yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        document.getElementById('burger-icon').addEventListener('click', function() {
            document.body.classList.toggle('sidebar-open');
        });
    </script>
</body>
</html>