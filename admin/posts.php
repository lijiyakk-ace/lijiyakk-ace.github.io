<?php
require_once 'admin_auth_check.php'; // Secure this page
require '../db.php';

// --- Handle Deletion ---
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $post_id_to_delete = (int)$_GET['delete'];

    // Check if the post to be deleted is a main post (has replies)
    $check_stmt = $conn->prepare("SELECT parent_id FROM forum WHERE id = ?");
    $check_stmt->bind_param("i", $post_id_to_delete);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $post = $result->fetch_assoc();

    // If it's a main post (parent_id is NULL), delete all its replies first
    if ($post && $post['parent_id'] === null) {
        $delete_replies_stmt = $conn->prepare("DELETE FROM forum WHERE parent_id = ?");
        $delete_replies_stmt->bind_param("i", $post_id_to_delete);
        $delete_replies_stmt->execute();
        $delete_replies_stmt->close();
    }

    // Now, delete the post/reply itself
    $delete_post_stmt = $conn->prepare("DELETE FROM forum WHERE id = ?");
    $delete_post_stmt->bind_param("i", $post_id_to_delete);
    $delete_post_stmt->execute();
    $delete_post_stmt->close();

    header("Location: posts.php?success=deleted");
    exit;
}

// --- Fetch all forum posts and replies ---
$posts_result = $conn->query("SELECT * FROM forum ORDER BY created_at DESC");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Forum Posts</title>
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
        .table-container { background: var(--card); padding: 30px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.07); margin-bottom: 40px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; color: #e6eef8; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid var(--glass); white-space: nowrap; }
        th { background-color: rgba(255,255,255,0.05); font-weight: 700; color: #fff; }
        tr:hover { background-color: var(--glass); }
        .content-preview { max-width: 400px; white-space: normal; word-break: break-word; }
        .post-type-badge { padding: 4px 8px; border-radius: 99px; font-size: 12px; font-weight: 700; text-transform: uppercase; }
        .type-post { background-color: rgba(29, 155, 240, 0.2); color: var(--accent); }
        .type-reply { background-color: rgba(92, 184, 92, 0.2); color: #5cb85c; }
        .actions a { color: #d9534f; text-decoration: none; font-weight: 600; }
        .actions a:hover { text-decoration: underline; }

        /* Confirmation Bubble Styles */
        .confirm-bubble-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 36, 0.5);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        .confirm-bubble-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        .confirm-bubble-content {
            background: var(--card);
            padding: 24px 32px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 30px rgba(0,0,0,0.3);
            text-align: center;
            max-width: 400px;
            transform: scale(0.95);
            transition: transform 0.3s ease;
        }
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
            <li><a href="posts.php" class="active">Manage Forum</a></li>
            <li><a href="notifications.php">Manage Notifications</a></li>
            <li><a href="feedback.php">View Feedback</a></li>
            <li><a href="carousel.php">Manage Carousel</a></li>
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
            <h1>Manage Forum</h1>
        </header>

        <main class="main-content">
            <div class="table-container">
                <h2>All Discussions & Replies</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Title / Content</th>
                            <th>Author</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($posts_result->num_rows > 0): ?>
                            <?php while($row = $posts_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td>
                                    <?php if ($row['parent_id'] === null): ?>
                                        <span class="post-type-badge type-post">Post</span>
                                    <?php else: ?>
                                        <span class="post-type-badge type-reply">Reply</span>
                                    <?php endif; ?>
                                </td>
                                <td class="content-preview">
                                    <?php
                                        $post_link_id = $row['parent_id'] ?? $row['id'];
                                        $display_text = $row['title'] ?? substr($row['content'], 0, 70) . '...';
                                    ?>
                                    <a href="../forum_post.php?id=<?php echo $post_link_id; ?>" target="_blank" style="color: #e6eef8; font-weight: 500;">
                                        <?php echo htmlspecialchars($display_text); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($row['author_username']); ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?></td>
                                <td class="actions">
                                    <a href="posts.php?delete=<?php echo $row['id']; ?>" class="delete-btn" data-id="<?php echo $row['id']; ?>" data-type="<?php echo ($row['parent_id'] === null) ? 'post' : 'reply'; ?>">Delete</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="text-align: center; color: var(--muted);">No forum posts found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Confirmation Bubble for Deletion -->
    <div id="confirm-bubble" class="confirm-bubble-overlay">
        <div class="confirm-bubble-content">
            <h4>Delete Post/Reply</h4>
            <p id="confirm-message">Are you sure you want to permanently delete this? This action cannot be undone.</p>
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
        const confirmMessage = document.getElementById('confirm-message');
        const cancelBtn = document.getElementById('cancel-delete-btn');
        const confirmBtn = document.getElementById('confirm-delete-btn');
        const deleteButtons = document.querySelectorAll('.delete-btn');
        let deleteUrl = '';

        deleteButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault(); // Prevent the link from navigating immediately
                const postId = button.dataset.id;
                const postType = button.dataset.type;
                
                let message = "Are you sure you want to permanently delete this reply? This action cannot be undone.";
                if (postType === 'post') {
                    message = "Are you sure you want to permanently delete this discussion? This will also delete all associated replies and cannot be undone.";
                }
                confirmMessage.innerHTML = message;
                deleteUrl = `posts.php?delete=${postId}`;
                confirmBubble.classList.add('show');
            });
        });

        cancelBtn.addEventListener('click', () => confirmBubble.classList.remove('show'));
        confirmBtn.addEventListener('click', () => { if (deleteUrl) window.location.href = deleteUrl; });
    </script>
</body>
</html>