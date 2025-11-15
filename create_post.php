<?php
session_start();
require 'db.php';

// 1. Redirect if user is not logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$message = '';

// 2. Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_post'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $author_username = $_SESSION['user'];
    $destination_id = isset($_POST['destination_id']) ? intval($_POST['destination_id']) : null;

    if (!empty($title) && !empty($content)) {
        try {
            $stmt = $conn->prepare("INSERT INTO forum (destination_id, author_username, title, content, created_at) 
                                    VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("isss", $destination_id, $author_username, $title, $content);
            
            if ($stmt->execute()) {
                $new_post_id = $conn->insert_id;
                // 3. Redirect to the new post
                header('Location: forum_post.php?id=' . $new_post_id);
                exit;
            } else {
                $message = "Error: Could not create the post. Please try again.";
            }
        } catch (mysqli_sql_exception $e) {
            $message = "Database error: " . $e->getMessage();
        }
    } else {
        $message = "Title and content cannot be empty.";
    }
}

// Fetch the current user's avatar for the header
$user_avatar_header = null;
if (isset($_SESSION['user'])) {
    $username = $_SESSION['user'];
    $avatar_stmt = $conn->prepare("SELECT avatar FROM users WHERE username = ?");
    $avatar_stmt->bind_param("s", $username);
    $avatar_stmt->execute();
    $avatar_result = $avatar_stmt->get_result();
    $user_data = $avatar_result->fetch_assoc();
    $user_avatar_header = $user_data['avatar'] ?? null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create New Discussion - Travel Forum</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
<style>
    :root{ --bg:#1e293b; /* slate-700 */ --card:#0b1220; --muted:#9aa4b2; --accent:#1d9bf0; --glass: rgba(255,255,255,0.03); }
    body { margin: 0; font-family: 'Montserrat', sans-serif; background:linear-gradient(180deg, var(--bg) 0%, #0f172a 100%); color: #e6eef8; }
    a { text-decoration: none; color: var(--accent); }
    a:hover { text-decoration: underline; }

    /* Header Styles from index.php */
    header { display: flex; justify-content: space-between; align-items: center; padding: 20px 50px; background-color: var(--card); color: #fff; border-bottom: 1px solid rgba(255,255,255,0.07); position: sticky; top: 0; z-index: 100; }
    header .logo { font-size: 24px; font-weight: bold; }
    .header-right-group { display: flex; align-items: center; gap: 35px; }
    .main-nav ul { list-style: none; margin: 0; padding: 0; display: flex; gap: 35px; }
    .main-nav a { color: var(--muted); text-decoration: none; font-weight: 500; font-size: 15px; padding: 5px 0; position: relative; transition: color 0.3s ease; }
    .main-nav a:hover { color: #fff; }
    .main-nav a.active { color: #fff; font-weight: 700; }
    .main-nav a.active::after { content: ''; position: absolute; bottom: -20px; left: 0; width: 100%; height: 2px; background-color: var(--accent); }

    /* Auth Buttons & Profile Dropdown */
    header .auth-buttons button { margin-left: 10px; padding: 8px 20px; border: 1px solid var(--muted); background-color: var(--glass); color: #fff; cursor: pointer; border-radius: 4px; font-weight: 500; font-family: 'Montserrat', sans-serif; transition: background-color 0.3s ease; }
    header .auth-buttons .btn-primary { background: linear-gradient(90deg,var(--accent),#3bb0ff); color: #021426; border: none; }
    header .auth-buttons button:hover { background-color: rgba(255,255,255,0.1); }
    .profile-icon { display: inline-block; width: 40px; height: 40px; border-radius: 50%; background-color: var(--glass); cursor: pointer; display: flex; align-items: center; justify-content: center; }
    .profile-dropdown { position: relative; display: inline-block; }
    .dropdown-content { display: none; position: absolute; top: calc(100% + 10px); right: 0; background-color: var(--card); min-width: 220px; box-shadow: 0 4px 30px rgba(0,0,0,0.1); border: 1px solid rgba(255, 255, 255, 0.1); z-index: 10; border-radius: 8px; padding: 8px 0; }
    .dropdown-content::before { content: ''; position: absolute; top: -10px; right: 12px; border-width: 0 8px 10px 8px; border-style: solid; border-color: transparent transparent var(--card) transparent; }
    .dropdown-content a { color: #e6eef8; padding: 12px 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; font-weight: 500; }
    .dropdown-content a:hover {background-color: rgba(29, 155, 240, 0.1)}
    .dropdown-header { padding: 12px 16px; border-bottom: 1px solid rgba(255, 255, 255, 0.1); margin-bottom: 8px; }
    .show { display:block; }

    /* Main Content Styles */
    .container { max-width: 800px; margin: 40px auto; padding: 0 50px; }
    .breadcrumbs { margin-bottom: 20px; font-size: 14px; color: var(--muted); }
    .breadcrumbs a { color: var(--muted); }
    .breadcrumbs a:hover { color: #fff; }
    .form-container { background: var(--card); padding: 30px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.07); }
    .form-container h1 { font-size: 28px; margin-top: 0; margin-bottom: 20px; border-left: 4px solid var(--accent); padding-left: 15px; color: #fff; }
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-weight: 600; margin-bottom: 8px; font-size: 16px; }
    .form-group input, .form-group textarea, .form-group select {
        width: 100%;
        padding: 12px;
        background: var(--glass);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 8px;
        color: #e6eef8;
        font-family: 'Montserrat', sans-serif;
        font-size: 16px;
        box-sizing: border-box; /* Ensure padding doesn't affect width */
    }
    .form-group select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%239aa4b2' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.5rem center;
        background-repeat: no-repeat;
        background-size: 1.5em 1.5em;
        padding-right: 2.5rem;
    }
    .form-group option { background-color: var(--card); }
    .form-group textarea { min-height: 200px; resize: vertical; }
    .btn-submit { padding: 12px 25px; background: var(--accent); color: #041022; border: none; border-radius: 8px; font-weight: 700; font-size: 16px; cursor: pointer; transition: background 0.3s; }
    .btn-submit:hover { background: #4fbfff; }
    .message { color: #d9534f; background-color: rgba(217, 83, 79, 0.1); border: 1px solid rgba(217, 83, 79, 0.5); padding: 10px; border-radius: 8px; margin-bottom: 20px; }
</style>
</head>
<body>

<header>
    <div class="logo">Travel Tales</div>
    <div class="header-right-group">
        <nav class="main-nav">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="destinations.php">Destinations</a></li>
                <li><a href="forum.php" class="active">Forum</a></li>
                <li><a href="quiz.php">Quiz</a></li>
                <li><a href="blogs.php">Blogs</a></li>
                <li><a href="articles.php">Articles</a></li>
            </ul>
        </nav>
        <div class="auth-buttons">
            <?php if(isset($_SESSION['user'])): ?>
            <div class="profile-dropdown">
                <div id="profileIcon" class="profile-icon">
                    <?php if (!empty($user_avatar_header)): ?>
                        <img src="<?php echo htmlspecialchars($user_avatar_header); ?>" alt="User Avatar" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">
                    <?php else: ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    <?php endif; ?>
                </div>
                <div id="profileDropdown" class="dropdown-content">
                    <div class="dropdown-header"><?php echo htmlspecialchars($_SESSION['user']); ?></div>
                    <a href="profile.php">Profile</a>
                    <a href="logout.php">Logout</a>
                </div>
            </div>
            <?php else: ?>
                <button onclick="window.location.href='login.php'">Login</button>
                <button class="btn-primary" onclick="window.location.href='signup.php'">Sign Up</button>
            <?php endif; ?>
        </div>
    </div>
</header>

<div class="container">
    <div class="breadcrumbs">
        <a href="forum.php">Forum</a> &raquo; <span>Create New Discussion</span>
    </div>

    <div class="form-container">
        <h1>Start a New Discussion</h1>
        <?php if (!empty($message)): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <form method="post" action="create_post.php">
            <div class="form-group">
                <label for="destination_id">Destination (optional)</label>
                <input type="number" id="destination_id" name="destination_id" placeholder="Enter destination ID if applicable">
            </div>
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" placeholder="Enter a descriptive title for your post" required>
            </div>
            <div class="form-group">
                <label for="content">Your Message</label>
                <textarea id="content" name="content" placeholder="Share your story, ask a question, or give advice..." required></textarea>
            </div>
            <button type="submit" name="create_post" class="btn-submit">Create Post</button>
        </form>
    </div>
</div>

<!-- Footer -->
<footer class="site-footer">
    <div class="footer-main">
        <div class="footer-column">
            <h4>Support</h4>
            <ul>
                <li><a href="#">Help Center</a></li>
                <li><a href="#">Live Chat</a></li>
                <li><a href="#">Contact Us</a></li>
                <li><a href="#">FAQ</a></li>
            </ul>
        </div>
        <div class="footer-column">
            <h4>Company</h4>
            <ul>
                <li><a href="#">About Us</a></li>
                <li><a href="#">Careers</a></li>
                <li><a href="#">Blog</a></li>
                <li><a href="#">Press</a></li>
                <li><a href="admin/login.php">Admin</a></li>
            </ul>
        </div>
        <div class="footer-column">
            <h4>Work with us</h4>
            <ul>
                <li><a href="#">As a Supplier</a></li>
                <li><a href="#">As a Content Creator</a></li>
                <li><a href="#">As an Affiliate Partner</a></li>
            </ul>
        </div>
        <div class="footer-column">
            <h4>Follow Us</h4>
            <div class="social-links">
                <a href="#" title="Instagram"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.-12 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.85s-.011 3.584-.069 4.85c-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07s-3.584-.012-4.85-.07c-3.252-.148-4.771-1.691-4.919-4.919-.058-1.265-.069-1.645-.069-4.85s.011-3.584.069-4.85c.149-3.225 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.85-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948s.014 3.667.072 4.947c.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072s3.667-.014 4.947-.072c4.358-.2 6.78-2.618 6.98-6.98.059-1.281.073-1.689.073-4.948s-.014-3.667-.072-4.947c-.2-4.358-2.618-6.78-6.98-6.98C15.667.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.88 1.44 1.44 0 000-2.88z"/></svg></a>
                <a href="#" title="X (Twitter)"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616v.064c0 2.298 1.634 4.212 3.793 4.649-.65.177-1.354.23-2.06.088.62 1.924 2.413 3.32 4.543 3.358-1.732 1.359-3.92 2.169-6.29 2.169-.409 0-.812-.023-1.21-.07 2.236 1.434 4.893 2.271 7.734 2.271 9.284 0 14.376-7.699 14.005-14.402.995-.718 1.858-1.612 2.543-2.639z"/></svg></a>
                <a href="#" title="YouTube"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg></a>
            </div>
            <h4 style="margin-top: 20px;">Get the app</h4>
        </div>
    </div>
    <div class="footer-bottom">
        <span>&copy; <?php echo date("Y"); ?> Travel Tales. All rights reserved.</span>
        <div class="legal-links">
            <a href="#">Imprint</a>
            <a href="#">Terms & Conditions</a>
            <a href="#">Privacy Statement</a>
        </div>
        <div class="social-links">
            <a href="#" title="Facebook"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/></svg></a>
            <a href="#" title="Instagram"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.85s-.011 3.584-.069 4.85c-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07s-3.584-.012-4.85-.07c-3.252-.148-4.771-1.691-4.919-4.919-.058-1.265-.069-1.645-.069-4.85s.011-3.584.069-4.85c.149-3.225 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.85-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948s.014 3.667.072 4.947c.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072s3.667-.014 4.947-.072c4.358-.2 6.78-2.618 6.98-6.98.059-1.281.073-1.689.073-4.948s-.014-3.667-.072-4.947c-.2-4.358-2.618-6.78-6.98-6.98C15.667.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.88 1.44 1.44 0 000-2.88z"/></svg></a>
            <a href="#" title="Twitter"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616v.064c0 2.298 1.634 4.212 3.793 4.649-.65.177-1.354.23-2.06.088.62 1.924 2.413 3.32 4.543 3.358-1.732 1.359-3.92 2.169-6.29 2.169-.409 0-.812-.023-1.21-.07 2.236 1.434 4.893 2.271 7.734 2.271 9.284 0 14.376-7.699 14.005-14.402.995-.718 1.858-1.612 2.543-2.639z"/></svg></a>
        </div>
    </div>
</footer>

<script>
const profileIcon = document.getElementById('profileIcon');
if (profileIcon) {
    const profileDropdown = document.getElementById('profileDropdown');
    profileIcon.addEventListener('click', (event) => {
        event.stopPropagation();
        profileDropdown.classList.toggle('show');
    });
    window.addEventListener('click', (e) => {
        if (!profileIcon.contains(e.target) && !profileDropdown.contains(e.target)) {
            profileDropdown.classList.remove('show');
        }
    });
}
</script>
</body>
</html>
