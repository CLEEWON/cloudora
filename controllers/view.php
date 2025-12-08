<?php
if (!isset($_GET['filename'])) {
    die("File tidak ditemukan.");
}

$filename = $_GET['filename'];

require_once __DIR__ . '/controllers/b2_authorize.php';
require_once __DIR__ . '/config/backblaze.php';

// Authorize ulang untuk dapat download URL
$auth = b2Authorize();
$fileUrl = $auth['downloadUrl'] . "/file/$B2_BUCKET_NAME/" . rawurlencode($filename);

// Tentukan tipe file
$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Lihat File - <?= htmlspecialchars($filename) ?></title>
</head>
<body>

<h2>Preview file: <?= htmlspecialchars($filename) ?></h2>

<?php if (in_array($ext, ['jpg','jpeg','png','gif'])): ?>
    <img src="<?= $fileUrl ?>" style="max-width:100%;">

<?php elseif ($ext == 'pdf'): ?>
    <iframe src="<?= $fileUrl ?>" width="100%" height="600px"></iframe>

<?php elseif (in_array($ext, ['mp4', 'webm'])): ?>
    <video controls width="100%">
        <source src="<?= $fileUrl ?>">
    </video>

<?php elseif (in_array($ext, ['mp3','wav'])): ?>
    <audio controls>
        <source src="<?= $fileUrl ?>">
    </audio>

<?php else: ?>
    <p>File tidak bisa di-preview. Klik untuk download:</p>
    <a href="<?= $fileUrl ?>" download>Download File</a>
<?php endif; ?>

</body>
</html>
