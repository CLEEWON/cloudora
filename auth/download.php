<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/formLogin.php");
    exit;
}

require_once __DIR__ . '/auth/config.php';
require_once __DIR__ . '/db/database.php';

$filename = isset($_GET['filename']) ? basename($_GET['filename']) : '';

if (empty($filename)) {
    $_SESSION['error'] = 'File tidak valid.';
    header("Location: views/halamanDashboard.php");
    exit;
}

// Security: Prevent directory traversal
if (strpos($filename, '..') !== false || strpos($filename, '/') !== false) {
    $_SESSION['error'] = 'Akses ditolak.';
    header("Location: views/halamanDashboard.php");
    exit;
}

$filePath = UPLOAD_DIR . $filename;

if (!file_exists($filePath)) {
    $_SESSION['error'] = 'File tidak ditemukan.';
    header("Location: views/halamanDashboard.php");
    exit;
}

// Verify ownership
$stmt = $conn->prepare("SELECT * FROM files WHERE file_name = ? AND user_id = ?");
$stmt->bind_param("si", $filename, $_SESSION['user_id']);
$stmt->execute();
$file = $stmt->get_result()->fetch_assoc();

if (!$file) {
    $_SESSION['error'] = 'Anda tidak memiliki akses ke file ini.';
    header("Location: views/halamanDashboard.php");
    exit;
}

// Update download count
$stmt = $conn->prepare("UPDATE files SET download_count = download_count + 1 WHERE file_name = ?");
$stmt->bind_param("s", $filename);
$stmt->execute();

// Log activity (optional)
$stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description) VALUES (?, 'download', ?)");
$desc = "Downloaded: " . $file['original_name'];
$stmt->bind_param("is", $_SESSION['user_id'], $desc);
$stmt->execute();

// Send file
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($file['original_name']) . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: must-revalidate');
header('Pragma: public');

readfile($filePath);
exit;
?>