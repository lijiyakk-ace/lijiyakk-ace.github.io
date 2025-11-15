<?php
session_start();
?>
<?php
// Fetch the current user's avatar for the header
$user_avatar_header = null;
if (isset($_SESSION['user'])) {
    require 'db.php'; // Ensure db connection is available
    $username = $_SESSION['user'];
    $avatar_stmt = $conn->prepare("SELECT avatar FROM users WHERE username = ?");
    $avatar_stmt->bind_param("s", $username);
    $avatar_stmt->execute();
    $user_avatar_header = $avatar_stmt->get_result()->fetch_assoc()['avatar'] ?? null;
}
?>

<!DOCTYPE html>
<html lang="en">
head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Transport Guidance for Travelers</title>
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
            <img src="img/transport-1.jpg" alt="Public transport">
            <div class="slide-content">
                <h2>Traverse with Confidence</h2>
                <p>Essential transport guidance for every traveler.</p>
            </div>
        </div>
        <div class="slide">
            <img src="img/transport-2.jpg" alt="Road transport">
            <div class="slide-content">
                <h2>Navigate Cities Smartly</h2>
                <p>Tips for using transit, buses, trains, and rideshares.</p>
            </div>
        </div>
        <div class="slide">
            <img src="img/transport-3.jpg" alt="Train travel">
            <div class="slide-content">
                <h2>Seamless Connections</h2>
                <p>Plan, book, and travel comfortably.</p>
            </div>
        </div>
    </div>
</div>

<div class="page-content-wrapper">
    <aside class="sidebar">
        <h3>Guidance Topics</h3>
        <ul>
            <li><a href="general_info.php">General Information</a></li>
            <li><a href="safety.php">Medical Guidance</a></li>
            <li><a href="transport_guidance.php" class="active">Transport Guidance</a></li>
            <li><a href="money.php">Money Guidance</a></li>
            <li><a href="food.php">Food Guidance</a></li>
            <li><a href="shopping.php">Shopping Guidance</a></li>
            <li><a href="cultural_guidance.php">Cultural Guidance</a></li>
        </ul>
    </aside>

    <div class="container">
        <h1>Transport Guidance for Travelers</h1>
        <p class="intro">Getting around in a new place can be exciting — and sometimes daunting. This guide provides you with best practices, tips, and precautions for using transport modes safely, efficiently, and cost-effectively.</p>

        <h2>Table of Contents</h2>
        <ul>
            <li><a href="#planning">Planning Your Transport</a></li>
            <li><a href="#public">Public Transport Tips</a></li>
            <li><a href="#rideshare-taxi">Rideshare, Taxis & Private Transport</a></li>
            <li><a href="#long-distance">Long-Distance Travel (Trains, Buses, Flights)</a></li>
            <li><a href="#local-mobility">Last-Mile & Local Mobility</a></li>
            <li><a href="#safety-tips">Safety Tips for Transport</a></li>
        </ul>

        <h2 id="planning">Planning Your Transport</h2>
        <img src="img/transport-planning.jpg" alt="A person looking at a transport map on a phone" class="topic-image">
        <p>Before you set out, a little preparation goes a long way.</p>
        <ul>
            <li>Research the available modes in your destination (bus, metro, tram, ferry, rideshare).</li>
            <li>Check schedules and routes in advance — many cities have transit apps or websites.</li>
            <li>Budget for transport costs (fares, tolls, surge pricing) and carry small change or local transit cards.</li>
            <li>Know peak hours and avoid traveling in congested times if possible.</li>
        </ul>

        <h2 id="public">Public Transport Tips</h2>
        <img src="img/public-transport.jpg" alt="A modern city bus at a bus stop" class="topic-image">
        <p>Public transport is often the most affordable and authentic way to travel locally.</p>
        <h3>Using Buses & Trams</h3>
        <ul>
            <li>Get route maps & learn major lines; use official apps if available.</li>
            <li>Stand in designated waiting areas; board from approved stops.</li>
            <li>Hold tight, especially in crowded conditions. Keep valuables close.</li>
        </ul>
        <h3>Using Metro / Subway / Light Rail</        >
        <ul>
            <li>Follow direction signage and platform markings.</li>
            <li>Scan transit cards, tokens, or tap-in/tap-out properly to avoid fines.</li>
            <li>Avoid empty train cars at night or in unfamiliar neighborhoods.</li>
        </ul>
        <h3>Using Ferry or Water Transport</h3>
        <ul>
            <li>Check for schedules, safety measures, and weather conditions.</li>
            <li>Wear life vests if provided and stay in designated passenger zones.</li>
        </ul>

        <h2 id="rideshare-taxi">Rideshare, Taxis & Private Transport</h2>
        <img src="img/taxi.jpg" alt="A person getting into a yellow taxi cab" class="topic-image">
        <ul>
            <li>Use trusted apps or registered taxi services; avoid street hails in risky areas.</li>
            <li>Share trip details or live location with someone you trust.</li>
            <li>Verify driver identity (name, license plate) before boarding.</li>
            <li>Avoid overtipping or paying cash if your app allows in-app payment.</li>
            <li>Use child seats, seat belts, or safety restraints where available.</li>
        </ul>

        <h2 id="long-distance">Long-Distance Travel (Trains, Buses, Flights)</h2>
        <img src="img/train-window.jpg" alt="View of a landscape from a high-speed train window" class="topic-image">
        <p>For journeys between cities or countries, efficiency and comfort matter.</p>
        <ul>
            <li>Book in advance for the best fares and seat availability.</li>
            <li>Choose reputable carriers or national operators; review their safety record.</li>
            <li>Arrive early for check-in, boarding, and security checks.</li>
            <li>When possible, select seats near emergency exits or aisles.</li>
            <li>Keep your travel documents, valuables, and essentials in your carry-on.</li>
        </ul>

        <h2 id="local-mobility">Last-Mile & Local Mobility</h2>
        <img src="img/scooter-rental.jpg" alt="A row of electric scooters available for rent on a city street" class="topic-image">
        <ul>
            <li>Use walking, bicycles, or e-scooters cautiously — always check road rules.</li>
            <li>Rent from reputable providers; wear helmets and protective gear.</li>
            <li>At night, avoid walking alone; prefer rideshares or well-lit paths.</li>
            <li>Be aware of local traffic patterns, one-way systems, and sidewalks.</li>
        </ul>

        <h2 id="safety-tips">Safety Tips for Transport</h2>
        <img src="img/transport-safety.jpg" alt="A person holding their bag securely on a crowded subway" class="topic-image">
        <ul>
            <li>Keep your bag in sight or between your legs; don’t place it overhead on public transit.</li>
            <li>Avoid flaunting valuables (phones, jewelry); use discreet phone handling.</li>
            <li>Trust your instincts — if a driver, vehicle, or route feels unsafe, decline or exit.</li>
            <li>In rideshares or taxis, note license plate and driver name before boarding.</li>
            <li>Stay alert when traveling at night or through unfamiliar areas.</li>
        </ul>

        <h2>Final Thoughts</h2>
        <p>Transportation is the backbone of your travel experience. With good planning, vigilance, and reliable choices, you transform what can be stressful into seamless journeys. Use public transit smartly, choose safe and trusted services, and stay aware at all times for a smoother travel experience.</p>
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
                document.getElementById('profileDropdown').classList.remove('show');
            }
        });
    }
