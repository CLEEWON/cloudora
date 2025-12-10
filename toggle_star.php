<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db/database.php';

// Cek Login
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/formLogin.php");
    exit;
}

// Cek file_id
if (!isset($_POST['file_id'])) {
    header("Location: views/halamanDashboard.php");
    exit;
}

$file_id = intval($_POST['file_id']);
$user_id = $_SESSION['user_id'];

// Cek status saat ini
$stmt = $conn->prepare("SELECT is_starred FROM files WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $file_id, $user_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$result) {
    header("Location: views/halamanDashboard.php");
    exit;
}

$newStatus = $result['is_starred'] ? 0 : 1;

// Update status
$stmt = $conn->prepare("UPDATE files SET is_starred = ? WHERE id = ? AND user_id = ?");
$stmt->bind_param("iii", $newStatus, $file_id, $user_id);
$stmt->execute();
$stmt->close();

$_SESSION['success'] = $newStatus
    ? 'Ditambahkan ke Berbintang ⭐'
    : 'Dihapus dari Berbintang';

// Redirect kembali ke folder aktif (jika ada)
$redirect = isset($_GET['folder_id']) ? $_GET['folder_id'] : '';
header("Location: views/halamanDashboard.php?folder_id=$redirect");
exit;
