<?php
session_start();
require_once '../config.php';
require_once '../db/database.php';

// Jalankan hanya jika request POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /** --------------------------------------------------------
     * BASIC RATE LIMITING (Anti Bruteforce)
     * -------------------------------------------------------- */
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_attempt'] = time();
    }

    // Jika percobaan >=5 dalam 5 menit
    if ($_SESSION['login_attempts'] >= 5 && (time() - $_SESSION['last_attempt']) < 300) {
        echo "<script>alert('Terlalu banyak percobaan login. Coba lagi 5 menit.'); window.history.back();</script>";
        exit;
    }

    /** --------------------------------------------------------
     * Ambil & Validasi Input
     * -------------------------------------------------------- */
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        echo "<script>alert('Email dan Password wajib diisi!'); window.history.back();</script>";
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Format email tidak valid!'); window.history.back();</script>";
        exit;
    }

    /** --------------------------------------------------------
     * Query Email
     * -------------------------------------------------------- */
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    /** --------------------------------------------------------
     * Jika User Ditemukan
     * -------------------------------------------------------- */
    if ($result->num_rows === 1) {

        $user = $result->fetch_assoc();

        // Cek status akun
        if ($user['status'] !== 'active') {
            echo "<script>alert('Akun anda tidak aktif.'); window.history.back();</script>";
            $_SESSION['login_attempts']++;
            $_SESSION['last_attempt'] = time();
            exit;
        }

        /** --------------------------------------------------------
         * Verifikasi Password
         * -------------------------------------------------------- */
        if (password_verify($password, $user['password'])) {

            // Reset percobaan login
            unset($_SESSION['login_attempts']);

            // Regenerate Session ID
            session_regenerate_id(true);

            /** --------------------------------------------------------
             * SIMPAN DATA USER KE SESSION
             * -------------------------------------------------------- */
            $_SESSION['user_id']       = $user['id'];
            $_SESSION['nama']          = htmlspecialchars($user['nama']);
            $_SESSION['email']         = $user['email'];
            $_SESSION['role']          = $user['role'];
            $_SESSION['created_at']    = $user['created_at'];      // <-- PENTING (untuk popup)
            $_SESSION['storage_limit'] = $user['storage_limit'];   // <-- PENTING (untuk kapasitas)
            $_SESSION['login_time']    = time();

            /** --------------------------------------------------------
             * Redirect Sesuai Role
             * -------------------------------------------------------- */
            header("Location: ../views/halamanDashboard.php");
            exit;
        } else {
            // Password salah
            echo "<script>alert('Password salah!'); window.history.back();</script>";
            $_SESSION['login_attempts']++;
            $_SESSION['last_attempt'] = time();
            exit;
        }

    } else {
        // Email tidak ditemukan
        echo "<script>alert('Email tidak ditemukan!'); window.history.back();</script>";
        $_SESSION['login_attempts']++;
        $_SESSION['last_attempt'] = time();
        exit;
    }

} else {
    header("Location: formLogin.php");
    exit;
}
?>
