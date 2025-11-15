<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Money Guidance for Travelers</title>
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

<!-- Hero Slider -->
<div class="hero-slider">
    <div class="slides-wrapper">
        <div class="slide">
            <img src="img/money-1.jpg" alt="Currency and cards">
            <div class="slide-content">
                <h2>Smart Money Moves for Travel</h2>
                <p>Best practices for handling money abroad with ease.</p>
            </div>
        </div>
        <div class="slide">
            <img src="img/money-2.jpg" alt="Wallet with foreign currency">
            <div class="slide-content">
                <h2>Manage Costs, Minimize Fees</h2>
                <p>Tips to save on exchange and banking fees.</p>
            </div>
        </div>
        <div class="slide">
            <img src="img/money-3.jpg" alt="Secure wallet">
            <div class="slide-content">
                <h2>Secure Your Funds On The Go</h2>
                <p>Protect your cards, cash, and backups while traveling.</p>
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
            <li><a href="transport.php">Transport Guidance</a></li>
            <li><a href="money.php" class="active">Money Guidance</a></li>
            <li><a href="food.php">Food Guidance</a></li>
            <li><a href="shopping.php">Shopping Guidance</a></li>
            <li><a href="culture.php">Cultural Guidance</a></li>
        </ul>
    </aside>

    <div class="container">
        <h1>Money Guidance for Travelers</h1>
        <p class="intro">Managing money while traveling can be tricky—between exchange rates, banking fees, security, and budgeting, one wrong move can cost a lot. Below is a detailed guide to help you handle money smartly on your trip.</p>

        <h2>Table of Contents</h2>
        <ul>
            <li><a href="#pre-trip">Pre-trip Money Prep</a></li>
            <li><a href="#cash-vs-card">Cash vs Card: What to Use When</a></li>
            <li><a href="#exchange-withdrawals">Currency Exchange & Withdrawals</a></li>
            <li><a href="#budgeting">Budgeting & Saving Strategies</a></li>
            <li><a href="#security">Security & Backup Plans</a></li>
            <li><a href="#tips">Extra Tips & Common Pitfalls</a></li>
        </ul>

        <h2 id="pre-trip">Pre-trip Money Prep</h2>
        <img src="img/money-prep.jpg" alt="A person planning a trip with cards and currency on a map" class="topic-image">
        <p>Prepare well before departure so money issues don’t derail your trip.</p>
        <ul>
            <li>Check your credit/debit cards for foreign transaction fees, ATM withdrawal fees, and daily limits.</li>
            <li>Notify your bank that you’ll be traveling (dates, countries) to avoid card blocks for “suspicious activity.”</li>
            <li>Order a backup card(s) and keep them in separate safe places.</li>
            <li>Consider getting a prepaid travel money card or multi-currency travel debit card.</li>
            <li>Have some local currency (small amount) ready before travel for immediate small expenses like transport or tips.</li>
        </ul>

        <h2 id="cash-vs-card">Cash vs Card: What to Use When</h2>
        <img src="img/cash-card.jpg" alt="A hand holding both cash and a credit card" class="topic-image">
        <p>Understanding when and how to use cash or card abroad can save you fees and headaches.</p>
        <h3>Using Cash</h3>
        <ul>
            <li>Pros: accepted everywhere (especially small vendors or rural areas), helps with budgeting.</li>
            <li>Cons: risk of theft or loss, unfavorable exchange rates at tourist spots.</li>
        </ul>
        <h3>Using Debit / Prepaid Travel Cards</h3>
        <ul>
            <li>Often lower fees than cash exchange; many cards let you spend in foreign currencies with minimal markup.</li>
            <li>Can withdraw local currency from ATMs. But watch ATM usage fees from both your bank and the local ATM.</li>
        </ul>
        <h3>Using Credit Cards</h3>
        <ul>
            <li>Great protection (fraud detection, purchase disputes).</li>
            <li>Choose cards with no foreign transaction fees and avoid dynamic currency conversion (always pay in local currency).</li>
            <li>Avoid using credit cards for ATM cash withdrawals unless absolutely necessary due to high interest and fees.</li>
        </ul>

        <h2 id="exchange-withdrawals">Currency Exchange & Withdrawals</h2>
        <img src="img/atm-exchange.jpg" alt="A person using an ATM in a foreign country" class="topic-image">
        <p>Where, when, and how you exchange money or withdraw from ATM matters a lot.</p>
        <ul>
            <li>Avoid exchanging money at airports or hotels — the rates are often very poor.</li>
            <li>Use ATMs from established banks in safer locations rather than independent/standalone ATMs.</li>
            <li>Withdraw larger amounts less frequently to minimize repeated ATM fees.</li>
            <li>Be aware of all fees: your bank’s fee, the ATM operator’s surcharge, and currency conversion markups.</li>
            <li>Always compare “buy” vs “sell” exchange rates, and ask for no commission where possible.</li>
        </ul>

        <h2 id="budgeting">Budgeting & Saving Strategies</h2>
        <img src="img/budget-app.jpg" alt="A smartphone displaying a travel budgeting application" class="topic-image">
        <p>Smart budgeting and cost-saving can stretch your trip further.</p>
        <ul>
            <li>Set a daily spending limit and track your expenses via apps or spreadsheets.</li>
            <li>Pack lightly to avoid excess baggage charges.</li>
            <li>Eat at local markets or street food stalls instead of tourist-heavy restaurants.</li>
            <li>Prepay for major services (hotels, tours) where discounts are offered.</li>
            <li>Use loyalty programs, cashback, or travel reward cards to offset costs.</li>
        </ul>

        <h2 id="security">Security & Backup Plans</h2>
        <img src="img/money-belt.jpg" alt="A discreet money belt worn under clothing" class="topic-image">
        <p>Having a backup can save you from crisis if your money or cards are lost or compromised.</p>
        <ul>
            <li>Don’t carry all your money in one place — split it between your wallet, a safe, and your luggage.</li>
            <li>Store digital (scanned) copies of passports and credit cards in a secure cloud service.</li>
            <li>Keep bank/issuer contact numbers (including international helplines) saved in your phone and on paper.</li>
            <li>Use money belts or hidden pouches in crowded or touristy areas.</li>
            <li>If a card is lost or stolen, report it immediately to your bank to block it and request a replacement.</li>
        </ul>

        <h2 id="tips">Extra Tips & Common Pitfalls</h2>
        <img src="img/money-tips.jpg" alt="A lightbulb icon over a pile of coins and notes" class="topic-image">
        <ul>
            <li>Avoid dynamic currency conversion — always choose to pay in the “local currency” at payment terminals.</li>
            <li>Be cautious of ATMs in dark, isolated places; prefer those inside banks.</li>
            <li>Watch out for card skimming devices—cover your keypad and use trusted ATMs. </li>
            <li>Don’t ignore small fees, as even “tiny” surcharges can add up significantly over a trip.</li>
            <li>Sell leftover foreign currency at your home bank or spend small amounts before leaving a country.</li>
        </ul>

        <h2>Final Thoughts</h2>
        <p>Handling money on the road requires vigilance and planning. By preparing ahead, using the right mix of cash and cards, budgeting smartly, and safeguarding your funds, you’ll be better positioned to enjoy your trip without financial stress.</p>
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
    </div>
