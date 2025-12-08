<?php
session_start();
require_once "../controllers/b2_authorize.php";
require_once "../config/backblaze.php";
require_once "../db/database.php";

if (!isset($_FILES['file'])) {
    die("No file uploaded.");
}

// Tahap 1: authorize
$auth = b2Authorize();
$apiUrl = $auth['apiUrl'];
$authToken = $auth['authorizationToken'];

// Tahap 2: get upload URL
$ch = curl_init($apiUrl . "/b2api/v2/b2_get_upload_url");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["bucketId" => $B2_BUCKET_ID]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: $authToken",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$uploadData = json_decode(curl_exec($ch), true);
curl_close($ch);

$uploadUrl = $uploadData['uploadUrl'];
$uploadToken = $uploadData['authorizationToken'];

// Tahap 3: upload file
$fileName = $_FILES['file']['name'];
$fileTmp  = $_FILES['file']['tmp_name'];
$fileSize = $_FILES['file']['size'];
$fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
$fileData = file_get_contents($fileTmp);

$sha1 = sha1($fileData);

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

$response = curl_exec($ch);

if ($response === false) {
    echo "cURL Error: " . curl_error($ch);
    curl_close($ch);
    exit;
}

$result = json_decode($response, true);
curl_close($ch);

// Debug jika API Backblaze kasih error JSON
if (!isset($result['fileId'])) {
    echo "<h3>Backblaze API Error:</h3>";
    echo "<pre>";
    print_r($result);
    echo "</pre>";
    exit;
}


// Jika sukses lanjut simpan DB
$fileUrl = $auth['downloadUrl'] . "/file/$B2_BUCKET_NAME/" . rawurlencode($fileName);

$stmt = $conn->prepare("INSERT INTO files (user_id, original_name, file_name, file_size, file_type) 
                        VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("issis",
    $_SESSION['user_id'],
    $fileName,
    $result['fileName'],
    $fileSize,
    $fileExt
);
$stmt->execute();
$stmt->close();

$_SESSION['success'] = "Upload berhasil!";
header("Location: ../views/halamanDashboard.php");
exit;
