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
$trashDir = UPLOAD_DIR . "trash/";
$trashPath = $trashDir . $filename;

// Pastikan file terdaftar di DB dan dapatkan owner
$stmtCheck = $conn->prepare("SELECT user_id FROM files WHERE file_name = ? LIMIT 1");
$stmtCheck->bind_param("s", $filename);
$stmtCheck->execute();
$resCheck = $stmtCheck->get_result();
if ($resCheck->num_rows === 0) {
    $_SESSION['error'] = 'File tidak ditemukan di database.';
    header("Location: views/halamanDashboard.php");
    exit;
}
$row = $resCheck->fetch_assoc();
$fileOwner = (int)$row['user_id'];

// Cek hak akses: hanya owner atau admin boleh memindahkan ke sampah
if ($_SESSION['role'] !== 'admin' && $fileOwner !== (int)$_SESSION['user_id']) {
    $_SESSION['error'] = 'Akses ditolak. Anda tidak memiliki izin untuk menghapus file ini.';
    header("Location: views/halamanDashboard.php");
    exit;
}

// Cek file ada di filesystem
if (!file_exists($oldPath)) {
    $_SESSION['error'] = 'File tidak ditemukan.';
    header("Location: views/halamanDashboard.php");
    exit;
}

// Pastikan folder trash ada
if (!is_dir($trashDir)) {
    if (!mkdir($trashDir, 0777, true)) {
        $_SESSION['error'] = 'Gagal membuat folder sampah.';
        header("Location: views/halamanDashboard.php");
        exit;
    }
}

// Pindahkan ke folder trash
if (!rename($oldPath, $trashPath)) {
    $_SESSION['error'] = 'Gagal memindahkan file ke sampah.';
    header("Location: views/halamanDashboard.php");
    exit;
}

// Update database → tandai is_deleted = 1 (tambahkan filter file_name dan owner untuk safety)
$stmt = $conn->prepare("UPDATE files SET is_deleted = 1 WHERE file_name = ? AND user_id = ?");
$stmt->bind_param("si", $filename, $fileOwner);
$stmt->execute();

$_SESSION['success'] = 'File berhasil dipindahkan ke sampah.';
header("Location: views/halamanDashboard.php");
exit;
