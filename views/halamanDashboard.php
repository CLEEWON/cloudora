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

// Ambil semua file user
$stmt = $conn->prepare("SELECT * FROM files WHERE user_id = ? AND is_deleted = 0 ORDER BY upload_date DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$files = $result->fetch_all(MYSQLI_ASSOC);

// Alert session
$success = $_SESSION['success'] ?? null;
$error   = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CLOUDORA Dashboard</title>
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

            <!-- Dashboard -->
            <a href="halamanDashboard.php" 
               class="<?= basename($_SERVER['PHP_SELF']) == 'halamanDashboard.php' ? 'active' : '' ?>">
                <i class="bi bi-house-door"></i> Beranda
            </a>

            <!-- Berbintang -->
            <a href="halamanBerbintang.php" 
               class="<?= basename($_SERVER['PHP_SELF']) == 'halamanBerbintang.php' ? 'active' : '' ?>">
                <i class="bi bi-star"></i> Berbintang
            </a>

            <!-- Penyimpanan -->
            <a href="halamamPenyimpanan.php" 
               class="<?= basename($_SERVER['PHP_SELF']) == 'halamanPenyimpanan.php' ? 'active' : '' ?>">
                <i class="bi bi-hdd"></i> Penyimpanan
            </a>

            <!-- Sampah -->
            <a href="halamanSampah.php" 
               class="<?= basename($_SERVER['PHP_SELF']) == 'halamanSampah.php' ? 'active' : '' ?>">
                <i class="bi bi-trash"></i> Sampah
            </a>

            <!-- ADMIN ONLY MENU -->
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <hr style="margin: 10px 0; opacity: .3;">

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
          <input type="text" placeholder="Cari file..." id="searchInput">
          <i class="bi bi-search"></i>
      </div>

<div class="profile-container">
    <i class="bi bi-person-circle profile-icon" id="profileBtn"></i>

    <div class="profile-popup" id="profilePopup">
        <p><strong><?= htmlspecialchars($_SESSION['nama']) ?></strong></p>
        <p>Email: <?= htmlspecialchars($_SESSION['email']) ?></p>
<div> 
    <?= htmlspecialchars($_SESSION['created_at'] ?? 'Tidak tersedia'); ?>
</div>

<div>
    <?= ($_SESSION['storage_limit'] ?? 0) . ' MB'; ?>
</div>

        <hr>
        <a href="../auth/logout.php" class="logout-btn"><i class="bi bi-box-arrow-right"></i> Keluar</a>
    </div>
</div>
  </div>

  <div class="welcome">
      Hai, <?= htmlspecialchars($_SESSION['nama'] ?? 'User') ?>
  </div>

  <!-- Alerts -->
  <?php if ($success): ?>
      <div class="alert alert-success"><i class="bi bi-check-circle"></i> <?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <?php if ($error): ?>
      <div class="alert alert-error"><i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <!-- FILE GRID -->
  <div class="file-area" id="fileGrid">

    <?php if (count($files) > 0): ?>
        <?php foreach ($files as $file): ?>

            <?php
            $ext = strtolower($file['file_type']);
            $icons = [
                'pdf' => ['bi-file-earmark-pdf-fill', '#E74C3C'],
                'jpg' => ['bi-file-earmark-image-fill', '#9B59B6'],
                'jpeg' => ['bi-file-earmark-image-fill', '#9B59B6'],
                'png' => ['bi-file-earmark-image-fill', '#9B59B6'],
                'gif' => ['bi-file-earmark-image-fill', '#9B59B6'],
                'txt' => ['bi-file-earmark-text-fill', '#95A5A6'],
                'mp4' => ['bi-file-earmark-play-fill', '#E91E63'],
                'mp3' => ['bi-file-earmark-music-fill', '#3498DB'],
                'zip' => ['bi-file-earmark-zip-fill', '#F39C12'],
                'rar' => ['bi-file-earmark-zip-fill', '#F39C12']
            ];
            $data = $icons[$ext] ?? ['bi-file-earmark-fill', '#95A5A6'];
            ?>

            <div class="file-card">
                <div class="file-icon">
                    <i class="bi <?= $data[0] ?>" style="font-size:3em; color:<?= $data[1] ?>"></i>
                </div>

                <p class="file-name"><?= htmlspecialchars($file['original_name']) ?></p>

                <div class="file-info">
                    <?= round($file['file_size']/1024, 2) ?> KB<br>
                    <?= date("d M Y", strtotime($file['upload_date'])) ?>
                </div>

                <div class="file-actions">

                    <!-- DOWNLOAD -->
<a href="../download.php?id=<?= $file['id'] ?>" class="btn-file btn-download">
    <i class="bi bi-download"></i>
</a>



                    <!-- DELETE -->
                    <form action="../controllers/softDeleteFile.php" method="POST" style="display:inline;">
                        <input type="hidden" name="file_name" value="<?= htmlspecialchars($file['file_name']) ?>">
                        <button class="btn-file btn-delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>

                    <!-- STAR -->
                    <form action="../toggle_star.php" method="POST" style="display:inline;">
                        <input type="hidden" name="filename" value="<?= htmlspecialchars($file['file_name']) ?>">
                        <button type="submit" class="btn-file btn-star <?= $file['is_starred'] ? 'active' : '' ?>">
                            <i class="bi bi-star<?= $file['is_starred'] ? '-fill' : '' ?>"></i>
                        </button>
                    </form>

                    <!-- VIEW -->
                    <a href="../view.php?filename=<?= urlencode($file['file_name']) ?>" 
                    target="_blank" 
                    class="btn-file btn-view">
    <i class="bi bi-eye"></i>
</a>


                </div>
            </div>

        <?php endforeach; ?>

    <?php else: ?>

        <div style="text-align:center; grid-column:1 / -1;">
            <i class="bi bi-folder-open" style="font-size:3em; opacity:.5;"></i><br>
            Belum ada file. Silakan unggah file.
        </div>

    <?php endif; ?>
  </div>

</div>

<!-- Floating Upload (Button + Form Menyatu) -->
<form id="uploadForm" action="../controllers/uploadFile.php" method="POST" enctype="multipart/form-data">

    <!-- Tombol Mengambang -->
    <label for="fileInput" class="floating-upload">
        <i class="bi bi-plus-lg"></i>
    </label>

    <!-- Input tersembunyi -->
    <input type="file" id="fileInput" name="file" style="display:none;"
           onchange="document.getElementById('uploadForm').submit();">

</form>



<!-- SEARCH BAR SCRIPT -->
<script>
document.getElementById("searchInput").addEventListener("keyup", function() {
    const keyword = this.value.toLowerCase();
    const items = document.querySelectorAll(".file-card");

    items.forEach(card => {
        let text = card.innerText.toLowerCase();
        card.style.display = text.includes(keyword) ? "" : "none";
    });
});

// =============== PROFILE POPUP SCRIPT (BENAR) =================
const btn = document.getElementById("profileBtn");
const popup = document.getElementById("profilePopup");

btn.addEventListener("click", (e) => {
    e.stopPropagation();
    popup.style.display = popup.style.display === "block" ? "none" : "block";
});

// Klik di luar → popup tertutup
document.addEventListener("click", function(e) {
    if (!popup.contains(e.target)) {
        popup.style.display = "none";
    }
});
</script>


</body>
</html>
