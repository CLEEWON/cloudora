<?php
session_start();
require_once __DIR__ . "/db/database.php";
// Pastikan user login
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit("Silakan login terlebih dahulu.");
}

// Validasi input
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    exit("Request tidak valid.");
}

$fileId = intval($_GET['id']);
$userId = intval($_SESSION['user_id']);

// Ambil file berdasarkan ID + pemiliknya
$sql = "SELECT file_name, original_name FROM files WHERE id = ? AND user_id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $fileId, $userId);
$stmt->execute();
$result = $stmt->get_result();
$file = $result->fetch_assoc();
$stmt->close();

if (!$file) {
    http_response_code(404);
    exit("File tidak ditemukan atau Anda tidak berhak mengaksesnya.");
}

// Path file
$uploadsDir = realpath(__DIR__ . "/uploads");

if (!$uploadsDir) {
    die("Folder uploads tidak ditemukan: " . __DIR__ . "/uploads");
}
$storagePath = $uploadsDir . DIRECTORY_SEPARATOR . $file['file_name'];
$real = realpath($storagePath);

// Validasi path agar tidak keluar folder uploads
if ($real === false || strpos($real, $uploadsDir) !== 0) {
    http_response_code(400);
    exit("Akses tidak valid.");
}

if (!file_exists($real) || !is_readable($real)) {
    http_response_code(404);
    exit("File tidak ditemukan di server.");
}

// Tentukan MIME type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $real) ?: "application/octet-stream";
finfo_close($finfo);

// Sanitasi nama asli
$originalName = str_replace(["\r", "\n", '"'], '', $file['original_name']);

// Header download
header("X-Content-Type-Options: nosniff");
header("Content-Type: application/octet-stream");
header('Content-Disposition: attachment; filename="' . $originalName . '"');
header("Content-Length: " . filesize($real));
header("Cache-Control: private, max-age=0, must-revalidate");

// Streaming aman untuk file besar
$chunkSize = 1024 * 1024 * 8; // 8MB
$handle = fopen($real, "rb");
while (!feof($handle)) {
    echo fread($handle, $chunkSize);
    flush();
}
fclose($handle);
exit;
