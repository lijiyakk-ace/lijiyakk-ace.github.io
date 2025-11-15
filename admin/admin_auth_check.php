<?php
session_start();

// Check if the admin is logged in, otherwise redirect to login page
// More robust check for both a general login flag and the specific role.
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php'); // Redirect to the admin login page within the same directory
    exit;
}