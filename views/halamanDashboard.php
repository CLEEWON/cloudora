<?php
session_start();

// Folder penyimpanan file
$uploadDir = __DIR__ . '/uploads';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

// Ambil daftar file
$files = array_diff(scandir($uploadDir), ['.', '..']);

// Ambil pesan sukses dari session (jika ada)
$success = $_SESSION['success'] ?? null;
unset($_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CLOUDORA Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root {
      --bg: #0a0a0a;
      --text: #e5e5e5;
      --primary: #c9a961;
      --accent: #1a1a1a;
      --border: rgba(201, 169, 97, 0.2);
      --font-main: 'Times New Roman', Times, serif;
      --red: #E74C3C;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: var(--font-main);
    }

    body {
      display: flex;
      min-height: 100vh;
      background-color: var(--bg);
      color: var(--text);
    }

    /* SIDEBAR */
    .sidebar {
      background-color: var(--accent);
      width: 260px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: 30px 20px;
      border-right: 1px solid var(--border);
      overflow-y: auto;
    }

    .logo {
      display: flex;
      flex-direction: column;
      align-items: center;
      font-weight: 300;
      font-size: 1.3em;
      letter-spacing: 4px;
      margin-bottom: 20px;
      color: var(--primary);
      gap: 12px;
    }

    .logo img {
      width: 70px;
      height: auto;
      object-fit: contain;
      opacity: 0.95;
    }

    .menu a {
      display: flex;
      align-items: center;
      gap: 12px;
      color: var(--text);
      text-decoration: none;
      padding: 12px 16px;
      border-radius: 3px;
      margin-bottom: 8px;
      font-weight: 300;
      font-size: 0.9em;
      transition: all 0.3s ease;
      border: 1px solid transparent;
    }

    .menu a:hover {
      background-color: rgba(201, 169, 97, 0.1);
      border-color: var(--border);
      color: var(--primary);
    }

    .menu a.active {
      background-color: rgba(201, 169, 97, 0.15);
      border-color: var(--primary);
      color: var(--primary);
    }

    .logout {
      background-color: transparent;
      color: var(--primary);
      border: 1.5px solid var(--primary);
      border-radius: 3px;
      padding: 12px 16px;
      text-align: center;
      font-weight: 400;
      font-size: 0.85em;
      letter-spacing: 1px;
      text-decoration: none;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      transition: all 0.3s ease;
      cursor: pointer;
    }

    .logout:hover {
      background-color: var(--primary);
      color: var(--bg);
    }

    /* MAIN CONTENT */
    .main {
      flex: 1;
      background-color: var(--bg);
      display: flex;
      flex-direction: column;
      padding: 30px;
      overflow-y: auto;
      position: relative;
    }

    /* TOPBAR */
    .topbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background-color: var(--accent);
      border-radius: 3px;
      padding: 16px 24px;
      border: 1px solid var(--border);
      margin-bottom: 30px;
      gap: 20px;
    }

    .search-box {
      background-color: rgba(255, 255, 255, 0.05);
      border-radius: 3px;
      display: flex;
      align-items: center;
      padding: 8px 16px;
      flex: 1;
      max-width: 400px;
      border: 1px solid var(--border);
    }

    .search-box input {
      border: none;
      outline: none;
      flex: 1;
      font-size: 0.9em;
      background: transparent;
      color: var(--text);
    }

    .profile-icon {
      font-size: 2em;
      color: var(--primary);
      opacity: 0.8;
      cursor: pointer;
    }

    .welcome {
      font-weight: 300;
      font-size: 1.6em;
      letter-spacing: 2px;
      color: var(--primary);
      margin-bottom: 20px;
    }

    .alert {
      padding: 14px 20px;
      background-color: rgba(201, 169, 97, 0.15);
      border: 1px solid var(--primary);
      border-radius: 3px;
      color: var(--primary);
      font-size: 0.85em;
      margin-bottom: 30px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    /* FILE AREA */
    .file-area {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
      gap: 24px;
      margin-top: 20px;
    }

    .file-card {
      text-align: center;
      padding: 20px 16px;
      background-color: var(--accent);
      border: 1px solid var(--border);
      border-radius: 3px;
      transition: all 0.3s ease;
      cursor: pointer;
      display: flex;
      flex-direction: column;
      gap: 12px;
      min-height: 240px;
      justify-content: space-between;
    }

    .file-card:hover {
      border-color: var(--primary);
      background-color: rgba(201, 169, 97, 0.05);
      transform: translateY(-4px);
      box-shadow: 0 4px 12px rgba(201, 169, 97, 0.1);
    }

    .file-actions {
      display: flex;
      gap: 6px;
      justify-content: center;
    }

    .btn-file {
      background-color: transparent;
      border: 1px solid var(--border);
      color: var(--text);
      padding: 6px 12px;
      font-size: 0.75em;
      border-radius: 3px;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .btn-download {
      border-color: var(--primary);
      color: var(--primary);
    }

    .btn-download:hover {
      background-color: var(--primary);
      color: var(--bg);
    }

    .btn-delete {
      border-color: rgba(231, 76, 60, 0.5);
      color: var(--red);
    }

    .btn-delete:hover {
      background-color: var(--red);
      color: var(--bg);
    }

    /* FLOATING UPLOAD BUTTON */
    .floating-upload {
      position: fixed;
      bottom: 30px;
      right: 30px;
      background-color: var(--primary);
      color: var(--bg);
      border: none;
      border-radius: 50%;
      width: 60px;
      height: 60px;
      font-size: 1.8em;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      box-shadow: 0 4px 12px rgba(201, 169, 97, 0.3);
      transition: all 0.3s ease;
      z-index: 100;
    }

    .floating-upload:hover {
      background-color: #d4b574;
      transform: rotate(90deg) scale(1.1);
    }

    #fileInput {
      display: none;
    }

  </style>
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
        <a href="#"><i class="bi bi-star"></i> Berbintang</a>
        <a href="#"><i class="bi bi-hdd"></i> Penyimpanan</a>
        <a href="#"><i class="bi bi-trash"></i> Sampah</a>
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
        <input type="text" placeholder="Cari file...">
        <i class="bi bi-search"></i>
      </div>
      <i class="bi bi-person-circle profile-icon"></i>
    </div>

    <!-- Welcome -->
    <div class="welcome">Selamat Datang di Cloudora</div>

    <!-- Alert -->
    <?php if ($success): ?>
      <div class="alert">
        <i class="bi bi-check-circle"></i>
        <span><?= htmlspecialchars($success) ?></span>
      </div>
    <?php endif; ?>

    <!-- File Grid -->
    <div class="file-area">
      <?php
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
      ?>

      <?php if (count($files) > 0): ?>
        <?php foreach ($files as $file): ?>
          <?php
          $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
          $fileData = $iconMap[$ext] ?? ['icon' => 'bi-file-earmark-fill', 'color' => '#95A5A6'];
          ?>
          <div class="file-card">
            <div class="file-icon">
              <i class="bi <?= $fileData['icon'] ?>" style="font-size:3em; color:<?= $fileData['color'] ?>;"></i>
            </div>
            <p class="file-name"><?= htmlspecialchars($file) ?></p>
            <div class="file-actions">
              <a href="download.php?filename=<?= urlencode($file) ?>" class="btn-file btn-download"><i class="bi bi-download"></i> Unduh</a>
              <form action="delete.php" method="POST" onsubmit="return confirm('Hapus file ini?');" style="display:inline;">
                <input type="hidden" name="filename" value="<?= htmlspecialchars($file) ?>">
                <button type="submit" class="btn-file btn-delete"><i class="bi bi-trash"></i> Hapus</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #666;">
          <i class="bi bi-folder-open" style="font-size: 3em; opacity: 0.5;"></i><br>
          Belum ada file. Silakan unggah file Anda.
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Floating Upload Button -->
  <button class="floating-upload" onclick="document.getElementById('fileInput').click();" title="Unggah File">
    <i class="bi bi-plus-lg"></i>
  </button>

  <form action="upload.php" method="POST" enctype="multipart/form-data" style="display:none;">
    <input type="file" id="fileInput" name="file" onchange="this.form.submit()">
  </form>

</body>
</html>
