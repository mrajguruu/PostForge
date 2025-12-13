<?php
/**
 * Admin Logout
 */

session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Delete remember me cookie
if (isset($_COOKIE['admin_remember'])) {
    setcookie('admin_remember', '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Start new session for flash message
session_start();
$_SESSION['flash_message'] = 'You have been logged out successfully';
$_SESSION['flash_type'] = 'success';

// Redirect to login
header('Location: login.php');
exit;
