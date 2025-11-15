<?php
session_start();
require 'db.php';

// Get category from URL
$category = $_GET['category'] ?? '';
if (empty($category)) die("Category not specified.");

// Fetch questions from DB
$stmt = $conn->prepare("SELECT * FROM quiz WHERE category = ? ORDER BY id ASC");
$stmt->bind_param("s", $category);
$stmt->execute();
$result = $stmt->get_result();

$questions = [];
while($row = $result->fetch_assoc()) {
    $questions[] = $row;
}
$stmt->close();

// Category images
$category_images = [
    'Geography' => 'img/geography.jpg',
    'History'   => 'img/history.jpg',
    'Culture'   => 'img/culture.jpg',
    'Nature'    => 'img/nature.jpg',
    'Food'      => 'img/food.jpg',
    'Travel'    => 'img/travel.jpg',
];
$hero_image = $category_images[$category] ?? 'img/default_quiz.jpg';

// Other categories for sidebar
$all_categories = ['Geography', 'History', 'Culture', 'Nature', 'Food', 'Travel'];
$other_categories = array_filter($all_categories, fn($c)=>$c!==$category);

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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($category) ?> Quiz</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#1e293b;--card:#0b1220;--muted:#98a0b3;--accent:#1d9bf0;--glass:rgba(255,255,255,0.03);}
body{margin:0;font-family:'Montserrat',sans-serif;color:#e6eef8;background:linear-gradient(180deg, var(--bg) 0%, #0f172a 100%);}
a{color:inherit;text-decoration:none}
header{display:flex;justify-content:space-between;align-items:center;padding:20px 50px;background-color:var(--card);color:#fff;border-bottom:1px solid rgba(255,255,255,0.07);position:sticky;top:0;z-index:100}
header .logo{font-size:24px;font-weight:bold}
.main-nav ul{list-style:none;display:flex;gap:35px;margin:0;padding:0}
.main-nav a{color:var(--muted);text-decoration:none;font-weight:500;font-size:15px;padding:5px 0;position:relative;transition:color 0.3s}
.main-nav a:hover{color:#fff}
.main-nav a.active{color:#fff;font-weight:700}

/* Profile Dropdown */
.header-right-group { display: flex; align-items: center; gap: 35px; }
.profile-icon { display: inline-block; width: 40px; height: 40px; border-radius: 50%; background-color: var(--glass); cursor: pointer; display: flex; align-items: center; justify-content: center; }
.profile-dropdown { position: relative; display: inline-block; }
.dropdown-content { display: none; position: absolute; top: calc(100% + 10px); right: 0; background-color: var(--card); min-width: 220px; box-shadow: 0 4px 30px rgba(0,0,0,0.1); border: 1px solid rgba(255, 255, 255, 0.1); z-index: 10; border-radius: 8px; padding: 8px 0; }
.dropdown-content::before { content: ''; position: absolute; top: -10px; right: 12px; border-width: 0 8px 10px 8px; border-style: solid; border-color: transparent transparent var(--card) transparent; }
.dropdown-content a { color: #e6eef8; padding: 12px 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; font-weight: 500; }
.dropdown-content a:hover {background-color: rgba(29, 155, 240, 0.1)}
.dropdown-header { padding: 12px 16px; border-bottom: 1px solid rgba(255, 255, 255, 0.1); margin-bottom: 8px; }
.show { display:block; }

/* Auth buttons for header (for non-logged-in users) */
header .auth-buttons button{margin-left:10px;padding:8px 20px;border:1px solid var(--muted);background:var(--glass);color:#fff;cursor:pointer;border-radius:4px;font-weight:500;transition:0.3s}
header .auth-buttons .btn-primary{background:linear-gradient(90deg,var(--accent),#3bb0ff);color:#021426;border:none;font-weight:700;}


/* Static Hero Section */
.hero {
    position: relative;
    width: 100%;
    height: 450px; /* Slightly shorter for an inner page */
    background-size: cover;
    background-position: center;
    display: flex;
    align-items: flex-end;
}
.hero::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(11,18,32,1) 0%, rgba(11,18,32,0) 80%);
}
.hero-content {
    position: relative;
    z-index: 2;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 80px 50px 40px;
    background: linear-gradient(to top, rgba(11,18,32,1) 0%, rgba(11,18,32,0) 80%);
    color: #fff;
}
.hero-content h2 { font-size: 36px; font-weight: 700; margin: 0 0 10px 0; text-shadow: 2px 2px 8px rgba(0,0,0,0.7); }
.hero-content p { font-size: 18px; font-weight: 400; text-shadow: 1px 1px 4px rgba(0,0,0,0.7); }

/* Page Content Wrapper (from safety.php) */
.page-content-wrapper {
    display: flex;
    gap: 30px;
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 50px;
    align-items: flex-start;
}

/* Sidebar Styles (from safety.php) */
.sidebar {
    flex: 1;
    min-width: 280px;
    background: var(--card);
    padding: 20px;
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.07);
    box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    position: sticky;
    top: 110px; /* header height + gap */
}
.sidebar h3 {
    color: #fff;
    font-size: 18px;
    margin-top: 0;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--glass);
}
.sidebar ul { list-style: none; padding: 0; margin: 0; }
.sidebar li { margin-bottom: 8px; }
.sidebar a { display: block; padding: 10px 15px; color: var(--muted); text-decoration: none; border-radius: 8px; transition: background-color 0.3s ease, color 0.3s ease; font-weight: 500; }
.sidebar a:hover { background-color: rgba(29, 155, 240, 0.1); color: #fff; }

/* Main content area */
.main-content { flex: 2.5; }
.quiz-card{background:var(--card);border-radius:12px;padding:28px;border:1px solid rgba(255,255,255,0.07)}
.q-text{font-size:18px;margin-bottom:12px}
.options{display:flex;flex-direction:column;gap:8px}
.opt{padding:12px;border-radius:10px;background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.03);cursor:pointer;transition:all 0.2s}
.opt:hover{background:rgba(255,255,255,0.07)}
.opt.correct{outline:2px solid #22c55e;background:rgba(34,197,94,0.1);}
.opt.wrong{outline:2px solid #ef4444;background:rgba(239,68,68,0.1);}
.disabled{pointer-events:none;opacity:0.85}
.feedback{margin-top:10px;font-weight:bold;}
.feedback.correct{color:#22c55e;}
.feedback.wrong{color:#ef4444;}
.pill{background:rgba(255,255,255,0.03);padding:6px 12px;border-radius:10px;font-size:13px;border:1px solid rgba(255,255,255,0.02);text-decoration:none;}
#nextBtn{background:var(--accent);color:#041022;border:none;padding:8px 16px;border-radius:8px;font-weight:700;cursor:pointer;}

/* Sidebar item styling */
.r-item{display:flex;gap:10px;align-items:center;padding:8px;border-radius:10px;background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.03);margin-bottom:8px}
.r-item:hover{background:rgba(255,255,255,0.05);}

@media(max-width:920px){
    .page-content-wrapper { flex-direction: column; padding: 20px; }
    .sidebar { position: static; width: 100%; max-width: none; margin-bottom: 30px; box-sizing: border-box; }
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
                <div class="dropdown-header"><?php echo htmlspecialchars($_SESSION['user']); ?></div>
                <a href="profile.php">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    Profile
                </a>
                <a href="logout.php">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-log-out"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                    Logout
                </a>
            </div>
        </div>
        <?php else: ?>
            <button onclick="window.location.href='login.php'">Login</button>
            <button class="btn-primary" onclick="window.location.href='signup.php'">Sign Up</button>
        <?php endif; ?>
    </div>
</div>
</header>
 
<div class="hero" style="background-image: url('<?= htmlspecialchars($hero_image) ?>');">
    <div class="hero-content">
        <h2><?= htmlspecialchars($category) ?> Quiz Challenge</h2>
        <p>Ready to test your knowledge and learn something new?</p>
    </div>
</div>


<div class="page-content-wrapper">
    <aside class="sidebar">
        <h3>Other Categories</h3>
        <?php foreach($other_categories as $cat): ?>
        <a href="quiz_detail.php?category=<?= urlencode($cat) ?>" class="r-item">
            <div style="width:58px;height:42px;border-radius:8px;background-image:url('<?= htmlspecialchars($category_images[$cat] ?? 'img/default_quiz.jpg') ?>');background-size:cover;background-position:center"></div>
            <div style="flex:1"><strong style="font-size:13px"><?= htmlspecialchars($cat) ?></strong></div>
        </a>
        <?php endforeach; ?>
    </aside>

    <main class="main-content">
        <?php if (empty($questions)): ?>
        <div class="quiz-card" style="text-align: center;">
            <h1><?= htmlspecialchars($category) ?> Quiz</h1>
            <p style="font-size: 18px; color: var(--muted); margin-top: 20px;">No questions for this category currently.</p>
            <a href="quiz.php" class="pill" style="margin-top: 20px; display: inline-block;">&larr; Back to Quizzes</a>
        </div>
        <?php else: ?>
        <div class="quiz-card">
            <h1><?= htmlspecialchars($category) ?> Quiz</h1>
            <div class="q-meta" id="progress"></div>
            <div class="q-text" id="qText"></div>
            <div class="options" id="options"></div>
            <div class="feedback" id="feedback"></div>
            <div style="margin-top:14px;display:flex;justify-content:space-between">
                <a href="quiz.php" class="pill">&larr; Back to Quizzes</a>
                <button id="nextBtn" style="display:none;">Next</button>
            </div>
        </div>
        <?php endif; ?>
    </main>
</div>

<?php if (!empty($questions)): ?>
<script>
const questions = <?= json_encode($questions) ?>;
let currentQIndex = 0;
let score = 0;
const qText = document.getElementById('qText');
const optionsEl = document.getElementById('options');
const feedbackEl = document.getElementById('feedback');
const nextBtn = document.getElementById('nextBtn');
const progressEl = document.getElementById('progress');

function renderQuestion(){
    feedbackEl.textContent = '';
    nextBtn.style.display='none';
    if(currentQIndex>=questions.length){
        qText.innerHTML=`<h2>Quiz Completed!</h2><p>Your score: ${score} / ${questions.length}</p>`;
        optionsEl.innerHTML='';
        progressEl.textContent='Finished';
        // Add a "Play Again" button
        nextBtn.textContent = 'Play Again';
        nextBtn.style.display = 'inline-block';
        nextBtn.onclick = () => { window.location.reload(); };
        return;
    }
    const q = questions[currentQIndex];
    progressEl.textContent=`Question ${currentQIndex+1} / ${questions.length}`;
    qText.textContent=q.question;
    optionsEl.innerHTML='';
    ['A','B','C','D'].forEach(k=>{
        const opt=document.createElement('div');
        opt.className='opt';
        opt.textContent=q['option_'+k.toLowerCase()];
        opt.dataset.key=k;
        opt.onclick=handleAnswer;
        optionsEl.appendChild(opt);
    });
}

function handleAnswer(e){
    const selectedKey=e.currentTarget.dataset.key;
    const correctKey=questions[currentQIndex].correct_option;
    document.querySelectorAll('.opt').forEach(opt=>{
        opt.classList.add('disabled');
        if(opt.dataset.key===correctKey) opt.classList.add('correct');
    });
    if(selectedKey===correctKey){
        score++;
        feedbackEl.textContent='Correct!';
        feedbackEl.className='feedback correct';
    }else{
        e.currentTarget.classList.add('wrong');
        feedbackEl.textContent='Incorrect!';
        feedbackEl.className='feedback wrong';
    }
    nextBtn.textContent = 'Next';
    nextBtn.style.display='inline-block';
    nextBtn.onclick = () => {
        currentQIndex++;
        renderQuestion();
    };
}

nextBtn.addEventListener('click',()=>{
    currentQIndex++;
    renderQuestion();
});

// Profile Dropdown JS
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

renderQuestion();
</script>
<?php endif; ?>

</body>
</html>
