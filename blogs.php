<?php
session_start();
require 'db.php'; // your database connection

// Function to handle file uploads

// Make sure the user is logged in by checking for the username in the session
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Fetch the logged-in user's ID, which is needed to create a blog post
$username = $_SESSION['user'];
$user_id_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$user_id_stmt->bind_param("s", $username);
$user_id_stmt->execute();
$user_id_result = $user_id_stmt->get_result();
if ($user_id_result->num_rows === 0) {
    // This is an edge case, but good to handle. User in session doesn't exist in DB.
    header("Location: logout.php");
    exit();
}
$user_row = $user_id_result->fetch_assoc();
$user_id = $user_row['id'];

// Fetch the current user's avatar for the header
$user_avatar_header = null;
if (isset($_SESSION['user'])) {
    $username_header = $_SESSION['user'];
    $avatar_stmt = $conn->prepare("SELECT avatar FROM users WHERE username = ?");
    $avatar_stmt->bind_param("s", $username_header);
    $avatar_stmt->execute();
    $avatar_result = $avatar_stmt->get_result();
    $user_data_header = $avatar_result->fetch_assoc();
    $user_avatar_header = $user_data_header['avatar'] ?? null;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $place_name = $conn->real_escape_string($_POST['place_name']);
    $title = $conn->real_escape_string($_POST['title']);
    $content = $conn->real_escape_string($_POST['content']);
    $image_path = handle_upload('image'); // Handle the image upload

    $sql = "INSERT INTO blogs (user_id, place_name, title, content, image) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issss", $user_id, $place_name, $title, $content, $image_path);
    $stmt->execute();
    $stmt->close();
    // Redirect to the same page to show the new blog
    header("Location: blogs.php");
    exit();
}

