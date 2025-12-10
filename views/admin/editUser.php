<?php
session_start();
require_once __DIR__ . '/../../db/database.php';

// Pastikan admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: halamanDashboard.php");
    exit;
}

// Validasi ID user
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID user tidak valid.");
}

$user_id = intval($_GET['id']);

// Ambil data user dari DB
$stmt = $conn->prepare("SELECT id, nama, email, role, status, storage_limit FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    die("User tidak ditemukan.");
}

// Jika form di-submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $status = $_POST['status'];
    $storage_limit = intval($_POST['storage_limit']);

    $stmt = $conn->prepare("UPDATE users SET nama=?, email=?, role=?, status=?, storage_limit=? WHERE id=?");
    $stmt->bind_param("ssssii", $nama, $email, $role, $status, $storage_limit, $user_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "User berhasil diperbarui.";
        header("Location: manageUsers.php");
        exit;
    } else {
        $error = "Gagal memperbarui user: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Edit User - Cloudora</title>
<link rel="stylesheet" href="../../assets/css/dashboard.css">
</head>
<body>
<div class="main">
    <h2>Edit User</h2>

    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <form method="POST">
        <label>Nama:</label>
        <input type="text" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" required><br>

        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required><br>

        <label>Role:</label>
        <select name="role">
            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
            <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
        </select><br>

        <label>Status:</label>
        <select name="status">
            <option value="active" <?= $user['status'] === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="inactive" <?= $user['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
        </select><br>

        <label>Storage Limit (MB):</label>
        <input type="number" name="storage_limit" value="<?= $user['storage_limit'] ?>" required><br>

        <button type="submit">Update</button>
        <a href="manageUsers.php">Batal</a>
    </form>
</div>
</body>
</html>
