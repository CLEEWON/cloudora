<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Jika belum login → redirect
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/formLogin.php");
    exit;
}

require_once __DIR__ . '/../auth/config.php';
require_once __DIR__ . '/../db/database.php';

// Ambil file berbintang user
$stmt = $conn->prepare("SELECT * FROM files WHERE user_id = ? AND is_starred = 1 ORDER BY upload_date DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$files = $result->fetch_all(MYSQLI_ASSOC);

// Ambil pesan alert
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
    <a href="halamanDashboard.php"><i class="bi bi-house-door"></i> Beranda</a>
    <a href="halamanBerbintang.php" class="active"><i class="bi bi-star"></i> Berbintang</a>
    <a href="halamamPenyimpanan.php"><i class="bi bi-hdd"></i> Penyimpanan</a>
    <a href="halamanSampah.php"><i class="bi bi-trash"></i> Sampah</a>
</div>

    </div>

    <a href="../auth/logout.php" class="logout">
      <i class="bi bi-box-arrow-left"></i> KELUAR
    </a>
  </div>                                    

  <!-- MAIN CONTENT -->
  <div class="main">

    <!-- TOPBAR -->
    <div class="topbar">
      <div class="search-box">
        <input type="text" placeholder="Cari file..." id="searchInput">
        <i class="bi bi-search"></i>
      </div>
      <i class="bi bi-person-circle profile-icon" title="<?= htmlspecialchars($_SESSION['nama'] ?? 'User') ?>"></i>
    </div>

    <!-- Welcome -->
    <div class="welcome">File Favorit, <?= htmlspecialchars($_SESSION['nama'] ?? 'User') ?></div>

    <!-- Alerts -->
    <?php if ($success): ?>
      <div class="alert alert-success">
        <i class="bi bi-check-circle"></i>
        <span><?= htmlspecialchars($success) ?></span>
      </div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="alert alert-error">
        <i class="bi bi-exclamation-triangle"></i>
        <span><?= htmlspecialchars($error) ?></span>
      </div>
    <?php endif; ?>

    <!-- File Grid -->
    <div class="file-area" id="fileGrid">
      <?php if (count($files) > 0): ?>
        <?php foreach ($files as $file): ?>
          <?php
          $ext = strtolower($file['file_type']);
          $iconMap = [
            'pdf' => ['icon' => 'bi-file-earmark-pdf-fill', 'color' => '#E74C3C'],
            'jpg' => ['icon' => 'bi-file-earmark-image-fill', 'color' => '#9B59B6'],
            'jpeg' => ['icon' => 'bi-file-earmark-image-fill', 'color' => '#9B59B6'],
            'png' => ['icon' => 'bi-file-earmark-image-fill', 'color' => '#9B59B6'],
            'gif' => ['icon' => 'bi-file-earmark-image-fill', 'color' => '#9B59B6'],
            'txt' => ['icon' => 'bi-file-earmark-text-fill', 'color' => '#95A5A6'],
            'mp4' => ['icon' => 'bi-file-earmark-play-fill', 'color' => '#E91E63'],
            'mp3' => ['icon' => 'bi-file-earmark-music-fill', 'color' => '#3498DB'],
            'zip' => ['icon' => 'bi-file-earmark-zip-fill', 'color' => '#F39C12'],
            'rar' => ['icon' => 'bi-file-earmark-zip-fill', 'color' => '#F39C12'],
          ];

          $fileData = $iconMap[$ext] ?? ['icon' => 'bi-file-earmark-fill', 'color' => '#95A5A6'];
          ?>
          <div class="file-card">
            <div class="file-icon">
              <i class="bi <?= $fileData['icon'] ?>" style="font-size:3em; color:<?= $fileData['color'] ?>;"></i>
            </div>
            <p class="file-name"><?= htmlspecialchars($file['original_name']) ?></p>
            <div class="file-info">
              <?= round($file['file_size']/1024, 2) ?> KB<br>
              <?= date('d M Y', strtotime($file['upload_date'])) ?>
            </div>
            <div class="file-actions">
              <a href="../download.php?filename=<?= urlencode($file['file_name']) ?>" class="btn-file btn-download"><i class="bi bi-download"></i> Unduh</a>
              <form action="../delete.php" method="POST" onsubmit="return confirm('Hapus file ini?');">
                <input type="hidden" name="filename" value="<?= htmlspecialchars($file['file_name']) ?>">
                <button type="submit" class="btn-file btn-delete"><i class="bi bi-trash"></i> Hapus</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div style="grid-column: 1 / -1; text-align: center;">
          <i class="bi bi-folder-open" style="font-size: 3em; opacity: 0.5;"></i><br>
          Belum ada file. Silakan unggah file Anda.
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Upload -->
  <button class="floating-upload" onclick="document.getElementById('fileInput').click();">
    <i class="bi bi-plus-lg"></i>
  </button>

  <form action="../upload.php" method="POST" enctype="multipart/form-data" style="display:none;">
    <input type="file" id="fileInput" name="file" onchange="this.form.submit()">
  </form>
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
