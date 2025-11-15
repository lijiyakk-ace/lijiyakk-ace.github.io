<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

// 1. Check if user is logged in
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit;
}

$username = $_SESSION['user'];

// 2. Fetch the current avatar path to delete the file
$stmt = $conn->prepare("SELECT avatar FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$current_avatar_path = $user['avatar'] ?? null;

// 3. Update the database to set avatar to NULL
$update_stmt = $conn->prepare("UPDATE users SET avatar = NULL WHERE username = ?");
$update_stmt->bind_param("s", $username);

if ($update_stmt->execute()) {
    // 4. If DB update is successful, delete the old file from the server
    if ($current_avatar_path && file_exists($current_avatar_path)) {
        unlink($current_avatar_path);
    }
    echo json_encode(['success' => true, 'message' => 'Profile picture removed.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error. Could not remove profile picture.']);
}

$stmt->close();
$update_stmt->close();
$conn->close();
?>