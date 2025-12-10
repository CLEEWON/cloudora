<?php
session_start();

// Path mengarah ke folder db
require_once "../db/database.php";
require_once "../controllers/b2_authorize.php"; // Pastikan file ini memang di controllers

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['file_name'])) {
    $_SESSION['error'] = "Invalid request!";
    header("Location: ../views/halamanSampah.php");
    exit;
}

$file_name = trim($_POST['file_name']);

// Cari fileId di database
$stmt = $conn->prepare("SELECT b2_file_id FROM files WHERE file_name = ?");
$stmt->bind_param("s", $file_name);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();

$file_id = $result['b2_file_id'] ?? null;

if (!$file_id) {
    $_SESSION['error'] = "File ID tidak ditemukan, tidak bisa hapus di Backblaze.";
    header("Location: ../views/halamanSampah.php");
    exit;
}

// Backblaze Auth
$auth = b2Authorize();
$apiUrl = $auth['apiUrl'];
$authToken = $auth['authorizationToken'];

$deletePayload = json_encode([
    'fileId'   => $file_id,
    'fileName' => $file_name
]);

$ch = curl_init($apiUrl . "/b2api/v2/b2_delete_file_version");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $deletePayload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: $authToken",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = json_decode(curl_exec($ch), true);
curl_close($ch);

if (isset($response['status'])) {
    $_SESSION['error'] = "Gagal hapus Backblaze: " . ($response['message'] ?? 'Unknown error');
    header("Location: ../views/halamanSampah.php");
    exit;
}

// Hapus database
$stmt = $conn->prepare("DELETE FROM files WHERE file_name = ?");
$stmt->bind_param("s", $file_name);
$stmt->execute();
$stmt->close();

$_SESSION['success'] = "File berhasil dihapus permanen!";
header("Location: ../views/halamanSampah.php");
exit;
