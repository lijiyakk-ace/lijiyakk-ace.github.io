<?php
require_once 'admin_auth_check.php'; // Secure this page
require '../db.php';

// 1. Validate ID and fetch existing data
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
$current_tags = explode(',', $destination['tags']);
$current_attractions = !empty($destination['attraction_details']) ? json_decode($destination['attraction_details'], true) : [];

// Function to handle file uploads (from destinations.php)
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

// 2. Handle form submission for update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_destination'])) {
    $title = $_POST['title'];
    $continent = $_POST['continent'];
    $country = $_POST['country'];
    $tags = isset($_POST['tags']) ? implode(',', $_POST['tags']) : '';
    $short_description = $_POST['short_description'];
    $food_details = $_POST['food_details'];
    $cultural_details = $_POST['cultural_details'];
    $ecosystem_details = $_POST['ecosystem_details'];
    $sensitivity = $_POST['ecosensitivity'];
    $latitude = !empty($_POST['latitude']) ? $_POST['latitude'] : null;
    $longitude = !empty($_POST['longitude']) ? $_POST['longitude'] : null;

    // Handle image uploads - only update if a new file is provided
    $image_url = handle_upload('image_url') ?? $destination['image_url'];
    $food_image_url = handle_upload('food_image_url') ?? $destination['food_image_url'];
    $culture_image_url = handle_upload('culture_image_url') ?? $destination['culture_image_url'];
    $ecosystem_image_url = handle_upload('ecosystem_image_url') ?? $destination['ecosystem_image_url'];
    $slider_image_1 = handle_upload('slider_image_1') ?? $destination['slider_image_1'];
    $slider_image_2 = handle_upload('slider_image_2') ?? $destination['slider_image_2'];
    $slider_image_3 = handle_upload('slider_image_3') ?? $destination['slider_image_3'];

    // Handle attractions (images and names)
    $attractions = [];
    if (isset($_POST['attraction_names']) && is_array($_POST['attraction_names'])) {
        foreach ($_POST['attraction_names'] as $index => $name) {
            $new_image_path = null;
            if (isset($_FILES['attraction_images']['name'][$index]) && $_FILES['attraction_images']['error'][$index] === UPLOAD_ERR_OK) {
                // A new file is being uploaded for this attraction
                $upload_dir = 'uploads/destinations/';
                if (!is_dir('../' . $upload_dir)) { mkdir('../' . $upload_dir, 0777, true); }
                $file_extension = pathinfo($_FILES['attraction_images']['name'][$index], PATHINFO_EXTENSION);
                $new_filename = uniqid('attraction_', true) . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;
                if (move_uploaded_file($_FILES['attraction_images']['tmp_name'][$index], '../' . $upload_path)) {
                    $new_image_path = $upload_path;
                }
            }
            $image_path = $new_image_path ?? $_POST['existing_attraction_images'][$index] ?? null;
            if (!empty($name) && !empty($image_path)) {
                $attractions[] = ['name' => $name, 'image' => $image_path];
            }
        }
    }
    $attraction_details_json = json_encode($attractions);

    $update_stmt = $conn->prepare("UPDATE destinations SET 
        title = ?, continent = ?, country = ?, image_url = ?, tags = ?, short_description = ?,
        food_details = ?, food_image_url = ?, cultural_details = ?, culture_image_url = ?,
        ecosystem_details = ?, ecosystem_image_url = ?, ecosensitivity = ?, attraction_details = ?,
        slider_image_1 = ?, slider_image_2 = ?, slider_image_3 = ?,
        latitude = ?, longitude = ?
        WHERE id = ?");
    $update_stmt->bind_param("sssssssssssssssssddi",
        $title, $continent, $country, $image_url, $tags, $short_description,
        $food_details, $food_image_url, $cultural_details, $culture_image_url, $ecosystem_details, $ecosystem_image_url, $sensitivity, $attraction_details_json,
        $slider_image_1, $slider_image_2, $slider_image_3, $latitude, $longitude,
        $id
    );
    $update_stmt->execute();
    $update_stmt->close();
    header("Location: destinations.php"); // Redirect back to the list
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Edit Destination</title>
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
        .sidebar h3 { font-size: 22px; text-align: center; margin: 0 0 30px 0; color: #fff; font-weight: bold; }
        .sidebar ul { list-style: none; padding: 0; margin: 0; flex-grow: 1; }
        .sidebar li { margin-bottom: 8px; }
        .sidebar a { display: block; padding: 12px 20px; color: var(--muted); text-decoration: none; border-radius: 8px; transition: background-color 0.3s ease, color 0.3s ease; font-weight: 500; }
        .sidebar a:hover { background-color: rgba(29, 155, 240, 0.1); color: #fff; }
        .sidebar a.active { background-color: var(--accent); color: #0b1220; font-weight: 700; box-shadow: 0 2px 10px rgba(29, 155, 240, 0.4); }

        /* Sidebar Open State */
        body.sidebar-open .sidebar { transform: translateX(0); }
        body.sidebar-open { padding-left: 260px; }

        /* Admin Header */
        .admin-header { display: flex; align-items: center; padding: 15px 30px; background: var(--card); border-bottom: 1px solid rgba(255,255,255,0.07); position: sticky; top: 0; z-index: 100; }
        .burger-icon { cursor: pointer; margin-right: 20px; }
        .burger-icon svg { width: 24px; height: 24px; stroke: var(--muted); transition: stroke 0.3s; }
        .burger-icon:hover svg { stroke: #fff; }

        .page-wrapper { min-height: 100vh; transition: padding-left 0.3s ease; }

        /* Main Content Area */
        .main-content { padding: 40px; }
        h2 { font-size: 28px; margin-top: 0; margin-bottom: 20px; border-left: 4px solid var(--accent); padding-left: 15px; color: #fff; }
        .form-container { background: var(--card); padding: 30px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.07); margin-bottom: 40px; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .form-group { display: flex; flex-direction: column; }
        .form-group.full-width { grid-column: 1 / -1; }
        label { font-weight: 600; margin-bottom: 8px; font-size: 14px; color: var(--muted); }
        input[type="text"], select, textarea { width: 100%; padding: 12px; background: var(--glass); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #e6eef8; font-family: 'Montserrat', sans-serif; font-size: 16px; box-sizing: border-box; transition: border-color 0.3s, box-shadow 0.3s; }
        input:focus, select:focus, textarea:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 2px rgba(29, 155, 240, 0.3); }
        textarea { min-height: 120px; resize: vertical; }
        input[type="file"] { padding: 8px; }
        .current-image { font-size: 12px; color: var(--muted); margin-top: 5px; }
        .current-image img { max-width: 100px; max-height: 60px; border-radius: 4px; margin-right: 10px; vertical-align: middle; }
        button[type="submit"] { grid-column: 1 / -1; padding: 12px 25px; background: var(--accent); color: #041022; border: none; border-radius: 8px; font-weight: 700; font-size: 16px; cursor: pointer; transition: background 0.3s; justify-self: start; }
        button[type="submit"]:hover { background: #4fbfff; }
        .attraction-group { display: grid; grid-template-columns: 1fr auto; gap: 10px; align-items: center; margin-bottom: 10px; }
        .attraction-group input { width: 100%; }
        #attractions-container { grid-column: 1 / -1; }
        #add-attraction-btn { justify-self: start; padding: 8px 15px; background: #334155; color: #e2e8f0; border: none; border-radius: 6px; cursor: pointer; transition: background 0.3s; font-weight: 600; }
        .remove-attraction-btn { background: #ef4444; color: white; border: none; border-radius: 50%; width: 28px; height: 28px; cursor: pointer; font-weight: bold; }
        .key-detail-group { display: grid; grid-template-columns: 1fr 2fr auto; gap: 10px; align-items: center; margin-bottom: 10px; }
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
            <li><a href="feedback.php">View Feedback</a></li>
            <li><a href="notifications.php">Manage Notifications</a></li>
            <li><a href="carousel.php">Manage Carousel</a></li>
            <li><a href="settings.php">Settings</a></li>
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
            <h1>Edit Destination</h1>
        </header>

        <main class="main-content">
            <div class="form-container">
            <h2>Editing '<?php echo htmlspecialchars($destination['title']); ?>'</h2>
            <form method="post" enctype="multipart/form-data" class="form-grid">
                <div class="form-group"><label for="title">Destination Title</label><input type="text" id="title" name="title" value="<?php echo htmlspecialchars($destination['title']); ?>" required></div>
                <div class="form-group"><label for="country">Country</label><input type="text" id="country" name="country" value="<?php echo htmlspecialchars($destination['country']); ?>" required></div>
                <div class="form-group">
                    <label for="continent">Continent</label>
                    <select id="continent" name="continent" required>
                        <?php $continents = ["Africa", "Antarctica", "Asia", "Australia", "Europe", "North America", "South America"]; ?>
                        <?php foreach ($continents as $c): ?>
                            <option value="<?php echo $c; ?>" <?php if ($destination['continent'] === $c) echo 'selected'; ?>><?php echo $c; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label for="latitude">Latitude</label><input type="text" id="latitude" name="latitude" value="<?php echo htmlspecialchars($destination['latitude']); ?>" placeholder="e.g., 48.8566"></div>
                <div class="form-group"><label for="longitude">Longitude</label><input type="text" id="longitude" name="longitude" value="<?php echo htmlspecialchars($destination['longitude']); ?>" placeholder="e.g., 2.3522"></div>

                <div class="form-group">
                    <label for="tags">Themes (hold Ctrl/Cmd to select multiple)</label>
                    <select id="tags" name="tags[]" multiple size="8">
                        <?php $themes = ["Adventure", "Beach", "City Break", "Countryside", "Cultural", "Historical", "Luxury", "Mountain", "Nature", "Tropical", "Wildlife"]; ?>
                        <?php foreach ($themes as $theme): ?>
                            <option value="<?php echo $theme; ?>" <?php if (in_array($theme, $current_tags)) echo 'selected'; ?>><?php echo $theme; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label for="ecosensitivity">Ecosensitivity</label><input type="text" id="ecosensitivity" name="ecosensitivity" value="<?php echo htmlspecialchars($destination['ecosensitivity']); ?>" placeholder="High/Medium/Low"></div>
                <div class="form-group full-width"><label for="short_description">Short Description</label><textarea id="short_description" name="short_description"><?php echo htmlspecialchars($destination['short_description']); ?></textarea></div>
                <div class="form-group full-width"><label for="food_details">Food Details</label><textarea id="food_details" name="food_details"><?php echo htmlspecialchars($destination['food_details']); ?></textarea></div>
                <div class="form-group full-width"><label for="cultural_details">Cultural Details</label><textarea id="cultural_details" name="cultural_details"><?php echo htmlspecialchars($destination['cultural_details']); ?></textarea></div>
                <div class="form-group full-width"><label for="ecosystem_details">Ecosystem Details</label><textarea id="ecosystem_details" name="ecosystem_details"><?php echo htmlspecialchars($destination['ecosystem_details']); ?></textarea></div>
                
                <div class="form-group">
                    <label for="image_url">Main Image (leave blank to keep current)</label>
                    <input type="file" id="image_url" name="image_url">
                    <?php if ($destination['image_url']): ?>
                        <div class="current-image">Current: <img src="../<?php echo htmlspecialchars($destination['image_url']); ?>" alt="Main Image"> <?php echo basename($destination['image_url']); ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="food_image_url">Food Image (leave blank to keep current)</label>
                    <input type="file" id="food_image_url" name="food_image_url">
                     <?php if ($destination['food_image_url']): ?>
                        <div class="current-image">Current: <img src="../<?php echo htmlspecialchars($destination['food_image_url']); ?>" alt="Food Image"> <?php echo basename($destination['food_image_url']); ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="culture_image_url">Culture Image (leave blank to keep current)</label>
                    <input type="file" id="culture_image_url" name="culture_image_url">
                     <?php if (!empty($destination['culture_image_url'])): ?>
                        <div class="current-image">Current: <img src="../<?php echo htmlspecialchars($destination['culture_image_url']); ?>" alt="Culture Image"> <?php echo basename($destination['culture_image_url']); ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="ecosystem_image_url">Ecosystem Image (leave blank to keep current)</label>
                    <input type="file" id="ecosystem_image_url" name="ecosystem_image_url">
                     <?php if ($destination['ecosystem_image_url']): ?>
                        <div class="current-image">Current: <img src="../<?php echo htmlspecialchars($destination['ecosystem_image_url']); ?>" alt="Ecosystem Image"> <?php echo basename($destination['ecosystem_image_url']); ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="slider_image_1">Slider Image 1 (leave blank to keep current)</label>
                    <input type="file" id="slider_image_1" name="slider_image_1">
                    <?php if (!empty($destination['slider_image_1'])): ?>
                        <div class="current-image">Current: <img src="../<?php echo htmlspecialchars($destination['slider_image_1']); ?>" alt="Slider Image 1"> <?php echo basename($destination['slider_image_1']); ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="slider_image_2">Slider Image 2 (leave blank to keep current)</label>
                    <input type="file" id="slider_image_2" name="slider_image_2">
                    <?php if (!empty($destination['slider_image_2'])): ?>
                        <div class="current-image">Current: <img src="../<?php echo htmlspecialchars($destination['slider_image_2']); ?>" alt="Slider Image 2"> <?php echo basename($destination['slider_image_2']); ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="slider_image_3">Slider Image 3 (leave blank to keep current)</label>
                    <input type="file" id="slider_image_3" name="slider_image_3">
                    <?php if (!empty($destination['slider_image_3'])): ?>
                        <div class="current-image">Current: <img src="../<?php echo htmlspecialchars($destination['slider_image_3']); ?>" alt="Slider Image 3"> <?php echo basename($destination['slider_image_3']); ?></div>
                    <?php endif; ?>
                </div>

                <div id="attractions-container" class="form-group full-width">
                    <label>Top Attractions</label>
                    <?php foreach ($current_attractions as $index => $attraction): ?>
                        <div class="attraction-group">
                            <input type="text" name="attraction_names[]" placeholder="Attraction Name" value="<?php echo htmlspecialchars($attraction['name']); ?>" required>
                            <div>
                                <input type="file" name="attraction_images[]" accept="image/*">
                                <input type="hidden" name="existing_attraction_images[]" value="<?php echo htmlspecialchars($attraction['image']); ?>">
                                <div class="current-image"><img src="../<?php echo htmlspecialchars($attraction['image']); ?>" alt="Attraction"> <?php echo basename($attraction['image']); ?></div>
                            </div>
                            <button type="button" class="remove-attraction-btn" onclick="this.parentElement.remove()">X</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" id="add-attraction-btn" class="full-width">Add Attraction</button>
                
                <button type="submit" name="update_destination">Update Destination</button>
            </form>
        </div>
        </main>
    </div>

    <script>
        document.getElementById('burger-icon').addEventListener('click', function() {
            document.body.classList.toggle('sidebar-open');
        });

        document.getElementById('add-attraction-btn').addEventListener('click', function() {
            const container = document.getElementById('attractions-container');
            const newIndex = container.querySelectorAll('.attraction-group').length;
            const newAttraction = document.createElement('div');
            newAttraction.className = 'attraction-group';
            newAttraction.innerHTML = `
                <input type="text" name="attraction_names[]" placeholder="Attraction Name" required>
                <div>
                    <input type="file" name="attraction_images[]" accept="image/*" required>
                    <input type="hidden" name="existing_attraction_images[]" value="">
                </div>
                <button type="button" class="remove-attraction-btn" onclick="this.parentElement.remove()">X</button>
            `;
            container.appendChild(newAttraction);
        });
    </script>
</body>
</html>