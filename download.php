<?php
/**
 * Secure File Download Handler - Cloudora
 * Improved version with enhanced security and logging
 */

session_start();
require_once __DIR__ . "/db/database.php";
require_once __DIR__ . "/auth/config.php";

// ============================================================
// 1. AUTHENTICATION CHECK
// ============================================================
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    error_log("Unauthorized download attempt from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    exit("Authentication required.");
}

// ============================================================
// 2. RATE LIMITING (Simple Implementation)
// ============================================================
$rateLimitKey = 'download_limit_' . $_SESSION['user_id'];
if (!isset($_SESSION[$rateLimitKey])) {
    $_SESSION[$rateLimitKey] = ['count' => 0, 'time' => time()];
}

// Reset counter every 60 seconds
if (time() - $_SESSION[$rateLimitKey]['time'] > 60) {
    $_SESSION[$rateLimitKey] = ['count' => 0, 'time' => time()];
}

// Max 20 downloads per minute
if ($_SESSION[$rateLimitKey]['count'] >= 20) {
    http_response_code(429);
    error_log("Rate limit exceeded for user: " . $_SESSION['user_id']);
    exit("Too many download requests. Please try again later.");
}

$_SESSION[$rateLimitKey]['count']++;

// ============================================================
// 3. INPUT VALIDATION
// ============================================================
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    error_log("Invalid download request - missing or invalid ID from user: " . $_SESSION['user_id']);
    exit("Invalid request.");
}

$fileId = intval($_GET['id']);
$userId = intval($_SESSION['user_id']);

// Validate positive numbers
if ($fileId <= 0) {
    http_response_code(400);
    exit("Invalid file ID.");
}

// ============================================================
// 4. DATABASE QUERY WITH SECURITY CHECKS
// ============================================================
// Include is_deleted check to prevent downloading deleted files
$sql = "SELECT id, file_name, original_name, file_size, file_type 
        FROM files 
        WHERE id = ? 
          AND user_id = ? 
          AND is_deleted = 0 
        LIMIT 1";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    error_log("Database prepare failed in download.php: " . $conn->error);
    exit("Service temporarily unavailable.");
}

$stmt->bind_param("ii", $fileId, $userId);

if (!$stmt->execute()) {
    http_response_code(500);
    error_log("Database execute failed in download.php for user $userId, file $fileId");
    $stmt->close();
    exit("Service temporarily unavailable.");
}

$result = $stmt->get_result();
$file = $result->fetch_assoc();
$stmt->close();

// ============================================================
// 5. FILE EXISTENCE & OWNERSHIP VALIDATION
// ============================================================
if (!$file) {
    http_response_code(404);
    error_log("File not found or access denied - User: $userId, File: $fileId");
    exit("File not found or access denied.");
}

// ============================================================
// 6. FILE PATH VALIDATION (Path Traversal Protection)
// ============================================================
$uploadsDir = realpath(__DIR__ . "/uploads");

if (!$uploadsDir) {
    http_response_code(500);
    error_log("Uploads directory not found: " . __DIR__ . "/uploads");
    exit("Service configuration error.");
}

$storagePath = $uploadsDir . DIRECTORY_SEPARATOR . $file['file_name'];
$realPath = realpath($storagePath);

// Prevent path traversal attacks
if ($realPath === false || strpos($realPath, $uploadsDir) !== 0) {
    http_response_code(400);
    error_log("Path traversal attempt detected - User: $userId, File: " . $file['file_name']);
    exit("Invalid file path.");
}

// ============================================================
// 7. FILE SYSTEM CHECKS
// ============================================================
if (!file_exists($realPath)) {
    http_response_code(404);
    error_log("Physical file missing - DB record exists but file not found: " . $realPath);
    exit("File not found on server.");
}

if (!is_readable($realPath)) {
    http_response_code(500);
    error_log("File not readable: " . $realPath);
    exit("File cannot be accessed.");
}

// ============================================================
// 8. MIME TYPE DETECTION
// ============================================================
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $realPath);
finfo_close($finfo);

if (!$mimeType) {
    $mimeType = "application/octet-stream";
}

// ============================================================
// 9. FILENAME SANITIZATION (RFC 6266 compliant)
// ============================================================
$originalName = $file['original_name'];

// Remove control characters and quotes
$safeName = preg_replace('/[^\x20-\x7E]/', '', $originalName);
$safeName = str_replace(['"', "'", "\\"], '', $safeName);

// Fallback if sanitization removes everything
if (empty($safeName)) {
    $safeName = 'download_' . $fileId . '.' . $file['file_type'];
}

// ============================================================


// ============================================================
// 11. ACTIVITY LOGGING (Important for audit trail)
// ============================================================
// $logStmt = $conn->prepare(
//     "INSERT INTO download_logs (user_id, file_id, ip_address, user_agent, downloaded_at) 
//      VALUES (?, ?, ?, ?, NOW())"
// );

if ($logStmt) {
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 255);
    
    $logStmt->bind_param("iiss", $userId, $fileId, $ipAddress, $userAgent);
    $logStmt->execute();
    $logStmt->close();
}

// Also log to error_log for server-side tracking
error_log(
    "File download: User={$userId}, File={$fileId}, Name={$originalName}, Size={$file['file_size']}, IP=" . 
    ($_SERVER['REMOTE_ADDR'] ?? 'unknown')
);

// ============================================================
// 12. SECURITY HEADERS
// ============================================================
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Force download (not inline display)
header('Content-Type: application/octet-stream');

// RFC 6266 compliant Content-Disposition with fallback
$encodedName = rawurlencode($safeName);
header("Content-Disposition: attachment; filename=\"{$safeName}\"; filename*=UTF-8''{$encodedName}");

header('Content-Length: ' . filesize($realPath));
header('Content-Transfer-Encoding: binary');
header('Cache-Control: private, max-age=0, must-revalidate, no-store');
header('Pragma: no-cache');
header('Expires: 0');

// ============================================================
// 13. STREAMING FILE OUTPUT (Memory Efficient)
// ============================================================
// Disable output buffering for large files
if (ob_get_level()) {
    ob_end_clean();
}

$chunkSize = 8 * 1024 * 1024; // 8MB chunks
$handle = fopen($realPath, 'rb');

if ($handle === false) {
    http_response_code(500);
    error_log("Failed to open file for reading: " . $realPath);
    exit("Failed to read file.");
}

// Stream file in chunks
while (!feof($handle)) {
    $chunk = fread($handle, $chunkSize);
    
    if ($chunk === false) {
        error_log("Error reading file chunk: " . $realPath);
        break;
    }
    
    echo $chunk;
    
    // Flush output to client
    if (ob_get_level()) {
        ob_flush();
    }
    flush();
    
    // Check if client disconnected
    if (connection_status() != CONNECTION_NORMAL) {
        error_log("Client disconnected during download: User={$userId}, File={$fileId}");
        break;
    }
}

fclose($handle);
exit;