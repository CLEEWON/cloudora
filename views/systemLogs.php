<?php
session_start();
require_once __DIR__ . '/../db/database.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: halamanDashboard.php");
    exit;
}

$logs = $conn->query("SELECT * FROM system_logs ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>System Logs - Cloudora</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div>
        <div class="logo"><img src="../assets/cloud.png"> CLOUDORA</div>

        <div class="menu">
            <a href="halamanDashboard.php"><i class="bi bi-house-door"></i> Beranda</a>
            <a href="halamanBerbintang.php"><i class="bi bi-star"></i> Berbintang</a>
            <a href="halamamPenyimpanan.php"><i class="bi bi-hdd"></i> Penyimpanan</a>
            <a href="halamanSampah.php"><i class="bi bi-trash"></i> Sampah</a>

            <hr>

            <a href="manageUsers.php"><i class="bi bi-people"></i> Manajemen User</a>
            <a href="manageStorage.php"><i class="bi bi-hdd-stack"></i> Manajemen Storage</a>
            <a class="active" href="systemLogs.php"><i class="bi bi-clipboard-data"></i> System Logs</a>
        </div>
    </div>

    <a href="../auth/logout.php" class="logout">
        <i class="bi bi-box-arrow-left"></i> KELUAR
    </a>
</div>

<div class="main">

    <div class="topbar"><h2 style="margin-left:10px;">System Logs</h2></div>

    <div class="content-card">
        <h3>Riwayat Aktivitas</h3>

<div class="table-wrapper">
<table class="admin-table">
    <tr>
        <th>ID</th>
        <th>User</th>
        <th>Aksi</th>
        <th>Waktu</th>
    </tr>

<?php while ($log = $logs->fetch_assoc()): ?>
<tr>
    <td><?= $log['id'] ?></td>
    <td><?= $log['user_email'] ?></td>
    <td><?= $log['action'] ?></td>
    <td><?= $log['created_at'] ?></td>
</tr>
<?php endwhile; ?>
</table>
</div>

    </div>

</div>

</body>
</html>
