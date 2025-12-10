<?php
session_start();
require_once __DIR__ . '/../db/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: halamanDashboard.php");
    exit;
}

$users = $conn->query("SELECT * FROM users ORDER BY id ASC");

$query = "SELECT id, nama, email, role, status, created_at, updated_at, storage_limit
          FROM users ORDER BY created_at DESC";

$result = $conn->query($query);

if (!$result) {
    die("Query Error: " . $conn->error);
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen User - Cloudora</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div>
        <div class="logo">
            <img src="../assets/cloud.png" alt="Cloudora Logo">
            CLOUDORA
        </div>

        <div class="menu">

            <a href="halamanDashboard.php">
                <i class="bi bi-house-door"></i> Beranda
            </a>

            <a href="halamanBerbintang.php">
                <i class="bi bi-star"></i> Berbintang
            </a>

            <a href="halamamPenyimpanan.php">
                <i class="bi bi-hdd"></i> Penyimpanan
            </a>

            <a href="halamanSampah.php">
                <i class="bi bi-trash"></i> Sampah
            </a>

            <!-- Admin -->
            <hr>

            <a class="active" href="manageUsers.php">
                <i class="bi bi-people"></i> Manajemen User
            </a>

            <a href="manageStorage.php">
                <i class="bi bi-hdd-stack"></i> Manajemen Storage
            </a>


        </div>
    </div>

    <a href="../auth/logout.php" class="logout">
        <i class="bi bi-box-arrow-left"></i> KELUAR
    </a>
</div>

<!-- MAIN -->
<div class="main">

    <!-- TOPBAR -->
    <div class="topbar">
        <h2 style="margin-left: 10px;">Manajemen User</h2>
    </div>

    <div class="content-card">
        <h3>Daftar Pengguna</h3>

<div class="table-wrapper">
<table class="admin-table">
    <tr>
        <th>ID</th>
        <th>Nama</th>
        <th>Email</th>
        <th>Role</th>
        <th>Status</th>
        <th>Storage</th>
        <th>Aksi</th>
    </tr>

    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['nama']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
        <td><?= $row['role'] ?></td>
        <td><?= $row['status'] ?></td>
        <td><?= number_format($row['storage_limit']) ?> MB</td>
        <td>
 <a href="admin/editUser.php?id=<?= $row['id'] ?>" class="btn-edit">Edit</a>
<a href="admin/deleteUser.php?id=<?= $row['id'] ?>" class="btn-delete" onclick="return confirm('Yakin ingin menghapus user ini?');">Hapus</a>

        </td>
    </tr>
    <?php endwhile; ?>
</table>
</div>


</div>
</body>
</html>
