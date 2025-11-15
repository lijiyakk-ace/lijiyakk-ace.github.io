<?php
session_start();
require 'db.php';

// 1. Validate and get Post ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: forum.php');
    exit;
}
$post_id = (int)$_GET['id'];

// 2. Fetch the main post
$stmt = $conn->prepare("
SELECT f.*, u.avatar,
           (SELECT COUNT(*) FROM forum WHERE author_username = f.author_username) as post_count,
           u.created_at as member_since
    FROM forum f
    JOIN users u ON f.author_username = u.username
    WHERE f.id = ? AND f.parent_id IS NULL
");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: forum.php');
    exit;
}
$post = $result->fetch_assoc();

// 3. Fetch replies (all rows with parent_id = this post id)
$replies = [];
$reply_stmt = $conn->prepare("    
    SELECT r.*, u.avatar, u.created_at as member_since,
           (SELECT COUNT(*) FROM forum WHERE author_username = r.author_username) as post_count
    FROM forum r   
    JOIN users u ON r.author_username = u.username
    WHERE r.parent_id = ?
    ORDER BY r.created_at ASC
");
$reply_stmt->bind_param("i", $post_id);
$reply_stmt->execute();
$reply_result = $reply_stmt->get_result();
while ($row = $reply_result->fetch_assoc()) {
    $replies[] = $row;
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
<title><?php echo htmlspecialchars($post['title']); ?> - Travel Forum</title>
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

    /* Main Content */
    .container { max-width: 900px; margin: 40px auto; padding: 0 50px; }
    .breadcrumbs { margin-bottom: 20px; font-size: 14px; color: var(--muted); }
    .breadcrumbs a { color: var(--muted); }
    .breadcrumbs a:hover { color: #fff; }

    .post-header h1 { font-size: 28px; margin-top: 0; margin-bottom: 20px; color: #fff; line-height: 1.4; }

    .post-container { display: flex; gap: 20px; background: var(--card); padding: 20px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.07); margin-bottom: 25px; }
    .author-info { flex: 0 0 150px; text-align: center; }
    .author-info .avatar { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid var(--glass); margin-bottom: 10px; }
    .author-info .username { font-weight: 700; color: #fff; font-size: 16px; }
    .author-info .meta { font-size: 12px; color: var(--muted); margin-top: 5px; }

    /* Post Body */
    .post-body { flex: 1; }
    .post-meta { font-size: 13px; color: var(--muted); margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid var(--glass); }
    .post-content { line-height: 1.8; font-size: 16px; color: #d1dce8; }

    .reply-form-container { background: var(--card); padding: 30px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.07); }
    .reply-form-container h3 { font-size: 20px; margin-top: 0; margin-bottom: 15px; border-left: 4px solid var(--accent); padding-left: 15px; color: #fff; }
    .reply-form-container textarea { width: 100%; min-height: 120px; padding: 12px; background: var(--glass); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #e6eef8; font-family: 'Montserrat', sans-serif; font-size: 16px; resize: vertical; margin-bottom: 15px; }
    .btn-submit { padding: 10px 20px; background: var(--accent); color: #041022; border: none; border-radius: 8px; font-weight: 700; font-size: 16px; cursor: pointer; transition: background 0.3s; }
    .reply-form-container textarea:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 2px rgba(29, 155, 240, 0.3); }
    .btn-submit:hover { background: #4fbfff; }
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
                    <?php if ($user_avatar_header): ?>
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
            <?php endif; ?>
        </div>
    </div>
</header>

<div class="container">
    <div class="breadcrumbs">
        <a href="forum.php">Forum</a> &raquo; <span><?php echo htmlspecialchars($post['title']); ?></span>
    </div>

    <div class="post-header">
        <h1><?php echo htmlspecialchars($post['title']); ?></h1>
    </div>

    <!-- Original Post -->
    <div class="post-container">
        <div class="author-info">
            <img src="<?php echo htmlspecialchars($post['avatar'] ?? 'img/default_avatar.png'); ?>" alt="<?php echo htmlspecialchars($post['author_username']); ?>'s avatar" class="avatar">
            <div class="username"><?php echo htmlspecialchars($post['author_username']); ?></div>
            <div class="meta">Posts: <?php echo $post['post_count']; ?></div>
            <div class="meta">Joined: <?php echo date('M Y', strtotime($post['member_since'])); ?></div>
        </div>
        <div class="post-body">
            <div class="post-meta">
                Posted on <?php echo date('F d, Y \a\t h:i A', strtotime($post['created_at'])); ?>
            </div>
            <div class="post-content">
                <?php echo nl2br(htmlspecialchars($post['content'])); ?>
            </div>
        </div>
    </div>

    <!-- Replies -->
    <?php foreach ($replies as $reply): ?>
    <div class="post-container">
        <div class="author-info">
            <img src="<?php echo htmlspecialchars($reply['avatar'] ?? 'img/default_avatar.png'); ?>" alt="<?php echo htmlspecialchars($reply['author_username']); ?>'s avatar" class="avatar">
            <div class="username"><?php echo htmlspecialchars($reply['author_username']); ?></div>
            <div class="meta">Posts: <?php echo $reply['post_count']; ?></div>
            <div class="meta">Joined: <?php echo date('M Y', strtotime($reply['member_since'])); ?></div>
        </div>
        <div class="post-body">
            <div class="post-meta">
                Posted on <?php echo date('F d, Y \a\t h:i A', strtotime($reply['created_at'])); ?>
            </div>
            <div class="post-content">
                <?php echo nl2br(htmlspecialchars($reply['content'])); ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Reply Form -->
    <?php if (isset($_SESSION['user'])): ?>
    <div class="reply-form-container">
        <h3>Post a Reply</h3>
        <form action="add_reply.php" method="post">
            <textarea name="content" placeholder="Write your reply here..." required></textarea>
            <input type="hidden" name="parent_id" value="<?php echo $post_id; ?>">
            <button type="submit" name="add_reply" class="btn-submit">Submit Reply</button>
        </form>
    </div>
    <?php else: ?>
    <div class="reply-form-container" style="text-align: center;">
        <p><a href="login.php">Log in</a> to post a reply.</p>
    </div>
    <?php endif; ?>
</div>

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
