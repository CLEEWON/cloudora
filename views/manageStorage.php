<?php
session_start();
require_once __DIR__ . '/../db/database.php';

// proteksi: hanya admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: halamanDashboard.php");
    exit;
}

// --------------------------------------------------
// QUERY: hitung pemakaian storage per user (dari tabel files)
// --------------------------------------------------
$query = "
    SELECT 
        u.id,
        u.nama,
        u.email,
        u.storage_limit,
        COALESCE(SUM(f.file_size), 0) AS used_storage
    FROM users u
    LEFT JOIN files f 
        ON u.id = f.user_id 
        AND f.is_deleted = 0
    GROUP BY u.id
    ORDER BY used_storage DESC
";

// jalankan query dan cek error
$result = $conn->query($query);
if (!$result) {
    die("Query Error (manageStorage): " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Storage - Cloudora</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        /* tambahan style ringkas jika belum ada di css */
        .admin-table { width:100%; border-collapse: collapse; margin-top:10px; }
        .admin-table th { background:#f5f7fb; padding:12px; text-align:left; font-weight:600; }
        .admin-table td { padding:12px; border-bottom:1px solid #eee; }
        .table-wrapper { padding:12px; background:#fff; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,.03); }
    </style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div>
        <div class="logo"><img src="../assets/cloud.png" alt="Cloudora Logo"> CLOUDORA</div>

        <div class="menu">
            <a href="halamanDashboard.php"><i class="bi bi-house-door"></i> Beranda</a>
            <a href="halamanBerbintang.php"><i class="bi bi-star"></i> Berbintang</a>
            <a href="halamanPenyimpanan.php"><i class="bi bi-hdd"></i> Penyimpanan</a>
            <a href="halamanSampah.php"><i class="bi bi-trash"></i> Sampah</a>

            <hr>

            <a href="manageUsers.php"><i class="bi bi-people"></i> Manajemen User</a>
            <a class="active" href="manageStorage.php"><i class="bi bi-hdd-stack"></i> Manajemen Storage</a>
        </div>
    </div>

    <a href="../auth/logout.php" class="logout">
        <i class="bi bi-box-arrow-left"></i> KELUAR
    </a>
</div>

<!-- MAIN -->
<div class="main">
    <div class="topbar"><h2 style="margin-left:10px;">Manajemen Storage</h2></div>

    <div class="content-card">
        <h3>Pengaturan Kapasitas Penyimpanan</h3>

        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Terpakai</th>
                        <th>Batas Storage</th>
                        <th>Persentase</th>
                    </tr>
                </thead>

                <tbody>
                    <?php while ($row = $result->fetch_assoc()): 
                        // NOTE:
                        // - asumsikan file_size disimpan dalam byte (umumnya). 
                        // - asumsikan storage_limit di kolom users disimpan dalam MB (sesuaikan jika berbeda).
                        $usedMB = round($row['used_storage'] / 1024 / 1024, 2); // bytes -> MB
                        $limitMB = (float)$row['storage_limit']; // jika storage_limit dalam MB. Jika dalam bytes, ubah konversinya.
                        $percent = ($limitMB > 0) ? round(($usedMB / $limitMB) * 100, 2) : 0;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($row['nama']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= $usedMB ?> MB</td>
                        <td><?= $limitMB ?> MB</td>
                        <td style="min-width:200px;">
                            <div style="width:100%; background:#eee; height:10px; border-radius:5px; overflow:hidden;">
                                <div style="
                                    width:<?= min($percent,100) ?>%; 
                                    height:100%; 
                                    background:<?= $percent > 80 ? '#e74c3c' : '#3498db' ?>;
                                "></div>
                            </div>
                            <span style="font-size:12px; color:#555;"><?= $percent ?>%</span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

</body>
</html>
