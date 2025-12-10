<?php
session_start();

// Cek login
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access");
}

// Cek parameter filename
if (!isset($_GET['filename']) || empty($_GET['filename'])) {
    die("File tidak ditemukan");
}

// Ambil nama file dan bersihkan path
$filename = basename($_GET['filename']); // untuk keamanan
$file_path = __DIR__ . "/uploads/" . $filename;

// Cek apakah file ada di server
if (!file_exists($file_path)) {
    die("File tidak ditemukan di server. Periksa folder uploads!");
}

// Set header sesuai tipe file agar bisa preview di browser
$mime = mime_content_type($file_path);
header("Content-Type: $mime");
header("Content-Disposition: inline; filename=\"" . $filename . "\"");

// Tampilkan file
readfile($file_path);
exit;
?>
