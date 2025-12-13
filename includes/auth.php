<?php
/**
 * Authentication Check
 * Include this file on all admin pages to ensure user is logged in
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Strict'
    ]);
}

// Check if user is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_email'])) {
    $_SESSION['flash_message'] = 'Please login to access the admin panel';
    $_SESSION['flash_type'] = 'warning';
    header('Location: login.php');
    exit;
}

// Check session timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
    session_unset();
    session_destroy();
    session_start();
    $_SESSION['flash_message'] = 'Session expired. Please login again';
    $_SESSION['flash_type'] = 'info';
    header('Location: login.php');
    exit;
}

// Update last activity time
$_SESSION['last_activity'] = time();
