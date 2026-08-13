<?php
require_once 'admin_backend.php';
$thisMonth = date('Y-m');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Dashboard – MotoSafe Driving School</title>
<link href="../assets/img/logo.png" rel="icon">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root{--bg:#0f1117;--bg2:#181c27;--bg3:#1e2436;--border:#2a3147;--accent:#7c6af7;--green:#3cb97a;--amber:#f59e0b;--red:#e55;--text:#e2e5f0;--muted:#8490a8;--sidebar-w:240px}
*{box-sizing:border-box;margin:0;padding:0}
body{background:var(--bg);color:var(--text);font-family:'Segoe UI',sans-serif;min-height:100vh;display:flex}
.sidebar{width:var(--sidebar-w);background:var(--bg2);border-right:1px solid var(--border);display:flex;flex-direction:column;padding:20px 0;flex-shrink:0;position:fixed;height:100vh;overflow-y:auto;z-index:200;transition:transform .3s ease}
.brand{padding:0 18px 20px;font-size:15px;font-weight:700;color:var(--accent);border-bottom:1px solid var(--border);margin-bottom:12px;display:flex;align-items:center;justify-content:space-between}
.sidebar-close{display:none;background:none;border:none;color:var(--muted);font-size:16px;cursor:pointer;padding:4px}
.sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:199}
.sidebar-overlay.open{display:block}
.top-bar{display:none;align-items:center;gap:12px;margin-bottom:16px}
.hamburger{background:none;border:1px solid var(--border);color:var(--text);padding:7px 10px;border-radius:6px;cursor:pointer;font-size:14px}
.nav-item{display:flex;align-items:center;gap:10px;padding:10px 18px;color:var(--muted);font-size:13px;cursor:pointer;text-decoration:none;border:none;background:none;width:100%;text-align:left;transition:.15s}
.nav-item:hover,.nav-item.active{background:rgba(124,106,247,.12);color:var(--text)}
.nav-item.active{border-left:3px solid var(--accent);color:var(--accent)}
.nav-item i{width:16px;text-align:center}
.section-divider{font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;padding:8px 18px 4px;margin-top:4px}
.main{margin-left:var(--sidebar-w);flex:1;padding:24px;min-height:100vh;transition:margin-left .3s ease}
.page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px}
.page-title{font-size:18px;font-weight:600}
.section{display:none}.section.active{display:block}
.panel{background:var(--bg2);border:1px solid var(--border);border-radius:10px;overflow:hidden;margin-bottom:16px}
.panel-head{padding:14px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px}
.panel-title{font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px}
.stats-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin-bottom:20px}
.stat-card{background:var(--bg2);border:1px solid var(--border);border-radius:10px;padding:16px;cursor:pointer;transition:.15s}
.stat-card:hover{border-color:var(--accent)}
.stat-num{font-size:26px;font-weight:700;color:var(--accent)}
.stat-label{font-size:11px;color:var(--muted);margin-top:4px}
.analytics-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-bottom:20px}
.analytic-card{background:var(--bg3);border:1px solid var(--border);border-radius:10px;padding:16px;display:flex;flex-direction:column;gap:6px}
.analytic-num{font-size:22px;font-weight:700}
.analytic-label{font-size:11px;color:var(--muted)}
.analytic-sub{font-size:10px;color:var(--muted)}
.duo-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px}
.tbl-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch}
@media(max-width:900px){.duo-grid{grid-template-columns:1fr}}
@media(max-width:768px){
    .form-grid{grid-template-columns:1fr!important}
    .sidebar{transform:translateX(-100%)}
    .sidebar.open{transform:translateX(0)}
    .sidebar-close{display:block}
    .top-bar{display:flex}
    .main{margin-left:0;padding:16px}
    .page-header{flex-direction:column;align-items:flex-start;gap:6px}
    .stats-row{grid-template-columns:repeat(2,1fr)}
    .analytics-grid{grid-template-columns:repeat(2,1fr)}
    .duo-grid{grid-template-columns:1fr}
    .panel-head{flex-direction:column;align-items:flex-start}
}
@media(max-width:480px){
    .stats-row{grid-template-columns:repeat(2,1fr)}
    .analytics-grid{grid-template-columns:repeat(2,1fr)}
    .stat-num{font-size:22px}
    .tbl{font-size:11px}
    .tbl th,.tbl td{padding:8px 8px}
    .be,.bds,.ber{padding:5px 10px;font-size:11px}
}
.tag{display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500}
.tag-amber{background:rgba(245,158,11,.15);color:#f59e0b}.tag-blue{background:rgba(59,130,246,.15);color:#60a5fa}
.tag-green{background:rgba(59,185,122,.15);color:#3cb97a}.tag-teal{background:rgba(20,184,166,.15);color:#2dd4bf}
.tag-coral{background:rgba(239,68,68,.15);color:#f87171}.tag-purple{background:rgba(124,106,247,.15);color:#a78bfa}
.tag-gray{background:rgba(100,116,139,.15);color:#94a3b8}
.bkp{background:rgba(245,158,11,.15);color:#f59e0b;padding:3px 10px;border-radius:20px;font-size:11px}
.bksu{background:rgba(59,130,246,.15);color:#60a5fa;padding:3px 10px;border-radius:20px;font-size:11px}
.bkb{background:rgba(124,106,247,.15);color:#a78bfa;padding:3px 10px;border-radius:20px;font-size:11px}
.bkg{background:rgba(59,185,122,.15);color:#3cb97a;padding:3px 10px;border-radius:20px;font-size:11px}
.bkr{background:rgba(229,85,85,.15);color:#f87171;padding:3px 10px;border-radius:20px;font-size:11px}
.bkd{background:rgba(100,116,139,.15);color:#94a3b8;padding:3px 10px;border-radius:20px;font-size:11px}
.be{background:var(--accent);color:#fff;border:none;padding:6px 14px;border-radius:6px;font-size:12px;cursor:pointer;display:inline-flex;align-items:center;gap:6px;transition:.15s}
.be:hover{opacity:.85}.bds{background:transparent;border:1px solid var(--border);color:var(--muted);padding:5px 12px;border-radius:6px;font-size:12px;cursor:pointer}
.ber{background:rgba(229,85,85,.15);color:#f87171;border:1px solid rgba(229,85,85,.3);padding:5px 12px;border-radius:6px;font-size:12px;cursor:pointer}
.tbl{width:100%;border-collapse:collapse;font-size:12px}
.tbl th{padding:10px 12px;text-align:left;color:var(--muted);font-weight:500;border-bottom:1px solid var(--border);font-size:11px}
.tbl td{padding:10px 12px;border-bottom:1px solid var(--border);vertical-align:middle}
.tbl tr:last-child td{border-bottom:none}
.tbl tbody tr:hover{background:rgba(255,255,255,.02)}
.di{background:var(--bg);border:1px solid var(--border);color:var(--text);padding:7px 10px;border-radius:6px;font-size:12px;width:100%}
.di:focus{outline:none;border-color:var(--accent)}
.form-label{font-size:11px;color:var(--muted);margin-bottom:4px;display:block}
.flash{padding:10px 16px;border-radius:8px;margin-bottom:16px;font-size:12px}
.flash-ok{background:rgba(59,185,122,.15);border:1px solid rgba(59,185,122,.3);color:var(--green)}
.flash-err{background:rgba(229,85,85,.15);border:1px solid rgba(229,85,85,.3);color:var(--red)}
.prog-bar{height:8px;background:var(--border);border-radius:4px;overflow:hidden}
.prog-fill{height:100%;background:var(--accent);border-radius:4px}
.cert-badge{background:rgba(245,158,11,.12);border:1px solid rgba(245,158,11,.3);color:#f59e0b;padding:4px 10px;border-radius:20px;font-size:11px;display:inline-flex;align-items:center;gap:5px}
.rank-medal{font-size:14px;width:22px;text-align:center}
.grade-bar{height:6px;background:var(--border);border-radius:3px;overflow:hidden;margin-top:3px}
.grade-fill{height:100%;border-radius:3px}
.sb-analytics{padding:14px 14px 0;border-top:1px solid var(--border);margin-top:8px}
.sb-analytics-title{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:10px;display:flex;align-items:center;gap:6px}
.sb-stat{display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid rgba(42,49,71,.5)}
.sb-stat:last-child{border-bottom:none}
.sb-stat-label{font-size:11px;color:var(--muted)}
.sb-stat-val{font-size:12px;font-weight:700;color:var(--text)}
.sb-mini-bar{height:4px;background:var(--border);border-radius:2px;overflow:hidden;margin-top:3px;width:70px}
.sb-mini-fill{height:100%;border-radius:2px;background:var(--accent)}
</style>
</head>
<body>
<script>
var T={dashboard:'Dashboard',students:'Students',instructors:'Instructors',
  appointments:'Appointments',rankings:'Rankings',
  reports:'Reports',communication:'Announcements',workload:'Workload Distribution',
  student_profile:'Student Profile',instructor_profile:'Instructor Profile',admins:'Admin Management',profile:'Change Password'};
</script>
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<div class="sidebar" id="sidebar">
    <div class="brand"><span><i class="fa-solid fa-shield-halved me-2"></i>ABROT Admin</span><button class="sidebar-close" onclick="closeSidebar()"><i class="fa-solid fa-xmark"></i></button></div>
    <a class="nav-item <?= $activeSection==='dashboard'?'active':'' ?>" onclick="showSection('dashboard')" href="javascript:void(0)"><i class="fa-solid fa-gauge"></i> Dashboard</a>
    <div class="section-divider">Management</div>
    <a class="nav-item <?= in_array($activeSection,['students','student_profile'])?'active':'' ?>" onclick="showSection('students')" href="javascript:void(0)"><i class="fa-solid fa-user-graduate"></i> Students</a>
    <a class="nav-item <?= in_array($activeSection,['instructors','instructor_profile'])?'active':'' ?>" onclick="showSection('instructors')" href="javascript:void(0)"><i class="fa-solid fa-chalkboard-user"></i> Instructors</a>
    <a class="nav-item <?= $activeSection==='appointments'?'active':'' ?>" onclick="showSection('appointments')" href="javascript:void(0)"><i class="fa-solid fa-calendar-check"></i> Appointments<?php if($pendingAppts>0): ?><span class="tag tag-blue"><?= $pendingAppts ?></span><?php endif; ?></a>
    <a class="nav-item <?= $activeSection==='calendar'?'active':'' ?>" onclick="showSection('calendar')" href="javascript:void(0)"><i class="fa-solid fa-calendar-days"></i> Calendar</a>
    <div class="section-divider">Insights</div>
    <a class="nav-item <?= $activeSection==='rankings'?'active':'' ?>" onclick="showSection('rankings')" href="javascript:void(0)"><i class="fa-solid fa-ranking-star"></i> Rankings</a>
    <a class="nav-item <?= $activeSection==='reports'?'active':'' ?>" onclick="showSection('reports')" href="javascript:void(0)"><i class="fa-solid fa-chart-bar"></i> Reports</a>
    <a class="nav-item <?= $activeSection==='communication'?'active':'' ?>" onclick="showSection('communication')" href="javascript:void(0)"><i class="fa-solid fa-bullhorn"></i> Announcements</a>
    <a class="nav-item <?= $activeSection==='workload'?'active':'' ?>" onclick="showSection('workload')" href="javascript:void(0)"><i class="fa-solid fa-chart-gantt"></i> Workload</a>
    <div style="flex:1"></div>
    <div class="sb-analytics">
        <div class="sb-analytics-title"><i class="fa-solid fa-chart-line" style="color:var(--accent)"></i> Live Stats</div>
        <div class="sb-stat"><div class="sb-stat-label">Active Appointments</div><div class="sb-stat-val" style="color:var(--amber)"><?= $sbActiveNow ?></div></div>
        <div class="sb-stat"><div class="sb-stat-label">Today's Sessions</div><div class="sb-stat-val"><?= $sbSessionsToday ?></div></div>
        <div class="sb-stat"><div class="sb-stat-label">Total Sessions</div><div class="sb-stat-val"><?= $totalSessions ?></div></div>
        <div class="sb-stat"><div><div class="sb-stat-label">Avg Instr. Rating</div><div class="sb-mini-bar"><div class="sb-mini-fill" style="width:<?= $sbAvgRatingSb*20 ?>%;background:<?= $sbRatColor ?>"></div></div></div><div class="sb-stat-val" style="color:<?= $sbRatColor ?>">★<?= $sbAvgRatingSb ?></div></div>
        <div class="sb-stat"><div class="sb-stat-label">Unverified Students</div><div class="sb-stat-val" style="color:<?= $sbUnverified>0?'var(--amber)':'var(--muted)' ?>"><?= $sbUnverified ?></div></div>
        <div class="sb-stat"><div class="sb-stat-label">New Students/Mo</div><div class="sb-stat-val" style="color:var(--accent)"><?= $newStudentsMonth ?></div></div>
        <div class="sb-stat"><div class="sb-stat-label">Today's Logins</div><div class="sb-stat-val"><?= $todayLogins ?></div></div>
    </div>
    <a class="nav-item <?= $activeSection==='admins'?'active':'' ?>" onclick="showSection('admins')" href="javascript:void(0)"><i class="fa-solid fa-user-shield"></i> Admins</a>
    <a class="nav-item <?= $activeSection==='profile'?'active':'' ?>" onclick="showSection('profile')" href="javascript:void(0)"><i class="fa-solid fa-key"></i> Change Password</a>
    <a class="nav-item" href="../admin_logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
</div>

<div class="main">
<div class="top-bar">
    <button class="hamburger" onclick="openSidebar()"><i class="fa-solid fa-bars"></i></button>
    <div class="page-title" id="page-title-text-mobile">Dashboard</div>
</div>
<div class="page-header">
    <div class="page-title" id="page-title-text">Dashboard</div>
    <div style="font-size:12px;color:var(--muted)"><?= $today ?></div>
</div>
<?php if($flash): ?><div class="flash flash-ok"><i class="fa-solid fa-circle-check me-2"></i><?= htmlspecialchars($flash) ?></div><?php endif; ?>
<?php if($flashErr): ?><div class="flash flash-err"><i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($flashErr) ?></div><?php endif; ?>

<!-- ══ DASHBOARD ══════════════════════════════════════════════ -->
<div id="section-dashboard" class="section <?= $activeSection==='dashboard'?'active':'' ?>">
<div class="stats-row">
    <div class="stat-card" onclick="showSection('students')"><div class="stat-num"><?= $totalStudents ?></div><div class="stat-label">Total Students</div></div>
    <div class="stat-card" onclick="showSection('instructors')"><div class="stat-num"><?= $totalInstructors ?></div><div class="stat-label">Instructors</div></div>
    <div class="stat-card" onclick="showSection('appointments')"><div class="stat-num" style="color:var(--amber)"><?= $pendingAppts ?></div><div class="stat-label">Active Appts</div></div>
    <div class="stat-card" onclick="showSection('appointments')"><div class="stat-num" style="color:var(--green)"><?= $completedAppts ?></div><div class="stat-label">Completed</div></div>
    <div class="stat-card"><div class="stat-num" style="color:var(--accent)"><?= $todayLogins ?></div><div class="stat-label">Today's Logins</div></div>
</div>

<!-- Top & Low Instructors -->
<div class="duo-grid">
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-trophy" style="color:var(--amber)"></i> Top 5 Rated Instructors</div>
        <a class="bds" onclick="showSection('instructors')" href="javascript:void(0)" style="font-size:11px">View All</a></div>
    <div class="tbl-wrap">
    <table class="tbl"><thead><tr><th>#</th><th>Instructor</th><th>Rating</th><th>Reviews</th></tr></thead><tbody>
    <?php $topInstructors->data_seek(0); $rank=1; if($topInstructors->num_rows===0): ?>
    <tr><td colspan="4" style="color:var(--muted);text-align:center;padding:20px">No ratings yet.</td></tr>
    <?php else: while($ti=$topInstructors->fetch_assoc()): $medals=['🥇','🥈','🥉','4️⃣','5️⃣']; ?>
    <tr><td><span class="rank-medal"><?= $medals[$rank-1]??$rank ?></span></td>
        <td><a href="<?= $selfFile ?>?section=instructor_profile&view_instructor=<?= $ti['instructor_id'] ?>" style="color:var(--text);text-decoration:none"><?= htmlspecialchars($ti['fullname']) ?></a></td>
        <td style="color:var(--amber)">★ <?= $ti['avg_r'] ?></td><td><?= $ti['cnt'] ?></td></tr>
    <?php $rank++; endwhile; endif; ?></tbody></table>
    </div><!-- /tbl-wrap -->
</div>
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-arrow-trend-down" style="color:var(--red)"></i> Low Rated Instructors</div></div>
    <div class="tbl-wrap">
    <table class="tbl"><thead><tr><th>#</th><th>Instructor</th><th>Rating</th><th>Reviews</th></tr></thead><tbody>
    <?php $lowInstructors->data_seek(0); $rank=1; if($lowInstructors->num_rows===0): ?>
    <tr><td colspan="4" style="color:var(--muted);text-align:center;padding:20px">No ratings yet.</td></tr>
    <?php else: while($li=$lowInstructors->fetch_assoc()): ?>
    <tr><td><span style="color:var(--muted);font-size:12px"><?= $rank ?></span></td>
        <td><a href="<?= $selfFile ?>?section=instructor_profile&view_instructor=<?= $li['instructor_id'] ?>" style="color:var(--text);text-decoration:none"><?= htmlspecialchars($li['fullname']) ?></a></td>
        <td style="color:var(--red)">★ <?= $li['avg_r'] ?></td><td><?= $li['cnt'] ?></td></tr>
    <?php $rank++; endwhile; endif; ?></tbody></table>
    </div><!-- /tbl-wrap -->
</div>
</div>

<!-- Top & Low Students This Month -->
<div class="duo-grid">
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-star" style="color:var(--amber)"></i> Top 5 Students – <?= date('F Y') ?></div>
        <small style="color:var(--muted);font-size:10px">Resets monthly</small></div>
    <div class="tbl-wrap">
    <table class="tbl"><thead><tr><th>#</th><th>Student</th><th>Avg Grade</th><th>Sessions</th></tr></thead><tbody>
    <?php $topStudents->data_seek(0); $rank=1; if($topStudents->num_rows===0): ?>
    <tr><td colspan="4" style="color:var(--muted);text-align:center;padding:20px">No grades this month.</td></tr>
    <?php else: while($ts=$topStudents->fetch_assoc()): $medals=['🥇','🥈','🥉','4️⃣','5️⃣']; $gc=$ts['avg_grade']>=80?'var(--green)':($ts['avg_grade']>=60?'var(--amber)':'var(--red)'); ?>
    <tr><td><span class="rank-medal"><?= $medals[$rank-1]??$rank ?></span></td>
        <td><a href="<?= $selfFile ?>?section=student_profile&view_student=<?= $ts['student_id'] ?>" style="color:var(--text);text-decoration:none"><?= htmlspecialchars($ts['fullname']) ?></a></td>
        <td style="color:<?= $gc ?>;font-weight:600"><?= $ts['avg_grade'] ?>/100</td><td><?= $ts['sess_cnt'] ?></td></tr>
    <?php $rank++; endwhile; endif; ?></tbody></table>
    </div><!-- /tbl-wrap -->
</div>
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-arrow-trend-down" style="color:var(--red)"></i> Low Performing Students – <?= date('F Y') ?></div>
        <small style="color:var(--muted);font-size:10px">Resets monthly</small></div>
    <div class="tbl-wrap">
    <table class="tbl"><thead><tr><th>#</th><th>Student</th><th>Avg Grade</th><th>Sessions</th></tr></thead><tbody>
    <?php $lowStudents->data_seek(0); $rank=1; if($lowStudents->num_rows===0): ?>
    <tr><td colspan="4" style="color:var(--muted);text-align:center;padding:20px">No grades this month.</td></tr>
    <?php else: while($ls=$lowStudents->fetch_assoc()): ?>
    <tr><td><span style="color:var(--muted);font-size:12px"><?= $rank ?></span></td>
        <td><a href="<?= $selfFile ?>?section=student_profile&view_student=<?= $ls['student_id'] ?>" style="color:var(--text);text-decoration:none"><?= htmlspecialchars($ls['fullname']) ?></a></td>
        <td style="color:var(--red);font-weight:600"><?= $ls['avg_grade'] ?>/100</td><td><?= $ls['sess_cnt'] ?></td></tr>
    <?php $rank++; endwhile; endif; ?></tbody></table>
    </div><!-- /tbl-wrap -->
</div>
</div>

<!-- Recent Appointments -->
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-clock"></i> Recent Appointments</div>
        <a class="be" onclick="showSection('appointments')" href="javascript:void(0)" style="font-size:11px">View All</a></div>
    <div class="tbl-wrap">
    <table class="tbl"><thead><tr><th>Student</th><th>Instructor</th><th>Course</th><th>Start Date</th><th>Status</th></tr></thead><tbody>
    <?php $recentA=q($conn,"SELECT a.*,s.fullname AS sname,i.fullname AS iname,ic.course_type FROM appointments a JOIN students s ON s.student_id=a.student_id JOIN instructors i ON i.instructor_id=a.instructor_id JOIN instructor_courses ic ON ic.course_id=a.course_id ORDER BY a.requested_at DESC LIMIT 8");
    if($recentA->num_rows===0): ?><tr><td colspan="5" style="color:var(--muted);text-align:center;padding:20px">No appointments yet.</td></tr>
    <?php else: while($ra=$recentA->fetch_assoc()): $tc=$cColors[$ra['course_type']]??'tag-gray'; ?>
    <tr><td><?= htmlspecialchars($ra['sname']) ?></td><td><?= htmlspecialchars($ra['iname']) ?></td>
        <td><span class="tag <?= $tc ?>"><?= htmlspecialchars($ra['course_type']) ?></span></td>
        <td><?= date('M d, Y',strtotime($ra['start_date'])) ?></td>
        <td><span class="<?= $scmS[$ra['status']]??'bkd' ?>"><?= $scmL[$ra['status']]??$ra['status'] ?></span></td></tr>
    <?php endwhile; endif; ?></tbody></table>
    </div><!-- /tbl-wrap -->
</div>

<!-- Analytics Overview -->
<div style="font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin:20px 0 12px;padding-bottom:6px;border-bottom:1px solid var(--border)">
    <i class="fa-solid fa-chart-line me-2" style="color:var(--accent)"></i>Analytics Overview
</div>
<div class="analytics-grid" style="margin-bottom:16px">
    <div class="analytic-card"><div class="analytic-num" style="color:var(--accent)"><?= $todayLogins ?></div><div class="analytic-label"><i class="fa-solid fa-right-to-bracket me-1"></i>Today's Logins</div><div class="analytic-sub">All users</div></div>
    <div class="analytic-card"><div class="analytic-num" style="color:var(--green)"><?= $monthLogins ?></div><div class="analytic-label"><i class="fa-solid fa-calendar me-1"></i>Monthly Logins</div><div class="analytic-sub"><?= date('F Y') ?></div></div>
    <div class="analytic-card"><div class="analytic-num" style="color:var(--amber)"><?= $newStudentsMonth ?></div><div class="analytic-label"><i class="fa-solid fa-user-plus me-1"></i>New Students</div><div class="analytic-sub">This month</div></div>
    <div class="analytic-card"><div class="analytic-num" style="color:var(--amber)"><?= number_format($totalHoursTaught,1) ?>h</div><div class="analytic-label"><i class="fa-solid fa-clock me-1"></i>Hours Taught</div><div class="analytic-sub">All time</div></div>
</div>
<div class="duo-grid" style="margin-bottom:16px">
    <div class="panel"><div class="panel-head"><div class="panel-title"><i class="fa-solid fa-chart-line me-1"></i> Monthly New Students (6 Months)</div></div>
        <div style="position:relative;height:220px;padding:12px"><canvas id="chartNewStudents"></canvas></div></div>
    <div class="panel"><div class="panel-head"><div class="panel-title"><i class="fa-solid fa-chart-bar me-1"></i> Appointment Status by Month</div></div>
        <div style="position:relative;height:220px;padding:12px"><canvas id="chartApptStatus"></canvas></div></div>
</div>
<div class="duo-grid" style="margin-bottom:16px">
    <div class="panel"><div class="panel-head"><div class="panel-title"><i class="fa-solid fa-chart-pie me-1"></i> TDC vs PDC Completion</div></div>
        <div style="display:flex;align-items:center;justify-content:center;padding:16px;gap:24px">
            <div style="position:relative;width:180px;height:180px;flex-shrink:0"><canvas id="chartTdcPdc"></canvas></div>
            <div style="font-size:12px;display:flex;flex-direction:column;gap:10px">
                <div style="display:flex;align-items:center;gap:8px"><span style="width:12px;height:12px;border-radius:50%;background:var(--amber);display:inline-block;flex-shrink:0"></span><div><div style="font-weight:700;font-size:16px;color:var(--amber)"><?= $tdcDoneCount ?></div><div style="color:var(--muted);font-size:11px">TDC Completed</div></div></div>
                <div style="display:flex;align-items:center;gap:8px"><span style="width:12px;height:12px;border-radius:50%;background:var(--green);display:inline-block;flex-shrink:0"></span><div><div style="font-weight:700;font-size:16px;color:var(--green)"><?= $pdcDoneCount ?></div><div style="color:var(--muted);font-size:11px">PDC Completed</div></div></div>
                <div style="display:flex;align-items:center;gap:8px"><span style="width:12px;height:12px;border-radius:50%;background:var(--accent);display:inline-block;flex-shrink:0"></span><div><div style="font-weight:700;font-size:16px;color:var(--accent)"><?= $tdcActiveCount+$pdcActiveCount ?></div><div style="color:var(--muted);font-size:11px">Currently Active</div></div></div>
                <div style="display:flex;align-items:center;gap:8px"><span style="width:12px;height:12px;border-radius:50%;background:var(--red);display:inline-block;flex-shrink:0"></span><div><div style="font-weight:700;font-size:16px;color:var(--red)"><?= array_sum($chartApptCancelled) ?></div><div style="color:var(--muted);font-size:11px">Cancelled/Rejected</div></div></div>
            </div>
        </div>
    </div>
    <div class="panel"><div class="panel-head"><div class="panel-title"><i class="fa-solid fa-chart-bar me-1"></i> Grade Distribution (All Sessions)</div></div>
        <div style="position:relative;height:220px;padding:12px"><canvas id="chartGradeDist"></canvas></div></div>
</div>
<div class="panel mb-3">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-venus-mars me-1"></i> Student Gender Distribution</div></div>
        <div style="display:flex;align-items:center;justify-content:center;padding:16px;gap:24px;min-height:200px">
            <div style="position:relative;width:180px;height:180px;flex-shrink:0"><canvas id="chartGender"></canvas></div>
            <div style="font-size:12px;display:flex;flex-direction:column;gap:12px">
                <div style="display:flex;align-items:center;gap:8px"><span style="width:12px;height:12px;border-radius:50%;background:#60a5fa;display:inline-block;flex-shrink:0"></span><div><div style="font-weight:700;font-size:22px;color:#60a5fa"><?= $maleCount ?></div><div style="color:var(--muted);font-size:11px"><i class="fa-solid fa-mars me-1"></i>Male</div></div></div>
                <div style="display:flex;align-items:center;gap:8px"><span style="width:12px;height:12px;border-radius:50%;background:#f472b6;display:inline-block;flex-shrink:0"></span><div><div style="font-weight:700;font-size:22px;color:#f472b6"><?= $femaleCount ?></div><div style="color:var(--muted);font-size:11px"><i class="fa-solid fa-venus me-1"></i>Female</div></div></div>
                <?php if($otherGenderCount>0): ?><div style="display:flex;align-items:center;gap:8px"><span style="width:12px;height:12px;border-radius:50%;background:var(--muted);display:inline-block;flex-shrink:0"></span><div><div style="font-weight:700;font-size:22px;color:var(--muted)"><?= $otherGenderCount ?></div><div style="color:var(--muted);font-size:11px">Not Specified</div></div></div><?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ══ STUDENTS ══════════════════════════════════════════════ -->
<div id="section-students" class="section <?= $activeSection==='students'?'active':'' ?>">
<div class="duo-grid">
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-trophy" style="color:var(--amber)"></i> Top 5 Students This Month</div></div>
    <div class="tbl-wrap">
    <table class="tbl"><thead><tr><th>#</th><th>Student</th><th>Avg Grade</th></tr></thead><tbody>
    <?php $topStudents->data_seek(0); $rank=1; if($topStudents->num_rows===0): ?>
    <tr><td colspan="3" style="color:var(--muted);text-align:center;padding:16px">No grades yet.</td></tr>
    <?php else: while($ts=$topStudents->fetch_assoc()): $medals=['🥇','🥈','🥉','4️⃣','5️⃣']; $gc=$ts['avg_grade']>=80?'var(--green)':($ts['avg_grade']>=60?'var(--amber)':'var(--red)'); ?>
    <tr><td><?= $medals[$rank-1]??$rank ?></td><td><?= htmlspecialchars($ts['fullname']) ?></td><td style="color:<?= $gc ?>;font-weight:600"><?= $ts['avg_grade'] ?></td></tr>
    <?php $rank++; endwhile; endif; ?></tbody></table>
    </div><!-- /tbl-wrap -->
</div>
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-arrow-trend-down" style="color:var(--red)"></i> Low Performing This Month</div></div>
    <div class="tbl-wrap">
    <table class="tbl"><thead><tr><th>#</th><th>Student</th><th>Avg Grade</th></tr></thead><tbody>
    <?php $lowStudents->data_seek(0); $rank=1; if($lowStudents->num_rows===0): ?>
    <tr><td colspan="3" style="color:var(--muted);text-align:center;padding:16px">No grades yet.</td></tr>
    <?php else: while($ls=$lowStudents->fetch_assoc()): ?>
    <tr><td><?= $rank ?></td><td><?= htmlspecialchars($ls['fullname']) ?></td><td style="color:var(--red);font-weight:600"><?= $ls['avg_grade'] ?></td></tr>
    <?php $rank++; endwhile; endif; ?></tbody></table>
    </div><!-- /tbl-wrap -->
</div>
</div>

<div class="panel mb-3">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-plus"></i> Add Student</div></div>
    <div style="padding:16px">
    <form method="POST" class="form-grid" style="display:grid;grid-template-columns:1fr 1fr auto;gap:10px;align-items:end">
        <input type="hidden" name="action" value="add_student">
        <div><label class="form-label">Full Name</label><input type="text" name="fullname" class="di" required></div>
        <div><label class="form-label">Email</label><input type="email" name="email" class="di" required></div>
        <button type="submit" class="be" style="height:36px"><i class="fa-solid fa-plus"></i> Add</button>
    </form>
    <div style="font-size:10px;color:var(--muted);margin-top:8px"><i class="fa-solid fa-circle-info me-1"></i>A system-generated 6-digit password will be emailed to the student automatically.</div>
    </div>
</div>

<div class="panel mb-3">
    <div style="padding:12px 16px;border-bottom:1px solid var(--border);display:flex;gap:10px;flex-wrap:wrap;align-items:center">
        <input type="text" id="stuSearch" class="di" style="width:220px" placeholder="Search students…" oninput="filterStudents()">
        <select id="stuStatusFilter" class="di" style="width:150px" onchange="filterStudents()">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
        <select id="stuVerifiedFilter" class="di" style="width:150px" onchange="filterStudents()">
            <option value="">All Verification</option>
            <option value="yes">Verified</option>
            <option value="no">Unverified</option>
        </select>
    </div>
</div>

<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-user-graduate"></i> All Students</div></div>
    <div class="tbl-wrap">
    <table class="tbl" id="studentsTable"><thead><tr><th>Name</th><th>Email</th><th>Mobile</th><th>Verified</th><th>Status</th><th>TDC Done</th><th>PDC Done</th><th>Joined</th><th></th></tr></thead><tbody>
    <?php $students=q($conn,"SELECT s.*,(SELECT CASE WHEN COALESCE(SUM(sess.hours_covered),0)>=15 THEN 1 ELSE 0 END FROM sessions sess JOIN appointments a ON a.appointment_id=sess.appointment_id JOIN instructor_courses ic ON ic.course_id=a.course_id WHERE sess.student_id=s.student_id AND ic.course_type LIKE 'TDC%' AND sess.status='completed') AS tdc_done,(SELECT COUNT(*) FROM appointments a JOIN instructor_courses ic ON ic.course_id=a.course_id WHERE a.student_id=s.student_id AND ic.course_type LIKE 'PDC%' AND a.status='completed') AS pdc_done FROM students s ORDER BY s.created_at DESC");
    if($students->num_rows===0): ?><tr><td colspan="10" style="color:var(--muted);text-align:center;padding:20px">No students.</td></tr>
    <?php else: while($st=$students->fetch_assoc()): $stActive = !isset($st['is_active']) || $st['is_active']==1; ?>
    <tr data-name="<?= strtolower(htmlspecialchars($st['fullname'])) ?>" data-email="<?= strtolower(htmlspecialchars($st['email'])) ?>" data-status="<?= $stActive?'active':'inactive' ?>" data-verified="<?= $st['is_verified']?'yes':'no' ?>">
        <td><?= htmlspecialchars($st['fullname']) ?></td>
        <td style="font-size:11px"><?= htmlspecialchars($st['email']) ?></td>
        <td style="font-size:11px"><?= $st['mobile']?htmlspecialchars($st['mobile']):'—' ?></td>
        <td><?= $st['is_verified']?'<span class="bkg">Yes</span>':'<span class="bkp">No</span>' ?></td>
        <td><?= $stActive?'<span class="bkg">Active</span>':'<span class="bkd">Inactive</span>' ?></td>
        <td><?= $st['tdc_done']?'<span class="bkg">✓</span>':'—' ?></td>
        <td><?= $st['pdc_done']?'<span class="bkg">✓</span>':'—' ?></td>
        <td style="font-size:11px;color:var(--muted)"><?= date('M d, Y',strtotime($st['created_at'])) ?></td>
        <td style="display:flex;gap:4px;flex-wrap:wrap">
            <a class="bds" style="font-size:11px;text-decoration:none" href="<?= $selfFile ?>?section=student_profile&view_student=<?= $st['student_id'] ?>">View</a>
            <button type="button" class="bds" style="font-size:11px" onclick='openEditStudentModal(<?= json_encode(["id"=>$st["student_id"],"fullname"=>$st["fullname"],"email"=>$st["email"],"mobile"=>$st["mobile"]??"","address"=>$st["address"]??"","gender"=>$st["gender"]??""]) ?>)'>Edit</button>
            <form method="POST" style="display:inline">
                <input type="hidden" name="action" value="toggle_student"><input type="hidden" name="student_id" value="<?= $st['student_id'] ?>">
                <button type="submit" class="bds" style="font-size:11px"><?= $stActive?'Deactivate':'Activate' ?></button>
            </form>
            <form method="POST" style="display:inline" onsubmit="return confirm('Delete this student?')">
                <input type="hidden" name="action" value="delete_student"><input type="hidden" name="student_id" value="<?= $st['student_id'] ?>">
                <button type="submit" class="ber" style="font-size:11px">Delete</button>
            </form>
        </td>
    </tr>
    <?php endwhile; endif; ?></tbody></table>
    </div><!-- /tbl-wrap -->
</div>

<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-user-graduate"></i> Recent Student Logins</div></div>
    <div class="tbl-wrap">
    <table class="tbl"><thead><tr><th>Student</th><th>Status</th><th>⚠</th><th>Time</th></tr></thead><tbody>
    <?php $sLogins=q($conn,"SELECT slh.*,s.fullname FROM student_login_history slh JOIN students s ON s.student_id=slh.student_id ORDER BY slh.login_datetime DESC LIMIT 10");
    if($sLogins->num_rows===0): ?><tr><td colspan="4" style="color:var(--muted);text-align:center;padding:16px">No student logins yet.</td></tr>
    <?php else: while($sl=$sLogins->fetch_assoc()): $fc=$sFailMap[(int)$sl['student_id']]??0; ?>
    <tr><td style="font-size:12px"><?= htmlspecialchars($sl['fullname']) ?></td>
        <td><?= $sl['status']==='success'?'<span class="bkg" style="font-size:10px">✓ OK</span>':'<span class="bkr" style="font-size:10px">✗ Fail</span>' ?></td>
        <td style="text-align:center"><?php if($fc>0): ?><span style="background:rgba(229,85,85,.15);color:var(--red);border-radius:8px;padding:1px 8px;font-size:11px"><?= $fc ?></span><?php else: ?>—<?php endif; ?></td>
        <td style="font-size:10px;color:var(--muted);white-space:nowrap"><?= date('M d, g:i A',strtotime($sl['login_datetime'])) ?></td></tr>
    <?php endwhile; endif; ?></tbody></table>
    </div><!-- /tbl-wrap -->
</div>
</div>

<!-- Edit Student Modal -->
<div id="editStudentModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:200;align-items:center;justify-content:center;padding:16px">
<div style="background:var(--bg2);border:1px solid var(--border);border-radius:12px;width:440px;max-width:95vw;padding:24px">
    <div style="font-size:15px;font-weight:600;margin-bottom:16px"><i class="fa-solid fa-pen me-2"></i>Edit Student</div>
    <form method="POST">
        <input type="hidden" name="action" value="edit_student">
        <input type="hidden" name="student_id" id="es_id">
        <div style="margin-bottom:10px"><label class="form-label">Full Name</label><input type="text" name="fullname" id="es_fullname" class="di" required></div>
        <div style="margin-bottom:10px"><label class="form-label">Email</label><input type="email" name="email" id="es_email" class="di" required></div>
        <div style="margin-bottom:10px"><label class="form-label">Mobile</label><input type="text" name="mobile" id="es_mobile" class="di"></div>
        <div style="margin-bottom:10px"><label class="form-label">Address</label><input type="text" name="address" id="es_address" class="di"></div>
        <div style="margin-bottom:16px"><label class="form-label">Gender</label>
            <select name="gender" id="es_gender" class="di"><option value="">—</option><option value="Male">Male</option><option value="Female">Female</option><option value="Other">Other</option></select>
        </div>
        <div style="display:flex;gap:8px;justify-content:flex-end">
            <button type="button" class="bds" onclick="closeEditStudentModal()">Cancel</button>
            <button type="submit" class="be"><i class="fa-solid fa-check"></i> Save Changes</button>
        </div>
    </form>
</div></div>

<!-- Student Profile -->
<div id="section-student_profile" class="section <?= $activeSection==='student_profile'?'active':'' ?>">
<?php if($viewStudentId): $sp=q($conn,"SELECT * FROM students WHERE student_id=$viewStudentId")->fetch_assoc(); ?>
<a href="<?= $selfFile ?>?section=students" class="bds mb-3" style="display:inline-flex;align-items:center;gap:6px"><i class="fa-solid fa-arrow-left"></i> Back to Students</a>
<?php if($sp):
$spIdPath='../assets/uploads/student_ids/';
?>
<div class="panel" style="margin-top:12px">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-user-graduate me-1"></i><?= htmlspecialchars($sp['fullname']) ?></div></div>
    <div style="padding:16px;display:flex;gap:20px;align-items:flex-start;flex-wrap:wrap">
        <div style="width:64px;height:64px;border-radius:50%;background:var(--accent);display:flex;align-items:center;justify-content:center;font-size:26px;font-weight:700;color:#fff;flex-shrink:0"><?= strtoupper(substr($sp['fullname'],0,1)) ?></div>
        <div style="flex:1;min-width:260px;display:grid;grid-template-columns:1fr 1fr;gap:12px">
            <div><span style="font-size:11px;color:var(--muted)">Email</span><div style="font-size:13px"><?= htmlspecialchars($sp['email']) ?></div></div>
            <div><span style="font-size:11px;color:var(--muted)">Mobile</span><div style="font-size:13px"><?= $sp['mobile']?htmlspecialchars($sp['mobile']):'—' ?></div></div>
            <div><span style="font-size:11px;color:var(--muted)">Gender</span><div style="font-size:13px"><?php if(!empty($sp['gender'])): ?><i class="fa-solid fa-<?= $sp['gender']==='Male'?'mars':($sp['gender']==='Female'?'venus':'genderless') ?> me-1" style="color:<?= $sp['gender']==='Male'?'#60a5fa':'#f472b6' ?>"></i><?= htmlspecialchars($sp['gender']) ?><?php else: ?>—<?php endif; ?></div></div>
            <div><span style="font-size:11px;color:var(--muted)">Verified</span><div><?= $sp['is_verified']?'<span class="bkg">Yes</span>':'<span class="bkp">No</span>' ?></div></div>
            <div><span style="font-size:11px;color:var(--muted)">Address</span><div style="font-size:12px"><?= !empty($sp['address'])?htmlspecialchars($sp['address']):'—' ?></div></div>
            <div><span style="font-size:11px;color:var(--muted)">Joined</span><div style="font-size:13px"><?= date('F d, Y',strtotime($sp['created_at'])) ?></div></div>
            <div><span style="font-size:11px;color:var(--muted)">Facebook</span><div style="font-size:12px"><?php if(!empty($sp['fb_link'])): ?><a href="<?= htmlspecialchars($sp['fb_link']) ?>" target="_blank" style="color:#60a5fa;text-decoration:none"><i class="fa-brands fa-facebook me-1"></i>View Profile</a><?php else: ?>—<?php endif; ?></div></div>
        </div>
        <div style="flex-shrink:0;text-align:center">
            <div style="font-size:10px;color:var(--muted);margin-bottom:8px"><i class="fa-solid fa-id-card me-1"></i>National ID / Valid ID</div>
            <?php if(!empty($sp['id_photo'])&&file_exists($spIdPath.$sp['id_photo'])): ?>
            <img src="<?= $spIdPath.htmlspecialchars($sp['id_photo']) ?>" style="max-width:200px;max-height:120px;border-radius:8px;border:2px solid var(--border);object-fit:cover;display:block;cursor:pointer;transition:.15s" onclick="openLightbox(this.src,'<?= addslashes(htmlspecialchars($spIdPath.$sp['id_photo'])) ?>')" onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='var(--border)'">
            <div style="display:flex;gap:6px;margin-top:6px;justify-content:center">
                <a href="<?= 'view_image.php?type=student_ids&file='.urlencode($sp['id_photo']).'&back='.urlencode($_SERVER['REQUEST_URI']) ?>" class="bds" style="font-size:11px"><i class="fa-solid fa-eye me-1"></i>View</a>
            </div>
            <?php else: ?>
            <div style="width:160px;height:100px;border:2px dashed var(--border);border-radius:8px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px">
                <i class="fa-solid fa-id-card" style="font-size:22px;color:var(--border)"></i>
                <span style="font-size:11px;color:var(--muted)">No ID uploaded</span>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Session Grades -->
<?php $studentGrades=q($conn,"SELECT sess.*,ic.course_type,DATE_FORMAT(sess.session_date,'%b %d') AS date_fmt FROM sessions sess JOIN appointments a ON a.appointment_id=sess.appointment_id JOIN instructor_courses ic ON ic.course_id=a.course_id WHERE sess.student_id=$viewStudentId AND sess.grade IS NOT NULL ORDER BY sess.session_date DESC LIMIT 20");
$avgGradeProfile=q($conn,"SELECT ROUND(AVG(grade),1) AS ag FROM sessions WHERE student_id=$viewStudentId AND grade IS NOT NULL AND DATE_FORMAT(session_date,'%Y-%m')='$thisMonth'")->fetch_assoc()['ag'];
if($studentGrades->num_rows>0): ?>
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-graduation-cap"></i> Session Grades</div>
        <?php if($avgGradeProfile): $gc=$avgGradeProfile>=80?'var(--green)':($avgGradeProfile>=60?'var(--amber)':'var(--red)'); ?>
        <div style="font-size:12px">This month avg: <strong style="color:<?= $gc ?>"><?= $avgGradeProfile ?>/100</strong></div>
        <?php endif; ?></div>
    <div class="tbl-wrap">
    <table class="tbl"><thead><tr><th>Date</th><th>Course</th><th>Grade</th><th>Improvements</th></tr></thead><tbody>
    <?php while($sg=$studentGrades->fetch_assoc()): $gc=$sg['grade']>=80?'var(--green)':($sg['grade']>=60?'var(--amber)':'var(--red)'); ?>
    <tr><td><?= $sg['date_fmt'] ?></td>
        <td><span class="tag <?= $cColors[$sg['course_type']]??'tag-gray' ?>"><?= htmlspecialchars($sg['course_type']) ?></span></td>
        <td><strong style="color:<?= $gc ?>"><?= $sg['grade'] ?>/100</strong><div class="grade-bar"><div class="grade-fill" style="width:<?= $sg['grade'] ?>%;background:<?= $gc ?>"></div></div></td>
        <td style="font-size:11px;color:var(--muted)"><?= $sg['improvements']?htmlspecialchars($sg['improvements']):'—' ?></td></tr>
    <?php endwhile; ?></tbody></table>
    </div><!-- /tbl-wrap -->
</div>
<?php endif; ?>

<!-- Appointment History -->
<div class="panel">
    <div class="panel-head"><div class="panel-title">Appointment History</div></div>
    <div class="tbl-wrap">
    <table class="tbl"><thead><tr><th>Course</th><th>Instructor</th><th>Start Date</th><th>Hours</th><th>Status</th></tr></thead><tbody>
    <?php $sappts=q($conn,"SELECT a.*,ic.course_type,i.fullname AS iname,(SELECT SUM(hours_covered) FROM sessions WHERE appointment_id=a.appointment_id AND status='completed') AS hdone FROM appointments a JOIN instructor_courses ic ON ic.course_id=a.course_id JOIN instructors i ON i.instructor_id=a.instructor_id WHERE a.student_id=$viewStudentId ORDER BY a.requested_at DESC");
    if($sappts->num_rows===0): ?><tr><td colspan="5" style="color:var(--muted);text-align:center;padding:16px">No appointments.</td></tr>
    <?php else: while($sa=$sappts->fetch_assoc()): $tc=$cColors[$sa['course_type']]??'tag-gray'; ?>
    <tr><td><span class="tag <?= $tc ?>"><?= htmlspecialchars($sa['course_type']) ?></span></td>
        <td><?= htmlspecialchars($sa['iname']) ?></td>
        <td><?= date('M d, Y',strtotime($sa['start_date'])) ?></td>
        <td><?= (float)($sa['hdone']??0) ?>/<?= $sa['total_hours'] ?> hrs</td>
        <td><span class="<?= $scmS[$sa['status']]??'bkd' ?>"><?= $scmL[$sa['status']]??$sa['status'] ?></span></td></tr>
    <?php endwhile; endif; ?></tbody></table>
    </div><!-- /tbl-wrap -->
</div>
<?php endif; endif; ?>
</div>

<!-- ══ INSTRUCTORS ════════════════════════════════════════════ -->
<div id="section-instructors" class="section <?= $activeSection==='instructors'?'active':'' ?>">
<div class="duo-grid">
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-trophy" style="color:var(--amber)"></i> Top 5 Rated Instructors</div></div>
    <div class="tbl-wrap">
    <table class="tbl"><thead><tr><th>#</th><th>Instructor</th><th>Rating</th><th>Reviews</th></tr></thead><tbody>
    <?php $topInstructors->data_seek(0); $rank=1; if($topInstructors->num_rows===0): ?>
    <tr><td colspan="4" style="color:var(--muted);text-align:center;padding:16px">No ratings yet.</td></tr>
    <?php else: while($ti=$topInstructors->fetch_assoc()): $medals=['🥇','🥈','🥉','4️⃣','5️⃣']; ?>
    <tr><td><?= $medals[$rank-1]??$rank ?></td><td><?= htmlspecialchars($ti['fullname']) ?></td><td style="color:var(--amber)">★ <?= $ti['avg_r'] ?></td><td><?= $ti['cnt'] ?></td></tr>
    <?php $rank++; endwhile; endif; ?></tbody></table>
    </div><!-- /tbl-wrap -->
</div>
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-arrow-trend-down" style="color:var(--red)"></i> Low Rated Instructors</div></div>
    <div class="tbl-wrap">
    <table class="tbl"><thead><tr><th>#</th><th>Instructor</th><th>Rating</th></tr></thead><tbody>
    <?php $lowInstructors->data_seek(0); $rank=1; if($lowInstructors->num_rows===0): ?>
    <tr><td colspan="3" style="color:var(--muted);text-align:center;padding:16px">No ratings yet.</td></tr>
    <?php else: while($li=$lowInstructors->fetch_assoc()): ?>
    <tr><td><?= $rank ?></td><td><?= htmlspecialchars($li['fullname']) ?></td><td style="color:var(--red)">★ <?= $li['avg_r'] ?></td></tr>
    <?php $rank++; endwhile; endif; ?></tbody></table>
    </div><!-- /tbl-wrap -->
</div>
</div>
<div class="panel mb-3">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-plus"></i> Add Instructor</div></div>
    <div style="padding:16px">
    <form method="POST" class="form-grid" style="display:grid;grid-template-columns:1fr 1fr auto;gap:10px;align-items:end">
        <input type="hidden" name="action" value="add_instructor">
        <div><label class="form-label">Full Name</label><input type="text" name="fullname" class="di" required></div>
        <div><label class="form-label">Email</label><input type="email" name="email" class="di" required></div>
        <button type="submit" class="be" style="height:36px"><i class="fa-solid fa-plus"></i> Add</button>
    </form>
    <div style="font-size:10px;color:var(--muted);margin-top:8px"><i class="fa-solid fa-circle-info me-1"></i>A system-generated 6-digit password will be emailed to the instructor automatically.</div>
    </div>
</div>
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-chalkboard-user"></i> All Instructors</div></div>
    <div class="tbl-wrap">
    <table class="tbl"><thead><tr><th>Name</th><th>Email</th><th>Mobile</th><th>Courses</th><th>Avg Rating</th><th>Appointments</th><th>Certified</th><th></th></tr></thead><tbody>
    <?php $instrs=q($conn,"SELECT i.*,(SELECT COUNT(*) FROM instructor_courses WHERE instructor_id=i.instructor_id AND is_active=1) AS course_cnt,(SELECT ROUND(AVG(rating),1) FROM instructor_ratings WHERE instructor_id=i.instructor_id) AS avg_r,(SELECT COUNT(*) FROM appointments WHERE instructor_id=i.instructor_id) AS appt_cnt FROM instructors i ORDER BY i.fullname ASC");
    if($instrs->num_rows===0): ?><tr><td colspan="8" style="color:var(--muted);text-align:center;padding:20px">No instructors.</td></tr>
    <?php else: while($ins=$instrs->fetch_assoc()): $certOk=!empty($ins['certificate_image'])&&file_exists($uploadDir.$ins['certificate_image']); ?>
    <tr>
        <td><?= htmlspecialchars($ins['fullname']) ?></td>
        <td style="font-size:11px"><?= htmlspecialchars($ins['email']) ?></td>
        <td style="font-size:11px"><?= isset($ins['mobile'])&&$ins['mobile']?htmlspecialchars($ins['mobile']):'—' ?></td>
        <td><?= $ins['course_cnt'] ?> active</td>
        <td><?= $ins['avg_r']?'★ '.$ins['avg_r']:'—' ?></td>
        <td><?= $ins['appt_cnt'] ?></td>
        <td><?= $certOk?'<span class="cert-badge"><i class="fa-solid fa-certificate"></i> Yes</span>':'—' ?></td>
        <td style="display:flex;gap:4px">
            <a class="bds" style="font-size:11px;text-decoration:none" href="<?= $selfFile ?>?section=instructor_profile&view_instructor=<?= $ins['instructor_id'] ?>">View</a>
            <form method="POST" style="display:inline" onsubmit="return confirm('Delete this instructor?')">
                <input type="hidden" name="action" value="delete_instructor"><input type="hidden" name="instructor_id" value="<?= $ins['instructor_id'] ?>">
                <button type="submit" class="ber" style="font-size:11px">Delete</button>
            </form>
        </td>
    </tr>
    <?php endwhile; endif; ?></tbody></table>
    </div><!-- /tbl-wrap -->
</div>

<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-chalkboard-user"></i> Recent Instructor Logins</div></div>
    <div class="tbl-wrap">
    <table class="tbl"><thead><tr><th>Instructor</th><th>Status</th><th>Time</th></tr></thead><tbody>
    <?php $iLogins=q($conn,"SELECT ilh.*,i.fullname FROM instructor_login_history ilh JOIN instructors i ON i.instructor_id=ilh.instructor_id ORDER BY ilh.login_time DESC LIMIT 10");
    if($iLogins->num_rows===0): ?><tr><td colspan="3" style="color:var(--muted);text-align:center;padding:16px">No instructor logins yet.</td></tr>
    <?php else: while($il=$iLogins->fetch_assoc()): ?>
    <tr><td style="font-size:12px"><?= htmlspecialchars($il['fullname']) ?></td>
        <td><?= $il['login_status']==='success'?'<span class="bkg" style="font-size:10px">✓ OK</span>':'<span class="bkr" style="font-size:10px">✗ Fail</span>' ?></td>
        <td style="font-size:10px;color:var(--muted);white-space:nowrap"><?= date('M d, g:i A',strtotime($il['login_time'])) ?></td></tr>
    <?php endwhile; endif; ?></tbody></table>
    </div><!-- /tbl-wrap -->
</div>
</div>

<!-- Instructor Profile -->
<div id="section-instructor_profile" class="section <?= $activeSection==='instructor_profile'?'active':'' ?>">
<?php if($viewInstructorId):
    $ip=q($conn,"SELECT * FROM instructors WHERE instructor_id=$viewInstructorId")->fetch_assoc();
?>
<a href="<?= $selfFile ?>?section=instructors" class="bds" style="display:inline-flex;align-items:center;gap:6px;margin-bottom:12px"><i class="fa-solid fa-arrow-left"></i> Back to Instructors</a>
<?php if($ip):
    $certOk=!empty($ip['certificate_image'])&&file_exists($uploadDir.$ip['certificate_image']);
    $instrRating=q($conn,"SELECT ROUND(AVG(rating),1) AS r,COUNT(*) AS c FROM instructor_ratings WHERE instructor_id=$viewInstructorId")->fetch_assoc();
    $ipTotalStudents=(int)q($conn,"SELECT COUNT(DISTINCT student_id) AS c FROM appointments WHERE instructor_id=$viewInstructorId AND status IN('approved','in_progress','completed')")->fetch_assoc()['c'];
    $ipTotalHrs=(float)q($conn,"SELECT COALESCE(SUM(hours_covered),0) AS h FROM sessions WHERE instructor_id=$viewInstructorId AND status='completed'")->fetch_assoc()['h'];
    $ipTotalSess=(int)q($conn,"SELECT COUNT(*) AS c FROM sessions WHERE instructor_id=$viewInstructorId AND status='completed'")->fetch_assoc()['c'];
?>
<!-- Info Card -->
<div class="panel" style="margin-top:12px;margin-bottom:16px">
    <div class="panel-head">
        <div class="panel-title">
            <div style="width:36px;height:36px;border-radius:50%;background:var(--accent);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:#fff;flex-shrink:0"><?= strtoupper(substr($ip['fullname'],0,1)) ?></div>
            <?= htmlspecialchars($ip['fullname']) ?>
        </div>
        <?php if($instrRating['r']): ?><div style="color:var(--amber)">★ <?= $instrRating['r'] ?> <span style="font-size:11px;color:var(--muted)">(<?= $instrRating['c'] ?> reviews)</span></div><?php endif; ?>
    </div>
    <div style="padding:16px;display:flex;gap:20px;align-items:flex-start;flex-wrap:wrap">
        <!-- Info grid -->
        <div style="flex:1;min-width:260px;display:grid;grid-template-columns:1fr 1fr;gap:12px">
            <div><span style="font-size:11px;color:var(--muted)">Email</span><div style="font-size:13px"><?= htmlspecialchars($ip['email']) ?></div></div>
            <div><span style="font-size:11px;color:var(--muted)">Mobile</span><div style="font-size:13px"><?= isset($ip['mobile'])&&$ip['mobile']?htmlspecialchars($ip['mobile']):'—' ?></div></div>
            <div><span style="font-size:11px;color:var(--muted)">Joined</span><div style="font-size:13px"><?= date('F d, Y',strtotime($ip['created_at'])) ?></div></div>
            <?php if(isset($ip['bio'])&&$ip['bio']): ?>
            <div style="grid-column:1/-1"><span style="font-size:11px;color:var(--muted)">Bio</span><div style="font-size:12px;margin-top:2px"><?= nl2br(htmlspecialchars($ip['bio'])) ?></div></div>
            <?php endif; ?>
            <?php if($certOk): ?>
            <div style="grid-column:1/-1">
                <span style="font-size:11px;color:var(--muted)">Instructor Certificate</span>
                <div style="margin-top:6px">
                    <img src="<?= htmlspecialchars('../assets/uploads/certificates/'.$ip['certificate_image']) ?>" style="max-height:100px;border-radius:6px;border:1px solid var(--border);display:block;margin-bottom:6px">
                    <a href="<?= 'view_image.php?type=certificates&file='.urlencode($ip['certificate_image']).'&back='.urlencode($_SERVER['REQUEST_URI']) ?>" class="bds" style="font-size:11px"><i class="fa-solid fa-eye me-1"></i>View</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <!-- Stats -->
        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-start">
            <div style="background:var(--bg3);border-radius:10px;padding:12px 18px;text-align:center;min-width:80px"><div style="font-size:20px;font-weight:700;color:var(--accent)"><?= $ipTotalStudents ?></div><div style="font-size:10px;color:var(--muted)">Students</div></div>
            <div style="background:var(--bg3);border-radius:10px;padding:12px 18px;text-align:center;min-width:80px"><div style="font-size:20px;font-weight:700;color:var(--green)"><?= $ipTotalHrs ?>h</div><div style="font-size:10px;color:var(--muted)">Hours Taught</div></div>
            <div style="background:var(--bg3);border-radius:10px;padding:12px 18px;text-align:center;min-width:80px"><div style="font-size:20px;font-weight:700;color:var(--text)"><?= $ipTotalSess ?></div><div style="font-size:10px;color:var(--muted)">Sessions</div></div>
            <?php if($instrRating['r']): ?><div style="background:var(--bg3);border-radius:10px;padding:12px 18px;text-align:center;min-width:80px"><div style="font-size:20px;font-weight:700;color:var(--amber)">★ <?= $instrRating['r'] ?></div><div style="font-size:10px;color:var(--muted)">Avg Rating</div></div><?php endif; ?>
        </div>
    </div>
</div>

<!-- Courses Offered -->
<div class="panel" style="margin-bottom:16px">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-book-open"></i> Courses Offered</div></div>
    <div class="tbl-wrap">
    <table class="tbl"><thead><tr><th>Course Type</th><th>Price/Hr</th><th>Student Limit/Date</th><th>Description</th><th>Requirements</th><th>Status</th><th></th></tr></thead><tbody>
    <?php $icr=q($conn,"SELECT * FROM instructor_courses WHERE instructor_id=$viewInstructorId ORDER BY course_type");
    while($c=$icr->fetch_assoc()): $tc=$cColors[$c['course_type']]??'tag-gray'; ?>
    <tr><td><span class="tag <?= $tc ?>"><?= htmlspecialchars($c['course_type']) ?></span></td>
        <td>₱<?= number_format($c['price_per_hr'],2) ?></td>
        <td><?php if(!empty($c['student_limit'])): ?><span style="background:rgba(124,106,247,.15);color:var(--accent);padding:2px 10px;border-radius:20px;font-size:11px"><i class="fa-solid fa-users me-1"></i><?= $c['student_limit'] ?>/date</span><?php else: ?><span style="color:var(--muted);font-size:11px">Unlimited</span><?php endif; ?></td>
        <td style="font-size:11px;color:var(--muted);max-width:160px"><?= !empty($c['description'])?htmlspecialchars($c['description']):'—' ?></td>
        <td style="font-size:11px;color:var(--muted);max-width:160px"><?= !empty($c['requirements'])?htmlspecialchars($c['requirements']):'—' ?></td>
        <td><?= $c['is_active']?'<span class="bkg">Active</span>':'<span class="bkd">Inactive</span>' ?></td>
        <td><button type="button" class="bds" style="font-size:11px" onclick='openAdminEditCourse(<?= json_encode(["id"=>$c["course_id"],"type"=>$c["course_type"],"price"=>$c["price_per_hr"],"limit"=>$c["student_limit"]??"","desc"=>$c["description"]??"","reqs"=>$c["requirements"]??""]) ?>)'><i class="fa-solid fa-pen"></i> Edit</button></td></tr>
    <?php endwhile; ?></tbody></table>
    </div><!-- /tbl-wrap -->
</div>

<!-- Admin Edit Course Modal -->
<div id="adminEditCourseModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:300;align-items:center;justify-content:center;padding:16px">
<div style="background:var(--bg2);border:1px solid var(--border);border-radius:12px;width:440px;max-width:100%;padding:24px">
    <div style="font-size:15px;font-weight:600;margin-bottom:4px"><i class="fa-solid fa-pen me-2" style="color:var(--accent)"></i>Edit Course</div>
    <div id="aec_course_type" style="font-size:12px;color:var(--muted);margin-bottom:16px"></div>
    <form method="POST">
    <input type="hidden" name="action" value="admin_edit_course">
    <input type="hidden" name="course_id" id="aec_course_id">
    <div style="margin-bottom:12px">
        <label class="form-label">Package Price (₱) *</label>
        <input type="number" name="price_per_hr" id="aec_price" class="di" min="0" step="0.01" required>
    </div>
    <div style="margin-bottom:12px">
        <label class="form-label">Student Limit per Date</label>
        <input type="number" name="student_limit" id="aec_limit" class="di" min="1" max="50" placeholder="Leave blank = unlimited">
    </div>
    <div style="margin-bottom:12px">
        <label class="form-label">Description (optional)</label>
        <input type="text" name="description" id="aec_desc" class="di" placeholder="Short description">
    </div>
    <div style="margin-bottom:16px">
        <label class="form-label">Requirements (optional)</label>
        <input type="text" name="requirements" id="aec_requirements" class="di" placeholder="e.g. Valid ID, closed shoes, own helmet…">
    </div>
    <div style="font-size:10px;color:var(--muted);margin-bottom:16px"><i class="fa-solid fa-circle-info me-1"></i>The instructor will be notified of this change.</div>
    <div style="display:flex;gap:8px;justify-content:flex-end">
        <button type="button" class="bds" onclick="closeAdminEditCourse()">Cancel</button>
        <button type="submit" class="be"><i class="fa-solid fa-save me-1"></i>Save Changes</button>
    </div>
    </form>
</div></div>

<!-- Appointment History -->
<div class="panel" style="margin-bottom:16px">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-calendar-check"></i> Appointment History</div></div>
    <div class="tbl-wrap">
    <table class="tbl"><thead><tr><th>Student</th><th>Course</th><th>Start Date</th><th>Hours Done</th><th>TDC Grade</th><th>Status</th></tr></thead><tbody>
    <?php $ipAppts=q($conn,"SELECT a.*,s.fullname AS sname,ic.course_type,(SELECT SUM(hours_covered) FROM sessions WHERE appointment_id=a.appointment_id AND status='completed') AS hdone FROM appointments a JOIN students s ON s.student_id=a.student_id JOIN instructor_courses ic ON ic.course_id=a.course_id WHERE a.instructor_id=$viewInstructorId ORDER BY a.requested_at DESC");
    if($ipAppts->num_rows===0): ?><tr><td colspan="6" style="color:var(--muted);text-align:center;padding:20px">No appointments yet.</td></tr>
    <?php else: while($ap=$ipAppts->fetch_assoc()): $tc=$cColors[$ap['course_type']]??'tag-gray'; $gc=isset($ap['tdc_overall_grade'])&&$ap['tdc_overall_grade']?($ap['tdc_overall_grade']>=80?'var(--green)':($ap['tdc_overall_grade']>=60?'var(--amber)':'var(--red)')):''; ?>
    <tr>
        <td><?= htmlspecialchars($ap['sname']) ?></td>
        <td><span class="tag <?= $tc ?>"><?= htmlspecialchars($ap['course_type']) ?></span></td>
        <td><?= date('M d, Y',strtotime($ap['start_date'])) ?></td>
        <td><?= (float)($ap['hdone']??0) ?>/<?= $ap['total_hours'] ?> hrs</td>
        <td><?= ($gc&&$ap['tdc_overall_grade'])?'<strong style="color:'.$gc.'">'.$ap['tdc_overall_grade'].'/100</strong>':'—' ?></td>
        <td><span class="<?= $scmS[$ap['status']]??'bkd' ?>"><?= $scmL[$ap['status']]??$ap['status'] ?></span></td>
    </tr>
    <?php endwhile; endif; ?></tbody></table>
    </div><!-- /tbl-wrap -->
</div>

<!-- Student Reviews -->
<?php $ipReviews=q($conn,"SELECT ir.*,s.fullname AS sname,ic.course_type FROM instructor_ratings ir JOIN students s ON s.student_id=ir.student_id JOIN appointments a ON a.appointment_id=ir.appointment_id JOIN instructor_courses ic ON ic.course_id=a.course_id WHERE ir.instructor_id=$viewInstructorId ORDER BY ir.rated_at DESC");
if($ipReviews->num_rows>0): ?>
<div class="panel" style="margin-bottom:16px">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-star" style="color:var(--amber)"></i> Student Reviews</div></div>
    <?php while($rv=$ipReviews->fetch_assoc()): ?>
    <div style="padding:12px 16px;border-bottom:1px solid var(--border)">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
            <div style="font-size:12px;font-weight:600"><?= htmlspecialchars($rv['sname']) ?></div>
            <span class="tag <?= $cColors[$rv['course_type']]??'tag-gray' ?>"><?= htmlspecialchars($rv['course_type']) ?></span>
            <div style="color:var(--amber);font-size:12px;margin-left:auto"><?php for($i=1;$i<=5;$i++) echo $i<=$rv['rating']?'★':'☆'; ?></div>
            <div style="font-size:10px;color:var(--muted)"><?= date('M d, Y',strtotime($rv['rated_at'])) ?></div>
        </div>
        <?php if($rv['comment']): ?><div style="font-size:12px;color:var(--muted)"><?= nl2br(htmlspecialchars($rv['comment'])) ?></div><?php endif; ?>
    </div>
    <?php endwhile; ?>
</div>
<?php endif; ?>

<?php endif; endif; ?>
</div>

<!-- ══ ADMIN MANAGEMENT ══════════════════════════════════════════ -->
<div id="section-admins" class="section <?= $activeSection==='admins'?'active':'' ?>">

<div class="panel mb-3">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-plus"></i> Add Admin</div></div>
    <div style="padding:16px">
    <form method="POST" class="form-grid" style="display:grid;grid-template-columns:1fr 1fr auto;gap:10px;align-items:end">
        <input type="hidden" name="action" value="add_admin">
        <div><label class="form-label">Full Name</label><input type="text" name="fullname" class="di" required></div>
        <div><label class="form-label">Email</label><input type="email" name="email" class="di" required></div>
        <button type="submit" class="be" style="height:36px"><i class="fa-solid fa-plus"></i> Add</button>
    </form>
    <div style="font-size:10px;color:var(--muted);margin-top:8px"><i class="fa-solid fa-circle-info me-1"></i>A system-generated 6-digit password will be emailed to the admin automatically.</div>
    </div>
</div>

<div class="panel mb-3">
    <div style="padding:12px 16px;border-bottom:1px solid var(--border);display:flex;gap:10px;flex-wrap:wrap;align-items:center">
        <input type="text" id="adminSearch" class="di" style="width:220px" placeholder="Search admins…" oninput="filterAdmins()">
        <select id="adminStatusFilter" class="di" style="width:150px" onchange="filterAdmins()">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
    </div>
</div>

<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-user-shield"></i> All Admins</div></div>
    <div class="tbl-wrap">
    <table class="tbl" id="adminsTable"><thead><tr><th>Name</th><th>Email</th><th>Status</th><th>Joined</th><th></th></tr></thead><tbody>
    <?php $allAdmins->data_seek(0); if($allAdmins->num_rows===0): ?><tr><td colspan="5" style="color:var(--muted);text-align:center;padding:20px">No admins.</td></tr>
    <?php else: while($ad=$allAdmins->fetch_assoc()): $adActive = !isset($ad['is_active']) || $ad['is_active']==1; $isSelf = ((int)$ad['admin_id']===(int)$admin_id); ?>
    <tr data-name="<?= strtolower(htmlspecialchars($ad['fullname'])) ?>" data-email="<?= strtolower(htmlspecialchars($ad['email'])) ?>" data-status="<?= $adActive?'active':'inactive' ?>">
        <td><?= htmlspecialchars($ad['fullname']) ?><?php if($isSelf): ?> <span class="tag tag-blue" style="font-size:9px">You</span><?php endif; ?></td>
        <td style="font-size:11px"><?= htmlspecialchars($ad['email']) ?></td>
        <td><?= $adActive?'<span class="bkg">Active</span>':'<span class="bkd">Inactive</span>' ?></td>
        <td style="font-size:11px;color:var(--muted)"><?= !empty($ad['created_at'])?date('M d, Y',strtotime($ad['created_at'])):'—' ?></td>
        <td style="display:flex;gap:4px;flex-wrap:wrap">
            <?php if(!$isSelf): ?>
            <form method="POST" style="display:inline">
                <input type="hidden" name="action" value="toggle_admin"><input type="hidden" name="admin_id" value="<?= $ad['admin_id'] ?>">
                <button type="submit" class="bds" style="font-size:11px"><?= $adActive?'Deactivate':'Activate' ?></button>
            </form>
            <form method="POST" style="display:inline" onsubmit="return confirm('Delete this admin?')">
                <input type="hidden" name="action" value="delete_admin"><input type="hidden" name="admin_id" value="<?= $ad['admin_id'] ?>">
                <button type="submit" class="ber" style="font-size:11px">Delete</button>
            </form>
            <?php endif; ?>
        </td>
    </tr>
    <?php endwhile; endif; ?></tbody></table>
    </div><!-- /tbl-wrap -->
</div>

<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-user-shield"></i> Recent Admin Logins</div></div>
    <div class="tbl-wrap">
    <table class="tbl"><thead><tr><th>Admin</th><th>Status</th><th>Time</th></tr></thead><tbody>
    <?php $adLogins=q($conn,"SELECT alh.*,ad.fullname FROM admin_login_history alh JOIN admins ad ON ad.admin_id=alh.admin_id ORDER BY alh.login_time DESC LIMIT 10");
    if($adLogins->num_rows===0): ?><tr><td colspan="3" style="color:var(--muted);text-align:center;padding:16px">No admin logins yet.</td></tr>
    <?php else: while($adl=$adLogins->fetch_assoc()): ?>
    <tr><td style="font-size:12px"><?= htmlspecialchars($adl['fullname']) ?></td>
        <td><?= $adl['login_status']==='success'?'<span class="bkg" style="font-size:10px">✓ OK</span>':'<span class="bkr" style="font-size:10px">✗ Fail</span>' ?></td>
        <td style="font-size:10px;color:var(--muted);white-space:nowrap"><?= date('M d, g:i A',strtotime($adl['login_time'])) ?></td></tr>
    <?php endwhile; endif; ?></tbody></table>
    </div><!-- /tbl-wrap -->
</div>
</div>

<!-- ══ CHANGE PASSWORD (ADMIN) ══════════════════════════════════ -->
<div id="section-profile" class="section <?= $activeSection==='profile'?'active':'' ?>">
<div class="panel" style="max-width:480px">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-key"></i> Change Password</div></div>
    <div style="padding:20px">
    <div style="font-size:12px;color:var(--muted);margin-bottom:16px">Signed in as <strong><?= htmlspecialchars($currentAdmin['fullname'] ?? '') ?></strong> (<?= htmlspecialchars($currentAdmin['email'] ?? '') ?>)</div>
    <form method="POST">
        <input type="hidden" name="action" value="change_password">
        <div style="margin-bottom:12px">
            <label class="form-label">Current Password</label>
            <div style="position:relative">
                <input type="password" name="current_password" id="cp_current" class="di" required autocomplete="current-password" style="padding-right:34px">
                <i class="fa-solid fa-eye" onclick="togglePwdField('cp_current',this)" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);cursor:pointer;color:var(--muted);font-size:12px"></i>
            </div>
        </div>
        <div style="margin-bottom:12px">
            <label class="form-label">New Password</label>
            <div style="position:relative">
                <input type="password" name="new_password" id="cp_new" class="di" required autocomplete="new-password" minlength="8" style="padding-right:34px">
                <i class="fa-solid fa-eye" onclick="togglePwdField('cp_new',this)" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);cursor:pointer;color:var(--muted);font-size:12px"></i>
            </div>
            <div style="font-size:10px;color:var(--muted);margin-top:3px">At least 8 characters, with uppercase, lowercase, number, and special character.</div>
        </div>
        <div style="margin-bottom:16px">
            <label class="form-label">Confirm New Password</label>
            <div style="position:relative">
                <input type="password" name="confirm_password" id="cp_confirm" class="di" required autocomplete="new-password" minlength="8" style="padding-right:34px">
                <i class="fa-solid fa-eye" onclick="togglePwdField('cp_confirm',this)" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);cursor:pointer;color:var(--muted);font-size:12px"></i>
            </div>
        </div>
        <button type="submit" class="be"><i class="fa-solid fa-key"></i> Update Password</button>
    </form>
    </div>
</div>
</div>

<!-- ══ APPOINTMENTS ══════════════════════════════════════════ -->
<div id="section-appointments" class="section <?= $activeSection==='appointments'?'active':'' ?>">
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="fa-solid fa-calendar-check"></i> All Appointments</div>
        <form method="POST" onsubmit="return confirm('Recalculate every slot count from real bookings? This fixes stale/stuck counters (e.g. from deleted appointments) and removes empty entries.')">
            <input type="hidden" name="action" value="resync_schedule_slots">
            <button type="submit" class="bds" style="font-size:11px"><i class="fa-solid fa-rotate me-1"></i>Resync Slots</button>
        </form>
    </div>
    <div class="tbl-wrap">
    <table class="tbl"><thead><tr><th>Student</th><th>Instructor</th><th>Course</th><th>Start Date</th><th>Hours</th><th>Price</th><th>TDC Grade</th><th>Status</th><th>Actions</th></tr></thead><tbody>
    <?php $apts=q($conn,"SELECT a.*,s.fullname AS sname,i.fullname AS iname,ic.course_type,
        (SELECT SUM(hours_covered) FROM sessions WHERE appointment_id=a.appointment_id AND status='completed') AS hdone,
        (SELECT session_id FROM sessions WHERE appointment_id=a.appointment_id AND status='completed' ORDER BY session_id DESC LIMIT 1) AS sess_id,
        (SELECT grade FROM sessions WHERE appointment_id=a.appointment_id AND status='completed' ORDER BY session_id DESC LIMIT 1) AS sess_grade,
        (SELECT hours_covered FROM sessions WHERE appointment_id=a.appointment_id AND status='completed' ORDER BY session_id DESC LIMIT 1) AS sess_hours
        FROM appointments a JOIN students s ON s.student_id=a.student_id JOIN instructors i ON i.instructor_id=a.instructor_id JOIN instructor_courses ic ON ic.course_id=a.course_id ORDER BY a.start_date DESC, a.requested_at DESC");
    if($apts->num_rows===0): ?><tr><td colspan="9" style="color:var(--muted);text-align:center;padding:20px">No appointments.</td></tr>
    <?php else: $_ai=0; while($ap=$apts->fetch_assoc()): $_ai++; $tc=$cColors[$ap['course_type']]??'tag-gray';
        $apIsTDC=stripos($ap['course_type'],'TDC')===0;
        $apEditable=in_array($ap['status'],['completed','in_progress']) && !empty($ap['sess_id']);
    ?>
    <tr<?= $_ai>5?' class="pg-allAppts-more" style="display:none"':'' ?>>
        <td><?= htmlspecialchars($ap['sname']) ?></td>
        <td><?= htmlspecialchars($ap['iname']) ?></td>
        <td><span class="tag <?= $tc ?>"><?= htmlspecialchars($ap['course_type']) ?></span></td>
        <td><?= date('M d, Y',strtotime($ap['start_date'])) ?></td>
        <td><?= (float)($ap['hdone']??0) ?>/<?= $ap['total_hours'] ?> hrs</td>
        <td>₱<?= number_format($ap['total_price'],2) ?></td>
        <td><?php if(isset($ap['tdc_overall_grade'])&&$ap['tdc_overall_grade']): $gc=$ap['tdc_overall_grade']>=80?'var(--green)':($ap['tdc_overall_grade']>=60?'var(--amber)':'var(--red)'); ?><strong style="color:<?= $gc ?>"><?= $ap['tdc_overall_grade'] ?>/100</strong><?php else: ?>—<?php endif; ?></td>
        <td><span class="<?= $scmS[$ap['status']]??'bkd' ?>"><?= $scmL[$ap['status']]??$ap['status'] ?></span></td>
        <td style="display:flex;gap:4px;flex-wrap:wrap">
            <?php if($apEditable): ?>
            <button type="button" class="bds" style="font-size:11px" onclick='openAdminEditSession(<?= json_encode(["sess_id"=>$ap["sess_id"],"sname"=>$ap["sname"],"course"=>$ap["course_type"],"isTdc"=>$apIsTDC,"grade"=>$ap["sess_grade"],"hours"=>$ap["sess_hours"]]) ?>)'><i class="fa-solid fa-pen"></i></button>
            <?php endif; ?>
            <form method="POST" onsubmit="return confirm('Permanently delete?')" style="display:inline">
                <input type="hidden" name="action" value="delete_appointment"><input type="hidden" name="appointment_id" value="<?= $ap['appointment_id'] ?>">
                <button type="submit" class="ber" style="font-size:11px"><i class="fa-solid fa-trash"></i></button>
            </form>
        </td>
    </tr>
    <?php endwhile; if($apts->num_rows>5): ?>
    <tr id="pg-allAppts-btn"><td colspan="9" style="text-align:center;padding:10px"><button type="button" class="bds" style="font-size:11px" onclick="showMoreRows('allAppts')">Show All (<?= $apts->num_rows ?>)</button></td></tr>
    <?php endif; endif; ?></tbody></table>
    </div><!-- /tbl-wrap -->
</div>

<!-- Admin Edit Session Modal -->
<div id="adminEditSessionModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:300;align-items:center;justify-content:center;padding:16px">
<div style="background:var(--bg2);border:1px solid var(--border);border-radius:12px;width:400px;max-width:100%;padding:24px">
    <div style="font-size:15px;font-weight:600;margin-bottom:4px"><i class="fa-solid fa-pen me-2" style="color:var(--accent)"></i>Edit Session</div>
    <div id="aes_label" style="font-size:12px;color:var(--muted);margin-bottom:16px"></div>
    <form method="POST">
    <input type="hidden" name="action" value="admin_edit_session">
    <input type="hidden" name="session_id" id="aes_sess_id">
    <div id="aes_grade_wrap" style="margin-bottom:12px">
        <label class="form-label">TDC Grade (1–100)</label>
        <input type="number" name="grade" id="aes_grade" class="di" min="1" max="100">
    </div>
    <div style="margin-bottom:16px">
        <label class="form-label">Hours Covered *</label>
        <input type="number" name="hours_covered" id="aes_hours" class="di" min="0.5" max="40" step="0.5" required>
    </div>
    <div style="display:flex;gap:8px;justify-content:flex-end">
        <button type="button" class="bds" onclick="closeAdminEditSession()">Cancel</button>
        <button type="submit" class="be"><i class="fa-solid fa-save me-1"></i>Save Changes</button>
    </div>
    </form>
</div></div>
</div>

<!-- ══ CALENDAR ══════════════════════════════════════════════ -->
<div id="section-calendar" class="section <?= $activeSection==='calendar'?'active':'' ?>">
<script>window._myCalMap = <?= json_encode($calMap) ?>;</script>
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-calendar-days"></i> System-Wide Calendar</div><div style="font-size:11px;color:var(--muted)">All instructors' appointments</div></div>
    <div style="padding:16px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;max-width:340px">
            <button type="button" class="bds" onclick="mcNav(-1)" style="padding:6px 14px"><i class="fa-solid fa-chevron-left"></i></button>
            <div id="mc_month_label" style="font-weight:600;font-size:14px"></div>
            <button type="button" class="bds" onclick="mcNav(1)" style="padding:6px 14px"><i class="fa-solid fa-chevron-right"></i></button>
        </div>
        <div style="max-width:480px">
            <div id="mc_weekdays" style="display:grid;grid-template-columns:repeat(7,1fr);gap:4px;margin-bottom:4px;font-size:11px;color:var(--muted);text-align:center"></div>
            <div id="mc_grid" style="display:grid;grid-template-columns:repeat(7,1fr);gap:4px"></div>
        </div>
        <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:14px;font-size:11px;color:var(--muted)">
            <span><span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:var(--amber);margin-right:4px"></span>Active</span>
            <span><span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:var(--green);margin-right:4px"></span>Completed</span>
        </div>
        <div id="mc_details" style="margin-top:18px"></div>
    </div>
</div>
</div>

<!-- ══ RANKINGS ══════════════════════════════════════════════ -->
<div id="section-rankings" class="section <?= $activeSection==='rankings'?'active':'' ?>">
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
    <div style="font-size:13px;font-weight:600"><i class="fa-solid fa-ranking-star me-2" style="color:var(--amber)"></i>Rankings – <?= date('F Y') ?></div>
    <small style="color:var(--muted);font-size:11px"><i class="fa-solid fa-rotate me-1"></i>Student grades reset monthly</small>
</div>
<div style="font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px">Instructors – by Rating</div>
<div class="duo-grid">
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-trophy" style="color:var(--amber)"></i> Top 5 Highest Rated</div><span style="font-size:10px;color:var(--muted)">All-time</span></div>
    <div class="tbl-wrap">
    <table class="tbl"><thead><tr><th>#</th><th>Instructor</th><th>Avg Rating</th><th>Reviews</th><th>Sessions Done</th></tr></thead><tbody>
    <?php $topInstructors->data_seek(0); $rank=1; if($topInstructors->num_rows===0): ?>
    <tr><td colspan="5" style="color:var(--muted);text-align:center;padding:20px">No ratings yet.</td></tr>
    <?php else: $medals=['🥇','🥈','🥉','4️⃣','5️⃣']; while($ti=$topInstructors->fetch_assoc()): ?>
    <tr><td><span class="rank-medal"><?= $medals[$rank-1]??$rank ?></span></td>
        <td><a href="<?= $selfFile ?>?section=instructor_profile&view_instructor=<?= $ti['instructor_id'] ?>" style="color:var(--text);text-decoration:none"><?= htmlspecialchars($ti['fullname']) ?></a></td>
        <td><span style="color:var(--amber);font-weight:600">★ <?= $ti['avg_r'] ?></span><div style="height:4px;background:var(--border);border-radius:2px;margin-top:3px;width:80px"><div style="height:4px;background:var(--amber);border-radius:2px;width:<?= round($ti['avg_r']/5*100) ?>%"></div></div></td>
        <td style="color:var(--muted)"><?= $ti['cnt'] ?></td><td style="color:var(--muted)"><?= $ti['done'] ?></td></tr>
    <?php $rank++; endwhile; endif; ?></tbody></table>
    </div><!-- /tbl-wrap -->
</div>
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-arrow-trend-down" style="color:var(--red)"></i> Top 5 Lowest Rated</div><span style="font-size:10px;color:var(--muted)">All-time · min 1 review</span></div>
    <div class="tbl-wrap">
    <table class="tbl"><thead><tr><th>#</th><th>Instructor</th><th>Avg Rating</th><th>Reviews</th></tr></thead><tbody>
    <?php $lowInstructors->data_seek(0); $rank=1; if($lowInstructors->num_rows===0): ?>
    <tr><td colspan="4" style="color:var(--muted);text-align:center;padding:20px">No ratings yet.</td></tr>
    <?php else: while($li=$lowInstructors->fetch_assoc()): ?>
    <tr><td><span style="color:var(--muted);font-size:12px"><?= $rank ?></span></td>
        <td><a href="<?= $selfFile ?>?section=instructor_profile&view_instructor=<?= $li['instructor_id'] ?>" style="color:var(--text);text-decoration:none"><?= htmlspecialchars($li['fullname']) ?></a></td>
        <td><span style="color:var(--red);font-weight:600">★ <?= $li['avg_r'] ?></span><div style="height:4px;background:var(--border);border-radius:2px;margin-top:3px;width:80px"><div style="height:4px;background:var(--red);border-radius:2px;width:<?= round($li['avg_r']/5*100) ?>%"></div></div></td>
        <td style="color:var(--muted)"><?= $li['cnt'] ?></td></tr>
    <?php $rank++; endwhile; endif; ?></tbody></table>
    </div><!-- /tbl-wrap -->
</div>
</div>
<div style="font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin:4px 0 8px">Students – by Session Grade (<?= date('F Y') ?>)</div>
<div class="duo-grid">
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-star" style="color:var(--amber)"></i> Top 5 Highest Grades</div><small style="color:var(--muted);font-size:10px"><i class="fa-solid fa-rotate me-1"></i>Resets monthly</small></div>
    <div class="tbl-wrap">
    <table class="tbl"><thead><tr><th>#</th><th>Student</th><th>Avg Grade</th><th>Sessions</th></tr></thead><tbody>
    <?php $topStudents->data_seek(0); $rank=1; if($topStudents->num_rows===0): ?>
    <tr><td colspan="4" style="color:var(--muted);text-align:center;padding:20px">No grades recorded this month.</td></tr>
    <?php else: $medals=['🥇','🥈','🥉','4️⃣','5️⃣']; while($ts=$topStudents->fetch_assoc()): $gc=$ts['avg_grade']>=80?'var(--green)':($ts['avg_grade']>=60?'var(--amber)':'var(--red)'); ?>
    <tr><td><span class="rank-medal"><?= $medals[$rank-1]??$rank ?></span></td>
        <td><a href="<?= $selfFile ?>?section=student_profile&view_student=<?= $ts['student_id'] ?>" style="color:var(--text);text-decoration:none"><?= htmlspecialchars($ts['fullname']) ?></a></td>
        <td><span style="color:<?= $gc ?>;font-weight:600"><?= $ts['avg_grade'] ?>/100</span><div class="grade-bar" style="width:90px;margin-top:3px"><div class="grade-fill" style="width:<?= $ts['avg_grade'] ?>%;background:<?= $gc ?>"></div></div></td>
        <td style="color:var(--muted)"><?= $ts['sess_cnt'] ?></td></tr>
    <?php $rank++; endwhile; endif; ?></tbody></table>
    </div><!-- /tbl-wrap -->
</div>
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-arrow-trend-down" style="color:var(--red)"></i> Top 5 Lowest Grades</div><small style="color:var(--muted);font-size:10px"><i class="fa-solid fa-rotate me-1"></i>Resets monthly</small></div>
    <div class="tbl-wrap">
    <table class="tbl"><thead><tr><th>#</th><th>Student</th><th>Avg Grade</th><th>Sessions</th></tr></thead><tbody>
    <?php $lowStudents->data_seek(0); $rank=1; if($lowStudents->num_rows===0): ?>
    <tr><td colspan="4" style="color:var(--muted);text-align:center;padding:20px">No grades recorded this month.</td></tr>
    <?php else: while($ls=$lowStudents->fetch_assoc()): ?>
    <tr><td><span style="color:var(--muted);font-size:12px"><?= $rank ?></span></td>
        <td><a href="<?= $selfFile ?>?section=student_profile&view_student=<?= $ls['student_id'] ?>" style="color:var(--text);text-decoration:none"><?= htmlspecialchars($ls['fullname']) ?></a></td>
        <td><span style="color:var(--red);font-weight:600"><?= $ls['avg_grade'] ?>/100</span><div class="grade-bar" style="width:90px;margin-top:3px"><div class="grade-fill" style="width:<?= $ls['avg_grade'] ?>%;background:var(--red)"></div></div></td>
        <td style="color:var(--muted)"><?= $ls['sess_cnt'] ?></td></tr>
    <?php $rank++; endwhile; endif; ?></tbody></table>
    </div><!-- /tbl-wrap -->
</div>
</div>
</div>

<!-- ══ REPORTS ════════════════════════════════════════════════ -->
<div id="section-reports" class="section <?= $activeSection==='reports'?'active':'' ?>">
<div class="panel mb-3">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-chart-pie"></i> Course Breakdown</div></div>
    <div class="tbl-wrap">
    <table class="tbl"><thead><tr><th>Course Type</th><th>Total</th><th>Completed</th><th>In Progress</th><th>Pending</th></tr></thead><tbody>
    <?php $courseReport=q($conn,"SELECT ic.course_type,COUNT(*) AS total,SUM(a.status='completed') AS done,SUM(a.status IN('approved','in_progress')) AS active,SUM(a.status='pending') AS pending FROM appointments a JOIN instructor_courses ic ON ic.course_id=a.course_id GROUP BY ic.course_type ORDER BY ic.course_type");
    while($cr=$courseReport->fetch_assoc()): $tc=$cColors[$cr['course_type']]??'tag-gray'; ?>
    <tr><td><span class="tag <?= $tc ?>"><?= htmlspecialchars($cr['course_type']) ?></span></td>
        <td><?= $cr['total'] ?></td><td><?= $cr['done'] ?></td><td><?= $cr['active'] ?></td><td><?= $cr['pending'] ?></td></tr>
    <?php endwhile; ?></tbody></table>
    </div><!-- /tbl-wrap -->
</div>
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-users"></i> Student Progress Summary</div></div>
    <div class="tbl-wrap">
    <table class="tbl"><thead><tr><th>Student</th><th>TDC Done</th><th>PDC Done</th><th>Avg Grade (TDC)</th><th>Avg Perf</th></tr></thead><tbody>
    <?php $sProgress=q($conn,"SELECT s.student_id,s.fullname,(SELECT CASE WHEN COALESCE(SUM(sess.hours_covered),0)>=15 THEN 1 ELSE 0 END FROM sessions sess JOIN appointments a ON a.appointment_id=sess.appointment_id JOIN instructor_courses ic ON ic.course_id=a.course_id WHERE sess.student_id=s.student_id AND ic.course_type LIKE 'TDC%' AND sess.status='completed') AS tdc_done,(SELECT COUNT(*) FROM appointments a JOIN instructor_courses ic ON ic.course_id=a.course_id WHERE a.student_id=s.student_id AND ic.course_type LIKE 'PDC%' AND a.status='completed') AS pdc_done,(SELECT ROUND(AVG(grade),1) FROM sessions WHERE student_id=s.student_id AND grade IS NOT NULL) AS avg_grade,(SELECT ROUND(AVG(performance_rating),1) FROM sessions WHERE student_id=s.student_id AND performance_rating IS NOT NULL) AS avg_perf FROM students s ORDER BY s.fullname ASC");
    while($sp=$sProgress->fetch_assoc()): $gc=$sp['avg_grade']?($sp['avg_grade']>=80?'var(--green)':($sp['avg_grade']>=60?'var(--amber)':'var(--red)')):''; ?>
    <tr><td><?= htmlspecialchars($sp['fullname']) ?></td>
        <td><?= $sp['tdc_done']?'<span class="bkg">✓</span>':'—' ?></td>
        <td><?= $sp['pdc_done']?'<span class="bkg">✓</span>':'—' ?></td>
        <td><?= $sp['avg_grade']?'<strong style="color:'.$gc.'">'.$sp['avg_grade'].'/100</strong>':'—' ?></td>
        <td><?= $sp['avg_perf']?'★ '.$sp['avg_perf']:'—' ?></td></tr>
    <?php endwhile; ?></tbody></table>
    </div><!-- /tbl-wrap -->
</div>
</div>

<!-- ══ ANNOUNCEMENTS ════════════════════════════════════════ -->
<div id="section-communication" class="section <?= $activeSection==='communication'?'active':'' ?>">
<div class="panel" style="margin-bottom:16px">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-bullhorn"></i> Send Announcement</div></div>
    <div style="padding:16px">
    <form method="POST" id="ann-form">
        <input type="hidden" name="action" value="announce">
        <div style="margin-bottom:14px">
            <label class="form-label">Send To</label>
            <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:10px">
            <?php $stoOptions=['all_students'=>['fa-user-graduate','All Students','var(--accent)',$stuRecipCount],'all_instructors'=>['fa-chalkboard-user','All Instructors','var(--green)',$insRecipCount],'everyone'=>['fa-users','Everyone','var(--amber)',$stuRecipCount+$insRecipCount],'specific_student'=>['fa-user','One Student','var(--accent)',null],'specific_instructor'=>['fa-id-badge','One Instructor','var(--green)',null]];
            foreach($stoOptions as $val=>[$ico,$lbl,$clr,$cnt2]): ?>
            <label style="flex:1;min-width:130px;cursor:pointer">
                <input type="radio" name="send_to" value="<?= $val ?>" <?= $val==='all_students'?'checked':'' ?> class="sto-radio" style="display:none">
                <div class="sto-btn" data-val="<?= $val ?>" data-color="<?= $clr ?>" style="border:2px solid var(--border);border-radius:8px;padding:10px 14px;display:flex;align-items:center;gap:8px;transition:.15s;user-select:none">
                    <i class="fa-solid <?= $ico ?>" style="color:<?= $clr ?>"></i>
                    <span style="font-size:12px;font-weight:500"><?= $lbl ?></span>
                    <?php if($cnt2!==null): ?><span style="margin-left:auto;background:rgba(255,255,255,.06);border-radius:10px;padding:1px 8px;font-size:11px;color:var(--muted)"><?= $cnt2 ?></span><?php endif; ?>
                </div>
            </label>
            <?php endforeach; ?>
            </div>
            <div id="ann-specific-student" style="display:none;margin-top:8px">
                <label class="form-label">Select Student</label>
                <select name="specific_student_id" id="ann-sid" class="di"><option value="">— choose student —</option>
                <?php $allStudentsList->data_seek(0); while($sr=$allStudentsList->fetch_assoc()): ?><option value="<?= $sr['student_id'] ?>"><?= htmlspecialchars($sr['fullname']) ?></option><?php endwhile; ?></select>
            </div>
            <div id="ann-specific-instructor" style="display:none;margin-top:8px">
                <label class="form-label">Select Instructor</label>
                <select name="specific_instructor_id" id="ann-iid" class="di"><option value="">— choose instructor —</option>
                <?php $allInstructorsList->data_seek(0); while($ir=$allInstructorsList->fetch_assoc()): ?><option value="<?= $ir['instructor_id'] ?>"><?= htmlspecialchars($ir['fullname']) ?></option><?php endwhile; ?></select>
            </div>
        </div>
        <div style="margin-bottom:14px"><label class="form-label">Message</label>
            <textarea name="message" class="di" rows="4" required placeholder="Type your announcement…"></textarea></div>
        <button type="submit" class="be"><i class="fa-solid fa-paper-plane me-1"></i> Send Announcement</button>
    </form>
    </div>
</div>
<?php $recentAnn=q($conn,"SELECT n.*,CASE WHEN n.user_type='student' THEN (SELECT fullname FROM students WHERE student_id=n.user_id) WHEN n.user_type='instructor' THEN (SELECT fullname FROM instructors WHERE instructor_id=n.user_id) END AS recip FROM notifications n WHERE n.type='announcement' ORDER BY n.created_at DESC LIMIT 50");
if($recentAnn->num_rows>0): ?>
<div class="panel" style="margin-bottom:16px">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-clock-rotate-left me-1"></i> Recent Announcements</div></div>
    <div class="tbl-wrap">
    <table class="tbl"><thead><tr><th>Message</th><th>Recipient</th><th>Audience</th><th>Read</th><th>Sent</th></tr></thead><tbody>
    <?php $targetMap=['all_students'=>'All Students','all_instructors'=>'All Instructors','everyone'=>'Everyone','specific_student'=>'Single Student','specific_instructor'=>'Single Instructor',''=>'—',null=>'—'];
    while($ra=$recentAnn->fetch_assoc()): $isInst=$ra['user_type']==='instructor'; $targetLbl=$targetMap[$ra['recipient_target']]??'—'; ?>
    <tr>
        <td style="max-width:260px;font-size:12px"><?= htmlspecialchars(substr(str_replace('📢 Admin: ','',$ra['message']),0,80)) ?><?= strlen($ra['message'])>88?'…':'' ?></td>
        <td style="font-size:12px"><?= htmlspecialchars($ra['recip']??'—') ?></td>
        <td><span class="tag <?= $isInst?'tag-green':'tag-blue' ?>" style="font-size:10px"><?= $targetLbl ?></span></td>
        <td><?= $ra['is_read']?'<span class="bkg" style="font-size:10px">✓</span>':'<span style="font-size:10px;color:var(--muted)">—</span>' ?></td>
        <td style="font-size:10px;color:var(--muted);white-space:nowrap"><?= date('M d, g:i A',strtotime($ra['created_at'])) ?></td>
    </tr>
    <?php endwhile; ?></tbody></table>
    </div><!-- /tbl-wrap -->
</div>
<?php endif; ?>
</div>

<!-- ══ WORKLOAD DISTRIBUTION ══════════════════════════════════ -->
<div id="section-workload" class="section <?= $activeSection==='workload'?'active':'' ?>">
<?php
$wlInstrs=q($conn,"SELECT i.instructor_id,i.fullname,
    (SELECT COUNT(*) FROM appointments WHERE instructor_id=i.instructor_id AND status IN('pending','approved','in_progress')) AS active_appts,
    (SELECT COUNT(*) FROM appointments WHERE instructor_id=i.instructor_id AND status='completed') AS done_appts,
    (SELECT COUNT(DISTINCT student_id) FROM appointments WHERE instructor_id=i.instructor_id AND status IN('approved','in_progress')) AS current_students,
    (SELECT COALESCE(SUM(hours_covered),0) FROM sessions WHERE instructor_id=i.instructor_id AND status='completed' AND DATE_FORMAT(session_date,'%Y-%m')=DATE_FORMAT(NOW(),'%Y-%m')) AS month_hrs,
    (SELECT COALESCE(SUM(hours_covered),0) FROM sessions WHERE instructor_id=i.instructor_id AND status='completed') AS total_hrs,
    (SELECT ROUND(AVG(rating),1) FROM instructor_ratings WHERE instructor_id=i.instructor_id) AS avg_rating,
    (SELECT COUNT(*) FROM instructor_courses WHERE instructor_id=i.instructor_id AND is_active=1) AS active_courses
    FROM instructors i ORDER BY active_appts ASC, month_hrs ASC");
$totalActiveAppts=(int)q($conn,"SELECT COUNT(*) AS c FROM appointments WHERE status IN('pending','approved','in_progress')")->fetch_assoc()['c'];
// Build instructor course map for assign modal
$instrCourseMap=[];
$icRes=q($conn,"SELECT ic.*,i.fullname AS iname FROM instructor_courses ic JOIN instructors i ON i.instructor_id=ic.instructor_id WHERE ic.is_active=1 ORDER BY i.fullname,ic.course_type");
while($ic=$icRes->fetch_assoc()) $instrCourseMap[$ic['instructor_id']][]=$ic;
$unassignedStudents=q($conn,"SELECT s.student_id,s.fullname FROM students s WHERE s.is_verified=1 AND NOT EXISTS(SELECT 1 FROM appointments a WHERE a.student_id=s.student_id AND a.status IN('pending','approved','in_progress')) ORDER BY s.fullname");
?>

<!-- ── Assign Student Panel ─────────────────────────────────── -->
<div class="panel" style="margin-bottom:16px">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-user-plus" style="color:var(--green)"></i> Assign Student to Instructor</div>
        <span style="font-size:11px;color:var(--muted)">Pre-approved — bypasses student booking flow</span></div>
    <div style="padding:16px">
    <?php if($flash): ?><div class="flash flash-ok" style="margin-bottom:12px"><i class="fa-solid fa-circle-check me-2"></i><?= htmlspecialchars($flash) ?></div><?php endif; ?>
    <?php if($flashErr): ?><div style="padding:10px 16px;border-radius:8px;margin-bottom:12px;font-size:12px;background:rgba(229,85,85,.15);border:1px solid rgba(229,85,85,.3);color:var(--red)"><i class="fa-solid fa-triangle-exclamation me-2"></i><?= htmlspecialchars($flashErr) ?></div><?php endif; ?>
    <form method="POST" id="assign_main_form" class="form-grid" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr auto;gap:12px;align-items:end">
        <input type="hidden" name="action" value="assign_student">
        <input type="hidden" name="total_hours" id="assign_hrs_hidden" value="8">
        <input type="hidden" name="admin_note" id="assign_note_hidden" value="">
        <div>
            <label class="form-label">Student *</label>
            <select name="student_id" class="di" required id="assign_student_sel">
                <option value="">— Select Student —</option>
                <?php
                // Only verified students with NO ongoing appointment (approved/in_progress).
                // Students already in an active course must use Reassign instead.
                $allStuRes=q($conn,"SELECT student_id,fullname FROM students WHERE is_verified=1 AND NOT EXISTS(SELECT 1 FROM appointments a WHERE a.student_id=students.student_id AND a.status IN('approved','in_progress')) ORDER BY fullname");
                while($sv=$allStuRes->fetch_assoc()):
                ?>
                <option value="<?= $sv['student_id'] ?>"><?= htmlspecialchars($sv['fullname']) ?></option>
                <?php endwhile; ?>
            </select>
            <div style="font-size:10px;color:var(--muted);margin-top:3px">Students with an ongoing appointment aren't listed — use <strong>Reassign</strong> for them instead.</div>
        </div>
        <div>
            <label class="form-label">Instructor *</label>
            <select name="instructor_id" class="di" required id="assign_instr_sel" onchange="loadAssignCourses(this.value)">
                <option value="">— Select Instructor —</option>
                <?php $wlInstrs->data_seek(0); while($wli=$wlInstrs->fetch_assoc()): $loadPct2=0; ?>
                <option value="<?= $wli['instructor_id'] ?>" data-load="<?= $wli['active_appts'] ?>">
                    <?= htmlspecialchars($wli['fullname']) ?> (<?= $wli['active_appts'] ?> active)
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div>
            <label class="form-label">Course *</label>
            <select name="course_id" class="di" required id="assign_course_sel">
                <option value="">— Select Instructor First —</option>
            </select>
        </div>
        <div>
            <label class="form-label">Start Date * <span id="assign_day_hint" style="color:var(--amber);font-size:10px"></span></label>
            <input type="hidden" name="start_date" id="assign_date">
            <button type="button" class="di" style="text-align:left;cursor:pointer" onclick="toggleAssignCalendar()"><i class="fa-solid fa-calendar-days me-1"></i><span id="assign_date_label">Pick a date</span></button>
        </div>
        <div>
            <button type="submit" class="be" style="height:36px" onclick="return syncAndConfirmAssign()"><i class="fa-solid fa-user-plus"></i> Assign</button>
        </div>
    </form>
    <!-- Visual calendar picker (Assign) -->
    <div id="assign_calendar" style="display:none;background:var(--bg3);border:1px solid var(--border);border-radius:10px;padding:12px;margin-top:10px;max-width:360px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
            <button type="button" class="bds" onclick="assignCalNav(-1)" style="padding:4px 10px;font-size:13px"><i class="fa-solid fa-chevron-left"></i></button>
            <div id="assign_cal_month_label" style="font-weight:600;font-size:13px"></div>
            <button type="button" class="bds" onclick="assignCalNav(1)" style="padding:4px 10px;font-size:13px"><i class="fa-solid fa-chevron-right"></i></button>
        </div>
        <div id="assign_cal_weekdays" style="display:grid;grid-template-columns:repeat(7,1fr);gap:3px;margin-bottom:4px;font-size:10px;color:var(--muted);text-align:center"></div>
        <div id="assign_cal_grid" style="display:grid;grid-template-columns:repeat(7,1fr);gap:3px"></div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:10px;font-size:10px;color:var(--muted)">
            <span><span style="display:inline-block;width:9px;height:9px;border-radius:2px;background:var(--green);margin-right:3px"></span>Available</span>
            <span><span style="display:inline-block;width:9px;height:9px;border-radius:2px;background:var(--amber);margin-right:3px"></span>Some slots taken</span>
            <span><span style="display:inline-block;width:9px;height:9px;border-radius:2px;background:var(--red);margin-right:3px"></span>Booked / blocked</span>
        </div>
    </div>
    <div style="margin-top:10px;display:flex;gap:10px;align-items:center;flex-wrap:wrap">
        <div style="flex:1">
            <label class="form-label">Hours (PDC only)</label>
            <input type="number" id="assign_hrs" class="di" value="8" min="1" max="40" style="width:80px" oninput="document.getElementById('assign_hrs_hidden').value=this.value">
        </div>
        <div style="flex:2">
            <label class="form-label">Admin Note (optional)</label>
            <input type="text" id="assign_note" class="di" placeholder="e.g. Reassigned from unavailable instructor" oninput="document.getElementById('assign_note_hidden').value=this.value">
        </div>
    </div>
    <!-- Slot check display -->
    <div id="assign_slot_info" style="display:none;margin-top:10px;padding:8px 12px;border-radius:6px;font-size:12px"></div>
    <!-- Light-load instructors hint -->
    <div style="margin-top:10px;padding:10px 12px;background:var(--bg3);border-radius:8px;font-size:11px;color:var(--muted)">
        <i class="fa-solid fa-lightbulb me-1" style="color:var(--amber)"></i>
        <strong>Light load instructors</strong> (recommended for assignment):
        <?php
        $wlInstrs->data_seek(0); $lightOnes=[]; $maxA2=1;
        while($wli2=$wlInstrs->fetch_assoc()){if((int)$wli2['active_appts']>$maxA2)$maxA2=(int)$wli2['active_appts'];}
        $wlInstrs->data_seek(0);
        while($wli2=$wlInstrs->fetch_assoc()){
            $p2=$maxA2>0?round($wli2['active_appts']/$maxA2*100):0;
            if($p2<50) $lightOnes[]=htmlspecialchars($wli2['fullname']).' ('.$wli2['active_appts'].' active)';
        }
        echo $lightOnes?implode(', ',$lightOnes):'All instructors are moderately or heavily loaded.';
        ?>
    </div>
    </div>
</div>

<div style="display:flex;gap:12px;margin-bottom:16px;flex-wrap:wrap">
    <div style="background:var(--bg2);border:1px solid var(--border);border-radius:10px;padding:14px 20px;flex:1;min-width:140px">
        <div style="font-size:22px;font-weight:700;color:var(--amber)"><?= $totalActiveAppts ?></div>
        <div style="font-size:11px;color:var(--muted)">Total Active Appointments</div>
    </div>
    <div style="background:var(--bg2);border:1px solid var(--border);border-radius:10px;padding:14px 20px;flex:1;min-width:140px">
        <div style="font-size:22px;font-weight:700;color:var(--accent)"><?= $wlInstrs->num_rows ?></div>
        <div style="font-size:11px;color:var(--muted)">Total Instructors</div>
    </div>
</div>

<div class="panel" style="margin-bottom:16px">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-chart-gantt" style="color:var(--accent)"></i> Instructor Load Overview</div>
        <small style="font-size:10px;color:var(--muted)">↑ Light load → Heavy load</small></div>
    <div class="tbl-wrap">
    <table class="tbl"><thead><tr><th>Instructor</th><th>Active Appts</th><th>Current Students</th><th>Month Hours</th><th>Total Hours</th><th>Completed</th><th>Avg Rating</th><th>Courses</th><th>Load</th><th>Students</th></tr></thead><tbody>
    <?php $wlInstrs->data_seek(0); $maxActive=1; $rows=[];
    while($r=$wlInstrs->fetch_assoc()){$rows[]=$r;if((int)$r['active_appts']>$maxActive)$maxActive=(int)$r['active_appts'];}
    if(empty($rows)): ?><tr><td colspan="10" style="text-align:center;color:var(--muted);padding:20px">No instructors yet.</td></tr>
    <?php else: foreach($rows as $r):
        $pct=$maxActive>0?round($r['active_appts']/$maxActive*100):0;
        $barColor=$pct>=80?'var(--red)':($pct>=50?'var(--amber)':'var(--green)');
        $loadLabel=$pct>=80?'Heavy':($pct>=50?'Moderate':'Light');
        $loadClass=$pct>=80?'bkr':($pct>=50?'bkp':'bkg');
        $iid=$r['instructor_id'];
    ?>
    <tr>
        <td><a href="<?= $selfFile ?>?section=instructor_profile&view_instructor=<?= $iid ?>" style="color:var(--text);text-decoration:none;font-weight:500"><?= htmlspecialchars($r['fullname']) ?></a></td>
        <td><strong style="color:<?= $r['active_appts']>0?'var(--amber)':'var(--muted)' ?>"><?= $r['active_appts'] ?></strong></td>
        <td><?= $r['current_students'] ?></td>
        <td><?= number_format($r['month_hrs'],1) ?>h</td>
        <td><?= number_format($r['total_hrs'],1) ?>h</td>
        <td style="color:var(--green)"><?= $r['done_appts'] ?></td>
        <td><?= $r['avg_rating']?'<span style="color:var(--amber)">★ '.$r['avg_rating'].'</span>':'<span style="color:var(--muted)">—</span>' ?></td>
        <td><?= $r['active_courses'] ?></td>
        <td style="min-width:120px">
            <div style="display:flex;align-items:center;gap:8px">
                <div style="flex:1;height:8px;background:var(--border);border-radius:4px;overflow:hidden"><div style="height:100%;width:<?= $pct ?>%;background:<?= $barColor ?>;border-radius:4px;transition:width .3s"></div></div>
                <span class="<?= $loadClass ?>" style="font-size:10px;white-space:nowrap"><?= $loadLabel ?></span>
            </div>
        </td>
        <td>
            <?php if($r['active_appts']>0): ?>
            <button class="bds" style="font-size:11px" onclick="toggleWlDetail(<?= $iid ?>)"><i class="fa-solid fa-eye me-1"></i>View (<?= $r['active_appts'] ?>)</button>
            <?php else: ?><span style="color:var(--muted);font-size:11px">—</span><?php endif; ?>
        </td>
    </tr>
    <?php if($r['active_appts']>0): ?>
    <tr id="wl-detail-<?= $iid ?>" style="display:none">
        <td colspan="10" style="padding:0;background:var(--bg3)">
            <div style="padding:12px 16px">
                <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px"><i class="fa-solid fa-users me-1"></i>Students currently with <?= htmlspecialchars($r['fullname']) ?></div>
                <div class="tbl-wrap"><table class="tbl" style="background:var(--bg2);border-radius:8px;overflow:hidden">
                    <thead><tr><th>Student</th><th>Course</th><th>Start Date</th><th>Hours</th><th>Status</th><th></th></tr></thead><tbody>
                    <?php
                    $wlStudents=q($conn,"SELECT a.appointment_id,a.student_id,a.course_id,a.start_date,a.total_hours,a.status,ic.course_type,s.fullname AS sname,
                        DATE_ADD(a.start_date, INTERVAL (CASE WHEN ic.course_type LIKE 'TDC%' OR ic.course_type LIKE '%Both%' THEN 2 ELSE 1 END) DAY) AS end_date,
                        EXISTS(SELECT 1 FROM sessions sess WHERE sess.appointment_id=a.appointment_id AND sess.status='completed') AS has_session
                        FROM appointments a
                        JOIN students s ON s.student_id=a.student_id
                        JOIN instructor_courses ic ON ic.course_id=a.course_id
                        WHERE a.instructor_id=$iid AND a.status IN('pending','approved','in_progress')
                        ORDER BY a.start_date ASC");
                    while($ws=$wlStudents->fetch_assoc()):
                        $tcw=$cColors[$ws['course_type']]??'tag-gray';
                        $isTDCw=stripos($ws['course_type'],'TDC')===0?1:0;
                        $isOverdue=($ws['end_date']<$today && !$ws['has_session']);
                    ?>
                    <tr<?= $isOverdue?' style="background:rgba(229,85,85,.06)"':'' ?>>
                        <td><?= htmlspecialchars($ws['sname']) ?></td>
                        <td><span class="tag <?= $tcw ?>"><?= htmlspecialchars($ws['course_type']) ?></span></td>
                        <td><?= date('M d, Y',strtotime($ws['start_date'])) ?><?php if($isOverdue): ?> <span class="bkr" style="font-size:9px"><i class="fa-solid fa-triangle-exclamation me-1"></i>Overdue</span><?php endif; ?></td>
                        <td><?= $ws['total_hours'] ?> hrs</td>
                        <td><span class="<?= $scmS[$ws['status']]??'bkd' ?>"><?= $scmL[$ws['status']]??$ws['status'] ?></span></td>
                        <td>
                            <button class="be" style="font-size:11px"
                                onclick="openReassignModal(<?= $ws['appointment_id'] ?>,<?= $iid ?>,'<?= addslashes(htmlspecialchars($ws['sname'])) ?>','<?= addslashes(htmlspecialchars($ws['course_type'])) ?>','<?= $ws['start_date'] ?>',<?= $ws['total_hours'] ?>,<?= $isTDCw ?>)">
                                <i class="fa-solid fa-right-left"></i> Reassign
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody></table></div>
            </div>
        </td>
    </tr>
    <?php endif; ?>
    <?php endforeach; endif; ?></tbody></table>
    </div><!-- /tbl-wrap -->
</div>

<!-- Upcoming schedule conflicts / full dates -->
<?php $fullSlots=q($conn,"SELECT cs.*,ic.course_type,i.fullname AS iname FROM course_schedules cs JOIN instructor_courses ic ON ic.course_id=cs.course_id JOIN instructors i ON i.instructor_id=cs.instructor_id WHERE cs.student_limit IS NOT NULL AND cs.student_limit>0 AND cs.booked_count>=cs.student_limit AND cs.schedule_date>=CURDATE() ORDER BY cs.schedule_date ASC LIMIT 20");
if($fullSlots->num_rows>0): ?>
<div class="panel" style="margin-bottom:16px">
    <div class="panel-head"><div class="panel-title" style="color:var(--red)"><i class="fa-solid fa-ban"></i> Fully Booked Upcoming Dates</div></div>
    <div class="tbl-wrap">
    <table class="tbl"><thead><tr><th>Date</th><th>Instructor</th><th>Course</th><th>Booked/Limit</th></tr></thead><tbody>
    <?php while($fs=$fullSlots->fetch_assoc()): $tc=$cColors[$fs['course_type']]??'tag-gray'; ?>
    <tr>
        <td><strong><?= date('M d, Y (D)',strtotime($fs['schedule_date'])) ?></strong></td>
        <td><?= htmlspecialchars($fs['iname']) ?></td>
        <td><span class="tag <?= $tc ?>"><?= htmlspecialchars($fs['course_type']) ?></span></td>
        <td><span class="bkr"><?= $fs['booked_count'] ?>/<?= $fs['student_limit'] ?> FULL</span></td>
    </tr>
    <?php endwhile; ?></tbody></table>
    </div><!-- /tbl-wrap -->
</div>
<?php endif; ?>

<!-- Per-instructor course schedules -->
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="fa-solid fa-calendar-days"></i> Upcoming Course Schedules (with Limits)</div>
        <form method="POST" onsubmit="return confirm('Recalculate every slot count from real bookings? This fixes stale/stuck counters (e.g. from deleted appointments) and removes empty entries.')">
            <input type="hidden" name="action" value="resync_schedule_slots">
            <button type="submit" class="bds" style="font-size:11px"><i class="fa-solid fa-rotate me-1"></i>Resync Slots</button>
        </form>
    </div>
    <?php $schedAll=q($conn,"SELECT cs.*,ic.course_type,i.fullname AS iname FROM course_schedules cs JOIN instructor_courses ic ON ic.course_id=cs.course_id JOIN instructors i ON i.instructor_id=cs.instructor_id WHERE cs.schedule_date>=CURDATE() AND cs.student_limit IS NOT NULL AND cs.booked_count>0 ORDER BY cs.schedule_date ASC, i.fullname ASC LIMIT 40");
    if($schedAll->num_rows===0): ?><div style="padding:16px;color:var(--muted);text-align:center;font-size:12px">No scheduled dates with limits yet. Instructors must set a student limit on their courses.</div>
    <?php else: ?>
    <div class="tbl-wrap">
    <table class="tbl"><thead><tr><th>Date</th><th>Instructor</th><th>Course</th><th>Booked</th><th>Limit</th><th>Slots Left</th></tr></thead><tbody>
    <?php while($sc=$schedAll->fetch_assoc()):
        $left=max(0,$sc['student_limit']-$sc['booked_count']);
        $pct2=round($sc['booked_count']/$sc['student_limit']*100);
        $barC=$pct2>=100?'var(--red)':($pct2>=75?'var(--amber)':'var(--green)');
        $tc=$cColors[$sc['course_type']]??'tag-gray';
    ?>
    <tr>
        <td><?= date('M d, Y (D)',strtotime($sc['schedule_date'])) ?></td>
        <td><?= htmlspecialchars($sc['iname']) ?></td>
        <td><span class="tag <?= $tc ?>"><?= htmlspecialchars($sc['course_type']) ?></span></td>
        <td>
            <div style="display:flex;align-items:center;gap:6px">
                <strong><?= $sc['booked_count'] ?></strong>
                <div style="width:50px;height:6px;background:var(--border);border-radius:3px;overflow:hidden"><div style="height:100%;width:<?= $pct2 ?>%;background:<?= $barC ?>;border-radius:3px"></div></div>
            </div>
        </td>
        <td><?= $sc['student_limit'] ?></td>
        <td><?= $left===0?'<span class="bkr">FULL</span>':'<span style="color:var(--green);font-weight:600">'.$left.' open</span>' ?></td>
    </tr>
    <?php endwhile; ?></tbody></table>
    </div><!-- /tbl-wrap -->
    <?php endif; ?>
</div>
</div>

</div><!-- /main -->

<!-- ══ REASSIGN MODAL ════════════════════════════════════════ -->
<div id="reassignModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:300;align-items:center;justify-content:center;padding:16px">
<div style="background:var(--bg2);border:1px solid var(--border);border-radius:12px;width:440px;max-width:100%;padding:24px">
    <div style="font-size:15px;font-weight:600;margin-bottom:4px"><i class="fa-solid fa-right-left me-2" style="color:var(--accent)"></i>Reassign Appointment</div>
    <div id="ra_summary" style="font-size:12px;color:var(--muted);margin-bottom:16px"></div>
    <form method="POST">
    <input type="hidden" name="action" value="reassign_appointment">
    <input type="hidden" name="appointment_id" id="ra_appt_id">
    <input type="hidden" name="old_instructor_id" id="ra_old_instr">
    <div style="margin-bottom:12px">
        <label class="form-label">Course Type (fixed)</label>
        <div id="ra_course_type_display" style="font-size:13px;font-weight:600"></div>
    </div>
    <div style="margin-bottom:12px">
        <label class="form-label">New Instructor *</label>
        <select id="ra_instr_sel" class="di" required onchange="loadReassignCourses()">
            <option value="">— Select Instructor —</option>
        </select>
    </div>
    <div style="margin-bottom:12px">
        <label class="form-label">Matching Course *</label>
        <select name="course_id" id="ra_course_sel" class="di" required onchange="checkReassignSlot()">
            <option value="">— Select Instructor First —</option>
        </select>
        <div style="font-size:10px;color:var(--muted);margin-top:3px">Only courses of the same type (<span id="ra_course_type_hint"></span>) offered by the new instructor are shown.</div>
    </div>
    <div style="margin-bottom:6px">
        <label class="form-label">Start Date * <span id="ra_day_hint" style="color:var(--amber);font-size:10px"></span></label>
        <input type="hidden" name="start_date" id="ra_date">
        <button type="button" class="di" style="text-align:left;cursor:pointer" onclick="toggleReassignCalendar()"><i class="fa-solid fa-calendar-days me-1"></i><span id="ra_date_label">Select instructor & course first</span></button>
    </div>
    <div id="ra_calendar" style="display:none;background:var(--bg3);border:1px solid var(--border);border-radius:10px;padding:12px;margin-bottom:12px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
            <button type="button" class="bds" onclick="raCalNav(-1)" style="padding:4px 10px;font-size:13px"><i class="fa-solid fa-chevron-left"></i></button>
            <div id="ra_cal_month_label" style="font-weight:600;font-size:13px"></div>
            <button type="button" class="bds" onclick="raCalNav(1)" style="padding:4px 10px;font-size:13px"><i class="fa-solid fa-chevron-right"></i></button>
        </div>
        <div id="ra_cal_weekdays" style="display:grid;grid-template-columns:repeat(7,1fr);gap:3px;margin-bottom:4px;font-size:10px;color:var(--muted);text-align:center"></div>
        <div id="ra_cal_grid" style="display:grid;grid-template-columns:repeat(7,1fr);gap:3px"></div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:10px;font-size:10px;color:var(--muted)">
            <span><span style="display:inline-block;width:9px;height:9px;border-radius:2px;background:var(--green);margin-right:3px"></span>Available</span>
            <span><span style="display:inline-block;width:9px;height:9px;border-radius:2px;background:var(--amber);margin-right:3px"></span>Some slots taken</span>
            <span><span style="display:inline-block;width:9px;height:9px;border-radius:2px;background:var(--red);margin-right:3px"></span>Booked / blocked</span>
        </div>
    </div>
    <div id="ra_slot_info" style="display:none;margin-bottom:12px;padding:8px 12px;border-radius:6px;font-size:12px"></div>
    <div style="display:flex;gap:8px;justify-content:flex-end">
        <button type="button" class="bds" onclick="closeReassignModal()">Cancel</button>
        <button type="submit" class="be" id="ra_submit_btn"><i class="fa-solid fa-right-left me-1"></i>Reassign</button>
    </div>
    </form>
</div></div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
var chartsOk=false; var _ci={};

// ── System-Wide Calendar (Admin) ────────────────────────────────────────
var _mcYear, _mcMonth;
function mcNav(dir){
    if(_mcYear===undefined){const t=new Date();_mcYear=t.getFullYear();_mcMonth=t.getMonth();}
    _mcMonth+=dir;
    if(_mcMonth<0){_mcMonth=11;_mcYear--;}
    if(_mcMonth>11){_mcMonth=0;_mcYear++;}
    renderMyCalendar();
}
function renderMyCalendar(){
    if(_mcYear===undefined){const t=new Date();_mcYear=t.getFullYear();_mcMonth=t.getMonth();}
    const monthNames=['January','February','March','April','May','June','July','August','September','October','November','December'];
    document.getElementById('mc_month_label').textContent=monthNames[_mcMonth]+' '+_mcYear;
    document.getElementById('mc_weekdays').innerHTML=['Su','Mo','Tu','We','Th','Fr','Sa'].map(d=>'<div>'+d+'</div>').join('');
    const grid=document.getElementById('mc_grid'); grid.innerHTML='';
    const map=window._myCalMap||{};
    const firstDay=new Date(_mcYear,_mcMonth,1).getDay();
    const daysInMonth=new Date(_mcYear,_mcMonth+1,0).getDate();
    const todayStr=new Date().toISOString().slice(0,10);
    for(let i=0;i<firstDay;i++) grid.appendChild(document.createElement('div'));
    for(let day=1;day<=daysInMonth;day++){
        const mm=String(_mcMonth+1).padStart(2,'0'), dd=String(day).padStart(2,'0');
        const dateStr=_mcYear+'-'+mm+'-'+dd;
        const entries=map[dateStr]||[];
        const hasCompleted=entries.some(e=>e.status==='completed');
        const hasActive=entries.some(e=>e.status==='approved'||e.status==='in_progress');
        const cell=document.createElement('button');
        cell.type='button';
        const isToday=(dateStr===todayStr);
        let bg='transparent', color='var(--text)';
        if(hasActive){bg='rgba(245,158,11,.18)';color='var(--amber)';}
        else if(hasCompleted){bg='rgba(59,185,122,.18)';color='var(--green)';}
        cell.style.cssText='aspect-ratio:1;border-radius:6px;font-size:12px;border:'+(isToday?'2px solid var(--accent)':'1px solid var(--border)')+
            ';cursor:'+(entries.length?'pointer':'default')+';background:'+bg+';color:'+color+';min-height:34px;position:relative';
        cell.textContent=day;
        if(entries.length){
            const badge=document.createElement('span');
            badge.textContent=entries.length;
            badge.style.cssText='position:absolute;top:1px;right:2px;font-size:8px;background:var(--accent);color:#fff;border-radius:6px;padding:0 3px;line-height:1.3';
            cell.appendChild(badge);
            cell.onclick=function(){ showMcDetails(dateStr,entries); };
        }
        grid.appendChild(cell);
    }
}
function showMcDetails(dateStr,entries){
    const box=document.getElementById('mc_details');
    const d=new Date(dateStr+'T00:00:00');
    const fmt=d.toLocaleDateString('en-US',{weekday:'long',month:'long',day:'numeric',year:'numeric'});
    let html='<div class="panel" style="border-color:var(--accent)"><div class="panel-head"><div class="panel-title">'+fmt+'</div></div>'+
        '<div class="tbl-wrap"><table class="tbl"><thead><tr><th>Instructor</th><th>Course</th><th>Students</th><th>Status</th></tr></thead><tbody>';
    entries.forEach(function(e){
        const statusColor=(e.status==='completed')?'var(--green)':'var(--amber)';
        const statusLabel=(e.status==='completed')?'Completed':(e.status==='in_progress'?'In Progress':'Booked');
        html+='<tr><td>'+e.iname+'</td><td><span style="font-size:11px">'+e.course_type+'</span></td>'+
            '<td style="font-size:11px">'+e.students.length+' — '+e.students.join(', ')+'</td>'+
            '<td><span style="font-size:11px;color:'+statusColor+'">'+statusLabel+'</span></td></tr>';
    });
    html+='</tbody></table></div></div>';
    box.innerHTML=html;
}
document.addEventListener('DOMContentLoaded',function(){ if(document.getElementById('mc_grid')) renderMyCalendar(); });

function openAdminEditSession(s){
    document.getElementById('aes_sess_id').value=s.sess_id;
    document.getElementById('aes_label').textContent=s.sname+' | '+s.course;
    document.getElementById('aes_grade_wrap').style.display=s.isTdc?'block':'none';
    document.getElementById('aes_grade').value=s.grade||'';
    document.getElementById('aes_hours').value=s.hours||'';
    document.getElementById('adminEditSessionModal').style.display='flex';
}
function closeAdminEditSession(){document.getElementById('adminEditSessionModal').style.display='none';}
document.addEventListener('DOMContentLoaded',function(){
    var m2=document.getElementById('adminEditSessionModal');
    if(m2) m2.addEventListener('click',function(e){if(e.target===this)closeAdminEditSession();});
});

function openAdminEditCourse(c){
    document.getElementById('aec_course_id').value=c.id;
    document.getElementById('aec_course_type').textContent=c.type;
    document.getElementById('aec_price').value=c.price;
    document.getElementById('aec_limit').value=c.limit||'';
    document.getElementById('aec_desc').value=c.desc||'';
    document.getElementById('aec_requirements').value=c.reqs||'';
    document.getElementById('adminEditCourseModal').style.display='flex';
}
function closeAdminEditCourse(){document.getElementById('adminEditCourseModal').style.display='none';}
document.addEventListener('DOMContentLoaded',function(){
    var m=document.getElementById('adminEditCourseModal');
    if(m) m.addEventListener('click',function(e){if(e.target===this)closeAdminEditCourse();});
});

function togglePwdField(id,icon){
    const inp=document.getElementById(id);
    if(inp.type==='password'){inp.type='text';icon.classList.remove('fa-eye');icon.classList.add('fa-eye-slash');}
    else{inp.type='password';icon.classList.remove('fa-eye-slash');icon.classList.add('fa-eye');}
}
function showMoreRows(id){
    document.querySelectorAll('.pg-'+id+'-more').forEach(function(r){r.style.display='';});
    var btn=document.getElementById('pg-'+id+'-btn');
    if(btn) btn.style.display='none';
}
function _destroy(id){if(_ci[id]){try{_ci[id].destroy();}catch(e){}delete _ci[id];}}

// ── Student Management ──────────────────────────────────────────────────
function filterStudents(){
    const q=(document.getElementById('stuSearch').value||'').toLowerCase();
    const st=document.getElementById('stuStatusFilter').value;
    const vf=document.getElementById('stuVerifiedFilter').value;
    document.querySelectorAll('#studentsTable tbody tr[data-name]').forEach(function(row){
        const matchQ = !q || row.dataset.name.includes(q) || row.dataset.email.includes(q);
        const matchSt = !st || row.dataset.status===st;
        const matchVf = !vf || row.dataset.verified===vf;
        row.style.display = (matchQ && matchSt && matchVf) ? '' : 'none';
    });
}
function openEditStudentModal(s){
    document.getElementById('es_id').value=s.id;
    document.getElementById('es_fullname').value=s.fullname;
    document.getElementById('es_email').value=s.email;
    document.getElementById('es_mobile').value=s.mobile;
    document.getElementById('es_address').value=s.address;
    document.getElementById('es_gender').value=s.gender;
    document.getElementById('editStudentModal').style.display='flex';
}
function closeEditStudentModal(){document.getElementById('editStudentModal').style.display='none';}

// ── Admin Management ────────────────────────────────────────────────────
function filterAdmins(){
    const q=(document.getElementById('adminSearch').value||'').toLowerCase();
    const st=document.getElementById('adminStatusFilter').value;
    document.querySelectorAll('#adminsTable tbody tr[data-name]').forEach(function(row){
        const matchQ = !q || row.dataset.name.includes(q) || row.dataset.email.includes(q);
        const matchSt = !st || row.dataset.status===st;
        row.style.display = (matchQ && matchSt) ? '' : 'none';
    });
}

['editStudentModal'].forEach(function(id){
    var el=document.getElementById(id);
    if(el) el.addEventListener('click',function(e){if(e.target===this)this.style.display='none';});
});

function initCharts(){
    if(chartsOk||typeof Chart==='undefined') return;
    chartsOk=true;
    var cs=getComputedStyle(document.documentElement);
    var accent=cs.getPropertyValue('--accent').trim()||'#7c6af7';
    var green=cs.getPropertyValue('--green').trim()||'#3cb97a';
    var amber=cs.getPropertyValue('--amber').trim()||'#f59e0b';
    var red=cs.getPropertyValue('--red').trim()||'#e55555';
    var muted=cs.getPropertyValue('--muted').trim()||'#8490a8';
    var border=cs.getPropertyValue('--border').trim()||'#2a3147';
    var bg=cs.getPropertyValue('--bg').trim()||'#0f1117';
    Chart.defaults.color=muted; Chart.defaults.font.family='Segoe UI,sans-serif'; Chart.defaults.font.size=11;

    _destroy('c1'); var e1=document.getElementById('chartNewStudents');
    if(e1){_ci['c1']=new Chart(e1,{type:'line',data:{labels:<?= json_encode($chartMonths) ?>,datasets:[{label:'New Students',data:<?= json_encode($chartNewStudents) ?>,borderColor:accent,backgroundColor:accent+'33',fill:true,tension:0.4,pointBackgroundColor:accent,pointRadius:4,pointHoverRadius:6}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{grid:{color:border+'44'}},y:{grid:{color:border+'44'},beginAtZero:true,ticks:{precision:0,stepSize:1}}}}});}

    _destroy('c2'); var e2=document.getElementById('chartApptStatus');
    if(e2){_ci['c2']=new Chart(e2,{type:'bar',data:{labels:<?= json_encode($chartMonths) ?>,datasets:[{label:'Completed',data:<?= json_encode($chartApptCompleted) ?>,backgroundColor:green+'bb',borderRadius:3,stack:'s'},{label:'Active',data:<?= json_encode($chartApptPending) ?>,backgroundColor:amber+'bb',borderRadius:3,stack:'s'},{label:'Cancelled',data:<?= json_encode($chartApptCancelled) ?>,backgroundColor:red+'88',borderRadius:3,stack:'s'}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom',labels:{boxWidth:10,padding:8}}},scales:{x:{grid:{color:border+'44'},stacked:true},y:{grid:{color:border+'44'},beginAtZero:true,stacked:true,ticks:{precision:0,stepSize:1}}}}});}

    _destroy('c3'); var e3=document.getElementById('chartTdcPdc');
    if(e3){_ci['c3']=new Chart(e3,{type:'doughnut',data:{labels:['TDC Done','PDC Done','Active','Cancelled'],datasets:[{data:[<?= $tdcDoneCount ?>,<?= $pdcDoneCount ?>,<?= $tdcActiveCount+$pdcActiveCount ?>,<?= array_sum($chartApptCancelled) ?>],backgroundColor:[amber+'dd',green+'dd',accent+'dd',red+'99'],borderColor:bg,borderWidth:2,hoverOffset:8}]},options:{responsive:true,maintainAspectRatio:false,cutout:'55%',plugins:{legend:{display:false}}}});}

    _destroy('c4'); var e4=document.getElementById('chartGradeDist');
    if(e4){_ci['c4']=new Chart(e4,{type:'bar',data:{labels:['90–100','80–89','70–79','60–69','< 60'],datasets:[{label:'Sessions',data:[<?= implode(',',$gradeBuckets) ?>],backgroundColor:[green+'cc',accent+'cc',amber+'cc','#fb923c99',red+'99'],borderRadius:4}]},options:{indexAxis:'y',responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{grid:{color:border+'44'},beginAtZero:true,ticks:{precision:0,stepSize:1}},y:{grid:{color:border+'22'}}}}});}

    _destroy('c5'); var e5=document.getElementById('chartGender');
    if(e5){_ci['c5']=new Chart(e5,{type:'doughnut',data:{labels:['Male','Female','Not Specified'],datasets:[{data:[<?= $maleCount ?>,<?= $femaleCount ?>,<?= $otherGenderCount ?>],backgroundColor:['#60a5fadd','#f472b6dd',border+'cc'],borderColor:bg,borderWidth:2,hoverOffset:6}]},options:{responsive:true,maintainAspectRatio:false,cutout:'55%',plugins:{legend:{display:false}}}});}
}

window.addEventListener('load',function(){
    document.querySelectorAll('.section').forEach(function(el){el.style.display=el.classList.contains('active')?'block':'none';});
    var dash=document.getElementById('section-dashboard');
    if(dash&&dash.style.display==='block') initCharts();
});

function openSidebar(){document.getElementById('sidebar').classList.add('open');document.getElementById('sidebarOverlay').classList.add('open');}
function closeSidebar(){document.getElementById('sidebar').classList.remove('open');document.getElementById('sidebarOverlay').classList.remove('open');}

function showSection(n){
    document.querySelectorAll('.section').forEach(function(el){el.classList.remove('active');el.style.display='none';});
    var el=document.getElementById('section-'+n);
    if(el){el.classList.add('active');el.style.display='block';}
    document.querySelectorAll('.nav-item').forEach(function(nav){nav.classList.remove('active');var oc=nav.getAttribute('onclick')||'';if(oc.indexOf("'"+n+"'")>-1)nav.classList.add('active');});
    var ti=document.getElementById('page-title-text'); if(ti)ti.textContent=T[n]||n;
    var tim=document.getElementById('page-title-text-mobile'); if(tim)tim.textContent=T[n]||n;
    if(n==='dashboard'){chartsOk=false;initCharts();}
    closeSidebar();
    return false;
}

function refreshSto(){
    var radios=document.querySelectorAll('.sto-radio'); var val='';
    for(var i=0;i<radios.length;i++){var r=radios[i],lbl=r.closest('label');if(!lbl)continue;var btn=lbl.querySelector('.sto-btn');if(!btn)continue;if(r.checked){btn.style.borderColor=btn.getAttribute('data-color');btn.style.background='rgba(255,255,255,.04)';val=r.value;}else{btn.style.borderColor='var(--border)';btn.style.background='';}}
    var ss=document.getElementById('ann-specific-student'); var si=document.getElementById('ann-specific-instructor');
    if(ss) ss.style.display=(val==='specific_student')?'block':'none';
    if(si) si.style.display=(val==='specific_instructor')?'block':'none';
    var sid=document.getElementById('ann-sid'); var iid=document.getElementById('ann-iid');
    if(sid) sid.required=(val==='specific_student');
    if(iid) iid.required=(val==='specific_instructor');
}
document.querySelectorAll('.sto-radio').forEach(function(r){r.addEventListener('change',refreshSto);});
document.querySelectorAll('.sto-btn').forEach(function(btn){btn.addEventListener('click',function(){var lbl=this.closest('label');if(lbl){var r=lbl.querySelector('.sto-radio');if(r){r.checked=true;refreshSto();}}});});
refreshSto();

// ── Assign Student JS ──────────────────────────────────────────────────────
var _instrCourses = <?php
    $icMapJs=[];
    $icRes2=q($conn,"SELECT ic.*,i.fullname AS iname FROM instructor_courses ic JOIN instructors i ON i.instructor_id=ic.instructor_id WHERE ic.is_active=1 ORDER BY i.fullname,ic.course_type");
    while($ic2=$icRes2->fetch_assoc()){
        $icMapJs[$ic2['instructor_id']][]=['course_id'=>$ic2['course_id'],'course_type'=>$ic2['course_type'],'price_per_hr'=>$ic2['price_per_hr'],'student_limit'=>$ic2['student_limit']??null,'is_tdc'=>stripos($ic2['course_type'],'TDC')===0];
    }
    echo json_encode($icMapJs);
?>;

var _scheduleMap = <?php
    $smRes=q($conn,"SELECT cs.course_id,cs.schedule_date,cs.booked_count,cs.student_limit FROM course_schedules cs WHERE cs.schedule_date>=CURDATE()");
    $smJs=[];
    while($sm=$smRes->fetch_assoc()){
        $key=$sm['course_id'].':'.$sm['schedule_date'];
        $smJs[$key]=['booked'=>(int)$sm['booked_count'],'limit'=>(int)$sm['student_limit']];
    }
    echo json_encode($smJs);
?>;

var _instrNames = <?php
    $inRes=q($conn,"SELECT instructor_id,fullname FROM instructors ORDER BY fullname");
    $inJs=[];
    while($in=$inRes->fetch_assoc()){ $inJs[$in['instructor_id']]=$in['fullname']; }
    echo json_encode($inJs);
?>;

var _assignSelectedCourse=null; // {course_id, course_type, is_tdc, is_combo, limit}
var _assignCalYear, _assignCalMonth, _assignSelectedDate=null;

function loadAssignCourses(instrId){
    var sel=document.getElementById('assign_course_sel');
    sel.innerHTML='<option value="">— Select Course —</option>';
    document.getElementById('assign_calendar').style.display='none';
    document.getElementById('assign_date').value='';
    document.getElementById('assign_date_label').textContent='Pick a date';
    _assignSelectedCourse=null;
    if(!instrId||!_instrCourses[instrId]){return;}
    _instrCourses[instrId].forEach(function(c){
        var opt=document.createElement('option');
        opt.value=c.course_id;
        var limitStr=c.student_limit?(' | '+c.student_limit+'/date'):'';
        opt.textContent=c.course_type+' — ₱'+parseFloat(c.price_per_hr).toFixed(2)+limitStr;
        opt.dataset.isTdc=c.is_tdc?'1':'0';
        opt.dataset.isCombo=(c.course_type.indexOf('Both')!==-1)?'1':'0';
        opt.dataset.courseType=c.course_type;
        opt.dataset.limit=c.student_limit||'0';
        sel.appendChild(opt);
    });
    checkAssignSlot();
}

function toggleAssignCalendar(){
    var instrId=document.getElementById('assign_instr_sel').value;
    var courseId=document.getElementById('assign_course_sel').value;
    if(!instrId||!courseId){ alert('Select an instructor and course first.'); return; }
    var sel=document.getElementById('assign_course_sel');
    var opt=sel.options[sel.selectedIndex];
    _assignSelectedCourse={
        course_id:courseId,
        instructor_id:instrId,
        course_type:opt.dataset.courseType,
        is_tdc:opt.dataset.isTdc==='1',
        is_combo:opt.dataset.isCombo==='1',
        limit:parseInt(opt.dataset.limit||'0')
    };
    var cal=document.getElementById('assign_calendar');
    cal.style.display=(cal.style.display==='none')?'block':'none';
    if(cal.style.display==='block'){
        var today=new Date();
        _assignCalYear=today.getFullYear(); _assignCalMonth=today.getMonth();
        renderAssignCalendar();
    }
}
function assignCalNav(dir){
    _assignCalMonth+=dir;
    if(_assignCalMonth<0){_assignCalMonth=11;_assignCalYear--;}
    if(_assignCalMonth>11){_assignCalMonth=0;_assignCalYear++;}
    renderAssignCalendar();
}
function assignDateStatus(dateStr){
    const c=_assignSelectedCourse;
    const d=new Date(dateStr+'T00:00:00');
    const today=new Date(); today.setHours(0,0,0,0);
    if(d<=today) return {status:'past'};
    const dow=d.getDay(); // 0=Sun,1=Mon,...,6=Sat
    if(c.is_tdc && dow!==6) return {status:'wrongday'};
    if(!c.is_tdc){
        const okDays=c.is_combo?[2,3]:[2,4];
        if(okDays.indexOf(dow)===-1) return {status:'wrongday'};
    }
    const busyEntries=(window._myCalMap||{})[dateStr]||[];
    const otherType=busyEntries.find(function(b){
        if(String(b.instructor_id)!==String(c.instructor_id)) return false;
        if(b.course_type===c.course_type) return false;
        if(c.is_tdc && b.course_type.indexOf('TDC')===0) return false;
        return true;
    });
    if(otherType) return {status:'blocked',label:'Booked: '+otherType.course_type};
    if(c.limit>0){
        const key=c.course_id+':'+dateStr;
        const slot=_scheduleMap[key];
        const booked=slot?slot.booked:0;
        if(booked>=c.limit) return {status:'blocked',label:'Fully booked ('+booked+'/'+c.limit+')'};
        if(booked>0) return {status:'partial',label:booked+'/'+c.limit+' slots taken'};
    }
    return {status:'open',label:'Available'};
}
function renderAssignCalendar(){
    const monthNames=['January','February','March','April','May','June','July','August','September','October','November','December'];
    document.getElementById('assign_cal_month_label').textContent=monthNames[_assignCalMonth]+' '+_assignCalYear;
    document.getElementById('assign_cal_weekdays').innerHTML=['Su','Mo','Tu','We','Th','Fr','Sa'].map(d=>'<div>'+d+'</div>').join('');
    const grid=document.getElementById('assign_cal_grid'); grid.innerHTML='';
    const firstDay=new Date(_assignCalYear,_assignCalMonth,1).getDay();
    const daysInMonth=new Date(_assignCalYear,_assignCalMonth+1,0).getDate();
    const colors={past:'var(--border)',wrongday:'var(--border)',blocked:'var(--red)',partial:'var(--amber)',open:'var(--green)'};
    for(let i=0;i<firstDay;i++) grid.appendChild(document.createElement('div'));
    for(let day=1;day<=daysInMonth;day++){
        const mm=String(_assignCalMonth+1).padStart(2,'0'), dd=String(day).padStart(2,'0');
        const dateStr=_assignCalYear+'-'+mm+'-'+dd;
        const st=assignDateStatus(dateStr);
        const cell=document.createElement('button');
        cell.type='button'; cell.textContent=day;
        const isSelected=(_assignSelectedDate===dateStr);
        const clickable=(st.status==='open'||st.status==='partial');
        cell.style.cssText='aspect-ratio:1;border-radius:6px;font-size:12px;border:1px solid var(--border);cursor:'+(clickable?'pointer':'not-allowed')+
            ';background:'+(isSelected?'var(--accent)':(st.status==='past'||st.status==='wrongday'?'transparent':colors[st.status]+'22'))+
            ';color:'+(isSelected?'#fff':(st.status==='past'||st.status==='wrongday'?'var(--muted)':'var(--text)'))+
            ';opacity:'+((st.status==='past'||st.status==='wrongday')?'0.35':'1')+';min-height:30px';
        cell.disabled=!clickable;
        cell.title=st.label||'';
        if(clickable) cell.onclick=function(){ selectAssignDate(dateStr); };
        grid.appendChild(cell);
    }
}
function selectAssignDate(dateStr){
    _assignSelectedDate=dateStr;
    document.getElementById('assign_date').value=dateStr;
    document.getElementById('assign_date_label').textContent=dateStr;
    renderAssignCalendar();
    checkAssignSlot();
}

function checkAssignSlot(){
    var courseId=document.getElementById('assign_course_sel').value;
    var date=document.getElementById('assign_date').value;
    var info=document.getElementById('assign_slot_info');
    if(!courseId||!date){info.style.display='none';return;}
    var sel=document.getElementById('assign_course_sel');
    var opt=sel.options[sel.selectedIndex];
    var limit=opt?parseInt(opt.dataset.limit||'0'):0;
    if(!limit){info.style.display='none';return;}
    var key=courseId+':'+date;
    var slot=_scheduleMap[key];
    var booked=slot?slot.booked:0;
    var left=limit-booked;
    info.style.display='block';
    if(left<=0){
        info.style.cssText='display:block;padding:8px 12px;border-radius:6px;font-size:12px;background:rgba(229,85,85,.12);border:1px solid rgba(229,85,85,.3);color:var(--red)';
        info.innerHTML='<i class="fa-solid fa-ban me-1"></i><strong>Fully booked</strong> on this date ('+booked+'/'+limit+'). Choose a different date.';
    } else {
        info.style.cssText='display:block;padding:8px 12px;border-radius:6px;font-size:12px;background:rgba(59,185,122,.1);border:1px solid rgba(59,185,122,.3);color:var(--green)';
        info.innerHTML='<i class="fa-solid fa-circle-check me-1"></i><strong>'+left+' slot'+(left>1?'s':'')+' available</strong> ('+booked+'/'+limit+' booked).';
    }
}

document.getElementById('assign_course_sel').addEventListener('change',function(){
    document.getElementById('assign_calendar').style.display='none';
    document.getElementById('assign_date').value='';
    document.getElementById('assign_date_label').textContent='Pick a date';
    checkAssignSlot();
});

function syncAndConfirmAssign(){
    document.getElementById('assign_hrs_hidden').value=document.getElementById('assign_hrs').value||8;
    document.getElementById('assign_note_hidden').value=document.getElementById('assign_note').value||'';
    var s=document.getElementById('assign_student_sel');
    var i=document.getElementById('assign_instr_sel');
    var c=document.getElementById('assign_course_sel');
    var d=document.getElementById('assign_date');
    if(!s.value||!i.value||!c.value||!d.value){alert('Please fill in all required fields.');return false;}
    var sName=s.options[s.selectedIndex].text;
    var iName=i.options[i.selectedIndex].text;
    var cName=c.options[c.selectedIndex].text;
    return confirm('Assign '+sName+' to '+iName+'\nCourse: '+cName+'\nDate: '+d.value+'\n\nThis will create a pre-approved appointment. Continue?');
}

// ── Workload: View Students toggle ─────────────────────────────────────────
function toggleWlDetail(instrId){
    var row=document.getElementById('wl-detail-'+instrId);
    if(!row)return;
    var isHidden=row.style.display==='none';
    row.style.display=isHidden?'table-row':'none';
}

// ── Reassign Modal ───────────────────────────────────────────────────────────
var _raCurrentInstr=0, _raIsTDC=0, _raCourseType='', _raOrigDate='', _raOrigHours=8;

var _raSelectedCourse=null; // {course_id, instructor_id, course_type, is_tdc, is_combo, limit}
var _raCalYear, _raCalMonth, _raSelectedDate=null;

function openReassignModal(apptId,oldInstrId,studentName,courseType,startDate,hours,isTDC){
    _raCurrentInstr=oldInstrId;
    _raIsTDC=isTDC;
    _raCourseType=courseType;
    _raOrigDate=startDate;
    _raSelectedCourse=null;
    _raSelectedDate=null;

    document.getElementById('ra_appt_id').value=apptId;
    document.getElementById('ra_old_instr').value=oldInstrId;
    document.getElementById('ra_summary').textContent='Student: '+studentName;
    document.getElementById('ra_course_type_display').textContent=courseType;
    document.getElementById('ra_course_type_hint').textContent=courseType;
    document.getElementById('ra_date').value='';
    document.getElementById('ra_date_label').textContent='Select instructor & course first';
    document.getElementById('ra_calendar').style.display='none';
    document.getElementById('ra_day_hint').textContent=isTDC?'(Saturday only)':(courseType.indexOf('Both')!==-1?'(Tuesday or Wednesday only)':'(Tuesday or Thursday only)');
    document.getElementById('ra_slot_info').style.display='none';

    // Populate instructor dropdown — exclude current instructor, only those offering this course type
    var sel=document.getElementById('ra_instr_sel');
    sel.innerHTML='<option value="">— Select Instructor —</option>';
    Object.keys(_instrCourses).forEach(function(instrId){
        if(parseInt(instrId)===oldInstrId)return;
        var hasMatch=_instrCourses[instrId].some(function(c){return c.course_type===courseType;});
        if(!hasMatch)return;
        var opt=document.createElement('option');
        opt.value=instrId;
        opt.textContent=_instrNames[instrId]||('Instructor #'+instrId);
        sel.appendChild(opt);
    });
    document.getElementById('ra_course_sel').innerHTML='<option value="">— Select Instructor First —</option>';

    document.getElementById('reassignModal').style.display='flex';
}

function loadReassignCourses(){
    var instrId=document.getElementById('ra_instr_sel').value;
    var sel=document.getElementById('ra_course_sel');
    sel.innerHTML='<option value="">— Select Course —</option>';
    document.getElementById('ra_calendar').style.display='none';
    document.getElementById('ra_date').value='';
    document.getElementById('ra_date_label').textContent='Pick a date';
    _raSelectedCourse=null;
    if(!instrId||!_instrCourses[instrId]){return;}
    _instrCourses[instrId].forEach(function(c){
        if(c.course_type!==_raCourseType)return;
        var opt=document.createElement('option');
        opt.value=c.course_id;
        var limitStr=c.student_limit?(' | '+c.student_limit+'/date'):'';
        opt.textContent=c.course_type+' — ₱'+parseFloat(c.price_per_hr).toFixed(2)+limitStr;
        opt.dataset.limit=c.student_limit||'0';
        opt.dataset.instrId=instrId;
        sel.appendChild(opt);
    });
    checkReassignSlot();
}

function toggleReassignCalendar(){
    var courseSel=document.getElementById('ra_course_sel');
    if(!courseSel.value){ alert('Select an instructor and course first.'); return; }
    var opt=courseSel.options[courseSel.selectedIndex];
    _raSelectedCourse={
        course_id:courseSel.value,
        instructor_id:opt.dataset.instrId,
        course_type:_raCourseType,
        is_tdc:_raIsTDC,
        is_combo:_raCourseType.indexOf('Both')!==-1,
        limit:parseInt(opt.dataset.limit||'0')
    };
    var cal=document.getElementById('ra_calendar');
    cal.style.display=(cal.style.display==='none')?'block':'none';
    if(cal.style.display==='block'){
        var today=new Date();
        _raCalYear=today.getFullYear(); _raCalMonth=today.getMonth();
        renderReassignCalendar();
    }
}
function raCalNav(dir){
    _raCalMonth+=dir;
    if(_raCalMonth<0){_raCalMonth=11;_raCalYear--;}
    if(_raCalMonth>11){_raCalMonth=0;_raCalYear++;}
    renderReassignCalendar();
}
function raDateStatus(dateStr){
    const c=_raSelectedCourse;
    const d=new Date(dateStr+'T00:00:00');
    const today=new Date(); today.setHours(0,0,0,0);
    if(d<=today) return {status:'past'};
    const dow=d.getDay();
    if(c.is_tdc && dow!==6) return {status:'wrongday'};
    if(!c.is_tdc){
        const okDays=c.is_combo?[2,3]:[2,4];
        if(okDays.indexOf(dow)===-1) return {status:'wrongday'};
    }
    const busyEntries=(window._myCalMap||{})[dateStr]||[];
    const otherType=busyEntries.find(function(b){
        if(String(b.instructor_id)!==String(c.instructor_id)) return false;
        if(b.course_type===c.course_type) return false;
        if(c.is_tdc && b.course_type.indexOf('TDC')===0) return false;
        return true;
    });
    if(otherType) return {status:'blocked',label:'Booked: '+otherType.course_type};
    if(c.limit>0){
        const key=c.course_id+':'+dateStr;
        const slot=_scheduleMap[key];
        const booked=slot?slot.booked:0;
        if(booked>=c.limit) return {status:'blocked',label:'Fully booked ('+booked+'/'+c.limit+')'};
        if(booked>0) return {status:'partial',label:booked+'/'+c.limit+' slots taken'};
    }
    return {status:'open',label:'Available'};
}
function renderReassignCalendar(){
    const monthNames=['January','February','March','April','May','June','July','August','September','October','November','December'];
    document.getElementById('ra_cal_month_label').textContent=monthNames[_raCalMonth]+' '+_raCalYear;
    document.getElementById('ra_cal_weekdays').innerHTML=['Su','Mo','Tu','We','Th','Fr','Sa'].map(d=>'<div>'+d+'</div>').join('');
    const grid=document.getElementById('ra_cal_grid'); grid.innerHTML='';
    const firstDay=new Date(_raCalYear,_raCalMonth,1).getDay();
    const daysInMonth=new Date(_raCalYear,_raCalMonth+1,0).getDate();
    const colors={past:'var(--border)',wrongday:'var(--border)',blocked:'var(--red)',partial:'var(--amber)',open:'var(--green)'};
    for(let i=0;i<firstDay;i++) grid.appendChild(document.createElement('div'));
    for(let day=1;day<=daysInMonth;day++){
        const mm=String(_raCalMonth+1).padStart(2,'0'), dd=String(day).padStart(2,'0');
        const dateStr=_raCalYear+'-'+mm+'-'+dd;
        const st=raDateStatus(dateStr);
        const cell=document.createElement('button');
        cell.type='button'; cell.textContent=day;
        const isSelected=(_raSelectedDate===dateStr);
        const clickable=(st.status==='open'||st.status==='partial');
        cell.style.cssText='aspect-ratio:1;border-radius:6px;font-size:12px;border:1px solid var(--border);cursor:'+(clickable?'pointer':'not-allowed')+
            ';background:'+(isSelected?'var(--accent)':(st.status==='past'||st.status==='wrongday'?'transparent':colors[st.status]+'22'))+
            ';color:'+(isSelected?'#fff':(st.status==='past'||st.status==='wrongday'?'var(--muted)':'var(--text)'))+
            ';opacity:'+((st.status==='past'||st.status==='wrongday')?'0.35':'1')+';min-height:30px';
        cell.disabled=!clickable;
        cell.title=st.label||'';
        if(clickable) cell.onclick=function(){ selectReassignDate(dateStr); };
        grid.appendChild(cell);
    }
}
function selectReassignDate(dateStr){
    _raSelectedDate=dateStr;
    document.getElementById('ra_date').value=dateStr;
    document.getElementById('ra_date_label').textContent=dateStr;
    renderReassignCalendar();
    checkReassignSlot();
}

function checkReassignSlot(){
    var courseSel=document.getElementById('ra_course_sel');
    var date=document.getElementById('ra_date').value;
    var info=document.getElementById('ra_slot_info');
    if(!courseSel.value||!date){info.style.display='none';return;}
    var opt=courseSel.options[courseSel.selectedIndex];
    var limit=opt?parseInt(opt.dataset.limit||'0'):0;
    if(!limit){info.style.display='none';return;}
    var key=courseSel.value+':'+date;
    var slot=_scheduleMap[key];
    var booked=slot?slot.booked:0;
    var left=limit-booked;
    info.style.display='block';
    if(left<=0){
        info.style.cssText='display:block;padding:8px 12px;border-radius:6px;font-size:12px;background:rgba(229,85,85,.12);border:1px solid rgba(229,85,85,.3);color:var(--red)';
        info.innerHTML='<i class="fa-solid fa-ban me-1"></i><strong>Fully booked</strong> on this date ('+booked+'/'+limit+'). Choose a different date.';
    } else {
        info.style.cssText='display:block;padding:8px 12px;border-radius:6px;font-size:12px;background:rgba(59,185,122,.1);border:1px solid rgba(59,185,122,.3);color:var(--green)';
        info.innerHTML='<i class="fa-solid fa-circle-check me-1"></i><strong>'+left+' slot'+(left>1?'s':'')+' available</strong> ('+booked+'/'+limit+' booked).';
    }
}

function closeReassignModal(){document.getElementById('reassignModal').style.display='none';}
document.getElementById('reassignModal').addEventListener('click',function(e){if(e.target===this)closeReassignModal();});
</script>

</body>
</html>
