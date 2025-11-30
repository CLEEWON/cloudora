<?php
require_once __DIR__ . '/auth/config.php';
require_once __DIR__ . '/db/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Jika belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/formLogin.php");
    exit;
}

// Pastikan ada file_name
if (!isset($_POST['filename'])) {
    header("Location: views/halamanDashboard.php");
    exit;
}

$filename = $_POST['filename'];

// Cek status bintang saat ini
$stmt = $conn->prepare("SELECT is_starred FROM files WHERE file_name = ? AND user_id = ?");
$stmt->bind_param("si", $filename, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if (!$result) {
    header("Location: views/halamanDashboard.php");
    exit;
}

$current = $result['is_starred'];
$newStatus = $current ? 0 : 1;

// Update
$stmt = $conn->prepare("UPDATE files SET is_starred = ? WHERE file_name = ? AND user_id = ?");
$stmt->bind_param("isi", $newStatus, $filename, $_SESSION['user_id']);
$stmt->execute();

$_SESSION['success'] = $newStatus ? 'Ditambahkan ke Berbintang ⭐' : 'Dihapus dari Berbintang';

header("Location: views/halamanDashboard.php");
exit;
