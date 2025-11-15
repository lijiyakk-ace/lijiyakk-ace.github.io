<?php
session_start();
require 'db.php'; // Include the database connection

// Fetch all destinations from the database
$destinations = [];
$result = $conn->query("SELECT id, title, continent as region, country, 'city' as type, tags, image_url as img, short_description as `desc`, latitude, longitude FROM destinations ORDER BY created_at DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $row['tags'] = explode(',', $row['tags']); // Convert comma-separated tags to an array
        $destinations[] = $row;
    }
}
$user_id = null; // Initialize user_id
$user_wishlist = []; // Initialize wishlist array
if (isset($_SESSION['user'])) {
    $username = $_SESSION['user'];
    $user_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $user_stmt->bind_param("s", $username);
    $user_stmt->execute();
    $user_id = $user_stmt->get_result()->fetch_assoc()['id'] ?? null;
    if($user_id) {
        $wishlist_stmt = $conn->prepare("SELECT destination_id FROM wishlist WHERE user_id = ?");
        $wishlist_stmt->bind_param("i", $user_id);
        $wishlist_stmt->execute();
        $wishlist_result = $wishlist_stmt->get_result();
        $user_wishlist = array_column($wishlist_result->fetch_all(MYSQLI_ASSOC), 'destination_id');
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Places — Travel Directory</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
/* Reset and Base Styles */
:root{
  --bg:#1e293b; /* slate-700 */
  --card:#0b1220;
  --muted:#9aa4b2;
  --accent:#1d9bf0;
  --glass: #0f172a;
  --max-width: 1400px;
}
body{
  margin:0;
  font-family:'Montserrat',sans-serif;
  background:linear-gradient(180deg, var(--bg) 0%, #0f172a 100%);
  color:#e6eef8;
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

/* Header Right Group for Nav and Auth */
.header-right-group {
    display: flex;
    align-items: center;
    gap: 35px; /* Space between nav links and auth buttons */
}

/* Main Navigation */
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

/* Search Container */
.search-container {
    flex-grow: 1;
    padding: 0 40px;
}

/* Main Layout */
.wrap{max-width:var(--max-width);margin:0 auto;padding:40px 50px;}
.main{ display:flex;flex-direction:column;gap:18px;background: var(--card);padding: 40px;border-radius: 12px;border: 1px solid rgba(255,255,255,0.07); }
.controls{ display:flex;justify-content:space-between;align-items:center;margin-bottom:18px; flex-wrap: wrap; gap: 15px; }
  margin:0;
  font-family:'Montserrat',sans-serif;
  background:linear-gradient(180deg, var(--bg) 0%, #0f172a 100%);
  color:#e6eef8;
}

/* Main Layout */
.wrap{max-width:var(--max-width);margin:0 auto;padding:40px 50px;}
.main{ display:flex;flex-direction:column;gap:18px;background: var(--card);padding: 40px;border-radius: 12px;border: 1px solid rgba(255,255,255,0.07); }
.controls{ display:flex;justify-content:space-between;align-items:center;margin-bottom:18px; flex-wrap: wrap; gap: 15px; }
.results{font-size:14px;color:var(--muted)}

/* View Toggle Buttons */
.view-toggle { display: flex; gap: 5px; background: var(--glass); border-radius: 8px; padding: 4px; border: 1px solid rgba(255,255,255,0.1); }
.view-toggle button { background: transparent; border: none; color: var(--muted); padding: 6px 12px; border-radius: 6px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 6px; }
.view-toggle button.active { background: var(--accent); color: #041022; }

/* Grid & Cards */
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:24px}

/* Map View Styles */
#map-view { display: none; }
#map { height: 600px; background-color: var(--bg); border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); }
/* Leaflet Popup Customization */
.leaflet-popup-content-wrapper { background: var(--card); color: #e6eef8; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); }
.leaflet-popup-tip { background: var(--card); }

.card{background:var(--glass);border-radius:12px;overflow:hidden;border:1px solid rgba(255,255,255,0.05);box-shadow:0 4px 20px rgba(0,0,0,0.2);transition:transform .3s ease,box-shadow .3s ease;display:flex;flex-direction:column}
.card:hover{transform:translateY(-5px);box-shadow:0 8px 30px rgba(0,0,0,0.3)}
.card .media{height:200px;overflow:hidden}
.card .media img{width:100%;height:100%;object-fit:cover;transition:transform .4s ease}
.card:hover .media img{transform:scale(1.05)}
.card .content{padding:18px;display:flex;flex-direction:column;flex-grow:1}
.card .title{font-size:18px;font-weight:700;margin-bottom:8px}
.card .meta{display:flex;justify-content:space-between;font-size:13px;margin-bottom:12px}
.card .tags{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:12px}
.card .extra-info { display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: var(--muted); margin-bottom: 12px; border-top: 1px solid var(--glass); padding-top: 12px; }
.card .price-range { font-weight: 700; color: #fff; }
.card .best-time { text-align: right; }

.card .tag{background:rgba(255,255,255,0.05);padding:4px 8px;border-radius:4px;font-size:12px;font-weight:600;color:var(--muted)}
.card .cta{margin-top:auto;display:flex;justify-content:space-between;align-items:center}
.page-btn{background:var(--accent);color:#041022;border:none;padding:8px 14px;border-radius:8px;font-weight:600;cursor:pointer;font-family:'Montserrat',sans-serif;transition:background .3s}
.page-btn:hover{background:#4fbfff}

/* Pagination */
.pagination{display:flex;justify-content:center;gap:8px;margin-top:30px}
.pagination .page-btn{background:var(--glass);color:var(--muted);border:1px solid rgba(255,255,255,0.1)}
.pagination .page-btn.active{background:var(--accent);color:#041022;border-color:var(--accent)}

/* Hidden input for search query */
.wishlist-btn {
    background: none;
    border: none;
    color: var(--muted);
    cursor: pointer;
    font-size: 1.2em;
    transition: color 0.3s ease;
}
.wishlist-btn.active {
    color: #ff4d4d; /* Red color for wishlisted items */
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
.legal-links a { color: var(--muted); text-decoration: none; }
.legal-links a:hover { text-decoration: underline; }
@media (max-width: 768px) {
    .footer-bottom {
        flex-direction: column;
        gap: 20px;
    }
}
</style>
</head>
<body>
<?php require 'header.php'; ?>

<main class="wrap" role="main">
  <section class="main">
    <div class="controls">
      <div class="left">
        <div class="results" id="resultsCount">Loading places…</div>
      </div>
      <div class="view-toggle">
        <button id="grid-view-btn" class="active">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
            Grid
        </button>
        <button id="map-view-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
            Map
        </button>
      </div>
      <div>
        <label class="muted">Sort:</label>
        <select id="sort" style="background:var(--glass);border:1px solid rgba(255,255,255,0.1);padding:8px;border-radius:8px;color:#fff">
          <option value="popular">Most popular</option>
          <option value="a-z">A → Z</option>
          <option value="recent">Recently added</option>
        </select>
      </div>
    </div>

    <div id="grid-view">
        <div class="grid" id="grid"></div>
    </div>
    <div id="map-view"><div id="map"></div></div>

    <div class="pagination" id="pagination"></div>
  </section>
</main>

<!-- Footer -->
<?php require 'footer.php'; ?>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const ALL_PLACES = <?php echo json_encode($destinations); ?>;
    const USER_WISHLIST = new Set(<?php echo json_encode($user_wishlist); ?>.map(String));
    let state = { q:'', region:'', country: '', tags:new Set(), sort:'popular', page:1, perPage:9 };
    const el = id => document.getElementById(id);
    let map = null; // To hold the map instance

    function capitalize(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''; }
    function truncate(s, n) { return s.length > n ? s.slice(0, n - 1) + '…' : s; }

    function render() {
        const grid = el('grid'), resultsCount = el('resultsCount');

        let items = ALL_PLACES.filter(p => {
            if (state.region && p.region.toLowerCase().replace(/ /g, '-') !== state.region) return false;
            if (state.country && p.country.toLowerCase().replace(/ /g, '-') !== state.country) return false;
            if (state.tags.size > 0) {
                const destinationTagsLower = p.tags.map(tag => tag.trim().toLowerCase());
                for (const t of state.tags) {
                    if (!destinationTagsLower.includes(t)) return false;
                }
            }
            if (state.q) {
                const q = state.q.toLowerCase();
                if (!(p.title.toLowerCase().includes(q) || p.desc.toLowerCase().includes(q) || p.tags.join(' ').toLowerCase().includes(q) || p.country.toLowerCase().includes(q))) return false;
            }
            return true;
        });

        if (state.sort === 'a-z') items.sort((a, b) => a.title.localeCompare(b.title));
        else if (state.sort === 'recent') items.sort((a, b) => b.id - a.id);
        else items.sort((a, b) => b.id - a.id); // Default sort to recent if 'popular' is chosen but no rating exists

        const total = items.length, pages = Math.max(1, Math.ceil(total / state.perPage));
        if (state.page > pages) state.page = pages;
        const start = (state.page - 1) * state.perPage, pageItems = items.slice(start, start + state.perPage);

        resultsCount.textContent = `${total} places found`;
        grid.innerHTML = '';

        if (total === 0) {
            grid.innerHTML = '<div style="grid-column: 1 / -1; text-align:center; padding: 40px; color: var(--muted);">No destinations match your criteria. Try adjusting your search or filters.</div>';
        } else {
            for (const p of pageItems) {
                const card = document.createElement('article');
                card.className = 'card';
                const isWishlisted = USER_WISHLIST.has(String(p.id));
                card.innerHTML = `
                    <div class="media"><img src="${p.img || 'img/default_destination.jpg'}" alt="${p.title}" loading="lazy"></div>
                    <div class="content">
                        <a href="destination_detail.php?id=${p.id}" style="text-decoration:none; color:inherit;"><div class="title">${p.title}</div></a>
                        <div class="meta"><div class="muted">${capitalize(p.region)}</div></div>
                        
                        <div class="tags">${p.tags.map(t => `<span class="tag">${t.trim()}</span>`).join('')}</div>
                        <div class="muted" style="font-size:13px; margin-bottom: 12px;">${truncate(p.desc, 90)}</div>
                        <div class="cta">
                            <a href="destination_detail.php?id=${p.id}" class="page-btn view" data-id="${p.id}">View Details</a>
                            ${<?php echo json_encode(isset($_SESSION['user'])); ?> ? `<button class="wishlist-btn ${isWishlisted ? 'active' : ''}" data-destination-id="${p.id}" title="Add to wishlist"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="${isWishlisted ? '#ff4d4d' : 'none'}" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg></button>` : ''}
                        </div>
                    </div>`;
                grid.appendChild(card);
            }
        }
        renderPagination(pages);
        renderMap(items);
    }

    function renderMap(items) {
        if (!map) {
            map = L.map('map', { worldCopyJump: false }).setView([20, 0], 2);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
                subdomains: 'abcd', maxZoom: 19
            }).addTo(map);
        }
        map.eachLayer(layer => { if (layer instanceof L.Marker) map.removeLayer(layer); });
        
        const locations = [];
        items.forEach(p => {
            if (p.latitude && p.longitude) {
                locations.push([p.latitude, p.longitude]);
                const popupContent = `
                    <div style="width: 200px;">
                        <img src="${p.img || 'img/default_destination.jpg'}" alt="${p.title}" style="width: 100%; height: 100px; object-fit: cover; border-radius: 4px;">
                        <h4 style="margin: 10px 0 5px 0; font-size: 16px; font-weight: 700;"><a href="destination_detail.php?id=${p.id}" style="text-decoration: none; color: inherit;">${p.title}</a></h4>
                        <p style="margin: 0; font-size: 13px; color: var(--muted);">${truncate(p.desc, 80)}</p>
                    </div>`;
                L.marker([p.latitude, p.longitude]).addTo(map).bindPopup(popupContent);
            }
        });

        // Adjust map view based on results
        if (locations.length === 1) {
            // If there's only one result, zoom in on it
            map.setView(locations[0], 10); // Zoom level 10
        } else if (locations.length > 1) {
            // If there are multiple results, fit them all in the view
            const bounds = L.latLngBounds(locations);
            map.fitBounds(bounds, { padding: [50, 50] }); // Add some padding
        } else {
            // If no results, reset to world view
            map.setView([20, 0], 2);
        }
    }

    function renderPagination(pages) {
        const pwrap = el('pagination');
        pwrap.innerHTML = '';
        for (let i = 1; i <= pages; i++) {
            const b = document.createElement('button');
            b.className = 'page-btn' + (i === state.page ? ' active' : '');
            b.textContent = i;
            b.addEventListener('click', () => { state.page = i; render(); });
            pwrap.appendChild(b);
        }
    }

    // View Toggle Logic
    const gridViewBtn = el('grid-view-btn');
    const mapViewBtn = el('map-view-btn');
    const gridView = el('grid-view');
    const mapView = el('map-view');

    gridViewBtn.addEventListener('click', () => {
        gridView.style.display = 'block'; mapView.style.display = 'none';
        gridViewBtn.classList.add('active'); mapViewBtn.classList.remove('active');
    });
    mapViewBtn.addEventListener('click', () => {
        gridView.style.display = 'none'; mapView.style.display = 'block';
        mapViewBtn.classList.add('active'); gridViewBtn.classList.remove('active');
        if(map) map.invalidateSize(); // Important: re-renders the map when its container becomes visible
    });

    // Sorting
    el('sort').addEventListener('change', e => {
        state.sort = e.target.value;
        state.page = 1;
        render();
    });

    const urlParams = new URLSearchParams(window.location.search);
    const theme = urlParams.get('theme') || '';
    const continent = urlParams.get('continent') || '';
    const country = urlParams.get('country') || '';
    const query = urlParams.get('q') || '';

    if (query) {
        state.q = query;
        const universalInput = document.getElementById('universal-search-input');
        if(universalInput) universalInput.value = query;
    }

    const themeSelect = document.getElementById('filter-theme');
    const continentSelect = document.getElementById('filter-continent');
    const countrySelect = document.getElementById('filter-country');

    if (theme && themeSelect) {
        themeSelect.value = theme;
        state.tags.add(theme);
    }
    if (continent && continentSelect) {
        continentSelect.value = continent;
        state.region = continent;
        continentSelect.dispatchEvent(new Event('change')); // This will populate countries
    }
    if (country && countrySelect) {
        // The change event on continent will populate this, we just need to set the value
        countrySelect.value = country;
        state.country = country;
    }
    render(); // Re-render with the new state from URL
});

// Wishlist functionality
document.addEventListener('click', async (event) => {
    const wishlistBtn = event.target.closest('.wishlist-btn');
    if (!wishlistBtn) return;

    event.preventDefault(); // Prevent any default action
    const destinationId = wishlistBtn.dataset.destinationId;

    // Optimistic UI update
    const heartIcon = wishlistBtn.querySelector('svg');

    try {
        const response = await fetch('toggle_wishlist.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ destination_id: destinationId })
        });
        const data = await response.json();

        if (!data.success) {
            alert('Error updating wishlist: ' + data.message);
        } else {
            // Update UI based on server response
            wishlistBtn.classList.toggle('active', data.is_wishlisted);
            heartIcon.style.fill = data.is_wishlisted ? '#ff4d4d' : 'none';
            if (data.is_wishlisted) {
                USER_WISHLIST.add(destinationId);
            } else {
                USER_WISHLIST.delete(destinationId);
            }
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An unexpected error occurred.');
    }
});
</script>
</body>
</html>
