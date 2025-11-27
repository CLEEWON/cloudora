<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/formLogin.php");
    exit;
}

// === Fungsi pembatasan role ===

// Hanya untuk admin
function onlyAdmin() {
    if ($_SESSION['role'] !== 'admin') {
        echo "<script>alert('Akses ditolak! Halaman ini hanya untuk Admin.'); window.location='../user/dashboard.php';</script>";
        exit;
    }
}

// Hanya untuk user biasa
function onlyUser() {
    if ($_SESSION['role'] !== 'user') {
        echo "<script>alert('Akses ditolak! Halaman ini hanya untuk User.'); window.location='../admin/dashboard.php';</script>";
        exit;
    }
}
?>
