<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

// 1. Check if user is logged in
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit;
}

$current_username = $_SESSION['user'];

// 2. Get the new data from the POST request
$data = json_decode(file_get_contents('php://input'), true);

$firstname = trim($data['firstname'] ?? '');
$lastname = trim($data['lastname'] ?? '');
$username = trim($data['username'] ?? '');
$country = trim($data['country'] ?? '');

// 3. Validate the input
if (empty($firstname) || empty($lastname) || empty($username) || empty($country)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

// 4. Check if the new username is already taken by another user
if ($username !== $current_username) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Username is already taken.']);
        exit;
    }
}

// 5. Update the database
$stmt = $conn->prepare("UPDATE users SET firstname = ?, lastname = ?, username = ?, country = ? WHERE username = ?");
$stmt->bind_param("sssss", $firstname, $lastname, $username, $country, $current_username);

if ($stmt->execute()) {
    // 6. If username was changed, update the session variable
    $_SESSION['user'] = $username;
    echo json_encode(['success' => true, 'message' => 'Profile updated successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error. Could not update profile.']);
}

$stmt->close();
$conn->close();
?>