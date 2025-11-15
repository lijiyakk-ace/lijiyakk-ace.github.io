<?php
// Start the session if it hasn't been started already.
// This makes the header self-sufficient.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure DB connection is available.
if (!isset($conn)) {
    require_once 'db.php';
}

// Fetch the current user's avatar for the header
$user_avatar_header = null;
if (isset($_SESSION['user'])) {
    $username = $_SESSION['user'];
    $avatar_stmt = $conn->prepare("SELECT avatar FROM users WHERE username = ?");
    $avatar_stmt->bind_param("s", $username);
    $avatar_stmt->execute();
    $user_avatar_header = $avatar_stmt->get_result()->fetch_assoc()['avatar'] ?? null;
    $avatar_stmt->close();
}

// --- Data for Universal Filter Bar ---

// Define a fixed list of continents
$filter_continents = [
    'Africa', 'Asia', 'Europe', 'North America', 'South America', 'Australia', 'Antarctica'
];

// Fetch all unique tags from the database
$all_tags_from_db = [];
$tags_result = $conn->query("SELECT tags FROM destinations WHERE tags IS NOT NULL AND tags != ''");
if ($tags_result) {
    while ($row = $tags_result->fetch_assoc()) {
        $tags_array = explode(',', $row['tags']);
        foreach ($tags_array as $tag) {
            $trimmed_tag = trim($tag);
            if (!empty($trimmed_tag)) {
                $all_tags_from_db[] = $trimmed_tag;
            }
        }
    }
}
$filter_unique_tags = array_unique($all_tags_from_db);
sort($filter_unique_tags);

