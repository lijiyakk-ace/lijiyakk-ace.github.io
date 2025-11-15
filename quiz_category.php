<?php
session_start();
require 'db.php';

// 1. Get category from URL and validate
$category = $_GET['category'] ?? '';
if (empty($category)) {
    die("Category not specified.");
}

// 2. Fetch all quizzes for this category
// In the current schema, each category is one quiz. We'll find the first question's ID to represent the quiz.
$stmt = $conn->prepare("SELECT MIN(id) as id, category, COUNT(*) as question_count FROM quiz WHERE category = ? GROUP BY category");
$stmt->bind_param("s", $category);
$stmt->execute();
$result = $stmt->get_result();
$quizzes = $result->fetch_all(MYSQLI_ASSOC);

if (empty($quizzes)) {
    // Optional: Redirect or show a message if no quizzes are found for this category
    // For now, we'll allow the page to render with a "no quizzes" message.
}

// 3. Map categories to images for the hero section
$category_images = [
    'Geography' => 'img/geography.jpg',
    'History'   => 'img/history.jpg',
    'Culture'   => 'img/culture.jpg',
    'Nature'    => 'img/nature.jpg',
    'Food'      => 'img/food.jpg',
    'Travel'    => 'img/travel.jpg',
];
$hero_image = $category_images[$category] ?? 'img/default_quiz.jpg';

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
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?php echo htmlspecialchars($category); ?> Quizzes</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#0f1724;--card:#0b1220;--muted:#98a0b3;--accent:#1d9bf0;--glass:rgba(255,255,255,0.04);}
body{margin:0;font-family:'Montserrat',sans-serif;background:linear-gradient(180deg,#071026 0%,#081228 60%);color:#e6eef8;line-height:1.45;}
a{color:inherit;text-decoration:none}
header{display:flex;justify-content:space-between;align-items:center;padding:20px 50px;background-color:var(--card);border-bottom:1px solid rgba(255,255,255,0.07);position:sticky;top:0;z-index:100}
header .logo{font-size:24px;font-weight:bold}
.main-nav ul{list-style:none;display:flex;gap:35px;margin:0;padding:0}
.main-nav a{color:var(--muted);text-decoration:none;font-weight:500;font-size:15px;padding:5px 0;position:relative;transition:color 0.3s}
.main-nav a:hover{color:#fff}
.main-nav a.active{color:#fff;font-weight:700}
.main-nav a.active::after{content:'';position:absolute;bottom:-20px;left:0;width:100%;height:2px;background-color:var(--accent)}
header .auth-buttons button{margin-left:10px;padding:8px 20px;border:1px solid var(--muted);background-color:var(--glass);color:#fff;cursor:pointer;border-radius:4px;font-weight:500;font-family:'Montserrat',sans-serif;transition:0.3s}
/* Profile Dropdown */
.header-right-group { display: flex; align-items: center; gap: 35px; }
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
header .auth-buttons .btn-primary{background:linear-gradient(90deg,var(--accent),#3bb0ff);color:#021426;border:none}

/* Hero Section */
.hero {
    position: relative;
    height: 350px;
    background-image: url('<?php echo htmlspecialchars($hero_image); ?>');
    background-size: cover;
    background-position: center;
    display: flex;
    align-items: flex-end;
}
.hero::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(11,18,32,1) 0%, rgba(11,18,32,0) 70%);
}
.hero-content {
    position: relative;
    z-index: 2;
    padding: 40px 50px;
}
.hero-content h1 {
    font-size: 42px;
    margin: 0;
    text-shadow: 2px 2px 8px rgba(0,0,0,0.7);
}

/* Main Content */
.container{max-width:1100px;margin:0 auto;padding:32px 16px}
.breadcrumbs { margin-bottom: 20px; font-size: 14px; color: var(--muted); }
.breadcrumbs a { color: var(--muted); }
.breadcrumbs a:hover { color: #fff; }

/* Quiz Card Grid */
.quiz-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 24px;
}
.quiz-card {
    background: var(--card);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 12px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.quiz-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.3);
}
.quiz-card-image {
    height: 180px;
    background-size: cover;
    background-position: center;
}
.quiz-card-content {
    padding: 20px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}
.quiz-card h3 {
    margin: 0 0 10px 0;
    font-size: 18px;
}
.quiz-card p {
    margin: 0 0 15px 0;
    color: var(--muted);
    font-size: 14px;
    flex-grow: 1;
}
.quiz-card-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.quiz-card-meta .questions {
    font-size: 13px;
    color: var(--muted);
}
.btn-start {
    background: var(--accent);
    color: #041022;
    border: none;
    padding: 8px 16px;
    border-radius: 8px;
    font-weight: 700;
    cursor: pointer;
}
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
                <li><a href="forum.php">Forum</a></li>
                <li><a href="quiz.php" class="active">Quiz</a></li>
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

<div class="hero">
    <div class="hero-content">
        <h1><?php echo htmlspecialchars($category); ?> Quizzes</h1>
    </div>
</div>

<div class="container">
    <div class="breadcrumbs">
        <a href="quiz.php">Quizzes</a> &raquo; <span><?php echo htmlspecialchars($category); ?></span>
    </div>

    <div class="quiz-grid">
        <?php if (!empty($quizzes)): ?>
            <?php foreach ($quizzes as $quiz): ?>
                <div class="quiz-card">
                    <div class="quiz-card-image" style="background-image: url('<?php echo htmlspecialchars($hero_image); ?>');"></div>
                    <div class="quiz-card-content">
                        <h3><?php echo htmlspecialchars($quiz['category']); ?> Quiz</h3>
                        <p>Test your knowledge about the <?php echo strtolower(htmlspecialchars($quiz['category'])); ?> of the world.</p>
                        <div class="quiz-card-meta">
                            <span class="questions"><?php echo $quiz['question_count']; ?> Questions</span>
                            <a href="quiz_detail.php?id=<?php echo $quiz['id']; ?>" class="btn-start">Start Quiz</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No quizzes found in this category yet. Check back soon!</p>
        <?php endif; ?>
    </div>
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