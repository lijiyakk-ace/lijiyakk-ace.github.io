<?php
require_once 'admin_auth_check.php'; // Secure this page
require '../db.php';

$tip_id = $_GET['id'] ?? null;
if (!$tip_id || !is_numeric($tip_id)) {
    header("Location: carousel.php");
    exit;
}

$tip_id = (int)$tip_id;

// Handle form submission for update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_tip'])) {
    $text = trim($_POST['text']);
    if (!empty($text)) {
        $stmt = $conn->prepare("UPDATE carousel SET text = ? WHERE id = ?");
        $stmt->bind_param("si", $text, $tip_id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['admin_success'] = "Tip updated successfully.";
        header("Location: carousel.php");
        exit;
    }
}

// Fetch the existing tip to edit
$stmt = $conn->prepare("SELECT text FROM carousel WHERE id = ?");
$stmt->bind_param("i", $tip_id);
$stmt->execute();
$result = $stmt->get_result();
$tip = $result->fetch_assoc();
$stmt->close();

if (!$tip) {
    // Tip not found
    header("Location: carousel.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Edit Tip</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root{
          --bg:#0f1724;
          --card:#0b1220;
          --muted:#9aa4b2;
          --accent:#1d9bf0;
          --glass: rgba(255,255,255,0.03);
        }
        body { margin: 0; font-family: 'Montserrat', sans-serif; background:linear-gradient(180deg,var(--bg) 0%, #071027 60%); color: #e6eef8; }
        .page-wrapper { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 40px; }
        .main-content { width: 100%; max-width: 700px; }
        h1, h2 { font-size: 28px; margin-top: 0; margin-bottom: 20px; border-left: 4px solid var(--accent); padding-left: 15px; color: #fff; }
        .form-container { background: var(--card); padding: 30px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.07); }
        .form-grid { display: grid; grid-template-columns: 1fr; gap: 20px; }
        .form-group { display: flex; flex-direction: column; }
        label { font-weight: 600; margin-bottom: 8px; font-size: 14px; color: var(--muted); }
        textarea { width: 100%; padding: 12px; background: var(--glass); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #e6eef8; font-family: 'Montserrat', sans-serif; font-size: 16px; box-sizing: border-box; resize: vertical; }
        textarea:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 2px rgba(29, 155, 240, 0.3); }
        button[type="submit"] { padding: 12px 25px; background: var(--accent); color: #041022; border: none; border-radius: 8px; font-weight: 700; font-size: 16px; cursor: pointer; transition: background 0.3s; justify-self: start; }
        button[type="submit"]:hover { background: #4fbfff; }
        .back-link { display: inline-block; margin-top: 20px; color: var(--muted); text-decoration: none; }
        .back-link:hover { color: var(--accent); }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <main class="main-content">
            <div class="form-container">
                <h2>Edit Tip</h2>
                <form method="post" class="form-grid">
                    <div class="form-group">
                        <label for="tip_text">Tip Text</label>
                        <textarea id="tip_text" name="text" rows="4" required><?php echo htmlspecialchars($tip['text']); ?></textarea>
                    </div>
                    <button type="submit" name="update_tip">Update Tip</button>
                </form>
                <a href="carousel.php" class="back-link">&larr; Back to All Tips</a>
            </div>
        </main>
    </div>
</body>
</html>
<?php $conn->close(); ?>