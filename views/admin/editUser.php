<?php
session_start();
if($_SESSION['role'] !== 'admin') exit;

require_once "../../db/database.php";

$id = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<form action="updateUser.php" method="POST">
    <input type="hidden" name="id" value="<?= $user['id'] ?>">

    Nama: <input type="text" name="nama" value="<?= $user['nama'] ?>"><br>
    Role:
    <select name="role">
        <option value="user"  <?= $user['role']=='user'?'selected':'' ?>>User</option>
        <option value="admin" <?= $user['role']=='admin'?'selected':'' ?>>Admin</option>
    </select>
    <br>

    Batas Penyimpanan (MB):
    <input type="number" name="storage_limit" value="<?= $user['storage_limit'] / (1024*1024) ?>">

    <button type="submit">Simpan</button>
</form>
