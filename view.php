<?php
if (!isset($_GET['filename'])) {
    die("File tidak ditemukan.");
}

$filename = basename($_GET['filename']);
$path = __DIR__ . "/uploads/" . $filename;

if (!file_exists($path)) {
    die("File tidak ada.");
}

// Deteksi MIME type agar browser bisa menampilkan file
$mime = mime_content_type($path);

header("Content-Type: $mime");
header("Content-Length: " . filesize($path));

// Tampilkan file ke browser
readfile($path);
exit;
