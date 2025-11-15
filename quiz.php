<?php
session_start();
require 'db.php';

// Define all quiz categories to ensure they are always displayed
$categories = ['Geography', 'History', 'Culture', 'Nature', 'Food', 'Travel'];

// Category images
$category_images = [
    'Geography' => 'img/geography.jpg',
    'History'   => 'img/history.jpg',
    'Culture'   => 'img/culture.jpg',
    'Nature'    => 'img/nature.jpg',
    'Food'      => 'img/food.jpg',
    'Travel'    => 'img/travel.jpg',
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Travel Quiz</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#1e293b;--card:#0b1220;--muted:#98a0b3;--accent:#1d9bf0;--radius:14px;--maxw:1100px;}
body{margin:0;font-family:'Montserrat',sans-serif;background:linear-gradient(180deg, var(--bg) 0%, #0f172a 100%);color:#e6eef8;}
a{color:inherit;text-decoration:none}
header{display:flex;justify-content:space-between;align-items:center;padding:20px 50px;background-color:var(--card);border-bottom:1px solid rgba(255,255,255,0.07);position:sticky;top:0;z-index:100}
header .logo{font-size:24px;font-weight:bold}
.header-right-group { display: flex; align-items: center; gap: 35px; }
.main-nav ul{list-style:none;display:flex;gap:35px;margin:0;padding:0}
.main-nav a{color:#98a0b3;text-decoration:none;font-weight:500;font-size:15px;padding:5px 0;position:relative;transition:color 0.3s}
.main-nav a:hover{color:#fff}
.main-nav a.active{color:#fff;font-weight:700}
.main-nav a.active::after { content: ''; position: absolute; bottom: -20px; left: 0; width: 100%; height: 2px; background-color: var(--accent); }

.search-container { flex-grow: 1; padding: 0 40px; }
.search-bar { display: flex; align-items: center; justify-content: space-between; max-width: 350px; margin: 0 auto; background-color: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 999px; padding: 8px 15px; cursor: pointer; transition: background-color 0.3s ease; }
.search-bar:hover { background-color: rgba(255,255,255,0.07); }
.search-bar span { color: var(--muted); font-weight: 500; }

header .auth-buttons button{margin-left:10px;padding:8px 20px;border:1px solid #98a0b3;background:rgba(255,255,255,0.04);color:#fff;cursor:pointer;border-radius:4px;font-weight:500;transition:0.3s}
header .auth-buttons .btn-primary{background:linear-gradient(90deg,var(--accent),#3bb0ff);color:#021426;border:none}

/* Profile Dropdown */
.profile-icon { display: inline-block; width: 40px; height: 40px; border-radius: 50%; background-color: var(--glass); cursor: pointer; display: flex; align-items: center; justify-content: center; }
.profile-dropdown { position: relative; display: inline-block; }
.dropdown-content { display: none; position: absolute; top: calc(100% + 10px); right: 0; background-color: var(--card); min-width: 220px; box-shadow: 0 4px 30px rgba(0,0,0,0.1); border: 1px solid rgba(255, 255, 255, 0.1); z-index: 10; border-radius: 8px; padding: 8px 0; }
.dropdown-content::before { content: ''; position: absolute; top: -10px; right: 12px; border-width: 0 8px 10px 8px; border-style: solid; border-color: transparent transparent var(--card) transparent; }
.dropdown-content a { color: #e6eef8; padding: 12px 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; font-weight: 500; }
.dropdown-content a:hover {background-color: rgba(29, 155, 240, 0.1)}
.dropdown-header { padding: 12px 16px; border-bottom: 1px solid rgba(255, 255, 255, 0.1); margin-bottom: 8px; }
.show { display:block; }

/* Hero Slider */
.hero-slider {
    position: relative;
    width: 100%;
    height: 450px; /* A bit shorter for an inner page */
    overflow: hidden;
}
.hero-slider .slides-wrapper {
    display: flex;
    width: 100%;
    height: 100%;
    transition: transform 0.5s ease-in-out;
}
.hero-slider .slide {
    position: relative;
    width: 100%;
    height: 100%;
    flex-shrink: 0;
}
.hero-slider .slide img { width: 100%; height: 100%; object-fit: cover; }
.slide-content {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 80px 50px 40px;
    background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0) 100%);
    color: #fff;
}
.slide-content h2 { font-size: 36px; font-weight: 700; margin: 0 0 10px 0; text-shadow: 2px 2px 8px rgba(0,0,0,0.7); }
.slide-content p { font-size: 18px; text-shadow: 1px 1px 6px rgba(0,0,0,0.8); }

.container{max-width:var(--maxw);margin:0 auto;padding:32px 16px}

/* Category Card Styles */
.category-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; margin-bottom: 40px; }
.category-card { background: var(--card); border: 1px solid rgba(255,255,255,0.07); border-radius: var(--radius); overflow: hidden; cursor: pointer; transition: transform 0.3s ease, box-shadow 0.3s ease; text-align:center; }
.category-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.3); }
.category-card.disabled { cursor: not-allowed; opacity: 0.6; }
.category-card img { width: 100%; height: 180px; object-fit: cover; display: block; }
.category-card h3 { padding: 15px 20px; margin: 0; font-size: 18px; }
</style>
</head>
<body>

<?php require 'header.php'; ?>

<div class="hero-slider">
    <div class="slides-wrapper">
        <div class="slide">
            <img src="img/quiz-1.jpg" alt="Quiz background image 1">
            <div class="slide-content">
                <h2>Test Your Travel Knowledge</h2>
                <p>Challenge yourself with quizzes on geography, culture, and more.</p>
            </div>
        </div>
        <div class="slide">
            <img src="img/quiz-2.jpg" alt="Quiz background image 2">
            <div class="slide-content">
                <h2>Discover & Learn</h2>
                <p>Each quiz is a new adventure waiting to be explored.</p>
            </div>
        </div>
        <div class="slide">
            <img src="img/quiz-3.jpg" alt="Quiz background image 3">
            <div class="slide-content">
                <h2>Become a Travel Expert</h2>
                <p>Expand your horizons, one question at a time.</p>
            </div>
        </div>
    </div>
</div>

<div class="container">
<h2>Quiz Categories</h2>
<div class="category-grid">
<?php foreach($categories as $category):
?>
<a href="quiz_detail.php?category=<?= urlencode($category) ?>" class="category-card">
    <img src="<?= htmlspecialchars($category_images[$category] ?? 'img/default_quiz.jpg') ?>" alt="<?= htmlspecialchars($category) ?>">
    <h3><?= htmlspecialchars($category) ?></h3>
</a>
<?php endforeach; ?>
</div>
</div>

<script>
// Hero slider JS
const sliderWrapper = document.querySelector('.hero-slider .slides-wrapper');
let slides = document.querySelectorAll('.hero-slider .slide');
let currentSlide = 0;

if (sliderWrapper && slides.length > 0) {
    const slideCount = slides.length;
    // Clone the first slide and append it to the end for a seamless loop
    const firstSlideClone = slides[0].cloneNode(true);
    sliderWrapper.appendChild(firstSlideClone);

    function nextHeroSlide() {
        currentSlide++;
        sliderWrapper.style.transform = `translateX(-${currentSlide * 100}%)`;
        sliderWrapper.style.transition = 'transform 0.5s ease-in-out';

        if (currentSlide >= slideCount) {
            setTimeout(() => {
                sliderWrapper.style.transition = 'none';
                currentSlide = 0;
                sliderWrapper.style.transform = `translateX(0%)`;
            }, 500); // This timeout should match the CSS transition duration
        }
    }
    setInterval(nextHeroSlide, 4000); // Change slide every 4 seconds
}
</script>
</body>
</html>
