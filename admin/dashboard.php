<?php
require_once 'admin_auth_check.php'; // Secure this page
require '../db.php'; // Use the central database connection file

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root{
          --bg:#1e293b; /* slate-700 */
          --card:#0b1220;
          --muted:#9aa4b2;
          --accent:#1d9bf0;
          --glass: rgba(255,255,255,0.03);
        }
        body {
            margin: 0;
            font-family: 'Montserrat', sans-serif;
            background:linear-gradient(180deg, var(--bg) 0%, #0f172a 100%);
            color: #e6eef8; 
            transition: padding-left 0.3s ease;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: 260px;
            background: var(--card);
            padding: 30px 20px;
            border-right: 1px solid rgba(255,255,255,0.07);
            display: flex;
            flex-direction: column;
            z-index: 200;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            box-sizing: border-box;
        }
        .sidebar h3 {
            font-size: 22px;
            text-align: center;
            margin: 0 0 30px 0;
            color: #fff;
            font-weight: bold;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
            flex-grow: 1;
        }
        .sidebar li {
            margin-bottom: 8px;
        }
        .sidebar a {
            display: block;
            padding: 12px 20px;
            color: var(--muted);
            text-decoration: none;
            border-radius: 8px;
            transition: background-color 0.3s ease, color 0.3s ease;
            font-weight: 500;
        }
        .sidebar a:hover {
            background-color: rgba(29, 155, 240, 0.1);
            color: #fff;
        }
        .sidebar a.active {
            background-color: var(--accent);
            color: #0b1220;
            font-weight: 700;
            box-shadow: 0 2px 10px rgba(29, 155, 240, 0.4);
        }

        /* Sidebar Open State */
        body.sidebar-open .sidebar {
            transform: translateX(0);
        }
        body.sidebar-open {
            padding-left: 260px;
        }

        /* Admin Header */
        .admin-header {
            display: flex;
            align-items: center;
            padding: 15px 30px;
            background: var(--card);
            border-bottom: 1px solid rgba(255,255,255,0.07);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .burger-icon {
            cursor: pointer;
            margin-right: 20px;
        }
        .burger-icon svg {
            width: 24px;
            height: 24px;
            stroke: var(--muted);
            transition: stroke 0.3s;
        }
        .burger-icon:hover svg {
            stroke: #fff;
        }

        .page-wrapper {
            min-height: 100vh;
            transition: padding-left 0.3s ease;
        }

        /* Main Content Area */
        .main-content {
            padding: 40px;
        }
        h1 {
            font-size: 28px;
            margin-top: 0;
            margin-bottom: 20px;
            border-left: 4px solid var(--accent);
            padding-left: 15px;
            color: #fff;
        }

        /* Card Grid Styles */
        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }
        .admin-card {
            background: var(--card);
            border-radius: 12px;
            padding: 25px;
            border: 1px solid rgba(255,255,255,0.07);
            text-decoration: none;
            color: #e6eef8;
            transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
            display: flex;
            flex-direction: column;
        }
        .admin-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
            border-color: rgba(29, 155, 240, 0.3);
        }
        .admin-card h3 {
            margin: 0 0 10px 0;
            font-size: 20px;
            color: #fff;
            border-left: 3px solid var(--accent);
            padding-left: 12px;
        }
        .admin-card p {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
            flex-grow: 1;
        }
        .admin-card .go-to {
            margin-top: 20px;
            font-weight: 600;
            color: var(--accent);
            align-self: flex-start;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h3>Travel Tales Admin</h3>
        <ul>
            <li><a href="dashboard.php" class="active">Dashboard</a></li>
            <li><a href="destinations.php">Manage Destinations</a></li>
            <li><a href="articles.php">Manage Articles</a></li>
            <li><a href="blog.php">Manage Blogs</a></li>
            <li><a href="manage_users.php">Manage Users</a></li>
            <li><a href="quiz.php">Manage Quiz</a></li>
            <li><a href="posts.php">Manage Forum</a></li>
            <li><a href="notifications.php">Manage Notifications</a></li>
            <li><a href="feedback.php">View Feedback</a></li>
            <li><a href="carousel.php" >Manage Carousel</a></li>
            <li><a href="settings.php">Settings</a></li>
            <li><a href="logout.php" style="color: #f0ad4e;">Logout</a></li>
        </ul>
        <div>
            <a href="../index.php" style="text-align: center; font-size: 14px;">&larr; Back to Main Site</a>
        </div>
    </div>

    <div class="page-wrapper">
        <header class="admin-header">
            <div class="burger-icon" id="burger-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </div>
            <h1>Dashboard</h1>
        </header>

        <main class="main-content">
            <div class="card-grid">
                <a href="destinations.php" class="admin-card">
                    <h3>Manage Destinations</h3>
                    <p>Add, edit, or remove travel destinations. Update details, images, and tags for places around the world.</p>
                    <span class="go-to">Go to Destinations &rarr;</span>
                </a>
                <a href="articles.php" class="admin-card">
                    <h3>Manage Articles</h3>
                    <p>Create, edit, and publish long-form articles. Manage categories, authors, and publication status.</p>
                    <span class="go-to">Go to Articles &rarr;</span>
                </a>
                <a href="blog.php" class="admin-card">
                    <h3>Manage Blogs</h3>
                    <p>Create, edit, or delete user-submitted blogs. Moderate content and manage blog posts from the admin panel.</p>
                    <span class="go-to">Go to Blogs &rarr;</span>
                </a>
                <a href="manage_users.php" class="admin-card">
                    <h3>Manage Users</h3>
                    <p>View the list of all registered users. Monitor new sign-ups and manage user accounts and permissions.</p>
                    <span class="go-to">Go to Users &rarr;</span>
                </a>
                <a href="quiz.php" class="admin-card">
                    <h3>Manage Quiz</h3>
                    <p>Add, edit, or delete questions for the various travel quizzes available on the site.</p>
                    <span class="go-to">Go to Quiz &rarr;</span>
                </a>
                <a href="posts.php" class="admin-card">
                    <h3>Manage Forum</h3>
                    <p>Moderate discussions, remove inappropriate content, and manage topics in the community forum.</p>
                    <span class="go-to">Go to Forum &rarr;</span>
                </a>
                <a href="notifications.php" class="admin-card">
                    <h3>Manage Notifications</h3>
                    <p>Create, view, and delete site-wide notifications that are shown to all users.</p>
                    <span class="go-to">Go to Notifications &rarr;</span>
                </a>
                <a href="feedback.php" class="admin-card">
                    <h3>View Feedback</h3>
                    <p>Review user feedback, ratings, and survey responses to improve the site.</p>
                    <span class="go-to">Go to Feedback &rarr;</span>
                </a>
                <a href="carousel.php" class="admin-card" id="manage-tips-card">
                    <h3>Manage Carousel</h3>
                    <p>Add or remove the rotating travel tips that appear on the homepage carousel.</p>
                    <span class="go-to">Go to Carousel &rarr;</span>
                </a>
                <a href="settings.php" class="admin-card">
                    <h3>Settings</h3>
                    <p>Configure site-wide settings, manage API keys, and adjust the overall behavior of the application.</p>
                    <span class="go-to">Go to Settings &rarr;</span>
                </a>
            </div>
        </main>
    </div>

    <?php
    // --- Close Connection ---
    $conn->close();
    ?>

    <script>
        document.getElementById('burger-icon').addEventListener('click', function() {
            document.body.classList.toggle('sidebar-open');
        });
    </script>
</body>
</html>