</header>

<!-- Hero Slider -->
<div class="hero-slider">
    <div class="slides-wrapper">
        <div class="slide">
            <img src="img/money-1.jpg" alt="Currency and cards">
            <div class="slide-content">
                <h2>Smart Money Moves for Travel</h2>
                <p>Best practices for handling money abroad with ease.</p>
            </div>
        </div>
        <div class="slide">
            <img src="img/money-2.jpg" alt="Wallet with foreign currency">
            <div class="slide-content">
                <h2>Manage Costs, Minimize Fees</h2>
                <p>Tips to save on exchange and banking fees.</p>
            </div>
        </div>
        <div class="slide">
            <img src="img/money-3.jpg" alt="Secure wallet">
            <div class="slide-content">
                <h2>Secure Your Funds On The Go</h2>
                <p>Protect your cards, cash, and backups while traveling.</p>
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
            <li><a href="transport.php">Transport Guidance</a></li>
            <li><a href="money.php" class="active">Money Guidance</a></li>
            <li><a href="food.php">Food Guidance</a></li>
            <li><a href="shopping.php">Shopping Guidance</a></li>
            <li><a href="culture.php">Cultural Guidance</a></li>
        </ul>
    </aside>

    <div class="container">
        <h1>Money Guidance for Travelers</h1>
        <p class="intro">Managing money while traveling can be tricky—between exchange rates, banking fees, security, and budgeting, one wrong move can cost a lot. Below is a detailed guide to help you handle money smartly on your trip.</p>

        <h2>Table of Contents</h2>
        <ul>
            <li><a href="#pre-trip">Pre-trip Money Prep</a></li>
            <li><a href="#cash-vs-card">Cash vs Card: What to Use When</a></li>
            <li><a href="#exchange-withdrawals">Currency Exchange & Withdrawals</a></li>
            <li><a href="#budgeting">Budgeting & Saving Strategies</a></li>
            <li><a href="#security">Security & Backup Plans</a></li>
            <li><a href="#tips">Extra Tips & Common Pitfalls</a></li>
        </ul>

        <h2 id="pre-trip">Pre-trip Money Prep</h2>
        <img src="img/money-prep.jpg" alt="A person planning a trip with cards and currency on a map" class="topic-image">
        <p>Prepare well before departure so money issues don’t derail your trip.</p>
        <ul>
            <li>Check your credit/debit cards for foreign transaction fees, ATM withdrawal fees, and daily limits.</li>
            <li>Notify your bank that you’ll be traveling (dates, countries) to avoid card blocks for “suspicious activity.”</li>
            <li>Order a backup card(s) and keep them in separate safe places.</li>
            <li>Consider getting a prepaid travel money card or multi-currency travel debit card.</li>
            <li>Have some local currency (small amount) ready before travel for immediate small expenses like transport or tips.</li>
        </ul>

        <h2 id="cash-vs-card">Cash vs Card: What to Use When</h2>
        <img src="img/cash-card.jpg" alt="A hand holding both cash and a credit card" class="topic-image">
        <p>Understanding when and how to use cash or card abroad can save you fees and headaches.</p>
        <h3>Using Cash</h3>
        <ul>
            <li>Pros: accepted everywhere (especially small vendors or rural areas), helps with budgeting.</li>
            <li>Cons: risk of theft or loss, unfavorable exchange rates at tourist spots.</li>
        </ul>
        <h3>Using Debit / Prepaid Travel Cards</h3>
        <ul>
            <li>Often lower fees than cash exchange; many cards let you spend in foreign currencies with minimal markup.</li>
            <li>Can withdraw local currency from ATMs. But watch ATM usage fees from both your bank and the local ATM.</li>
        </ul>
        <h3>Using Credit Cards</h3>
        <ul>
            <li>Great protection (fraud detection, purchase disputes).</li>
            <li>Choose cards with no foreign transaction fees and avoid dynamic currency conversion (always pay in local currency).</li>
            <li>Avoid using credit cards for ATM cash withdrawals unless absolutely necessary due to high interest and fees.</li>
        </ul>

        <h2 id="exchange-withdrawals">Currency Exchange & Withdrawals</h2>
        <img src="img/atm-exchange.jpg" alt="A person using an ATM in a foreign country" class="topic-image">
        <p>Where, when, and how you exchange money or withdraw from ATM matters a lot.</p>
        <ul>
            <li>Avoid exchanging money at airports or hotels — the rates are often very poor.</li>
            <li>Use ATMs from established banks in safer locations rather than independent/standalone ATMs.</li>
            <li>Withdraw larger amounts less frequently to minimize repeated ATM fees.</li>
            <li>Be aware of all fees: your bank’s fee, the ATM operator’s surcharge, and currency conversion markups.</li>
            <li>Always compare “buy” vs “sell” exchange rates, and ask for no commission where possible.</li>
        </ul>

        <h2 id="budgeting">Budgeting & Saving Strategies</h2>
        <img src="img/budget-app.jpg" alt="A smartphone displaying a travel budgeting application" class="topic-image">
        <p>Smart budgeting and cost-saving can stretch your trip further.</p>
        <ul>
            <li>Set a daily spending limit and track your expenses via apps or spreadsheets.</li>
            <li>Pack lightly to avoid excess baggage charges.</li>
            <li>Eat at local markets or street food stalls instead of tourist-heavy restaurants.</li>
            <li>Prepay for major services (hotels, tours) where discounts are offered.</li>
            <li>Use loyalty programs, cashback, or travel reward cards to offset costs.</li>
        </ul>

        <h2 id="security">Security & Backup Plans</h2>
        <img src="img/money-belt.jpg" alt="A discreet money belt worn under clothing" class="topic-image">
        <p>Having a backup can save you from crisis if your money or cards are lost or compromised.</p>
        <ul>
            <li>Don’t carry all your money in one place — split it between your wallet, a safe, and your luggage.</li>
            <li>Store digital (scanned) copies of passports and credit cards in a secure cloud service.</li>
            <li>Keep bank/issuer contact numbers (including international helplines) saved in your phone and on paper.</li>
            <li>Use money belts or hidden pouches in crowded or touristy areas.</li>
            <li>If a card is lost or stolen, report it immediately to your bank to block it and request a replacement.</li>
        </ul>

        <h2 id="tips">Extra Tips & Common Pitfalls</h2>
        <img src="img/money-tips.jpg" alt="A lightbulb icon over a pile of coins and notes" class="topic-image">
        <ul>
            <li>Avoid dynamic currency conversion — always choose to pay in the “local currency” at payment terminals.</li>
            <li>Be cautious of ATMs in dark, isolated places; prefer those inside banks.</li>
            <li>Watch out for card skimming devices—cover your keypad and use trusted ATMs. </li>
            <li>Don’t ignore small fees, as even “tiny” surcharges can add up significantly over a trip.</li>
            <li>Sell leftover foreign currency at your home bank or spend small amounts before leaving a country.</li>
        </ul>

        <h2>Final Thoughts</h2>
        <p>Handling money on the road requires vigilance and planning. By preparing ahead, using the right mix of cash and cards, budgeting smartly, and safeguarding your funds, you’ll be better positioned to enjoy your trip without financial stress.</p>
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
