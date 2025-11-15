<?php
require_once 'admin_auth_check.php'; // Secure this page
require '../db.php';

// Function to handle file uploads
function handle_upload($file_key, $upload_dir = 'uploads/articles/') {
    if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK) {
        if (!is_dir('../' . $upload_dir)) {
            mkdir('../' . $upload_dir, 0777, true);
        }
        $file = $_FILES[$file_key];
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_filename = uniqid('article_', true) . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;

        if (move_uploaded_file($file['tmp_name'], '../' . $upload_path)) {
            return $upload_path;
        }
    }
    return null;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_article'])) {
    $author_id = $_POST['author_id'];
    $title = $_POST['title'];
    $summery = $_POST['summery'];
    $content = $_POST['content'];
    $category = $_POST['category'];
    $status = $_POST['status'];
    $image_url = handle_upload('image_url');

    $stmt = $conn->prepare("INSERT INTO articles (author_id, title, summery, content, image_url, category, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $author_id, $title, $summery, $content, $image_url, $category, $status);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch existing articles
$articles_result = $conn->query("
    SELECT a.id, a.title, a.category, a.status, a.created_at, u.username as author_name 
    FROM articles a 
    LEFT JOIN users u ON a.author_id = u.id 
    ORDER BY a.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Admin - Manage Articles</title>
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
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .form-group { display: flex; flex-direction: column; }
        .form-group.full-width { grid-column: 1 / -1; }
        label { font-weight: 600; margin-bottom: 8px; font-size: 14px; color: var(--muted); }
        input, select, textarea { width: 100%; padding: 12px; background: var(--glass); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #e6eef8; font-family: 'Montserrat', sans-serif; font-size: 16px; box-sizing: border-box; }
        input:focus, select:focus, textarea:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 2px rgba(29, 155, 240, 0.3); }
        textarea { min-height: 120px; resize: vertical; }
        input[type="file"] { padding: 8px; }
        button[type="submit"] { grid-column: 1 / -1; padding: 12px 25px; background: var(--accent); color: #041022; border: none; border-radius: 8px; font-weight: 700; font-size: 16px; cursor: pointer; transition: background 0.3s; justify-self: start; }
        button[type="submit"]:hover { background: #4fbfff; }
        table { width: 100%; border-collapse: collapse; color: #e6eef8; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid var(--glass); }
        th { background-color: rgba(255,255,255,0.05); font-weight: 700; color: #fff; }
        tr:hover { background-color: var(--glass); }
        .status-badge { padding: 4px 8px; border-radius: 99px; font-size: 12px; font-weight: 700; text-transform: uppercase; }
        .status-published { background-color: rgba(92, 184, 92, 0.2); color: #5cb85c; }
        .status-draft { background-color: rgba(240, 173, 78, 0.2); color: #f0ad4e; }

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

        .confirm-bubble-actions button { padding: 8px 20px; border-radius: 8px; border: none; font-weight: 600; cursor: pointer; font-family: inherit; transition: background-color 0.3s; }
        .confirm-bubble-actions .btn-cancel { background: var(--glass); color: var(--muted); border: 1px solid rgba(255,255,255,0.1); }
        .confirm-bubble-actions .btn-cancel:hover { background: rgba(255,255,255,0.1); color: #fff; }
        .confirm-bubble-actions .btn-confirm { background: #d9534f; color: #fff; }
        .confirm-bubble-actions .btn-confirm:hover { background: #c9302c; }
        @media (max-width: 800px) {
            body.sidebar-open { padding-left: 0; }
            .sidebar { width: 220px; }
        }
    </style>
</head>
<body>
    <nav class="sidebar" aria-label="Admin navigation">
        <h3>Travel Tales Admin</h3>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="destinations.php">Manage Destinations</a></li>
            <li><a href="articles.php" class="active">Manage Articles</a></li>
            <li><a href="blog.php">Manage Blogs</a></li>
            <li><a href="manage_users.php">Manage Users</a></li>
            <li><a href="quiz.php">Manage Quiz</a></li>
            <li><a href="posts.php">Manage Forum</a></li>
            <li><a href="notifications.php">Manage Notifications</a></li>
            <li><a href="feedback.php">View Feedback</a></li>
            <li><a href="carousel.php">Manage Carousel</a></li>
            <li><a href="settings.php">Settings</a></li>
            <li><a href="logout.php" style="color: #f0ad4e;">Logout</a></li>
        </ul>
        <div style="margin-top: auto; text-align:center;">
            <a href="../index.php" style="font-size: 14px; color: var(--muted);">← Back to Main Site</a>
        </div>
    </nav>

    <div class="page-wrapper">
        <header class="admin-header">
            <div class="burger-icon" id="burger-icon" role="button" tabindex="0" aria-label="Toggle sidebar">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </div>
            <h1>Manage Articles</h1>
        </header>

        <main class="main-content">
            <div class="form-container">
                <h2>Add New Article</h2>
                <form method="post" enctype="multipart/form-data" class="form-grid">
                    <div class="form-group"><label for="title">Title</label><input type="text" id="title" name="title" required></div>
                    <div class="form-group"><label for="author_id">Author ID</label><input type="number" id="author_id" name="author_id" required></div>
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" required>
                            <option value="">Select a Category</option>
                            <option value="Adventure">Adventure</option>
                            <option value="Beach">Beach</option>
                            <option value="City Break">City Break</option>
                            <option value="Cultural">Cultural</option>
                            <option value="Historical">Historical</option>
                            <option value="Mountain">Mountain</option>
                            <option value="Nature">Nature</option>
                            <option value="Wildlife">Wildlife</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" required>
                            <option value="published">Published</option>
                            <option value="draft">Draft</option>
                        </select>
                    </div>
                    <div class="form-group full-width"><label for="summery">Summary</label><textarea id="summery" name="summery" rows="3" required></textarea></div>
                    <div class="form-group full-width"><label for="content">Full Content</label><textarea id="content" name="content" rows="8" required></textarea></div>
                    <div class="form-group"><label for="image_url">Article Image</label><input type="file" id="image_url" name="image_url" accept="image/*" required></div>
                    <button type="submit" name="add_article">Add Article</button>
                </form>
            </div>

            <div class="table-container">
                <h2>Existing Articles</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($articles_result && $articles_result->num_rows > 0): ?>
                            <?php while($row = $articles_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td><?php echo htmlspecialchars($row['author_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['category']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo htmlspecialchars($row['status']); ?>">
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                <td style="display: flex; gap: 8px;">
                                    <a href="edit_article.php?id=<?php echo $row['id']; ?>" style="color: var(--accent); text-decoration: none; font-weight: 600;">Edit</a>
                                    <a href="delete_article.php?id=<?php echo $row['id']; ?>" class="delete-btn" data-id="<?php echo $row['id']; ?>" data-title="<?php echo htmlspecialchars($row['title']); ?>" style="color: #d9534f; text-decoration: none; font-weight: 600;">Delete</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7" style="text-align: center;">No articles found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Confirmation Bubble for Deletion -->
    <div id="confirm-bubble" class="confirm-bubble-overlay">
        <div class="confirm-bubble-content">
            <h4>Delete Article</h4>
            <p id="confirm-message">Are you sure you want to permanently delete this article? This action cannot be undone.</p>
            <div class="confirm-bubble-actions">
                <button id="cancel-delete-btn" class="btn-cancel">Cancel</button>
                <button id="confirm-delete-btn" class="btn-confirm">Confirm Delete</button>
            </div>
        </div>
    </div>
    <script>
        // Sidebar toggle (safe: element exists)
        (function(){
            const burger = document.getElementById('burger-icon');
            if (!burger) return;
            const toggle = () => document.body.classList.toggle('sidebar-open');
            burger.addEventListener('click', toggle);
            burger.addEventListener('keydown', function(e){
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggle(); }
            });
        })();

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
                const articleId = button.dataset.id;
                const articleTitle = button.dataset.title;
                
                confirmMessage.innerHTML = `Are you sure you want to permanently delete the article <strong>"${articleTitle}"</strong>? This action cannot be undone.`;
                deleteUrl = `delete_article.php?id=${articleId}`;
                
                confirmBubble.classList.add('show');
            });
        });

        cancelBtn.addEventListener('click', () => confirmBubble.classList.remove('show'));
        confirmBtn.addEventListener('click', () => {
            if (deleteUrl) window.location.href = deleteUrl;
        });
    </script>
</body>
</html>