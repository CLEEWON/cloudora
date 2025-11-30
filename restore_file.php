<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/formLogin.php");
    exit;
}

require_once 'db/database.php';

$filename = basename($_POST['filename']);

$trashDir  = __DIR__ . '/trash/';
$uploadDir = __DIR__ . '/uploads/';

$oldPath = $trashDir . $filename;
$newPath = $uploadDir . $filename;

if (rename($oldPath, $newPath)) {

    $stmt = $conn->prepare("UPDATE files SET is_deleted = 0 WHERE file_name = ?");
    $stmt->bind_param("s", $filename);
    $stmt->execute();

    $_SESSION['success'] = "File berhasil dipulihkan.";

} else {
    $_SESSION['error'] = "Gagal memulihkan file.";
}

header("Location: views/halamanSampah.php");
exit;
