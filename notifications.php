<?php
session_start();
require 'db.php';

// Redirect if user is not logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Fetch all notifications from the database, newest first
$notifications_result = $conn->query("SELECT * FROM notifications ORDER BY created_at DESC");

// Fetch the current user's avatar for the header
$user_avatar_header = null;
$username = $_SESSION['user'];
$avatar_stmt = $conn->prepare("SELECT avatar FROM users WHERE username = ?");
$avatar_stmt->bind_param("s", $username);
$avatar_stmt->execute();
$user_avatar_header = $avatar_stmt->get_result()->fetch_assoc()['avatar'] ?? null;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Notifications - Travel Tales</title>
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
        }
        a { text-decoration: none; color: var(--accent); }
        a:hover { text-decoration: underline; }

        /* Header Styles */
        header { display: flex; justify-content: space-between; align-items: center; padding: 20px 50px; background-color: var(--card); color: #fff; border-bottom: 1px solid rgba(255,255,255,0.07); position: sticky; top: 0; z-index: 100; }
        header .logo { font-size: 24px; font-weight: bold; }
        .header-right-group { display: flex; align-items: center; gap: 35px; }
        .main-nav ul { list-style: none; margin: 0; padding: 0; display: flex; gap: 35px; }
        .main-nav a { color: var(--muted); text-decoration: none; font-weight: 500; font-size: 15px; padding: 5px 0; position: relative; transition: color 0.3s ease; }
        .main-nav a:hover { color: #fff; }
        .main-nav a.active { color: #fff; font-weight: 700; }
        .main-nav a.active::after { content: ''; position: absolute; bottom: -20px; left: 0; width: 100%; height: 2px; background-color: var(--accent); }

        /* Profile Dropdown */
        .profile-icon { display: inline-block; width: 40px; height: 40px; border-radius: 50%; background-color: var(--glass); cursor: pointer; display: flex; align-items: center; justify-content: center; }
        .profile-dropdown { position: relative; display: inline-block; }
        .dropdown-content { display: none; position: absolute; top: calc(100% + 10px); right: 0; background-color: var(--card); min-width: 250px; box-shadow: 0 4px 30px rgba(0,0,0,0.1); border: 1px solid rgba(255, 255, 255, 0.1); z-index: 10; border-radius: 8px; padding: 8px 0; }
        .dropdown-content::before { content: ''; position: absolute; top: -10px; right: 12px; border-width: 0 8px 10px 8px; border-style: solid; border-color: transparent transparent var(--card) transparent; }
        .dropdown-content a { color: #e6eef8; padding: 14px 20px; text-decoration: none; display: flex; align-items: center; gap: 12px; font-weight: 500; }
        .dropdown-content a:hover {background-color: rgba(29, 155, 240, 0.1)}
        .dropdown-header { padding: 14px 20px; border-bottom: 1px solid rgba(255, 255, 255, 0.1); margin-bottom: 8px; }
        .dropdown-header span { font-weight: 700; color: #fff; }
        .show { display:block; }

        /* Main Content Styles */
        .container { max-width: 800px; margin: 40px auto; padding: 0 50px; }
        .container h1 { font-size: 28px; margin-top: 0; margin-bottom: 20px; border-left: 4px solid var(--accent); padding-left: 15px; color: #fff; }
        
        /* Notification List Styles */
        .notification-list { display: flex; flex-direction: column; gap: 20px; }
        .notification-item { background: var(--card); padding: 20px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.07); }
        .notification-item p { margin: 0; line-height: 1.7; font-size: 16px; }
        .notification-item .date { font-size: 13px; color: var(--muted); margin-top: 12px; }
    </style>
    <style>
        /* Footer Styles */
        .site-footer { background-color: var(--card); color: var(--muted); padding: 50px 50px 20px; border-top: 1px solid rgba(255,255,255,0.07); }
        .footer-main { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 40px; margin-bottom: 40px; }
        .footer-column h4 { color: #e6eef8; font-size: 16px; margin-bottom: 15px; font-weight: 600; }
        .footer-column ul { list-style: none; padding: 0; margin: 0; }
        .footer-column ul li { margin-bottom: 10px; }
        .footer-column ul a { color: var(--muted); text-decoration: none; font-size: 14px; transition: color 0.3s ease; }
        .footer-column ul a:hover { color: var(--accent); }
        .footer-bottom { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; padding-top: 20px; border-top: 1px solid var(--glass); font-size: 13px; }
        .social-links { display: flex; gap: 15px; }
        .social-links a { color: var(--muted); transition: color 0.3s ease; }
        .social-links a:hover { color: #fff; }
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
                <li><a href="quiz.php">Quiz</a></li>
                <li><a href="blogs.php">Blogs</a></li>
                <li><a href="articles.php">Articles</a></li>
            </ul>
        </nav>
        <div class="auth-buttons">
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
                    <a href="profile.php">Profile</a>
                    <a href="notifications.php" style="background-color: rgba(29, 155, 240, 0.1);">Notifications</a>
                    <a href="feedback.php">Feedback</a>
                    <a href="support.php">Support</a>
                    <a href="logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="container">
    <h1>Site Notifications</h1>
    <div class="notification-list">
        <?php if ($notifications_result && $notifications_result->num_rows > 0): ?>
            <?php while($row = $notifications_result->fetch_assoc()): ?>
                <div class="notification-item">
                    <p><?php echo nl2br(htmlspecialchars($row['text'])); ?></p>
                    <div class="date">Posted: <?php echo date('F d, Y \a\t h:i A', strtotime($row['created_at'])); ?></div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="notification-item">
                <p>There are no new notifications at this time.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require 'footer.php'; ?>

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