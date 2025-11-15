<?php
session_start();
require 'db.php'; // For header to work correctly
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About Us - Travel Tales</title>
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

        /* Hero Section */
        .hero {
            position: relative;
            height: 450px;
            width: 100%;
            background: no-repeat center center/cover;
            display: flex;
            align-items: flex-end;
        }
        .hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(11,18,32,1) 0%, rgba(11,18,32,0) 60%);
        }
        .hero-content {
            position: relative;
            z-index: 2;
            padding: 40px 50px;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }
        .hero-content h1 {
            font-size: 48px;
            margin: 0 0 10px 0;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.7);
        }
        .hero-content p {
            font-size: 20px;
            color: var(--muted);
            max-width: 700px;
        }

        /* Main Content */
        .container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 50px;
        }
        .content-section {
            background: var(--card);
            padding: 30px;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.07);
            margin-bottom: 30px;
        }
        .content-section h2 {
            font-size: 28px;
            margin-top: 0;
            margin-bottom: 20px;
            border-left: 4px solid var(--accent);
            padding-left: 15px;
            color: #fff;
        }
        .content-section p {
            line-height: 1.8;
            font-size: 16px;
            color: var(--muted);
        }

        /* Services Grid */
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .service-card {
            background: var(--glass);
            padding: 20px;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.05);
        }
        .service-card h3 {
            font-size: 18px;
            color: #fff;
            margin: 0 0 10px 0;
        }
        .service-card p {
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
        }

        /* Team Section */
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 25px;
            text-align: center;
        }
        .team-member img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--glass);
            margin-bottom: 10px;
        }
        .team-member h4 {
            margin: 0 0 5px 0;
            color: #fff;
        }
        .team-member span {
            font-size: 13px;
            color: var(--accent);
        }

        /* Footer Styles from index.php */
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
        .legal-links {
            display: flex;
            gap: 20px;
        }

    </style>
</head>
<body>

<?php require 'header.php'; ?>

<!-- Hero Section -->
<div class="hero" style="background-image: url('img/about-hero.jpg');">
    <div class="hero-content">
        <h1>About Travel Tales</h1>
        <p>Your trusted companion for every step of your journey.</p>
    </div>
</div>

<div class="container">
    <section class="content-section">
        <h2>Our Mission</h2>
        <p>At Travel Tales, our mission is to inspire curiosity, simplify travel planning, and foster a global community of adventurers. We believe that travel is more than just visiting new places; it's about experiencing different cultures, creating lasting memories, and sharing stories that connect us all. We're here to provide you with the tools, knowledge, and inspiration to make every journey unforgettable.</p>
    </section>

    <section class="content-section">
        <h2>What We Offer</h2>
        <div class="services-grid">
            <div class="service-card">
                <h3>In-Depth Destination Guides</h3>
                <p>Explore our comprehensive guides covering everything from iconic landmarks to hidden gems, complete with cultural insights, food recommendations, and practical tips.</p>
            </div>
            <div class="service-card">
                <h3>Engaging Articles & Blogs</h3>
                <p>Read inspiring stories from our community and expert-written articles that provide deep dives into travel topics, tips, and trends.</p>
            </div>
            <div class="service-card">
                <h3>Community Forum</h3>
                <p>Connect with fellow travelers! Ask questions, share your experiences, and get real-time advice from a passionate community.</p>
            </div>
            <div class="service-card">
                <h3>Fun Travel Quizzes</h3>
                <p>Test your knowledge about world geography, cultures, and landmarks with our fun and interactive quizzes. Learn something new with every question!</p>
            </div>
        </div>
    </section>

    <section class="content-section">
        <h2>Meet the Team (Placeholder)</h2>
        <div class="team-grid">
            <div class="team-member">
                <img src="img/default_avatar.png" alt="Team Member 1">
                <h4>Alex Doe</h4>
                <span>Founder & Chief Explorer</span>
            </div>
            <div class="team-member">
                <img src="img/default_avatar.png" alt="Team Member 2">
                <h4>Jane Smith</h4>
                <span>Head of Content</span>
            </div>
            <div class="team-member">
                <img src="img/default_avatar.png" alt="Team Member 3">
                <h4>Sam Wilson</h4>
                <span>Community Manager</span>
            </div>
            <div class="team-member">
                <img src="img/default_avatar.png" alt="Team Member 4">
                <h4>Emily Rose</h4>
                <span>Lead Developer</span>
            </div>
        </div>
    </section>

    <section class="content-section" style="text-align: center;">
        <h2>Join Our Journey</h2>
        <p>Ready to start your next adventure? <a href="signup.php">Create an account</a> to join our community, save your favorite destinations, and share your own travel tales. The world is waiting!</p>
    </section>
</div>

<!-- Footer -->
<?php require 'footer.php'; ?>

</body>
</html>