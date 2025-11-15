<?php
session_start();
require 'db.php';

// 1. Check for ID and fetch destination details
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid destination ID.");
}

$id = (int)$_GET['id'];
$stmt = $conn->prepare("SELECT * FROM destinations WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Destination not found.");
}

$destination = $result->fetch_assoc();
$attractions = !empty($destination['attraction_details']) ? json_decode($destination['attraction_details'], true) : [];

// 2. Fetch other destinations from the same continent for the sidebar
$sidebar_destinations = [];
$continent = $destination['continent'];
$current_id = $destination['id'];
$sidebar_stmt = $conn->prepare("SELECT id, title, image_url FROM destinations WHERE continent = ? AND id != ? ORDER BY created_at DESC LIMIT 5");
$sidebar_stmt->bind_param("si", $continent, $current_id);
$sidebar_stmt->execute();
$sidebar_result = $sidebar_stmt->get_result();
while ($row = $sidebar_result->fetch_assoc()) {
    $sidebar_destinations[] = $row;
}

// 3. Fetch related forum discussions
$forum_posts = [];
$searchTerm = '%' . $destination['title'] . '%';
try {
    $forum_stmt = $conn->prepare("SELECT id, title, author_username, created_at FROM forum WHERE (title LIKE ? OR content LIKE ?) AND parent_id IS NULL ORDER BY created_at DESC LIMIT 5");
    if ($forum_stmt) {
        $forum_stmt->bind_param("ss", $searchTerm, $searchTerm);
        $forum_stmt->execute();
        $forum_result = $forum_stmt->get_result();
        while ($post = $forum_result->fetch_assoc()) {
            $forum_posts[] = $post;
        }
    }
} catch (mysqli_sql_exception $e) {
    // Ignore if table doesn't exist
}

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
    <title><?php echo htmlspecialchars($destination['title']); ?> - Travel Guide</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <style>
        #map {
            height: 400px;
            width: 100%;
            background-color: var(--bg);
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 30px;
            z-index: 5; /* Ensure map controls are clickable */
        }
    </style>
    <style>
        /* CSS same as your previous code */
        :root{
          --bg:#0f1724;
          --card:#0b1220;
          --muted:#9aa4b2;
          --accent:#1d9bf0;
          --glass: rgba(255,255,255,0.03);
        }
        body {margin:0;font-family:'Montserrat',sans-serif;background:linear-gradient(180deg,var(--bg) 0%, #071027 60%);color:#e6eef8;}
        header{display:flex;justify-content:space-between;align-items:center;padding:20px 50px;background-color:var(--card);color:#fff;border-bottom:1px solid rgba(255,255,255,0.07);position:sticky;top:0;z-index:100;}
        header .logo{font-size:24px;font-weight:bold;}
        .header-right-group{display:flex;align-items:center;gap:35px;}
        .main-nav ul{list-style:none;margin:0;padding:0;display:flex;gap:35px;}
        .main-nav a{color:var(--muted);text-decoration:none;font-weight:500;font-size:15px;padding:5px 0;position:relative;transition:color 0.3s ease;}
        .main-nav a:hover{color:#fff;}
        .main-nav a.active{color:#fff;font-weight:700;}
        .main-nav a.active::after{content:'';position:absolute;bottom:-20px;left:0;width:100%;height:2px;background-color:var(--accent);}
        .hero{position:relative;height:500px;width:100%;background:no-repeat center center/cover;display:flex;align-items:flex-end;}
        /* Profile Dropdown */
        .profile-icon { display: inline-block; width: 40px; height: 40px; border-radius: 50%; background-color: var(--glass); cursor: pointer; display: flex; align-items: center; justify-content: center; }
        .profile-dropdown { position: relative; display: inline-block; }
        .dropdown-content { display: none; position: absolute; top: calc(100% + 10px); right: 0; background-color: var(--card); min-width: 250px; box-shadow: 0 4px 30px rgba(0,0,0,0.1); border: 1px solid rgba(255, 255, 255, 0.1); z-index: 10; border-radius: 8px; padding: 8px 0; }
        .dropdown-content::before { content: ''; position: absolute; top: -10px; right: 12px; border-width: 0 8px 10px 8px; border-style: solid; border-color: transparent transparent var(--card) transparent; }
        .dropdown-content a { color: #e6eef8; padding: 14px 20px; text-decoration: none; display: flex; align-items: center; gap: 12px; font-weight: 500; }
        .dropdown-content a:hover {background-color: rgba(29, 155, 240, 0.1)}
        .dropdown-header {
            padding: 14px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 8px;
        }
        .dropdown-header span { font-weight: 700; color: #fff; }
        .show { display:block; }
        .hero::after{content:'';position:absolute;inset:0;background:linear-gradient(to top, rgba(11,18,32,1) 0%, rgba(11,18,32,0) 60%);}
        .hero-content{position:relative;z-index:2;padding:40px 50px;}
        .hero-content h1{font-size:48px;margin:0 0 10px 0;text-shadow:2px 2px 8px rgba(0,0,0,0.7);}
        .hero-content p{font-size:20px;color:var(--muted);max-width:700px;}
        .page-content-wrapper{display:flex;gap:30px;max-width:1400px;margin:0 auto;padding:40px 50px;align-items:flex-start;}
        
        /* --- New Sidebar & Page Wrapper Styles --- */
        .page-wrapper { transition: padding-left 0.3s ease; }
        .sidebar{
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: 280px;
            background: var(--card);
            padding: 30px 20px;
            border-right: 1px solid rgba(255,255,255,0.07);
            z-index: 200;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            box-sizing: border-box;
        }
        body.sidebar-open .sidebar { transform: translateX(0); }
        body.sidebar-open .page-wrapper { padding-left: 280px; }

        header {
            display: flex;
            align-items: center;
        }

        .sidebar h3{color:#fff;font-size:18px;margin-top:0;margin-bottom:15px;padding-bottom:10px;border-bottom:1px solid var(--glass);}
        .sidebar ul{list-style:none;padding:0;margin:0;}
        .sidebar li{margin-bottom:8px;}
        .sidebar a{display:block;padding:10px 15px;color:var(--muted);text-decoration:none;border-radius:8px;transition:background-color 0.3s ease,color 0.3s ease;font-weight:500;}
        .sidebar a:hover{background-color:rgba(29,155,240,0.1);color:#fff;}
        .sidebar a.active{background-color:var(--accent);color:#0b1220;font-weight:700;}
        /* Attraction Scroller Styles (like MakeMyTrip) */
        .attractions-scroller {
            display: flex;
            overflow-x: auto;
            gap: 20px;
            padding: 10px 0 20px 0;
            scrollbar-width: thin;
            scrollbar-color: var(--muted) var(--card);
        }
        .attractions-scroller::-webkit-scrollbar { height: 8px; }
        .attractions-scroller::-webkit-scrollbar-track { background: var(--glass); border-radius: 4px; }
        .attractions-scroller::-webkit-scrollbar-thumb { background-color: var(--muted); border-radius: 4px; }
        .attraction-card { flex: 0 0 220px; background: var(--glass); border-radius: 12px; overflow: hidden; border: 1px solid rgba(255,255,255,0.05); box-shadow: 0 4px 20px rgba(0,0,0,0.2); transition: transform .3s ease, box-shadow .3s ease; display: flex; flex-direction: column; text-decoration: none; }
        .attraction-card:hover { transform: translateY(-5px); box-shadow: 0 8px 30px rgba(0,0,0,0.3); }
        .attraction-card img { width: 100%; height: 150px; object-fit: cover; transition: transform .4s ease; }
        .attraction-card:hover img { transform: scale(1.05); }
        .attraction-card-content { padding: 15px; }
        .attraction-card .name { font-size: 16px; font-weight: 600; color: #e6eef8; margin: 0; }        /* About Section (GetYourGuide style) */
        .highlights-container {
            background: var(--card);
            padding: 30px;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.07);
            margin-bottom: 30px;
        }
        .highlights-list h2, .key-details h2 { font-size: 24px; margin-top: 0; margin-bottom: 20px; border-left: 4px solid var(--accent); padding-left: 15px; color: #fff; }
        .highlights-list ul { list-style: none; padding: 0; margin: 0; }
        .highlights-list li { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; font-size: 16px; color: var(--muted); }
        .highlights-list li svg { flex-shrink: 0; width: 20px; height: 20px; stroke: var(--accent); }
        .key-details-box { 
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            background: var(--glass); border-radius: 10px; padding: 20px; border: 1px solid rgba(255,255,255,0.05); 
        }
        .key-details-box p { margin: 0 0 15px 0; display: flex; flex-direction: column; }
        .key-details-box p strong { font-weight: 600; color: #fff; margin-bottom: 4px; font-size: 14px; }
        .key-details-box p span { color: var(--muted); font-size: 14px; }
        .topic-image{width:100%;max-height:350px;object-fit:cover;border-radius:10px;margin:1em 0 1.5em 0;border:1px solid var(--glass);}
        /* Reviews & Ratings Box */
        .reviews-box { background: var(--glass); border-radius: 10px; padding: 20px; border: 1px solid rgba(255,255,255,0.05); }
        .overall-rating { display: flex; align-items: center; gap: 15px; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid var(--glass); }
        .overall-rating .score { font-size: 42px; font-weight: 700; color: #fff; }
        .overall-rating .stars { font-size: 20px; color: #f5b32a; }
        .overall-rating .summary { font-size: 14px; color: var(--muted); }
        .review-snippet { margin-bottom: 15px; }
        .review-snippet p { margin: 0 0 5px 0; font-size: 14px; line-height: 1.6; color: #d1dce8; }
        .review-snippet .author { font-size: 13px; font-weight: 600; color: var(--muted); }
        .reviews-box .read-more-reviews {
            display: block;
            text-align: center;
            margin-top: 20px;
            font-weight: 600;
        }
        .detail-section{background:var(--card);padding:30px;border-radius:12px;border:1px solid rgba(255,255,255,0.07);margin-bottom:30px;}
        .detail-section h2{scroll-margin-top:120px;font-size:28px;margin-top:0;margin-bottom:20px;border-left:4px solid var(--accent);padding-left:15px;color:#fff;}
        .detail-section p{line-height:1.8;color:var(--muted);font-size:16px;}
        .forum-list ul{list-style:none;padding:0;}
        .forum-list li{padding:15px 0;border-bottom:1px solid var(--glass);}
        .forum-list li:last-child{border-bottom:none;}
        .forum-list a{color:#e6eef8;text-decoration:none;font-weight:600;font-size:18px;transition:color 0.3s ease;}
        .forum-list a:hover{color:var(--accent);}
        .forum-list .post-meta{font-size:13px;color:var(--muted);margin-top:5px;}
        @media(max-width:992px){.page-content-wrapper{flex-direction:column;}.sidebar{position:static;width:100%;max-width:none;margin-bottom:30px;}}
        @media(max-width:992px){ body.sidebar-open .page-wrapper { padding-left: 0; } }
        /* New Image Slider Styles */
        .image-bulge-slider {
            display: flex;
            justify-content: center; /* Re-center for the bulge effect */
            align-items: center;
            gap: 20px;
            margin-top: 30px; /* Add space after the highlights card */
            margin-bottom: 30px;
            height: 250px; /* Set a fixed height for the container */
        }
        .image-bulge-slider .slide-image {
            width: 30%;
            height: 200px;
            object-fit: cover;
            border-radius: 12px;
            border: 2px solid var(--glass);
            transition: all 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            filter: grayscale(80%) brightness(0.7);
        }
        .image-bulge-slider .slide-image.active {
            width: 40%;
            height: 250px;
            filter: grayscale(0%) brightness(1);
            border: 3px solid var(--accent);
        }
        .site-footer { background-color: var(--card); color: var(--muted); padding: 50px 50px 20px; border-top: 1px solid rgba(255,255,255,0.07); } .footer-main { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 40px; margin-bottom: 40px; } .footer-column h4 { color: #e6eef8; font-size: 16px; margin-bottom: 15px; font-weight: 600; } .footer-column ul { list-style: none; padding: 0; margin: 0; } .footer-column ul li { margin-bottom: 10px; } .footer-column ul a { color: var(--muted); text-decoration: none; font-size: 14px; transition: color 0.3s ease; } .footer-column ul a:hover { color: var(--accent); } .payment-methods, .app-buttons { display: flex; flex-wrap: wrap; gap: 10px; } .payment-methods img, .app-buttons img { height: 30px; } .app-buttons img { height: 40px; } .footer-bottom { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; padding-top: 20px; border-top: 1px solid var(--glass); font-size: 13px; } .social-links { display: flex; gap: 15px; } .social-links a { color: var(--muted); transition: color 0.3s ease; } .social-links a:hover { color: #fff; } .social-links svg { width: 20px; height: 20px; } .legal-links { display: flex; gap: 20px; } .legal-links a { color: var(--muted); text-decoration: none; } .legal-links a:hover { text-decoration: underline; } @media (max-width: 768px) { .footer-bottom { flex-direction: column; gap: 20px; } }
    </style>
</head>
<body>

<!-- Sidebar (now outside the main flow) -->
<aside class="sidebar">
    <h3>Table of Contents</h3>
    <ul>
        <li><a href="#culture">Cultural Insights</a></li>
        <li><a href="#food">Food & Cuisine</a></li>
        <li><a href="#ecosystem">Ecosystem</a></li>
        <li><a href="#attractions">Top Attractions</a></li>
        <li><a href="#forum">Forum Discussions</a></li>
    </ul>
</aside>

<!-- Header -->
<header>
    <!-- Sidebar Toggle Button -->
    <div id="sidebar-toggle" class="profile-icon" style="margin-right: 20px;" title="Toggle Menu">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line>
        </svg>
    </div>
    <div class="logo" style="margin-right: auto;">Travel Tales</div>
    <div class="header-right-group" style="margin-left: 20px;">
        <nav class="main-nav" style="margin-right: 35px;">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="destinations.php" class="active">Destinations</a></li>
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

<div class="page-wrapper">
    <!-- Hero Section -->
    <div class="hero" style="background-image:url('<?php echo htmlspecialchars($destination['image_url'] ?? '../img/default_destination.jpg'); ?>')">
        <div class="hero-content">
            <h1><?php echo htmlspecialchars($destination['title']); ?></h1>
        </div>
    </div>

    <!-- Main Content -->
    <div class="page-content-wrapper">
        <div class="container" style="flex: 1; max-width: 1400px;">
        <!-- About Section -->
        <div class="highlights-container">
            <div class="highlights-list">
                <h2>About</h2>
                <?php if (!empty($destination['short_description'])): ?>
                    <p style="color: var(--muted); margin-top: -10px; margin-bottom: 25px; font-size: 16px; line-height: 1.7;"><?php echo htmlspecialchars($destination['short_description']); ?></p>
                <?php endif; ?>
                <ul>
                    <?php
                        $highlights = !empty($destination['tags']) ? explode(',', $destination['tags']) : [];
                        if (!empty($highlights)) {
                            foreach ($highlights as $highlight) {
                                echo '<li><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> ' . htmlspecialchars(trim($highlight)) . '</li>';
                            }
                        }
                    ?>
                </ul>
            </div>
        </div>

        <!-- Location Map -->
        <?php if (!empty($destination['latitude']) && !empty($destination['longitude'])): ?>
            <h2 style="margin-top: 30px; margin-bottom: 20px;">Location on Map</h2>
            <div id="map"></div>
        <?php endif; ?>

        <!-- New Image Slider (now under highlights) -->
        <?php
            $slider_images = array_filter([
                $destination['slider_image_1'] ?? null,
                $destination['slider_image_2'] ?? null,
                $destination['slider_image_3'] ?? null,
            ]);
            if (count($slider_images) === 3):
        ?>
        <div class="image-bulge-slider" id="image-bulge-slider">
            <img src="<?php echo htmlspecialchars($slider_images[0]); ?>" alt="Slider Image 1" class="slide-image active">
            <img src="<?php echo htmlspecialchars($slider_images[1]); ?>" alt="Slider Image 2" class="slide-image">
            <img src="<?php echo htmlspecialchars($slider_images[2]); ?>" alt="Slider Image 3" class="slide-image">
        </div>
        <?php endif; ?>

        <!-- Cultural Details -->
        <section class="detail-section" id="culture">
            <h2>Cultural Insights</h2>
            <?php if (!empty($destination['culture_image_url'])): ?>
                <img src="<?php echo htmlspecialchars($destination['culture_image_url']); ?>" alt="Culture of <?php echo htmlspecialchars($destination['title']); ?>" class="topic-image">
            <?php endif; ?>
            <p><?php echo !empty($destination['cultural_details']) ? nl2br(htmlspecialchars($destination['cultural_details'])) : 'Information about the local culture and traditions will be available soon.'; ?></p>
        </section>

        <!-- Food Details -->
        <section class="detail-section" id="food">
            <h2>Food & Cuisine</h2>
            <?php if (!empty($destination['food_image_url'])): ?>
                <img src="<?php echo htmlspecialchars($destination['food_image_url']); ?>" alt="Food in <?php echo htmlspecialchars($destination['title']); ?>" class="topic-image">
            <?php endif; ?>
            <p><?php echo !empty($destination['food_details']) ? nl2br(htmlspecialchars($destination['food_details'])) : 'Details about the local food and cuisine will be available soon.'; ?></p>
        </section>

        <!-- Ecosystem & Ecosensitivity -->
        <section class="detail-section" id="ecosystem">
            <h2>Ecosystem & Ecosensitivity</h2>
            <?php if (!empty($destination['ecosystem_image_url'])): ?>
                <img src="<?php echo htmlspecialchars($destination['ecosystem_image_url']); ?>" alt="Ecosystem of <?php echo htmlspecialchars($destination['title']); ?>" class="topic-image">
            <?php endif; ?>
            <p><?php echo !empty($destination['ecosystem_details']) ? nl2br(htmlspecialchars($destination['ecosystem_details'])) : 'Information about the local ecosystem and responsible travel tips will be available soon.'; ?></p>
        </section>

        <!-- Top Attractions Waterfall Slider -->
        <?php
            if (!empty($attractions)):
        ?>
        <section class="detail-section" id="attractions">
            <h2>Top Attractions</h2>
            <div class="attractions-scroller" id="attractions-scroller"></div>
            <template id="attraction-template">
                <a href="#" class="attraction-card">
                    <img src="" alt="">
                    <div class="attraction-card-content">
                        <h4 class="name"></h4>
                    </div>
                </a>
            </template>
        </section>
        <?php endif; ?>

        <!-- Related Forum Discussions -->
        <section class="detail-section forum-list" id="forum">
            <h2>Related Forum Discussions</h2>
            <?php if (!empty($forum_posts)): ?>
                <ul>
                    <?php foreach ($forum_posts as $post): ?>
                        <li>
                            <a href="forum_post.php?id=<?php echo $post['id']; ?>">
                                <?php echo htmlspecialchars($post['title']); ?>
                            </a>
                            <div class="post-meta">
                                by <?php echo htmlspecialchars($post['author_username']); ?> on <?php echo date('M d, Y', strtotime($post['created_at'])); ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No forum discussions found related to <?php echo htmlspecialchars($destination['title']); ?>. <a href="forum.php">Start a new discussion!</a></p>
            <?php endif; ?>
        </section>
        </div>
    </div>

</div>

<!-- Footer -->
<?php require 'footer.php'; ?>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
    // --- New Sidebar Toggle Logic ---
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const pageWrapper = document.querySelector('.page-wrapper');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', (e) => {
            e.stopPropagation(); // Prevent this click from immediately closing the sidebar
            document.body.classList.toggle('sidebar-open');
        });
    }
    if (pageWrapper) {
        pageWrapper.addEventListener('click', () => {
            document.body.classList.remove('sidebar-open');
        });
    }

    // Profile Dropdown JS
    const profileIcon = document.getElementById('profileIcon');
    if (profileIcon) {
        const profileDropdown = document.getElementById('profileDropdown');
        profileIcon.addEventListener('click', function(event) {
            event.stopPropagation();
            profileDropdown.classList.toggle('show');
        });
        window.addEventListener('click', function(event) {
            if (!profileIcon.contains(event.target) && !event.target.closest('.profile-dropdown')) {
                document.getElementById('profileDropdown').classList.remove('show');
            }
        });
    }

    // --- New Image Bulge Slider Logic ---
    const bulgeSlider = document.getElementById('image-bulge-slider');
    if (bulgeSlider) {
        const images = bulgeSlider.querySelectorAll('.slide-image');
        let currentIndex = 0;

        setInterval(() => {
            // Remove active class from current image
            images[currentIndex].classList.remove('active');
            // Move to the next image, looping back to the start
            currentIndex = (currentIndex + 1) % images.length;
            // Add active class to the new current image
            images[currentIndex].classList.add('active');
        }, 3000); // Change image every 3 seconds
    }

    // --- New Waterfall Slider & Sidebar Highlighting Logic ---
    document.addEventListener('DOMContentLoaded', () => {
        // 1. Populate Waterfall Slider for Attractions
        const attractionsData = <?php echo json_encode($attractions ?? []); ?>;
        const scrollerContainer = document.getElementById('attractions-scroller');
        const template = document.getElementById('attraction-template');

        if (attractionsData.length > 0 && scrollerContainer && template) {
            // Populate the grid with attraction cards
            attractionsData.forEach(attraction => {
                const card = template.content.cloneNode(true).querySelector('.attraction-card');
                card.href = `https://www.google.com/search?q=${encodeURIComponent(attraction.name + ' ' + '<?php echo htmlspecialchars($destination['title']); ?>')}`;
                card.target = '_blank';
                card.querySelector('img').src = attraction.image.startsWith('http') ? attraction.image : attraction.image.replace(/^\//, ''); // Use path directly, remove leading slash if any
                card.querySelector('img').alt = attraction.name;
                card.querySelector('.name').textContent = attraction.name;
                scrollerContainer.appendChild(card);
            });
        }

        // 2. Sidebar Active Link Highlighting on Scroll
        const sections = document.querySelectorAll('.detail-section');
        const navLinks = document.querySelectorAll('.sidebar a[href^="#"]');

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    navLinks.forEach(link => {
                        link.classList.remove('active');
                        if (link.getAttribute('href').substring(1) === entry.target.id) {
                            link.classList.add('active');
                        }
                    });
                }
            });
        }, {
            rootMargin: '-50% 0px -50% 0px', // Highlight when section is in the middle of the viewport
            threshold: 0
        });

        sections.forEach(section => {
            observer.observe(section);
        });

        // 3. Initialize the destination detail map
        <?php if (!empty($destination['latitude']) && !empty($destination['longitude'])): ?>
        const lat = <?php echo $destination['latitude']; ?>;
        const lng = <?php echo $destination['longitude']; ?>;
        const map = L.map('map').setView([lat, lng], 13); // Set view with a good zoom level

        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
            subdomains: 'abcd',
            maxZoom: 19
        }).addTo(map);

        const popupContent = `<div style="text-align: center;"><strong><?php echo htmlspecialchars($destination['title']); ?></strong><br>Location</div>`;

        L.marker([lat, lng]).addTo(map)
            .bindPopup(popupContent)
            .openPopup();
        <?php endif; ?>
    });
</script>
</body>
</html>
