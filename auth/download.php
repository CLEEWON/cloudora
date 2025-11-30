<?php
session_start();

// Include session protection
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/formLogin.php");
    exit;
}

// Get filename from GET parameter
$filename = isset($_GET['filename']) ? basename($_GET['filename']) : '';

if (empty($filename)) {
    $_SESSION['error'] = 'Invalid file request.';
    header("Location: views/halamanDashboard.php");
    exit;
}

// Security: Prevent directory traversal
if (strpos($filename, '..') !== false || strpos($filename, '/') !== false) {
    $_SESSION['error'] = 'Invalid file request.';
    header("Location: views/halamanDashboard.php");
    exit;
}

// Define upload directory
$uploadDir = __DIR__ . '/uploads/';
$filePath = $uploadDir . $filename;

// Check if file exists
if (!file_exists($filePath)) {
    $_SESSION['error'] = 'File not found.';
    header("Location: views/halamanDashboard.php");
    exit;
}

// Log download (optional)
require_once 'db/database.php';
$stmt = $conn->prepare("UPDATE files SET download_count = download_count + 1 WHERE file_name = ?");
$stmt->bind_param("s", $filename);
$stmt->execute();

// Set headers for file download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Expires: 0');

// Output file content
readfile($filePath);
exit;
?>