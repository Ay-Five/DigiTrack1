<?php session_start();
$flash_err  = $_SESSION['flash_error']   ?? null;
$flash_suc  = $_SESSION['flash_success'] ?? null;
$open_modal = $_SESSION['open_modal']    ?? null;
unset($_SESSION['flash_error'], $_SESSION['flash_success'], $_SESSION['open_modal']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MotoSafe – Driving School Management Platform</title>
  <link href="assets/img/logo.png" rel="icon">
  <link href="assets/img/logo.png" rel="apple-touch-icon">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,700;1,9..144,400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="assets/css/index.css" rel="stylesheet">
</head>
<body>
  <button id="backToTop" onclick="scrollToTop()">
    <i class="fas fa-arrow-up"></i>
  </button>
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
        <img src="assets/img/logo.png" alt="MotoSafe Logo" style="width:40px;height:40px;">
      </div>
      <span class="logo-text">Moto<em>Safe</em></span>
    </a>
    <ul class="nav-links" id="navLinks">
      <li><a href="#home" class="active">Home</a></li>
      <li><a href="#about">About</a></li>
      <li><a href="#services">Services</a></li>
      <li><a href="#contact">Contact</a></li>
    </ul>
    <div class="nav-actions">
      <button class="nbtn nbtn-ghost inst-secret" onclick="openM('instModal')" tabindex="-1" aria-hidden="true">
        <i class="fas fa-chalkboard-user"></i> Instructor
      </button>
      <a href="student/student_portal.php" class="nbtn nbtn-primary join-us-btn" style="box-shadow:0 0 0 2px var(--gold),0 4px 20px rgba(37,99,235,.4);">
        <i class="fas fa-calendar-check"></i> Join Us
      </a>
      <a href="instructor/instructor_portal.php" class="nbtn nbtn-primary join-us-btn" style="box-shadow:0 0 0 2px var(--gold),0 4px 20px rgba(37,99,235,.4);">
        <i class="fas fa-calendar-check"></i> Instructor login
      </a>
      <a href="admin/admin_login.php" class="nbtn nbtn-gold admin-secret" tabindex="-1" aria-hidden="true" title="Admin">
        <i class="fas fa-shield-halved"></i>
      </a>
      <div class="hamburger" id="hamburger" onclick="toggleMenu()" aria-label="Menu">
        <span></span><span></span><span></span>
      </div>
    </div>
  </nav>

  <!---- Mobile Menu ---->
  <div class="mobile-menu" id="mobileMenu">
    <a href="#home"     onclick="closeMenu()"><i class="fas fa-house"       style="color:var(--sky);width:20px"></i> Home</a>
    <a href="#about"    onclick="closeMenu()"><i class="fas fa-circle-info" style="color:var(--sky);width:20px"></i> About</a>
    <a href="#services" onclick="closeMenu()"><i class="fas fa-tools"      style="color:var(--sky);width:20px"></i> Services</a>
    <a href="#contact"  onclick="closeMenu()"><i class="fas fa-envelope"    style="color:var(--sky);width:20px"></i> Contact</a>
    <div class="m-divider"></div>
    <div class="m-actions">
      <a href="student/student_portal.php" class="nbtn nbtn-primary">
        <i class="fas fa-calendar-check"></i> Book a Session
      </a>
    </div>
  </div>

  <!---- Main Content ---->
  <!---- Hero Section ---->
  <section class="hero" id="home">
    <div class="hero-photo"></div>
    <div class="hero-body">
      <div class="hero-left">
        <div class="hero-badge"><div class="badge-dot"></div> LTO-Accredited · Miagao, Iloilo</div>
        <h1 class="hero-h1">Welcome to<br><span class="g">MotoSafe</span></h1>
        <p class="hero-tagline">"Drive Smart. Drive Safe. Learn from the Best."</p>
        <div class="hero-btns">
          <a href="student/student_portal.php" class="hbtn hbtn-primary">
            <i class="fas fa-calendar-check"></i> Book a Session
          </a>
          <a href="#about" class="hbtn hbtn-outline">
            <i class="fas fa-circle-info"></i> Learn More
          </a>
        </div>
      </div>
    </div>
    <div class="hero-bottom">
      <!---- Why Choose Us card ---->
      <div class="hero-why">
        <div class="why-head">
          <div class="why-ico"><i class="fas fa-star" style="color:#93c5fd;"></i></div>
          <h3>Why Choose Us?</h3>
        </div>
        <div class="why-divider"></div>
        <p>Our LTO-approved instructors deliver quality driving education for both TDC (face-to-face &amp; online) and PDC hands-on sessions — covering car and motorcycle.</p>
        <div class="why-actions">
          <a href="#about" class="why-btn why-btn-outline">Learn More <i class="fas fa-arrow-right"></i></a>
          <a href="student/student_portal.php" class="why-btn why-btn-fill"><i class="fas fa-calendar-check"></i> Book Now</a>
        </div>
      </div>
      <!---- Feature cards ---->
      <div class="hero-cards-wrap">
        <div class="hero-cards">
          <div class="hcard c1">
            <div class="hcard-line"></div>
            <div class="hcard-ico" style="background:rgba(37,99,235,.18);border:1px solid rgba(37,99,235,.25);">
              <i class="fas fa-calendar-check" style="color:var(--sky);"></i>
            </div>
            <h3>Smart Scheduling</h3>
            <p>Book TDC or PDC sessions online in minutes — automated conflict detection keeps every instructor and student on track.</p>
          </div>
          <div class="hcard c2">
            <div class="hcard-line"></div>
            <div class="hcard-ico" style="background:rgba(16,185,129,.16);border:1px solid rgba(16,185,129,.25);">
              <i class="fas fa-chart-line" style="color:#34d399;"></i>
            </div>
            <h3>Performance Monitoring</h3>
            <p>Real-time KPIs, student pass rates, and lesson dashboards give admins full visibility at a glance.</p>
          </div>
          <div class="hcard c3">
            <div class="hcard-line"></div>
            <div class="hcard-ico" style="background:rgba(245,158,11,.16);border:1px solid rgba(245,158,11,.25);">
              <i class="fas fa-trophy" style="color:var(--gold);"></i>
            </div>
            <h3>LTO Compliance</h3>
            <p>Automated license renewal alerts ensure your instructors stay compliant — keeping your school always audit-ready.</p>
          </div>
          <div class="hcard c4">
            <div class="hcard-line"></div>
            <div class="hcard-ico" style="background:rgba(139,92,246,.16);border:1px solid rgba(139,92,246,.25);">
              <i class="fas fa-comments" style="color:#a78bfa;"></i>
            </div>
            <h3>Student Feedback</h3>
            <p>Collect, review, and act on lesson feedback with built-in analytics that continuously improve teaching quality.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!---- About Section ---->
  <section id="about">
    <div class="about-inner">
      <div class="about-text reveal">
        <p class="sec-eyebrow">About MotoSafe</p>
        <h2 class="sec-h">Built for modern<br><span>driving schools</span></h2>
        <p style="margin-top:20px;">
           MotoSafe is an LTO-accredited driving school located in Miagao, Iloilo, dedicated to providing
  top-quality driving education for both beginners and aspiring licensed drivers. Our experienced and
  certified instructors ensure that every student learns the proper skills, road discipline, and
  confidence needed for safe and responsible driving.
        </p>
        
        <!--<div class="about-stats">
          <div class="stat-item"><div class="stat-num gold">500+</div><div class="stat-label">Students Enrolled</div></div>
          <div class="stat-item"><div class="stat-num blue">50+</div><div class="stat-label">Instructors</div></div>
          <div class="stat-item"><div class="stat-num green">98%</div><div class="stat-label">Pass Rate</div></div>
        </div>
        -->
      </div>

      <div class="about-cards reveal" style="transition-delay:.15s">
        <div class="acard">
          <div class="acard-ico"><i class="fas fa-road" style="color:#34d399;"></i></div>
          <div>
            <h4>Comprehensive Driving Courses</h4>
            <p>We offer both Theoretical Driving Course (TDC) and Practical Driving Course (PDC) for cars and motorcycles,
        designed to meet LTO requirements and real-world driving conditions.</p>
          </div>
        </div>
        <div class="acard">
          <div class="fas fa-user-check" style="color:#a78bfa;"></i></div>
          <div>
            <h4>Student-Focused Learning</h4>
            <p>Our training approach is centered on each student’s pace and comfort, ensuring a supportive environment
        where learners can build confidence behind the wheel.</p>
          </div>
        </div>
        <div class="acard">
          <div class="fas fa-shield-halved" style="color:var(--gold);"></i></div>
          <div>
            <h4>Safety & LTO Compliance</h4>
            <p>MotoSafe follows all LTO guidelines and safety standards, ensuring that every student receives proper training
        and is well-prepared for licensing and real-life driving situations.</p>
          </div>
        </div>
        <div class="acard">
          <div class="acard-ico"><i class="fas fa-location-dot" style="color:#f87171;"></i></div>
          <div>
            <h4>Our Location</h4>
            <p>Nochete Bldg., 2F, Tajanlangit St., Brgy. Tacas, Miagao, Iloilo — behind St. Louise de Marillac School.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!---- Services Section ---->
  <section id="services">
    <div class="sec-head reveal">
      <p class="sec-eyebrow">Our Services</p>
      <h2 class="sec-h">Driving Courses We Offer at<br><span> MotoSafe</span></h2>
    </div>
    <div class="feat-grid">
      <div class="feat-card reveal">
        <div class="feat-card-glow"></div>
        <div class="feat-icon" style="background:rgba(37,99,235,.14);">
          <i class="fas fa-book-open" style="color:var(--sky);font-size:20px;"></i>
        </div>
        <h3>Theoretical Driving Course (TDC)</h3>
        <p>15-hour course available face-to-face and online, covering road safety, traffic rules, and driving fundamentals required by the LTO.</p>
      </div>
      <div class="feat-card reveal" style="transition-delay:.08s">
        <div class="feat-card-glow"></div>
        <div class="feat-icon" style="background:rgba(16,185,129,.14);">
          <i class="fas fa-car" style="color:#34d399;font-size:20px;"></i>
        </div>
        <h3>Practical Driving Course (PDC)</h3>
        <p>8-hour hands-on driving training with professional instructors to develop real-world driving skills and confidence.</p>
      </div>
      <div class="feat-card reveal" style="transition-delay:.16s">
        <div class="feat-card-glow"></div>
        <div class="feat-icon" style="background:rgba(245,158,11,.14);">
          <i class="fas fa-motorcycle" style="color:var(--gold);font-size:20px;"></i>
        </div>
        <h3>Vehicle Options</h3>
        <p>Choose between motorcycle (manual & automatic) and car (manual & automatic) training based on your preferred license type.</p>
      </div>
      <div class="feat-card reveal" style="transition-delay:.24s">
        <div class="feat-card-glow"></div>
        <div class="feat-icon" style="background:rgba(139,92,246,.14);">
          <i class="fas fa-id-card" style="color:#a78bfa;font-size:20px;"></i>
        </div>
        <h3>LTO Certification</h3>
        <p>Receive official certificates upon completion, required for LTO licensing and ensuring you are fully road-ready.</p>
        </div>
    </div>
  </section>

  <!---- Contact Section ---->
  <section id="contact">
    <div class="contact-wrap">
      <span class="sec-eyebrow reveal">Get in Touch</span>
      <h2 class="sec-h reveal" style="margin-top:10px;margin-bottom:0;">Have questions?<br><span>We're here to help</span></h2>
      <p class="contact-sub reveal">
        Whether you're a driving school admin ready to get started, an instructor with questions, or a student needing support —
        reach out anytime and we'll get back to you.
      </p>
      <div class="contact-grid reveal">
        <div class="ci-card">
          <div class="ci-ico"><i class="fas fa-envelope" style="color:var(--sky);font-size:22px;"></i></div>
          <div class="ci-lbl">Email</div>
          <div class="ci-val">motosafe@gmail.com</div>
        </div>
        <div class="ci-card">
          <div class="ci-ico"><i class="fas fa-mobile-screen" style="color:var(--sky);font-size:22px;"></i></div>
          <div class="ci-lbl">Phone </div>
          <div class="ci-val"> 0945 234 3692 | 0966 359 3005</div>
        </div>
        <div class="ci-card">
          <div class="ci-ico"><i class="fas fa-clock" style="color:var(--sky);font-size:22px;"></i></div>
          <div class="ci-lbl">Office Hours</div>
          <div class="ci-val">Mon – Sat, 8:00 AM – 5:00 PM</div>
        </div>
        <div class="ci-card">
          <div class="ci-ico"><i class="fas fa-location-dot" style="color:var(--sky);font-size:22px;"></i></div>
          <div class="ci-lbl">Address</div>
          <div class="ci-val">Nochete Bldg. 2F, Brgy. Tacas,<br>Miagao, Iloilo</div>
        </div>
      </div>

      <!-- Google Map embed — Miag-ao, Iloilo -->
      <div class="map-wrap reveal">
        <iframe
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d31454.34!2d122.2363190!3d10.6420129!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x33ab5b9a7fae6ec5%3A0x4a95d3d317139cb1!2sMiagao%2C%20Iloilo!5e0!3m2!1sen!2sph!4v1700000000000"
          allowfullscreen=""
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade"
          title="MotoSafe Location – Miag-ao, Iloilo">
        </iframe>
      </div>
    </div>
  </section>

  <!---- Footer ---->
  <footer>
    <div class="footer-inner">
      <!---- Bramd ---->
      <div class="fcol fcol-brand">
        <a href="index.php" class="footer-logo">
          <img src="assets/img/logo.png" alt="MotoSafe" width="36" height="36">
          <span style="color:#ffffff;">Moto</span><span style="color:#f59e0b;">Safe</span>
        </a>
        <p class="footer-about">
          LTO-accredited driving school in Miagao, Iloilo — offering TDC and PDC courses for car and motorcycle.
        </p>
        <div class="footer-socials">
          <a href="#" class="fsoc" title="Facebook"  aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="fsoc" title="Instagram" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
          <a href="#" class="fsoc" title="TikTok"    aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
          <a href="#" class="fsoc" title="LinkedIn"  aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
        </div>
      </div>
      <!---- Quick Links ---->
      <div class="fcol">
        <h4 class="fcol-title">Quick Links</h4>
        <ul class="flinks">
          <li><a href="#home"><i class="fas fa-chevron-right"></i> Home</a></li>
          <li><a href="#about"><i class="fas fa-chevron-right"></i> About Us</a></li>
          <li><a href="#services"><i class="fas fa-chevron-right"></i> Services</a></li>
          <li><a href="#contact"><i class="fas fa-chevron-right"></i> Contact</a></li>
          <li><a href="student/student_portal.php"><i class="fas fa-chevron-right"></i> Student Portal</a></li>
        </ul>
      </div>

      <!--- Contact Info ---->
      <div class="fcol">
        <h4 class="fcol-title">Contact Us</h4>
        <ul class="fcontact">
          <li>
            <div class="fci-ico"><i class="fas fa-location-dot"></i></div>
            <span>Nochete Bldg. 2F, Brgy. Tacas,<br>Miagao, Iloilo, Philippines</span>
          </li>
          <li>
            <div class="fci-ico"><i class="fas fa-mobile-screen"></i></div>
            <a href="tel:+639123456789">0945 234 3692 | 0966 359 3005</a>
          </li>
          <li>
            <div class="fci-ico"><i class="fas fa-envelope"></i></div>
            <a href="mailto:motosafe@gmail.com">motosafe@gmail.com</a>
          </li>
          <li>
            <div class="fci-ico"><i class="fas fa-clock"></i></div>
            <span>Mon – Sat &nbsp;8:00 AM – 5:00 PM</span>
          </li>
        </ul>
      </div>

      <!---- Send a Message ---->
      <!--
      <div class="fcol fcol-msg">
        <h4 class="fcol-title">Send a Message</h4>
        <form class="footer-form" method="POST" action="contact_handler.php" autocomplete="off">
          <input type="hidden" name="csrf" value="<?= bin2hex(random_bytes(16)) ?>">
          <div class="ff-group">
            <input type="text"  name="name"    class="ff-input" placeholder="Your name"     required>
          </div>
          <div class="ff-group">
            <input type="email" name="email"   class="ff-input" placeholder="Email address" required>
          </div>
          <div class="ff-group">
            <textarea name="message" class="ff-input ff-textarea" placeholder="Your message…" rows="3" required></textarea>
          </div>
          <button type="submit" class="ff-btn">
            <i class="fas fa-paper-plane"></i> Send Message
          </button>
        </form>
      </div>
      -->
    </div>
    <div class="footer-bar">
      <span>© <?= date('Y') ?> <strong>Digitract</strong> — Instructor Scheduling &amp; Performance Monitoring System</span>
      <span class="footer-bar-links">
        <a href="#">Privacy Policy</a>
        <span class="fb-sep">·</span>
        <a href="#">Terms of Use</a>
      </span>
    </div>
  </footer>

  <!-- ═══════════ SCRIPTS ═══════════ -->
  <script>
  /* ── Secret keyboard shortcuts: type "instructor" or "admin" ── */
  (function(){
    let buf = '';
    const KEYWORDS = { instructor: 'inst-secret', admin: 'admin-secret' };
    const maxLen = Math.max(...Object.keys(KEYWORDS).map(k => k.length));
    document.addEventListener('keydown', e => {
      const tag = document.activeElement?.tagName;
      if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') return;
      if (e.key.length === 1) buf = (buf + e.key.toLowerCase()).slice(-maxLen);
      for (const [word, cls] of Object.entries(KEYWORDS)) {
        if (buf.endsWith(word)) {
          document.querySelectorAll('.' + cls).forEach(el => el.classList.add('revealed'));
          if (word === 'instructor') setTimeout(() => openM('instModal'), 200);
          if (word === 'admin')      setTimeout(() => { window.location.href = 'admin/admin_portal.php'; }, 300);
          buf = '';
        }
      }
    });
  })();

  /* ── Active nav link on scroll ── */
  const sections = ['home', 'about', 'services', 'contact'];
  window.addEventListener('scroll', () => {
    document.getElementById('mainNav').classList.toggle('scrolled', scrollY > 40);
    let cur = 'home';
    sections.forEach(id => {
      const el = document.getElementById(id);
      if (el && scrollY >= el.offsetTop - 110) cur = id;
    });
    document.querySelectorAll('.nav-links a, .mobile-menu a[href^="#"]').forEach(a => {
      a.classList.toggle('active', a.getAttribute('href') === '#' + cur);
    });
  }, { passive: true });

  /* ── Mobile menu ── */
  function toggleMenu() {
    const hb = document.getElementById('hamburger');
    const mm = document.getElementById('mobileMenu');
    const open = mm.classList.toggle('open');
    hb.classList.toggle('open', open);
    document.body.style.overflow = open ? 'hidden' : '';
  }
  function closeMenu() {
    document.getElementById('hamburger').classList.remove('open');
    document.getElementById('mobileMenu').classList.remove('open');
    document.body.style.overflow = '';
  }
  document.addEventListener('click', e => {
    const mm = document.getElementById('mobileMenu');
    const hb = document.getElementById('hamburger');
    if (mm.classList.contains('open') && !mm.contains(e.target) && !hb.contains(e.target)) closeMenu();
  });

  /* ── Scroll reveal ── */
  const observer = new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
  }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
  document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

  /* ── Modal helpers ── */
  function openM(id)  { document.getElementById(id).classList.add('on');    document.body.style.overflow = 'hidden'; }
  function closeM(id) { document.getElementById(id).classList.remove('on'); document.body.style.overflow = ''; }
  function overlayClose(e, id) { if (e.target === document.getElementById(id)) closeM(id); }
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeM('instModal'); });

  /* ── Instructor modal tabs ── */
  function itab(t) {
    document.getElementById('iLoginForm').style.display  = t === 'login'  ? 'block' : 'none';
    document.getElementById('iSignupForm').style.display = t === 'signup' ? 'block' : 'none';
    document.querySelectorAll('#instModal .mtab').forEach((b, i) =>
      b.classList.toggle('on', (i === 0 && t === 'login') || (i === 1 && t === 'signup'))
    );
    clearA('iAlert');
  }

  /* ── Password toggle ── */
  function tpwd(id, icon) {
    const el = document.getElementById(id), show = el.type === 'password';
    el.type = show ? 'text' : 'password';
    icon.className = show ? 'fas fa-eye-slash ftoggle' : 'fas fa-eye ftoggle';
  }

  /* ── Alert helpers ── */
  function showA(id, type, msg) {
    const el = document.getElementById(id);
    el.className = `malert on ${type}`;
    el.innerHTML = `<i class="fas fa-${type === 'err' ? 'circle-exclamation' : type === 'suc' ? 'circle-check' : 'circle-info'}"></i><span>${msg}</span>`;
  }
  function clearA(id) { const el = document.getElementById(id); el.className = 'malert'; el.innerHTML = ''; }

  /* ── Flash messages from PHP session ── */
  window.addEventListener('DOMContentLoaded', () => {
    <?php if ($flash_err && $open_modal !== 'stu'): ?>
      showA('iAlert', 'err', <?= json_encode($flash_err) ?>); openM('instModal');
    <?php endif; ?>
    <?php if ($flash_suc && $open_modal !== 'stu'): ?>
      showA('iAlert', 'suc', <?= json_encode($flash_suc) ?>); openM('instModal');
    <?php endif; ?>
  });

  /* ── Back to top ── */
  const backToTop = document.getElementById('backToTop');
  window.addEventListener('scroll', () => {
    backToTop.style.display = (document.body.scrollTop > 200 || document.documentElement.scrollTop > 200) ? 'block' : 'none';
  }, { passive: true });
  function scrollToTop() { window.scrollTo({ top: 0, behavior: 'smooth' }); }
  </script>
</body>
</html>