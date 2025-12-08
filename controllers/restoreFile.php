<?php
session_start();
require_once "../db/database.php";

if (!isset($_POST['file_name'])) {
    die("No file specified to restore.");
}

$fileName = $_POST['file_name'];

$stmt = $conn->prepare("UPDATE files SET is_deleted = 0 WHERE user_id = ? AND file_name = ?");
$stmt->bind_param("is", $_SESSION['user_id'], $fileName);
$stmt->execute();
$stmt->close();

// Notifikasi dan redirect
$_SESSION['success'] = "File berhasil dikembalikan!";
header("Location: ../views/halamanDashboard.php");
exit;
?>
