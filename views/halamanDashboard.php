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

// Folder aktif saat ini
$current_folder_id = isset($_GET['folder_id']) && $_GET['folder_id'] !== '' 
    ? intval($_GET['folder_id']) 
    : null;

// Ambil Folder sesuai parent
$queryFolders = "
    SELECT * FROM folders 
    WHERE user_id = ? AND parent_id " . ($current_folder_id === null ? "IS NULL" : "= ?");
$stmtFolders = $conn->prepare($queryFolders);
if ($current_folder_id === null) {
    $stmtFolders->bind_param("i", $_SESSION['user_id']);
} else {
    $stmtFolders->bind_param("ii", $_SESSION['user_id'], $current_folder_id);
}
$stmtFolders->execute();
$folders = $stmtFolders->get_result()->fetch_all(MYSQLI_ASSOC);

// Ambil File sesuai folder
$queryFiles = "
    SELECT * FROM files 
    WHERE user_id = ? AND is_deleted = 0 AND folder_id " . ($current_folder_id === null ? "IS NULL" : "= ?") . "
    ORDER BY upload_date DESC";
$stmtFiles = $conn->prepare($queryFiles);
if ($current_folder_id === null) {
    $stmtFiles->bind_param("i", $_SESSION['user_id']);
} else {
    $stmtFiles->bind_param("ii", $_SESSION['user_id'], $current_folder_id);
}
$stmtFiles->execute();
$files = $stmtFiles->get_result()->fetch_all(MYSQLI_ASSOC);

// Alerts
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

<style>
/* (Floating button styles tetap sama dari kode kamu sebelumnya — dipertahankan) */
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
            <a href="halamanDashboard.php" class="active">
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

                        <!-- MENU ADMIN -->
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <hr style="margin:10px 0; opacity:0.3;">

                <a href="manageUsers.php"><i class="bi bi-people"></i> Manajemen User</a>
                <a href="manageStorage.php"><i class="bi bi-hdd-stack"></i> Manajemen Storage</a>
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
                <hr>
                <a href="../auth/logout.php" class="logout-btn"><i class="bi bi-box-arrow-right"></i> Keluar</a>
            </div>
        </div>
    </div>

    <div class="welcome">Hai, <?= htmlspecialchars($_SESSION['nama']) ?></div>

    <!-- Alerts -->
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="file-area" id="fileGrid">

<!-- ================= Folder List ================= -->
<?php foreach ($folders as $folder): ?>
    <a href="halamanDashboard.php?folder_id=<?= $folder['id'] ?>" class="file-card folder-card">
        <div class="file-icon">
            <i class="bi bi-folder-fill" style="font-size:3em; color:#ffffff"></i>
        </div>
        <p class="file-name"><?= htmlspecialchars($folder['folder_name']) ?></p>
        <div class="file-info">Dibuat: <?= date("d M Y", strtotime($folder['created_at'])) ?></div>
    </a>
<?php endforeach; ?>

<!-- ================= File List ================= -->
<?php if (count($files) > 0): ?>
    <?php foreach ($files as $file): 

        $ext = strtolower($file['file_type']);
        $icons = [
            'pdf'=>['bi-file-earmark-pdf-fill', '#E74C3C'],
            'jpg'=>['bi-file-earmark-image-fill', '#9B59B6'],
            'jpeg'=>['bi-file-earmark-image-fill', '#9B59B6'],
            'png'=>['bi-file-earmark-image-fill', '#9B59B6'],
            'gif'=>['bi-file-earmark-image-fill', '#9B59B6'],
            'txt'=>['bi-file-earmark-text-fill', '#95A5A6'],
            'mp4'=>['bi-file-earmark-play-fill', '#E91E63'],
            'mp3'=>['bi-file-earmark-music-fill', '#3498DB'],
            'zip'=>['bi-file-earmark-zip-fill', '#F39C12'],
            'rar'=>['bi-file-earmark-zip-fill', '#F39C12']
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

                <a href="../download.php?id=<?= $file['id'] ?>" class="btn-file btn-download">
                    <i class="bi bi-download"></i>
                </a>

                <form action="../controllers/softDeleteFile.php" method="POST" style="display:inline;">
                    <input type="hidden" name="file_id" value="<?= $file['id'] ?>">
                    <button class="btn-file btn-delete"><i class="bi bi-trash"></i></button>
                </form>

                <form action="../toggle_star.php" method="POST" style="display:inline;">
                    <input type="hidden" name="file_id" value="<?= $file['id'] ?>">
                    <button class="btn-file btn-star <?= $file['is_starred'] ? 'active' : '' ?>">
                        <i class="bi bi-star<?= $file['is_starred'] ? '-fill' : '' ?>"></i>
                    </button>
                </form>


<a href="../view.php?filename=<?= $file['file_name'] ?>" target="_blank" class="btn-file btn-view">
    <i class="bi bi-eye"></i>
</a>

            </div>
        </div>

    <?php endforeach; ?>

<?php else: ?>
    <div style="text-align:center; grid-column:1 / -1;">
        <i class="bi bi-folder-open" style="font-size:3em; opacity:.5;"></i><br>
        Belum ada file di folder ini.
    </div>
<?php endif; ?>

    </div>

</div>


<!-- UPLOAD FORM HIDDEN -->
<form id="uploadForm" action="../controllers/uploadFile.php" method="POST" enctype="multipart/form-data" style="display:none;">
    <input type="hidden" name="folder_id" value="<?= $current_folder_id ?? '' ?>">
    <input type="file" id="fileInput" name="file" onchange="document.getElementById('uploadForm').submit();">
</form>

<!-- Floating Buttons -->
<label for="fileInput" class="floating-upload"><i class="bi bi-plus-lg"></i></label>
<button class="floating-folder" type="button" onclick="openFolderModal()"><i class="bi bi-folder-plus"></i></button>


<!-- Modal Create Folder -->
<div id="folderModal" class="modal-overlay">
    <div class="modal-box">
        <h3>Buat Folder Baru</h3>

        <form action="../controllers/createFolder.php" method="POST">
            <input type="hidden" name="parent_id" value="<?= $current_folder_id ?? '' ?>">
            <input type="text" name="folder_name" placeholder="Nama folder..." required>

            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeFolderModal()">Batal</button>
                <button type="submit" class="btn-create">Buat</button>
            </div>
        </form>
    </div>
</div>

<script>
// Search
document.getElementById("searchInput").addEventListener("keyup", function() {
    const keyword = this.value.toLowerCase();
    document.querySelectorAll(".file-card").forEach(card => {
        card.style.display = card.innerText.toLowerCase().includes(keyword) ? "" : "none";
    });
});

// Profile toggle
const btn = document.getElementById("profileBtn");
const popup = document.getElementById("profilePopup");
btn.onclick = (e)=>{e.stopPropagation(); popup.style.display = popup.style.display === "block" ? "none" : "block"; };
document.addEventListener("click", ()=> popup.style.display = "none");

// Modal
function openFolderModal(){ document.getElementById("folderModal").style.display="flex"; }
function closeFolderModal(){ document.getElementById("folderModal").style.display="none"; }
</script>

</body>
</html>
