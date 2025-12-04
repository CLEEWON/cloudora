<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/formLogin.php");
    exit;
}

require_once 'auth/config.php';
require_once 'db/database.php';

if (!isset($_POST['filename'])) {
    $_SESSION['error'] = 'Request tidak valid.';
    header("Location: views/halamanSampah.php");
    exit;
}

$filename = basename($_POST['filename']);
$user_id = $_SESSION['user_id'];

// Validasi file milik user + is_deleted = 1
$stmt = $conn->prepare("SELECT file_name FROM files WHERE file_name = ? AND user_id = ? AND is_deleted = 1");
$stmt->bind_param("si", $filename, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = 'File tidak ditemukan atau bukan milik Anda.';
    header("Location: views/halamanSampah.php");
    exit;
}

// ---- PATH YANG BENAR ----
$trashPath   = __DIR__ . "/uploads/trash/" . $filename;
$restorePath = __DIR__ . "/uploads/" . $filename;

// File harus ada di trash
if (!file_exists($trashPath)) {
    $_SESSION['error'] = "File tidak ditemukan di folder sampah. Path: $trashPath";
    header("Location: views/halamanSampah.php");
    exit;
}

// Cek folder writable
if (!is_writable(dirname($restorePath))) {
    $_SESSION['error'] = 'Folder uploads tidak bisa ditulis.';
    header("Location: views/halamanSampah.php");
    exit;
}

// Pindahkan file dari trash ke uploads
if (!rename($trashPath, $restorePath)) {
    $err = error_get_last();
    $_SESSION['error'] = 'Gagal memulihkan file: ' . $err['message'];
    header("Location: views/halamanSampah.php");
    exit;
}

// Update database
$stmt = $conn->prepare("UPDATE files SET is_deleted = 0 WHERE file_name = ? AND user_id = ?");
$stmt->bind_param("si", $filename, $user_id);
$stmt->execute();

$_SESSION['success'] = 'File berhasil dipulihkan!';
header("Location: views/halamanSampah.php");
exit;