?>
<style>
    /* Universal Search Bar */
    .search-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        max-width: 350px;
        margin: 0 auto;
        background-color: var(--glass);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 999px;
        padding: 0; /* Remove padding to let input fill it */
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    .search-bar:hover {
        background-color: rgba(255,255,255,0.07);
    }
    .search-bar input {
        padding: 8px 15px;
    }
    .search-bar svg {
        margin-right: 15px;
    }

    /* Universal Filter Bar */
    .filter-bar { display: flex; justify-content: center; align-items: center; gap: 25px; padding: 20px 50px; background-color: var(--card); border-bottom: 1px solid rgba(255,255,255,0.07); flex-wrap: wrap; display: none; }
    .filter-group { display: flex; flex-direction: column; }
    .filter-group label { font-size: 12px; color: var(--muted); margin-bottom: 5px; font-weight: 500; }
    .filter-bar select { background-color: var(--glass); color: #e6eef8; border: 1px solid rgba(255,255,255,0.1); border-radius: 6px; padding: 8px 12px; font-family: 'Montserrat', sans-serif; font-size: 14px; min-width: 180px; }
    .filter-bar select:disabled { opacity: 0.5; cursor: not-allowed; }
    .filter-bar option { background-color: var(--card); color: #e6eef8; }
    .filter-bar .btn-primary {
        background: linear-gradient(90deg,var(--accent),#3bb0ff);
        color: #021426; border: none; font-weight: 700;
        font-family: 'Montserrat', sans-serif; transition: background-color 0.3s ease;
        cursor: pointer; border-radius: 6px;
    }
</style>
<style>
    /* Profile Dropdown Styles */
    .profile-icon { display: inline-block; width: 40px; height: 40px; border-radius: 50%; background-color: var(--glass); cursor: pointer; display: flex; align-items: center; justify-content: center; }
    .profile-dropdown { position: relative; display: inline-block; }
    .dropdown-content { display: none; position: absolute; top: calc(100% + 10px); right: 0; background-color: var(--card); min-width: 250px; box-shadow: 0 4px 30px rgba(0,0,0,0.1); border: 1px solid rgba(255, 255, 255, 0.1); z-index: 10; border-radius: 8px; padding: 8px 0; }
    .dropdown-content::before { content: ''; position: absolute; top: -10px; right: 12px; border-width: 0 8px 10px 8px; border-style: solid; border-color: transparent transparent var(--card) transparent; }
    .dropdown-content a { color: #e6eef8; padding: 14px 20px; text-decoration: none; display: flex; align-items: center; gap: 12px; font-weight: 500; }
    .dropdown-content a:hover {background-color: rgba(29, 155, 240, 0.1)}
    .dropdown-header { padding: 14px 20px; border-bottom: 1px solid rgba(255, 255, 255, 0.1); margin-bottom: 8px; }
    .dropdown-header span { font-weight: 700; color: #fff; }
    .show { display:block; }
</style>

<!-- Header -->
<header>
    <div class="logo">Travel Tales</div>
    <div class="search-container">
        <div id="universal-search-bar" class="search-bar">
            <input type="text" id="universal-search-input" placeholder="Search destinations, themes..." style="background: transparent; border: none; color: inherit; font-family: inherit; font-size: 14px; width: 100%;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--muted)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        </div>
    </div>
    <div class="header-right-group">
        <nav class="main-nav">
            <ul>
                <?php
                    $current_page = basename($_SERVER['PHP_SELF']);
                    $nav_links = [
                        'index.php' => 'Home',
                        'destinations.php' => 'Destinations',
                        'forum.php' => 'Forum',
                        'quiz.php' => 'Quiz',
                        'blogs.php' => 'Blogs',
                        'articles.php' => 'Articles'
                    ];
                    foreach ($nav_links as $url => $title) {
                        echo '<li><a href="' . $url . '" class="' . ($current_page == $url ? 'active' : '') . '">' . $title . '</a></li>';
                    }
                ?>
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
                    <a href="profile.php">Profile</a>
                    <a href="notifications.php">Notifications</a>
                    <a href="feedback.php">Feedback</a>
                    <a href="support.php">Support</a>
                    <a href="logout.php">Logout</a>
                </div>
            </div>
            <?php else: ?>
                <button onclick="window.location.href='login.php'" class="btn-secondary">Login</button>
                <button onclick="window.location.href='signup.php'" class="btn-primary">Sign Up</button>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- Universal Filter Bar -->
<div id="universal-filter-bar" class="filter-bar">
    <div class="filter-group">
        <label for="filter-theme">Theme</label>
        <select id="filter-theme">
            <option value="">Any Theme</option>
            <?php foreach ($filter_unique_tags as $tag): ?>
                <option value="<?php echo htmlspecialchars(strtolower($tag)); ?>"><?php echo htmlspecialchars(ucfirst($tag)); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filter-group">
        <label for="filter-continent">Continent</label>
        <select id="filter-continent">
            <option value="">Any Continent</option>
            <?php foreach ($filter_continents as $continent): ?>
                <option value="<?php echo htmlspecialchars(strtolower(str_replace(' ', '-', $continent))); ?>"><?php echo htmlspecialchars($continent); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filter-group">
        <label for="filter-country">Country</label>
        <select id="filter-country" disabled>
            <option value="">Select Continent First</option>
        </select>
    </div>
    <div class="filter-group" style="justify-content: flex-end;">
        <button id="apply-filters-btn" class="btn-primary" style="padding: 8px 20px; height: 37px;">
            Search
        </button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Universal Search and Filter Logic ---
    const universalSearchBar = document.getElementById('universal-search-bar');
    const universalSearchInput = document.getElementById('universal-search-input');
    const universalFilterBar = document.getElementById('universal-filter-bar');

    // 1. Toggle Filter Bar
    if (universalSearchBar) {
        universalSearchBar.addEventListener('click', (event) => {
            event.stopPropagation();
            if (universalFilterBar) {
                universalFilterBar.style.display = universalFilterBar.style.display === 'flex' ? 'none' : 'flex';
            }
        });
    }

    // 2. Close Filter Bar on outside click
    window.addEventListener('click', (event) => {
        if (universalFilterBar && universalSearchBar && !universalFilterBar.contains(event.target) && !universalSearchBar.contains(event.target)) {
            universalFilterBar.style.display = 'none';
        }
    });

    // 3. Handle Keyword Search (Enter key)
    if (universalSearchInput) {
        universalSearchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const query = e.target.value.trim();
                if (query) {
                    window.location.href = `destinations.php?q=${encodeURIComponent(query)}`;
                }
            }
        });
    }

    // 4. Dynamic Country Dropdown Logic (and other filter actions)
    const continentSelect = document.getElementById('filter-continent');
    const countrySelect = document.getElementById('filter-country');
    const countriesByContinent = {
        'africa': ['Nigeria', 'Egypt', 'South Africa', 'Kenya', 'Morocco', 'Tanzania', 'Ethiopia'],
        'asia': ['China', 'India', 'Japan', 'Thailand', 'Vietnam', 'Indonesia', 'South Korea', 'Malaysia', 'United Arab Emirates'],
        'europe': ['France', 'Germany', 'Italy', 'Spain', 'United Kingdom', 'Greece', 'Portugal', 'Netherlands', 'Switzerland'],
        'north-america': ['USA', 'Canada', 'Mexico', 'Cuba', 'Costa Rica'],
        'south-america': ['Brazil', 'Argentina', 'Peru', 'Colombia', 'Chile'],
        'australia': ['Australia', 'New Zealand', 'Fiji']
    };

    if (continentSelect && countrySelect) {
        continentSelect.addEventListener('change', function() {
            const selectedContinent = this.value;
            countrySelect.innerHTML = ''; // Clear current options

            if (selectedContinent && countriesByContinent[selectedContinent]) {
                countrySelect.disabled = false;
                let defaultOption = document.createElement('option');
                defaultOption.value = "";
                defaultOption.textContent = "Any Country";
                countrySelect.appendChild(defaultOption);

                countriesByContinent[selectedContinent].forEach(country => {
                    let option = document.createElement('option');
                    option.value = country.toLowerCase().replace(/ /g, '-');
                    option.textContent = country;
                    countrySelect.appendChild(option);
                });
            } else {
                countrySelect.disabled = true;
                let option = document.createElement('option');
                option.value = "";
                option.textContent = "Select Continent First";
                countrySelect.appendChild(option);
            }
        });
    }

    // 5. Apply Filters Button Logic
    const applyFiltersBtn = document.getElementById('apply-filters-btn');
    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', () => {
            const theme = document.getElementById('filter-theme').value;
            const continent = document.getElementById('filter-continent').value;
            const country = document.getElementById('filter-country').value;

            const params = new URLSearchParams();
            if (theme) params.append('theme', theme);
            if (continent) params.append('continent', continent);
            if (country) params.append('country', country);

            window.location.href = `destinations.php?${params.toString()}`;
        });
    }

    // --- Profile Dropdown Logic ---
    const profileIcon = document.getElementById('profileIcon');
    if (profileIcon) {
        profileIcon.addEventListener('click', function(event) {
            event.stopPropagation();
            document.getElementById('profileDropdown').classList.toggle('show');
        });

        window.addEventListener('click', function(event) {
            if (!profileIcon.contains(event.target) && !event.target.closest('.profile-dropdown')) {
                var dropdowns = document.getElementsByClassName("dropdown-content");
                for (var i = 0; i < dropdowns.length; i++) {
                    dropdowns[i].classList.remove('show');
                }
            }
        });
    }
});
</script>