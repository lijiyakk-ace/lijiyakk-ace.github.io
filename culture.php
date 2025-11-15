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
  <title>Cultural Guidance & Etiquette</title>
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

    /* Footer Styles from index.php */
    .site-footer { background-color: var(--card); color: var(--muted); padding: 50px 50px 20px; border-top: 1px solid rgba(255,255,255,0.07); }
    .footer-main { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 40px; margin-bottom: 40px; }
    .footer-column h4 { color: #e6eef8; font-size: 16px; margin-bottom: 15px; font-weight: 600; }
    .footer-column ul { list-style: none; padding: 0; margin: 0; }
    .footer-column ul li { margin-bottom: 10px; }
    .footer-column ul a { color: var(--muted); text-decoration: none; font-size: 14px; transition: color 0.3s ease; }
    .footer-column ul a:hover { color: var(--accent); }
    .payment-methods, .app-buttons { display: flex; flex-wrap: wrap; gap: 10px; }
    .payment-methods img, .app-buttons img { height: 30px; }
    .app-buttons img { height: 40px; }
    .footer-bottom { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; padding-top: 20px; border-top: 1px solid var(--glass); font-size: 13px; }
    .social-links { display: flex; gap: 15px; }
    .social-links a { color: var(--muted); transition: color 0.3s ease; }
    .social-links a:hover { color: #fff; }
    .social-links svg { width: 20px; height: 20px; }
    .legal-links { display: flex; gap: 20px; }
    .legal-links a { color: var(--muted); text-decoration: none; }
    .legal-links a:hover { text-decoration: underline; }
    @media (max-width: 768px) {
        .footer-bottom {
            flex-direction: column;
            gap: 20px;
        }
    }
    /* Overwrite sidebar ul styles for footer */
    .footer-column ul {
        padding-left: 0;
    }
    .footer-column ul li {
        margin-bottom: 10px;
        color: var(--muted);
    }
    .footer-column ul li a {
        color: var(--muted);
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
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    Profile
                </a>
                <a href="logout.php">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
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
      <img src="img/culture-1.jpg" alt="Cultural scene">
      <div class="slide-content">
        <h2>Embrace Cultural Understanding</h2>
        <p>Essential etiquette for every traveler.</p>
      </div>
    </div>
    <div class="slide">
      <img src="img/culture-2.jpg" alt="Local custom">
      <div class="slide-content">
        <h2>Respect Differences, Connect Deeply</h2>
        <p>How to avoid faux pas and show cultural respect.</p>
      </div>
    </div>
    <div class="slide">
      <img src="img/culture-3.jpg" alt="Traditional greeting">
      <div class="slide-content">
        <h2>Be a Thoughtful Guest</h2>
        <p>Guidelines to blend in and build goodwill.</p>
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
      <li><a href="money.php">Money Guidance</a></li>
      <li><a href="food.php">Food Guidance</a></li>
      <li><a href="shopping.php">Shopping Guidance</a></li>
      <li><a href="culture.php" class="active">Cultural Guidance</a></li>
    </ul>
  </aside>

  <div class="container">
    <h1>Cultural Guidance & Etiquette for Travelers</h1>
    <p class="intro">Traveling into new cultures is exciting—but also sensitive. Knowing local customs, respectful behaviors, and cultural norms can help you avoid misunderstandings and foster meaningful connections. Use this guide as your compass to travel with dignity and awareness.</p>

    <h2>Table of Contents</h2>
    <ul>
      <li><a href="#why-culture">Why Cultural Etiquette Matters</a></li>
      <li><a href="#greetings-communication">Greetings & Communication</a></li>
      <li><a href="#dress-appearance">Dress, Appearance & Modesty</a></li>
      <li><a href="#gestures-body-language">Gestures, Body Language & Personal Space</a></li>
      <li><a href="#religion-customs">Religion, Rituals & Sensitivities</a></li>
      <li><a href="#dining-etiquette">Dining Etiquette</a></li>
      <li><a href="#photography-conduct">Photography & Public Conduct</a></li>
      <li><a href="#handling-mistakes">Handling Mistakes & Recovery</a></li>
    </ul>

    <h2 id="why-culture">Why Cultural Etiquette Matters</h2>
    <img src="img/culture-why.jpg" alt="Two people from different cultures shaking hands warmly" class="topic-image">
    <p>When traveling, you are a guest in someone else’s society. Observing etiquette helps:</p>
    <ul>
      <li>Show respect and humility rather than ignorance or arrogance.</li>
      <li>Prevent unintentional offense or misunderstandings.</li>
      <li>Open doors for more sincere interactions with locals.</li>
      <li>Foster goodwill and positive memories.</li>
    </ul>

    <h2 id="greetings-communication">Greetings & Communication</h2>
    <img src="img/culture-greeting.jpg" alt="A person learning to say hello from a local guide" class="topic-image">
    <ul>
      <li>Learn a few basic local phrases: hello, thank you, please, excuse me.</li>
      <li>Research greeting norms: handshake, bow, cheek-kiss, nod, etc.</li>
      <li>Be attentive to gender dynamics—some societies frown on cross-gender physical contact. :contentReference[oaicite:0]{index=0}</li>
      <li>Avoid startling people—address elders or strangers politely and with deference.</li>
      <li>Listen more than you speak—sometimes silence or gesture says more.</li>
      </ul>

    <h2 id="dress-appearance">Dress, Appearance & Modesty</h2>
    <img src="img/culture-dress.jpg" alt="A traveler wearing modest clothing appropriate for a temple" class="topic-image">
    <ul>
      <li>Check dress codes beforehand—religious sites often require covered shoulders, legs, head coverings.</li>
      <li>Avoid conspicuous or provocative clothing in conservative regions. :contentReference[oaicite:1]{index=1}</li>
      <li>Remove hats or shoes when entering private homes or sacred places if that’s expected.</li>
      <li>Use neutral colors when unsure—don’t attract attention by flashy attire in sensitive regions.</li>
    </ul>

    <h2 id="gestures-body-language">Gestures, Body Language & Personal Space</h2>
    <img src="img/culture-gestures.jpg" alt="A chart showing different hand gestures and their meanings" class="topic-image">
    <ul>
      <li>Be cautious with hand gestures—some mean very different things elsewhere (thumbs-up, “OK”, beckoning fingers). :contentReference[oaicite:2]{index=2}</li>
      <li>Avoid touching someone’s head (in some cultures, it’s sacred). :contentReference[oaicite:3]{index=3}</li>
      <li>Don’t point directly at people—point with your whole hand or nod instead. :contentReference[oaicite:4]{index=4}</li>
      <li>Respect personal space—what’s normal in one country may be intrusive in another. :contentReference[oaicite:5]{index=5}</li>
      <li>Be careful with left hand usage—some cultures deem it “unclean” to eat or give/receive items with left. :contentReference[oaicite:6]{index=6}</li>
    </ul>

    <h2 id="religion-customs">Religion, Rituals & Sensitivities</h2>
    <img src="img/culture-religion.jpg" alt="A quiet moment of observation inside a beautiful mosque" class="topic-image">
    <ul>
      <li>Research religious practices (prayer times, fasting, dress, prohibited behaviors). :contentReference[oaicite:7]{index=7}</li>
      <li>Avoid visiting sacred sites during worship times without checking permission.</li>
      <li>Observe rules about photography (some religious buildings or ceremonies prohibit photos).</li>
      <li>Don’t joke about, criticize, or debate someone’s beliefs unless you are invited and tread extremely carefully.</li>
    </ul>

    <h2 id="dining-etiquette">Dining Etiquette</h2>
    <img src="img/culture-dining.jpg" alt="A group of people sharing a meal at a traditional dining table" class="topic-image">
    <ul>
      <li>Learn local dining manners: hand used for eating, communal dishes, sharing, burping norms, etc. :contentReference[oaicite:8]{index=8}</li>
      <li>Wait for the host to eat first if applicable.</li>
      <li>Don’t overfill your plate; leaving some may signify fullness or appreciation (depending on culture). :contentReference[oaicite:9]{index=9}</li>
      <li>Avoid criticizing flavors, ingredients, or hygiene, even if you don’t like something—be tactful and polite.</li>
      <li>Check tipping practices—some cultures expect gratuity, others consider it rude. :contentReference[oaicite:10]{index=10}</li>
    </ul>

    <h2 id="photography-conduct">Photography & Public Conduct</h2>
    <img src="img/culture-photo.jpg" alt="A photographer respectfully asking a local artisan for permission to take a photo" class="topic-image">
    <ul>
      <li>Always ask permission before photographing people—especially elders, children, or religious figures. :contentReference[oaicite:11]{index=11}</li>
      <li>Be careful where you take selfies or photos—some places (memorials, religious grounds, private property) find it disrespectful. :contentReference[oaicite:12]{index=12}</li>
      <li>Avoid loud or boisterous behavior in quiet or sacred places—keep your voice low.</li>
      <li>Don’t litter, damage property, or disrespect local symbols, art, or heritage. :contentReference[oaicite:13]{index=13}</li>
    </ul>

    <h2 id="handling-mistakes">Handling Mistakes & Recovery</h2>
    <img src="img/culture-apology.jpg" alt="A traveler showing a gesture of apology and humility" class="topic-image">
    <p>Even with research, you might slip up. Here’s how to address it gracefully.</p>
    <ul>
      <li>Apologize sincerely if you offend—“I’m sorry—I didn’t know” often works.</li>
      <li>Be open to correction and learn rather than defend or argue.</li>
      <li>Respect local reactions; if someone seems upset, withdraw and show humility.</li>
      <li>Use observing locals as guides—copy respectful behavior after you notice what’s accepted.</li>
    </ul>

    <h2>Final Thoughts</h2>
    <p>Cultural understanding transforms travel from surface-level sightseeing into meaningful connection. It shows respect, opens doors, and enriches your journey. Be humble, curious, and conscientious—let local people teach you, and you’ll come home not just with selfies, but with stories of respect and friendship.</p>
  </div>
</div>

<!-- Footer -->
<footer class="site-footer">
    <div class="footer-main">
        <div class="footer-column">
            <h4>Support</h4>
            <ul>
                <li><a href="#">Help Center</a></li>
                <li><a href="#">Live Chat</a></li>
                <li><a href="#">Contact Us</a></li>
                <li><a href="#">FAQ</a></li>
            </ul>
        </div>
        <div class="footer-column">
            <h4>Company</h4>
            <ul>
                <li><a href="#">About Us</a></li>
                <li><a href="#">Careers</a></li>
                <li><a href="#">Blog</a></li>
                <li><a href="#">Press</a></li>
                <li><a href="admin/login.php">Admin</a></li>
            </ul>
        </div>
        <div class="footer-column">
            <h4>Work with us</h4>
            <ul>
                <li><a href="#">As a Supplier</a></li>
                <li><a href="#">As a Content Creator</a></li>
                <li><a href="#">As an Affiliate Partner</a></li>
            </ul>
        </div>
        <div class="footer-column">
            <h4>Follow Us</h4>
            <div class="social-links">
                <a href="#" title="Instagram"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.85s-.011 3.584-.069 4.85c-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07s-3.584-.012-4.85-.07c-3.252-.148-4.771-1.691-4.919-4.919-.058-1.265-.069-1.645-.069-4.85s.011-3.584.069-4.85c.149-3.225 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.85-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948s.014 3.667.072 4.947c.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072s3.667-.014 4.947-.072c4.358-.2 6.78-2.618 6.98-6.98.059-1.281.073-1.689.073-4.948s-.014-3.667-.072-4.947c-.2-4.358-2.618-6.78-6.98-6.98C15.667.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.88 1.44 1.44 0 000-2.88z"/></svg></a>
                <a href="#" title="X (Twitter)"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616v.064c0 2.298 1.634 4.212 3.793 4.649-.65.177-1.354.23-2.06.088.62 1.924 2.413 3.32 4.543 3.358-1.732 1.359-3.92 2.169-6.29 2.169-.409 0-.812-.023-1.21-.07 2.236 1.434 4.893 2.271 7.734 2.271 9.284 0 14.376-7.699 14.005-14.402.995-.718 1.858-1.612 2.543-2.639z"/></svg></a>
                <a href="#" title="YouTube"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3I68z"/></svg></a>
            </div>
            <h4 style="margin-top: 20px;">Get the app</h4>
        </div>
    </div>
    <div class="footer-bottom">
        <span>&copy; <?php echo date("Y"); ?> Travel Tales. All rights reserved.</span>
        <div class="legal-links">
            <a href="#">Imprint</a>
            <a href="#">Terms & Conditions</a>
            <a href="#">Privacy Statement</a>
        </div>
        <div class="social-links">
            <a href="#" title="Facebook"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/></svg></a>
            <a href="#" title="Instagram"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.85s-.011 3.584-.069 4.85c-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07s-3.584-.012-4.85-.07c-3.252-.148-4.771-1.691-4.919-4.919-.058-1.265-.069-1.645-.069-4.85s.011-3.584.069-4.85c.149-3.225 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.85-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948s.014 3.667.072 4.947c.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072s3.667-.014 4.947-.072c4.358-.2 6.78-2.618 6.98-6.98.059-1.281.073-1.689.073-4.948s-.014-3.667-.072-4.947c-.2-4.358-2.618-6.78-6.98-6.98C15.667.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.88 1.44 1.44 0 000-2.88z"/></svg></a>
            <a href="#" title="Twitter"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616v.064c0 2.298 1.634 4.212 3.793 4.649-.65.177-1.354.23-2.06.088.62 1.924 2.413 3.32 4.543 3.358-1.732 1.359-3.92 2.169-6.29 2.169-.409 0-.812-.023-1.21-.07 2.236 1.434 4.893 2.271 7.734 2.271 9.284 0 14.376-7.699 14.005-14.402.995-.718 1.858-1.612 2.543-2.639z"/></svg></a>
        </div>
    </div>
</footer>

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
      <img src="img/culture-1.jpg" alt="Cultural scene">
      <div class="slide-content">
        <h2>Embrace Cultural Understanding</h2>
        <p>Essential etiquette for every traveler.</p>
      </div>
    </div>
    <div class="slide">
      <img src="img/culture-2.jpg" alt="Local custom">
      <div class="slide-content">
        <h2>Respect Differences, Connect Deeply</h2>
        <p>How to avoid faux pas and show cultural respect.</p>
      </div>
    </div>
    <div class="slide">
      <img src="img/culture-3.jpg" alt="Traditional greeting">
      <div class="slide-content">
        <h2>Be a Thoughtful Guest</h2>
        <p>Guidelines to blend in and build goodwill.</p>
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
      <li><a href="transport_guidance.php">Transport Guidance</a></li>
      <li><a href="money.php">Money Guidance</a></li>
      <li><a href="food.php">Food Guidance</a></li>
      <li><a href="shopping.php">Shopping Guidance</a></li>
      <li><a href="cultural.php" class="active">Cultural Guidance</a></li>
    </ul>
  </aside>

  <div class="container">
    <h1>Cultural Guidance & Etiquette for Travelers</h1>
    <p class="intro">Traveling into new cultures is exciting—but also sensitive. Knowing local customs, respectful behaviors, and cultural norms can help you avoid misunderstandings and foster meaningful connections. Use this guide as your compass to travel with dignity and awareness.</p>

    <h2>Table of Contents</h2>
    <ul>
      <li><a href="#why-culture">Why Cultural Etiquette Matters</a></li>
      <li><a href="#greetings-communication">Greetings & Communication</a></li>
      <li><a href="#dress-appearance">Dress, Appearance & Modesty</a></li>
      <li><a href="#gestures-body-language">Gestures, Body Language & Personal Space</a></li>
      <li><a href="#religion-customs">Religion, Rituals & Sensitivities</a></li>
      <li><a href="#dining-etiquette">Dining Etiquette</a></li>
      <li><a href="#photography-conduct">Photography & Public Conduct</a></li>
      <li><a href="#handling-mistakes">Handling Mistakes & Recovery</a></li>
    </ul>

    <h2 id="why-culture">Why Cultural Etiquette Matters</h2>
    <img src="img/culture-why.jpg" alt="Two people from different cultures shaking hands warmly" class="topic-image">
    <p>When traveling, you are a guest in someone else’s society. Observing etiquette helps:</p>
    <ul>
      <li>Show respect and humility rather than ignorance or arrogance.</li>
      <li>Prevent unintentional offense or misunderstandings.</li>
      <li>Open doors for more sincere interactions with locals.</li>
      <li>Foster goodwill and positive memories.</li>
    </ul>

    <h2 id="greetings-communication">Greetings & Communication</h2>
    <img src="img/culture-greeting.jpg" alt="A person learning to say hello from a local guide" class="topic-image">
    <ul>
      <li>Learn a few basic local phrases: hello, thank you, please, excuse me.</li>
      <li>Research greeting norms: handshake, bow, cheek-kiss, nod, etc.</li>
      <li>Be attentive to gender dynamics—some societies frown on cross-gender physical contact. :contentReference[oaicite:0]{index=0}</li>
      <li>Avoid startling people—address elders or strangers politely and with deference.</li>
      <li>Listen more than you speak—sometimes silence or gesture says more.</li>
    </ul>

    <h2 id="dress-appearance">Dress, Appearance & Modesty</h2>
    <img src="img/culture-dress.jpg" alt="A traveler wearing modest clothing appropriate for a temple" class="topic-image">
    <ul>
      <li>Check dress codes beforehand—religious sites often require covered shoulders, legs, head coverings.</li>
      <li>Avoid conspicuous or provocative clothing in conservative regions. :contentReference[oaicite:1]{index=1}</li>
      <li>Remove hats or shoes when entering private homes or sacred places if that’s expected.</li>
      <li>Use neutral colors when unsure—don’t attract attention by flashy attire in sensitive regions.</li>
    </ul>

    <h2 id="gestures-body-language">Gestures, Body Language & Personal Space</h2>
    <img src="img/culture-gestures.jpg" alt="A chart showing different hand gestures and their meanings" class="topic-image">
    <ul>
      <li>Be cautious with hand gestures—some mean very different things elsewhere (thumbs-up, “OK”, beckoning fingers). :contentReference[oaicite:2]{index=2}</li>
      <li>Avoid touching someone’s head (in some cultures, it’s sacred). :contentReference[oaicite:3]{index=3}</li>
      <li>Don’t point directly at people—point with your whole hand or nod instead. :contentReference[oaicite:4]{index=4}</li>
      <li>Respect personal space—what’s normal in one country may be intrusive in another. :contentReference[oaicite:5]{index=5}</li>
      <li>Be careful with left hand usage—some cultures deem it “unclean” to eat or give/receive items with left. :contentReference[oaicite:6]{index=6}</li>
    </ul>

    <h2 id="religion-customs">Religion, Rituals & Sensitivities</h2>
    <img src="img/culture-religion.jpg" alt="A quiet moment of observation inside a beautiful mosque" class="topic-image">
    <ul>
      <li>Research religious practices (prayer times, fasting, dress, prohibited behaviors). :contentReference[oaicite:7]{index=7}</li>
      <li>Avoid visiting sacred sites during worship times without checking permission.</li>
      <li>Observe rules about photography (some religious buildings or ceremonies prohibit photos).</li>
      <li>Don’t joke about, criticize, or debate someone’s beliefs unless you are invited and tread extremely carefully.</li>
    </ul>

    <h2 id="dining-etiquette">Dining Etiquette</h2>
    <img src="img/culture-dining.jpg" alt="A group of people sharing a meal at a traditional dining table" class="topic-image">
    <ul>
      <li>Learn local dining manners: hand used for eating, communal dishes, sharing, burping norms, etc. :contentReference[oaicite:8]{index=8}</li>
      <li>Wait for the host to eat first if applicable.</li>
      <li>Don’t overfill your plate; leaving some may signify fullness or appreciation (depending on culture). :contentReference[oaicite:9]{index=9}</li>
      <li>Avoid criticizing flavors, ingredients, or hygiene, even if you don’t like something—be tactful and polite.</li>
      <li>Check tipping practices—some cultures expect gratuity, others consider it rude. :contentReference[oaicite:10]{index=10}</li>
    </ul>

    <h2 id="photography-conduct">Photography & Public Conduct</h2>
    <img src="img/culture-photo.jpg" alt="A photographer respectfully asking a local artisan for permission to take a photo" class="topic-image">
    <ul>
      <li>Always ask permission before photographing people—especially elders, children, or religious figures. :contentReference[oaicite:11]{index=11}</li>
      <li>Be careful where you take selfies or photos—some places (memorials, religious grounds, private property) find it disrespectful. :contentReference[oaicite:12]{index=12}</li>
      <li>Avoid loud or boisterous behavior in quiet or sacred places—keep your voice low.</li>
      <li>Don’t litter, damage property, or disrespect local symbols, art, or heritage. :contentReference[oaicite:13]{index=13}</li>
    </ul>

    <h2 id="handling-mistakes">Handling Mistakes & Recovery</h2>
    <img src="img/culture-apology.jpg" alt="A traveler showing a gesture of apology and humility" class="topic-image">
    <p>Even with research, you might slip up. Here’s how to address it gracefully.</p>
    <ul>
      <li>Apologize sincerely if you offend—“I’m sorry—I didn’t know” often works.</li>
      <li>Be open to correction and learn rather than defend or argue.</li>
      <li>Respect local reactions; if someone seems upset, withdraw and show humility.</li>
      <li>Use observing locals as guides—copy respectful behavior after you notice what’s accepted.</li>
    </ul>

    <h2>Final Thoughts</h2>
    <p>Cultural understanding transforms travel from surface-level sightseeing into meaningful connection. It shows respect, opens doors, and enriches your journey. Be humble, curious, and conscientious—let local people teach you, and you’ll come home not just with selfies, but with stories of respect and friendship.</p>
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
