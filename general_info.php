<?php
session_start();
require 'db.php';

// Fetch the current user's avatar for the header
$user_avatar_header = null;
if (isset($_SESSION['user'])) {
    $username = $_SESSION['user'];
    // Prepare statement to prevent SQL injection
    $avatar_stmt = $conn->prepare("SELECT avatar FROM users WHERE username = ?");
    if ($avatar_stmt) {
        $avatar_stmt->bind_param("s", $username);
        $avatar_stmt->execute();
        $user_avatar_header = $avatar_stmt->get_result()->fetch_assoc()['avatar'] ?? null;
        $avatar_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>General Information – Travel Documents</title>
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

    /* Header Styles from forum.php */
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
    .legal-links { display: flex; gap: 20px; }

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
    h2 { border-left: 4px solid var(--accent); padding-left: 0.8em; font-size: 1.8em; }
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
    .section { margin-bottom: 2.5em; }
    .tips { margin-top: 1em; padding-left: 1em; background: var(--glass); border-radius: 8px; padding: 15px 20px; }
    .tips ul { padding-left: 20px; margin: 0; }
    .tips li { margin-bottom: 0.8em; color: var(--muted); }
    .faq { margin-top: 2em; }
    .faq h3 { margin-top: 1.2em; color: #e6eef8; }
    .faq dd { margin-left: 1em; margin-bottom: 1em; color: var(--muted); }
    .conclusion {
      margin-top: 2em;
      padding: 1em;
      background: rgba(29, 155, 240, 0.1);
      border-left: 4px solid var(--accent);
      border-radius: 0 8px 8px 0;
    }

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
                <li><a href="index.php">Home</a></li>
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
                    <div class="dropdown-header"><?php echo htmlspecialchars($_SESSION['user']); ?></div>
                    <a href="profile.php"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg> Profile</a>
                    <a href="logout.php"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg> Logout</a>
                </div>
            </div>
            <?php else: ?>
                <button onclick="window.location.href='login.php'">Login</button>
                <button class="btn-primary" onclick="window.location.href='signup.php'">Sign Up</button>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- Hero Slider -->
<div class="hero-slider">
    <div class="slides-wrapper">
        <div class="slide">
            <img src="img/general-1.jpg" alt="Slide 1">
            <div class="slide-content">
                <h2>Travel Smart, Travel Safe</h2>
                <p>Your guide to essential travel knowledge.</p>
            </div>
        </div>
        <div class="slide">
            <img src="img/general-2.jpg" alt="Slide 2">
            <div class="slide-content">
                <h2>Plan with Confidence</h2>
                <p>Everything you need to know before you go.</p>
            </div>
        </div>
        <div class="slide">
            <img src="img/general-3.jpg" alt="Slide 3">
            <div class="slide-content">
                <h2>Journey Without Worry</h2>
                <p>Stay informed and enjoy a seamless adventure.</p>
            </div>
        </div>
    </div>
</div>

<div class="page-content-wrapper">
    <aside class="sidebar">
        <h3>Guidance Topics</h3>
        <ul>
            <li><a href="general_info.php" class="active">General Information</a></li>
            <li><a href="safety.php">Medical Guidance</a></li>
            <li><a href="transport.php">Transport Guidance</a></li>
            <li><a href="money.php">Money Guidance</a></li>
            <li><a href="food.php">Food Guidance</a></li>
            <li><a href="shopping.php">Shopping Guidance</a></li>
            <li><a href="culture.php">Cultural Guidance</a></li>
        </ul>
    </aside>

    <div class="container">
        <!-- Page Title -->
        <h1>Essential Travel Documents You Must Carry</h1>
        <p class="intro">
          Travel can be exhilarating—but the one thing that can ground your trip (literally) is missing paperwork. Below is a deep dive into every document you should prioritize to ensure your journey remains smooth, safe, and stress-free.
        </p>

        <!-- Table of Contents -->
        <h2>Table of Contents</h2>
        <ul>
            <li><a href="#passport">1. Passport: Your Primary Travel Identity</a></li> 
            <li><a href="#visa">2. Visa & Entry / Exit Permits</a></li>
            <li><a href="#insurance">3. Travel Insurance & Medical Documentation</a></li>
            <li><a href="#proof">4. Proof of Travel & Accommodation</a></li>
            <li><a href="#local-id">5. National ID & Special Local Documents</a></li>
            <li><a href="#faq">Frequently Asked Questions</a></li>
        </ul>

        <!-- Section: Passport -->
        <div class="section" id="passport">
          <h2>1. Passport: Your Primary Travel Identity</h2>
          <img src="img/passport.jpg" alt="A person holding a passport with travel stamps" class="topic-image">
          <p>
            Your passport isn’t just a booklet of stamps—it’s your identity on the global stage. Whether checking into hotels, passing through immigration, or dealing with lost luggage, this document will frequently be your lifeline.
          </p>
          <p>
            <strong>Check Validity & Conditions:</strong> Many countries require that your passport be valid for at least <em>six months beyond your intended return date</em>. An otherwise perfect travel plan can be derailed at the immigration desk if your passport expires too soon.
          </p>
          <div class="tips">
            <ul>
              <li>Count blank visa pages—some nations require two consecutive pages.</li>
              <li>Inspect your passport’s condition—avoid tears, stains, or water damage.</li>
              <li>Make & carry photocopies or scanned backups stored separately.</li>
              <li>Use a waterproof sleeve or travel pouch for extra protection.</li>
            </ul>
          </div>
        </div>

        <!-- Section: Visa & Entry Permits -->
        <div class="section" id="visa">
          <h2>2. Visa & Entry / Exit Permits</h2>
          <img src="img/visa-stamp.jpg" alt="Close-up of a visa stamp in a passport" class="topic-image">
          <p>
            While many travelers assume visas are only for exotic lands, nearly every country defines its own rules for entry. Whether you need a visa prior to arrival or can get one on entry depends on your nationality and your destination.
          </p>
          <p>
            <strong>Key considerations include:</strong> visa type (tourist, work, student), duration of stay, whether you can extend, and any extra documentation required (proof of funds, travel itinerary, etc.).
          </p>
          <div class="tips">
            <ul>
              <li>Visit official embassy or immigration websites for requirements.</li>
              <li>Apply well in advance—some visas take weeks or more to process.</li>
              <li>Carry both digital and printed versions of any visa/approval.</li>
              <li>If your journey passes through transit countries, check for transit visa rules too.</li>
            </ul>
          </div>
        </div>

        <!-- Section: Travel Insurance & Medical Documents -->
        <div class="section" id="insurance">
          <h2>3. Travel Insurance & Medical Documentation</h2>
          <img src="img/insurance-doc.jpg" alt="Travel insurance documents next to a first-aid kit" class="topic-image">
          <p>
            Missing a flight is grim; not being able to access medical care abroad is worse. A good travel insurance policy can turn a crisis into a manageable event.
          </p>
          <p>
            Your policy should cover medical emergencies, evacuation, trip cancellation, and lost or stolen items. In many destinations, proof of coverage is mandatory.
          </p>
          <div class="tips">
            <ul>
              <li>Ensure your policy covers pre-existing conditions if needed.</li>
              <li>Store your policy number, contact details, and claim process.</li>
              <li>Some countries now require specific vaccination certificates (yellow fever, COVID, etc.).</li>
              <li>Bring extra doses of essential prescriptions if you have chronic conditions.</li>
            </ul>
          </div>
        </div>

        <!-- Section: Proof of Travel & Accommodation -->
        <div class="section" id="proof">
          <h2>4. Proof of Travel & Accommodation</h2>
          <img src="img/booking.jpg" alt="A smartphone showing a flight booking confirmation on the screen" class="topic-image">
          <p>
            Border officials often ask: “Where will you stay?” and “How will you depart?” Having solid proof gives you credibility and prevents sticky situations.
          </p>
          <div class="tips">
            <ul>
              <li>Carry your flight itinerary (outbound + return or onward).</li>
              <li>Bring hotel or rental confirmations (printed and mobile screenshots).</li>
              <li>Maintain a basic travel plan or route outline for authorities if asked.</li>
            </ul>
          </div>
        </div>

        <!-- Section: National ID & Special Permits -->
        <div class="section" id="local-id">
          <h2>5. National ID & Special Local Documents</h2>
          <img src="img/id-card.jpg" alt="A collection of different national ID cards and a driver's license" class="topic-image">
          <p>
            National identity documents and local permits complement your international travel paperwork—especially when you roam within or near your own country, or enter restricted zones abroad.
          </p>
          <div class="tips">
            <ul>
              <li>Carry a driver’s license, national identity card, or voter ID as a backup.</li>
              <li>Check if protected zones, heritage sites or restricted border areas require special permits.</li>
              <li>When traveling with minors, carry birth certificates or consent letters if required.</li>
              <li>Student IDs or work permits may help in access to discounts or expedited entry.</li>
            </ul>
          </div>
        </div>

        <!-- FAQ Section -->
        <div class="faq" id="faq">
          <h2>Frequently Asked Questions</h2>
          <h3>Can I travel with an expired visa?</h3>
          <dd>No. Even if you previously held a visa, an expired visa is unusable. Always travel with valid, unexpired documents.</dd>

          <h3>What if my documents are stolen?</h3>
          <dd>
            Report immediately to local authorities (police), contact your embassy/consulate, and use your backup copies (scans/photocopies) to expedite replacement.
          </dd>

          <h3>Are digital copies enough?</h3>
          <dd>
            Originals are mandatory for immigration, boarding, and many transactions. Digital copies are backups for emergencies—not a substitute.
          </dd>

          <h3>Do I need documents for transit countries?</h3>
          <dd>
            Yes. Even if you’re just passing through and not leaving the airport, some transit nations require transit visas or have document checks. Always verify.
          </dd>
        </div>

        <!-- Conclusion -->
        <div class="conclusion">
          <h2>Final Thoughts</h2>
          <p>
            Documents are more than formalities—they are your safety net when traveling across regions, cultures, and legal systems. Investing just a little time in preparation, backup, and organization goes a long way toward ensuring a hassle-free experience.
          </p>
          <p>
            Use a checklist, review everything a week beforehand, and keep backups (physical & digital) for all your crucial papers. When those pieces are in place, you leave worry behind and travel with confidence.
          </p>
        </div>
    </div>
  </div>

<?php require 'footer.php'; ?>

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

    // The profile dropdown JS is now in header.php
</script>
</body>
</body>
</html>
