<?php
// Start session and include database connection.
session_start();
require 'db.php';

// Fetch top 4 destinations
$top_destinations = [];
$dest_result = $conn->query("SELECT id, title, image_url FROM destinations ORDER BY created_at DESC LIMIT 4");
if ($dest_result) {
    while ($row = $dest_result->fetch_assoc()) {
        $top_destinations[] = $row;
    }
}

// Fetch travel tips from the database
$tips = [];
if (!isset($conn)) require 'db.php'; // Ensure DB connection if not already included
$tips_result = $conn->query("SELECT text FROM carousel ORDER BY created_at DESC");
if ($tips_result) {
    while ($row = $tips_result->fetch_assoc()) {
        $tips[] = $row['text'];
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Travel Guide</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">

    <style>
        /* Base Styles from destinations.php */
        :root{
          --bg:#1e293b; /* slate-700 */
          --card:#0b1220;
          --muted:#9aa4b2;
          --accent:#1d9bf0;
          --glass: #0f172a;
        }
        body{
          margin:0;
          font-family:'Montserrat',sans-serif;
          background:linear-gradient(180deg, var(--bg) 0%, #0f172a 100%);
          color:#e6eef8;
        }

        /* Header Styles from destinations.php */
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
            gap: 35px; /* Space between nav links and auth buttons */
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

        .search-container {
            flex-grow: 1;
            padding: 0 40px;
        }
    </style>
    <style>
        /* Hero Slider */
        .hero-slider {
            position: relative;
            width: 100%;
            height: 700px;
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
            height: 700px;
            flex-shrink: 0; /* Prevent slides from shrinking */
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
            padding: 100px 50px 50px;
            background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0) 100%);
            color: #fff;
        }

        .slide-content h2 {
            font-size: 48px;
            font-weight: 700;
            margin: 0 0 10px 0;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.7);
        }

        .slide-content p {
            font-size: 20px;
            font-weight: 400;
            text-shadow: 1px 1px 4px rgba(0,0,0,0.7);
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }

        /* Rotating carousel for travel tips */
        .carousel {
            background-color: var(--card);
            color: var(--muted);
            padding: 40px 50px; /* Increased vertical padding for more space */
            text-align: center;
            overflow: hidden; /* To contain the sliding tips */
            height: 40px; /* Set a fixed height to prevent layout shift */
            display: flex;
            align-items: center;
            justify-content: center; /* Center the wrapper horizontally */
            position: relative; /* Needed for positioning the wrapper */
            margin: 40px 0; /* Add vertical margin for spacing */
        }

        .carousel .tips-wrapper {
            display: flex; /* Arrange tips in a row */
            transition: transform 0.8s cubic-bezier(0.77, 0, 0.175, 1); /* Smooth sliding transition */
        }

        /* Top Destinations */
        .top-destinations {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            padding: 40px 50px;
            gap: 20px;
        }

        .destination-card-link {
            text-decoration: none;
            color: inherit;
        }
        .destination-card {
            background: var(--glass);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            text-align: center;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .destination-card:hover {
            transform: scale(1.05);
        }

        .destination-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .destination-card h3 {
            padding: 10px;
        }

        /* Quote */
        .quote {
            text-align: center;
            font-style: italic;
            font-size: 24px;
            padding: 80px 50px;
            background-color: #071027;
            color: var(--muted);
        }

        /* Guidance Cards */
        .guidance-slider-container {
            position: relative;
            padding: 40px 50px;
            max-width: 1400px;
            margin: 0 auto;
        }
        .guidance-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .guidance-card {
            background: var(--card);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 10px;
            overflow: hidden;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        @media (max-width: 992px) {
            .guidance-cards { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 600px) {
            .guidance-cards { grid-template-columns: 1fr; }
        }

        .guidance-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }

        .guidance-card-content {
            padding: 20px;
        }

        .guidance-card h3 {
            margin: 0 0 8px 0;
            font-size: 18px;
            color: #e6eef8;
        }

        .guidance-card p {
            margin: 0 0 16px 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.5;
        }

        .guidance-card .read-more {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }

        .guidance-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.3);
        }

        /* Forum Preview */
        .forum-preview {
            padding: 40px 50px;
            background-color: var(--card);
        }

        .forum-preview h2 {
            margin-bottom: 20px;
        }

        .forum-preview ul {
            list-style: none;
            padding: 0;
        }

        .forum-preview ul li {
            padding: 10px 0;
            border-bottom: 1px solid var(--glass);
        }

        /* Footer */
        .site-footer {
            background-color: var(--card);
            color: var(--muted);
            padding: 50px 50px 20px;
            border-top: 1px solid rgba(255,255,255,0.07);
        }
        .footer-main {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }
        .footer-column h4 {
            color: #e6eef8;
            font-size: 16px;
            margin-bottom: 15px;
            font-weight: 600;
        }
        .footer-column ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .footer-column ul li {
            margin-bottom: 10px;
        }
        .footer-column ul a {
            color: var(--muted);
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }
        .footer-column ul a:hover {
            color: var(--accent);
        }
        .payment-methods, .app-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .payment-methods img, .app-buttons img {
            height: 30px;
        }
        .app-buttons img {
            height: 40px;
        }

        .footer-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            padding-top: 20px;
            border-top: 1px solid var(--glass);
            font-size: 13px;
        }
        .social-links {
            display: flex;
            gap: 15px;
        }
        .social-links a {
            color: var(--muted);
            transition: color 0.3s ease;
        }
        .social-links a:hover {
            color: #fff;
        }
        .social-links svg {
            width: 20px;
            height: 20px;
        }
        .legal-links {
            display: flex;
            gap: 20px;
        }
        .legal-links a {
            color: var(--muted);
            text-decoration: none;
        }
        .legal-links a:hover {
            text-decoration: underline;
        }

        /* Forum CTA Section */
        .forum-cta-section {
            position: relative;
            padding: 100px 50px;
            background-image: url('img/forum-cta-bg.jpg'); /* Make sure you have an image with this name */
            background-size: cover;
            background-position: center;
            text-align: center;
            color: #fff;
            border-radius: 12px;
            margin: 40px 50px;
            overflow: hidden;
        }
        .forum-cta-section::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(11, 18, 32, 0.7); /* Dark overlay */
            z-index: 1;
        }
        .forum-cta-content {
            position: relative;
            z-index: 2;
        }
        .forum-cta-section h2 {
            font-size: 36px;
            font-weight: 700;
            margin: 0 0 15px 0;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.5);
        }
        .forum-cta-section p {
            font-size: 18px;
            max-width: 600px;
            margin: 0 auto 30px;
            color: var(--muted);
        }

        @media (max-width: 1200px) { .guidance-card { flex-basis: calc(50% - 10px); } }
        @media (max-width: 768px) { .guidance-card { flex-basis: 100%; } }
        @media (max-width: 768px) {
            .footer-bottom {
                flex-direction: column;
                gap: 20px;
            }
        }

        /* Old Footer Style - can be removed */
        /* footer {
            text-align: center;
            padding: 30px 50px;
            background-color: var(--card);
            color: #fff;
        } */

        /* Success Message Bubble */
        .success-bubble {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 90%;
            max-width: 450px;
            background: rgba(15, 23, 36, 0.8);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 30px rgba(0,0,0,0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
            z-index: 1000;
            text-align: center;
            color: #fff;
            font-size: 1.2em;
            font-weight: 500;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.5s ease, visibility 0.5s ease;
        }
        .success-bubble.show {
            opacity: 1;
            visibility: visible;
        }
    </style>

</head>
<body>

<?php require 'header.php'; ?>

<!-- Success Message Bubble -->
<?php
if (isset($_SESSION['success_message'])) {
    echo '<div id="success-bubble" class="success-bubble">' . $_SESSION['success_message'] . '</div>';
    // Unset the session variable so it doesn't show again on refresh
    unset($_SESSION['success_message']);
}
?>

<!-- Hero Slider -->
<div class="hero-slider">
    <div class="slides-wrapper">
        <div class="slide">
            <img src="img/carousel-1.jpg" alt="Slide 1">
            <div class="slide-content">
                <h2>Discover the World, One Destination at a Time</h2>
                <p>Find iconic spots and hidden gems with ease</p>
            </div>
        </div>
        <div class="slide">
            <img src="img/carousel-2.jpg" alt="Slide 2">
            <div class="slide-content">
                <h2>Your Personalized Travel Companion</h2>
                <p>Save favorites and get tailored pick</p>
            </div>
        </div>
        <div class="slide">
            <img src="img/carousel-3.jpg" alt="Slide 3">
            <div class="slide-content">
                <h2>Travel Smart, Travel Inspired</h2>
                <p>Quick tips and insights for every journey</p>
            </div>
        </div>
    </div>
</div>

<!-- Rotating Carousel for Travel Tips -->
<div class="carousel">
    <div class="tips-wrapper">
        <?php if (!empty($tips)): ?>
            <?php foreach ($tips as $tip): ?>
                <div class="tip-card" style="flex-shrink: 0; width: 100%; text-align: center; font-size: 18px; font-weight: 500;"><?php echo htmlspecialchars($tip); ?></div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="tip-card" style="flex-shrink: 0; width: 100%; text-align: center; font-size: 18px; font-weight: 500;">Welcome to Travel Tales!</div>
        <?php endif; ?>
    </div>
</div>

<!-- Top Destinations -->
<div class="top-destinations">
    <?php if (!empty($top_destinations)): ?> 
        <?php foreach ($top_destinations as $dest): ?>
            <a href="destinations.php" class="destination-card-link">
                <div class="destination-card">
                    <img src="<?php echo htmlspecialchars($dest['image_url'] ?? 'img/default_destination.jpg'); ?>" alt="<?php echo htmlspecialchars($dest['title']); ?>">
                    <h3><?php echo htmlspecialchars($dest['title']); ?></h3>
                </div>
            </a>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No top destinations to display right now.</p>
    <?php endif; ?>
</div>

<!-- Quote -->
<div class="quote">
    "You can never cross the ocean until you have the courage to lose sight of the shore." - Christopher Columbus
</div>

<!-- Guidance Cards -->
<div class="guidance-slider-container">
    <div class="guidance-cards">
        <div class="guidance-card" onclick="window.location.href='general_info.php'">
            <img src="img/general-info.jpg" alt="General Information">
            <div class="guidance-card-content">
                <h3>General Information</h3>
                <p>Essential tips and facts for the modern traveler. Plan your trip with confidence.</p>
                <span class="read-more">Read More &rarr;</span>
            </div>
        </div>
        <div class="guidance-card" onclick="window.location.href='safety.php'">
            <img src="img/pretravel.jpg" alt="Safety Guidance">
            <div class="guidance-card-content">
                <h3>Safety Guidance</h3>
                <p>Stay safe and secure on your adventures with our expert advice and alerts.</p>
                <span class="read-more">Read More &rarr;</span>
            </div>
        </div>
        <div class="guidance-card" onclick="window.location.href='transport_guidance.php'">
            <img src="img/transport-safety.jpg" alt="Transport Guidance">
            <div class="guidance-card-content">
                <h3>Transport Guidance</h3>
                <p>Navigate like a local with our guides on public and private transportation.</p>
                <span class="read-more">Read More &rarr;</span>
            </div>
        </div>
        <div class="guidance-card" onclick="window.location.href='money.php'">
            <img src="img/cash-card.jpg" alt="Money Guidance">
            <div class="guidance-card-content">
                <h3>Money Guidance</h3>
                <p>Manage your budget, exchange currency, and spend wisely on your travels.</p>
                <span class="read-more">Read More &rarr;</span>
            </div>
        </div>
        <div class="guidance-card" onclick="window.location.href='food.php'">
            <img src="img/food-allergy.jpg" alt="Food Guidance">
            <div class="guidance-card-content">
                <h3>Food Guidance</h3>
                <p>Savor the flavors of the world with our guides to local food and dining.</p>
                <span class="read-more">Read More &rarr;</span>
            </div>
        </div>
        <div class="guidance-card" onclick="window.location.href='shopping.php'">
            <img src="img/shopping-prep.jpg" alt="Shopping Guidance">
            <div class="guidance-card-content">
                <h3>Shopping Guidance</h3>
                <p>Discover the best markets, boutiques, and souvenir spots around the world.</p>
                <span class="read-more">Read More &rarr;</span>
            </div>
        </div>
        <div class="guidance-card" onclick="window.location.href='culture.php'">
            <img src="img/culture.jpg" alt="Cultural Guidance">
            <div class="guidance-card-content">
                <h3>Cultural Guidance</h3>
                <p>Embrace local customs and etiquette to enrich your travel experience.</p>
                <span class="read-more">Read More &rarr;</span>
            </div>
        </div>
    </div>
</div>

<!-- Forum CTA -->
<div class="forum-cta-section">
    <div class="forum-cta-content">
        <h2>Join the Conversation</h2>
        <p>Share your travel stories, ask for advice, and connect with a global community of fellow adventurers. Your next great tip is just a post away.</p>
        <a href="forum.php" class="btn-primary" style="padding: 12px 25px; font-size: 16px; font-weight: 700; text-decoration: none;">
            Go to Forum &rarr;
        </a>
    </div>
</div>


<!-- Footer -->
<?php require 'footer.php'; ?>

<script>
    // Hero slider JS
    const sliderWrapper = document.querySelector('.hero-slider .slides-wrapper');
    let slides = document.querySelectorAll('.hero-slider .slide');
    let currentSlide = 0;
    const slideCount = slides.length;

    // Clone the first slide and append it to the end for a seamless loop
    const firstSlideClone = slides[0].cloneNode(true);
    sliderWrapper.appendChild(firstSlideClone);

    function nextHeroSlide() {
        currentSlide++;
        sliderWrapper.style.transform = `translateX(-${currentSlide * 100}%)`;
        sliderWrapper.style.transition = 'transform 0.5s ease-in-out';

        // If we are at the cloned slide, reset to the beginning without transition
        if (currentSlide >= slideCount) {
            setTimeout(() => {
                sliderWrapper.style.transition = 'none';
                currentSlide = 0;
                sliderWrapper.style.transform = `translateX(0%)`;
            }, 500); // This timeout should match the transition duration
        }
    }

    setInterval(nextHeroSlide, 3000);

    // Travel tips carousel JS
    const tipsWrapper = document.querySelector('.carousel .tips-wrapper');
    if (tipsWrapper && tipsWrapper.children.length > 1) {
        const tips = document.querySelectorAll('.carousel .tip-card');
        const tipCount = tips.length;
        let currentTipIndex = 0;

        // Clone the first tip and add it to the end for a seamless loop
        const firstTipClone = tips[0].cloneNode(true);
        tipsWrapper.appendChild(firstTipClone);

        function nextTip() {
            currentTipIndex++;
            tipsWrapper.style.transform = `translateX(-${currentTipIndex * 100}%)`;
            tipsWrapper.style.transition = 'transform 0.8s cubic-bezier(0.77, 0, 0.175, 1)';

            // When it reaches the cloned slide, reset to the beginning without a transition
            if (currentTipIndex >= tipCount) {
                setTimeout(() => {
                    tipsWrapper.style.transition = 'none';
                    currentTipIndex = 0;
                    tipsWrapper.style.transform = 'translateX(0%)';
                }, 800); // This timeout must match the transition duration
            }
        }

        setInterval(nextTip, 4000); // Change tip every 4 seconds
    } else if (tipsWrapper && tipsWrapper.children.length > 0) {
        // If there's only one tip, just make sure it's visible
        tipsWrapper.children[0].style.display = 'block';
    }

    // Success bubble JS
    const successBubble = document.getElementById('success-bubble');
    if (successBubble) {
        // Show the bubble
        setTimeout(() => {
            successBubble.classList.add('show');
        }, 100); // Small delay to allow rendering

        // Hide the bubble after 4 seconds
        setTimeout(() => {
            successBubble.classList.remove('show');
        }, 4000);
    }

</script>

</body>
</html>
