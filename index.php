<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CLOUDORA | Secure. Reliable. Premium.</title>
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

    .container {
      max-width: 560px;
      width: 100%;
      text-align: center;
    }

    .logo {
      width: 300px;
      margin-bottom: 24px;
      opacity: 0.9;
    }

    h1 {
      font-size: 2.8em;
      font-weight: 300;
      letter-spacing: 8px;
      color: var(--primary);
      margin-bottom: 12px;
    }

    .subtitle {
      font-size: 0.75em;
      letter-spacing: 3px;
      color: var(--primary);
      opacity: 0.7;
      margin-bottom: 48px;
      font-weight: 300;
    }

    .tagline {
      font-size: 1.05em;
      line-height: 1.8;
      color: var(--text);
      opacity: 0.8;
      margin-bottom: 48px;
      font-weight: 300;
    }

    .btn {
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
      text-decoration: none;
      display: inline-block;
    }

    .btn:hover {
      background: var(--primary);
      color: var(--bg);
      transform: translateY(-2px);
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
    }

    footer a {
      color: #777;
      text-decoration: none;
      transition: color 0.3s;
    }

    footer a:hover {
      color: var(--primary);
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

    .container {
      animation: fadeIn 1s ease-out;
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
  </style>
</head>
<body>

  <div class="container">
    <img src="assets/cloud.png" alt="Cloudora Logo" class="logo">
    <h1>CLOUDORA</h1>
    <p class="subtitle">SECURE · RELIABLE · PREMIUM</p>

    <p class="tagline">
      Komputasi awan generasi berikutnya dengan kinerja, keandalan, dan keamanan yang tak tertandingi.
    </p>

    <a href="auth/formLogin.php" class="btn">GET STARTED</a>
  </div>

  <footer>
    © 2025 CLOUDORA<br>
    <a href="#">Privacy</a> · <a href="#">Terms</a>
  </footer>

</body>
</html>
