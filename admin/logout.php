<?php
session_start();

// Unset specific admin session variables to be safe
unset($_SESSION['admin_logged_in']);
unset($_SESSION['user_role']);
unset($_SESSION['user']);

// Destroy the entire session
session_destroy();

// Redirect to the admin login page after logout
header("Location: login.php?logout=success");
exit;
?>