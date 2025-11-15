<?php
session_start();
require 'db.php';

// --- Fetch Articles ---
$articles_per_page = 7; // We'll fetch 7: 1 for featured, 6 for the grid
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $articles_per_page;

$category_filter = $_GET['category'] ?? '';

$sql = "SELECT a.*, u.username as author_name FROM articles a LEFT JOIN users u ON a.author_id = u.id WHERE a.status = 'published'";
$count_sql = "SELECT COUNT(*) FROM articles WHERE status = 'published'";

if (!empty($category_filter)) {
    $sql .= " AND a.category LIKE ?";
    $count_sql .= " AND category LIKE ?";
}

$sql .= " ORDER BY a.created_at DESC LIMIT ? OFFSET ?";

// Fetch total count for pagination
$stmt_count = $conn->prepare($count_sql);
if (!empty($category_filter)) {
    $search_term = "%" . $category_filter . "%";
    $stmt_count->bind_param("s", $search_term);
}
$stmt_count->execute();
$total_articles = $stmt_count->get_result()->fetch_row()[0];
$total_pages = ceil($total_articles / $articles_per_page);

// Fetch articles for the current page
$stmt = $conn->prepare($sql);
if (!empty($category_filter)) {
    $stmt->bind_param("sii", $search_term, $articles_per_page, $offset);
} else {
    $stmt->bind_param("ii", $articles_per_page, $offset);
}
$stmt->execute();
$result = $stmt->get_result();
$articles = $result->fetch_all(MYSQLI_ASSOC);

