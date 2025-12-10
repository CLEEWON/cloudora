<?php
session_start();
require_once "../controllers/b2_authorize.php";
require_once "../config/backblaze.php";
require_once "../db/database.php";

// Validasi
if (!isset($_POST['file_id'])) {
    die("No file specified.");
}

$file_id = intval($_POST['file_id']);
$user_id = $_SESSION['user_id'];

// Ambil data file di DB
$stmt = $conn->prepare("SELECT file_name, b2_file_id FROM files WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $file_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$file = $result->fetch_assoc();
$stmt->close();

if (!$file) {
    die("File not found or not your file.");
}

$fileName = $file['file_name'];
$b2FileId = $file['b2_file_id'];

// Authorize B2
$auth = b2Authorize();
$apiUrl = $auth['apiUrl'];
$authToken = $auth['authorizationToken'];

// Delete di Backblaze (version delete)
$payload = [
    "fileName" => $fileName,
    "fileId"   => $b2FileId
];

$ch = curl_init($apiUrl . "/b2api/v2/b2_delete_file_version");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: $authToken",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$result = json_decode(curl_exec($ch), true);
curl_close($ch);

// Update DB → soft delete
$stmt = $conn->prepare("UPDATE files SET is_deleted = 1 WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $file_id, $user_id);
$stmt->execute();
$stmt->close();

$_SESSION['success'] = "File berhasil dihapus!";
header("Location: ../views/halamanDashboard.php");
exit;
?>
