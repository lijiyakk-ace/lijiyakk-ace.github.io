<?php
require_once 'admin_auth_check.php'; // Secure this page
require '../db.php'; // Use the central database connection file

// --- Handle Search and Fetch Users ---
$search_query = $_GET['search'] ?? '';
$sql = "SELECT id, username, email, created_at, status FROM users";

if (!empty($search_query)) {
    // Use a WHERE clause to filter results
    $sql .= " WHERE username LIKE ? OR email LIKE ?";
}

$sql .= " ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);

if (!empty($search_query)) {
    $search_term = "%" . $search_query . "%";
    $stmt->bind_param("ss", $search_term, $search_term);
}
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Users</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root{
          --bg:#0f1724;
          --card:#0b1220;
          --muted:#9aa4b2;
          --accent:#1d9bf0;
          --glass: rgba(255,255,255,0.03);
        }
        body {
            margin: 0;
            font-family: 'Montserrat', sans-serif;
            background:linear-gradient(180deg,var(--bg) 0%, #071027 60%);
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

        /* Main Content Area */
        .main-content {
            padding: 40px;
        }
        h1, h2 {
            font-size: 28px;
            margin-top: 0;
            margin-bottom: 20px;
            border-left: 4px solid var(--accent);
            padding-left: 15px;
            color: #fff;
        }
        .table-container {
            overflow-x: auto; /* For smaller screens */
        }

        /* Search Form Styles */
        .search-container {
            background: var(--card);
            padding: 20px 30px;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.07);
            margin-bottom: 40px;
        }
        .search-form {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        .search-form input[type="text"] {
            flex-grow: 1;
            padding: 12px;
            background: var(--glass);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            color: #e6eef8;
            font-family: 'Montserrat', sans-serif;
            font-size: 16px;
        }
        .search-form input[type="text"]:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 2px rgba(29, 155, 240, 0.3);
        }
        .search-form .btn {
            padding: 12px 20px;
        }
        .table-container {
            background: var(--card);
            padding: 30px;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.07);
            margin-bottom: 40px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            color: #e6eef8;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--glass);
        }
        th {
            background-color: rgba(255,255,255,0.05);
            font-weight: 700;
            color: #fff;
        }
        tr:hover {
            background-color: var(--glass);
        }
        .no-users {
            color: #777;
            text-align: center;
        }

        /* Action Buttons */
        .actions {
            display: flex;
            gap: 8px;
        }
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            text-decoration: none;
            color: #fff;
            display: inline-block;
        }
        .btn-suspend { background-color: #f0ad4e; }
        .btn-activate { background-color: #5cb85c; }
        .btn-delete { background-color: #d9534f; }
        .btn:hover { opacity: 0.85; }

        /* Status Badge */
        .status-badge {
            padding: 4px 8px;
            border-radius: 99px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .status-active { background-color: rgba(92, 184, 92, 0.2); color: #5cb85c; }
        .status-suspended { background-color: rgba(240, 173, 78, 0.2); color: #f0ad4e; }

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
        .confirm-bubble-overlay.show .confirm-bubble-content {
            transform: scale(1);
        }
        .confirm-bubble-content h4 { margin: 0 0 10px 0; font-size: 18px; color: #fff; }
        .confirm-bubble-content p { margin: 0 0 20px 0; color: var(--muted); font-size: 14px; }
        .confirm-bubble-actions { display: flex; gap: 12px; justify-content: center; }
        .confirm-bubble-actions button { padding: 8px 20px; border-radius: 8px; border: none; font-weight: 600; cursor: pointer; font-family: inherit; transition: background-color 0.3s; }
        .confirm-bubble-actions .btn-cancel { background: var(--glass); color: var(--muted); border: 1px solid rgba(255,255,255,0.1); }
        .confirm-bubble-actions .btn-cancel:hover { background: rgba(255,255,255,0.1); color: #fff; }
        .confirm-bubble-actions .btn-confirm { background: #d9534f; color: #fff; }
        .confirm-bubble-actions .btn-confirm:hover { background: #c9302c; }

        /* Specific styles for action confirm button */
        .confirm-bubble-actions .btn-confirm-action.suspend { background-color: #f0ad4e; }
        .confirm-bubble-actions .btn-confirm-action.activate { background-color: #5cb85c; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h3>Travel Tales Admin</h3>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="destinations.php">Manage Destinations</a></li>
            <li><a href="articles.php">Manage Articles</a></li>
            <li><a href="blog.php">Manage Blogs</a></li>
            <li><a href="manage_users.php" class="active">Manage Users</a></li>
            <li><a href="quiz.php">Manage Quiz</a></li>
            <li><a href="posts.php">Manage Forum</a></li>
            <li><a href="notifications.php">Manage Notifications</a></li>
            <li><a href="carousel.php">Manage Carousel</a></li>
            <li><a href="logout.php" style="color: #f0ad4e;">Logout</a></li>
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
            <h1>Manage Users</h1>
        </header>

        <main class="main-content">
            <div class="search-container">
                <form action="manage_users.php" method="GET" class="search-form">
                    <input type="text" name="search" placeholder="Search by username or email..." value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit" class="btn btn-activate" style="background-color: var(--accent); color: #041022;">Search</button>
                    <?php if (!empty($search_query)): ?>
                        <a href="manage_users.php" class="btn btn-suspend">Clear</a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="table-container">
                <h2>Registered Users</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Registration Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && $result->num_rows > 0) {
                            // Output data of each row
                            while($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row["id"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["username"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["email"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["created_at"]) . "</td>";
                                
                                // Status Badge
                                $status_class = $row["status"] === 'active' ? 'status-active' : 'status-suspended';
                                echo "<td><span class='status-badge " . $status_class . "'>" . htmlspecialchars($row["status"]) . "</span></td>";

                                // Action Buttons
                                echo "<td class='actions'>";
                                if ($row["status"] === 'active') {
                                    echo "<button class='btn btn-suspend action-btn' data-id='" . $row["id"] . "' data-username='" . htmlspecialchars($row["username"]) . "' data-action='suspend'>Suspend</button>";
                                } else {
                                    echo "<button class='btn btn-activate action-btn' data-id='" . $row["id"] . "' data-username='" . htmlspecialchars($row["username"]) . "' data-action='activate'>Activate</button>";
                                }
                                echo "<button class='btn btn-delete delete-btn' data-id='" . $row["id"] . "' data-username='" . htmlspecialchars($row["username"]) . "'>Delete</button>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' class='no-users'>No users found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Confirmation Bubble for User Deletion -->
    <div id="confirm-bubble" class="confirm-bubble-overlay">
        <div class="confirm-bubble-content">
            <h4>Delete User</h4>
            <p id="confirm-message">Are you sure you want to permanently delete this user? This action cannot be undone.</p>
            <div class="confirm-bubble-actions">
                <button id="cancel-delete-btn" class="btn-cancel">Cancel</button>
                <button id="confirm-delete-btn" class="btn-confirm">Confirm Delete</button>
            </div>
        </div>
    </div>

    <!-- Confirmation Bubble for User Actions (Suspend/Activate) -->
    <div id="confirm-action-bubble" class="confirm-bubble-overlay">
        <div class="confirm-bubble-content">
            <h4 id="action-confirm-title">Confirm Action</h4>
            <p id="action-confirm-message">Are you sure you want to perform this action?</p>
            <div class="confirm-bubble-actions">
                <button id="cancel-action-btn" class="btn-cancel">Cancel</button>
                <button id="confirm-action-btn" class="btn-confirm-action">Confirm</button>
            </div>
        </div>
    </div>

    <?php
    // --- Close Connection ---
    $stmt->close();
    $conn->close();
    ?>

    <script>
        document.getElementById('burger-icon').addEventListener('click', function() {
            document.body.classList.toggle('sidebar-open');
        });

        // --- Delete Confirmation Modal Logic ---
        const confirmBubble = document.getElementById('confirm-bubble');
        const confirmMessage = document.getElementById('confirm-message');
        const cancelBtn = document.getElementById('cancel-delete-btn');
        const confirmBtn = document.getElementById('confirm-delete-btn');
        const deleteButtons = document.querySelectorAll('.delete-btn');
        let deleteUrl = '';

        deleteButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault(); // Prevent default button action
                const userId = button.dataset.id;
                const username = button.dataset.username;
                
                // Set the message and the URL for the confirm button
                confirmMessage.innerHTML = `Are you sure you want to permanently delete user <strong>${username}</strong> and all their content? This cannot be undone.`;
                deleteUrl = `delete_user.php?id=${userId}`;
                
                // Show the modal
                confirmBubble.classList.add('show');
            });
        });

        // Hide modal on cancel
        cancelBtn.addEventListener('click', () => {
            confirmBubble.classList.remove('show');
        });

        // Proceed with deletion on confirm
        confirmBtn.addEventListener('click', () => {
            if (deleteUrl) {
                window.location.href = deleteUrl;
            }
        });

        // --- Suspend/Activate Confirmation Modal Logic ---
        const actionConfirmBubble = document.getElementById('confirm-action-bubble');
        const actionConfirmTitle = document.getElementById('action-confirm-title');
        const actionConfirmMessage = document.getElementById('action-confirm-message');
        const cancelActionBtn = document.getElementById('cancel-action-btn');
        const confirmActionBtn = document.getElementById('confirm-action-btn');
        const actionButtons = document.querySelectorAll('.action-btn');
        let actionUrl = '';

        actionButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const userId = button.dataset.id;
                const username = button.dataset.username;
                const action = button.dataset.action;

                // Customize modal content based on the action
                const actionText = action.charAt(0).toUpperCase() + action.slice(1);
                actionConfirmTitle.textContent = `${actionText} User`;
                actionConfirmMessage.innerHTML = `Are you sure you want to ${action} user <strong>${username}</strong>?`;
                
                // Set URL and button style
                actionUrl = `handle_user_action.php?action=${action}&id=${userId}`;
                confirmActionBtn.textContent = `Confirm ${actionText}`;
                confirmActionBtn.className = `btn-confirm-action ${action}`; // Add class for styling

                // Show the modal
                actionConfirmBubble.classList.add('show');
            });
        });

        cancelActionBtn.addEventListener('click', () => {
            actionConfirmBubble.classList.remove('show');
        });

        confirmActionBtn.addEventListener('click', () => {
            if (actionUrl) {
                window.location.href = actionUrl;
            }
        });
    </script>
</body>
</html>
