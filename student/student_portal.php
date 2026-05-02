<?php
  session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MotoSafe – Student Portal</title>
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
  <!---- Nav ---->
  <nav id="mainNav">
    <a href="index.php" class="logo">
      <div class="logo-mark">
        <img src="../assets/img/logo.png" alt="MotoSafe Logo" style="width:40px;height:40px;">
      </div>
      <span class="logo-text">Moto<em>Safe</em></span>
    </a>
    <ul class="nav-links">
      <li><a href="../index.php#home">Home</a></li>
      <li><a href="../index.php#about">About</a></li>
      <li><a href="../index.php#services">Services</a></li>
      <li><a href="../index.php#contact">Contact</a></li>
    </ul>
    <div class="nav-actions">
      <a href="student_portal.php" class="nbtn nbtn-primary active student-btn">
        <i class="fas fa-user-graduate"></i> Student
      </a>
      <div class="hamburger" id="hamburger" onclick="toggleMenu()">
        <span></span><span></span><span></span>
      </div>
    </div>
  </nav>
  <!---- Mobile Menu ---->
  <div class="mobile-menu" id="mobileMenu">
    <a href="../index.php#home"     onclick="closeMenu()"><i class="fas fa-house"        style="color:var(--sky);width:20px"></i> Home</a>
    <a href="../index.php#about"    onclick="closeMenu()"><i class="fas fa-circle-info"  style="color:var(--sky);width:20px"></i> About</a>
    <a href="../index.php#services" onclick="closeMenu()"><i class="fas fa-grid-2"       style="color:var(--sky);width:20px"></i> Services</a>
    <a href="../index.php#contact"  onclick="closeMenu()"><i class="fas fa-envelope"     style="color:var(--sky);width:20px"></i> Contact</a>
    <div class="m-divider"></div>
    <div class="m-actions">
      <a href="student_portal.php" class="nbtn nbtn-primary"><i class="fas fa-user-graduate"></i> Student Portal</a>
    </div>
  </div>
  <!---- Main Content ---->
<main class="page-main">
  <div class="auth-card">
    <div class="card-glow card-glow-1"></div>
    <div class="card-glow card-glow-2"></div>
    <div class="card-inner">
      <!---- Header ---->
      <div class="card-header">
        <div class="card-logo-mark">
        <img src="../assets/img/logo.png" alt="MotoSafe Logo" style="width:30px;height:30px;">
        </div>
        <span class="card-brand">Moto<em>Safe</em></span>
      </div>
      <div class="portal-pill"><div class="pill-dot"></div> Student Portal</div>
      <!---- Tabs ---->
      <div class="auth-tabs">
        <button class="auth-tab on" id="tab-in" onclick="switchTab('in')">
          <i class="fas fa-arrow-right-to-bracket" style="font-size:12px;margin-right:5px;"></i>Sign In
        </button>
        <button class="auth-tab" id="tab-up" onclick="switchTab('up')">
          <i class="fas fa-user-plus" style="font-size:12px;margin-right:5px;"></i>Create Account
        </button>
      </div>
      <!---- Sign in Form---->
      <div class="auth-form on" id="form-in">
        <div class="falert" id="alert-in"></div>
        <form method="POST" action="student_account_process.php">
          <div class="fg">
            <label class="flabel" for="si-email">Email Address</label>
            <div class="fwrap">
              <i class="fas fa-envelope fico"></i>
              <input class="finput" type="email" id="si-email" name="email" placeholder="you@example.com" autocomplete="email">
            </div>
          </div>
          <div class="fg">
            <label class="flabel" for="si-pass">Password</label>
            <div class="fwrap">
              <i class="fas fa-lock fico"></i>
              <input class="finput" type="password" id="si-pass" name="password" placeholder="Enter your password" autocomplete="current-password" style="padding-right:38px;">
              <i class="fas fa-eye feye" id="si-eye" onclick="togglePwd('si-pass','si-eye')"></i>
            </div>
          </div>
          <a href="set_session.php?action=forgot" class="forgot-link">Forgot password?</a>
            <!---- Message Handling ---->
            <?php if (isset($_SESSION['error'])): ?>
              <div class="message error">
                <?= htmlspecialchars($_SESSION['error']); ?>
              </div>
              <?php unset($_SESSION['error']); ?>
              <?php endif; ?>
              <?php if (isset($_SESSION['success'])): ?>
              <div class="message success">
                <?= htmlspecialchars($_SESSION['success']); ?>
              </div>
              <?php unset($_SESSION['success']); ?>
              <?php endif; 
            ?>
          <button type="submit" class="submit-btn" name="student_login">
            <i class="fas fa-arrow-right-to-bracket"></i>
            <span>Sign In</span>
          </button>
        </form>
        <p class="terms-txt">Don't have an account? <a onclick="switchTab('up')">Create one free</a></p>
      </div>
      <!---- Sign up Form---->
      <div class="auth-form" id="form-up">
        <div class="falert" id="alert-up"></div>
        <form method="POST" action="student_account_process.php">
          <div class="fg">
            <label class="flabel" for="su-name">Full Name</label>
            <div class="fwrap">
              <i class="fas fa-user fico"></i>
              <input class="finput" type="text" id="su-name" name="fullname" placeholder="Juan dela Cruz" autocomplete="name" oninput="validateName(this)" required>
            </div>
          </div>
          <div class="fg">
            <label class="flabel" for="su-email">Email Address</label>
            <div class="fwrap">
              <i class="fas fa-envelope fico"></i>
              <input class="finput" type="email" id="su-email" name="email" placeholder="you@example.com" autocomplete="email" oninput="validateEmail(this)" required>
            </div>
          </div>
          <div class="fg">
            <label class="flabel" for="su-pass">Password</label>
            <div class="fwrap">
              <i class="fas fa-lock fico"></i>
              <input class="finput" type="password" id="su-pass" name="password"
              placeholder="Create a strong password" autocomplete="new-password"
              style="padding-right:38px;"
              oninput="checkStrength(this.value); checkMatch();" required>
              <i class="fas fa-eye feye" id="su-eye" onclick="togglePwd('su-pass','su-eye')"></i>
            </div>
            <div class="str-wrap">
              <div class="str-bar"><div class="str-fill" id="str-fill"></div></div>
              <span class="str-text" id="str-text">Enter a password</span>
            </div>
          </div>
          <div class="fg">
            <label class="flabel" for="su-conf">Confirm Password</label>
            <div class="fwrap">
              <i class="fas fa-lock fico"></i>
              <input class="finput" type="password" id="su-conf" name="confirm_password"
              placeholder="Re-enter password" autocomplete="new-password"
              style="padding-right:38px;"
              oninput="checkMatch()" required>
              <i class="fas fa-eye feye" id="sc-eye" onclick="togglePwd('su-conf','sc-eye')"></i>
            </div>
            <div class="match-msg" id="match-msg"></div>
          </div>
          <button type="submit" class="submit-btn" name="student_register">
            <i class="fas fa-user-plus"></i>
            <span>Create Account</span>
          </button>
        </form>
        <p class="terms-txt">
          By signing up you agree to our <a>Terms</a> &amp; <a>Privacy Policy</a>.<br>
          Already have an account? <a onclick="switchTab('in')">Sign in</a>
        </p>
      </div>
    </div><!-- /card-inner -->
  </div><!-- /auth-card -->
</main>
<script src="../assets/js/right-click.js"></script>
<script src="../assets/js/portal.js"></script>
</body>
</html>