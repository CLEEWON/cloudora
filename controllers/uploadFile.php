<?php
session_start();
require_once "../controllers/b2_authorize.php";
require_once "../config/backblaze.php";
require_once "../db/database.php";

// Validasi file upload
if (!isset($_FILES['file'])) {
    die("No file uploaded.");
}

// Pastikan user login
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized.");
}

$user_id = $_SESSION['user_id'];

// Validasi folder_id (boleh null)
$folder_id = isset($_POST['folder_id']) && $_POST['folder_id'] !== '' && $_POST['folder_id'] !== 'NULL'
    ? intval($_POST['folder_id'])
    : null;

// Ambil data file
$fileName = $_FILES['file']['name'];
$fileTmp  = $_FILES['file']['tmp_name'];
$fileSize = $_FILES['file']['size'];
$fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

// =====================
// Upload ke Backblaze B2
// =====================
$auth = b2Authorize();
$apiUrl = $auth['apiUrl'];
$authToken = $auth['authorizationToken'];

// Get upload URL
$ch = curl_init($apiUrl . "/b2api/v2/b2_get_upload_url");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["bucketId" => $B2_BUCKET_ID]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: $authToken",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$uploadData = json_decode(curl_exec($ch), true);
curl_close($ch);

if (!isset($uploadData['uploadUrl'])) {
    die("Failed to get upload URL from B2.");
}

$uploadUrl   = $uploadData['uploadUrl'];
$uploadToken = $uploadData['authorizationToken'];

// Baca file
$fileData = file_get_contents($fileTmp);
$sha1 = sha1($fileData);

// Upload ke B2
$ch = curl_init($uploadUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: $uploadToken",
    "X-Bz-File-Name: " . rawurlencode($fileName),
    "Content-Type: application/octet-stream",
    "X-Bz-Content-Sha1: $sha1"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $fileData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$result = json_decode(curl_exec($ch), true);
curl_close($ch);

if (!isset($result['fileId'])) {
    echo "<pre>Upload Error:\n";
    print_r($result);
    echo "</pre>";
    exit;
}

$b2_file_id = $result['fileId'];

// =====================
// Simpan salinan lokal untuk preview
// =====================
$localDir = __DIR__ . "/../uploads/";
if (!is_dir($localDir)) mkdir($localDir, 0777, true);

$localFile = $localDir . basename($fileName);

// Gunakan move_uploaded_file agar file temp dipindahkan ke folder uploads
if (!move_uploaded_file($fileTmp, $localFile)) {
    die("Gagal menyimpan salinan file untuk preview.");
}

// =====================
// Simpan metadata ke MySQL
// =====================
$stmt = $conn->prepare("
    INSERT INTO files (user_id, folder_id, original_name, file_name, file_size, file_type, b2_file_id)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");
$stmt->bind_param(
    "iisssis",
    $user_id,
    $folder_id,
    $fileName,
    $fileName,
    $fileSize,
    $fileExt,
    $b2_file_id
);

if (!$stmt->execute()) {
    die("DB Error: " . $stmt->error);
}

$stmt->close();
$conn->close();

// Redirect ke dashboard
$_SESSION['success'] = "Upload file berhasil!";
header("Location: ../views/halamanDashboard.php?folder_id=" . ($folder_id ?? ''));
exit;
?>
