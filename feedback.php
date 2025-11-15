<?php
session_start();
require 'db.php';

// Redirect if user is not logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Fetch user ID
$username = $_SESSION['user'];
$user_stmt = $conn->prepare("SELECT id, avatar FROM users WHERE username = ?");
$user_stmt->bind_param("s", $username);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$user_id = $user['id'];
$user_avatar_header = $user['avatar'] ?? null;

$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    // Sanitize and retrieve all form data
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $q1 = isset($_POST['q1_navigation']) ? (int)$_POST['q1_navigation'] : null;
    $q2 = isset($_POST['q2_design']) ? (int)$_POST['q2_design'] : null;
    $q3 = isset($_POST['q3_performance']) ? (int)$_POST['q3_performance'] : null;
    $q4 = isset($_POST['q4_content_quality']) ? (int)$_POST['q4_content_quality'] : null;
    $q5 = isset($_POST['q5_feature_satisfaction']) ? (int)$_POST['q5_feature_satisfaction'] : null;
    $q6 = isset($_POST['q6_found_info']) ? trim($_POST['q6_found_info']) : null;
    $q7 = isset($_POST['q7_recommend']) ? (int)$_POST['q7_recommend'] : null;
    $q8 = trim($_POST['q8_most_liked']);
    $q9 = trim($_POST['q9_improvement_suggestion']);
    $q10 = trim($_POST['q10_missing_features']);
    $comments = trim($_POST['comments']);

    if ($rating > 0) {
        $stmt = $conn->prepare("INSERT INTO feedback (user_id, rating, q1_navigation, q2_design, q3_performance, q4_content_quality, q5_feature_satisfaction, q6_found_info, q7_recommend, q8_most_liked, q9_improvement_suggestion, q10_missing_features, comments) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiiiiissssss", $user_id, $rating, $q1, $q2, $q3, $q4, $q5, $q6, $q7, $q8, $q9, $q10, $comments);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Thank you for your feedback!";
            header("Location: index.php");
            exit;
        } else {
            $message = "Error: Could not submit your feedback. Please try again.";
            $message_type = 'error';
        }
        $stmt->close();
    } else {
        $message = "Please provide an overall rating.";
        $message_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Site Feedback - Travel Tales</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root{--bg:#1e293b; /* slate-700 */ --card:#0b1220;--muted:#9aa4b2;--accent:#1d9bf0;--glass:rgba(255,255,255,0.03);}
        body{margin:0;font-family:'Montserrat',sans-serif;background:linear-gradient(180deg, var(--bg) 0%, #0f172a 100%);color:#e6eef8}
        a{text-decoration:none;color:var(--accent)}a:hover{text-decoration:underline}
        header{display:flex;justify-content:space-between;align-items:center;padding:20px 50px;background-color:var(--card);color:#fff;border-bottom:1px solid rgba(255,255,255,0.07);position:sticky;top:0;z-index:100}
        header .logo{font-size:24px;font-weight:bold}
        .header-right-group{display:flex;align-items:center;gap:35px}
        .main-nav ul{list-style:none;margin:0;padding:0;display:flex;gap:35px}
        .main-nav a{color:var(--muted);text-decoration:none;font-weight:500;font-size:15px;padding:5px 0;position:relative;transition:color .3s ease}
        .main-nav a:hover{color:#fff}
        .profile-icon{display:inline-block;width:40px;height:40px;border-radius:50%;background-color:var(--glass);cursor:pointer;display:flex;align-items:center;justify-content:center}
        .profile-dropdown{position:relative;display:inline-block}
        .dropdown-content{display:none;position:absolute;top:calc(100% + 10px);right:0;background-color:var(--card);min-width:250px;box-shadow:0 4px 30px rgba(0,0,0,.1);border:1px solid rgba(255,255,255,.1);z-index:10;border-radius:8px;padding:8px 0}
        .dropdown-content::before{content:'';position:absolute;top:-10px;right:12px;border-width:0 8px 10px 8px;border-style:solid;border-color:transparent transparent var(--card) transparent}
        .dropdown-content a{color:#e6eef8;padding:14px 20px;text-decoration:none;display:flex;align-items:center;gap:12px;font-weight:500}
        .dropdown-content a:hover{background-color:rgba(29,155,240,.1)}
        .dropdown-header{padding:14px 20px;border-bottom:1px solid rgba(255,255,255,.1);margin-bottom:8px}
        .dropdown-header span{font-weight:700;color:#fff}
        .show{display:block}
        .container{max-width:800px;margin:40px auto;padding:0 50px}
        .container h1{font-size:28px;margin-top:0;margin-bottom:20px;border-left:4px solid var(--accent);padding-left:15px;color:#fff}
        .form-container{background:var(--card);padding:30px;border-radius:12px;border:1px solid rgba(255,255,255,.07)}
        .form-group{margin-bottom:25px}
        .form-group label{display:block;font-weight:600;margin-bottom:10px;font-size:16px}
        .form-group textarea,.form-group input[type="text"]{width:100%;padding:12px;background:var(--glass);border:1px solid rgba(255,255,255,.1);border-radius:8px;color:#e6eef8;font-family:'Montserrat',sans-serif;font-size:16px;box-sizing:border-box}
        .form-group textarea{min-height:120px;resize:vertical}
        .btn-submit{padding:12px 25px;background:var(--accent);color:#041022;border:none;border-radius:8px;font-weight:700;font-size:16px;cursor:pointer;transition:background .3s}
        .btn-submit:hover{background:#4fbfff}
        .message{padding:15px;border-radius:8px;margin-bottom:20px;font-weight:500}
        .message.success{background-color:rgba(92,184,92,.2);color:#5cb85c;border:1px solid #5cb85c}
        .message.error{background-color:rgba(217,83,79,.2);color:#d9534f;border:1px solid #d9534f}
        .star-rating{display:flex;flex-direction:row-reverse;justify-content:flex-end;gap:5px}
        .star-rating input[type="radio"]{display:none}
        .star-rating label{font-size:30px;color:var(--muted);cursor:pointer;transition:color .2s}
        .star-rating input[type="radio"]:checked ~ label,.star-rating label:hover,.star-rating label:hover ~ label{color:#f5b32a}
        .radio-group label{margin-right:15px}
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
                    <div class="dropdown-header"><span><?php echo htmlspecialchars($_SESSION['user']); ?></span></div>
                    <a href="profile.php">Profile</a>
                    <a href="notifications.php">Notifications</a>
                    <a href="feedback.php" style="background-color: rgba(29, 155, 240, 0.1);">Feedback</a>
                    <a href="support.php">Support</a>
                    <a href="logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="container">
    <h1>Site Feedback</h1>
    <div class="form-container">
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label>1. Overall, how would you rate our website? (Required)</label>
                <div class="star-rating">
                    <input type="radio" id="star5" name="rating" value="5" /><label for="star5" title="5 stars">&#9733;</label>
                    <input type="radio" id="star4" name="rating" value="4" /><label for="star4" title="4 stars">&#9733;</label>
                    <input type="radio" id="star3" name="rating" value="3" /><label for="star3" title="3 stars">&#9733;</label>
                    <input type="radio" id="star2" name="rating" value="2" /><label for="star2" title="2 stars">&#9733;</label>
                    <input type="radio" id="star1" name="rating" value="1" /><label for="star1" title="1 star">&#9733;</label>
                </div>
            </div>

            <div class="form-group">
                <label>2. How easy was it to navigate the site? (1=Very Difficult, 5=Very Easy)</label>
                <div class="radio-group">
                    <?php for ($i = 1; $i <= 5; $i++) echo "<label><input type='radio' name='q1_navigation' value='$i'> $i</label>"; ?>
                </div>
            </div>

            <div class="form-group">
                <label>3. How would you rate the visual design and layout? (1=Poor, 5=Excellent)</label>
                <div class="radio-group">
                    <?php for ($i = 1; $i <= 5; $i++) echo "<label><input type='radio' name='q2_design' value='$i'> $i</label>"; ?>
                </div>
            </div>

            <div class="form-group">
                <label>4. How satisfied are you with the site's performance (speed)? (1=Very Dissatisfied, 5=Very Satisfied)</label>
                <div class="radio-group">
                    <?php for ($i = 1; $i <= 5; $i++) echo "<label><input type='radio' name='q3_performance' value='$i'> $i</label>"; ?>
                </div>
            </div>

            <div class="form-group">
                <label>5. How would you rate the quality of the content (articles, guides)? (1=Poor, 5=Excellent)</label>
                <div class="radio-group">
                    <?php for ($i = 1; $i <= 5; $i++) echo "<label><input type='radio' name='q4_content_quality' value='$i'> $i</label>"; ?>
                </div>
            </div>

            <div class="form-group">
                <label>6. How satisfied are you with the features (e.g., Forum, Quiz)? (1=Very Dissatisfied, 5=Very Satisfied)</label>
                <div class="radio-group">
                    <?php for ($i = 1; $i <= 5; $i++) echo "<label><input type='radio' name='q5_feature_satisfaction' value='$i'> $i</label>"; ?>
                </div>
            </div>

            <div class="form-group">
                <label>7. Did you find the information you were looking for?</label>
                <div class="radio-group">
                    <label><input type="radio" name="q6_found_info" value="Yes"> Yes</label>
                    <label><input type="radio" name="q6_found_info" value="Partially"> Partially</label>
                    <label><input type="radio" name="q6_found_info" value="No"> No</label>
                </div>
            </div>

            <div class="form-group">
                <label>8. How likely are you to recommend our site to a friend? (1=Not Likely, 10=Very Likely)</label>
                <div class="radio-group">
                    <?php for ($i = 1; $i <= 10; $i++) echo "<label><input type='radio' name='q7_recommend' value='$i'> $i</label>"; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="q8">9. What did you like most about our website?</label>
                <input type="text" id="q8" name="q8_most_liked" placeholder="e.g., The detailed articles, the quiz feature...">
            </div>

            <div class="form-group">
                <label for="q9">10. What is one thing we could do to improve the site?</label>
                <input type="text" id="q9" name="q9_improvement_suggestion" placeholder="e.g., Add more destinations, improve mobile view...">
            </div>

            <div class="form-group">
                <label for="q10">11. Are there any features you wish we had?</label>
                <input type="text" id="q10" name="q10_missing_features" placeholder="e.g., A trip planner, user reviews on destinations...">
            </div>

            <div class="form-group">
                <label for="comments">12. Any other comments or thoughts?</label>
                <textarea id="comments" name="comments" placeholder="Share any additional feedback here..."></textarea>
            </div>

            <button type="submit" name="submit_feedback" class="btn-submit">Submit Feedback</button>
        </form>
    </div>
</div>

<!-- Footer -->
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