<?php
/**
 * Cloudora - Cloud Storage Application Configuration
 * Production-ready configuration with environment-based settings
 */

// Error reporting for development, disable in production
if (getenv('APP_ENV') === 'development' || !defined('PRODUCTION')) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
}


// Database configuration - using environment variables for cloud deployment
define('DB_HOST', getenv('DB_HOST') ?: (defined('DB_HOST_OVERRIDE') ? DB_HOST_OVERRIDE : 'localhost'));
define('DB_USER', getenv('DB_USER') ?: (defined('DB_USER_OVERRIDE') ? DB_USER_OVERRIDE : 'root'));
define('DB_PASS', getenv('DB_PASS') ?: (defined('DB_PASS_OVERRIDE') ? DB_PASS_OVERRIDE : ''));
define('DB_NAME', getenv('DB_NAME') ?: (defined('DB_NAME_OVERRIDE') ? DB_NAME_OVERRIDE : 'cloudora'));
define('DB_PORT', getenv('DB_PORT') ?: (defined('DB_PORT_OVERRIDE') ? DB_PORT_OVERRIDE : '3306'));

// Application settings
define('APP_NAME', 'Cloudora');
define('APP_VERSION', '1.0.0');
define('UPLOAD_DIR', dirname(__DIR__) . '/uploads/');
define('MAX_FILE_SIZE', 2000 * 1024 * 1024); // 10MB in bytes
define('ALLOWED_FILE_TYPES', [
    'jpg', 'jpeg', 'png', 'gif', 'pdf', 'txt', 'doc', 'docx',
    'xls', 'xlsx', 'zip', 'rar', 'mp3', 'mp4', 'ppt', 'pptx'
]);

// Security settings
define('CSRF_TOKEN_LIFETIME', 3600); // 1 hour
define('SESSION_LIFETIME', 3600); // 1 hour

// Create upload directory if it doesn't exist
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// Set timezone
date_default_timezone_set(getenv('TIMEZONE') ?: 'Asia/Jakarta');
?>