<?php
session_start();
require 'db.php';

// 1. Check if user is logged in
if (!isset($_SESSION['user'])) {
    // If not logged in, you could redirect or show an error.
    // Redirecting back to the post is a good user experience.
    if (isset($_POST['parent_id'])) {
        header('Location: forum_post.php?id=' . $_POST['parent_id'] . '&error=notloggedin');
    } else {
        header('Location: forum.php');
    }
    exit;
}

// 2. Process the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_reply'])) {
    $content = trim($_POST['content']);
    $parent_id = (int)$_POST['parent_id'];
    $author_username = $_SESSION['user'];

    if (!empty($content) && $parent_id > 0) {
        // A reply is an entry in the 'forum' table with a parent_id.
        // The title for a reply is NULL.
        $stmt = $conn->prepare("INSERT INTO forum (parent_id, author_username, content) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $parent_id, $author_username, $content);
        $stmt->execute();
    }

    // 3. Redirect back to the post page
    header('Location: forum_post.php?id=' . $parent_id);
    exit;
}
?>