// Separate the first article to be featured
$featured_article = null;
$other_articles = [];
if (!empty($articles)) {
    $featured_article = array_shift($articles); // Takes the first article
    $other_articles = $articles; // The rest of the articles
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Latest Articles</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --max-w: 1100px;
      --gap: 20px;
      --bg:#1e293b; /* slate-700 */
      --card:#0b1220;
      --muted:#9aa4b2;
      --accent:#1d9bf0;
      --glass: rgba(255,255,255,0.03);
    }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: 'Montserrat', sans-serif;
      background: linear-gradient(180deg, var(--bg) 0%, #0f172a 100%);
      color: #e6eef8;
    }

    /* Header Styles */
    header { display: flex; justify-content: space-between; align-items: center; padding: 20px 50px; background-color: var(--card); color: #fff; border-bottom: 1px solid rgba(255,255,255,0.07); position: sticky; top: 0; z-index: 100; }
    header .logo { font-size: 24px; font-weight: bold; }
    .header-right-group { display: flex; align-items: center; gap: 35px; }
    .main-nav ul { list-style: none; margin: 0; padding: 0; display: flex; gap: 35px; }
    .main-nav a { color: var(--muted); text-decoration: none; font-weight: 500; font-size: 15px; padding: 5px 0; position: relative; transition: color 0.3s ease; }
    .main-nav a:hover { color: #fff; }
    .main-nav a.active { color: #fff; font-weight: 700; }
    .main-nav a.active::after { content: ''; position: absolute; bottom: -20px; left: 0; width: 100%; height: 2px; background-color: var(--accent); }
    .search-container { flex-grow: 1; padding: 0 40px; }
    .search-bar { display: flex; align-items: center; justify-content: space-between; max-width: 350px; margin: 0 auto; background-color: var(--glass); border: 1px solid rgba(255,255,255,0.1); border-radius: 999px; padding: 8px 15px; cursor: pointer; transition: all 0.3s ease; }
    .search-bar:hover { background-color: rgba(255,255,255,0.07); border-color: rgba(255,255,255,0.2); }
    .search-bar span { color: var(--muted); font-weight: 500; }
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

    header {
      /* This style was conflicting with the main header style, so it's removed. */
    }

    /* Hero Slider Styles */
    .hero-slider { position: relative; width: 100%; height: 450px; overflow: hidden; }
    .hero-slider .slides-wrapper { display: flex; width: 100%; height: 100%; transition: transform 0.5s ease-in-out; }
    .hero-slider .slide { position: relative; width: 100%; height: 100%; flex-shrink: 0; }
    .hero-slider .slide img { width: 100%; height: 100%; object-fit: cover; }
    .slide-content { position: absolute; bottom: 0; left: 0; right: 0; padding: 80px 50px 40px; background: linear-gradient(to top, rgba(11,18,32,0.9) 0%, rgba(11,18,32,0) 100%); color: #fff; }
    .slide-content h2 { font-size: 36px; margin: 0 0 10px 0; text-shadow: 2px 2px 8px rgba(0,0,0,0.7); }
    .slide-content p { font-size: 18px; color: var(--muted); max-width: 700px; text-shadow: 1px 1px 4px rgba(0,0,0,0.7); }

    .page-header {
        padding: 40px 50px;
    }
    .page-header h1 {
        font-size: 36px;
        text-align: center;
    }

    /* Featured Article Styles */
    .featured-article-card {
        background: var(--card);
        border-radius: 12px;
        border: 1px solid rgba(255,255,255,0.07);
        margin-bottom: 40px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .featured-article-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 30px rgba(0,0,0,0.3);
    }
    .featured-article-card .featured-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        min-height: 300px;
    }
    .featured-article-card .featured-content {
        padding: 30px;
        display: flex;
        flex-direction: column;
    }
    .featured-article-card .featured-content .article-title {
        font-size: 24px;
        line-height: 1.4;
    }

    /* Search/Filter Bar */
    .filter-container {
        background: var(--card);
        padding: 20px 30px;
        border-radius: 12px;
        border: 1px solid rgba(255,255,255,0.07);
        margin-bottom: 40px;
    }
    .filter-form { display: flex; gap: 15px; align-items: center; }
    .filter-form input[type="text"] { flex-grow: 1; padding: 12px; background: var(--glass); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #e6eef8; font-family: 'Montserrat', sans-serif; font-size: 16px; }
    .filter-form input[type="text"]:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 2px rgba(29, 155, 240, 0.3); }
    .filter-form button { padding: 12px 20px; background: var(--accent); color: #041022; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; }
    .filter-form a { color: var(--muted); text-decoration: none; font-size: 14px; }

    .articles-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 24px; /* Increased space between articles */
    }
    .article-card {
      background: var(--card);
      border-radius: 12px;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      border: 1px solid rgba(255,255,255,0.05);
      box-shadow: 0 4px 20px rgba(0,0,0,0.2);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .article-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 30px rgba(0,0,0,0.3);
    }
    .article-card img {
      width: 100%;
      object-fit: cover;
      aspect-ratio: 16 / 9;
      transition: transform .4s ease;
    }
    .article-card:hover img {
      transform: scale(1.05);
    }
    .article-content {
      padding: 18px;
      flex: 1;
      display: flex;
      flex-direction: column;
    }
    .article-meta {
      font-size: 13px;
      color: var(--muted);
      margin-bottom: 10px;
    }
    .article-title {
      font-size: 18px;
      margin: 0 0 10px 0;
      flex: 0;
      color: #e6eef8;
    }
    .article-snippet {
      font-size: 14px;
      color: var(--muted);
      flex: 1;
      line-height: 1.6;
    }
    .read-more {
      margin-top: 12px;
      text-decoration: none;
      color: var(--accent);
      font-weight: 600;
    }
    .pagination {
      margin-top: 32px;
      text-align: center;
    }
    .pagination a {
      margin: 0 4px;
      color: var(--muted);
      text-decoration: none;
      padding: 8px 14px;
      border-radius: 8px;
      background: var(--glass);
      border: 1px solid rgba(255,255,255,0.1);
      transition: all 0.3s ease;
    }
    .pagination a:hover {
      background: rgba(255,255,255,0.1);
      color: #fff;
    }
    .pagination a.active {
      font-weight: bold;
      background: var(--accent);
      color: #041022;
      border-color: var(--accent);
    }

    /* Page Layout with Waterfall Slider */
    .page-wrapper {
        display: flex;
        max-width: 1400px;
        margin: 0 auto;
        padding: 40px 40px; /* Aligned slider more to the left */
        gap: 40px; /* Increased space between slider and articles */
        align-items: flex-start;
    }
    .main-content-area { flex: 2.5; }
    .waterfall-slider {
        flex: 0 0 450px; /* Made the slider bigger */
        height: calc(100vh - 121px);
        overflow: hidden;
        position: sticky;
        top: 101px;
        border-radius: 12px;
    }
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
    .waterfall-column { display: flex; flex-direction: column; animation: waterfall-scroll 30s linear infinite; }
    .waterfall-column img { width: 100%; height: auto; object-fit: cover; margin-bottom: 15px; border-radius: 10px; border: 1px solid var(--glass); }
    @keyframes waterfall-scroll { 0% { transform: translateY(0); } 100% { transform: translateY(-50%); }
    }
  </style>
</head>
<body>
<?php require 'header.php'; ?>

<div class="hero-slider">
    <div class="slides-wrapper">
        <div class="slide">
            <img src="img/article-hero-1.jpg" alt="Person writing in a journal with a scenic background">
            <div class="slide-content">
                <h2>In-Depth Travel Stories</h2>
                <p>Explore detailed narratives and guides from around the globe.</p>
            </div>
        </div>
        <div class="slide">
            <img src="img/article-hero-2.jpg" alt="A map and a compass on a wooden table">
            <div class="slide-content">
                <h2>Expert Insights & Tips</h2>
                <p>Gain knowledge from seasoned travelers and local experts.</p>
            </div>
        </div>
        <div class="slide">
            <img src="img/article-hero-3.jpg" alt="A library of travel books">
            <div class="slide-content">
                <h2>Journey Beyond the Surface</h2>
                <p>Discover the culture, history, and secrets of your next destination.</p>
            </div>
        </div>
    </div>
</div>

  <div class="page-wrapper">
      <aside class="waterfall-slider">
          <div class="waterfall-column" id="waterfall-column">
              <!-- Images can be dynamically loaded or hardcoded -->
              <img src="img/slider-1.jpg" alt="Article preview 1">
              <img src="img/slider-2.jpg" alt="Article preview 2">
              <img src="img/slider-3.jpg" alt="Article preview 3">
              <img src="img/slider-4.jpg" alt="Article preview 4">
              <img src="img/slider-5.jpg" alt="Article preview 5">
              <img src="img/slider-6.jpg" alt="Article preview 6">
          </div>
      </aside>

      <main class="main-content-area">
          <div class="filter-container">
              <form action="articles.php" method="GET" class="filter-form">
                  <input type="text" name="category" placeholder="Search by category or place..." value="<?php echo htmlspecialchars($category_filter); ?>">
                  <button type="submit">Search</button>
                  <?php if (!empty($category_filter)): ?>
                      <a href="articles.php">Clear</a>
                  <?php endif; ?>
              </form>
          </div>

          <?php if ($featured_article): ?>
          <div class="featured-article-card">
              <img src="<?php echo htmlspecialchars($featured_article['image_url']); ?>" alt="<?php echo htmlspecialchars($featured_article['title']); ?>" class="featured-image">
              <div class="featured-content">
                  <div class="article-meta"><?php echo htmlspecialchars($featured_article['author_name'] ?? 'Staff'); ?> • <?php echo date('M d, Y', strtotime($featured_article['created_at'])); ?></div>
                  <h2 class="article-title"><?php echo htmlspecialchars($featured_article['title']); ?></h2>
                  <div class="article-snippet"><?php echo htmlspecialchars($featured_article['summery']); ?></div>
                  <a class="read-more" href="view_article.php?id=<?php echo $featured_article['id']; ?>">Read more →</a>
              </div>
          </div>
          <?php endif; ?>

          <div id="articles" class="articles-grid">
              <?php if (!empty($other_articles)): ?>
                  <?php foreach ($other_articles as $article): ?>
                      <div class="article-card">
                          <img src="<?php echo htmlspecialchars($article['image_url']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>">
                          <div class="article-content">
                              <div class="article-meta"><?php echo htmlspecialchars($article['author_name'] ?? 'Staff'); ?> • <?php echo date('M d, Y', strtotime($article['created_at'])); ?></div>
                              <h2 class="article-title"><?php echo htmlspecialchars($article['title']); ?></h2>
                              <div class="article-snippet"><?php echo htmlspecialchars(substr($article['summery'], 0, 80)) . '...'; ?></div>
                              <a class="read-more" href="view_article.php?id=<?php echo $article['id']; ?>">Read more →</a>
                          </div>
                      </div>
                  <?php endforeach; ?>
              <?php else: ?>
                  <?php if (!$featured_article): ?>
                      <p style="grid-column: 1 / -1; text-align: center; color: var(--muted);">No articles found matching your criteria.</p>
                  <?php endif; ?>
              <?php endif; ?>
          </div>

          <div class="pagination" id="pagination">
              <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                  <a href="?page=<?php echo $p; ?>&category=<?php echo urlencode($category_filter); ?>" class="<?php if ($p == $page) echo 'active'; ?>">
                      <?php echo $p; ?>
                  </a>
              <?php endfor; ?>
          </div>
      </main>
  </div>

<!-- Footer -->
<?php require 'footer.php'; ?>

  <script>
  </script>
  <script>
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
  </script>
</body>
</html>
