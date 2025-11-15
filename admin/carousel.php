<?php
session_start();
require '../db.php';

// In a real app, you'd have an admin role check here.
// if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
//     header("Location: ../login.php");
//     exit();
// }

// --- Handle Add Tip ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_tip'])) {
    $text = trim($_POST['text']);
    if (!empty($text)) {
        $stmt = $conn->prepare("INSERT INTO carousel (text) VALUES (?)");
        $stmt->bind_param("s", $text);
        $stmt->execute();
        $stmt->close();
        $_SESSION['admin_success'] = "Tip added successfully.";
        header("Location: carousel.php");
        exit;
    }
}

// --- Handle Deletion ---
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id_to_delete = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM carousel WHERE id = ?");
    $stmt->bind_param("i", $id_to_delete);
    $stmt->execute();
    $stmt->close();
    $_SESSION['admin_success'] = "Tip deleted successfully.";
    header("Location: carousel.php");
    exit;
}

// --- Fetch all tips ---
$tips_result = $conn->query("SELECT id, text, created_at FROM carousel ORDER BY created_at DESC");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Tips</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root{
          --bg:#0f1724;
          --card:#0b1220;
          --muted:#9aa4b2;
          --accent:#1d9bf0;
          --glass: rgba(255,255,255,0.03);
        }
        body { margin: 0; font-family: 'Montserrat', sans-serif; background:linear-gradient(180deg,var(--bg) 0%, #071027 60%); color: #e6eef8; transition: padding-left 0.3s ease; }
        .sidebar { position: fixed; top: 0; left: 0; height: 100%; width: 260px; background: var(--card); padding: 30px 20px; border-right: 1px solid rgba(255,255,255,0.07); display: flex; flex-direction: column; z-index: 200; transform: translateX(-100%); transition: transform 0.3s ease; box-sizing: border-box; }
        .sidebar h3 { font-size: 22px; text-align: center; margin: 0 0 30px 0; color: #fff; font-weight: bold; }
        .sidebar ul { list-style: none; padding: 0; margin: 0; flex-grow: 1; }
        .sidebar li { margin-bottom: 8px; }
        .sidebar a { display: block; padding: 12px 20px; color: var(--muted); text-decoration: none; border-radius: 8px; transition: background-color 0.3s ease, color 0.3s ease; font-weight: 500; }
        .sidebar a:hover { background-color: rgba(29, 155, 240, 0.1); color: #fff; }
        .sidebar a.active { background-color: var(--accent); color: #0b1220; font-weight: 700; box-shadow: 0 2px 10px rgba(29, 155, 240, 0.4); }
        body.sidebar-open .sidebar { transform: translateX(0); }
        body.sidebar-open { padding-left: 260px; }
        .admin-header { display: flex; align-items: center; padding: 15px 30px; background: var(--card); border-bottom: 1px solid rgba(255,255,255,0.07); position: sticky; top: 0; z-index: 100; }
        .burger-icon { cursor: pointer; margin-right: 20px; }
        .burger-icon svg { width: 24px; height: 24px; stroke: var(--muted); transition: stroke 0.3s; }
        .burger-icon:hover svg { stroke: #fff; }
        .page-wrapper { min-height: 100vh; transition: padding-left 0.3s ease; }
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
        table { width: 100%; border-collapse: collapse; color: #e6eef8; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid var(--glass); }
        th { background-color: rgba(255,255,255,0.05); font-weight: 700; color: #fff; }
        tr:hover { background-color: var(--glass); }
        .success-message { padding: 15px; background-color: rgba(92, 184, 92, 0.2); color: #5cb85c; border: 1px solid #5cb85c; border-radius: 8px; margin-bottom: 20px; }
        .confirm-bubble-overlay { position: fixed; inset: 0; background: rgba(15, 23, 36, 0.5); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); z-index: 1000; display: flex; align-items: center; justify-content: center; opacity: 0; visibility: hidden; transition: opacity 0.3s ease, visibility 0.3s ease; }
        .confirm-bubble-overlay.show { opacity: 1; visibility: visible; }
        .confirm-bubble-content { background: var(--card); padding: 24px 32px; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 8px 30px rgba(0,0,0,0.3); text-align: center; max-width: 400px; transform: scale(0.95); transition: transform 0.3s ease; }
        .confirm-bubble-overlay.show .confirm-bubble-content { transform: scale(1); }
        .confirm-bubble-content h4 { margin: 0 0 10px 0; font-size: 18px; color: #fff; }
        .confirm-bubble-content p { margin: 0 0 20px 0; color: var(--muted); font-size: 14px; }
        .confirm-bubble-actions { display: flex; gap: 12px; justify-content: center; }
        .confirm-bubble-actions button { padding: 8px 20px; border-radius: 8px; border: none; font-weight: 600; cursor: pointer; font-family: inherit; transition: background-color 0.3s; }
        .confirm-bubble-actions .btn-cancel { background: var(--glass); color: var(--muted); border: 1px solid rgba(255,255,255,0.1); }
        .confirm-bubble-actions .btn-cancel:hover { background: rgba(255,255,255,0.1); color: #fff; }
        .confirm-bubble-actions .btn-confirm { background: #d9534f; color: #fff; }
        .confirm-bubble-actions .btn-confirm:hover { background: #c9302c; }
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
            <li><a href="feedback.php">View Feedback</a></li>
            <li><a href="carousel.php" class="active">Manage Carousel</a></li>
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
            <h1>Manage Carousel Tips</h1>
        </header>

        <main class="main-content">
            <?php if (isset($_SESSION['admin_success'])): ?>
                <div class="success-message"><?php echo $_SESSION['admin_success']; unset($_SESSION['admin_success']); ?></div>
            <?php endif; ?>

            <div class="form-container">
                <h2>Add New Tip</h2>
                <form method="post" class="form-grid">
                    <div class="form-group">
                        <label for="text">Tip Text</label>
                        <textarea id="text" name="text" rows="3" required></textarea>
                    </div>
                    <button type="submit" name="add_tip">Add Tip</button>
                </form>
            </div>

            <div class="table-container">
                <h2>Existing Tips</h2>
                <table>
                    <thead>
                        <tr><th>ID</th><th>Tip Text</th><th>Created At</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if ($tips_result && $tips_result->num_rows > 0): ?>
                            <?php while($row = $tips_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td style="max-width: 500px; white-space: normal;"><?php echo htmlspecialchars($row['text']); ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?></td>
                                <td style="display: flex; gap: 8px;">
                                    <a href="edit_carousel.php?id=<?php echo $row['id']; ?>" style="color: var(--accent); text-decoration: none; font-weight: 600;">Edit</a>
                                    <a href="carousel.php?delete=<?php echo $row['id']; ?>" class="delete-btn" data-id="<?php echo $row['id']; ?>" style="color: #d9534f; text-decoration: none; font-weight: 600;">Delete</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" style="text-align: center; color: var(--muted);">No tips yet. Add one above!</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Confirmation Bubble for Deletion -->
    <div id="confirm-bubble" class="confirm-bubble-overlay">
        <div class="confirm-bubble-content">
            <h4>Delete Tip</h4>
            <p id="confirm-message">Are you sure you want to permanently delete this tip? This action cannot be undone.</p>
            <div class="confirm-bubble-actions">
                <button id="cancel-delete-btn" class="btn-cancel">Cancel</button>
                <button id="confirm-delete-btn" class="btn-confirm">Confirm Delete</button>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('burger-icon').addEventListener('click', function() {
            document.body.classList.toggle('sidebar-open');
        });

        // --- Delete Confirmation Modal Logic ---
        const confirmBubble = document.getElementById('confirm-bubble');
        const cancelBtn = document.getElementById('cancel-delete-btn');
        const confirmBtn = document.getElementById('confirm-delete-btn');
        const deleteButtons = document.querySelectorAll('.delete-btn');
        let deleteUrl = '';

        deleteButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                deleteUrl = button.href;
                confirmBubble.classList.add('show');
            });
        });

        cancelBtn.addEventListener('click', () => confirmBubble.classList.remove('show'));
        confirmBtn.addEventListener('click', () => { if (deleteUrl) window.location.href = deleteUrl; });
    </script>
</body>
</html>

<?php $conn->close(); ?>