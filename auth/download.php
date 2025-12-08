<?php
// auth/download.php (secure)
session_start();
require_once "../config/database.php"; // $conn harus berasal dari sini

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Silakan login terlebih dahulu.');
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    exit('Invalid request.');
}

$fileId = intval($_GET['id']);
$userId = intval($_SESSION['user_id']);

// Ambil metadata file
$query = "SELECT id, user_id, file_name, original_name, file_type FROM files WHERE id = ? AND user_id = ? LIMIT 1";
$stmt = $conn->prepare($query);
if (!$stmt) {
    http_response_code(500);
    exit('DB error.');
}
$stmt->bind_param("ii", $fileId, $userId);
$stmt->execute();
$result = $stmt->get_result();
$file = $result->fetch_assoc();
$stmt->close();

if (!$file) {
    http_response_code(404);
    exit('File tidak ditemukan atau Anda tidak berhak mengaksesnya.');
}

// Build path and validate
$uploadsDir = realpath(__DIR__ . "/../uploads"); // adjust as needed
if (!$uploadsDir) {
    http_response_code(500);
    exit('Server config error.');
}
$storagePath = $uploadsDir . DIRECTORY_SEPARATOR . $file['file_name'];
$real = realpath($storagePath);
if ($real === false || strpos($real, $uploadsDir) !== 0) {
    http_response_code(400);
    exit('Permintaan tidak valid.');
}

if (!is_file($real) || !is_readable($real)) {
    http_response_code(404);
    exit('File tidak ditemukan di server.');
}

// Determine mime (but force download)
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $real) ?: 'application/octet-stream';
finfo_close($finfo);

// Sanitize original_name
$safeOriginal = str_replace(["\r", "\n", '"'], '', $file['original_name']);

header('X-Content-Type-Options: nosniff');
header('Content-Type: application/octet-stream'); // force download
header('Content-Disposition: attachment; filename="'.$safeOriginal.'"');
header('Content-Length: ' . filesize($real));
header('Cache-Control: private, max-age=0, must-revalidate');

// Optional: log download event
// log_download($userId, $fileId, $_SERVER['REMOTE_ADDR']);

$chunkSize = 8 * 1024 * 1024;
$handle = fopen($real, 'rb');
if ($handle === false) {
    http_response_code(500);
    exit('Gagal membuka file.');
}
while (!feof($handle)) {
    echo fread($handle, $chunkSize);
    flush();
}
fclose($handle);
exit;
?>
