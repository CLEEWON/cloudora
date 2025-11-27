<?php
session_start();
include '../db/database.php';

// Jika form dikirim (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        echo "<script>alert('Email dan Password wajib diisi!');</script>";
    } else {
        // Cek apakah email ada di database
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Cek status aktif
            if ($user['status'] !== 'active') {
                echo "<script>alert('Akun anda tidak aktif. Hubungi admin.');</script>";
            } else {
                // Verifikasi password
                if (password_verify($password, $user['password'])) {
                    // Buat session login
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['nama'] = $user['nama'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];

                    // Arahkan berdasarkan role
                    if ($user['role'] === 'admin') {
                        header("Location: ../admin/dashboard.php");
                    } else {
                        header("Location: ../user/dashboard.php");
                    }
                    exit;
                } else {
                    echo "<script>alert('Password salah!');</script>";
                }
            }
        } else {
            echo "<script>alert('Email tidak ditemukan!');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CLOUDORA | Login</title>

  <style>
    :root {
      --bg: #000000ff;
      --text: #e5e5e5;
      --primary: #c9a961;
      --accent: #1a1a1a;
      --font-main: 'Times New Roman', Times, serif;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      background: var(--bg);
      color: var(--text);
      font-family: var(--font-main);
      overflow-x: hidden;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 40px 20px;
    }

    /* Subtle gradient overlay */
    body::before {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: radial-gradient(circle at 50% 20%, rgba(201, 169, 97, 0.03), transparent 60%);
      pointer-events: none;
    }

    .login-container {
      max-width: 400px;
      width: 100%;
      text-align: center;
      animation: fadeIn 1s ease-out;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .logo {
      width: 100px;
      margin-bottom: 20px;
      opacity: 0.9;
    }

    h1 {
      font-size: 2em;
      font-weight: 300;
      letter-spacing: 6px;
      color: var(--primary);
      margin-bottom: 8px;
    }

    .subtitle {
      font-size: 0.7em;
      letter-spacing: 2px;
      color: var(--primary);
      opacity: 0.7;
      margin-bottom: 40px;
      font-weight: 300;
    }

    form {
      margin-bottom: 32px;
    }

    label {
      display: block;
      text-align: left;
      font-size: 0.75em;
      margin-bottom: 8px;
      color: var(--text);
      opacity: 0.8;
      font-weight: 300;
      letter-spacing: 1px;
      text-transform: uppercase;
    }

    label span {
      color: var(--primary);
    }

    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 14px 18px;
      background: transparent;
      border: 1px solid rgba(201, 169, 97, 0.3);
      border-radius: 2px;
      margin-bottom: 20px;
      font-size: 0.95em;
      color: var(--text);
      font-family: var(--font-main);
      transition: all 0.3s ease;
    }

    input:focus {
      outline: none;
      border-color: var(--primary);
      background: rgba(201, 169, 97, 0.05);
    }

    input::placeholder {
      color: #555;
      font-weight: 300;
    }

    button {
      background: transparent;
      color: var(--primary);
      border: 1.5px solid var(--primary);
      padding: 14px 48px;
      border-radius: 2px;
      font-weight: 500;
      font-size: 0.9em;
      letter-spacing: 2px;
      cursor: pointer;
      transition: all 0.4s ease;
      width: 100%;
      font-family: var(--font-main);
      margin-top: 8px;
    }

    button:hover {
      background: var(--primary);
      color: var(--bg);
      transform: translateY(-2px);
    }

    .signup {
      margin-top: 32px;
      font-size: 0.85em;
      color: var(--text);
      opacity: 0.7;
      font-weight: 300;
      letter-spacing: 0.5px;
    }

    .signup a {
      color: var(--primary);
      font-weight: 400;
      text-decoration: none;
      transition: opacity 0.3s;
    }

    .signup a:hover {
      opacity: 0.8;
      text-decoration: underline;
    }

    footer {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      padding: 24px;
      font-size: 0.7em;
      color: #555;
      letter-spacing: 1px;
      font-weight: 300;
      text-align: center;
    }

    footer a {
      color: #777;
      text-decoration: none;
      transition: color 0.3s;
    }

    footer a:hover {
      color: var(--primary);
    }

    .back {
        color: #7e6834ff;
    }

    .back:hover{
        color: var(--primary);
    }
  </style>
</head>

<body>
  <div class="login-container">
    <img src="../assets/cloud.png" alt="Cloudora Icon" class="logo">

    <h1>CLOUDORA</h1>
    <p class="subtitle">SECURE ACCESS</p>

    <form method="POST" action="loginController.php">
      

      <label for="email">E-mail <span>*</span></label>
      <input id="email" type="email" name="email" required autofocus placeholder="your@email.com">

      <label for="password">Kata Sandi <span>*</span></label>
      <input id="password" type="password" name="password" required placeholder="••••••••">

      <button type="submit">MASUK</button>
    </form>
    <a class="back" href="../index.php">← Back to Dashboard</a>
  </div>

</body>
</html>
