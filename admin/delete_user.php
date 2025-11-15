<?php
require_once 'admin_auth_check.php'; // Secure this page
require '../db.php';

// 1. Validate input
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: manage_users.php?error=invalid_id');
    exit;
}

$user_id = (int)$_GET['id'];

// 2. Get the username before deleting the user
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header('Location: manage_users.php?error=user_not_found');
    exit;
}
$user = $result->fetch_assoc();
$username_to_delete = $user['username'];

// 3. Delete all content associated with the user (e.g., forum posts and replies)
$delete_content_stmt = $conn->prepare("DELETE FROM forum WHERE author_username = ?");
$delete_content_stmt->bind_param("s", $username_to_delete);
$delete_content_stmt->execute();

// 4. Delete the user from the 'users' table
$delete_user_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$delete_user_stmt->bind_param("i", $user_id);
$delete_user_stmt->execute();

// 5. Redirect back to the user management page
header('Location: manage_users.php?success=user_deleted');
exit;
?>