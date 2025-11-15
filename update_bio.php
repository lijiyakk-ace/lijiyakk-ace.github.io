<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

// 1. Check if user is logged in
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit;
}

// 2. Get the new bio from the POST request
$data = json_decode(file_get_contents('php://input'), true);
$bio = $data['bio'] ?? null;

if ($bio === null) {
    echo json_encode(['success' => false, 'message' => 'No bio content provided.']);
    exit;
}

// 3. Update the database
$username = $_SESSION['user'];
$stmt = $conn->prepare("UPDATE users SET bio = ? WHERE username = ?");
$stmt->bind_param("ss", $bio, $username);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Bio updated successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error. Could not update bio.']);
}
?>