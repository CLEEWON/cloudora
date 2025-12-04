<?php
/**
 * Helper Functions untuk Cloudora
 */

// Format file size
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

// Get file icon & color
function getFileIcon($extension) {
    $icons = [
        'pdf' => ['icon' => 'bi-file-earmark-pdf-fill', 'color' => '#E74C3C'],
        'jpg' => ['icon' => 'bi-file-earmark-image-fill', 'color' => '#9B59B6'],
        'jpeg' => ['icon' => 'bi-file-earmark-image-fill', 'color' => '#9B59B6'],
        'png' => ['icon' => 'bi-file-earmark-image-fill', 'color' => '#9B59B6'],
        'gif' => ['icon' => 'bi-file-earmark-image-fill', 'color' => '#9B59B6'],
        'txt' => ['icon' => 'bi-file-earmark-text-fill', 'color' => '#95A5A6'],
        'doc' => ['icon' => 'bi-file-earmark-word-fill', 'color' => '#2C3E50'],
        'docx' => ['icon' => 'bi-file-earmark-word-fill', 'color' => '#2C3E50'],
        'xls' => ['icon' => 'bi-file-earmark-excel-fill', 'color' => '#27AE60'],
        'xlsx' => ['icon' => 'bi-file-earmark-excel-fill', 'color' => '#27AE60'],
        'ppt' => ['icon' => 'bi-file-earmark-ppt-fill', 'color' => '#E67E22'],
        'pptx' => ['icon' => 'bi-file-earmark-ppt-fill', 'color' => '#E67E22'],
        'mp4' => ['icon' => 'bi-file-earmark-play-fill', 'color' => '#E91E63'],
        'mp3' => ['icon' => 'bi-file-earmark-music-fill', 'color' => '#3498DB'],
        'zip' => ['icon' => 'bi-file-earmark-zip-fill', 'color' => '#F39C12'],
        'rar' => ['icon' => 'bi-file-earmark-zip-fill', 'color' => '#F39C12'],
    ];

    return $icons[$extension] ?? ['icon' => 'bi-file-earmark-fill', 'color' => '#95A5A6'];
}

// Log activity
function logActivity($conn, $userId, $action, $description) {
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $stmt->bind_param("isss", $userId, $action, $description, $ip);
    $stmt->execute();
}

// Sanitize filename
function sanitizeFilename($filename) {
    return preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
}
?>