<?php
session_start();
require_once 'auth/config.php';
require_once 'db/database.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Unauthorized.';
    header("Location: views/halamanDashboard.php");
    exit;
}

if (!isset($_POST['filename'])) {
    $_SESSION['error'] = 'Invalid request.';
    header("Location: views/halamanDashboard.php");
    exit;
}

$filename = basename($_POST['filename']);
$oldPath = UPLOAD_DIR . $filename;
$trashPath = UPLOAD_DIR . "trash/" . $filename;

// Cek file ada
if (!file_exists($oldPath)) {
    $_SESSION['error'] = 'File tidak ditemukan.';
    header("Location: views/halamanDashboard.php");
    exit;
}

// Pindahkan ke folder trash
if (!rename($oldPath, $trashPath)) {
    $_SESSION['error'] = 'Gagal memindahkan file ke sampah.';
    header("Location: views/halamanDashboard.php");
    exit;
}

// Update database → tandai is_deleted = 1
$stmt = $conn->prepare("UPDATE files SET is_deleted = 1 WHERE file_name = ?");
$stmt->bind_param("s", $filename);
$stmt->execute();

$_SESSION['success'] = 'File berhasil dipindahkan ke sampah.';
header("Location: views/halamanDashboard.php");
exit;
