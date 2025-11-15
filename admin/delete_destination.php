<?php
require_once 'admin_auth_check.php'; // Secure this page
require '../db.php';

// 1. Validate input
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: destinations.php?error=invalid_id');
    exit;
}

$id = (int)$_GET['id'];

// 2. Get the image paths before deleting the record
$stmt = $conn->prepare("SELECT image_url, food_image_url, culture_image_url, ecosystem_image_url FROM destinations WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header('Location: destinations.php?error=not_found');
    exit;
}
$images = $result->fetch_assoc();
$stmt->close();

// 3. Delete the destination from the database
$delete_stmt = $conn->prepare("DELETE FROM destinations WHERE id = ?");
$delete_stmt->bind_param("i", $id);

if ($delete_stmt->execute()) {
    // 4. If DB deletion is successful, delete the associated image files
    foreach ($images as $image_path) {
        if (!empty($image_path) && file_exists('../' . $image_path)) {
            unlink('../' . $image_path);
        }
    }
    header('Location: destinations.php?success=deleted');
} else {
    header('Location: destinations.php?error=delete_failed');
}

$delete_stmt->close();
$conn->close();
exit;
?>