// Fetch all blogs
$sql = "SELECT b.*, u.username, u.avatar FROM blogs b JOIN users u ON b.user_id = u.id ORDER BY b.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel Blogs</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root{ --bg:#1e293b; /* slate-700 */ --card:#0b1220; --muted:#9aa4b2; --accent:#1d9bf0; --glass: rgba(255,255,255,0.03); }
        body { margin: 0; font-family: 'Montserrat', sans-serif; background:linear-gradient(180deg, var(--bg) 0%, #0f172a 100%); color: #e6eef8; }
        a { text-decoration: none; color: var(--accent); }
        a:hover { text-decoration: underline; }

        /* Header Styles */
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

        /* Hero Slider */
        .hero-slider { position: relative; width: 100%; height: 450px; overflow: hidden; }
        .hero-slider .slides-wrapper { display: flex; width: 100%; height: 100%; transition: transform 0.5s ease-in-out; }
        .hero-slider .slide { position: relative; width: 100%; height: 100%; flex-shrink: 0; }
        .hero-slider .slide img { width: 100%; height: 100%; object-fit: cover; }
        .slide-content { position: absolute; bottom: 0; left: 0; right: 0; padding: 80px 50px 40px; background: linear-gradient(to top, rgba(11,18,32,0.9) 0%, rgba(11,18,32,0) 100%); color: #fff; }
        .slide-content h1 { font-size: 48px; margin: 0 0 10px 0; text-shadow: 2px 2px 8px rgba(0,0,0,0.7); }
        .slide-content p { font-size: 20px; color: var(--muted); max-width: 700px; }

        /* Main Content Styles */
        .page-wrapper { display: flex; max-width: 1400px; margin: 0 auto; padding: 40px 50px; gap: 30px; align-items: flex-start; }
        .container { flex: 2.5; max-width: 900px; padding: 0; margin: 0; }

        /* Content Section Styles */
        .content-section { background: var(--card); padding: 30px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.07); margin-bottom: 30px; }
        .content-section h2 { font-size: 24px; margin-top: 0; margin-bottom: 20px; border-left: 4px solid var(--accent); padding-left: 15px; color: #fff; }

        /* Form Styles */
        .form-group { margin-bottom: 20px; }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px;
            background: var(--glass);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            color: #e6eef8;
            font-family: 'Montserrat', sans-serif;
            font-size: 16px;
            box-sizing: border-box;
        }
        .form-group textarea { min-height: 150px; resize: vertical; }
        .btn-submit { padding: 12px 25px; background: var(--accent); color: #041022; border: none; border-radius: 8px; font-weight: 700; font-size: 16px; cursor: pointer; transition: background 0.3s; }
        .btn-submit:hover { background: #4fbfff; }

        /* Custom File Input */
        .file-upload-label {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            background: var(--glass);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .file-upload-label:hover {
            background-color: rgba(255,255,255,0.07);
        }
        .file-upload-label svg {
            stroke: var(--muted);
        }
        .file-upload-label span {
            color: var(--muted);
        }

        /* Blog Post List Styles */
        .blog-post { background: var(--glass); border: 1px solid rgba(255,255,255,0.05); padding: 20px; margin-bottom: 20px; border-radius: 10px; }
        .blog-post h3 { margin: 0 0 10px 0; font-size: 20px; color: #fff; }
        .blog-post .meta { font-size: 13px; color: var(--muted); margin-bottom: 15px; }
        .blog-post p { line-height: 1.7; font-size: 16px; color: #d1dce8; margin: 0; }
        .blog-post .blog-image { width: 100%; height: auto; max-height: 400px; object-fit: cover; border-radius: 8px; margin-bottom: 20px; border: 1px solid var(--glass); }

        /* Waterfall Slider Styles */
        .waterfall-slider {
            flex: 0 0 450px; /* A bit more big */
            height: calc(100vh - 121px); /* Adjust based on header height and top padding */
            overflow: hidden;
            position: sticky;
            top: 101px; /* Header height + top padding */
            border-radius: 12px;
        }
        .waterfall-column { display: flex; flex-direction: column; animation: waterfall-scroll 30s linear infinite; }
        .waterfall-column img { width: 100%; height: auto; object-fit: cover; margin-bottom: 15px; border-radius: 10px; border: 1px solid var(--glass); }
        @keyframes waterfall-scroll {
            0% { transform: translateY(0); }
            100% { transform: translateY(-50%); }
        }

        footer { text-align: center; padding: 40px 50px; color: var(--muted); font-size: 14px; border-top: 1px solid var(--glass); margin-top: 40px; }

        /* Responsive Adjustments */
        @media (max-width: 1200px) {
            .waterfall-slider { display: none; } /* Hide slider on smaller screens */
            .page-wrapper { justify-content: center; }
            .container { max-width: 900px; padding: 0 50px; }
        }
    </style>
    <style>
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
        .social-links a { color: var(--muted); transition: color 0.3s ease; }
        .social-links a:hover { color: #fff; }
        .social-links svg { width: 20px; height: 20px; }
        .legal-links { display: flex; gap: 20px; }
        .legal-links a { color: var(--muted); text-decoration: none; }
        .legal-links a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<?php require 'header.php'; ?>

<div class="hero-slider">
    <div class="slides-wrapper">
        <div class="slide">
            <img src="img/blog-hero-1.jpg" alt="Blogger writing in a scenic location">
            <div class="slide-content">
                <h1>Share Your Travel Experience</h1>
                <p>Document your journeys, share your stories, and inspire others to explore.</p>
            </div>
        </div>
        <div class="slide">
            <img src="img/blog-hero-2.jpg" alt="A collection of travel photos">
            <div class="slide-content">
                <h1>Every Journey is a Story</h1>
                <p>Create a beautiful log of your adventures for the world to see.</p>
            </div>
        </div>
    </div>
</div>

<div class="page-wrapper">
    <aside class="waterfall-slider">
        <div class="waterfall-column" id="waterfall-column">
            <!-- Images will be duplicated by JS for seamless loop -->
            <img src="img/slider-1.jpg" alt="Scenic view 1">
            <img src="img/slider-2.jpg" alt="Scenic view 2">
            <img src="img/slider-3.jpg" alt="Scenic view 3">
            <img src="img/slider-4.jpg" alt="Scenic view 4">
            <img src="img/slider-5.jpg" alt="Scenic view 5">
            <img src="img/slider-6.jpg" alt="Scenic view 6">
        </div>
    </aside>

    <main class="container">
        <section class="content-section">
            <h2>Create a New Blog Post</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <input type="text" name="place_name" placeholder="Place Name (e.g., Paris, France)" required>
                </div>
                <div class="form-group">
                    <label for="image_url" class="file-upload-label">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                        <span id="file-chosen-text">Choose a blog image (optional)</span>
                    </label>
                    <input type="file" id="image" name="image" accept="image/*" style="display: none;">
                </div>
                <div class="form-group">
                    <input type="text" name="title" placeholder="Blog Title" required>
                </div>
                <div class="form-group">
                    <textarea name="content" placeholder="Write your experience..." required></textarea>
                </div>
                <button type="submit" class="btn-submit">Post Blog</button>
            </form>
        </section>

        <section class="content-section">
            <h2>All Blogs</h2>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="blog-post">';
                    if (!empty($row['image'])) {
                        echo '<img src="' . htmlspecialchars($row['image']) . '" alt="' . htmlspecialchars($row['title']) . '" class="blog-image">';
                    }
                    echo '<h3>' . htmlspecialchars($row['title']) . ' - ' . htmlspecialchars($row['place_name']) . '</h3>';
                    echo '<div class="meta">By ' . htmlspecialchars($row['username']) . ' on ' . date('M d, Y', strtotime($row['created_at'])) . '</div>';
                    echo '<p>' . nl2br(htmlspecialchars($row['content'])) . '</p>';
                    echo '</div>';
                }
            } else {
                echo "<p>No blogs posted yet. Be the first!</p>";
            }
            ?>
        </section>
    </main>
</div>

<!-- Footer -->
<?php require 'footer.php'; ?>

<script>
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

// Hero slider JS
const heroSliderWrapper = document.querySelector('.hero-slider .slides-wrapper');
if (heroSliderWrapper) {
    let heroSlides = document.querySelectorAll('.hero-slider .slide');
    let currentHeroSlide = 0;
    const heroSlideCount = heroSlides.length;

    if (heroSlideCount > 1) {
        const firstHeroSlideClone = heroSlides[0].cloneNode(true);
        heroSliderWrapper.appendChild(firstHeroSlideClone);

        function nextHeroSlide() {
            currentHeroSlide++;
            heroSliderWrapper.style.transform = `translateX(-${currentHeroSlide * 100}%)`;
            heroSliderWrapper.style.transition = 'transform 0.5s ease-in-out';

            if (currentHeroSlide >= heroSlideCount) {
                setTimeout(() => {
                    heroSliderWrapper.style.transition = 'none';
                    currentHeroSlide = 0;
                    heroSliderWrapper.style.transform = `translateX(0%)`;
                }, 500);
            }
        }
        setInterval(nextHeroSlide, 5000); // Change slide every 5 seconds
    }
}

// Waterfall Slider Logic
const waterfallColumn = document.getElementById('waterfall-column');
if (waterfallColumn) {
    const images = waterfallColumn.innerHTML;
    waterfallColumn.innerHTML += images;
}

// Custom file input text update
const imageInput = document.getElementById('image');
if (imageInput) {
    const fileChosenText = document.getElementById('file-chosen-text');
    imageInput.addEventListener('change', function(){
        const fileName = this.files[0] ? this.files[0].name : "Choose a blog image (optional)";
        fileChosenText.textContent = fileName;
    });
}

</script>

</body>
</html>
