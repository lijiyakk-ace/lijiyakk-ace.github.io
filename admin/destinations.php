<?php
require_once 'admin_auth_check.php'; // Secure this page
require '../db.php'; // adjust path

// Function to handle file uploads
function handle_upload($file_key, $upload_dir = 'uploads/destinations/') {
    if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK) {
        if (!is_dir('../' . $upload_dir)) {
            mkdir('../' . $upload_dir, 0777, true);
        }
        $file = $_FILES[$file_key];
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_filename = uniqid('', true) . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;

        if (move_uploaded_file($file['tmp_name'], '../' . $upload_path)) {
            return $upload_path;
        }
    }
    return null;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_destination'])) {
    $title = $_POST['title'];
    $continent = $_POST['continent'];
    $country = $_POST['country'];
    $tags = isset($_POST['tags']) ? implode(',', $_POST['tags']) : ''; // Handle array from multi-select
    $short_description = $_POST['short_description'];

    // Handle image uploads
    $image_url = handle_upload('image_url');
    $food_image_url = handle_upload('food_image_url');
    $culture_image_url = handle_upload('culture_image_url');
    $ecosystem_image_url = handle_upload('ecosystem_image_url');
    $slider_image_1 = handle_upload('slider_image_1');
    $slider_image_2 = handle_upload('slider_image_2');
    $slider_image_3 = handle_upload('slider_image_3');

    // Handle attractions (images and names)
    $attractions = [];
    if (isset($_FILES['attraction_images']) && isset($_POST['attraction_names'])) {
        $file_count = count($_FILES['attraction_images']['name']);
        for ($i = 0; $i < $file_count; $i++) {
            if ($_FILES['attraction_images']['error'][$i] === UPLOAD_ERR_OK && !empty($_POST['attraction_names'][$i])) {
                $upload_dir = 'uploads/destinations/';
                if (!is_dir('../' . $upload_dir)) { mkdir('../' . $upload_dir, 0777, true); }
                $file_extension = pathinfo($_FILES['attraction_images']['name'][$i], PATHINFO_EXTENSION);
                $new_filename = uniqid('attraction_', true) . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;
                if (move_uploaded_file($_FILES['attraction_images']['tmp_name'][$i], '../' . $upload_path)) {
                    $attractions[] = ['name' => $_POST['attraction_names'][$i], 'image' => $upload_path];
                }
            }
        }
    }
    $attraction_details_json = json_encode($attractions);

    $food_details = $_POST['food_details'];
    $cultural_details = $_POST['cultural_details'];
    $ecosystem_details = $_POST['ecosystem_details'];
    $sensitivity = $_POST['ecosensitivity'];
    // Add latitude and longitude from the form
    $latitude = !empty($_POST['latitude']) ? $_POST['latitude'] : null;
    $longitude = !empty($_POST['longitude']) ? $_POST['longitude'] : null;

    $stmt = $conn->prepare("INSERT INTO destinations
        (title, continent, country, image_url, tags, short_description, food_details, food_image_url, cultural_details, culture_image_url, ecosystem_details, ecosystem_image_url, ecosensitivity, attraction_details, slider_image_1, slider_image_2, slider_image_3, latitude, longitude)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssssssssssssdd", 
        $title, $continent, $country, $image_url, $tags, $short_description, 
        $food_details, $food_image_url, $cultural_details, $culture_image_url,
        $ecosystem_details, $ecosystem_image_url, $sensitivity, $attraction_details_json,
        $slider_image_1, $slider_image_2, $slider_image_3, $latitude, $longitude
    );
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']); // Redirect to avoid form resubmission
    exit;
}

