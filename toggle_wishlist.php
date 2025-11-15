<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    $response['message'] = 'Authentication required.';
    echo json_encode($response);
    exit;
}

// Get user ID
$username = $_SESSION['user'];
$user_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$user_stmt->bind_param("s", $username);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data = $user_result->fetch_assoc();
$user_id = $user_data['id'] ?? null;

if (!$user_id) {
    $response['message'] = 'User not found.';
    echo json_encode($response);
    exit;
}

// Get destination ID from POST request
$input = json_decode(file_get_contents('php://input'), true);
$destination_id = $input['destination_id'] ?? null;

if (!$destination_id) {
    $response['message'] = 'Destination ID is required.';
    echo json_encode($response);
    exit;
}

// Check if already wishlisted
$check_stmt = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND destination_id = ?");
$check_stmt->bind_param("ii", $user_id, $destination_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    // Already wishlisted, so remove it
    $delete_stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND destination_id = ?");
    $delete_stmt->bind_param("ii", $user_id, $destination_id);
    if ($delete_stmt->execute()) {
        $response['success'] = true;
        $response['is_wishlisted'] = false;
        $response['message'] = 'Removed from wishlist.';
    } else {
        $response['message'] = 'Error removing from wishlist.';
    }
} else {
    // Not wishlisted, so add it
    $insert_stmt = $conn->prepare("INSERT INTO wishlist (user_id, destination_id) VALUES (?, ?)");
    $insert_stmt->bind_param("ii", $user_id, $destination_id);
    if ($insert_stmt->execute()) {
        $response['success'] = true;
        $response['is_wishlisted'] = true;
        $response['message'] = 'Added to wishlist.';
    } else {
        $response['message'] = 'Error adding to wishlist.';
    }
}

echo json_encode($response);

$conn->close();
?>