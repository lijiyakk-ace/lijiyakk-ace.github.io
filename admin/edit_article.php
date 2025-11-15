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

// 1. Validate ID and fetch article data
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid article ID.");
}

$id = (int)$_GET['id'];
$stmt = $conn->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Article not found.");
}
$article_data = $result->fetch_assoc();
$stmt->close();

// 2. Handle form submission for updating
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_article'])) {
    $author_id = $_POST['author_id'];
    $title = $_POST['title'];
    $summery = $_POST['summery'];
    $content = $_POST['content'];
    $category = $_POST['category'];
    $status = $_POST['status'];

    // Handle image upload - only update if a new file is provided
    $image_url = handle_upload('image_url') ?? $article_data['image_url'];

    $update_stmt = $conn->prepare("UPDATE articles SET author_id=?, title=?, summery=?, content=?, image_url=?, category=?, status=? WHERE id=?");
    $update_stmt->bind_param("issssssi", $author_id, $title, $summery, $content, $image_url, $category, $status, $id);
    
    if ($update_stmt->execute()) {
        header("Location: articles.php"); // Redirect back to the main articles admin page
        exit;
    } else {
        $error_message = "Error updating article. Please try again.";
    }
    $update_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Article</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root{--bg:#0f1724;--card:#0b1220;--muted:#9aa4b2;--accent:#1d9bf0;--glass:rgba(255,255,255,0.03);}
        body{margin:0;font-family:'Montserrat',sans-serif;background:linear-gradient(180deg,var(--bg) 0%, #071027 60%);color:#e6eef8;transition:padding-left 0.3s ease;}
        .sidebar{position:fixed;top:0;left:0;height:100%;width:260px;background:var(--card);padding:30px 20px;border-right:1px solid rgba(255,255,255,0.07);display:flex;flex-direction:column;z-index:200;transform:translateX(-100%);transition:transform 0.3s ease;box-sizing:border-box;}
        .sidebar h3{font-size:22px;text-align:center;margin:0 0 30px 0;color:#fff;font-weight:bold;}
        .sidebar ul{list-style:none;padding:0;margin:0;flex-grow:1;}
        .sidebar li{margin-bottom:8px;}
        .sidebar a{display:block;padding:12px 20px;color:var(--muted);text-decoration:none;border-radius:8px;transition:background-color 0.3s ease,color 0.3s ease;font-weight:500;}
        .sidebar a:hover{background-color:rgba(29,155,240,0.1);color:#fff;}
        .sidebar a.active{background-color:var(--accent);color:#0b1220;font-weight:700;box-shadow:0 2px 10px rgba(29,155,240,0.4);}
        body.sidebar-open .sidebar{transform:translateX(0);}
        body.sidebar-open{padding-left:260px;}
        .admin-header{display:flex;align-items:center;padding:15px 30px;background:var(--card);border-bottom:1px solid rgba(255,255,255,0.07);position:sticky;top:0;z-index:100;}
        .burger-icon{cursor:pointer;margin-right:20px;}
        .burger-icon svg{width:24px;height:24px;stroke:var(--muted);transition:stroke 0.3s;}
        .burger-icon:hover svg{stroke:#fff;}
        .page-wrapper{min-height:100vh;transition:padding-left 0.3s ease;}
        .main-content{padding:40px;}
        h1,h2{font-size:28px;margin-top:0;margin-bottom:20px;border-left:4px solid var(--accent);padding-left:15px;color:#fff;}
        .form-container{background:var(--card);padding:30px;border-radius:12px;border:1px solid rgba(255,255,255,0.07);margin-bottom:40px;}
        .form-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:20px;}
        .form-group{display:flex;flex-direction:column;}
        .form-group.full-width{grid-column:1 / -1;}
        label{font-weight:600;margin-bottom:8px;font-size:14px;color:var(--muted);}
        input,select,textarea{width:100%;padding:12px;background:var(--glass);border:1px solid rgba(255,255,255,0.1);border-radius:8px;color:#e6eef8;font-family:'Montserrat',sans-serif;font-size:16px;box-sizing:border-box;}
        input:focus,select:focus,textarea:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 2px rgba(29,155,240,0.3);}
        textarea{min-height:120px;resize:vertical;}
        button[type="submit"]{grid-column:1 / -1;padding:12px 25px;background:var(--accent);color:#041022;border:none;border-radius:8px;font-weight:700;font-size:16px;cursor:pointer;transition:background 0.3s;justify-self:start;}
        button[type="submit"]:hover{background:#4fbfff;}
        .cancel-link{justify-self:start;grid-column:1 / -1;margin-top:-10px;color:var(--muted);text-decoration:none;font-size:14px;}
        .cancel-link:hover{color:#fff;}
        .current-image{font-size:12px;color:var(--muted);margin-top:5px;}
        .current-image img{max-width:100px;max-height:60px;border-radius:4px;margin-right:10px;vertical-align:middle;}
    </style>
</head>
<body>
    <div class="sidebar">
        <h3>Travel Tales Admin</h3>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="destinations.php">Manage Destinations</a></li>
            <li><a href="articles.php" class="active">Manage Articles</a></li>
            <li><a href="blog.php">Manage Blogs</a></li>
            <li><a href="manage_users.php">Manage Users</a></li>
            <li><a href="quiz.php">Manage Quiz</a></li>
            <li><a href="posts.php">Manage Forum</a></li>
            <li><a href="feedback.php">View Feedback</a></li>
            <li><a href="notifications.php">Manage Notifications</a></li>
            <li><a href="carousel.php">Manage Carousel</a></li>
            <li><a href="settings.php">Settings</a></li>
        </ul>
        <div><a href="../index.php" style="text-align: center; font-size: 14px;">&larr; Back to Main Site</a></div>
    </div>

    <div class="page-wrapper">
        <header class="admin-header">
            <div class="burger-icon" id="burger-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg></div>
            <h1>Edit Article</h1>
        </header>

        <main class="main-content">
            <div class="form-container">
                <h2>Editing Article ID: <?= $id ?></h2>
                <?php if (isset($error_message)): ?>
                    <p style="color: red;"><?= $error_message ?></p>
                <?php endif; ?>
                <form method="POST" enctype="multipart/form-data" class="form-grid">
                    <div class="form-group"><label for="title">Title</label><input type="text" id="title" name="title" value="<?= htmlspecialchars($article_data['title']) ?>" required></div>
                    <div class="form-group"><label for="author_id">Author ID</label><input type="number" id="author_id" name="author_id" value="<?= htmlspecialchars($article_data['author_id']) ?>" required></div>
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" required>
                            <?php 
                            $categories = ["Adventure", "Beach", "City Break", "Cultural", "Historical", "Mountain", "Nature", "Wildlife"];
                            foreach ($categories as $cat) {
                                $selected = ($article_data['category'] === $cat) ? 'selected' : '';
                                echo "<option value='$cat' $selected>$cat</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" required>
                            <option value="published" <?= ($article_data['status'] === 'published') ? 'selected' : '' ?>>Published</option>
                            <option value="draft" <?= ($article_data['status'] === 'draft') ? 'selected' : '' ?>>Draft</option>
                        </select>
                    </div>
                    <div class="form-group full-width"><label for="summery">Summary</label><textarea id="summery" name="summery" rows="3" required><?= htmlspecialchars($article_data['summery']) ?></textarea></div>
                    <div class="form-group full-width"><label for="content">Full Content</label><textarea id="content" name="content" rows="8" required><?= htmlspecialchars($article_data['content']) ?></textarea></div>
                    <div class="form-group">
                        <label for="image_url">Article Image (leave blank to keep current)</label>
                        <input type="file" id="image_url" name="image_url" accept="image/*">
                        <?php if ($article_data['image_url']): ?>
                            <div class="current-image">Current: <img src="../<?= htmlspecialchars($article_data['image_url']); ?>" alt="Article Image"> <?= basename($article_data['image_url']); ?></div>
                        <?php endif; ?>
                    </div>
                    <button type="submit" name="update_article">Update Article</button>
                    <a href="articles.php" class="cancel-link">Cancel</a>
                </form>
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