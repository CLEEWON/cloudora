<?php
session_start();
require_once "../controllers/b2_authorize.php";
require_once "../config/backblaze.php";
require_once "../db/database.php";

if (!isset($_POST['file_name'])) {
    die("No file specified.");
}

$fileName = $_POST['file_name'];

// Tahap 1: authorize
$auth = b2Authorize();
$apiUrl = $auth['apiUrl'];
$authToken = $auth['authorizationToken'];

// Tahap 2: Hide File dari Backblaze
$payload = [
    "bucketId" => $B2_BUCKET_ID,
    "fileName" => $fileName
];

$ch = curl_init($apiUrl . "/b2api/v2/b2_hide_file");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: $authToken",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$result = json_decode(curl_exec($ch), true);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http !== 200) {
    echo "<h3>Backblaze Hide File Error:</h3>";
    echo "<pre>";
    print_r($result);
    echo "</pre>";
    exit;
}

// Tahap 3: Update database → soft delete status
$stmt = $conn->prepare("UPDATE files SET is_deleted = 1 WHERE user_id = ? AND file_name = ?");
$stmt->bind_param("is", $_SESSION['user_id'], $fileName);
$stmt->execute();
$stmt->close();

$_SESSION['success'] = "File berhasil dihapus !";
header("Location: ../views/halamanDashboard.php");
exit;
?>
