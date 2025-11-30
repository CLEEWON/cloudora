--- auth/loginController.php (原始)
<?php
session_start();
require_once '../db/database.php';

// Jalankan hanya jika request POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        echo "<script>alert('Email dan Password wajib diisi!'); window.history.back();</script>";
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if ($user['status'] !== 'active') {
            echo "<script>alert('Akun anda tidak aktif.'); window.history.back();</script>";
            exit;
        }

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            // Redirect sesuai role
            if ($user['role'] === 'admin') {
                header("Location: ../views/halamanDashboard.php");
                exit;
            } else {
                header("Location: ../views/halamanDashboard.php");
                exit;
            }
        } else {
            echo "<script>alert('Password salah!'); window.history.back();</script>";
            exit;
        }
    } else {
        echo "<script>alert('Email tidak ditemukan!'); window.history.back();</script>";
        exit;
    }
} else {
    header("Location: formLogin.php");
    exit;
}
?>

+++ auth/loginController.php (修改后)
<?php
require_once '../config.php';
session_start();

// Jalankan hanya jika request POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Rate limiting (basic implementation)
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_attempt'] = time();
    }

    // Check if too many attempts
    if ($_SESSION['login_attempts'] >= 5 && (time() - $_SESSION['last_attempt']) < 300) { // 5 attempts in 5 minutes
        echo "<script>alert('Too many login attempts. Please try again later.'); window.history.back();</script>";
        exit;
    }

    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        echo "<script>alert('Email dan Password wajib diisi!'); window.history.back();</script>";
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Email tidak valid!'); window.history.back();</script>";
        exit;
    }

    require_once '../db/database.php';

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if ($user['status'] !== 'active') {
            echo "<script>alert('Akun anda tidak aktif.'); window.history.back();</script>";
            $_SESSION['login_attempts']++;
            $_SESSION['last_attempt'] = time();
            exit;
        }

        if (password_verify($password, $user['password'])) {
            // Reset login attempts on successful login
            unset($_SESSION['login_attempts']);

            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama'] = htmlspecialchars($user['nama']);
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['login_time'] = time();

            // Redirect sesuai role
            if ($user['role'] === 'admin') {
                header("Location: ../views/halamanDashboard.php");
                exit;
            } else {
                header("Location: ../views/halamanDashboard.php");
                exit;
            }
        } else {
            echo "<script>alert('Password salah!'); window.history.back();</script>";
            $_SESSION['login_attempts']++;
            $_SESSION['last_attempt'] = time();
            exit;
        }
    } else {
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