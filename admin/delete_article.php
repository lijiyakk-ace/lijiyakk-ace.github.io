<?php
require_once 'admin_auth_check.php'; // Secure this page
require '../db.php';

// 1. Validate input
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: articles.php?error=invalid_id');
    exit;
}

$id = (int)$_GET['id'];

// 2. Get the image path before deleting the record
$stmt = $conn->prepare("SELECT image_url FROM articles WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$article = $result->fetch_assoc();
$stmt->close();

// 3. Delete the article from the database
$delete_stmt = $conn->prepare("DELETE FROM articles WHERE id = ?");
$delete_stmt->bind_param("i", $id);

if ($delete_stmt->execute()) {
    // 4. If DB deletion is successful, delete the associated image file
    if ($article && !empty($article['image_url']) && file_exists('../' . $article['image_url'])) {
        unlink('../' . $article['image_url']);
    }
    header('Location: articles.php?success=deleted');
} else {
    header('Location: articles.php?error=delete_failed');
}

$delete_stmt->close();
$conn->close();
exit;
?>