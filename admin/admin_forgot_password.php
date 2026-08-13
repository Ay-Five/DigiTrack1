<?php
session_start();

if (!isset($_SESSION['allow_admin_forgot'])) {
    header("Location: admin_portal.php");
    exit();
}

unset($_SESSION['allow_admin_forgot']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MotoSafe – Admin Forgot Password</title>
  <link href="../assets/img/logo.png" rel="icon">
  <link href="../assets/img/logo.png" rel="apple-touch-icon">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,700;1,9..144,400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="../assets/css/portal.css" rel="stylesheet">
</head>
<body>
  <div class="ambient">
    <div class="ambient-grid"></div>
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>
  </div>
  <nav id="mainNav">
    <a href="../index.php" class="logo">
      <div class="logo-mark"><img src="../assets/img/logo.png" alt="MotoSafe Logo" style="width:40px;height:40px;"></div>
      <span class="logo-text">Moto<em>Safe</em></span>
    </a>
    <ul class="nav-links">
      <li><a href="../index.php#home">Home</a></li>
      <li><a href="../index.php#about">About</a></li>
      <li><a href="../index.php#services">Services</a></li>
      <li><a href="../index.php#contact">Contact</a></li>
    </ul>
    <div class="nav-actions">
      <a href="admin_portal.php" class="nbtn nbtn-primary active student-btn">
        <i class="fas fa-user-shield"></i> Admin
      </a>
      <div class="hamburger" id="hamburger" onclick="toggleMenu()"><span></span><span></span><span></span></div>
    </div>
  </nav>
  <div class="mobile-menu" id="mobileMenu">
    <a href="../index.php#home" onclick="closeMenu()"><i class="fas fa-house" style="color:var(--sky);width:20px"></i> Home</a>
    <a href="../index.php#about" onclick="closeMenu()"><i class="fas fa-circle-info" style="color:var(--sky);width:20px"></i> About</a>
    <a href="../index.php#services" onclick="closeMenu()"><i class="fas fa-grid-2" style="color:var(--sky);width:20px"></i> Services</a>
    <a href="../index.php#contact" onclick="closeMenu()"><i class="fas fa-envelope" style="color:var(--sky);width:20px"></i> Contact</a>
    <div class="m-divider"></div>
    <div class="m-actions">
      <a href="admin_portal.php" class="nbtn nbtn-primary"><i class="fas fa-user-shield"></i> Admin Portal</a>
    </div>
  </div>
  <main class="page-main">
    <div class="auth-card">
      <div class="card-glow card-glow-1"></div>
      <div class="card-glow card-glow-2"></div>
      <div class="card-inner">
        <div class="card-header">
          <div class="card-logo-mark"><img src="../assets/img/logo.png" alt="MotoSafe Logo" style="width:30px;height:30px;"></div>
          <span class="card-brand">Moto<em>Safe</em></span>
        </div>
        <div class="portal-pill"><div class="pill-dot"></div> Admin Portal</div>
        <div class="auth-form on" id="form-forgot">
          <div class="falert" id="alert-forgot"></div>
          <div style="margin-bottom:24px;">
            <h2 style="font-size:18px;font-weight:700;margin:0 0 6px;">Forgot your password?</h2>
            <p style="font-size:13px;color:var(--muted);margin:0;line-height:1.6;">
              Enter your registered admin email and we'll send a one-time password (OTP) to reset your password.
            </p>
          </div>
          <form method="POST" action="admin_account_process.php">
            <div class="fg">
              <label class="flabel" for="fp-email">Admin Email Address</label>
              <div class="fwrap">
                <i class="fas fa-envelope fico"></i>
                <input class="finput" type="email" id="fp-email" name="email" placeholder="admin@example.com" autocomplete="email" required>
              </div>
            </div>
            <?php if (isset($_SESSION['error'])): ?>
              <div class="message error"><?= htmlspecialchars($_SESSION['error']); ?></div>
              <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
              <div class="message success"><?= htmlspecialchars($_SESSION['success']); ?></div>
              <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            <button type="submit" class="submit-btn" name="forgot_admin_password">
              <i class="fas fa-paper-plane"></i>
              <span>Send OTP</span>
            </button>
          </form>
          <p class="terms-txt">Remembered it? <a href="admin_portal.php">Back to Sign In</a></p>
        </div>
      </div>
    </div>
  </main>
  <script src="../assets/js/right-click.js"></script>
  <script src="../assets/js/portal.js"></script>
</body>
</html>
