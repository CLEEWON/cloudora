<?php
// Pastikan parameter filename diterima
if (!isset($_GET['filename'])) {
    die("File tidak ditemukan.");
}

$filename = basename($_GET['filename']); // aman dari path traversal
$path = __DIR__ . "/uploads/" . $filename;  // uploads berada di root

// Cek file benar-benar ada
if (!file_exists($path)) {
    die("File tidak ditemukan di server.");
}

// Set header agar file terdownload
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Content-Length: " . filesize($path));

readfile($path);
exit;
