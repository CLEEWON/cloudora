<?php
session_start();
require_once 'auth/config.php';
require_once 'db/database.php';

if (!isset($_POST['filename'])) {
    $_SESSION['error'] = 'Invalid request.';
    header("Location: views/halamanSampah.php");
    exit;
}

$filename = basename($_POST['filename']);
$filePath = UPLOAD_DIR . "trash/" . $filename;

if (file_exists($filePath)) unlink($filePath);

$stmt = $conn->prepare("DELETE FROM files WHERE file_name = ?");
$stmt->bind_param("s", $filename);
$stmt->execute();

$_SESSION['success'] = 'File dihapus permanen.';
header("Location: views/halamanSampah.php");
exit;
