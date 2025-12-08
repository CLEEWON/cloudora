<?php
session_start();
require_once __DIR__ . '/../db/database.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../halamanDashboard.php");
    exit;
}

// Total user
$users = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];

// Total file
$files = $conn->query("SELECT COUNT(*) AS total FROM files")->fetch_assoc()['total'];

// Total storage digunakan
$storage = $conn->query("SELECT SUM(file_size) AS total FROM files")->fetch_assoc()['total'] ?? 0;
$storageMB = round($storage / 1024 / 1024, 2);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
</head>
<body>

<h2>📊 Admin Dashboard</h2>

<div class="admin-stats">

    <div class="card">
        <h3>Total User</h3>
        <p><?= $users ?></p>
    </div>

    <div class="card">
        <h3>Total File Terunggah</h3>
        <p><?= $files ?></p>
    </div>

    <div class="card">
        <h3>Total Storage Terpakai</h3>
        <p><?= $storageMB ?> MB</p>
    </div>

</div>

</body>
</html>
