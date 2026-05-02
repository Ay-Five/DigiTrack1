  /* ── Nav scroll ── */
  window.addEventListener('scroll', () =>
    document.getElementById('mainNav').classList.toggle('scrolled', scrollY > 40),
    { passive: true }
  );

  /* ── Hamburger / mobile menu ── */
  function toggleMenu(){
    const hb = document.getElementById('hamburger');
    const mm = document.getElementById('mobileMenu');
    const open = mm.classList.toggle('open');
    hb.classList.toggle('open', open);
    document.body.style.overflow = open ? 'hidden' : '';
  }
  function closeMenu(){
    document.getElementById('hamburger').classList.remove('open');
    document.getElementById('mobileMenu').classList.remove('open');
    document.body.style.overflow = '';
  }
  document.addEventListener('click', e => {
    const mm = document.getElementById('mobileMenu');
    const hb = document.getElementById('hamburger');
    if(mm.classList.contains('open') && !mm.contains(e.target) && !hb.contains(e.target)) closeMenu();
  });
  window.addEventListener('resize', () => { if(window.innerWidth > 768) closeMenu(); });

  /* ── Tab switcher ── */
  function switchTab(t){
    document.getElementById('form-in').classList.toggle('on', t==='in');
    document.getElementById('form-up').classList.toggle('on', t==='up');
    document.getElementById('tab-in').classList.toggle('on',  t==='in');
    document.getElementById('tab-up').classList.toggle('on',  t==='up');
    clearAlert('alert-in');
    clearAlert('alert-up');
  }

  /* ── Password eye toggle ── */
  function togglePwd(inputId, eyeId){
    const el  = document.getElementById(inputId);
    const eye = document.getElementById(eyeId);
    const show = el.type === 'password';
    el.type = show ? 'text' : 'password';
    eye.className = show ? 'fas fa-eye-slash feye' : 'fas fa-eye feye';
  }

  /* ── Alert helpers ── */
  function showAlert(id, type, msg){
    const el = document.getElementById(id);
    el.className = 'falert on ' + type;
    const icon = type === 'err' ? 'fa-circle-exclamation' : 'fa-circle-check';
    el.innerHTML = `<i class="fas ${icon}"></i><span>${msg}</span>`;
  }
  function clearAlert(id){
    const el = document.getElementById(id);
    el.className = 'falert';
    el.innerHTML = '';
  }

  /* ── Live validation ── */
  function validateName(el){
    const ok = el.value.trim().length >= 2;
    el.classList.toggle('valid',   ok && el.value.length > 0);
    el.classList.toggle('invalid', !ok && el.value.length > 0);
  }
  function validateEmail(el){
    const ok = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(el.value);
    el.classList.toggle('valid',   ok);
    el.classList.toggle('invalid', !ok && el.value.length > 0);
  }

  /* ── Password strength ── */
  function checkStrength(pw){
    const fill = document.getElementById('str-fill');
    const txt  = document.getElementById('str-text');
    let score = 0;
    if(pw.length >= 8)          score++;
    if(pw.length >= 12)         score++;
    if(/[A-Z]/.test(pw))        score++;
    if(/[0-9]/.test(pw))        score++;
    if(/[^A-Za-z0-9]/.test(pw)) score++;
    const map = [
      { w:'0%',   col:'transparent',  label:'' },
      { w:'20%',  col:'#ef4444',      label:'Very weak' },
      { w:'40%',  col:'#f97316',      label:'Weak' },
      { w:'60%',  col:'#f59e0b',      label:'Fair' },
      { w:'80%',  col:'#3b82f6',      label:'Strong' },
      { w:'100%', col:'#10b981',      label:'Very strong' },
    ];
    const s = pw.length === 0 ? 0 : Math.max(1, score);
    fill.style.width      = map[s].w;
    fill.style.background = map[s].col;
    txt.textContent       = s === 0 ? 'Enter a password' : map[s].label;
    txt.style.color       = s === 0 ? 'rgba(255,255,255,0.32)' : map[s].col;
  }

  /* ── Confirm password match ── */
  function checkMatch(){
    const p    = document.getElementById('su-pass').value;
    const c    = document.getElementById('su-conf').value;
    const conf = document.getElementById('su-conf');
    const msg  = document.getElementById('match-msg');
    if(c.length === 0){
      msg.textContent = ''; msg.className = 'match-msg';
      conf.classList.remove('valid','invalid'); return;
    }
    const ok = p === c;
    conf.classList.toggle('valid',   ok);
    conf.classList.toggle('invalid', !ok);
    msg.className = 'match-msg ' + (ok ? 'ok' : 'no');
    msg.innerHTML = ok
      ? '<i class="fas fa-check" style="font-size:10px;"></i> Passwords match'
      : '<i class="fas fa-xmark" style="font-size:10px;"></i> Passwords do not match';
  }