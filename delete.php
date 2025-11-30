<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/formLogin.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['filename'])) {
    $_SESSION['error'] = 'Invalid request.';
    header("Location: views/halamanDashboard.php");
    exit;
}

$filename = basename($_POST['filename']);

require_once 'db/database.php';

// Pastikan file milik user
$stmt = $conn->prepare("SELECT id, user_id FROM files WHERE file_name = ?");
$stmt->bind_param("s", $filename);
$stmt->execute();
$fileData = $stmt->get_result()->fetch_assoc();

if (!$fileData) {
    $_SESSION['error'] = 'File not found.';
    header("Location: views/halamanDashboard.php");
    exit;
}

// Cek kepemilikan
if ($fileData['user_id'] != $_SESSION['user_id']) {
    $_SESSION['error'] = 'Not allowed.';
    header("Location: views/halamanDashboard.php");
    exit;
}

// UPDATE: Pindahkan ke sampah (bukan hapus)
$update = $conn->prepare("UPDATE files SET is_deleted = 1 WHERE file_name = ?");
$update->bind_param("s", $filename);

if ($update->execute()) {
    $_SESSION['success'] = 'File berhasil dipindahkan ke Sampah.';
} else {
    $_SESSION['error'] = 'Gagal memindahkan file.';
}

header("Location: views/halamanDashboard.php");
exit;
?>
