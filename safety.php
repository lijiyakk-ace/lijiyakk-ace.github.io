<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Traveler Medical Guidance</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    :root{
      --bg:#0f1724; /* deep navy */
      --card:#0b1220;
      --muted:#9aa4b2;
      --accent:#1d9bf0; /* twitter-like */
      --glass: rgba(255,255,255,0.03);
    }

    body {
        margin: 0;
        font-family: 'Montserrat', sans-serif;
        background:linear-gradient(180deg,var(--bg) 0%, #071027 60%);
        color: #e6eef8;
    }

    /* Header */
    header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 50px;
        background-color: var(--card);
        color: #fff;
        border-bottom: 1px solid rgba(255,255,255,0.07);
        position: sticky;
        top: 0;
        z-index: 100;
    }

    header .logo {
        font-size: 24px;
        font-weight: bold;
    }

    .header-right-group {
        display: flex;
        align-items: center;
        gap: 35px;
    }

    .main-nav {
        display: flex;
        justify-content: center;
    }
    .main-nav ul {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        gap: 35px;
    }
    .main-nav a {
        color: var(--muted);
        text-decoration: none;
        font-weight: 500;
        font-size: 15px;
        padding: 5px 0;
        position: relative;
        transition: color 0.3s ease;
    }
    .main-nav a:hover {
        color: #fff;
    }
    .main-nav a.active {
        color: #fff;
        font-weight: 700;
    }
    .main-nav a.active::after {
        content: '';
        position: absolute;
        bottom: -20px;
        left: 0;
        width: 100%;
        height: 2px;
        background-color: var(--accent);
    }

    /* Profile Dropdown (copied from index.php for consistency) */
    .profile-icon {
        display: inline-block;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: var(--glass);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .profile-dropdown { position: relative; display: inline-block; }
    .dropdown-content {
        display: none;
        position: absolute;
        top: calc(100% + 10px);
        right: 0;
        background-color: var(--card);
        min-width: 250px;
        box-shadow: 0 4px 30px rgba(0,0,0,0.1);
        border: 1px solid rgba(255, 255, 255, 0.1);
        z-index: 10;
        border-radius: 8px;
        padding: 8px 0;
    }
    .dropdown-content::before {
        content: '';
        position: absolute;
        top: -10px;
        right: 12px;
        border-width: 0 8px 10px 8px;
        border-style: solid;
        border-color: transparent transparent var(--card) transparent;
    }
    .dropdown-content a {
        color: #e6eef8;
        padding: 14px 20px;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 12px;
        font-family: 'Montserrat', sans-serif;
        font-weight: 500;
    }
    .dropdown-content a:hover {background-color: rgba(29, 155, 240, 0.1)}
    .dropdown-header {
        padding: 14px 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        margin-bottom: 8px;
    }
    .dropdown-header span { font-weight: 700; color: #fff; }
    .show { display:block; }

    /* Page Content Wrapper for Sidebar and Main Content */
    .page-content-wrapper {
        display: flex;
        gap: 30px;
        width: auto; /* Allow it to be flexible */
        padding: 40px 50px; /* Use padding for side spacing */
        align-items: flex-start; /* Align items to the top */
    }

    /* Sidebar Styles */
    .sidebar {
        flex: 1; /* Allow sidebar to take up 1 part of the available space */
        min-width: 280px; /* Ensure it doesn't get too small */
        background: var(--card);
        padding: 20px;
        border-radius: 12px;
        border: 1px solid rgba(255,255,255,0.07);
        box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    }

    .sidebar h3 {
        color: #fff;
        font-size: 18px;
        margin-top: 0;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--glass);
    }

    .sidebar ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .sidebar li {
        margin-bottom: 8px;
    }

    .sidebar a {
        display: block;
        padding: 10px 15px;
        color: var(--muted);
        text-decoration: none;
        border-radius: 8px;
        transition: background-color 0.3s ease, color 0.3s ease;
        font-weight: 500;
    }

    .sidebar a:hover {
        background-color: rgba(29, 155, 240, 0.1);
        color: #fff;
    }

    .sidebar a.active {
        background-color: var(--accent);
        color: #0b1220; /* Dark text for active link on accent background */
        font-weight: 700;
        box-shadow: 0 2px 10px rgba(29, 155, 240, 0.4);
    }


    /* Hero Slider (copied from index.php) */
    .hero-slider {
        position: relative;
        width: 100%;
        height: 500px; /* A bit shorter for inner pages */
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
    .hero-slider .slide img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .slide-content {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 80px 50px 40px;
        background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0) 100%);
        color: #fff;
    }
    .slide-content h2 {
        font-size: 36px;
        font-weight: 700;
        margin: 0 0 10px 0;
        text-shadow: 2px 2px 8px rgba(0,0,0,0.7);
    }
    .slide-content p {
        font-size: 18px;
        font-weight: 400;
        text-shadow: 1px 1px 4px rgba(0,0,0,0.7);
    }

    /* Content Styles */
    .container { /* This will now be the main content area */
        flex: 2; /* Allow content to take up 2 parts of the space */
        margin: 0; /* Remove margin, as flex gap handles spacing */
        background: var(--card);
        padding: 40px;
        border-radius: 12px;
        border: 1px solid rgba(255,255,255,0.07);
    }
    h1, h2, h3 { color: #fff; }
    h1 { font-size: 2.5em; margin-bottom: 0.5em; }
    h2 { border-left: 4px solid var(--accent); padding-left: 0.8em; font-size: 1.8em; margin-top: 1.5em; }
    p { line-height: 1.7; color: var(--muted); }
    a { color: var(--accent); text-decoration: none; }
    .container a:hover { text-decoration: underline; } /* Underline only for content links */

    .intro {
      font-size: 1.2rem;
      color: #e6eef8;
      margin-bottom: 1.5em;
      border-bottom: 1px solid var(--glass);
      padding-bottom: 1.5em;
    }
    .container ul { padding-left: 20px; }
    .container ul li { margin-bottom: 0.8em; color: var(--muted); }

    .topic-image {
        width: 100%;
        max-height: 350px;
        object-fit: cover;
        border-radius: 10px;
        margin-top: 1em;
        margin-bottom: 1.5em;
        border: 1px solid var(--glass);
    }
  </style>
  <style>
    /* Responsive adjustments for sidebar */
    @media (max-width: 992px) {
        .page-content-wrapper { flex-direction: column; align-items: center; }
        .sidebar { width: 90%; max-width: 600px; margin-bottom: 30px; }
        .container { margin-top: 0; } /* Remove top margin when stacked */
    }
  </style>
</head>
<body>

<header>
    <div class="logo">Travel Tales</div>
    <div class="header-right-group">
        <nav class="main-nav">
            <ul>
                <li><a href="index.php" class="active">Home</a></li>
                <li><a href="destinations.php">Destinations</a></li>
                <li><a href="forum.php">Forum</a></li>
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

<div class="hero-slider">
    <div class="slides-wrapper">
        <div class="slide">
            <img src="img/medical-3.jpg" alt="Slide 1">
            <div class="slide-content">
                <h2>Travel Healthy, Travel Safe</h2>
                <p>Essential medical guidance for every journey.</p>
            </div>
        </div>
        <div class="slide">
            <img src="img/medical-2.jpg" alt="Slide 2">
            <div class="slide-content">
                <h2>Plan with Health in Mind</h2>
                <p>Know what your body needs before departure.</p>
            </div>
        </div>
        <div class="slide">
            <img src="img/medical-1.jpg" alt="Slide 3">
            <div class="slide-content">
                <h2>Stay Fit, Stay Prepared</h2>
                <p>Minimize health risks and enjoy your adventure.</p>
            </div>
        </div>
    </div>
</div>

<div class="page-content-wrapper">
    <aside class="sidebar">
        <h3>Guidance Topics</h3>
        <ul>
            <li><a href="general_info.php">General Information</a></li>
            <li><a href="safety.php" class="active">Medical Guidance</a></li>
            <li><a href="transport.php">Transport Guidance</a></li>
            <li><a href="money.php">Money Guidance</a></li>
            <li><a href="food.php">Food Guidance</a></li>
            <li><a href="shopping.php">Shopping Guidance</a></li>
            <li><a href="culture.php">Cultural Guidance</a></li>
        </ul>
    </aside>

    <div class="container">
        <h1>Medical Guidance for Travelers</h1>
        <p class="intro">Traveling is thrilling, but your health is your greatest asset. This guide outlines the essential medical preparations, vaccinations, medications, and precautions every traveler should consider before leaving home.</p>

        <h2>Table of Contents</h2>
        <ul>
            <li><a href="#pre-travel">Pre-Travel Health Preparation</a></li>
            <li><a href="#vaccinations">Vaccinations & Preventive Measures</a></li>
            <li><a href="#medications">Travel Medications & Supplies</a></li>
            <li><a href="#diet-hydration">Diet & Hydration Tips</a></li>
            <li><a href="#emergencies">Medical Emergencies Abroad</a></li>
            <li><a href="#tech-health">Digital Health & Travel Tech</a></li>
        </ul>

        <h2 id="pre-travel">Pre-Travel Health Preparation</h2>
        <img src="img/pretravel.jpg" alt="Health preparation with a checklist and medical items" class="topic-image">
        <p>Proper planning reduces health risks and ensures a comfortable journey.</p>
        <ul>
            <li>Schedule a pre-travel medical checkup for routine blood work, vital signs, and any chronic condition management.</li>
            <li>Consult a travel medicine specialist if visiting areas with specific health risks (malaria, dengue, Zika, etc.).</li>
            <li>Prepare a personalized health kit including prescription medications and copies of medical history.</li>
            <li>Review any health insurance policies for international coverage and emergency evacuation options.</li>
        </ul>

        <h2 id="vaccinations">Vaccinations & Preventive Measures</h2>
        <img src="img/vaccination.jpg" alt="Doctor preparing a vaccination" class="topic-image">
        <ul>
            <li>Ensure routine vaccinations are up-to-date (e.g., tetanus, measles, polio).</li>
            <li>Check destination-specific vaccines (yellow fever, hepatitis A/B, typhoid, meningitis).</li>
            <li>Take preventive medications if traveling to malaria or high-risk infection areas.</li>
            <li>Carry documentation of vaccinations and any required health certificates.</li>
        </ul>

        <h2 id="medications">Travel Medications & Supplies</h2>
        <img src="img/first-aid.jpg" alt="A well-stocked travel first-aid kit" class="topic-image">
        <ul>
            <li>Pack sufficient supply of prescription medications, with copies of prescriptions.</li>
            <li>Include over-the-counter remedies for common issues (painkillers, anti-diarrheal, antihistamines).</li>
            <li>Bring a basic first aid kit: bandages, antiseptics, thermometer, insect repellent, sunscreen.</li>
            <li>Consider motion sickness tablets or altitude sickness medication if relevant.</li>
        </ul>

        <h2 id="diet-hydration">Diet & Hydration Tips</h2>
        <img src="img/hydrated.jpg" alt="A person drinking bottled water with fresh food in the background" class="topic-image">
        <ul>
            <li>Stay hydrated, especially in hot climates or during long flights.</li>
            <li>Prefer bottled or purified water and avoid untreated local sources.</li>
            <li>Eat freshly cooked foods; avoid undercooked or raw dishes in high-risk regions.</li>
            <li>Consider probiotics or digestive aids to prevent traveler’s diarrhea.</li>
        </ul>

        <h2 id="emergencies">Medical Emergencies Abroad</h2>
        <img src="img/emergency.jpg" alt="An emergency sign on a building in a foreign country" class="topic-image">
        <ul>
            <li>Know local emergency numbers and hospital locations.</li>
            <li>Keep your health insurance details and emergency contacts easily accessible.</li>
            <li>Report serious incidents immediately and keep records of medical treatment and expenses.</li>
            <li>Learn basic first aid procedures relevant to your destination risks.</li>
        </ul>

        <h2 id="tech-health">Digital Health & Travel Tech</h2>
        <img src="img/health-app.jpg" alt="A smartphone showing a health and travel application" class="topic-image">
        <ul>
            <li>Use apps to track medications, vaccinations, and medical appointments while traveling.</li>
            <li>Maintain digital copies of prescriptions, health insurance, and vaccination records.</li>
            <li>Enable location sharing with trusted contacts for emergencies.</li>
            <li>Consider wearable health monitors for heart rate, sleep, and activity tracking.</li>
        </ul>

        <h2>Final Thoughts</h2>
        <p>Prioritizing your health ensures a smoother and more enjoyable travel experience. By preparing your medical needs in advance, staying vigilant about hygiene, and using preventive measures, you safeguard yourself against most travel-related health risks.</p>
    </div>
</div>

<script>
    // Hero slider JS
    const sliderWrapper = document.querySelector('.hero-slider .slides-wrapper');
    let slides = document.querySelectorAll('.hero-slider .slide');
    let currentSlide = 0;
    const slideCount = slides.length;

    if (sliderWrapper && slides.length > 0) {
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
                }, 500);
            }
        }
        setInterval(nextHeroSlide, 3000);
    }

    // Profile Dropdown JS
    const profileIcon = document.getElementById('profileIcon');
    if (profileIcon) {
        profileIcon.addEventListener('click', function() {
            document.getElementById('profileDropdown').classList.toggle('show');
        });

        // Close the dropdown if the user clicks outside of it
        window.addEventListener('click', function(event) {
            if (!profileIcon.contains(event.target) && !event.target.closest('.profile-dropdown')) {
                var dropdowns = document.getElementsByClassName("dropdown-content");
                for (var i = 0; i < dropdowns.length; i++) {
                    dropdowns[i].classList.remove('show');
                }
            }
        });
    }
</script>

</body>
</html>
