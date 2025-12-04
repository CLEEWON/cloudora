<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/formLogin.php");
    exit;
}

require_once "../../db/database.php";

$result = $conn->query("SELECT id, nama, email, role, storage_limit FROM users ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kelola User - Admin</title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
</head>

<body>

<h2>Kelola User</h2>

<table class="file-table">
    <tr>
        <th>Nama</th>
        <th>Email</th>
        <th>Role</th>
        <th>Batas Storage</th>
        <th>Aksi</th>
    </tr>

    <?php while ($u = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $u['nama'] ?></td>
        <td><?= $u['email'] ?></td>
        <td><?= $u['role'] ?></td>
        <td><?= round($u['storage_limit'] / (1024*1024)) ?> MB</td>
        <td>
            <a href="editUser.php?id=<?= $u['id'] ?>" class="btn-file">Edit</a>
            <a href="hapusUser.php?id=<?= $u['id'] ?>" class="btn-file btn-delete"
                onclick="return confirm('Hapus user ini?');">Hapus</a>
        </td>
    </tr>
    <?php endwhile; ?>

</table>

</body>
</html>
