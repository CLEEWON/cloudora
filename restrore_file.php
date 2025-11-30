<?php
session_start();
require_once 'auth/config.php';
require_once 'db/database.php';

$filename = $_POST['filename'] ?? null;

if ($filename) {
    $stmt = $conn->prepare("UPDATE files SET is_deleted = 0 WHERE file_name = ? AND user_id = ?");
    $stmt->bind_param("si", $filename, $_SESSION['user_id']);
    $stmt->execute();

    $_SESSION['success'] = "File berhasil dipulihkan!";
}

header("Location: views/halamanSampah.php");
exit;
