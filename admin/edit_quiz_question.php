<?php
require_once 'admin_auth_check.php'; // Secure this page
require '../db.php';

// 1. Validate ID and fetch question data
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid question ID.");
}

$id = (int)$_GET['id'];
$stmt = $conn->prepare("SELECT * FROM quiz WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Question not found.");
}
$question_data = $result->fetch_assoc();
$stmt->close();

// 2. Handle form submission for updating
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_question'])) {
    $category = $_POST['category'];
    $question = $_POST['question'];
    $option_a = $_POST['option_a'];
    $option_b = $_POST['option_b'];
    $option_c = $_POST['option_c'];
    $option_d = $_POST['option_d'];
    $correct_option = $_POST['correct_option'];

    $update_stmt = $conn->prepare("UPDATE quiz SET category=?, question=?, option_a=?, option_b=?, option_c=?, option_d=?, correct_option=? WHERE id=?");
    $update_stmt->bind_param("sssssssi", $category, $question, $option_a, $option_b, $option_c, $option_d, $correct_option, $id);
    
    if ($update_stmt->execute()) {
        header("Location: quiz.php"); // Redirect back to the main quiz admin page
        exit;
    } else {
        $error_message = "Error updating question. Please try again.";
    }
    $update_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Quiz Question</title>
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
        .sidebar { position: fixed; top: 0; left: 0; height: 100%; width: 260px; background: var(--card); padding: 30px 20px; border-right: 1px solid rgba(255,255,255,0.07); display: flex; flex-direction: column; z-index: 200; transform: translateX(-100%); transition: transform 0.3s ease; box-sizing: border-box; }
        .sidebar h3 { font-size: 22px; text-align: center; margin: 0 0 30px 0; color: #fff; font-weight: bold; }
        .sidebar ul { list-style: none; padding: 0; margin: 0; flex-grow: 1; }
        .sidebar li { margin-bottom: 8px; }
        .sidebar a { display: block; padding: 12px 20px; color: var(--muted); text-decoration: none; border-radius: 8px; transition: background-color 0.3s ease, color 0.3s ease; font-weight: 500; }
        .sidebar a:hover { background-color: rgba(29, 155, 240, 0.1); color: #fff; }
        .sidebar a.active { background-color: var(--accent); color: #0b1220; font-weight: 700; box-shadow: 0 2px 10px rgba(29, 155, 240, 0.4); }
        body.sidebar-open .sidebar { transform: translateX(0); }
        body.sidebar-open { padding-left: 260px; }
        .admin-header { display: flex; align-items: center; padding: 15px 30px; background: var(--card); border-bottom: 1px solid rgba(255,255,255,0.07); position: sticky; top: 0; z-index: 100; }
        .burger-icon { cursor: pointer; margin-right: 20px; }
        .burger-icon svg { width: 24px; height: 24px; stroke: var(--muted); transition: stroke 0.3s; }
        .burger-icon:hover svg { stroke: #fff; }
        .page-wrapper { min-height: 100vh; transition: padding-left 0.3s ease; }
        .main-content { padding: 40px; }
        h1, h2 { font-size: 28px; margin-top: 0; margin-bottom: 20px; border-left: 4px solid var(--accent); padding-left: 15px; color: #fff; }
        .form-container { background: var(--card); padding: 30px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.07); margin-bottom: 40px; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .form-group { display: flex; flex-direction: column; }
        .form-group.full-width { grid-column: 1 / -1; }
        label { font-weight: 600; margin-bottom: 8px; font-size: 14px; color: var(--muted); }
        input[type="text"], select, textarea { width: 100%; padding: 12px; background: var(--glass); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #e6eef8; font-family: 'Montserrat', sans-serif; font-size: 16px; box-sizing: border-box; }
        input:focus, select:focus, textarea:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 2px rgba(29, 155, 240, 0.3); }
        textarea { min-height: 120px; resize: vertical; }
        button[type="submit"] { grid-column: 1 / -1; padding: 12px 25px; background: var(--accent); color: #041022; border: none; border-radius: 8px; font-weight: 700; font-size: 16px; cursor: pointer; transition: background 0.3s; justify-self: start; }
        button[type="submit"]:hover { background: #4fbfff; }
        .cancel-link { justify-self: start; grid-column: 1 / -1; margin-top: -10px; color: var(--muted); text-decoration: none; font-size: 14px; }
        .cancel-link:hover { color: #fff; }
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
                    <line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </div>
            <h1>Edit Quiz Question</h1>
        </header>

        <main class="main-content">
            <div class="form-container">
                <h2>Editing Question ID: <?= $id ?></h2>
                <?php if (isset($error_message)): ?>
                    <p style="color: red;"><?= $error_message ?></p>
                <?php endif; ?>
                <form method="POST" class="form-grid">
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" required>
                            <?php 
                            $categories = ["Geography", "History", "Culture", "Nature", "Food", "Travel"];
                            foreach ($categories as $cat) {
                                $selected = ($question_data['category'] === $cat) ? 'selected' : '';
                                echo "<option value='$cat' $selected>$cat</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group full-width">
                        <label for="question">Question</label>
                        <textarea id="question" name="question" required><?= htmlspecialchars($question_data['question']) ?></textarea>
                    </div>
                    <div class="form-group"><label for="option_a">Option A</label><input type="text" id="option_a" name="option_a" value="<?= htmlspecialchars($question_data['option_a']) ?>" required></div>
                    <div class="form-group"><label for="option_b">Option B</label><input type="text" id="option_b" name="option_b" value="<?= htmlspecialchars($question_data['option_b']) ?>" required></div>
                    <div class="form-group"><label for="option_c">Option C</label><input type="text" id="option_c" name="option_c" value="<?= htmlspecialchars($question_data['option_c']) ?>" required></div>
                    <div class="form-group"><label for="option_d">Option D</label><input type="text" id="option_d" name="option_d" value="<?= htmlspecialchars($question_data['option_d']) ?>" required></div>
                    <div class="form-group">
                        <label for="correct_option">Correct Option</label>
                        <select id="correct_option" name="correct_option" required>
                            <?php 
                            $options = ["A", "B", "C", "D"];
                            foreach ($options as $opt) {
                                $selected = ($question_data['correct_option'] === $opt) ? 'selected' : '';
                                echo "<option value='$opt' $selected>Option $opt</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" name="update_question">Update Question</button>
                    <a href="quiz.php" class="cancel-link">Cancel</a>
                </form>
            </div>
        </main>
    </div>

    <script>
        document.getElementById('burger-icon').addEventListener('click', function() {
            document.body.classList.toggle('sidebar-open');
        });
    </script>
</body>
</html>

```

These changes provide a complete workflow for managing your quiz questions. You can now easily add, edit, and delete them from the admin panel.

<!--
[PROMPT_SUGGESTION]Can you apply the admin panel's CSS styling to the `edit_quiz_question.php` page?[/PROMPT_SUGGESTION]
[PROMPT_SUGGESTION]Can you fetch the quiz questions from the database on the public-facing `quiz.php` page instead of using hardcoded data?[/PROMPT_SUGGESTION]
-->