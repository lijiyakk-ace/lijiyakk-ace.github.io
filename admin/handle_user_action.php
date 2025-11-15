<?php
require_once 'admin_auth_check.php'; // Secure this page
require '../db.php';

// 1. Validate input
if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['action'])) {
    header('Location: manage_users.php?error=invalid_request');
    exit;
}

$user_id = (int)$_GET['id'];
$action = $_GET['action'];

// 2. Determine the new status based on the action
$new_status = '';
if ($action === 'suspend') {
    $new_status = 'suspended';
} elseif ($action === 'activate') {
    $new_status = 'active';
} else {
    header('Location: manage_users.php?error=invalid_action');
    exit;
}

// 3. Update the user's status in the database
$stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
$stmt->bind_param("si", $new_status, $user_id);
$stmt->execute();

// 4. Redirect back to the user management page
header('Location: manage_users.php?success=status_updated');
exit;
?>