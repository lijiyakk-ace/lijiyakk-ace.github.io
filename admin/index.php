<?php
/**
 * This file serves as a simple entry point to the admin area.
 * It immediately redirects to the main admin dashboard.
 */
require_once 'admin_auth_check.php'; // Secure this page
header('Location: dashboard.php');
exit;
?>