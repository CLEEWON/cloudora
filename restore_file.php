<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/formLogin.php");
    exit;
}

require_once 'auth/config.php';
require_once 'db/database.php';

if (!isset($_POST['filename'])) {
    $_SESSION['error'] = 'Invalid request.';
    header("Location: views/halamanSampah.php");
    exit;
}

$filename = basename($_POST['filename']);

// Path SESUAI struktur GitHub
$trashPath   = __DIR__ . "/auth/uploads/trash/" . $filename;
$restorePath = __DIR__ . "/auth/uploads/" . $filename;

// Cek apakah file ada di trash
if (!file_exists($trashPath)) {
    $_SESSION['error'] = 'File tidak ditemukan di sampah.';
    header("Location: views/halamanSampah.php");
    exit;
}

// Pindahkan kembali ke folder uploads
if (!rename($trashPath, $restorePath)) {
    $_SESSION['error'] = 'Gagal memulihkan file.';
    header("Location: views/halamanSampah.php");
    exit;
}

// Update database
$stmt = $conn->prepare("UPDATE files SET is_deleted = 0 WHERE file_name = ?");
$stmt->bind_param("s", $filename);
$stmt->execute();

$_SESSION['success'] = 'File berhasil dipulihkan.';
header("Location: views/halamanSampah.php");
exit;
