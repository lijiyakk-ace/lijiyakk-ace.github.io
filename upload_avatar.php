<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

// 1. Check if user is logged in
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit;
}

// 2. Check if a file was uploaded
if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    $uploadErrors = [
        UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
        UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
        UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded.',
        UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.',
    ];
    $errorCode = $_FILES['avatar']['error'] ?? UPLOAD_ERR_NO_FILE;
    $message = $uploadErrors[$errorCode] ?? 'Unknown upload error.';
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

// 4. Create a unique filename and define upload path
$uploadDir = 'uploads/avatars/';
if (!is_dir($uploadDir)) {
    // Attempt to create the directory
    if (!mkdir($uploadDir, 0777, true)) {
        echo json_encode(['success' => false, 'message' => 'Failed to create upload directory. Check server permissions.']);
        exit;
    }
}

// Function to check if a directory is writable
function is_writable_directory($dir) {
    return is_writable($dir);
}


$file = $_FILES['avatar'];

// 3. Validate file type and size
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
$maxSize = 5 * 1024 * 1024; // 5 MB

if (!in_array($file['type'], $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Please upload a JPG, PNG, or GIF.']);
    exit;
}

if ($file['size'] > $maxSize) {
    echo json_encode(['success' => false, 'message' => 'File is too large. Maximum size is 5 MB.']);
    exit;
}

$username = $_SESSION['user'];
$fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
$newFileName = $username . '_' . time() . '.' . $fileExtension;
$uploadPath = $uploadDir . $newFileName;

// 5. Move the file and update the database
// Check if the temp file exists
if (!file_exists($file['tmp_name'])) {
    echo json_encode(['success' => false, 'message' => 'Temporary file not found.']);
    exit;
}

// Check if the upload directory is writable
if (!is_writable_directory($uploadDir)) {
    echo json_encode(['success' => false, 'message' => 'Upload directory is not writable.']);
    exit;
}
if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
    // Update the user's avatar path in the database
    $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE username = ?");
    $stmt->bind_param("ss", $uploadPath, $username);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'filePath' => $uploadPath]);
    } else {
        // If DB update fails, delete the uploaded file to prevent orphans
        unlink($uploadPath);
        echo json_encode(['success' => false, 'message' => 'Database error. Could not save profile picture.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file.']);
}

?>