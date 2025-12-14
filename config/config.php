<?php
/**
 * PostForge - Configuration File
 * Contains all site-wide settings and constants
 */

// Environment Configuration
// Automatically detect environment based on hostname
$isProduction = (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'infinityfree') !== false);
define('ENVIRONMENT', $isProduction ? 'production' : 'development');

// Site Configuration
if (ENVIRONMENT === 'production') {
    define('SITE_NAME', 'PostForge');
    define('SITE_URL', 'http://your-subdomain.infinityfreeapp.me');  // ← PLACEHOLDER: Change to your hosting URL
    define('ADMIN_EMAIL', 'admin@postforge.com');
} else {
    define('SITE_NAME', getenv('SITE_NAME') ?: 'PostForge');
    define('SITE_URL', getenv('SITE_URL') ?: 'http://localhost/PostForge');
    define('ADMIN_EMAIL', getenv('ADMIN_EMAIL') ?: 'admin@postforge.com');
}

// Database Configuration
if (ENVIRONMENT === 'production') {
    // Production: Hosting Database Credentials
    // ⚠️ PLACEHOLDER VALUES - Replace with your actual hosting credentials
    define('DB_HOST', 'sqlXXX.infinityfree.com');         // ← Your MySQL hostname
    define('DB_NAME', 'your_database_name');              // ← Your database name
    define('DB_USER', 'your_database_user');              // ← Your database username
    define('DB_PASS', 'YOUR_DATABASE_PASSWORD');          // ← Your database password
} else {
    // Development: Local Database (XAMPP/WAMP)
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    define('DB_NAME', getenv('DB_NAME') ?: 'blog_management');
    define('DB_USER', getenv('DB_USER') ?: 'root');
    define('DB_PASS', getenv('DB_PASS') ?: '');
}
define('DB_CHARSET', 'utf8mb4');

// Upload Configuration
define('UPLOAD_DIR', __DIR__ . '/../uploads/posts/');
define('UPLOAD_URL', SITE_URL . '/uploads/posts/');
define('PROFILE_UPLOAD_DIR', __DIR__ . '/../uploads/profiles/');
define('PROFILE_UPLOAD_URL', SITE_URL . '/uploads/profiles/');
define('MAX_FILE_SIZE', 2 * 1024 * 1024); // 2MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

// Pagination
define('POSTS_PER_PAGE', 10);
define('COMMENTS_PER_PAGE', 20);

// Session Configuration
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');

// Date & Time
if (ENVIRONMENT === 'production') {
    date_default_timezone_set('UTC');
} else {
    date_default_timezone_set(getenv('TIMEZONE') ?: 'Asia/Kolkata');
}

// Error Reporting & Logging
if (ENVIRONMENT === 'production') {
    // Production: Hide errors from users, log them instead
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/php-errors.log');
} else {
    // Development: Show all errors for debugging
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/php-errors.log');
}
