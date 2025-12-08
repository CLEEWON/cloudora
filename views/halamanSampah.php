<?php
// views/halamanSampah.php (versi bersih & diperbaiki)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/formLogin.php");
    exit;
}

require_once __DIR__ . '/../auth/config.php';
require_once __DIR__ . '/../db/database.php';

// Ambil file yang sudah dihapus (is_deleted = 1)
$stmt = $conn->prepare("SELECT * FROM files WHERE user_id = ? AND is_deleted = 1 ORDER BY upload_date DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$files = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Ambil pesan alert
$success = $_SESSION['success'] ?? null;
$error   = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Sampah - Cloudora</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
      /* Perubahan kecil khusus sampah */
      .trash-info p {
          /* background: #f1c40f;   latar kuning */
          color: #f1c40f;           /* teks hitam sesuai permintaan */
          padding: 15px;
          border-radius: 12px;
          margin-bottom: 20px;
          /* box-shadow: 0 3px 10px rgba(0,0,0,0.08); */
          font-size: 19px;
      }

      /* Pastikan search-box lebar itemnya konsisten */
      .topbar .search-box input {
          width: 100%;
      }
    </style>
</head>
<body>

<div class="sidebar">
    <div>
        <div class="logo">
            <img src="../assets/cloud.png" alt="Cloudora Logo">
            CLOUDORA
        </div>

        <div class="menu">

            <a href="halamanDashboard.php"
               class="<?= basename($_SERVER['PHP_SELF']) == 'halamanDashboard.php' ? 'active' : '' ?>">
                <i class="bi bi-house-door"></i> Beranda
            </a>

            <a href="halamanBerbintang.php"
               class="<?= basename($_SERVER['PHP_SELF']) == 'halamanBerbintang.php' ? 'active' : '' ?>">
                <i class="bi bi-star"></i> Berbintang
            </a>

            <a href="halamamPenyimpanan.php"
               class="<?= basename($_SERVER['PHP_SELF']) == 'halamanPenyimpanan.php' ? 'active' : '' ?>">
                <i class="bi bi-hdd"></i> Penyimpanan
            </a>

            <a href="halamanSampah.php"
               class="<?= basename($_SERVER['PHP_SELF']) == 'halamanSampah.php' ? 'active' : '' ?>">
                <i class="bi bi-trash"></i> Sampah
            </a>

            <!-- ADMIN MENU -->
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>

                <hr style="margin: 12px 0; opacity: .3;">

                <a href="manageUsers.php"
                   class="<?= basename($_SERVER['PHP_SELF']) == 'manageUsers.php' ? 'active' : '' ?>">
                    <i class="bi bi-people"></i> Manajemen User
                </a>

                <a href="manageStorage.php"
                   class="<?= basename($_SERVER['PHP_SELF']) == 'manageStorage.php' ? 'active' : '' ?>">
                    <i class="bi bi-hdd-stack"></i> Manajemen Storage
                </a>

                <a href="systemLogs.php"
                   class="<?= basename($_SERVER['PHP_SELF']) == 'systemLogs.php' ? 'active' : '' ?>">
                    <i class="bi bi-clipboard-data"></i> System Logs
                </a>

            <?php endif; ?>

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
            <!-- beri id agar JS bisa target -->
            <input type="text" id="searchInput" placeholder="Cari file...">
            <i class="bi bi-search"></i>
        </div>
        <i class="bi bi-person-circle profile-icon"></i>
    </div>

    <div class="welcome">Sampah</div>

    <?php if ($success): ?>
      <div class="alert alert-success">
          <i class="bi bi-check-circle"></i> <?= htmlspecialchars($success) ?>
      </div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="alert alert-error">
          <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <div class="trash-info">
        <p>File yang dihapus akan tetap berada di sini sampai Anda menghapusnya secara permanen.</p>
    </div>

    <!-- TABEL SAMPAH -->
    <table class="file-table" id="trashTable">
        <thead>
        <tr>
            <th>Nama File</th>
            <th>Ukuran</th>
            <th>Tanggal</th>
            <th>Aksi</th>
        </tr>
        </thead>
        <tbody>
        <?php if (!empty($files)): ?>
            <?php foreach ($files as $file): ?>
                <tr class="file-row">
                    <td class="cell-name"><?= htmlspecialchars($file['original_name']) ?></td>
                    <td class="cell-size"><?= round($file['file_size']/1024,2) ?> KB</td>
                    <td class="cell-date"><?= date("d M Y", strtotime($file['upload_date'])) ?></td>
                    <td class="cell-action">

<form action="../controllers/restoreFile.php" method="POST" style="display:inline;">
    <input type="hidden" name="file_name" value="<?= htmlspecialchars($file['file_name']) ?>">
    <button class="btn-file btn-restore">
        <i class="bi bi-arrow-counterclockwise"></i>
    </button>
</form>


                        <!-- Delete Permanently -->
                        <form action="../delete_permanent.php" method="POST" style="display:inline;">
                            <input type="hidden" name="filename" value="<?= htmlspecialchars($file['file_name']) ?>">
                            <button type="submit" class="btn-file btn-delete" title="Hapus Permanen" onclick="return confirm('Hapus permanen file ini?');">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </form>

                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="4" style="text-align:center; padding:25px;">
                    <i class="bi bi-folder-x" style="font-size:30px; opacity:0.4;"></i><br>
                    Tidak ada file di sampah
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

</div>

<!-- Search JS: cocok untuk file-card dan tabel -->
<script>
(function(){
  const input = document.getElementById('searchInput');
  if (!input) return;

  input.addEventListener('input', function() {
    const q = this.value.trim().toLowerCase();

    // cari di baris tabel (sampah menggunakan table)
    const rows = document.querySelectorAll('#trashTable tbody tr');
    rows.forEach(r => {
      // text gabungan dari sel yang relevan
      const text = (r.innerText || '').toLowerCase();
      if (q === '' || text.includes(q)) {
        r.style.display = '';
      } else {
        r.style.display = 'none';
      }
    });

    // juga support file-card apabila ada di halaman lain
    const cards = document.querySelectorAll('.file-card');
    cards.forEach(c => {
      const text = (c.innerText || '').toLowerCase();
      c.style.display = (q === '' || text.includes(q)) ? '' : 'none';
    });
  });
})();
</script>
</body>
</html>
