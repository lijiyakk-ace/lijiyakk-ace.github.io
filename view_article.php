<?php
session_start();
require 'db.php';

// 1. Validate ID and fetch article details
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: articles.php?error=invalid_id');
    exit;
}

$id = (int)$_GET['id'];
$stmt = $conn->prepare("
    SELECT a.*
    FROM articles a 
    WHERE a.id = ? AND a.status = 'published'
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: articles.php?error=not_found');
    exit;
}

$article = $result->fetch_assoc();

// 2. Fetch other recent articles for the sidebar
$sidebar_articles = [];
$sidebar_stmt = $conn->prepare("SELECT id, title, image_url FROM articles WHERE status = 'published' AND id != ? ORDER BY created_at DESC LIMIT 4");
$sidebar_stmt->bind_param("i", $id);
$sidebar_stmt->execute();
$sidebar_result = $sidebar_stmt->get_result();
while ($row = $sidebar_result->fetch_assoc()) {
    $sidebar_articles[] = $row;
}

// Fetch the current user's avatar for the header
$user_avatar_header = null;
if (isset($_SESSION['user'])) {
    $username = $_SESSION['user'];
    $avatar_stmt = $conn->prepare("SELECT avatar FROM users WHERE username = ?");
    $avatar_stmt->bind_param("s", $username);
    $avatar_stmt->execute();
    $user_avatar_header = $avatar_stmt->get_result()->fetch_assoc()['avatar'] ?? null;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($article['title']); ?> - Travel Tales</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root{
          --bg:#0f1724;
          --card:#0b1220;
          --muted:#9aa4b2;
          --accent:#1d9bf0;
          --glass: rgba(255,255,255,0.03);
        }
        body {margin:0;font-family:'Montserrat',sans-serif;background:linear-gradient(180deg,var(--bg) 0%, #071027 60%);color:#e6eef8;}
        header{display:flex;justify-content:space-between;align-items:center;padding:20px 50px;background-color:var(--card);color:#fff;border-bottom:1px solid rgba(255,255,255,0.07);position:sticky;top:0;z-index:100;}
        header .logo{font-size:24px;font-weight:bold;}
        .header-right-group{display:flex;align-items:center;gap:35px;}
        .main-nav ul{list-style:none;margin:0;padding:0;display:flex;gap:35px;}
        .main-nav a{color:var(--muted);text-decoration:none;font-weight:500;font-size:15px;padding:5px 0;position:relative;transition:color 0.3s ease;}
        .main-nav a:hover{color:#fff;}
        .main-nav a.active{color:#fff;font-weight:700;}
        .main-nav a.active::after{content:'';position:absolute;bottom:-20px;left:0;width:100%;height:2px;background-color:var(--accent);}
        .hero{position:relative;height:500px;width:100%;background:no-repeat center center/cover;display:flex;align-items:flex-end;}
        /* Profile Dropdown */
        .profile-icon { display: inline-block; width: 40px; height: 40px; border-radius: 50%; background-color: var(--glass); cursor: pointer; display: flex; align-items: center; justify-content: center; }
        .profile-dropdown { position: relative; display: inline-block; }
        .dropdown-content { display: none; position: absolute; top: calc(100% + 10px); right: 0; background-color: var(--card); min-width: 250px; box-shadow: 0 4px 30px rgba(0,0,0,0.1); border: 1px solid rgba(255, 255, 255, 0.1); z-index: 10; border-radius: 8px; padding: 8px 0; }
        .dropdown-content::before { content: ''; position: absolute; top: -10px; right: 12px; border-width: 0 8px 10px 8px; border-style: solid; border-color: transparent transparent var(--card) transparent; }
        .dropdown-content a { color: #e6eef8; padding: 14px 20px; text-decoration: none; display: flex; align-items: center; gap: 12px; font-weight: 500; }
        .dropdown-content a:hover {background-color: rgba(29, 155, 240, 0.1)}
        .dropdown-header {
            padding: 14px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 8px;
        }
        .dropdown-header span { font-weight: 700; color: #fff; }
        .show { display:block; }
        .hero::after{content:'';position:absolute;inset:0;background:linear-gradient(to top, rgba(11,18,32,1) 0%, rgba(11,18,32,0) 60%);}
        .hero-content{position:relative;z-index:2;padding:40px 50px;max-width:1200px;margin:0 auto;width:100%;}
        .hero-content h1{font-size:48px;margin:0 0 10px 0;text-shadow:2px 2px 8px rgba(0,0,0,0.7);}
        .hero-content p{font-size:20px;color:var(--muted);max-width:700px;}
        .page-content-wrapper{display:flex;gap:30px;max-width:1200px;margin:0 auto;padding:40px 50px;align-items:flex-start;}
        .sidebar{flex:1;min-width:280px;max-width:320px;position:sticky;top:110px;}
        .sidebar h3{color:#fff;font-size:18px;margin-top:0;margin-bottom:15px;padding-bottom:10px;border-bottom:1px solid var(--glass);}
        .sidebar-item { display: flex; gap: 15px; align-items: center; margin-bottom: 15px; color: var(--muted); text-decoration: none; }
        .sidebar-item:hover .sidebar-item-title { color: var(--accent); }
        .sidebar-item-img { width: 80px; height: 60px; object-fit: cover; border-radius: 8px; }
        .sidebar-item-title { font-weight: 600; font-size: 14px; color: #e6eef8; transition: color 0.3s ease; }
        .main-article-content{flex:2.5;background:var(--card);padding:30px;border-radius:12px;border:1px solid rgba(255,255,255,0.07);}
        .article-meta{display:flex;align-items:center;gap:12px;margin-bottom:20px;padding-bottom:20px;border-bottom:1px solid var(--glass);}
        .author-avatar{width:48px;height:48px;border-radius:50%;object-fit:cover;}
        .author-details .author-name{font-weight:700;color:#fff;}
        .author-details .publish-date{font-size:13px;color:var(--muted);}
        .article-body{line-height:1.8;font-size:16px;color:#d1dce8;}
        .article-body h2{font-size:24px;margin-top:1.5em;margin-bottom:0.8em;color:#fff;border-left:3px solid var(--accent);padding-left:10px;}
        .article-body p{margin-bottom:1.2em;}
        .article-body img{max-width:100%;height:auto;border-radius:10px;margin:1em 0;}
        @media(max-width:992px){.page-content-wrapper{flex-direction:column;}.sidebar{position:static;width:100%;max-width:none;margin-bottom:30px;}}
    </style>
</head>
<body>

<!-- Header -->
<header>
    <div class="logo">Travel Tales</div>
    <div class="header-right-group">
        <nav class="main-nav">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="destinations.php">Destinations</a></li>
                <li><a href="forum.php">Forum</a></li>
                <li><a href="quiz.php">Quiz</a></li>
                <li><a href="blogs.php">Blogs</a></li>
                <li><a href="articles.php" class="active">Articles</a></li>
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
                    <div class="dropdown-header">
                        <span><?php echo htmlspecialchars($_SESSION['user']); ?></span>
                    </div>
                    <a href="profile.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        Profile
                    </a>
                    <a href="notifications.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-bell"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                        Notifications
                    </a>
                    <a href="support.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-help-circle"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                        Support
                    </a>
                    <a href="logout.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-log-out"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                        Logout
                    </a>
                </div>
            </div>
            <?php else: ?>
                <button onclick="window.location.href='login.php'" class="btn-secondary">Login</button>
                <button onclick="window.location.href='signup.php'" class="btn-primary">Sign Up</button>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- Hero Section -->
<div class="hero" style="background-image:url('<?php echo htmlspecialchars(ltrim($article['image_url'], '/')); ?>')">
    <div class="hero-content">
        <h1><?php echo htmlspecialchars($article['title']); ?></h1>
        <p><?php echo htmlspecialchars($article['summery']); ?></p>
    </div>
</div>

<!-- Main Content -->
<div class="page-content-wrapper">
    <main class="main-article-content">
        <div class="article-meta" style="justify-content: flex-end;">
            <div class="publish-date">Published on <?php echo date('F d, Y', strtotime($article['created_at'])); ?></div>
        </div>
        <div class="article-body">
            <?php 
                // Using nl2br to preserve line breaks.
                // Note: htmlspecialchars is removed to allow HTML tags from the admin editor to render.
                // For production, consider using a library like HTML Purifier to sanitize the content.
                echo nl2br($article['content']); 
            ?>
        </div>
    </main>

    <aside class="sidebar">
        <h3>More Articles</h3>
        <?php if (!empty($sidebar_articles)): ?>
            <?php foreach ($sidebar_articles as $item): ?>
                <a href="view_article.php?id=<?php echo $item['id']; ?>" class="sidebar-item">
                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="sidebar-item-img">
                    <div class="sidebar-item-title"><?php echo htmlspecialchars($item['title']); ?></div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color: var(--muted); font-size: 14px;">No other articles available.</p>
        <?php endif; ?>
    </aside>
</div>

<script>
    // Profile Dropdown JS
    const profileIcon = document.getElementById('profileIcon');
    if (profileIcon) {
        const profileDropdown = document.getElementById('profileDropdown');
        profileIcon.addEventListener('click', function(event) {
            event.stopPropagation();
            profileDropdown.classList.toggle('show');
        });
        window.addEventListener('click', function(event) {
            if (!profileIcon.contains(event.target) && !event.target.closest('.profile-dropdown')) {
                document.getElementById('profileDropdown').classList.remove('show');
            }
        });
    }
</script>
</body>
</html>
