<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/formLogin.php");
    exit;
}

require_once __DIR__ . '/../auth/config.php';
require_once __DIR__ . '/../db/database.php';

// Hitung pemakaian storage user
$stmt = $conn->prepare("SELECT SUM(file_size) AS used FROM files WHERE user_id = ? AND is_deleted = 0");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$usage = $stmt->get_result()->fetch_assoc();
$used = $usage['used'] ?? 0;

$total = 2000 * 1024 * 1024; // 2000MB
$percent = ($used / $total) * 100;
if ($percent > 100) $percent = 100;

// Ambil semua file user
$stmtList = $conn->prepare("SELECT * FROM files WHERE user_id = ? AND is_deleted = 0 ORDER BY upload_date DESC");
$stmtList->bind_param("i", $_SESSION['user_id']);
$stmtList->execute();
$files = $stmtList->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Penyimpanan - Cloudora</title>
  <link rel="stylesheet" href="../assets/css/dashboard.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
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
            <a href="halamanDashboard.php"><i class="bi bi-house-door"></i> Beranda</a>
            <a href="halamanBerbintang.php"><i class="bi bi-star"></i> Berbintang</a>
            <a href="#" class="active"><i class="bi bi-hdd"></i> Penyimpanan</a>
            <a href="halamanSampah.php"><i class="bi bi-trash"></i> Sampah</a>
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
        <div class="search-box">
            <input type="text" placeholder="Cari file...">
            <i class="bi bi-search"></i>
        </div>

        <i class="bi bi-person-circle profile-icon"></i>
    </div>

    <div class="welcome">Penyimpanan</div>

    <!-- STORAGE BOX -->
    <div class="storage-box" style="
        background:#fff;
        padding:20px;
        border-radius:15px;
        box-shadow:0 3px 10px rgba(0,0,0,0.1);
        margin-bottom:30px;">
        
        <div style="font-size:16px; font-weight:bold;">
            <?= round($used / (1024*1024), 2) ?> MB dari 2000 MB digunakan
        </div>

        <div class="bar-bg">
            <div class="bar-fill"
                style="width:<?= $percent ?>%;
                background:<?= $percent > 90 ? '#e74c3c' : ($percent > 70 ? '#f1c40f' : '#f1c40f') ?>">
            </div>
        </div>

    </div>

    <!-- TABLE -->
    <table class="file-table">
        <tr>
            <th>Nama File</th>
            <th>Ukuran</th>
            <th>Tanggal</th>
            <th>Aksi</th>
        </tr>

        <?php foreach ($files as $file): ?>
            <tr class="file-row">
                <td><?= htmlspecialchars($file['original_name']) ?></td>
                <td><?= round($file['file_size']/1024,2) ?> KB</td>
                <td><?= date("d M Y", strtotime($file['upload_date'])) ?></td>
                <td>
                    <a class="btn-file btn-download" 
                       href="../download.php?filename=<?= urlencode($file['file_name']) ?>">
                        <i class="bi bi-download"></i>
                    </a>

                    <form action="../delete.php" method="POST" style="display:inline;">
                        <input type="hidden" name="filename" value="<?= htmlspecialchars($file['file_name']) ?>">
                        <button class="btn-file btn-delete"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

</div>
<script>
document.getElementById("searchInput").addEventListener("keyup", function() {
    const keyword = this.value.toLowerCase();
    const cards = document.querySelectorAll(".file-card, .file-row");

    cards.forEach(item => {
        let text = item.innerText.toLowerCase();
        if (text.includes(keyword)) {
            item.style.display = "";
        } else {
            item.style.display = "none";
       

</body>
</html>
