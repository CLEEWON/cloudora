<?php
session_start();

// Load config & database
require_once __DIR__ . '/auth/config.php';
require_once __DIR__ . '/db/database.php';

// Protect unauthorized access
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/formLogin.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {

    $file = $_FILES['file'];

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error'] = 'Upload gagal. Silakan coba lagi.';
        header("Location: views/halamanDashboard.php");
        exit;
    }

    // Check file size limit
    if ($file['size'] > MAX_FILE_SIZE) {
        $_SESSION['error'] = 'Ukuran file melebihi batas (' . (MAX_FILE_SIZE/1024/1024) . ' MB).';
        header("Location: views/halamanDashboard.php");
        exit;
    }

    // Validate file type
    $fileName = basename($file['name']);
    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (!in_array($extension, ALLOWED_FILE_TYPES)) {
        $_SESSION['error'] = 'Tipe file tidak diperbolehkan.';
        header("Location: views/halamanDashboard.php");
        exit;
    }

    // Create unique file name
    $uniqueName = uniqid() . '_' . $fileName;
    $target = UPLOAD_DIR . $uniqueName;

    // Try moving the file
    if (move_uploaded_file($file['tmp_name'], $target)) {

        // Save record to database
        $stmt = $conn->prepare("
            INSERT INTO files (user_id, original_name, file_name, file_size, file_type, upload_date)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");

        $stmt->bind_param(
            "issis",
            $_SESSION['user_id'],
            $fileName,
            $uniqueName,
            $file['size'],
            $extension
        );

        $stmt->execute();

        $_SESSION['success'] = 'File berhasil diunggah!';
        header("Location: views/halamanDashboard.php");
        exit;

    } else {
        $_SESSION['error'] = 'Gagal menyimpan file.';
        header("Location: views/halamanDashboard.php");
        exit;
    }

} else {
    header("Location: views/halamanDashboard.php");
    exit;
}
?>