// Fetch destinations
$result = $conn->query("SELECT * FROM destinations ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Destinations</title>
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
            transition: padding-left 0.3s ease;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: 260px;
            background: var(--card);
            padding: 30px 20px;
            border-right: 1px solid rgba(255,255,255,0.07);
            display: flex;
            flex-direction: column;
            z-index: 200;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            box-sizing: border-box;
        }
        .sidebar h3 {
            font-size: 22px;
            text-align: center;
            margin: 0 0 30px 0;
            color: #fff;
            font-weight: bold;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
            flex-grow: 1;
        }
        .sidebar li {
            margin-bottom: 8px;
        }
        .sidebar a {
            display: block;
            padding: 12px 20px;
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
            color: #0b1220;
            font-weight: 700;
            box-shadow: 0 2px 10px rgba(29, 155, 240, 0.4);
        }

        /* Sidebar Open State */
        body.sidebar-open .sidebar {
            transform: translateX(0);
        }
        body.sidebar-open {
            padding-left: 260px;
        }

        /* Admin Header */
        .admin-header {
            display: flex;
            align-items: center;
            padding: 15px 30px;
            background: var(--card);
            border-bottom: 1px solid rgba(255,255,255,0.07);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .burger-icon {
            cursor: pointer;
            margin-right: 20px;
        }
        .burger-icon svg {
            width: 24px;
            height: 24px;
            stroke: var(--muted);
            transition: stroke 0.3s;
        }
        .burger-icon:hover svg {
            stroke: #fff;
        }

        .page-wrapper {
            min-height: 100vh;
            transition: padding-left 0.3s ease;
        }

        /* Admin Header */
        .admin-header {
            display: flex;
            align-items: center;
            padding: 15px 30px;
            background: var(--card);
            border-bottom: 1px solid rgba(255,255,255,0.07);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .burger-icon {
            cursor: pointer;
            margin-right: 20px;
        }
        .burger-icon svg {
            width: 24px;
            height: 24px;
            stroke: var(--muted);
        }
        .burger-icon:hover svg {
            stroke: #fff;
        }

        /* Main Content Area */
        .main-content {
            padding: 40px;
        }
        h2 {
            font-size: 28px;
            margin-top: 0;
            margin-bottom: 20px;
            border-left: 4px solid var(--accent);
            padding-left: 15px;
            color: #fff;
        }
        .form-container, .table-container {
            background: var(--card);
            padding: 30px;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.07);
            margin-bottom: 40px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        label {
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
            color: var(--muted);
        }
        input[type="text"], select, textarea {
            width: 100%;
            padding: 12px;
            background: var(--glass);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            color: #e6eef8;
            font-family: 'Montserrat', sans-serif;
            font-size: 16px;
            box-sizing: border-box;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 2px rgba(29, 155, 240, 0.3);
        }
        textarea {
            min-height: 120px;
            resize: vertical;
        }
        input[type="file"] {
            padding: 8px;
        }
        button[type="submit"] {
            grid-column: 1 / -1;
            padding: 12px 25px;
            background: var(--accent);
            color: #041022;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
            justify-self: start;
        }
        button[type="submit"]:hover {
            background: #4fbfff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            color: #e6eef8;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            font-weight: 700;
            color: #fff;
        }
        tr:hover {
            background-color: var(--glass);
        }

        /* Confirmation Bubble Styles */
        .confirm-bubble-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 36, 0.5);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        .confirm-bubble-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        .confirm-bubble-content {
            background: var(--card);
            padding: 24px 32px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 30px rgba(0,0,0,0.3);
            text-align: center;
            max-width: 400px;
            transform: scale(0.95);
            transition: transform 0.3s ease;
        }
        .confirm-bubble-overlay.show .confirm-bubble-content { transform: scale(1); }
        .confirm-bubble-content h4 { margin: 0 0 10px 0; font-size: 18px; color: #fff; }
        .confirm-bubble-content p { margin: 0 0 20px 0; color: var(--muted); font-size: 14px; }
        .confirm-bubble-actions { display: flex; gap: 12px; justify-content: center; }
        .confirm-bubble-actions button { padding: 8px 20px; border-radius: 8px; border: none; font-weight: 600; cursor: pointer; font-family: inherit; transition: background-color 0.3s; }
        .confirm-bubble-actions .btn-cancel { background: var(--glass); color: var(--muted); border: 1px solid rgba(255,255,255,0.1); }
        .confirm-bubble-actions .btn-cancel:hover { background: rgba(255,255,255,0.1); color: #fff; }
        .confirm-bubble-actions .btn-confirm { background: #d9534f; color: #fff; }
        .confirm-bubble-actions .btn-confirm:hover { background: #c9302c; }

        /* Attraction form styles */
        .attraction-group { display: grid; grid-template-columns: 1fr 1fr auto; gap: 10px; align-items: center; margin-bottom: 10px; }
        .attraction-group input { width: 100%; }
        #attractions-container { grid-column: 1 / -1; }
        #add-attraction-btn { grid-column: 1 / -1; justify-self: start; padding: 8px 15px; background: #334155; color: #e2e8f0; border: none; border-radius: 6px; cursor: pointer; transition: background 0.3s; font-weight: 600; margin-top: 10px; }
        .remove-attraction-btn { background: #ef4444; color: white; border: none; border-radius: 50%; width: 28px; height: 28px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h3>Travel Tales Admin</h3>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="destinations.php" class="active">Manage Destinations</a></li>
            <li><a href="articles.php">Manage Articles</a></li>
            <li><a href="blog.php">Manage Blogs</a></li>
            <li><a href="manage_users.php">Manage Users</a></li>
            <li><a href="quiz.php">Manage Quiz</a></li>
            <li><a href="posts.php">Manage Forum</a></li>
            <li><a href="notifications.php">Manage Notifications</a></li>
            <li><a href="feedback.php">View Feedback</a></li>
            <li><a href="carousel.php">Manage Carousel</a></li>
            <li><a href="settings.php">Settings</a></li>
            <li><a href="logout.php" style="color: #f0ad4e;">Logout</a></li>
        </ul>
        <div>
            <a href="../index.php" style="text-align: center; font-size: 14px;">&larr; Back to Main Site</a>
        </div>
    </div>

    <div class="page-wrapper">
        <header class="admin-header">
            <div class="burger-icon" id="burger-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </div>
            <h1>Manage Destinations</h1>
        </header>

        <main class="main-content">
            <div class="form-container">
            <h2>Add Destination</h2>
            <form method="post" enctype="multipart/form-data" class="form-grid">
                <div class="form-group"><label for="title">Destination Title</label><input type="text" id="title" name="title" required></div>
                <div class="form-group"><label for="country">Country</label><input type="text" id="country" name="country" required></div>
                <div class="form-group"><label for="continent">Continent</label><select id="continent" name="continent" required><option value="">Select a Continent</option><option value="Africa">Africa</option><option value="Antarctica">Antarctica</option><option value="Asia">Asia</option><option value="Australia">Australia</option><option value="Europe">Europe</option><option value="North America">North America</option><option value="South America">South America</option></select></div>
                <div class="form-group"><label for="tags">Themes (hold Ctrl/Cmd to select multiple)</label><select id="tags" name="tags[]" multiple size="8"><option value="Adventure">Adventure</option><option value="Beach">Beach</option><option value="City Break">City Break</option><option value="Countryside">Countryside</option><option value="Cultural">Cultural</option><option value="Historical">Historical</option><option value="Luxury">Luxury</option><option value="Mountain">Mountain</option><option value="Nature">Nature</option><option value="Tropical">Tropical</option><option value="Wildlife">Wildlife</option></select></div>
                <div class="form-group"><label for="ecosensitivity">Ecosensitivity</label><input type="text" id="ecosensitivity" name="ecosensitivity" placeholder="High/Medium/Low"></div>
                <div class="form-group full-width"><label for="short_description">Short Description</label><textarea id="short_description" name="short_description"></textarea></div>
                <div class="form-group full-width"><label for="food_details">Food Details</label><textarea id="food_details" name="food_details"></textarea></div>
                <div class="form-group full-width"><label for="cultural_details">Cultural Details</label><textarea id="cultural_details" name="cultural_details"></textarea></div>
                <div class="form-group full-width"><label for="ecosystem_details">Ecosystem Details</label><textarea id="ecosystem_details" name="ecosystem_details"></textarea></div>
                <div class="form-group"><label for="image_url">Main Image</label><input type="file" id="image_url" name="image_url"></div>
                <div class="form-group"><label for="food_image_url">Food Image</label><input type="file" id="food_image_url" name="food_image_url"></div>
                <div class="form-group"><label for="culture_image_url">Culture Image</label><input type="file" id="culture_image_url" name="culture_image_url"></div>
                <div class="form-group"><label for="ecosystem_image_url">Ecosystem Image</label><input type="file" id="ecosystem_image_url" name="ecosystem_image_url"></div>
                
                <div class="form-group"><label for="slider_image_1">Slider Image 1</label><input type="file" id="slider_image_1" name="slider_image_1"></div>
                <div class="form-group"><label for="slider_image_2">Slider Image 2</label><input type="file" id="slider_image_2" name="slider_image_2"></div>
                <div class="form-group"><label for="slider_image_3">Slider Image 3</label><input type="file" id="slider_image_3" name="slider_image_3"></div>

                <div class="form-group"><label for="latitude">Latitude</label><input type="text" id="latitude" name="latitude" placeholder="e.g., 48.8566"></div>
                <div class="form-group"><label for="longitude">Longitude</label><input type="text" id="longitude" name="longitude" placeholder="e.g., 2.3522"></div>
                
                <div id="attractions-container" class="form-group full-width"><label>Top Attractions</label></div>
                <button type="button" id="add-attraction-btn">Add Attraction</button>

                <button type="submit" name="add_destination">Add Destination</button>
            </form>
        </div>

            <div class="table-container">
            <h2>Existing Destinations</h2>
            <table>
                <thead>
                    <tr><th>ID</th><th>Title</th><th>Continent</th><th>Country</th><th>Tags</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['continent']); ?></td>
                        <td><?php echo htmlspecialchars($row['country']); ?></td>
                        <td><?php echo htmlspecialchars($row['tags']); ?></td>
                        <td style="display: flex; gap: 8px;">
                            <a href="edit_destination.php?id=<?php echo $row['id']; ?>" style="color: var(--accent); text-decoration: none; font-weight: 600;">Edit</a>
                            <a href="delete_destination.php?id=<?php echo $row['id']; ?>" class="delete-btn" data-id="<?php echo $row['id']; ?>" data-title="<?php echo htmlspecialchars($row['title']); ?>" style="color: #d9534f; text-decoration: none; font-weight: 600;">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        </main>
    </div>

    <!-- Confirmation Bubble for Deletion -->
    <div id="confirm-bubble" class="confirm-bubble-overlay">
        <div class="confirm-bubble-content">
            <h4>Delete Destination</h4>
            <p id="confirm-message">Are you sure you want to permanently delete this destination? This action cannot be undone.</p>
            <div class="confirm-bubble-actions">
                <button id="cancel-delete-btn" class="btn-cancel">Cancel</button>
                <button id="confirm-delete-btn" class="btn-confirm">Confirm Delete</button>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('burger-icon').addEventListener('click', function() {
            document.body.classList.toggle('sidebar-open');
        });

        document.getElementById('add-attraction-btn').addEventListener('click', function() {
            const container = document.getElementById('attractions-container');
            const newAttraction = document.createElement('div');
            newAttraction.className = 'attraction-group';
            newAttraction.innerHTML = `
                <input type="text" name="attraction_names[]" placeholder="Attraction Name" required>
                <input type="file" name="attraction_images[]" accept="image/*" required>
                <button type="button" class="remove-attraction-btn" onclick="this.parentElement.remove()">X</button>
            `;
            container.appendChild(newAttraction);
        });

        // Add one attraction field by default
        document.getElementById('add-attraction-btn').click();

        // --- Delete Confirmation Modal Logic ---
        const confirmBubble = document.getElementById('confirm-bubble');
        const confirmMessage = document.getElementById('confirm-message');
        const cancelBtn = document.getElementById('cancel-delete-btn');
        const confirmBtn = document.getElementById('confirm-delete-btn');
        const deleteButtons = document.querySelectorAll('.delete-btn');
        let deleteUrl = '';

        deleteButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault(); // Prevent the link from navigating immediately
                const destId = button.dataset.id;
                const destTitle = button.dataset.title;
                
                confirmMessage.innerHTML = `Are you sure you want to permanently delete <strong>${destTitle}</strong>? This will also delete all associated images and cannot be undone.`;
                deleteUrl = `delete_destination.php?id=${destId}`;
                
                confirmBubble.classList.add('show');
            });
        });

        cancelBtn.addEventListener('click', () => confirmBubble.classList.remove('show'));
        confirmBtn.addEventListener('click', () => { if (deleteUrl) window.location.href = deleteUrl; });
    </script>
</body>
</html>
