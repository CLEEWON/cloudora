<?php
session_start();
require_once __DIR__ . '/../../db/database.php';

// Pastikan admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: halamanDashboard.php");
    exit;
}

// Validasi ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID user tidak valid.");
}

$user_id = intval($_GET['id']);

// Admin tidak bisa menghapus diri sendiri
if ($user_id == $_SESSION['user_id']) {
    die("Anda tidak dapat menghapus akun sendiri.");
}

// Hapus user
$stmt = $conn->prepare("DELETE FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    $_SESSION['success'] = "User berhasil dihapus.";
} else {
    $_SESSION['error'] = "Gagal menghapus user: " . $stmt->error;
}

$stmt->close();
$conn->close();

header("Location: manageUsers.php");
exit;