</script>

</body>
</html>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    Profile
                </a>
                <a href="logout.php">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                    Logout
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</header>

<div class="hero-slider">
    <div class="slides-wrapper">
        <div class="slide">
            <img src="img/transport-1.jpg" alt="Public transport">
            <div class="slide-content">
                <h2>Traverse with Confidence</h2>
                <p>Essential transport guidance for every traveler.</p>
            </div>
        </div>
        <div class="slide">
            <img src="img/transport-2.jpg" alt="Road transport">
            <div class="slide-content">
                <h2>Navigate Cities Smartly</h2>
                <p>Tips for using transit, buses, trains, and rideshares.</p>
            </div>
        </div>
        <div class="slide">
            <img src="img/transport-3.jpg" alt="Train travel">
            <div class="slide-content">
                <h2>Seamless Connections</h2>
                <p>Plan, book, and travel comfortably.</p>
            </div>
        </div>
    </div>
</div>

<div class="page-content-wrapper">
    <aside class="sidebar">
        <h3>Guidance Topics</h3>
        <ul>
            <li><a href="general_info.php">General Information</a></li>
            <li><a href="safety.php">Medical Guidance</a></li>
            <li><a href="transport_guidance.php" class="active">Transport Guidance</a></li>
            <li><a href="money.php">Money Guidance</a></li>
            <li><a href="food.php">Food Guidance</a></li>
            <li><a href="shopping.php">Shopping Guidance</a></li>
            <li><a href="cultural_guidance.php">Cultural Guidance</a></li>
        </ul>
    </aside>

    <div class="container">
        <h1>Transport Guidance for Travelers</h1>
        <p class="intro">Getting around in a new place can be exciting — and sometimes daunting. This guide provides you with best practices, tips, and precautions for using transport modes safely, efficiently, and cost-effectively.</p>

        <h2>Table of Contents</h2>
        <ul>
            <li><a href="#planning">Planning Your Transport</a></li>
            <li><a href="#public">Public Transport Tips</a></li>
            <li><a href="#rideshare-taxi">Rideshare, Taxis & Private Transport</a></li>
            <li><a href="#long-distance">Long-Distance Travel (Trains, Buses, Flights)</a></li>
            <li><a href="#local-mobility">Last-Mile & Local Mobility</a></li>
            <li><a href="#safety-tips">Safety Tips for Transport</a></li>
        </ul>

        <h2 id="planning">Planning Your Transport</h2>
        <img src="img/transport-planning.jpg" alt="A person looking at a transport map on a phone" class="topic-image">
        <p>Before you set out, a little preparation goes a long way.</p>
        <ul>
            <li>Research the available modes in your destination (bus, metro, tram, ferry, rideshare).</li>
            <li>Check schedules and routes in advance — many cities have transit apps or websites.</li>
            <li>Budget for transport costs (fares, tolls, surge pricing) and carry small change or local transit cards.</li>
            <li>Know peak hours and avoid traveling in congested times if possible.</li>
        </ul>

        <h2 id="public">Public Transport Tips</h2>
        <img src="img/public-transport.jpg" alt="A modern city bus at a bus stop" class="topic-image">
        <p>Public transport is often the most affordable and authentic way to travel locally.</p>
        <h3>Using Buses & Trams</h3>
        <ul>
            <li>Get route maps & learn major lines; use official apps if available.</li>
            <li>Stand in designated waiting areas; board from approved stops.</li>
            <li>Hold tight, especially in crowded conditions. Keep valuables close.</li>
        </ul>
        <h3>Using Metro / Subway / Light Rail</        >
        <ul>
            <li>Follow direction signage and platform markings.</li>
            <li>Scan transit cards, tokens, or tap-in/tap-out properly to avoid fines.</li>
            <li>Avoid empty train cars at night or in unfamiliar neighborhoods.</li>
        </ul>
        <h3>Using Ferry or Water Transport</h3>
        <ul>
            <li>Check for schedules, safety measures, and weather conditions.</li>
            <li>Wear life vests if provided and stay in designated passenger zones.</li>
        </ul>

        <h2 id="rideshare-taxi">Rideshare, Taxis & Private Transport</h2>
        <img src="img/taxi.jpg" alt="A person getting into a yellow taxi cab" class="topic-image">
        <ul>
            <li>Use trusted apps or registered taxi services; avoid street hails in risky areas.</li>
            <li>Share trip details or live location with someone you trust.</li>
            <li>Verify driver identity (name, license plate) before boarding.</li>
            <li>Avoid overtipping or paying cash if your app allows in-app payment.</li>
            <li>Use child seats, seat belts, or safety restraints where available.</li>
        </ul>

        <h2 id="long-distance">Long-Distance Travel (Trains, Buses, Flights)</h2>
        <img src="img/train-window.jpg" alt="View of a landscape from a high-speed train window" class="topic-image">
        <p>For journeys between cities or countries, efficiency and comfort matter.</p>
        <ul>
            <li>Book in advance for the best fares and seat availability.</li>
            <li>Choose reputable carriers or national operators; review their safety record.</li>
            <li>Arrive early for check-in, boarding, and security checks.</li>
            <li>When possible, select seats near emergency exits or aisles.</li>
            <li>Keep your travel documents, valuables, and essentials in your carry-on.</li>
        </ul>

        <h2 id="local-mobility">Last-Mile & Local Mobility</h2>
        <img src="img/scooter-rental.jpg" alt="A row of electric scooters available for rent on a city street" class="topic-image">
        <ul>
            <li>Use walking, bicycles, or e-scooters cautiously — always check road rules.</li>
            <li>Rent from reputable providers; wear helmets and protective gear.</li>
            <li>At night, avoid walking alone; prefer rideshares or well-lit paths.</li>
            <li>Be aware of local traffic patterns, one-way systems, and sidewalks.</li>
        </ul>

        <h2 id="safety-tips">Safety Tips for Transport</h2>
        <img src="img/transport-safety.jpg" alt="A person holding their bag securely on a crowded subway" class="topic-image">
        <ul>
            <li>Keep your bag in sight or between your legs; don’t place it overhead on public transit.</li>
            <li>Avoid flaunting valuables (phones, jewelry); use discreet phone handling.</li>
            <li>Trust your instincts — if a driver, vehicle, or route feels unsafe, decline or exit.</li>
            <li>In rideshares or taxis, note license plate and driver name before boarding.</li>
            <li>Stay alert when traveling at night or through unfamiliar areas.</li>
        </ul>

        <h2>Final Thoughts</h2>
        <p>Transportation is the backbone of your travel experience. With good planning, vigilance, and reliable choices, you transform what can be stressful into seamless journeys. Use public transit smartly, choose safe and trusted services, and stay aware at all times for a smoother travel experience.</p>
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
                document.getElementById('profileDropdown').classList.remove('show');
            }
        });
    }
</script>

</body>
</html>
