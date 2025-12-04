<?php
session_start();
if($_SESSION['role'] !== 'admin') exit;

require_once "../../db/database.php";

$id = $_GET['id'];

$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

$conn->query("DELETE FROM files WHERE user_id = $id");

header("Location: kelolaUser.php");
exit;
