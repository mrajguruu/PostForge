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

// Auto-cleanup: Remove temporary user-created content older than 24 hours
// This runs once per session to clean up abandoned temporary content
if (!isset($_SESSION['cleanup_done'])) {
    try {
        require_once __DIR__ . '/../config/database.php';
        $db = getDB();

        // Delete non-demo posts older than 24 hours
        $db->exec("DELETE FROM posts WHERE is_demo = 0 AND created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)");

        // Delete non-demo categories older than 24 hours (only if they have no posts)
        $db->exec("DELETE FROM categories WHERE is_demo = 0 AND created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR) AND id NOT IN (SELECT DISTINCT category_id FROM posts WHERE category_id IS NOT NULL)");

        // Delete non-demo comments older than 24 hours
        $db->exec("DELETE FROM comments WHERE is_demo = 0 AND created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)");

        $_SESSION['cleanup_done'] = true;
    } catch (Exception $e) {
        // Silent fail - cleanup is not critical
    }
}
