<?php
session_start();
require_once "../db/database.php";

$folder_name = trim($_POST['folder_name']);
$parent_id = isset($_POST['parent_id']) && $_POST['parent_id'] !== '' && $_POST['parent_id'] != 0 
    ? intval($_POST['parent_id']) 
    : null;

$stmt = $conn->prepare("
    INSERT INTO folders (user_id, folder_name, parent_id)
    VALUES (?, ?, ?)
");
$stmt->bind_param("isi", $_SESSION['user_id'], $folder_name, $parent_id);
$stmt->execute();
$stmt->close();

// Redirect: jika root folder jangan ikutkan folder_id
if ($parent_id === null) {
    header("Location: ../views/halamanDashboard.php");
} else {
    header("Location: ../views/halamanDashboard.php?folder_id=$parent_id");
}
exit;
