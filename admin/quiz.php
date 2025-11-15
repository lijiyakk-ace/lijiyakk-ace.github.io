<?php
require_once 'admin_auth_check.php'; // Secure this page
require '../db.php';

// Handle adding new quiz question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_question'])) {
    $category = $_POST['category'];
    $question = $_POST['question'];
    $option_a = $_POST['option_a'];
    $option_b = $_POST['option_b'];
    $option_c = $_POST['option_c'];
    $option_d = $_POST['option_d'];
    $correct_option = $_POST['correct_option'];

    $stmt = $conn->prepare("INSERT INTO quiz (category, question, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $category, $question, $option_a, $option_b, $option_c, $option_d, $correct_option);
    $stmt->execute();
    $stmt->close();
    header("Location: quiz.php"); // Redirect to prevent form resubmission
    exit;
}

// Handle deleting a quiz question
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM quiz WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: quiz.php"); // Redirect to clean URL
    exit;
}

// Fetch all quiz questions
$result = $conn->query("SELECT * FROM quiz ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Quiz</title>
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
        h1, h2 { font-size: 28px; margin-top: 0; margin-bottom: 20px; border-left: 4px solid var(--accent); padding-left: 15px; color: #fff; }
        .form-container, .table-container { background: var(--card); padding: 30px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.07); margin-bottom: 40px; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .form-group { display: flex; flex-direction: column; }
        .form-group.full-width { grid-column: 1 / -1; }
        label { font-weight: 600; margin-bottom: 8px; font-size: 14px; color: var(--muted); }
        input[type="text"], select, textarea { width: 100%; padding: 12px; background: var(--glass); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #e6eef8; font-family: 'Montserrat', sans-serif; font-size: 16px; box-sizing: border-box; transition: border-color 0.3s, box-shadow 0.3s; }
        input:focus, select:focus, textarea:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 2px rgba(29, 155, 240, 0.3); }
        textarea { min-height: 120px; resize: vertical; }
        button[type="submit"] { grid-column: 1 / -1; padding: 12px 25px; background: var(--accent); color: #041022; border: none; border-radius: 8px; font-weight: 700; font-size: 16px; cursor: pointer; transition: background 0.3s; justify-self: start; }
        button[type="submit"]:hover { background: #4fbfff; }
        table { width: 100%; border-collapse: collapse; color: #e6eef8; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid var(--glass); }
        th { background-color: rgba(255,255,255,0.05); font-weight: 700; color: #fff; }
        tr:hover { background-color: var(--glass); }
        .actions a { color: var(--accent); text-decoration: none; font-weight: 600; }
        .actions a:hover { text-decoration: underline; }
        .actions a.delete { color: #d9534f; }

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
            <li><a href="manage_users.php">Manage Users</a></li>
            <li><a href="quiz.php" class="active">Manage Quiz</a></li>
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
                    <line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </div>
            <h1>Manage Quiz</h1>
        </header>

        <main class="main-content">
            <div class="form-container">
                <h2>Add New Quiz Question</h2>
                <form method="POST" class="form-grid">
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" required>
                            <option value="">-- Select Category --</option>
                            <option value="Geography">Geography</option>
                            <option value="History">History</option>
                            <option value="Culture">Culture</option>
                            <option value="Nature">Nature</option>
                            <option value="Food">Food</option>
                            <option value="Travel">Travel</option>
                        </select>
                    </div>
                    <div class="form-group full-width">
                        <label for="question">Question</label>
                        <textarea id="question" name="question" required></textarea>
                    </div>
                    <div class="form-group"><label for="option_a">Option A</label><input type="text" id="option_a" name="option_a" required></div>
                    <div class="form-group"><label for="option_b">Option B</label><input type="text" id="option_b" name="option_b" required></div>
                    <div class="form-group"><label for="option_c">Option C</label><input type="text" id="option_c" name="option_c" required></div>
                    <div class="form-group"><label for="option_d">Option D</label><input type="text" id="option_d" name="option_d" required></div>
                    <div class="form-group">
                        <label for="correct_option">Correct Option</label>
                        <select id="correct_option" name="correct_option" required>
                            <option value="">-- Select Correct --</option>
                            <option value="A">Option A</option>
                            <option value="B">Option B</option>
                            <option value="C">Option C</option>
                            <option value="D">Option D</option>
                        </select>
                    </div>
                    <button type="submit" name="add_question">Add Question</button>
                </form>
            </div>

            <div class="table-container">
                <h2>All Quiz Questions</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Category</th>
                            <th>Question</th>
                            <th>Correct</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['category']) ?></td>
                                <td style="white-space: normal; max-width: 400px;"><?= htmlspecialchars($row['question']) ?></td>
                                <td><?= $row['correct_option'] ?></td>
                                <td class="actions" style="white-space: nowrap;">
                                    <a href="edit_quiz_question.php?id=<?= $row['id'] ?>">Edit</a> |
                                    <a href="quiz.php?delete=<?= $row['id'] ?>" class="delete delete-btn" data-id="<?= $row['id'] ?>" data-question="<?= htmlspecialchars($row['question']) ?>">Delete</a>
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
            <h4>Delete Quiz Question</h4>
            <p id="confirm-message">Are you sure you want to permanently delete this question? This action cannot be undone.</p>
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
                const questionId = button.dataset.id;
                const questionText = button.dataset.question;
                
                // Truncate long questions for the message
                const truncatedQuestion = questionText.length > 100 ? questionText.substring(0, 100) + '...' : questionText;
                
                confirmMessage.innerHTML = `Are you sure you want to permanently delete the question: <strong>"${truncatedQuestion}"</strong>? This action cannot be undone.`;
                deleteUrl = `quiz.php?delete=${questionId}`;
                
                confirmBubble.classList.add('show');
            });
        });

        cancelBtn.addEventListener('click', () => confirmBubble.classList.remove('show'));
        confirmBtn.addEventListener('click', () => { if (deleteUrl) window.location.href = deleteUrl; });
    </script>
</body>
</html>
