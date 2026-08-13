<?php
session_start();

if (
    !isset($_SESSION['reset_admin_verified']) ||
    $_SESSION['reset_admin_verified'] !== true ||
    !isset($_SESSION['reset_admin_email'])
) {
    header("Location: admin_forgot_password.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MotoSafe – Reset Admin Password</title>
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
        <div class="auth-form on" id="form-reset">
          <div class="falert" id="alert-reset"></div>
          <div style="margin-bottom:24px;">
            <h2 style="font-size:18px;font-weight:700;margin:0 0 6px;">Reset your password</h2>
            <p style="font-size:13px;color:var(--muted);margin:0;line-height:1.6;">
              Choose a strong new password for your admin account.
            </p>
          </div>
          <form method="POST" action="admin_account_process.php" id="reset-form">
            <div class="fg">
              <label class="flabel" for="rp-pass">New Password</label>
              <div class="fwrap">
                <i class="fas fa-lock fico"></i>
                <input class="finput" type="password" id="rp-pass" name="password" placeholder="Enter new password"
                  required autocomplete="new-password" style="padding-right:38px;"
                  oninput="checkRules()" onkeyup="matchCheck()">
                <i class="fas fa-eye feye" id="rp-eye" onclick="togglePwd('rp-pass','rp-eye')"></i>
              </div>
            </div>

            <!-- Password Rules -->
            <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:12px;padding:14px 16px;margin-bottom:16px;">
              <p style="font-size:11px;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:var(--muted);margin:0 0 10px;">Password Requirements</p>
              <div style="display:grid;gap:7px;">
                <div id="rule-len" style="display:flex;align-items:center;gap:8px;font-size:12.5px;color:var(--muted);transition:color 0.2s;">
                  <i class="fas fa-circle-xmark" id="icon-len" style="font-size:13px;color:#f43f5e;transition:color 0.2s;"></i>At least 8 characters
                </div>
                <div id="rule-upper" style="display:flex;align-items:center;gap:8px;font-size:12.5px;color:var(--muted);transition:color 0.2s;">
                  <i class="fas fa-circle-xmark" id="icon-upper" style="font-size:13px;color:#f43f5e;transition:color 0.2s;"></i>One uppercase letter (A–Z)
                </div>
                <div id="rule-lower" style="display:flex;align-items:center;gap:8px;font-size:12.5px;color:var(--muted);transition:color 0.2s;">
                  <i class="fas fa-circle-xmark" id="icon-lower" style="font-size:13px;color:#f43f5e;transition:color 0.2s;"></i>One lowercase letter (a–z)
                </div>
                <div id="rule-num" style="display:flex;align-items:center;gap:8px;font-size:12.5px;color:var(--muted);transition:color 0.2s;">
                  <i class="fas fa-circle-xmark" id="icon-num" style="font-size:13px;color:#f43f5e;transition:color 0.2s;"></i>One number (0–9)
                </div>
                <div id="rule-special" style="display:flex;align-items:center;gap:8px;font-size:12.5px;color:var(--muted);transition:color 0.2s;">
                  <i class="fas fa-circle-xmark" id="icon-special" style="font-size:13px;color:#f43f5e;transition:color 0.2s;"></i>One special character (!@#$%^&*)
                </div>
              </div>
              <div style="margin-top:14px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                  <span style="font-size:11px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:var(--muted);">Strength</span>
                  <span id="strength-label" style="font-size:11px;font-weight:700;color:var(--muted);transition:color 0.3s;">—</span>
                </div>
                <div style="height:5px;background:rgba(255,255,255,0.06);border-radius:999px;overflow:hidden;">
                  <div id="strength-bar" style="height:100%;width:0%;border-radius:999px;transition:width 0.4s ease,background 0.4s ease;"></div>
                </div>
              </div>
            </div>

            <div class="fg">
              <label class="flabel" for="rp-confirm">Confirm Password</label>
              <div class="fwrap">
                <i class="fas fa-lock fico"></i>
                <input class="finput" type="password" id="rp-confirm" name="confirm_password"
                  placeholder="Re-enter your password" required autocomplete="new-password"
                  style="padding-right:38px;" onkeyup="matchCheck()">
                <i class="fas fa-eye feye" id="rp-confirm-eye" onclick="togglePwd('rp-confirm','rp-confirm-eye')"></i>
              </div>
              <div id="match-msg" style="font-size:12px;margin-top:6px;display:none;"></div>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
              <div class="message error"><?= htmlspecialchars($_SESSION['error']); ?></div>
              <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
              <div class="message success"><?= htmlspecialchars($_SESSION['success']); ?></div>
              <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <button type="submit" class="submit-btn" name="reset_admin_password" id="submit-btn">
              <i class="fas fa-key"></i>
              <span>Update Password</span>
            </button>
          </form>
          <p class="terms-txt">Remembered it? <a href="admin_portal.php">Back to Sign In</a></p>
        </div>
      </div>
    </div>
  </main>
  <script src="../assets/js/right-click.js"></script>
  <script src="../assets/js/portal.js"></script>
  <script>
    function checkRules() {
      const val = document.getElementById('rp-pass').value;
      const rules = [
        { id: 'len',     test: val.length >= 8 },
        { id: 'upper',   test: /[A-Z]/.test(val) },
        { id: 'lower',   test: /[a-z]/.test(val) },
        { id: 'num',     test: /[0-9]/.test(val) },
        { id: 'special', test: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(val) },
      ];
      let passed = 0;
      rules.forEach(r => {
        const icon = document.getElementById('icon-' + r.id);
        const row  = document.getElementById('rule-' + r.id);
        if (r.test) {
          icon.className = 'fas fa-circle-check'; icon.style.color = '#10b981'; row.style.color = '#e8efff'; passed++;
        } else {
          icon.className = 'fas fa-circle-xmark'; icon.style.color = '#f43f5e'; row.style.color = 'var(--muted)';
        }
      });
      const bar = document.getElementById('strength-bar');
      const label = document.getElementById('strength-label');
      const levels = [
        { w:'0%',   bg:'transparent', txt:'—',           col:'var(--muted)' },
        { w:'20%',  bg:'#f43f5e',     txt:'Very Weak',   col:'#f43f5e' },
        { w:'40%',  bg:'#f97316',     txt:'Weak',        col:'#f97316' },
        { w:'60%',  bg:'#f59e0b',     txt:'Fair',        col:'#f59e0b' },
        { w:'80%',  bg:'#3d7fff',     txt:'Strong',      col:'#3d7fff' },
        { w:'100%', bg:'#10b981',     txt:'Very Strong', col:'#10b981' },
      ];
      const lvl = val.length === 0 ? 0 : Math.min(passed, 5);
      bar.style.width = levels[lvl].w; bar.style.background = levels[lvl].bg;
      label.textContent = levels[lvl].txt; label.style.color = levels[lvl].col;
    }
    function matchCheck() {
      const pass = document.getElementById('rp-pass').value;
      const confirm = document.getElementById('rp-confirm').value;
      const msg = document.getElementById('match-msg');
      if (confirm.length === 0) { msg.style.display = 'none'; return; }
      msg.style.display = 'flex'; msg.style.alignItems = 'center'; msg.style.gap = '6px';
      if (pass === confirm) {
        msg.innerHTML = '<i class="fas fa-circle-check" style="color:#10b981;"></i> <span style="color:#10b981;">Passwords match</span>';
      } else {
        msg.innerHTML = '<i class="fas fa-circle-xmark" style="color:#f43f5e;"></i> <span style="color:#f43f5e;">Passwords do not match</span>';
      }
    }
  </script>
</body>
</html>
