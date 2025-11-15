<?php
require_once 'admin_auth_check.php'; // Secure this page
require '../db.php';

// --- Handle Add Notification ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_notification'])) {
    $text = trim($_POST['text']);
    if (!empty($text)) {
        $stmt = $conn->prepare("INSERT INTO notifications (text, created_at) VALUES (?, NOW())");
        $stmt->bind_param("s", $text);
        $stmt->execute();
        $stmt->close();
        $_SESSION['success_message'] = "Notification added successfully.";
        header("Location: notifications.php");
        exit;
    }
}

// --- Handle Deletion ---
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id_to_delete = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ?");
    $stmt->bind_param("i", $id_to_delete);
    $stmt->execute();
    $stmt->close();
    $_SESSION['success_message'] = "Notification deleted successfully.";
    header("Location: notifications.php");
    exit;
}

// --- Fetch all notifications ---
$notifications_result = $conn->query("SELECT * FROM notifications ORDER BY created_at DESC");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Notifications</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root{
          --bg:#0f1724;
          --card:#0b1220;
          --muted:#9aa4b2;
          --accent:#1d9bf0;
          --glass: rgba(255,255,255,0.03);
        }
        body {
            margin: 0;
            font-family: 'Montserrat', sans-serif;
            background:linear-gradient(180deg,var(--bg) 0%, #071027 60%);
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
        .sidebar h3 { font-size: 22px; text-align: center; margin: 0 0 30px 0; color: #fff; font-weight: bold; }
        .sidebar ul { list-style: none; padding: 0; margin: 0; flex-grow: 1; }
        .sidebar li { margin-bottom: 8px; }
        .sidebar a { display: block; padding: 12px 20px; color: var(--muted); text-decoration: none; border-radius: 8px; transition: background-color 0.3s ease, color 0.3s ease; font-weight: 500; }
        .sidebar a:hover { background-color: rgba(29, 155, 240, 0.1); color: #fff; }
        .sidebar a.active { background-color: var(--accent); color: #0b1220; font-weight: 700; box-shadow: 0 2px 10px rgba(29, 155, 240, 0.4); }

        /* Sidebar Open State */
        body.sidebar-open .sidebar { transform: translateX(0); }
        body.sidebar-open { padding-left: 260px; }

        /* Admin Header */
        .admin-header { display: flex; align-items: center; padding: 15px 30px; background: var(--card); border-bottom: 1px solid rgba(255,255,255,0.07); position: sticky; top: 0; z-index: 100; }
        .burger-icon { cursor: pointer; margin-right: 20px; }
        .burger-icon svg { width: 24px; height: 24px; stroke: var(--muted); transition: stroke 0.3s; }
        .burger-icon:hover svg { stroke: #fff; }

        .page-wrapper { min-height: 100vh; transition: padding-left 0.3s ease; }

        /* Main Content Area */
        .main-content { padding: 40px; }
        h1, h2 { font-size: 28px; margin-top: 0; margin-bottom: 20px; border-left: 4px solid var(--accent); padding-left: 15px; color: #fff; }
        .form-container, .table-container { background: var(--card); padding: 30px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.07); margin-bottom: 40px; }
        .form-grid { display: grid; grid-template-columns: 1fr; gap: 20px; }
        .form-group { display: flex; flex-direction: column; }
        label { font-weight: 600; margin-bottom: 8px; font-size: 14px; color: var(--muted); }
        textarea { width: 100%; padding: 12px; background: var(--glass); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #e6eef8; font-family: 'Montserrat', sans-serif; font-size: 16px; box-sizing: border-box; resize: vertical; }
        textarea:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 2px rgba(29, 155, 240, 0.3); }
        button[type="submit"] { padding: 12px 25px; background: var(--accent); color: #041022; border: none; border-radius: 8px; font-weight: 700; font-size: 16px; cursor: pointer; transition: background 0.3s; justify-self: start; }
        button[type="submit"]:hover { background: #4fbfff; }
        
        /* Notification List */
        .notification-list .notification-item { display: flex; justify-content: space-between; align-items: center; padding: 15px; border-radius: 8px; background: var(--glass); border: 1px solid rgba(255,255,255,0.05); margin-bottom: 12px; }
        .notification-item p { margin: 0; }
        .notification-item .text { color: #e6eef8; }
        .notification-item .date { font-size: 12px; color: var(--muted); margin-top: 4px; }
        .notification-item .delete-btn { color: #d9534f; text-decoration: none; font-weight: 600; }

        /* Success Message */
        .success-message { padding: 15px; background-color: rgba(92, 184, 92, 0.2); color: #5cb85c; border: 1px solid #5cb85c; border-radius: 8px; margin-bottom: 20px; }
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
            <li><a href="notifications.php" class="active">Manage Notifications</a></li>
            <li><a href="feedback.php">View Feedback</a></li>
            <li><a href="carousel.php" >Manage Carousel</a></li>
            <li><a href="logout.php" style="color: #f0ad4e;">Logout</a></li>
            <li><a href="settings.php">Settings</a></li>
        </ul>
        <div>
            <a href="../index.php" style="text-align: center; font-size: 14px;">&larr; Back to Main Site</a>
        </div>
    </div>

    <div class="page-wrapper">
        <header class="admin-header">
            <div class="burger-icon" id="burger-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </div>
            <h1>Manage Notifications</h1>
        </header>

        <main class="main-content">
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="success-message"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
            <?php endif; ?>

            <div class="form-container">
                <h2>Add New Notification</h2>
                <form method="post" class="form-grid">
                    <div class="form-group">
                        <label for="text">Notification Text</label>
                        <textarea id="text" name="text" rows="3" required></textarea>
                    </div>
                    <button type="submit" name="add_notification">Add Notification</button>
                </form>
            </div>

            <div class="table-container">
                <h2>Existing Notifications</h2>
                <div class="notification-list">
                    <?php if ($notifications_result && $notifications_result->num_rows > 0): ?>
                        <?php while($row = $notifications_result->fetch_assoc()): ?>
                        <div class="notification-item">
                            <div>
                                <p class="text"><?php echo htmlspecialchars($row['text']); ?></p>
                                <p class="date">Created: <?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?></p>
                            </div>
                            <a href="notifications.php?delete=<?php echo $row['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this notification?')">Delete</a>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: var(--muted);">No notifications yet.</p>
                    <?php endif; ?>
                </div>
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