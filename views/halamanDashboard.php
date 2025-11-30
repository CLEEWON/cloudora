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
            <a href="#" class="active"><i class="bi bi-house-door"></i> Beranda</a>
            <a href="halamanBerbintang.php"><i class="bi bi-star"></i> Berbintang</a>
            <a href="halamamPenyimpanan.php"><i class="bi bi-hdd"></i> Penyimpanan</a>
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
          <input type="text" placeholder="Cari file..." id="searchInput">
          <i class="bi bi-search"></i>
      </div>

      <i class="bi bi-person-circle profile-icon" title="<?= htmlspecialchars($_SESSION['nama'] ?? 'User') ?>"></i>
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
                    <a href="../download.php?filename=<?= urlencode($file['file_name']) ?>" class="btn-file btn-download">
                        <i class="bi bi-download"></i>
                    </a>

                    <!-- DELETE -->
                    <form action="../delete.php" method="POST" style="display:inline;">
                        <input type="hidden" name="filename" value="<?= htmlspecialchars($file['file_name']) ?>">
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

<!-- Floating Upload -->
<button class="floating-upload" onclick="document.getElementById('fileInput').click();">
    <i class="bi bi-plus-lg"></i>
</button>

<form action="../upload.php" method="POST" enctype="multipart/form-data" style="display:none;">
    <input type="file" id="fileInput" name="file" onchange="this.form.submit()">
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
</script>

</body>
</html>
