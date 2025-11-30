<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/formLogin.php");
    exit;
}

if (!isset($_POST['filename'])) {
    $_SESSION['error'] = 'Invalid request.';
    header("Location: views/halamanSampah.php");
    exit;
}

$filename = basename($_POST['filename']);

require_once 'db/database.php';

$stmt = $conn->prepare("SELECT * FROM files WHERE file_name = ?");
$stmt->bind_param("s", $filename);
$stmt->execute();
$file = $stmt->get_result()->fetch_assoc();

if (!$file) {
    $_SESSION['error'] = 'File not found.';
    header("Location: views/halamanSampah.php");
    exit;
}

$path = __DIR__ . '/uploads/' . $filename;

// Hapus file fisik
if (file_exists($path)) {
    unlink($path);
}

// Hapus dari DB
$delete = $conn->prepare("DELETE FROM files WHERE file_name = ?");
$delete->bind_param("s", $filename);
$delete->execute();

$_SESSION['success'] = 'File dihapus permanen.';
header("Location: views/halamanSampah.php");
exit;
