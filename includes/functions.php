<?php
/**
 * Helper Functions
 * Reusable utility functions for the Blog Management System
 */

/**
 * Sanitize output to prevent XSS attacks
 * @param string $data
 * @return string
 */
function sanitize($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF token
 * @return string
 */
function generateCSRFToken() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Validate CSRF token
 * @param string $token
 * @return bool
 */
function validateCSRFToken($token) {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        return false;
    }
    return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Generate URL-friendly slug from string
 * @param string $string
 * @return string
 */
function createSlug($string) {
    $slug = strtolower(trim($string));
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}

/**
 * Redirect to a page
 * @param string $page
 * @param string $message
 * @param string $type
 */
function redirect($page, $message = '', $type = 'success') {
    if ($message) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    header("Location: $page");
    exit;
}

/**
 * Display flash message
 * @return string
 */
function displayFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';

        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);

        $alertClass = [
            'success' => 'alert-success',
            'error' => 'alert-danger',
            'warning' => 'alert-warning',
            'info' => 'alert-info'
        ][$type] ?? 'alert-info';

        return "<div class='alert $alertClass alert-dismissible fade show' role='alert'>
                    " . sanitize($message) . "
                    <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                </div>";
    }
    return '';
}

/**
 * Format date to readable format
 * @param string $date
 * @param string $format
 * @return string
 */
function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

/**
 * Get time ago string with proper singular/plural handling
 * Handles timezone differences correctly
 * @param string $datetime
 * @return string
 */
function timeAgo($datetime) {
    if (empty($datetime)) {
        return 'Unknown';
    }

    // Calculate time difference using simple timestamp comparison
    $timestamp = strtotime($datetime);
    if ($timestamp === false) {
        return 'Invalid date';
    }

    $diff = time() - $timestamp;

    // Handle negative differences (shouldn't happen if timezones match)
    if ($diff < 0) {
        $diff = 0;
    }

    // Just now (0-10 seconds)
    if ($diff < 10) {
        return 'Just now';
    }

    // Seconds (10-59 seconds)
    if ($diff < 60) {
        return $diff . ' seconds ago';
    }

    // Minutes (1-59 minutes)
    if ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ($minutes == 1 ? ' minute ago' : ' minutes ago');
    }

    // Hours (1-23 hours)
    if ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ($hours == 1 ? ' hour ago' : ' hours ago');
    }

    // Days (1-6 days)
    if ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ($days == 1 ? ' day ago' : ' days ago');
    }

    // Weeks (1-4 weeks)
    if ($diff < 2592000) { // 30 days
        $weeks = floor($diff / 604800);
        return $weeks . ($weeks == 1 ? ' week ago' : ' weeks ago');
    }

    // Months (1-11 months)
    if ($diff < 31536000) { // 365 days
        $months = floor($diff / 2592000);
        return $months . ($months == 1 ? ' month ago' : ' months ago');
    }

    // Years (1-4 years)
    $years = floor($diff / 31536000);
    if ($years < 5) {
        return $years . ($years == 1 ? ' year ago' : ' years ago');
    }

    // For very old content (5+ years), show the actual date
    try {
        return $time->format('M d, Y');
    } catch (Exception $e) {
        return date('M d, Y', $timestamp ?? time());
    }
}

/**
 * Truncate text to specified length
 * @param string $text
 * @param int $length
 * @param string $suffix
 * @return string
 */
function truncate($text, $length = 100, $suffix = '...') {
    if (strlen($text) > $length) {
        return substr($text, 0, $length) . $suffix;
    }
    return $text;
}

/**
 * Generate excerpt from content
 * @param string $content
 * @param int $length
 * @return string
 */
function generateExcerpt($content, $length = 150) {
    $text = strip_tags($content);
    return truncate($text, $length);
}

/**
 * Validate image upload
 * @param array $file
 * @return array
 */
function validateImage($file) {
    $errors = [];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'File upload failed';
        return ['success' => false, 'errors' => $errors];
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        $errors[] = 'File size exceeds maximum allowed (2MB)';
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
        $errors[] = 'Invalid file type. Only JPG, PNG, and GIF allowed';
    }

    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }

    return ['success' => true];
}

/**
 * Upload image file
 * @param array $file
 * @param string $directory
 * @return array
 */
function uploadImage($file, $directory = UPLOAD_DIR) {
    $validation = validateImage($file);

    if (!$validation['success']) {
        return $validation;
    }

    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = bin2hex(random_bytes(16)) . '.' . $extension;
    $filepath = $directory . $filename;

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath
        ];
    }

    return ['success' => false, 'errors' => ['Failed to save file']];
}

/**
 * Delete uploaded file
 * @param string $filename
 * @param string $directory
 * @return bool
 */
function deleteUploadedFile($filename, $directory = UPLOAD_DIR) {
    $filepath = $directory . $filename;
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return false;
}

/**
 * Get pagination data
 * @param int $total
 * @param int $perPage
 * @param int $currentPage
 * @return array
 */
function getPagination($total, $perPage, $currentPage = 1) {
    $totalPages = ceil($total / $perPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;

    return [
        'total' => $total,
        'per_page' => $perPage,
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'offset' => $offset,
        'has_prev' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages
    ];
}

/**
 * Sanitize HTML content (for rich text editor)
 * @param string $html
 * @return string
 */
function sanitizeHTML($html) {
    $allowed_tags = '<p><a><strong><em><u><h1><h2><h3><h4><h5><h6><ul><ol><li><br><img><blockquote><code><pre>';
    return strip_tags($html, $allowed_tags);
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_email']);
}

/**
 * Get current admin ID
 * @return int|null
 */
function getCurrentAdminId() {
    return $_SESSION['admin_id'] ?? null;
}

/**
 * Format number with commas
 * @param int $number
 * @return string
 */
function formatNumber($number) {
    return number_format($number);
}

/**
 * Get status badge HTML
 * @param string $status
 * @return string
 */
function getStatusBadge($status) {
    $badges = [
        'published' => '<span class="badge bg-success">Published</span>',
        'draft' => '<span class="badge bg-warning">Draft</span>',
        'pending' => '<span class="badge bg-info">Pending</span>',
        'approved' => '<span class="badge bg-success">Approved</span>',
        'spam' => '<span class="badge bg-danger">Spam</span>'
    ];

    return $badges[$status] ?? '<span class="badge bg-secondary">' . ucfirst($status) . '</span>';
}
