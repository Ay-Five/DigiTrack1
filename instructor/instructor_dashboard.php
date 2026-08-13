<?php
require_once 'instructor_backend.php';
$sbTotalSess=(int)q($conn,"SELECT COUNT(*) AS c FROM sessions WHERE instructor_id=$instructor_id AND status='completed'")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Instructor Dashboard – MotoSafe Driving School</title>
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
.main{margin-left:var(--sidebar-w);flex:1;padding:24px;min-height:100vh;transition:margin-left .3s ease}
.page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px}
.page-title{font-size:18px;font-weight:600}
.section{display:none}.section.active{display:block}
.panel{background:var(--bg2);border:1px solid var(--border);border-radius:10px;overflow:hidden;margin-bottom:16px}
.panel-head{padding:14px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px}
.panel-title{font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px}
.stats-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin-bottom:20px}
.stat-card{background:var(--bg2);border:1px solid var(--border);border-radius:10px;padding:16px;cursor:pointer}
.stat-num{font-size:26px;font-weight:700;color:var(--accent)}
.stat-label{font-size:11px;color:var(--muted);margin-top:4px}
.duo-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px}
.tbl-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch}
@media(max-width:900px){.duo-grid{grid-template-columns:1fr}}
@media(max-width:768px){
    .sidebar{transform:translateX(-100%)}
    .sidebar.open{transform:translateX(0)}
    .sidebar-close{display:block}
    .top-bar{display:flex}
    .main{margin-left:0;padding:16px}
    .page-header{flex-direction:column;align-items:flex-start;gap:6px}
    .stats-row{grid-template-columns:repeat(2,1fr)}
    .duo-grid{grid-template-columns:1fr}
    .panel-head{flex-direction:column;align-items:flex-start}
    .chat-layout{flex-direction:column!important;height:auto!important}
    .chat-contacts{width:100%!important;max-height:220px}
    .profile-grid{grid-template-columns:1fr!important}
}
@media(max-width:480px){
    .stats-row{grid-template-columns:repeat(2,1fr)}
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
input[type="date"].di::-webkit-calendar-picker-indicator{filter:invert(1);cursor:pointer}
.di:focus{outline:none;border-color:var(--accent)}
.form-label{font-size:11px;color:var(--muted);margin-bottom:4px;display:block}
.flash{padding:10px 16px;border-radius:8px;margin-bottom:16px;font-size:12px}
.flash-ok{background:rgba(59,185,122,.15);border:1px solid rgba(59,185,122,.3);color:var(--green)}
.flash-err{background:rgba(229,85,85,.15);border:1px solid rgba(229,85,85,.3);color:var(--red)}
.prog-bar{height:8px;background:var(--border);border-radius:4px;overflow:hidden}
.prog-fill{height:100%;background:var(--accent);border-radius:4px}
.cert-badge{background:rgba(245,158,11,.12);border:1px solid rgba(245,158,11,.3);color:#f59e0b;padding:4px 10px;border-radius:20px;font-size:11px;display:inline-flex;align-items:center;gap:5px}
.profile-avatar{width:72px;height:72px;border-radius:50%;background:var(--accent);display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:700;color:#fff;flex-shrink:0}
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
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<div class="sidebar" id="sidebar">
    <div class="brand"><span><i class="fa-solid fa-chalkboard-user me-2"></i>ABROT Instructor</span><button class="sidebar-close" onclick="closeSidebar()"><i class="fa-solid fa-xmark"></i></button></div>
    <a class="nav-item <?= $activeSection==='dashboard'?'active':'' ?>" onclick="showSection('dashboard')" href="#">
        <i class="fa-solid fa-gauge"></i> Dashboard</a>
    <a class="nav-item <?= $activeSection==='appointments'?'active':'' ?>" onclick="showSection('appointments')" href="#">
        <i class="fa-solid fa-calendar-check"></i> Appointments
        <?php if($pendingSessionLogs->num_rows>0): ?><span class="tag tag-amber"><?= $pendingSessionLogs->num_rows ?></span><?php endif; ?></a>
    <a class="nav-item <?= $activeSection==='calendar'?'active':'' ?>" onclick="showSection('calendar')" href="#">
        <i class="fa-solid fa-calendar-days"></i> My Calendar</a>
    <a class="nav-item <?= in_array($activeSection,['students','student_profile'])?'active':'' ?>" onclick="showSection('students')" href="#">
        <i class="fa-solid fa-users"></i> My Students</a>
    <a class="nav-item <?= $activeSection==='performance'?'active':'' ?>" onclick="showSection('performance')" href="#">
        <i class="fa-solid fa-star"></i> Performance
        <?php if($unratedSessions->num_rows>0): ?><span class="tag tag-amber"><?= $unratedSessions->num_rows ?></span><?php endif; ?></a>
    <a class="nav-item <?= $activeSection==='courses'?'active':'' ?>" onclick="showSection('courses')" href="#">
        <i class="fa-solid fa-book-open"></i> My Courses</a>
    <a class="nav-item <?= $activeSection==='messages'?'active':'' ?>" onclick="showSection('messages')" href="#">
        <i class="fa-solid fa-bell"></i> Notifications
        <?php if($unreadCnt>0): ?><span class="tag tag-amber"><?= $unreadCnt ?></span><?php endif; ?></a>
    <a class="nav-item <?= in_array($activeSection,['chat'])?'active':'' ?>" onclick="showSection('chat')" href="#">
        <i class="fa-solid fa-comments"></i> Messages
        <?php if($totalUnreadMsg>0): ?><span class="tag tag-blue"><?= $totalUnreadMsg ?></span><?php endif; ?></a>
    <a class="nav-item <?= $activeSection==='profile'?'active':'' ?>" onclick="showSection('profile')" href="#">
        <i class="fa-solid fa-user-circle"></i> Profile</a>
    <div style="flex:1"></div>

    <?php $sbRatColor=$avgRating>=4?'#3cb97a':($avgRating>=3?'#f59e0b':'#e55'); ?>
    <div class="sb-analytics">
        <div class="sb-analytics-title"><i class="fa-solid fa-chart-line" style="color:var(--accent)"></i> My Stats</div>
        <div class="sb-stat"><div class="sb-stat-label">My Rating</div><div class="sb-stat-val" style="color:<?= $sbRatColor ?>">★ <?= $avgRating ?: '—' ?></div></div>
        <div class="sb-stat"><div class="sb-stat-label">Total Students</div><div class="sb-stat-val" style="color:var(--accent)"><?= $sbTotalStudents ?></div></div>
        <div class="sb-stat"><div class="sb-stat-label">Hours Taught</div><div class="sb-stat-val"><?= $sbTotalHrsTaught ?>h</div></div>
        <div class="sb-stat"><div class="sb-stat-label">This Month</div><div class="sb-stat-val" style="color:var(--amber)"><?= $sbMonthHrs ?>h</div></div>
        <div class="sb-stat"><div class="sb-stat-label">Total Sessions</div><div class="sb-stat-val"><?= $sbTotalSess ?></div></div>

    </div>
    <a class="nav-item" href="instructor_logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
</div>

<div class="main">
<div class="top-bar">
    <button class="hamburger" onclick="openSidebar()"><i class="fa-solid fa-bars"></i></button>
    <div class="page-title" id="page-title-text-mobile">Dashboard</div>
</div>
<div class="page-header">
    <div class="page-title" id="page-title-text">Dashboard</div>
    <div style="font-size:12px;color:var(--muted)">Welcome, <?= htmlspecialchars($instructor['fullname']) ?></div>
</div>

<?php if($flash): ?><div class="flash flash-ok"><i class="fa-solid fa-circle-check me-2"></i><?= htmlspecialchars($flash) ?></div><?php endif; ?>
<?php if($flashErr): ?><div class="flash flash-err"><i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($flashErr) ?></div><?php endif; ?>

<!-- ══ DASHBOARD ══════════════════════════════════════════════ -->
<div id="section-dashboard" class="section <?= $activeSection==='dashboard'?'active':'' ?>">
<div class="stats-row">
    <div class="stat-card" onclick="showSection('appointments')">
        <div class="stat-num" style="color:var(--amber)"><?= $statPending ?></div>
        <div class="stat-label">Pending Requests</div>
    </div>
    <div class="stat-card" onclick="showSection('appointments')">
        <div class="stat-num"><?= $statActive ?></div>
        <div class="stat-label">Active Appointments</div>
    </div>
    <div class="stat-card" onclick="showSection('appointments')">
        <div class="stat-num" style="color:var(--green)"><?= $statDone ?></div>
        <div class="stat-label">Completed</div>
    </div>
    <div class="stat-card">
        <div class="stat-num" style="color:var(--amber)"><?= $avgRating ? '★ '.$avgRating : '—' ?></div>
        <div class="stat-label">Avg. Rating</div>
    </div>
</div>

<!-- Top Students This Month -->
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-trophy" style="color:var(--amber)"></i> Top 5 Students – <?= date('F Y') ?></div>
        <small style="color:var(--muted);font-size:10px">Monthly reset</small>
    </div>
    <div class="tbl-wrap">
    <table class="tbl"><thead><tr><th>#</th><th>Student</th><th>Avg Grade</th><th>Sessions</th></tr></thead><tbody>
    <?php $topStudents->data_seek(0); $rank=1; if($topStudents->num_rows===0): ?>
    <tr><td colspan="4" style="color:var(--muted);text-align:center;padding:20px">No graded sessions this month.</td></tr>
    <?php else: while($ts=$topStudents->fetch_assoc()): $medals=['🥇','🥈','🥉','4️⃣','5️⃣']; $gc=$ts['avg_grade']>=80?'var(--green)':($ts['avg_grade']>=60?'var(--amber)':'var(--red)'); ?>
    <tr>
        <td><span style="font-size:14px"><?= $medals[$rank-1]??$rank ?></span></td>
        <td><?= htmlspecialchars($ts['fullname']) ?></td>
        <td><strong style="color:<?= $gc ?>"><?= $ts['avg_grade'] ?>/100</strong></td>
        <td><?= $ts['sess_cnt'] ?></td>
    </tr>
    <?php $rank++; endwhile; endif; ?>
    </tbody></table>
    </div><!-- /tbl-wrap -->
</div>

<!-- Analytics Charts -->
<div style="font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin:20px 0 12px;padding-bottom:6px;border-bottom:1px solid var(--border)">
    <i class="fa-solid fa-chart-line me-2" style="color:var(--accent)"></i>My Performance Analytics
</div>
<div class="duo-grid" style="margin-bottom:16px">
    <div class="panel">
        <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-chart-area me-1"></i> Hours Taught – Last 6 Months</div></div>
        <div style="padding:16px;height:200px;position:relative"><canvas id="instrChartHours"></canvas></div>
    </div>
    <div class="panel">
        <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-chart-pie me-1"></i> TDC vs PDC Sessions</div></div>
        <div style="padding:16px;display:flex;align-items:center;gap:20px;height:200px">
            <div style="flex:0 0 150px;height:150px;position:relative"><canvas id="instrChartSplit"></canvas></div>
            <div style="flex:1;font-size:12px;display:flex;flex-direction:column;gap:12px">
                <div><div style="color:var(--amber);font-weight:700;font-size:18px"><?= $instrTDCSess ?></div><div style="color:var(--muted);font-size:11px">TDC Sessions</div></div>
                <div><div style="color:var(--green);font-weight:700;font-size:18px"><?= $instrPDCSess ?></div><div style="color:var(--muted);font-size:11px">PDC Sessions</div></div>
                <div><div style="color:var(--accent);font-weight:700;font-size:18px"><?= $instrTDCSess+$instrPDCSess ?></div><div style="color:var(--muted);font-size:11px">Total Sessions</div></div>
            </div>
        </div>
    </div>
</div>
<div class="panel" style="margin-bottom:16px">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-chart-bar me-1"></i> My Students' Grade Distribution</div></div>
    <div style="padding:16px;height:200px;position:relative"><canvas id="instrChartGrades"></canvas></div>
</div>
</div>

<!-- ══ APPOINTMENTS ═══════════════════════════════════════════ -->
<div id="section-appointments" class="section <?= $activeSection==='appointments'?'active':'' ?>">

<!-- ── TODAY'S APPOINTMENTS ─────────────────────────────────────── -->
<div style="font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:12px;padding-bottom:6px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
    <span><i class="fa-solid fa-calendar-day me-1" style="color:var(--accent)"></i>Today's Appointments <span style="font-weight:400;text-transform:none;letter-spacing:0">(package due to be logged today)</span></span>
    <?php if($todayAppts->num_rows>1): ?>
    <form method="POST" onsubmit="return confirm('Log all <?= $todayAppts->num_rows ?> packages as complete for today? Grades can be set individually afterward.')" style="display:inline">
        <input type="hidden" name="action" value="log_all_sessions">
        <input type="hidden" name="end_date" value="<?= $today ?>">
        <button type="submit" class="be" style="font-size:11px"><i class="fa-solid fa-check-double"></i> Log All (<?= $todayAppts->num_rows ?>)</button>
    </form>
    <?php endif; ?>
</div>
<div class="panel" style="margin-bottom:20px">
    <div class="tbl-wrap">
    <table class="tbl"><thead><tr><th>Student</th><th>Course</th><th>Package Dates</th><th>Status</th><th>Actions</th></tr></thead><tbody>
    <?php $todayAppts->data_seek(0); if($todayAppts->num_rows===0): ?>
    <tr><td colspan="5" style="color:var(--muted);text-align:center;padding:20px">No appointments scheduled for today.</td></tr>
    <?php else: while($ap=$todayAppts->fetch_assoc()):
        $tc=$cColors[$ap['course_type']]??'tag-gray';
        $isTDC=stripos($ap['course_type'],'TDC')===0;
    ?>
    <tr>
        <td><strong><?= htmlspecialchars($ap['sname']) ?></strong></td>
        <td><span class="tag <?= $tc ?>"><?= htmlspecialchars($ap['course_type']) ?></span></td>
        <td style="font-size:11px;white-space:nowrap"><?= date('M d',strtotime($ap['start_date'])) ?> – <?= date('M d, Y',strtotime($ap['end_date'])) ?><div style="color:var(--muted)"><?= $ap['total_hours'] ?> hrs total</div></td>
        <td><span class="<?= $scmS[$ap['status']]??'bkd' ?>"><?= $scmL[$ap['status']]??$ap['status'] ?></span></td>
        <td style="display:flex;gap:6px;flex-wrap:wrap">
            <button class="bds" style="font-size:10px" onclick="openSessionModal(<?= $ap['appointment_id'] ?>,<?= $ap['student_id'] ?>,'<?= addslashes($ap['sname']) ?>','<?= $isTDC?'TDC':'PDC' ?>','<?= date('M d',strtotime($ap['start_date'])) ?>','<?= date('M d, Y',strtotime($ap['end_date'])) ?>',<?= $ap['total_hours'] ?>,<?php if($isTDC): $rem=max(1,min(15,15-(float)($ap['tdc_prior_hours']??0))); echo $rem; else: echo $ap['total_hours']; endif; ?>,<?= stripos($ap['course_type'],'Both')!==false?'true':'false' ?>)">
                <i class="fa-solid fa-plus"></i> Log Session
            </button>
            <button class="ber" style="font-size:10px" onclick="openCancelModal(<?= $ap['appointment_id'] ?>,'<?= addslashes($ap['sname']) ?>')">
                <i class="fa-solid fa-xmark me-1"></i>Cancel
            </button>
        </td>
    </tr>
    <?php endwhile; endif; ?>
    </tbody></table>
    </div>
</div>

<!-- ── ONGOING (FUTURE) APPOINTMENTS ───────────────────────────────── -->
<div style="font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:12px;padding-bottom:6px;border-bottom:1px solid var(--border)">
    <i class="fa-solid fa-circle-dot me-1" style="color:var(--green)"></i>Ongoing Appointments <span style="font-weight:400;text-transform:none;letter-spacing:0">(upcoming — session log opens on the package's last day)</span>
</div>
<?php $ongoingAppts->data_seek(0); if($ongoingAppts->num_rows===0): ?>
<div class="panel" style="margin-bottom:20px"><div style="padding:24px;text-align:center;color:var(--muted)">No upcoming appointments.</div></div>
<?php else: ?>
<div class="panel" style="margin-bottom:20px">
    <div style="padding:10px 16px;border-bottom:1px solid var(--border);display:flex;gap:8px;align-items:end;flex-wrap:wrap">
        <form method="POST" onsubmit="return confirm('Cancel ALL appointments starting on this date?')" style="display:flex;gap:8px;align-items:end">
            <input type="hidden" name="action" value="cancel_all_appointments">
            <div><label class="form-label" style="font-size:10px">Cancel All Starting On</label><input type="date" name="target_date" class="di" required style="width:150px"></div>
            <div><label class="form-label" style="font-size:10px">Reason</label><input type="text" name="cancel_reason" class="di" placeholder="Reason (shown to students)" style="width:220px"></div>
            <button type="submit" class="ber" style="font-size:11px;height:32px"><i class="fa-solid fa-ban"></i> Cancel All</button>
        </form>
    </div>
    <div class="tbl-wrap">
    <table class="tbl"><thead><tr><th>Student</th><th>Course</th><th>Package Dates</th><th>Status</th><th>Actions</th></tr></thead><tbody>
    <?php while($ap=$ongoingAppts->fetch_assoc()):
        $tc=$cColors[$ap['course_type']]??'tag-gray';
    ?>
    <tr>
        <td><?= htmlspecialchars($ap['sname']) ?></td>
        <td><span class="tag <?= $tc ?>"><?= htmlspecialchars($ap['course_type']) ?></span></td>
        <td style="font-size:11px;white-space:nowrap"><?= date('M d',strtotime($ap['start_date'])) ?> – <?= date('M d, Y',strtotime($ap['end_date'])) ?><div style="color:var(--muted)"><?= $ap['total_hours'] ?> hrs total</div></td>
        <td><span class="<?= $scmS[$ap['status']]??'bkd' ?>"><?= $scmL[$ap['status']]??$ap['status'] ?></span></td>
        <td>
            <button class="ber" style="font-size:10px" onclick="openCancelModal(<?= $ap['appointment_id'] ?>,'<?= addslashes($ap['sname']) ?>')">
                <i class="fa-solid fa-xmark me-1"></i>Cancel
            </button>
        </td>
    </tr>
    <?php endwhile; ?>
    </tbody></table>
    </div>
</div>
<?php endif; ?>

<!-- ── PENDING SESSION LOGS ─────────────────────────────────────── -->
<?php $pendingSessionLogs->data_seek(0); if($pendingSessionLogs->num_rows>0): ?>
<div style="font-size:12px;font-weight:700;color:var(--red);text-transform:uppercase;letter-spacing:.06em;margin-bottom:12px;padding-bottom:6px;border-bottom:1px solid var(--border)">
    <i class="fa-solid fa-triangle-exclamation me-1"></i>Pending Session Logs <span style="font-weight:400;text-transform:none;letter-spacing:0;color:var(--muted)">(package end date has passed with no session logged)</span>
</div>
<div class="panel" style="margin-bottom:20px;border-color:rgba(229,85,85,.3)">
    <div class="tbl-wrap">
    <table class="tbl"><thead><tr><th>Student</th><th>Course</th><th>Package Dates</th><th>Status</th><th>Actions</th></tr></thead><tbody>
    <?php while($ap=$pendingSessionLogs->fetch_assoc()):
        $tc=$cColors[$ap['course_type']]??'tag-gray';
        $isTDC=stripos($ap['course_type'],'TDC')===0;
    ?>
    <tr>
        <td><strong><?= htmlspecialchars($ap['sname']) ?></strong></td>
        <td><span class="tag <?= $tc ?>"><?= htmlspecialchars($ap['course_type']) ?></span></td>
        <td style="white-space:nowrap;color:var(--red);font-size:11px"><?= date('M d',strtotime($ap['start_date'])) ?> – <?= date('M d, Y',strtotime($ap['end_date'])) ?></td>
        <td><span class="bkr">Overdue</span></td>
        <td style="display:flex;gap:6px;flex-wrap:wrap">
            <button class="be" style="font-size:10px" onclick="openSessionModal(<?= $ap['appointment_id'] ?>,<?= $ap['student_id'] ?>,'<?= addslashes($ap['sname']) ?>','<?= $isTDC?'TDC':'PDC' ?>','<?= date('M d',strtotime($ap['start_date'])) ?>','<?= date('M d, Y',strtotime($ap['end_date'])) ?>',<?= $ap['total_hours'] ?>,<?php if($isTDC): $rem=max(1,min(15,15-(float)($ap['tdc_prior_hours']??0))); echo $rem; else: echo $ap['total_hours']; endif; ?>,<?= stripos($ap['course_type'],'Both')!==false?'true':'false' ?>)">
                <i class="fa-solid fa-plus"></i> Log Session
            </button>
            <button class="ber" style="font-size:10px" onclick="openNoShowModal(<?= $ap['appointment_id'] ?>,'<?= addslashes($ap['sname']) ?>','<?= $ap['end_date'] ?>')">
                <i class="fa-solid fa-user-slash"></i> No Show
            </button>
        </td>
    </tr>
    <?php endwhile; ?>
    </tbody></table>
    </div>
</div>
<?php endif; ?>

<!-- ── RECENT ───────────────────────────────────────────────────── -->
<div style="font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin:20px 0 12px;padding-bottom:6px;border-bottom:1px solid var(--border)">
    <i class="fa-solid fa-clock-rotate-left me-1"></i>Recent Appointments
</div>
<div class="panel">
    <div class="tbl-wrap">
    <table class="tbl"><thead><tr><th>Student</th><th>Course</th><th>Start Date</th><th>Progress</th><th>TDC Grade</th><th>Status</th><th>Actions</th></tr></thead><tbody>
    <?php $recentAppts->data_seek(0); if($recentAppts->num_rows===0): ?>
    <tr><td colspan="8" style="color:var(--muted);text-align:center;padding:24px">No past appointments yet.</td></tr>
    <?php else: $_ri=0; while($ap=$recentAppts->fetch_assoc()): $_ri++;
        $tc=$cColors[$ap['course_type']]??'tag-gray';
        $hoursDone=(float)($ap['hours_done']??0);
        $pct=min(100,round($hoursDone/$ap['total_hours']*100));
        $isTDC=stripos($ap['course_type'],'TDC')===0;
    ?>
    <tr<?= $_ri>5?' class="pg-recentAppts-more" style="display:none"':'' ?>>
        <td><?= htmlspecialchars($ap['sname']) ?></td>
        <td><span class="tag <?= $tc ?>"><?= htmlspecialchars($ap['course_type']) ?></span></td>
        <td style="white-space:nowrap"><?= date('M d, Y',strtotime($ap['start_date'])) ?></td>
        <td style="min-width:100px">
            <div class="prog-bar"><div class="prog-fill" style="width:<?= $pct ?>%"></div></div>
            <div style="font-size:10px;color:var(--muted);margin-top:2px"><?= $hoursDone ?>/<?= $ap['total_hours'] ?> hrs</div>
        </td>
        <td><?php if($isTDC&&isset($ap['tdc_overall_grade'])&&$ap['tdc_overall_grade']): $gc=$ap['tdc_overall_grade']>=80?'var(--green)':($ap['tdc_overall_grade']>=60?'var(--amber)':'var(--red)'); ?>
            <strong style="color:<?= $gc ?>"><?= $ap['tdc_overall_grade'] ?>/100</strong>
        <?php elseif($isTDC): ?>—<?php else: ?><span style="color:var(--muted);font-size:11px">N/A</span><?php endif; ?></td>
        <td>
            <span class="<?= $scmS[$ap['status']]??'bkd' ?>"><?= $scmL[$ap['status']]??$ap['status'] ?></span>
            <?php if($ap['status']==='cancelled' && !empty($ap['instructor_note'])): ?>
            <div style="font-size:10px;color:var(--muted);margin-top:2px"><i class="fa-solid fa-circle-info me-1"></i><?= htmlspecialchars(substr($ap['instructor_note'],0,80)) ?></div>
            <?php endif; ?>
        </td>
        <td>—</td>
    </tr>
    <?php endwhile; if($recentAppts->num_rows>5): ?>
    <tr id="pg-recentAppts-btn"><td colspan="7" style="text-align:center;padding:10px"><button type="button" class="bds" style="font-size:11px" onclick="showMoreRows('recentAppts')">Show All (<?= $recentAppts->num_rows ?>)</button></td></tr>
    <?php endif; endif; ?>
    </tbody></table>
    </div><!-- /tbl-wrap -->
</div>
</div>

<!-- ══ MY CALENDAR ════════════════════════════════════════════ -->
<div id="section-calendar" class="section <?= $activeSection==='calendar'?'active':'' ?>">
<script>window._myCalMap = <?= json_encode($calMap) ?>;</script>
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-calendar-days"></i> My Calendar</div></div>
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

<!-- ══ MY STUDENTS ════════════════════════════════════════════ -->
<div id="section-students" class="section <?= $activeSection==='students'?'active':'' ?>">

<!-- Top Students -->
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-trophy" style="color:var(--amber)"></i> Top 5 Students This Month</div></div>
    <div class="tbl-wrap">
    <table class="tbl"><thead><tr><th>#</th><th>Student</th><th>Avg Grade</th><th>Sessions</th></tr></thead><tbody>
    <?php $topStudents->data_seek(0); $rank=1; if($topStudents->num_rows===0): ?>
    <tr><td colspan="4" style="color:var(--muted);text-align:center;padding:16px">No grades this month.</td></tr>
    <?php else: while($ts=$topStudents->fetch_assoc()): $medals=['🥇','🥈','🥉','4️⃣','5️⃣']; $gc=$ts['avg_grade']>=80?'var(--green)':($ts['avg_grade']>=60?'var(--amber)':'var(--red)'); ?>
    <tr><td><?= $medals[$rank-1]??$rank ?></td><td><?= htmlspecialchars($ts['fullname']) ?></td><td style="color:<?= $gc ?>;font-weight:600"><?= $ts['avg_grade'] ?>/100</td><td><?= $ts['sess_cnt'] ?></td></tr>
    <?php $rank++; endwhile; endif; ?>
    </tbody></table>
    </div><!-- /tbl-wrap -->
</div>

<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-users"></i> My Students</div></div>
    <div class="tbl-wrap">
    <table class="tbl"><thead><tr><th>Student</th><th>Email</th><th>Latest Course</th><th>Status</th><th>Sessions</th><th>Avg Grade</th><th>Action</th></tr></thead><tbody>
    <?php $myStudents->data_seek(0); if($myStudents->num_rows===0): ?>
    <tr><td colspan="7" style="color:var(--muted);text-align:center;padding:24px">No students yet.</td></tr>
    <?php else: while($st=$myStudents->fetch_assoc()): $tc=$cColors[$st['latest_course']??'']??'tag-gray';
        $avgG=q($conn,"SELECT ROUND(AVG(grade),1) AS ag FROM sessions WHERE student_id={$st['student_id']} AND instructor_id=$instructor_id AND grade IS NOT NULL")->fetch_assoc()['ag'];
        $gc=$avgG?($avgG>=80?'var(--green)':($avgG>=60?'var(--amber)':'var(--red)')):''; ?>
    <tr>
        <td><?= htmlspecialchars($st['fullname']) ?></td>
        <td style="font-size:11px;color:var(--muted)"><?= htmlspecialchars($st['email']) ?></td>
        <td><?php if($st['latest_course']): ?><span class="tag <?= $tc ?>"><?= htmlspecialchars($st['latest_course']) ?></span><?php else: ?>—<?php endif; ?></td>
        <td><span class="<?= $scmS[$st['latest_status']??'']??'bkd' ?>"><?= $scmL[$st['latest_status']??'']??'—' ?></span></td>
        <td><?php $sessCnt=(int)q($conn,"SELECT COUNT(*) AS c FROM sessions WHERE student_id={$st['student_id']} AND instructor_id=$instructor_id")->fetch_assoc()['c']; ?><?= $sessCnt ?> session<?= $sessCnt!=1?'s':'' ?></td>
        <td><?= $avgG?'<strong style="color:'.$gc.'">'.$avgG.'/100</strong>':'—' ?></td>
        <td><a href="<?= $selfFile ?>?section=student_profile&view_student=<?= $st['student_id'] ?>" class="bds" style="font-size:11px;white-space:nowrap"><i class="fa-solid fa-eye me-1"></i>View Profile</a></td>
    </tr>
    <?php endwhile; endif; ?>
    </tbody></table>
    </div><!-- /tbl-wrap -->
</div>
</div>

<!-- ══ STUDENT PROFILE ══════════════════════════════════════ -->
<div id="section-student_profile" class="section <?= $activeSection==='student_profile'?'active':'' ?>">
<?php if($viewStudentId>0):
    $vStu=q($conn,"SELECT * FROM students WHERE student_id=$viewStudentId")->fetch_assoc();
    $enrolled=(int)q($conn,"SELECT COUNT(*) AS c FROM appointments WHERE student_id=$viewStudentId AND instructor_id=$instructor_id")->fetch_assoc()['c'];
    if($vStu&&$enrolled>0):
    $stuAppts=q($conn,"SELECT a.*,ic.course_type,
        (SELECT SUM(hours_covered) FROM sessions WHERE appointment_id=a.appointment_id AND status='completed') AS hours_done,
        (SELECT COUNT(*) FROM sessions WHERE appointment_id=a.appointment_id AND status='completed') AS sess_count,
        (SELECT ROUND(AVG(grade),1) FROM sessions WHERE appointment_id=a.appointment_id AND grade IS NOT NULL) AS avg_grade
        FROM appointments a
        JOIN instructor_courses ic ON ic.course_id=a.course_id
        WHERE a.student_id=$viewStudentId AND a.instructor_id=$instructor_id
        ORDER BY a.requested_at DESC");
    $stuSess=q($conn,"SELECT sess.*,ic.course_type FROM sessions sess
        JOIN appointments a ON a.appointment_id=sess.appointment_id
        JOIN instructor_courses ic ON ic.course_id=a.course_id
        WHERE sess.student_id=$viewStudentId AND sess.instructor_id=$instructor_id
        ORDER BY sess.session_date DESC");
    $stuRating=q($conn,"SELECT * FROM instructor_ratings WHERE student_id=$viewStudentId AND instructor_id=$instructor_id ORDER BY rated_at DESC LIMIT 1")->fetch_assoc();
?>
<div style="margin-bottom:12px">
    <a href="<?= $selfFile ?>?section=students" class="bds" style="font-size:12px"><i class="fa-solid fa-arrow-left me-1"></i> Back to My Students</a>
</div>

<?php

$spIdPath_i='../assets/uploads/student_ids/';
$totalSess=(int)q($conn,"SELECT COUNT(*) AS c FROM sessions WHERE student_id=$viewStudentId AND instructor_id=$instructor_id AND status='completed'")->fetch_assoc()['c'];
$totalHrs=(float)q($conn,"SELECT COALESCE(SUM(hours_covered),0) AS h FROM sessions WHERE student_id=$viewStudentId AND instructor_id=$instructor_id AND status='completed'")->fetch_assoc()['h'];
$overallAvg=q($conn,"SELECT ROUND(AVG(grade),1) AS ag FROM sessions WHERE student_id=$viewStudentId AND instructor_id=$instructor_id AND grade IS NOT NULL")->fetch_assoc()['ag'];
$gc=$overallAvg?($overallAvg>=80?'var(--green)':($overallAvg>=60?'var(--amber)':'var(--red)')):'var(--muted)';
?>
<div class="panel" style="margin-bottom:16px">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-user-graduate me-1"></i><?= htmlspecialchars($vStu['fullname']) ?></div></div>
    <div style="padding:16px;display:flex;gap:20px;align-items:flex-start;flex-wrap:wrap">
        <!-- Avatar -->
        <div style="width:64px;height:64px;border-radius:50%;background:var(--accent);display:flex;align-items:center;justify-content:center;font-size:26px;font-weight:700;color:#fff;flex-shrink:0"><?= strtoupper(substr($vStu['fullname'],0,1)) ?></div>
        <!-- Info grid -->
        <div style="flex:1;min-width:260px;display:grid;grid-template-columns:1fr 1fr;gap:12px">
            <div><span style="font-size:11px;color:var(--muted)">Email</span><div style="font-size:13px"><?= htmlspecialchars($vStu['email']) ?></div></div>
            <div><span style="font-size:11px;color:var(--muted)">Mobile</span><div style="font-size:13px"><?= !empty($vStu['mobile'])?htmlspecialchars($vStu['mobile']):'—' ?></div></div>
            <div><span style="font-size:11px;color:var(--muted)">Gender</span><div style="font-size:13px"><?php if(!empty($vStu['gender'])): ?><i class="fa-solid fa-<?= $vStu['gender']==='Male'?'mars':($vStu['gender']==='Female'?'venus':'genderless') ?> me-1" style="color:<?= $vStu['gender']==='Male'?'#60a5fa':'#f472b6' ?>"></i><?= htmlspecialchars($vStu['gender']) ?><?php else: ?>—<?php endif; ?></div></div>
            <div><span style="font-size:11px;color:var(--muted)">Verified</span><div><?= $vStu['is_verified']?'<span class="bkg">Yes</span>':'<span class="bkp">No</span>' ?></div></div>
            <div><span style="font-size:11px;color:var(--muted)">Address</span><div style="font-size:12px"><?= !empty($vStu['address'])?htmlspecialchars($vStu['address']):'—' ?></div></div>
            <div><span style="font-size:11px;color:var(--muted)">Joined</span><div style="font-size:13px"><?= date('F d, Y',strtotime($vStu['created_at'])) ?></div></div>
            <div><span style="font-size:11px;color:var(--muted)">Facebook</span><div style="font-size:12px"><?php if(!empty($vStu['fb_link'])): ?><a href="<?= htmlspecialchars($vStu['fb_link']) ?>" target="_blank" style="color:#60a5fa;text-decoration:none"><i class="fa-brands fa-facebook me-1"></i>View Profile</a><?php else: ?>—<?php endif; ?></div></div>

        </div>
        <!-- ID Photo -->
        <div style="flex-shrink:0;text-align:center">
            <div style="font-size:10px;color:var(--muted);margin-bottom:8px"><i class="fa-solid fa-id-card me-1"></i>National ID / Valid ID</div>
            <?php if(!empty($vStu['id_photo'])&&file_exists($spIdPath_i.$vStu['id_photo'])): ?>
            <img src="<?= $spIdPath_i.htmlspecialchars($vStu['id_photo']) ?>" style="max-width:200px;max-height:120px;border-radius:8px;border:2px solid var(--border);object-fit:cover;display:block;cursor:pointer;transition:.15s" onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='var(--border)'">
            <div style="display:flex;gap:6px;margin-top:6px;justify-content:center">
                <a href="<?= 'view_image.php?type=student_ids&file='.urlencode($vStu['id_photo']).'&back='.urlencode($_SERVER['REQUEST_URI']) ?>" class="bds" style="font-size:11px"><i class="fa-solid fa-eye me-1"></i>View</a>
            </div>
            <?php else: ?>
            <div style="width:160px;height:100px;border:2px dashed var(--border);border-radius:8px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px">
                <i class="fa-solid fa-id-card" style="font-size:22px;color:var(--border)"></i>
                <span style="font-size:11px;color:var(--muted)">No ID uploaded</span>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- Quick stats row -->
    <div style="padding:12px 16px;border-top:1px solid var(--border);display:flex;gap:12px;flex-wrap:wrap">
        <div style="background:var(--bg3);border-radius:10px;padding:10px 18px;text-align:center;min-width:80px"><div style="font-size:20px;font-weight:700;color:var(--accent)"><?= $totalSess ?></div><div style="font-size:10px;color:var(--muted)">Sessions</div></div>
        <div style="background:var(--bg3);border-radius:10px;padding:10px 18px;text-align:center;min-width:80px"><div style="font-size:20px;font-weight:700;color:var(--green)"><?= $totalHrs ?></div><div style="font-size:10px;color:var(--muted)">Hours</div></div>
        <div style="background:var(--bg3);border-radius:10px;padding:10px 18px;text-align:center;min-width:80px"><div style="font-size:20px;font-weight:700;color:<?= $gc ?>"><?= $overallAvg??'—' ?></div><div style="font-size:10px;color:var(--muted)">Avg Grade</div></div>
        <?php if($stuRating): ?><div style="background:var(--bg3);border-radius:10px;padding:10px 18px;text-align:center;min-width:80px"><div style="font-size:20px;font-weight:700;color:var(--amber)"><?= $stuRating['rating'] ?>★</div><div style="font-size:10px;color:var(--muted)">Their Rating</div></div><?php endif; ?>
    </div>
    <?php if($stuRating&&!empty($stuRating['comment'])): ?>
    <div style="padding:0 16px 16px">
        <div style="background:var(--bg3);border-radius:8px;padding:10px 14px;font-size:12px;color:var(--muted);border-left:3px solid var(--amber)">
            <i class="fa-solid fa-quote-left me-1" style="color:var(--amber)"></i><?= htmlspecialchars($stuRating['comment']) ?>
        </div>
    </div>
    <?php endif; ?>
</div>


<div class="panel" style="margin-bottom:16px">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-calendar-check"></i> Appointments with You</div></div>
    <div class="tbl-wrap">
    <table class="tbl"><thead><tr><th>Course</th><th>Start Date</th><th>Status</th><th>Sessions</th><th>Hours</th><th>Avg Grade</th></tr></thead><tbody>
    <?php if($stuAppts->num_rows===0): ?>
    <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:16px">No appointments found.</td></tr>
    <?php else: while($sa=$stuAppts->fetch_assoc()): $tc=$cColors[$sa['course_type']]??'tag-gray'; ?>
    <tr>
        <td><span class="tag <?= $tc ?>"><?= htmlspecialchars($sa['course_type']) ?></span></td>
        <td style="font-size:12px"><?= date('M d, Y',strtotime($sa['start_date'])) ?></td>
        <td><span class="<?= $scmS[$sa['status']]??'bkd' ?>"><?= $scmL[$sa['status']]??'—' ?></span></td>
        <td style="font-size:12px"><?= (int)$sa['sess_count'] ?> session<?= $sa['sess_count']!=1?'s':'' ?></td>
        <td style="font-size:12px"><?= $sa['hours_done']??0 ?> hrs</td>
        <td style="font-size:12px"><?php if($sa['avg_grade']): $gc2=$sa['avg_grade']>=80?'var(--green)':($sa['avg_grade']>=60?'var(--amber)':'var(--red)'); ?><strong style="color:<?= $gc2 ?>"><?= $sa['avg_grade'] ?>/100</strong><?php else: ?>—<?php endif; ?></td>
    </tr>
    <?php endwhile; endif; ?>
    </tbody></table>
    </div><!-- /tbl-wrap -->
</div>

<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-list-check"></i> Session History</div></div>
    <div class="tbl-wrap">
    <table class="tbl"><thead><tr><th>Date</th><th>Course</th><th>Session #</th><th>Hours</th><th>Grade</th><th>Rating</th><th>Notes</th></tr></thead><tbody>
    <?php if($stuSess->num_rows===0): ?>
    <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:16px">No sessions recorded yet.</td></tr>
    <?php else: while($ss=$stuSess->fetch_assoc()): $tc=$cColors[$ss['course_type']]??'tag-gray'; $ratingStars=''; for($i=1;$i<=5;$i++) $ratingStars.=$i<=($ss['performance_rating']??0)?'★':'☆'; ?>
    <tr>
        <td style="font-size:12px;white-space:nowrap"><?= date('M d, Y',strtotime($ss['session_date'])) ?></td>
        <td><span class="tag <?= $tc ?>" style="font-size:10px"><?= htmlspecialchars($ss['course_type']) ?></span></td>
        <td style="font-size:12px">Day <?= $ss['session_number'] ?></td>
        <td style="font-size:12px"><?= $ss['hours_covered'] ?> hrs</td>
        <td style="font-size:12px"><?php if($ss['grade']): $gc3=$ss['grade']>=80?'var(--green)':($ss['grade']>=60?'var(--amber)':'var(--red)'); ?><strong style="color:<?= $gc3 ?>"><?= $ss['grade'] ?>/100</strong><?php else: ?>—<?php endif; ?></td>
        <td style="color:var(--amber);font-size:12px"><?= $ss['performance_rating']?$ratingStars:'—' ?></td>
        <td style="font-size:11px;color:var(--muted);max-width:200px"><?= htmlspecialchars($ss['performance_notes']??'—') ?></td>
    </tr>
    <?php endwhile; endif; ?>
    </tbody></table>
    </div><!-- /tbl-wrap -->
</div>

<?php else: ?>
<div style="text-align:center;padding:60px;color:var(--muted)">
    <i class="fa-solid fa-user-slash" style="font-size:36px;margin-bottom:12px;display:block;opacity:.3"></i>
    <div>Student not found or not enrolled with you.</div>
    <a href="<?= $selfFile ?>?section=students" class="be" style="margin-top:16px;display:inline-block">Back to My Students</a>
</div>
<?php endif; endif; ?>
</div>

<!-- ══ PERFORMANCE ════════════════════════════════════════════ -->
<div id="section-performance" class="section <?= $activeSection==='performance'?'active':'' ?>">

<?php $unratedSessions->data_seek(0); if($unratedSessions->num_rows>0): ?>
<div class="panel">
    <div class="panel-head"><div class="panel-title" style="color:var(--amber)"><i class="fa-solid fa-star-half-stroke"></i> Sessions Needing Grade / Rating</div></div>
    <div class="tbl-wrap">
    <table class="tbl"><thead><tr><th>Student</th><th>Course</th><th>Session Date</th><th>Session #</th><th>Hours</th><th>Action</th></tr></thead><tbody>
    <?php $unratedSessions->data_seek(0); while($us=$unratedSessions->fetch_assoc()): $tc=$cColors[$us['course_type']]??'tag-gray'; ?>
    <tr>
        <td><?= htmlspecialchars($us['sname']) ?></td>
        <td><span class="tag <?= $tc ?>"><?= htmlspecialchars($us['course_type']) ?></span></td>
        <td><?= date('M d, Y',strtotime($us['session_date'])) ?></td>
        <td>Day <?= $us['session_number']??'?' ?></td>
        <td><?= $us['hours_covered'] ?> hrs</td>
        <td><button class="be" style="font-size:11px" onclick="openPerfModal(<?= $us['session_id'] ?>,'<?= addslashes($us['sname']) ?>','<?= date('M d, Y',strtotime($us['session_date'])) ?>',<?= $us['session_number']??1 ?>,<?= stripos($us['course_type'],'TDC')===0?'true':'false' ?>,<?= $us['grade']??'null' ?>,<?= $us['performance_rating']??'null' ?>,'<?= addslashes($us['improvements']??'') ?>','<?= addslashes($us['performance_notes']??'') ?>')">
            <i class="fa-solid fa-pencil"></i> Grade & Rate
        </button></td>
    </tr>
    <?php endwhile; ?>
    </tbody></table>
    </div><!-- /tbl-wrap -->
</div>
<?php endif; ?>

<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-list-check"></i> All Session Records</div></div>
    <div style="padding:12px 16px">
    <?php
    $allSess=q($conn,"SELECT sess.*,ic.course_type,s.fullname AS sname FROM sessions sess JOIN appointments a ON a.appointment_id=sess.appointment_id JOIN instructor_courses ic ON ic.course_id=a.course_id JOIN students s ON s.student_id=sess.student_id WHERE sess.instructor_id=$instructor_id ORDER BY sess.session_date DESC,sess.session_id DESC");
    if($allSess->num_rows===0): ?>
    <div style="color:var(--muted);text-align:center;padding:24px">No sessions yet.</div>
    <?php else:
        $sessByDate=[];
        while($ss=$allSess->fetch_assoc()) $sessByDate[$ss['session_date']][]=$ss;
        $dateKeys=array_keys($sessByDate);
        $totalDates=count($dateKeys);
        $_di=0;
        foreach($dateKeys as $dKey): $_di++;
            $rows=$sessByDate[$dKey];
    ?>
    <div<?= $_di>5?' class="pg-allSess-more" style="display:none"':'' ?> style="margin-bottom:16px">
        <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px"><?= date('F d, Y (l)',strtotime($dKey)) ?></div>
        <div class="tbl-wrap">
        <table class="tbl"><thead><tr><th>Student</th><th>Course</th><th>Session #</th><th>Hours</th><th>Grade (TDC)</th><th>Rating</th><th>Improvements</th><th></th></tr></thead><tbody>
        <?php foreach($rows as $ss): $tc=$cColors[$ss['course_type']]??'tag-gray'; $gc=$ss['grade']?($ss['grade']>=80?'var(--green)':($ss['grade']>=60?'var(--amber)':'var(--red)')):''; $isNoShow=($ss['status']??'')==='no_show'; $ssIsTDC=stripos($ss['course_type'],'TDC')===0; ?>
        <tr<?= $isNoShow?' style="background:rgba(229,85,85,.06)"':'' ?>>
            <td><?= htmlspecialchars($ss['sname']) ?></td>
            <td><span class="tag <?= $tc ?>"><?= htmlspecialchars($ss['course_type']) ?></span></td>
            <td>Day <?= $ss['session_number']??'—' ?></td>
            <td><?php if($isNoShow): ?><span class="bkr" style="font-size:10px"><i class="fa-solid fa-user-slash me-1"></i>No Show</span><?php else: ?><?= $ss['hours_covered'] ?> hrs<?php endif; ?></td>
            <td><?= (!$isNoShow&&$ss['grade'])?'<strong style="color:'.$gc.'">'.$ss['grade'].'/100</strong>':'<span style="color:var(--muted)">—</span>' ?></td>
            <td><?php if($ssIsTDC): ?><span style="color:var(--muted)">—</span><?php elseif($ss['performance_rating']): ?><span style="color:var(--amber)"><?= str_repeat('★',$ss['performance_rating']).str_repeat('☆',5-$ss['performance_rating']) ?></span><?php else: ?><span class="bkp">Not rated</span><?php endif; ?></td>
            <td style="font-size:11px;color:var(--muted);max-width:150px"><?= $ss['improvements']?htmlspecialchars(substr($ss['improvements'],0,60)).'…':'—' ?></td>
            <td><?php if(!$isNoShow): ?><button class="bds" style="font-size:10px" onclick="openPerfModal(<?= $ss['session_id'] ?>,'<?= addslashes($ss['sname']) ?>','<?= date('M d, Y',strtotime($ss['session_date'])) ?>',<?= $ss['session_number']??1 ?>,<?= $ssIsTDC?'true':'false' ?>,<?= $ss['grade']??'null' ?>,<?= $ss['performance_rating']??'null' ?>,'<?= addslashes($ss['improvements']??'') ?>','<?= addslashes($ss['performance_notes']??'') ?>')"><i class="fa-solid fa-pen"></i></button><?php endif; ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody></table>
        </div>
    </div>
    <?php endforeach;
    if($totalDates>5): ?>
    <div id="pg-allSess-btn" style="text-align:center;padding:10px"><button type="button" class="bds" style="font-size:11px" onclick="showMoreRows('allSess')">Show All Dates (<?= $totalDates ?>)</button></div>
    <?php endif; endif; ?>
    </div>
</div>

<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-medal"></i> Student Reviews of You</div></div>
    <?php $myRatings=q($conn,"SELECT ir.*,s.fullname AS sname,ic.course_type FROM instructor_ratings ir JOIN students s ON s.student_id=ir.student_id JOIN appointments a ON a.appointment_id=ir.appointment_id JOIN instructor_courses ic ON ic.course_id=a.course_id WHERE ir.instructor_id=$instructor_id ORDER BY ir.rated_at DESC");
    if($myRatings->num_rows===0): ?><div style="padding:20px;text-align:center;color:var(--muted);font-size:12px">No reviews yet.</div>
    <?php else: while($rv=$myRatings->fetch_assoc()): ?>
    <div style="padding:12px 16px;border-bottom:1px solid var(--border)">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
            <?php if(!empty($rv['is_anonymous'])): ?>
            <?php if(!empty($rv['is_anonymous'])): ?>
            <div style="font-size:12px;font-weight:600;color:var(--muted)"><i class="fa-solid fa-user-secret me-1"></i>Anonymous</div>
            <?php else: ?>
            <div style="font-size:12px;font-weight:600"><?= htmlspecialchars($rv['sname']) ?></div>
            <?php endif; ?>
            <?php else: ?>
            <div style="font-size:12px;font-weight:600"><?= htmlspecialchars($rv['sname']) ?></div>
            <?php endif; ?>
            <span class="tag <?= $cColors[$rv['course_type']]??'tag-gray' ?>"><?= htmlspecialchars($rv['course_type']) ?></span>
            <div style="color:var(--amber);font-size:12px;margin-left:auto"><?= stars($rv['rating']) ?></div>
            <div style="font-size:10px;color:var(--muted)"><?= date('F Y',strtotime($rv['rated_at'])) ?></div>
        </div>
        <?php if($rv['comment']): ?><div style="font-size:12px;color:var(--muted)"><?= nl2br(htmlspecialchars($rv['comment'])) ?></div><?php endif; ?>
    </div>
    <?php endwhile; endif; ?>
</div>
</div>

<!-- ══ MY COURSES ═════════════════════════════════════════════ -->
<div id="section-courses" class="section <?= $activeSection==='courses'?'active':'' ?>">
<?php $editCourseId=(int)($_GET['edit_course']??0); ?>
<div class="panel mb-3">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-plus"></i> Add New Course</div></div>
    <div style="padding:16px">
    <form method="POST" class="form-grid" style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:10px;align-items:end">
        <input type="hidden" name="action" value="add_course">
        <div><label class="form-label">Course Type</label>
            <select name="course_type" class="di" required>
                <option value="">— Select —</option>
                <?php foreach($courseTypeOpts as $opt): ?><option value="<?= $opt ?>"><?= $opt ?></option><?php endforeach; ?>
            </select></div>
        <div><label class="form-label">Package Price (₱)</label>
            <input type="number" name="price_per_hr" class="di" min="0" step="0.01" required placeholder="e.g. 2010.00">
            <div style="font-size:10px;color:var(--muted);margin-top:3px">Total price for the whole package, not per hour.</div></div>
        <div><label class="form-label">Student Limit per Date</label>
            <input type="number" name="student_limit" class="di" min="1" max="50" placeholder="Leave blank = unlimited"></div>
        <button type="submit" class="be" style="height:36px"><i class="fa-solid fa-plus"></i> Add</button>
        <div style="grid-column:1/-1"><label class="form-label">Description (optional)</label>
            <input type="text" name="description" class="di" placeholder="Short description"></div>
        <div style="grid-column:1/-1"><label class="form-label">Requirements (optional)</label>
            <input type="text" name="requirements" class="di" placeholder="e.g. Valid ID, closed shoes, own helmet…"></div>
    </form>
    </div>
</div>
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-book-open"></i> My Courses</div></div>
    <div class="tbl-wrap">
    <table class="tbl"><thead><tr><th>Course Type</th><th>Price</th><th>Package</th><th>Student Limit</th><th>Description</th><th>Requirements</th><th>Status</th><th></th></tr></thead><tbody>
    <?php $myCourses->data_seek(0); if($myCourses->num_rows===0): ?>
    <tr><td colspan="8" style="color:var(--muted);text-align:center;padding:24px">No courses yet. Add one above.</td></tr>
    <?php else: while($c=$myCourses->fetch_assoc()): $tc=$cColors[$c['course_type']]??'tag-gray'; $isTDC=stripos($c['course_type'],'TDC')===0; $isCombo=stripos($c['course_type'],'Both')!==false;
        if($isTDC){$pkgHrs=15;$pkgDays=3;} elseif($isCombo){$pkgHrs=16;$pkgDays=3;} else {$pkgHrs=8;$pkgDays=2;}
    ?>
    <tr>
        <td><span class="tag <?= $tc ?>"><?= htmlspecialchars($c['course_type']) ?></span></td>
        <td>₱<?= number_format($c['price_per_hr'],2) ?></td>
        <td><strong><?= $pkgHrs ?> hrs</strong> / <?= $pkgDays ?> days</td>
        <td><?php if(!empty($c['student_limit'])): ?><span style="background:rgba(124,106,247,.15);color:var(--accent);padding:2px 10px;border-radius:20px;font-size:11px"><i class="fa-solid fa-users me-1"></i><?= $c['student_limit'] ?>/date</span><?php else: ?><span style="color:var(--muted);font-size:11px">Unlimited</span><?php endif; ?></td>
        <td style="font-size:11px;color:var(--muted)"><?= !empty($c['description'])?htmlspecialchars($c['description']):'—' ?></td>
        <td style="font-size:11px;color:var(--muted)"><?= !empty($c['requirements'])?htmlspecialchars($c['requirements']):'—' ?></td>
        <td><?= $c['is_active']?'<span class="bkg">Active</span>':'<span class="bkd">Inactive</span>' ?></td>
        <td style="display:flex;gap:4px;flex-wrap:wrap">
            <button class="bds" style="font-size:11px" onclick="openEditCourse(<?= $c['course_id'] ?>,'<?= addslashes(htmlspecialchars($c['description'])) ?>','<?= addslashes(htmlspecialchars($c['requirements']??'')) ?>',<?= $c['price_per_hr'] ?>,'<?= $c['student_limit']??'' ?>')"><i class="fa-solid fa-pen"></i> Edit</button>
            <form method="POST"><input type="hidden" name="action" value="toggle_course"><input type="hidden" name="course_id" value="<?= $c['course_id'] ?>">
            <button type="submit" class="bds" style="font-size:11px"><?= $c['is_active']?'Deactivate':'Activate' ?></button></form>
            <form method="POST" onsubmit="return confirm('Delete this course?')"><input type="hidden" name="action" value="delete_course"><input type="hidden" name="course_id" value="<?= $c['course_id'] ?>">
            <button type="submit" class="ber" style="font-size:11px">Delete</button></form>
        </td>
    </tr>
    <?php endwhile; endif; ?>
    </tbody></table>
    </div><!-- /tbl-wrap -->
</div>

<!-- Upcoming Schedule Slots -->
<?php
$scheduleSlots=q($conn,"SELECT cs.*,ic.course_type FROM course_schedules cs JOIN instructor_courses ic ON ic.course_id=cs.course_id WHERE cs.instructor_id=$instructor_id AND cs.schedule_date>=CURDATE() AND cs.booked_count>0 ORDER BY cs.schedule_date ASC LIMIT 20");
if($scheduleSlots->num_rows>0):
?>
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-calendar-days"></i> Upcoming Schedule Slots</div>
        <small style="color:var(--muted);font-size:10px">Dates with student bookings</small></div>
    <div class="tbl-wrap">
    <table class="tbl"><thead><tr><th>Date</th><th>Course</th><th>Booked</th><th>Limit</th><th>Slots Left</th></tr></thead><tbody>
    <?php while($sl=$scheduleSlots->fetch_assoc()):
        $limit=$sl['student_limit']??0;
        $booked=(int)$sl['booked_count'];
        $left=$limit>0?max(0,$limit-$booked):null;
        $pct=$limit>0?round($booked/$limit*100):0;
        $barColor=$pct>=100?'var(--red)':($pct>=75?'var(--amber)':'var(--green)');
        $tc=$cColors[$sl['course_type']]??'tag-gray';
    ?>
    <tr>
        <td><strong><?= date('M d, Y (D)',strtotime($sl['schedule_date'])) ?></strong></td>
        <td><span class="tag <?= $tc ?>"><?= htmlspecialchars($sl['course_type']) ?></span></td>
        <td>
            <div style="display:flex;align-items:center;gap:8px">
                <span style="font-weight:600"><?= $booked ?></span>
                <?php if($limit>0): ?>
                <div style="width:60px;height:6px;background:var(--border);border-radius:3px;overflow:hidden"><div style="height:100%;width:<?= $pct ?>%;background:<?= $barColor ?>;border-radius:3px"></div></div>
                <?php endif; ?>
            </div>
        </td>
        <td><?= $limit>0?$limit:'<span style="color:var(--muted)">Unlimited</span>' ?></td>
        <td><?php if($left===null): ?><span style="color:var(--muted)">∞</span>
            <?php elseif($left===0): ?><span class="bkr">FULL</span>
            <?php else: ?><span style="color:var(--green);font-weight:600"><?= $left ?> open</span>
            <?php endif; ?></td>
    </tr>
    <?php endwhile; ?></tbody></table>
    </div><!-- /tbl-wrap -->
</div>
<?php endif; ?>
</div>

<!-- ══ EDIT COURSE MODAL ═══════════════════════════════════════ -->
<div id="editCourseModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:200;align-items:center;justify-content:center;padding:16px">
<div style="background:var(--bg2);border:1px solid var(--border);border-radius:12px;width:440px;max-width:95vw;padding:24px">
    <div style="font-size:15px;font-weight:600;margin-bottom:16px"><i class="fa-solid fa-pen me-2" style="color:var(--accent)"></i>Edit Course</div>
    <form method="POST">
    <input type="hidden" name="action" value="update_course">
    <input type="hidden" name="course_id" id="ec_course_id">
    <div style="margin-bottom:12px"><label class="form-label">Package Price (₱) *</label>
        <input type="number" name="price_per_hr" id="ec_price" class="di" min="0" step="0.01" required></div>
    <div style="margin-bottom:12px"><label class="form-label">Student Limit per Date</label>
        <input type="number" name="student_limit" id="ec_limit" class="di" min="1" max="50" placeholder="Leave blank = unlimited">
        <div style="font-size:10px;color:var(--muted);margin-top:3px">Maximum students that can book this course on the same start date.</div></div>
    <div style="margin-bottom:12px"><label class="form-label">Description (optional)</label>
        <input type="text" name="description" id="ec_desc" class="di" placeholder="Short description"></div>
    <div style="margin-bottom:16px"><label class="form-label">Requirements (optional)</label>
        <input type="text" name="requirements" id="ec_requirements" class="di" placeholder="e.g. Valid ID, closed shoes, own helmet…"></div>
    <div style="display:flex;gap:8px;justify-content:flex-end">
        <button type="button" class="bds" onclick="closeEditCourse()">Cancel</button>
        <button type="submit" class="be"><i class="fa-solid fa-save me-1"></i>Save Changes</button>
    </div>
    </form>
</div></div>

<!-- ══ NOTIFICATIONS ══════════════════════════════════════════ -->
<div id="section-messages" class="section <?= $activeSection==='messages'?'active':'' ?>">
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-bell"></i> Notifications</div>
        <form method="POST"><input type="hidden" name="action" value="mark_read">
        <button type="submit" class="bds" style="font-size:11px">Mark all read</button></form>
    </div>
    <div style="padding:12px 16px;display:flex;flex-direction:column;gap:8px">
    <?php $notifs->data_seek(0); if($notifs->num_rows===0): ?>
    <p style="color:var(--muted);text-align:center;padding:20px">No notifications.</p>
    <?php else: while($n=$notifs->fetch_assoc()): ?>
    <div style="padding:10px 12px;background:<?= !$n['is_read']?'rgba(124,106,247,.06)':'var(--bg3)' ?>;border-radius:8px;border-left:3px solid <?= !$n['is_read']?'var(--accent)':'var(--border)' ?>">
        <div style="font-size:12px"><?= htmlspecialchars($n['message']) ?></div>
        <div style="font-size:10px;color:var(--muted);margin-top:3px"><?= date('M d, Y g:i A',strtotime($n['created_at'])) ?></div>
    </div>
    <?php endwhile; endif; ?>
    </div>
</div>
</div>

<!-- ══ CHAT / MESSAGING ════════════════════════════════════════ -->
<div id="section-chat" class="section <?= in_array($activeSection,['chat'])?'active':'' ?>">
<?php
$studentMap=[];
$myStudents->data_seek(0);
while($ms=$myStudents->fetch_assoc()){
    $sid=(int)$ms['student_id'];
    if(!isset($studentMap[$sid])) $studentMap[$sid]=$ms;
}
?>
<div style="display:flex;gap:16px;height:calc(100vh - 160px);min-height:400px">
<div style="width:240px;flex-shrink:0;background:var(--bg2);border:1px solid var(--border);border-radius:10px;overflow-y:auto">
    <div style="padding:12px 14px;border-bottom:1px solid var(--border);font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.06em">My Students</div>
    <?php if(empty($studentMap)): ?>
    <div style="padding:20px;text-align:center;color:var(--muted);font-size:12px">No enrolled students yet.</div>
    <?php else: foreach($studentMap as $sid=>$ms):
        $unread=$unreadMsgMap[$sid]??0;
        $isActive=($chatStudentId===$sid);
    ?>
    <a href="<?= $selfFile ?>?section=chat&chat_student=<?= $sid ?>"
       style="display:flex;align-items:center;gap:10px;padding:10px 14px;text-decoration:none;border-bottom:1px solid var(--border);background:<?= $isActive?'rgba(124,106,247,.12)':'transparent' ?>;transition:.15s"
       onmouseover="this.style.background='rgba(124,106,247,.08)'" onmouseout="this.style.background='<?= $isActive?'rgba(124,106,247,.12)':'transparent' ?>'">
        <div style="width:34px;height:34px;border-radius:50%;background:var(--accent);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#fff;flex-shrink:0"><?= strtoupper(substr($ms['fullname'],0,1)) ?></div>
        <div style="flex:1;min-width:0"><div style="font-size:12px;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($ms['fullname']) ?></div></div>
        <?php if($unread>0): ?><span class="tag tag-blue" style="font-size:10px;padding:1px 6px"><?= $unread ?></span><?php endif; ?>
    </a>
    <?php endforeach; endif; ?>
</div>
<div style="flex:1;display:flex;flex-direction:column;background:var(--bg2);border:1px solid var(--border);border-radius:10px;overflow:hidden">
<?php if($chatStudentId>0&&isset($studentMap[$chatStudentId])):
    $cStudent=$studentMap[$chatStudentId];
    $conn->query("UPDATE messages SET is_read=1 WHERE receiver_id=$instructor_id AND receiver_type='instructor' AND sender_id=$chatStudentId AND sender_type='student'");
    $convRes=q($conn,"SELECT * FROM messages WHERE (sender_id=$instructor_id AND sender_type='instructor' AND receiver_id=$chatStudentId AND receiver_type='student') OR (sender_id=$chatStudentId AND sender_type='student' AND receiver_id=$instructor_id AND receiver_type='instructor') ORDER BY created_at ASC");
?>
<div style="padding:12px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px">
    <div style="width:32px;height:32px;border-radius:50%;background:var(--accent);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#fff"><?= strtoupper(substr($cStudent['fullname'],0,1)) ?></div>
    <div><div style="font-size:13px;font-weight:600"><?= htmlspecialchars($cStudent['fullname']) ?></div></div>
</div>
<div style="flex:1;overflow-y:auto;padding:16px;display:flex;flex-direction:column;gap:10px" id="msg-thread">
    <?php if($convRes->num_rows===0): ?>
    <div style="text-align:center;color:var(--muted);font-size:12px;margin:auto">No messages yet. Say hello!</div>
    <?php else: while($cv=$convRes->fetch_assoc()): $isMine=$cv['sender_type']==='instructor'; ?>
    <div style="display:flex;justify-content:<?= $isMine?'flex-end':'flex-start' ?>">
        <div style="max-width:72%;background:<?= $isMine?'var(--accent)':'var(--bg3)' ?>;color:<?= $isMine?'#fff':'var(--text)' ?>;padding:8px 12px;border-radius:<?= $isMine?'14px 14px 4px 14px':'14px 14px 14px 4px' ?>;font-size:12px;line-height:1.5">
            <?= nl2br(htmlspecialchars($cv['body'])) ?>
            <div style="font-size:9px;opacity:.65;margin-top:4px;text-align:right"><?= date('M d, g:i A',strtotime($cv['created_at'])) ?></div>
        </div>
    </div>
    <?php endwhile; endif; ?>
</div>
<form method="POST" style="padding:12px 16px;border-top:1px solid var(--border);display:flex;gap:8px">
    <input type="hidden" name="action" value="send_message">
    <input type="hidden" name="to_student_id" value="<?= $chatStudentId ?>">
    <input type="hidden" name="appointment_id" value="<?= $cStudent['appointment_id']??0 ?>">
    <textarea name="body" class="di" rows="1" placeholder="Type a message…" required style="flex:1;resize:none;height:38px;padding:8px 12px" onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();this.form.submit();}"></textarea>
    <button type="submit" class="be" style="height:38px;padding:0 16px"><i class="fa-solid fa-paper-plane"></i></button>
</form>
<?php else: ?>
<div style="flex:1;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:12px;color:var(--muted)">
    <i class="fa-solid fa-comments" style="font-size:36px;opacity:.3"></i>
    <div style="font-size:13px">Select a student to start a conversation</div>
</div>
<?php endif; ?>
</div>
</div>
</div>

<!-- ══ PROFILE ════════════════════════════════════════════════ -->
<div id="section-profile" class="section <?= $activeSection==='profile'?'active':'' ?>">
<?php $certOk=!empty($instructor['certificate_image'])&&file_exists($uploadDir.$instructor['certificate_image']); ?>
<div class="panel mb-3">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-user-circle"></i> My Profile</div></div>
    <div style="padding:20px;display:flex;gap:20px;align-items:flex-start">
        <div class="profile-avatar"><?= strtoupper(substr($instructor['fullname'],0,1)) ?></div>
        <div style="flex:1">
            <div style="font-size:18px;font-weight:700;margin-bottom:4px"><?= htmlspecialchars($instructor['fullname']) ?></div>
            <div style="font-size:12px;color:var(--muted);margin-bottom:4px"><?= htmlspecialchars($instructor['email']) ?></div>
            <?php if($avgRating): ?><div style="color:var(--amber)">★ <?= $avgRating ?> instructor rating</div><?php endif; ?>
            <?php if($certOk): ?><div style="margin-top:8px"><span class="cert-badge"><i class="fa-solid fa-certificate"></i> Certified Instructor</span></div><?php endif; ?>
        </div>
    </div>
</div>
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-pen"></i> Edit Profile</div></div>
    <div style="padding:20px">
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="update_profile">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
            <div><label class="form-label">Full Name *</label><input type="text" name="fullname" class="di" value="<?= htmlspecialchars($instructor['fullname']) ?>" required></div>
            <div><label class="form-label">Email *</label><input type="email" name="email" class="di" value="<?= htmlspecialchars($instructor['email']) ?>" required></div>
            <div><label class="form-label">Mobile Number</label><input type="text" name="mobile" class="di" value="<?= htmlspecialchars($instructor['mobile']??'') ?>" placeholder="+63 9XX XXX XXXX"></div>
            <div><label class="form-label">Upload New Certificate (Image/PDF)</label>
                <input type="file" name="certificate_image" class="di" accept="image/*,.pdf">
                <?php if($certOk): ?>
                <div style="font-size:10px;color:var(--green);margin-top:4px"><i class="fa-solid fa-check me-1"></i>Certificate uploaded. Upload new to replace.</div>
                <div style="margin-top:10px;padding:10px;background:var(--bg3);border-radius:8px;border:1px solid var(--border)">
                    <div style="font-size:10px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px"><i class="fa-solid fa-id-card me-1"></i>Current Certificate</div>
                    <img src="<?= htmlspecialchars('../assets/uploads/certificates/'.$instructor['certificate_image']) ?>" style="max-height:120px;border-radius:6px;border:1px solid var(--border);display:block">
                    <a href="<?= 'view_image.php?type=certificates&file='.urlencode($instructor['certificate_image']).'&back='.urlencode($_SERVER['REQUEST_URI']) ?>" class="bds" style="font-size:11px;margin-top:6px;display:inline-flex;align-items:center;gap:4px"><i class="fa-solid fa-eye me-1"></i>View Full Size</a>
                </div>
                <?php endif; ?>
            </div>
            <div style="grid-column:1/-1"><label class="form-label">Bio / Short Description</label>
                <textarea name="bio" class="di" rows="3" placeholder="Describe your experience, specializations, teaching style…"><?= htmlspecialchars($instructor['bio']??'') ?></textarea></div>
        </div>
        <div style="margin-top:16px;display:flex;align-items:center;gap:12px">
            <button type="submit" class="be"><i class="fa-solid fa-save"></i> Save Profile</button>
        </div>
    </form>
    </div>
</div>
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-key"></i> Change Password</div></div>
    <div style="padding:20px;max-width:440px">
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

</div><!-- /main -->

<!-- ══ CANCEL APPOINTMENT MODAL ══════════════════════════════════ -->
<div id="cancelModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:200;align-items:center;justify-content:center;padding:16px">
<div style="background:var(--bg2);border:1px solid var(--border);border-radius:12px;width:420px;max-width:95vw;padding:24px">
    <div style="font-size:15px;font-weight:600;margin-bottom:4px;color:var(--red)"><i class="fa-solid fa-xmark-circle me-2"></i>Cancel Appointment</div>
    <div id="cm_sname" style="font-size:12px;color:var(--muted);margin-bottom:16px"></div>
    <form method="POST" id="cm_form">
    <input type="hidden" name="appointment_id" id="cm_appt_id">
    <input type="hidden" name="action" value="cancel_appointment_instructor">
    <div style="margin-bottom:12px">
        <label class="form-label">Reason for Cancellation <span style="color:var(--muted);font-size:10px">(will be shown to student)</span></label>
        <textarea name="cancel_reason" id="cm_reason" class="di" rows="3" placeholder="e.g. Instructor unavailable on this date…" required></textarea>
    </div>
    <div style="font-size:11px;color:var(--amber);margin-bottom:12px"><i class="fa-solid fa-triangle-exclamation me-1"></i>The student's record will not be affected. Their slot will be freed and they will be notified.</div>
    <div style="display:flex;gap:8px;justify-content:flex-end">
        <button type="button" class="bds" onclick="closeCancelModal()">Back</button>
        <button type="submit" class="ber"><i class="fa-solid fa-xmark me-1"></i>Confirm Cancel</button>
    </div>
    </form>
</div></div>

<!-- ══ LOG SESSION MODAL ════════════════════════════════════════ -->
<div id="sessionModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:200;align-items:center;justify-content:center;padding:16px">
<div style="background:var(--bg2);border:1px solid var(--border);border-radius:12px;width:460px;max-width:95vw;padding:24px;overflow-y:auto;max-height:90vh">
    <div style="font-size:15px;font-weight:600;margin-bottom:4px"><i class="fa-solid fa-plus me-2" style="color:var(--accent)"></i>Log Session — Complete Package</div>
    <div id="sm_sname" style="font-size:12px;color:var(--muted);margin-bottom:4px"></div>
    <div id="sm_pkg_info" style="font-size:11px;color:var(--accent);margin-bottom:16px;background:rgba(124,106,247,.1);border:1px solid rgba(124,106,247,.25);padding:8px 10px;border-radius:6px"></div>
    <form method="POST">
    <input type="hidden" name="action" value="add_session">
    <input type="hidden" name="appointment_id" id="sm_appt_id">
    <div id="sm_hours_single" style="margin-bottom:12px">
        <label class="form-label">Hours Completed *</label>
        <input type="number" name="hours_covered" id="sm_hours" class="di" min="0.5" max="40" step="0.5">
        <div style="font-size:10px;color:var(--muted);margin-top:3px">For TDC, defaults to the hours still needed to reach 15 total (cumulative across bookings). For PDC, defaults to the full package total. Adjust if actual hours differ.</div>
    </div>
    <div id="sm_hours_combo" style="display:none;margin-bottom:12px">
        <label class="form-label">Hours Completed — by Vehicle *</label>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
            <div>
                <label class="form-label" style="font-size:10px;color:var(--green)"><i class="fa-solid fa-car me-1"></i>Car Hours</label>
                <input type="number" name="car_hours" id="sm_car_hours" class="di" min="0" max="20" step="0.5" value="8">
            </div>
            <div>
                <label class="form-label" style="font-size:10px;color:var(--coral,#f0836a)"><i class="fa-solid fa-motorcycle me-1"></i>Motorcycle Hours</label>
                <input type="number" name="moto_hours" id="sm_moto_hours" class="di" min="0" max="20" step="0.5" value="8">
            </div>
        </div>
        <div style="font-size:10px;color:var(--muted);margin-top:3px">Defaults to 8 hours each — adjust to reflect the actual time spent per vehicle. These credit the student's Car and Motorcycle progress separately.</div>
    </div>
    <div id="sm_tdc_fields">
        <div style="margin-bottom:12px">
            <label class="form-label">Overall Grade (1–100) <span style="color:var(--accent)">TDC only</span></label>
            <input type="number" name="grade" id="sm_grade" class="di" min="1" max="100" placeholder="e.g. 85">
        </div>
        <div style="margin-bottom:12px">
            <label class="form-label">Suggested Improvements</label>
            <textarea name="improvements" class="di" rows="2" placeholder="Areas the student should improve…"></textarea>
        </div>
    </div>
    <div style="margin-bottom:12px">
        <label class="form-label">Performance Notes</label>
        <textarea name="performance_notes" class="di" rows="2" placeholder="General session notes…"></textarea>
    </div>
    <div style="font-size:10px;color:var(--muted);margin-bottom:12px"><i class="fa-solid fa-circle-info me-1"></i>This logs the full package as complete in one action. This can only be done once per appointment.</div>
    <div style="display:flex;gap:8px;justify-content:flex-end">
        <button type="button" class="bds" onclick="closeSessionModal()">Cancel</button>
        <button type="submit" class="be"><i class="fa-solid fa-check"></i> Log Session</button>
    </div>
    </form>
</div></div>

<!-- ══ NO SHOW MODAL ══════════════════════════════════════════ -->
<div id="noShowModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:200;align-items:center;justify-content:center;padding:16px">
<div style="background:var(--bg2);border:1px solid var(--border);border-radius:12px;width:420px;max-width:95vw;padding:24px">
    <div style="font-size:15px;font-weight:600;margin-bottom:4px;color:var(--red)"><i class="fa-solid fa-user-slash me-2"></i>Mark Student as No Show</div>
    <div id="ns_sname" style="font-size:12px;color:var(--muted);margin-bottom:16px"></div>
    <form method="POST">
    <input type="hidden" name="action" value="no_show">
    <input type="hidden" name="appointment_id" id="ns_appt_id">
    <input type="hidden" name="session_date" id="ns_date">
    <div style="margin-bottom:12px">
        <label class="form-label">Remarks <span style="color:var(--muted);font-size:10px">(optional, shown to student)</span></label>
        <textarea name="no_show_note" id="ns_note" class="di" rows="3" placeholder="e.g. Student did not arrive, no notice given…"></textarea>
    </div>
    <div style="font-size:11px;color:var(--amber);margin-bottom:12px"><i class="fa-solid fa-triangle-exclamation me-1"></i>This will log a No Show session for this date. The student will be notified.</div>
    <div style="display:flex;gap:8px;justify-content:flex-end">
        <button type="button" class="bds" onclick="closeNoShowModal()">Cancel</button>
        <button type="submit" class="ber"><i class="fa-solid fa-user-slash"></i> Confirm No Show</button>
    </div>
    </form>
</div></div>

<!-- ══ PERFORMANCE RATING MODAL ═════════════════════════════════ -->
<div id="perfModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:200;align-items:center;justify-content:center;padding:16px">
<div style="background:var(--bg2);border:1px solid var(--border);border-radius:12px;width:440px;max-width:95vw;padding:24px;overflow-y:auto;max-height:90vh">
    <div style="font-size:15px;font-weight:600;margin-bottom:4px"><i class="fa-solid fa-star me-2" style="color:var(--amber)"></i><span id="pm_title">Grade & Rate Session</span></div>
    <div id="pm_label" style="font-size:12px;color:var(--muted);margin-bottom:16px"></div>
    <form method="POST">
    <input type="hidden" name="action" value="rate_student">
    <input type="hidden" name="session_id" id="pm_sess_id">
    <div id="pm_grade_wrap" style="margin-bottom:12px">
        <label class="form-label">Numeric Grade (1–100) <span id="pm_grade_req" style="color:var(--red)">*</span></label>
        <input type="number" name="grade" id="pm_grade" class="di" min="1" max="100" placeholder="e.g. 88">
        <div style="font-size:10px;color:var(--muted);margin-top:3px">80–100: Excellent | 60–79: Good | Below 60: Needs Improvement</div>
    </div>
    <div style="margin-bottom:12px">
        <label class="form-label">Suggested Improvements</label>
        <textarea name="improvements" id="pm_improve" class="di" rows="2" placeholder="What should the student improve?"></textarea>
    </div>
    <div id="pm_rating_wrap" style="margin-bottom:12px">
        <label class="form-label">Performance Rating (1–5 stars) <span id="pm_rating_req" style="color:var(--red)">*</span></label>
        <div style="display:flex;gap:6px">
            <?php for($i=1;$i<=5;$i++): ?>
            <label style="cursor:pointer;font-size:26px;color:var(--border)" onclick="setPerfRating(<?= $i ?>)" id="pstar<?= $i ?>">★</label>
            <?php endfor; ?>
        </div>
        <input type="hidden" name="performance_rating" id="pm_rating">
    </div>
    <div style="margin-bottom:12px">
        <label class="form-label">Performance Notes</label>
        <textarea name="performance_notes" id="pm_notes" class="di" rows="2" placeholder="Additional feedback…"></textarea>
    </div>
    <div style="display:flex;gap:8px;justify-content:flex-end">
        <button type="button" class="bds" onclick="closePerfModal()">Cancel</button>
        <button type="submit" class="be"><i class="fa-solid fa-paper-plane"></i> Save</button>
    </div>
    </form>
</div></div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// ── My Calendar page ────────────────────────────────────────────────────
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
        cell.textContent=day;
        const isToday=(dateStr===todayStr);
        let bg='transparent', color='var(--text)';
        if(hasActive){bg='rgba(245,158,11,.18)';color='var(--amber)';}
        else if(hasCompleted){bg='rgba(59,185,122,.18)';color='var(--green)';}
        cell.style.cssText='aspect-ratio:1;border-radius:6px;font-size:12px;border:'+(isToday?'2px solid var(--accent)':'1px solid var(--border)')+
            ';cursor:'+(entries.length?'pointer':'default')+';background:'+bg+';color:'+color+';min-height:34px';
        if(entries.length) cell.onclick=function(){ showMcDetails(dateStr,entries); };
        grid.appendChild(cell);
    }
}
function showMcDetails(dateStr,entries){
    const box=document.getElementById('mc_details');
    const d=new Date(dateStr+'T00:00:00');
    const fmt=d.toLocaleDateString('en-US',{weekday:'long',month:'long',day:'numeric',year:'numeric'});
    let html='<div class="panel" style="border-color:var(--accent)"><div class="panel-head"><div class="panel-title">'+fmt+'</div></div><div style="padding:12px 16px">';
    entries.forEach(function(e){
        const statusColor=(e.status==='completed')?'var(--green)':'var(--amber)';
        const statusLabel=(e.status==='completed')?'Completed':(e.status==='in_progress'?'In Progress':'Booked');
        html+='<div style="padding:8px 0;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">'+
            '<div><strong style="font-size:12px">'+e.course_type+'</strong><div style="font-size:11px;color:var(--muted)">'+e.sname+'</div></div>'+
            '<span style="font-size:11px;color:'+statusColor+'">'+statusLabel+'</span></div>';
    });
    html+='</div></div>';
    box.innerHTML=html;
}
document.addEventListener('DOMContentLoaded',function(){ if(document.getElementById('mc_grid')) renderMyCalendar(); });

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
const T={dashboard:'Dashboard',appointments:'Appointments',students:'My Students',student_profile:'Student Profile',
  performance:'Performance',courses:'My Courses',messages:'Notifications',chat:'Messages',profile:'Profile'};

function openSidebar(){document.getElementById('sidebar').classList.add('open');document.getElementById('sidebarOverlay').classList.add('open');}
function closeSidebar(){document.getElementById('sidebar').classList.remove('open');document.getElementById('sidebarOverlay').classList.remove('open');}

function showSection(n){
    document.querySelectorAll('.section').forEach(s=>s.classList.remove('active'));
    document.querySelectorAll('.nav-item').forEach(x=>x.classList.remove('active'));
    const el=document.getElementById('section-'+n);
    if(el)el.classList.add('active');
    document.getElementById('page-title-text').textContent=T[n]||n;
    const tim=document.getElementById('page-title-text-mobile');if(tim)tim.textContent=T[n]||n;
    document.querySelectorAll('.nav-item').forEach(x=>{
        if(x.getAttribute('onclick')&&x.getAttribute('onclick').includes("'"+n+"'"))x.classList.add('active');
    });
    const u=new URL(window.location.href);
    u.searchParams.set('section',n);
    window.history.pushState({},'',u);
    if(n==='dashboard') setTimeout(initInstrCharts,80);
    closeSidebar();
}

var instrChartsOk=false;
function initInstrCharts(){
    if(instrChartsOk||typeof Chart==='undefined') return;
    instrChartsOk=true;
    var cs=getComputedStyle(document.documentElement);
    var accent=cs.getPropertyValue('--accent').trim()||'#7c6af7';
    var green=cs.getPropertyValue('--green').trim()||'#3cb97a';
    var amber=cs.getPropertyValue('--amber').trim()||'#f59e0b';
    var red=cs.getPropertyValue('--red').trim()||'#e55555';
    var muted=cs.getPropertyValue('--muted').trim()||'#8490a8';
    var border=cs.getPropertyValue('--border').trim()||'#2a3147';
    var bg=cs.getPropertyValue('--bg').trim()||'#0f1117';
    Chart.defaults.color=muted;Chart.defaults.font.family='Segoe UI,sans-serif';Chart.defaults.font.size=11;

    var c1=document.getElementById('instrChartHours');
    if(c1){new Chart(c1,{type:'line',data:{labels:<?= json_encode($instrMonths) ?>,datasets:[{label:'Hours Taught',data:<?= json_encode($instrSessPerMonth) ?>,borderColor:accent,backgroundColor:accent+'33',fill:true,tension:0.4,pointBackgroundColor:accent,pointRadius:4}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{grid:{color:border+'44'}},y:{grid:{color:border+'44'},beginAtZero:true}}}});}

    var c2=document.getElementById('instrChartSplit');
    if(c2){new Chart(c2,{type:'doughnut',data:{labels:['TDC','PDC'],datasets:[{data:[<?= $instrTDCSess ?>,<?= $instrPDCSess ?>],backgroundColor:[amber+'cc',green+'cc'],borderColor:bg,borderWidth:3,hoverOffset:6}]},options:{responsive:true,maintainAspectRatio:false,cutout:'60%',plugins:{legend:{display:false}}}});}

    var c3=document.getElementById('instrChartGrades');
    if(c3){new Chart(c3,{type:'bar',data:{labels:['90–100','80–89','70–79','60–69','< 60'],datasets:[{label:'Students',data:[<?= implode(',',$instrGradeBuckets) ?>],backgroundColor:[green+'cc',accent+'cc',amber+'cc','#fb923c99',red+'99'],borderRadius:4}]},options:{indexAxis:'y',responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{grid:{color:border+'44'},beginAtZero:true,ticks:{precision:0,stepSize:1}},y:{grid:{color:border+'22'}}}}});}
}

(function(){ if(document.querySelector('#section-dashboard.active')) setTimeout(initInstrCharts,120); })();

function openCancelModal(id,sname){
    document.getElementById('cm_appt_id').value=id;
    document.getElementById('cm_sname').textContent='Student: '+sname;
    document.getElementById('cm_reason').value='';
    document.getElementById('cancelModal').style.display='flex';
}
function closeCancelModal(){document.getElementById('cancelModal').style.display='none';}

function openSessionModal(apptId,studentId,sname,type,startDate,endDate,totalHrs,defaultHrs,isCombo){
    _sessionIsTDC=(type==='TDC');
    document.getElementById('sm_appt_id').value=apptId;
    document.getElementById('sm_sname').textContent='Student: '+sname+' | '+type+' Package';
    document.getElementById('sm_pkg_info').innerHTML='<i class="fa-solid fa-calendar-check me-1"></i>'+startDate+' – '+endDate+' &middot; '+totalHrs+' package hours';
    document.getElementById('sm_hours_single').style.display=isCombo?'none':'block';
    document.getElementById('sm_hours_combo').style.display=isCombo?'block':'none';
    document.getElementById('sm_hours').required=!isCombo;
    document.getElementById('sm_hours').value=(defaultHrs!==undefined&&defaultHrs!==null)?defaultHrs:totalHrs;
    document.getElementById('sm_car_hours').value=8;
    document.getElementById('sm_moto_hours').value=8;
    document.getElementById('sm_tdc_fields').style.display=_sessionIsTDC?'block':'none';
    document.getElementById('sessionModal').style.display='flex';
}
function closeSessionModal(){document.getElementById('sessionModal').style.display='none';}

function openNoShowModal(apptId,sname,date){
    document.getElementById('ns_appt_id').value=apptId;
    document.getElementById('ns_date').value=date;
    document.getElementById('ns_sname').textContent='Student: '+sname+' | Missed date: '+date;
    document.getElementById('ns_note').value='';
    document.getElementById('noShowModal').style.display='flex';
}
function closeNoShowModal(){document.getElementById('noShowModal').style.display='none';}

function openPerfModal(sessId,sname,date,sessNum,isTDC,existGrade,existRating,existImprove,existNotes){
    document.getElementById('pm_sess_id').value=sessId;
    document.getElementById('pm_label').textContent='Student: '+sname+' | '+date+' (Day '+sessNum+')';
    document.getElementById('pm_title').textContent=(existGrade||existRating?'Edit ':'')+(isTDC?'Grade Session':'Rate Session');
    document.getElementById('pm_grade_wrap').style.display=isTDC?'block':'none';
    document.getElementById('pm_grade').required=!!isTDC;
    document.getElementById('pm_grade_req').style.display=isTDC?'inline':'none';
    document.getElementById('pm_grade').value=existGrade||'';
    document.getElementById('pm_rating_wrap').style.display=isTDC?'none':'block';
    document.getElementById('pm_rating_req').style.display=isTDC?'none':'inline';
    document.getElementById('pm_improve').value=existImprove||'';
    document.getElementById('pm_notes').value=existNotes||'';
    document.getElementById('pm_rating').value=isTDC?'':(existRating||'');
    for(let i=1;i<=5;i++)document.getElementById('pstar'+i).style.color=(!isTDC&&existRating&&i<=existRating)?'var(--amber)':'var(--border)';
    document.getElementById('perfModal').style.display='flex';
}
function closePerfModal(){document.getElementById('perfModal').style.display='none';}
function setPerfRating(n){
    document.getElementById('pm_rating').value=n;
    for(let i=1;i<=5;i++)document.getElementById('pstar'+i).style.color=i<=n?'var(--amber)':'var(--border)';
}

function openEditCourse(id,desc,reqs,price,limit){
    document.getElementById('ec_course_id').value=id;
    document.getElementById('ec_desc').value=desc;
    document.getElementById('ec_requirements').value=reqs||'';
    document.getElementById('ec_price').value=price;
    document.getElementById('ec_limit').value=limit||'';
    document.getElementById('editCourseModal').style.display='flex';
}
function closeEditCourse(){document.getElementById('editCourseModal').style.display='none';}
document.getElementById('cancelModal').addEventListener('click',function(e){if(e.target===this)closeCancelModal();});
document.getElementById('noShowModal').addEventListener('click',function(e){if(e.target===this)closeNoShowModal();});
document.getElementById('editCourseModal').addEventListener('click',function(e){if(e.target===this)closeEditCourse();});
(function(){var t=document.getElementById('msg-thread');if(t) t.scrollTop=t.scrollHeight;})();
</script>
</body>
</html>
