<?php
session_start();
if($_SESSION['role'] !== 'admin') exit;

require_once "../../db/database.php";

$id = $_POST['id'];
$nama = $_POST['nama'];
$role = $_POST['role'];
$limitMB = $_POST['storage_limit'] * 1024 * 1024;

$stmt = $conn->prepare("UPDATE users SET nama = ?, role = ?, storage_limit = ? WHERE id = ?");
$stmt->bind_param("ssii", $nama, $role, $limitMB, $id);
$stmt->execute();

header("Location: kelolaUser.php");
exit;
