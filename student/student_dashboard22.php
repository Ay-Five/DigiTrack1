<?php
session_start();
if (!isset($_SESSION['student_id'])) { header('Location: student_login.php'); exit; }
$student_id = (int)$_SESSION['student_id'];
require_once '../includes/db.php';

$today = date('Y-m-d');
$selfFile = basename(__FILE__);

function q($c,$s){return $c->query($s);}
function esc($c,$v){return $c->real_escape_string($v);}
$cColors=['TDC Face-to-Face'=>'tag-amber','TDC Online'=>'tag-blue',
          'PDC Car Manual'=>'tag-green','PDC Car Automatic'=>'tag-teal',
          'PDC Motorcycle Manual'=>'tag-coral','PDC Motorcycle Automatic'=>'tag-purple'];
function stars($n){$s='';for($i=1;$i<=5;$i++)$s.=$i<=$n?'★':'☆';return $s;}

// ── check TDC completion ──────────────────────────────────────
$tdcDone = (int)q($conn,"
    SELECT COUNT(*) AS c FROM appointments a
    JOIN instructor_courses ic ON ic.course_id=a.course_id
    WHERE a.student_id=$student_id
      AND ic.course_type IN('TDC Face-to-Face','TDC Online')
      AND a.status='completed'
")->fetch_assoc()['c'];

// ── check if student already has an active instructor ─────────
$activeInstructor = q($conn,"
    SELECT a.instructor_id,i.fullname,ic.course_type,a.status
    FROM appointments a
    JOIN instructors i ON i.instructor_id=a.instructor_id
    JOIN instructor_courses ic ON ic.course_id=a.course_id
    WHERE a.student_id=$student_id
      AND a.status IN('pending','approved','in_progress')
    ORDER BY a.requested_at DESC LIMIT 1
")->fetch_assoc();

// ── active section ────────────────────────────────────────────
$activeSection = 'dashboard';
if (!empty($_GET['section'])) $activeSection = preg_replace('/[^a-z_]/','',$_GET['section']);
$viewInstrId = (int)($_GET['view_instr'] ?? 0);

// ── POST handlers ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $action = $_POST['action'] ?? '';

    if ($action==='book_appointment') {
        // Block if already has active instructor
        if($activeInstructor){
            $_SESSION['flash_err']='You already have an active appointment with '.$activeInstructor['fullname'].'. Complete all sessions before booking a new instructor.';
            header("Location: $selfFile?section=my_appointments"); exit;
        }

        $instr_id = (int)($_POST['instructor_id'] ?? 0);
        $course_id = (int)($_POST['course_id'] ?? 0);
        $start_date = esc($conn, $_POST['start_date'] ?? '');
        $note = esc($conn, $_POST['student_note'] ?? '');

        $cRow = q($conn,"SELECT * FROM instructor_courses WHERE course_id=$course_id AND instructor_id=$instr_id AND is_active=1")->fetch_assoc();
        if (!$cRow) { $_SESSION['flash_err']='Invalid course selected.'; header("Location: $selfFile?section=view_instr&view_instr=$instr_id"); exit; }

        $isPDC = stripos($cRow['course_type'],'PDC')===0;
        if ($isPDC && !$tdcDone) {
            $_SESSION['flash_err']='You must complete TDC before booking PDC.';
            header("Location: $selfFile?section=view_instr&view_instr=$instr_id"); exit;
        }

        // TDC: validate Sat/Sun
        $isTDC = stripos($cRow['course_type'],'TDC')===0;
        if($isTDC){
            $dayOfWeek=date('N',strtotime($start_date)); // 6=Sat, 7=Sun
            if($dayOfWeek<6){
                $_SESSION['flash_err']='TDC start date must be a Saturday or Sunday.';
                header("Location: $selfFile?section=view_instr&view_instr=$instr_id"); exit;
            }
        }

        $hrs = $isTDC ? 15 : (int)($_POST['total_hours'] ?? 8);
        $price = round($cRow['price_per_hr'] * $hrs, 2);

        $conn->query("INSERT INTO appointments (student_id,instructor_id,course_id,start_date,total_hours,total_price,student_note)
            VALUES ($student_id,$instr_id,$course_id,'$start_date',$hrs,$price,'$note')");

        $sname = esc($conn, $_SESSION['student_name'] ?? 'A student');
        $conn->query("INSERT INTO notifications (user_id,user_type,message,type) VALUES
            ($instr_id,'instructor','New appointment request for {$cRow['course_type']} on $start_date from $sname.','booking_request')");

        $_SESSION['flash']='Appointment request sent!';
        header("Location: $selfFile?section=my_appointments"); exit;
    }

    if ($action==='cancel_appointment') {
        $appt_id = (int)($_POST['appointment_id'] ?? 0);
        $conn->query("UPDATE appointments SET status='cancelled' WHERE appointment_id=$appt_id AND student_id=$student_id AND status='pending'");
        $_SESSION['flash']='Appointment cancelled.';
        header("Location: $selfFile?section=my_appointments"); exit;
    }

    if ($action==='rate_instructor') {
        $appt_id = (int)($_POST['appointment_id'] ?? 0);
        $rating  = max(1,min(5,(int)($_POST['rating'] ?? 5)));
        $comment = esc($conn, $_POST['comment'] ?? '');
        $aRow = q($conn,"SELECT * FROM appointments WHERE appointment_id=$appt_id AND student_id=$student_id AND status='completed'")->fetch_assoc();
        if ($aRow) {
            $conn->query("INSERT IGNORE INTO instructor_ratings (appointment_id,student_id,instructor_id,rating,comment)
                VALUES ($appt_id,$student_id,{$aRow['instructor_id']},$rating,'$comment')");
        }
        header("Location: $selfFile?section=my_appointments"); exit;
    }

    if ($action==='mark_read_student') {
        $conn->query("UPDATE notifications SET is_read=1 WHERE user_id=$student_id AND user_type='student'");
        header("Location: $selfFile?section=messages"); exit;
    }

    // Update student profile
    if($action==='update_profile'){
        $name=esc($conn,$_POST['fullname']??'');
        $email=esc($conn,$_POST['email']??'');
        $mobile=esc($conn,$_POST['mobile']??'');
        $address=esc($conn,$_POST['address']??'');
        $conn->query("UPDATE students SET fullname='$name',email='$email',mobile='$mobile',address='$address' WHERE student_id=$student_id");
        $_SESSION['student_name']=$_POST['fullname']??'';
        $_SESSION['flash']='Profile updated.';
        header("Location: $selfFile?section=profile"); exit;
    }
}

// ── data loads ────────────────────────────────────────────────
$student = q($conn,"SELECT * FROM students WHERE student_id=$student_id")->fetch_assoc();
$unreadCnt = (int)q($conn,"SELECT COUNT(*) AS c FROM notifications WHERE user_id=$student_id AND user_type='student' AND is_read=0")->fetch_assoc()['c'];

$myAppts = q($conn,"
    SELECT a.*,ic.course_type,ic.price_per_hr,i.fullname AS instr_name,
           tc.cert_id, tc.delivery_method AS cert_delivery, tc.cert_image AS cert_img,
           ir.rating AS my_rating,
           (SELECT SUM(hours_covered) FROM sessions WHERE appointment_id=a.appointment_id AND status='completed') AS hours_done
    FROM appointments a
    JOIN instructor_courses ic ON ic.course_id=a.course_id
    JOIN instructors i ON i.instructor_id=a.instructor_id
    LEFT JOIN tdc_certificates tc ON tc.appointment_id=a.appointment_id
    LEFT JOIN instructor_ratings ir ON ir.appointment_id=a.appointment_id AND ir.student_id=$student_id
    WHERE a.student_id=$student_id
    ORDER BY a.requested_at DESC
");

// All instructors for browse (no "My Courses" filter — show all)
$allInstructors = q($conn,"
    SELECT i.instructor_id,i.fullname,i.email,i.certificate_image,
           (SELECT COUNT(*) FROM instructor_courses WHERE instructor_id=i.instructor_id AND is_active=1) AS course_count,
           (SELECT GROUP_CONCAT(DISTINCT course_type ORDER BY course_type SEPARATOR '|')
            FROM instructor_courses WHERE instructor_id=i.instructor_id AND is_active=1) AS courses,
           (SELECT ROUND(AVG(rating),1) FROM instructor_ratings WHERE instructor_id=i.instructor_id) AS avg_rating,
           (SELECT COUNT(*) FROM instructor_ratings WHERE instructor_id=i.instructor_id) AS rating_count
    FROM instructors i
    WHERE EXISTS(SELECT 1 FROM instructor_courses ic WHERE ic.instructor_id=i.instructor_id AND ic.is_active=1)
    ORDER BY avg_rating DESC, i.fullname ASC
");

// View single instructor — show ALL their courses (TDC + PDC)
$viewInstr = null;
$instrAllCourses = null;
if ($viewInstrId > 0) {
    $viewInstr = q($conn,"
        SELECT i.instructor_id,i.fullname,i.email,i.certificate_image,i.bio,
               (SELECT ROUND(AVG(rating),1) FROM instructor_ratings WHERE instructor_id=i.instructor_id) AS avg_rating,
               (SELECT COUNT(*) FROM instructor_ratings WHERE instructor_id=i.instructor_id) AS rating_count
        FROM instructors i WHERE i.instructor_id=$viewInstrId
    ")->fetch_assoc();
    // Show ALL active courses (TDC and PDC together)
    $instrAllCourses = q($conn,"SELECT * FROM instructor_courses WHERE instructor_id=$viewInstrId AND is_active=1 ORDER BY course_type");
}

$uploadDir = '../assets/uploads/certificates/';
$notifs = q($conn,"SELECT * FROM notifications WHERE user_id=$student_id AND user_type='student' ORDER BY created_at DESC LIMIT 30");
$mySessions = q($conn,"
    SELECT s.*,a.course_id,ic.course_type,i.fullname AS instr_name
    FROM sessions s
    JOIN appointments a ON a.appointment_id=s.appointment_id
    JOIN instructor_courses ic ON ic.course_id=a.course_id
    JOIN instructors i ON i.instructor_id=s.instructor_id
    WHERE s.student_id=$student_id
    ORDER BY s.session_date DESC
");

$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);
$flashErr = $_SESSION['flash_err'] ?? null; unset($_SESSION['flash_err']);
$scmS=['pending'=>'bkp','approved'=>'bksu','in_progress'=>'bkb','completed'=>'bkg','rejected'=>'bkr','cancelled'=>'bkd'];
$scmL=['pending'=>'Pending','approved'=>'Approved','in_progress'=>'In Progress','completed'=>'Completed','rejected'=>'Rejected','cancelled'=>'Cancelled'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Student Dashboard – ABROT Driving School</title>
<link href="../assets/img/logo.png" rel="icon">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root{--bg:#0f1117;--bg2:#181c27;--bg3:#1e2436;--border:#2a3147;--accent:#7c6af7;--green:#3cb97a;--amber:#f59e0b;--red:#e55;--text:#e2e5f0;--muted:#8490a8}
*{box-sizing:border-box;margin:0;padding:0}
body{background:var(--bg);color:var(--text);font-family:'Segoe UI',sans-serif;min-height:100vh;display:flex}
.sidebar{width:220px;background:var(--bg2);border-right:1px solid var(--border);display:flex;flex-direction:column;padding:20px 0;flex-shrink:0;position:fixed;height:100vh;overflow-y:auto;z-index:100}
.brand{padding:0 18px 20px;font-size:15px;font-weight:700;color:var(--accent);border-bottom:1px solid var(--border);margin-bottom:12px}
.nav-item{display:flex;align-items:center;gap:10px;padding:10px 18px;color:var(--muted);font-size:13px;cursor:pointer;border-radius:0;transition:.15s;text-decoration:none;border:none;background:none;width:100%;text-align:left}
.nav-item:hover,.nav-item.active{background:rgba(124,106,247,.12);color:var(--text)}
.nav-item.active{border-left:3px solid var(--accent);color:var(--accent)}
.nav-item i{width:16px;text-align:center}
.main{margin-left:220px;flex:1;padding:24px;min-height:100vh}
.page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px}
.page-title{font-size:18px;font-weight:600}
.section{display:none}.section.active{display:block}
.panel{background:var(--bg2);border:1px solid var(--border);border-radius:10px;overflow:hidden;margin-bottom:16px}
.panel-head{padding:14px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.panel-title{font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px}
.stats-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-bottom:20px}
.stat-card{background:var(--bg2);border:1px solid var(--border);border-radius:10px;padding:16px;cursor:pointer}
.stat-num{font-size:26px;font-weight:700;color:var(--accent)}
.stat-label{font-size:11px;color:var(--muted);margin-top:4px}
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
.tbl{width:100%;border-collapse:collapse;font-size:12px}
.tbl th{padding:10px 12px;text-align:left;color:var(--muted);font-weight:500;border-bottom:1px solid var(--border);font-size:11px}
.tbl td{padding:10px 12px;border-bottom:1px solid var(--border)}
.tbl tr:last-child td{border-bottom:none}
.tbl tbody tr:hover{background:rgba(255,255,255,.02)}
.instr-card{background:var(--bg3);border:1px solid var(--border);border-radius:10px;padding:16px;display:flex;flex-direction:column;gap:10px;transition:.15s;cursor:pointer}
.instr-card:hover{border-color:var(--accent);transform:translateY(-2px)}
.instr-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px}
.avatar{width:44px;height:44px;border-radius:50%;background:var(--accent);display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:700;color:#fff;flex-shrink:0}
.di{background:var(--bg);border:1px solid var(--border);color:var(--text);padding:7px 10px;border-radius:6px;font-size:12px;width:100%}
.di:focus{outline:none;border-color:var(--accent)}
.form-label{font-size:11px;color:var(--muted);margin-bottom:4px;display:block}
.prog-bar{height:8px;background:var(--border);border-radius:4px;overflow:hidden}
.prog-fill{height:100%;background:var(--accent);border-radius:4px;transition:width .3s}
.stars-input label{color:var(--border);cursor:pointer;font-size:20px}
.stars-input input:checked ~ label,.stars-input label:hover,.stars-input label:hover ~ label{color:var(--amber)}
.stars-input{display:flex;flex-direction:row-reverse;justify-content:flex-end}
.flash{padding:10px 16px;border-radius:8px;margin-bottom:16px;font-size:12px}
.flash-ok{background:rgba(59,185,122,.15);border:1px solid rgba(59,185,122,.3);color:var(--green)}
.flash-err{background:rgba(229,85,85,.15);border:1px solid rgba(229,85,85,.3);color:var(--red)}
.cert-badge{background:rgba(245,158,11,.12);border:1px solid rgba(245,158,11,.3);color:#f59e0b;padding:4px 10px;border-radius:20px;font-size:11px;display:inline-flex;align-items:center;gap:5px}
.lock-banner{background:rgba(229,85,85,.08);border:1px solid rgba(229,85,85,.25);border-radius:8px;padding:12px 16px;font-size:12px;color:var(--red);display:flex;align-items:center;gap:10px;margin-bottom:12px}
.active-instr-banner{background:rgba(124,106,247,.08);border:1px solid rgba(124,106,247,.3);border-radius:8px;padding:12px 16px;font-size:12px;color:var(--accent);display:flex;align-items:center;gap:10px;margin-bottom:12px}
.grade-bar{height:6px;background:var(--border);border-radius:3px;overflow:hidden;margin-top:3px}
.grade-fill{height:100%;border-radius:3px}
.course-section-label{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);padding:8px 12px;border-bottom:1px solid var(--border);background:var(--bg3)}
</style>
</head>
<body>

<div class="sidebar">
    <div class="brand"><i class="fa-solid fa-car me-2"></i>ABROT Student</div>
    <a class="nav-item <?= $activeSection==='dashboard'?'active':'' ?>" onclick="showSection('dashboard')" href="#">
        <i class="fa-solid fa-gauge"></i> Dashboard</a>
    <a class="nav-item <?= in_array($activeSection,['instructors','view_instr'])?'active':'' ?>" onclick="showSection('instructors')" href="#">
        <i class="fa-solid fa-users"></i> Browse Instructors</a>
    <a class="nav-item <?= $activeSection==='my_appointments'?'active':'' ?>" onclick="showSection('my_appointments')" href="#">
        <i class="fa-solid fa-calendar-check"></i> My Appointments</a>
    <a class="nav-item <?= $activeSection==='progress'?'active':'' ?>" onclick="showSection('progress')" href="#">
        <i class="fa-solid fa-chart-line"></i> My Progress</a>
    <a class="nav-item <?= $activeSection==='messages'?'active':'' ?>" onclick="showSection('messages')" href="#">
        <i class="fa-solid fa-bell"></i> Notifications
        <?php if($unreadCnt>0): ?><span class="tag tag-amber"><?= $unreadCnt ?></span><?php endif; ?></a>
    <a class="nav-item <?= $activeSection==='profile'?'active':'' ?>" onclick="showSection('profile')" href="#">
        <i class="fa-solid fa-user"></i> Profile</a>
    <div style="flex:1"></div>
    <a class="nav-item" href="student_logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
</div>

<div class="main">
<div class="page-header">
    <div class="page-title" id="page-title-text">Dashboard</div>
    <div style="font-size:12px;color:var(--muted)">Welcome, <?= htmlspecialchars($student['fullname']) ?></div>
</div>

<?php if($flash): ?><div class="flash flash-ok"><i class="fa-solid fa-circle-check me-2"></i><?= htmlspecialchars($flash) ?></div><?php endif; ?>
<?php if($flashErr): ?><div class="flash flash-err"><i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($flashErr) ?></div><?php endif; ?>

<!-- ══ DASHBOARD ══════════════════════════════════════════════ -->
<div id="section-dashboard" class="section <?= $activeSection==='dashboard'?'active':'' ?>">
<?php
$totalAppts = (int)q($conn,"SELECT COUNT(*) AS c FROM appointments WHERE student_id=$student_id")->fetch_assoc()['c'];
$activeAppts = (int)q($conn,"SELECT COUNT(*) AS c FROM appointments WHERE student_id=$student_id AND status IN('approved','in_progress')")->fetch_assoc()['c'];
$completedAppts = (int)q($conn,"SELECT COUNT(*) AS c FROM appointments WHERE student_id=$student_id AND status='completed'")->fetch_assoc()['c'];
$hasCert = (int)q($conn,"SELECT COUNT(*) AS c FROM tdc_certificates WHERE student_id=$student_id")->fetch_assoc()['c'];
$avgGrade = q($conn,"SELECT ROUND(AVG(grade),1) AS ag FROM sessions WHERE student_id=$student_id AND grade IS NOT NULL")->fetch_assoc()['ag'];
?>
<div class="stats-row">
    <div class="stat-card" onclick="showSection('my_appointments')">
        <div class="stat-num"><?= $totalAppts ?></div><div class="stat-label">Total Appointments</div>
    </div>
    <div class="stat-card" onclick="showSection('my_appointments')">
        <div class="stat-num" style="color:var(--amber)"><?= $activeAppts ?></div><div class="stat-label">Active</div>
    </div>
    <div class="stat-card" onclick="showSection('my_appointments')">
        <div class="stat-num" style="color:var(--green)"><?= $completedAppts ?></div><div class="stat-label">Completed</div>
    </div>
    <div class="stat-card">
        <div class="stat-num" style="color:var(--amber)"><?= $hasCert ? '✓' : '–' ?></div><div class="stat-label">TDC Certificate</div>
    </div>
    <?php if($avgGrade): $gc=$avgGrade>=80?'var(--green)':($avgGrade>=60?'var(--amber)':'var(--red)'); ?>
    <div class="stat-card" onclick="showSection('progress')">
        <div class="stat-num" style="color:<?= $gc ?>"><?= $avgGrade ?></div><div class="stat-label">Avg TDC Grade</div>
    </div>
    <?php endif; ?>
</div>

<?php if($activeInstructor): ?>
<div class="active-instr-banner">
    <i class="fa-solid fa-chalkboard-user"></i>
    <span>You are currently enrolled with <strong><?= htmlspecialchars($activeInstructor['fullname']) ?></strong> (<span class="<?= $scmL[$activeInstructor['status']]??'' ?>"><?= $scmL[$activeInstructor['status']] ?></span>). Complete all sessions to book a new instructor.</span>
</div>
<?php elseif(!$tdcDone): ?>
<div class="lock-banner">
    <i class="fa-solid fa-lock"></i>
    <span>Complete <strong>15 hours of TDC</strong> first to unlock PDC courses.</span>
</div>
<?php else: ?>
<div class="flash flash-ok mb-3"><i class="fa-solid fa-certificate me-2"></i>TDC complete — PDC courses are now unlocked!</div>
<?php endif; ?>

<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-calendar-check"></i> Recent Appointments</div>
        <a class="be" onclick="showSection('my_appointments')" href="#" style="font-size:11px">View All</a></div>
    <table class="tbl"><thead><tr><th>Course</th><th>Instructor</th><th>Start Date</th><th>Hours</th><th>Status</th></tr></thead><tbody>
    <?php
    $recentA=q($conn,"SELECT a.*,ic.course_type,i.fullname AS iname FROM appointments a JOIN instructor_courses ic ON ic.course_id=a.course_id JOIN instructors i ON i.instructor_id=a.instructor_id WHERE a.student_id=$student_id ORDER BY a.requested_at DESC LIMIT 5");
    if($recentA->num_rows===0): ?><tr><td colspan="5" style="color:var(--muted);text-align:center;padding:24px">No appointments yet. <a href="#" onclick="showSection('instructors')">Browse instructors</a> to book one.</td></tr>
    <?php else: while($ra=$recentA->fetch_assoc()): $tc=$cColors[$ra['course_type']]??'tag-gray'; ?>
    <tr>
        <td><span class="tag <?= $tc ?>"><?= htmlspecialchars($ra['course_type']) ?></span></td>
        <td><?= htmlspecialchars($ra['iname']) ?></td>
        <td><?= date('M d, Y',strtotime($ra['start_date'])) ?></td>
        <td><?= $ra['total_hours'] ?> hrs</td>
        <td><span class="<?= $scmS[$ra['status']]??'bkd' ?>"><?= $scmL[$ra['status']]??$ra['status'] ?></span></td>
    </tr>
    <?php endwhile; endif; ?>
    </tbody></table>
</div>
</div>

<!-- ══ BROWSE INSTRUCTORS ═════════════════════════════════════ -->
<div id="section-instructors" class="section <?= $activeSection==='instructors'?'active':'' ?>">
<div class="panel-head" style="background:var(--bg2);border-radius:10px;margin-bottom:16px;border:1px solid var(--border)">
    <div class="panel-title"><i class="fa-solid fa-users"></i> Available Instructors</div>
    <input type="text" class="di" style="width:200px" placeholder="Search instructors…" oninput="handleSearch(this.value)">
</div>
<?php if($activeInstructor): ?>
<div class="active-instr-banner"><i class="fa-solid fa-info-circle"></i>
    <span>You have an active appointment with <strong><?= htmlspecialchars($activeInstructor['fullname']) ?></strong>. You can browse instructors, but cannot book until your current sessions are complete.</span></div>
<?php elseif(!$tdcDone): ?>
<div class="lock-banner"><i class="fa-solid fa-info-circle"></i>
    <span>You can book any TDC course. PDC courses unlock after completing 15 hrs of TDC.</span></div>
<?php endif; ?>
<div class="instr-grid">
<?php $allInstructors->data_seek(0); while($ins=$allInstructors->fetch_assoc()):
    $certOk=$ins['certificate_image']&&file_exists($uploadDir.$ins['certificate_image']);
    $initial=strtoupper(substr($ins['fullname'],0,1)); ?>
<div class="instr-card" onclick="viewInstructor(<?= $ins['instructor_id'] ?>)">
    <div style="display:flex;align-items:center;gap:12px">
        <div class="avatar"><?= $initial ?></div>
        <div style="flex:1">
            <div style="font-size:13px;font-weight:600"><?= htmlspecialchars($ins['fullname']) ?></div>
            <?php if($certOk): ?><div style="font-size:11px;color:var(--green);margin-top:2px"><i class="fa-solid fa-certificate me-1"></i>Certified</div><?php endif; ?>
        </div>
        <?php if($ins['avg_rating']): ?>
        <div style="text-align:right">
            <div style="color:var(--amber);font-size:13px">★ <?= $ins['avg_rating'] ?></div>
            <div style="font-size:10px;color:var(--muted)"><?= $ins['rating_count'] ?> reviews</div>
        </div>
        <?php endif; ?>
    </div>
    <div style="display:flex;flex-wrap:wrap;gap:4px">
        <?php foreach(explode('|',$ins['courses']??'') as $ct): if(!$ct)continue; $tc=$cColors[$ct]??'tag-gray'; ?>
        <span class="tag <?= $tc ?>"><?= htmlspecialchars($ct) ?></span>
        <?php endforeach; ?>
    </div>
    <div style="font-size:11px;color:var(--accent)">View All Courses & Book →</div>
</div>
<?php endwhile; ?>
</div>
</div>

<!-- ══ VIEW INSTRUCTOR ════════════════════════════════════════ -->
<div id="section-view_instr" class="section <?= $activeSection==='view_instr'?'active':'' ?>">
<?php if($viewInstr): $certOk2=!empty($viewInstr['certificate_image'])&&file_exists($uploadDir.$viewInstr['certificate_image']); ?>
<a href="#" onclick="showSection('instructors')" class="bds mb-3" style="display:inline-flex;align-items:center;gap:6px"><i class="fa-solid fa-arrow-left"></i> Back to Instructors</a>

<div class="panel" style="margin-top:12px">
    <div class="panel-head">
        <div class="panel-title">
            <div class="avatar" style="width:36px;height:36px;font-size:14px"><?= strtoupper(substr($viewInstr['fullname'],0,1)) ?></div>
            <?= htmlspecialchars($viewInstr['fullname']) ?>
            <?php if($certOk2): ?><span class="tag tag-green"><i class="fa-solid fa-certificate"></i>Certified</span><?php endif; ?>
        </div>
        <?php if($viewInstr['avg_rating']): ?>
        <div style="color:var(--amber)">★ <?= $viewInstr['avg_rating'] ?> <span style="font-size:11px;color:var(--muted)">(<?= $viewInstr['rating_count'] ?> reviews)</span></div>
        <?php endif; ?>
    </div>
    <?php if(!empty($viewInstr['bio'])): ?>
    <div style="padding:12px 16px;border-bottom:1px solid var(--border);font-size:12px;color:var(--muted)"><?= nl2br(htmlspecialchars($viewInstr['bio'])) ?></div>
    <?php endif; ?>
    <?php if($certOk2): ?>
    <div style="padding:12px 16px;border-bottom:1px solid var(--border)">
        <div style="font-size:11px;color:var(--muted);margin-bottom:6px">Instructor Certificate</div>
        <img src="<?= htmlspecialchars('../assets/uploads/certificates/'.$viewInstr['certificate_image']) ?>" style="max-height:120px;border-radius:6px;cursor:pointer" onclick="window.open(this.src)">
    </div>
    <?php endif; ?>
</div>

<!-- ALL Courses: TDC first, then PDC -->
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-book-open"></i> All Courses Offered</div>
        <div style="font-size:11px;color:var(--muted)">TDC must be completed before PDC</div>
    </div>

    <?php if($activeInstructor): ?>
    <div style="padding:12px 16px;background:rgba(124,106,247,.06);border-bottom:1px solid var(--border);font-size:12px;color:var(--accent)">
        <i class="fa-solid fa-info-circle me-2"></i>You already have an active appointment. Complete your current sessions to book again.
    </div>
    <?php endif; ?>

    <?php
    // Separate TDC & PDC
    $instrAllCourses->data_seek(0);
    $tdcCourses=[]; $pdcCourses=[];
    while($c=$instrAllCourses->fetch_assoc()){
        if(stripos($c['course_type'],'TDC')===0) $tdcCourses[]=$c;
        else $pdcCourses[]=$c;
    }
    ?>

    <!-- TDC Courses -->
    <?php if(!empty($tdcCourses)): ?>
    <div class="course-section-label"><i class="fa-solid fa-book me-2" style="color:#f59e0b"></i>TDC – Theoretical Driving Course (Saturday & Sunday only)</div>
    <table class="tbl"><thead><tr><th>Course Type</th><th>Price/Hr</th><th>Hours Required</th><th>Total Price</th><th></th></tr></thead><tbody>
    <?php foreach($tdcCourses as $c): $tc=$cColors[$c['course_type']]??'tag-gray'; $totalP=number_format($c['price_per_hr']*15,2); ?>
    <tr>
        <td><span class="tag <?= $tc ?>"><?= htmlspecialchars($c['course_type']) ?></span></td>
        <td>₱<?= number_format($c['price_per_hr'],2) ?></td>
        <td><strong>15 hrs</strong> (required)</td>
        <td>₱<?= $totalP ?></td>
        <td>
        <?php if($activeInstructor): ?>
            <span style="font-size:11px;color:var(--muted)"><i class="fa-solid fa-clock me-1"></i>Active appt in progress</span>
        <?php elseif($tdcDone): ?>
            <span style="font-size:11px;color:var(--green)"><i class="fa-solid fa-check me-1"></i>TDC Complete</span>
        <?php else: ?>
            <button class="be" onclick="openBookModal(<?= $viewInstr['instructor_id'] ?>,<?= $c['course_id'] ?>,'<?= addslashes($c['course_type']) ?>',<?= $c['price_per_hr'] ?>,true)" style="font-size:11px">
                <i class="fa-solid fa-calendar-plus"></i> Book
            </button>
        <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody></table>
    <?php endif; ?>

    <!-- PDC Courses -->
    <?php if(!empty($pdcCourses)): ?>
    <div class="course-section-label"><i class="fa-solid fa-car me-2" style="color:#3cb97a"></i>PDC – Practical Driving Course <?= !$tdcDone?'<span style="color:var(--red)">(Locked – Complete TDC first)</span>':'' ?></div>
    <table class="tbl"><thead><tr><th>Course Type</th><th>Price/Hr</th><th>Hours</th><th>Total</th><th></th></tr></thead><tbody>
    <?php foreach($pdcCourses as $c): $tc=$cColors[$c['course_type']]??'tag-gray'; $locked=!$tdcDone; ?>
    <tr>
        <td><span class="tag <?= $tc ?>"><?= htmlspecialchars($c['course_type']) ?></span></td>
        <td>₱<?= number_format($c['price_per_hr'],2) ?></td>
        <td>Flexible</td>
        <td>Based on hours</td>
        <td>
        <?php if($locked): ?>
            <span style="font-size:11px;color:var(--red)"><i class="fa-solid fa-lock me-1"></i>Complete TDC first</span>
        <?php elseif($activeInstructor): ?>
            <span style="font-size:11px;color:var(--muted)"><i class="fa-solid fa-clock me-1"></i>Active appt in progress</span>
        <?php else: ?>
            <button class="be" onclick="openBookModal(<?= $viewInstr['instructor_id'] ?>,<?= $c['course_id'] ?>,'<?= addslashes($c['course_type']) ?>',<?= $c['price_per_hr'] ?>,false)" style="font-size:11px">
                <i class="fa-solid fa-calendar-plus"></i> Book
            </button>
        <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody></table>
    <?php endif; ?>
</div>

<!-- Reviews -->
<?php $reviews=q($conn,"SELECT ir.*,s.fullname AS sname FROM instructor_ratings ir JOIN students s ON s.student_id=ir.student_id WHERE ir.instructor_id={$viewInstr['instructor_id']} ORDER BY ir.rated_at DESC LIMIT 5");
if($reviews->num_rows>0): ?>
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-star"></i> Student Reviews</div></div>
    <div style="padding:12px 16px;display:flex;flex-direction:column;gap:12px">
    <?php while($rv=$reviews->fetch_assoc()): ?>
    <div style="padding:10px;background:var(--bg3);border-radius:8px">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
            <div style="font-size:12px;font-weight:600"><?= htmlspecialchars($rv['sname']) ?></div>
            <div style="color:var(--amber);font-size:12px"><?= stars($rv['rating']) ?></div>
            <div style="font-size:10px;color:var(--muted);margin-left:auto"><?= date('M d, Y',strtotime($rv['rated_at'])) ?></div>
        </div>
        <?php if($rv['comment']): ?><div style="font-size:12px;color:var(--muted)"><?= nl2br(htmlspecialchars($rv['comment'])) ?></div><?php endif; ?>
    </div>
    <?php endwhile; ?>
    </div>
</div>
<?php endif; ?>
<?php else: ?><p style="color:var(--muted)">Instructor not found.</p><?php endif; ?>
</div>

<!-- ══ MY APPOINTMENTS ════════════════════════════════════════ -->
<div id="section-my_appointments" class="section <?= $activeSection==='my_appointments'?'active':'' ?>">
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-calendar-check"></i> My Appointments</div></div>
    <table class="tbl"><thead><tr><th>Course</th><th>Instructor</th><th>Start</th><th>Hours</th><th>Progress</th><th>TDC Grade</th><th>Status</th><th>Certificate</th><th></th></tr></thead><tbody>
    <?php $myAppts->data_seek(0); if($myAppts->num_rows===0): ?>
    <tr><td colspan="9" style="color:var(--muted);text-align:center;padding:24px">No appointments yet. <a href="#" onclick="showSection('instructors')">Browse instructors</a> to book one.</td></tr>
    <?php else: while($ap=$myAppts->fetch_assoc()):
        $tc=$cColors[$ap['course_type']]??'tag-gray';
        $hoursDone=(float)($ap['hours_done']??0);
        $pct=min(100,round($hoursDone/$ap['total_hours']*100));
        $isTDC=stripos($ap['course_type'],'TDC')===0;
        $gc=isset($ap['tdc_overall_grade'])&&$ap['tdc_overall_grade']?($ap['tdc_overall_grade']>=80?'var(--green)':($ap['tdc_overall_grade']>=60?'var(--amber)':'var(--red)')):'';
    ?>
    <tr>
        <td><span class="tag <?= $tc ?>"><?= htmlspecialchars($ap['course_type']) ?></span></td>
        <td><?= htmlspecialchars($ap['instr_name']) ?></td>
        <td><?= date('M d, Y',strtotime($ap['start_date'])) ?></td>
        <td><?= $hoursDone ?>/<?= $ap['total_hours'] ?> hrs</td>
        <td style="min-width:80px">
            <div class="prog-bar"><div class="prog-fill" style="width:<?= $pct ?>%"></div></div>
            <div style="font-size:10px;color:var(--muted);margin-top:2px"><?= $pct ?>%</div>
        </td>
        <td><?= ($isTDC&&isset($ap['tdc_overall_grade'])&&$ap['tdc_overall_grade'])?'<strong style="color:'.$gc.'">'.$ap['tdc_overall_grade'].'/100</strong>':'—' ?></td>
        <td><span class="<?= $scmS[$ap['status']]??'bkd' ?>"><?= $scmL[$ap['status']]??$ap['status'] ?></span></td>
        <td>
            <?php if($ap['cert_id']): ?>
            <span class="cert-badge"><i class="fa-solid fa-certificate"></i>
            <?php if(isset($ap['cert_delivery'])&&$ap['cert_delivery']==='online'&&!empty($ap['cert_img'])&&file_exists('../assets/uploads/certs/'.$ap['cert_img'])): ?>
            <a href="../assets/uploads/certs/<?= htmlspecialchars($ap['cert_img']) ?>" target="_blank" style="color:inherit;text-decoration:none">Download</a>
            <?php else: ?>Issued<?php endif; ?></span>
            <?php else: ?>—<?php endif; ?>
        </td>
        <td style="display:flex;gap:6px;flex-wrap:wrap">
            <?php if($ap['status']==='pending'): ?>
            <form method="POST" onsubmit="return confirm('Cancel this appointment?')">
                <input type="hidden" name="action" value="cancel_appointment">
                <input type="hidden" name="appointment_id" value="<?= $ap['appointment_id'] ?>">
                <button type="submit" class="bds" style="font-size:11px">Cancel</button>
            </form>
            <?php endif; ?>
            <?php if($ap['status']==='completed' && !$ap['my_rating']): ?>
            <button class="be" style="font-size:11px" onclick="openRateModal(<?= $ap['appointment_id'] ?>,'<?= addslashes($ap['instr_name']) ?>')">
                <i class="fa-solid fa-star"></i> Rate
            </button>
            <?php elseif($ap['my_rating']): ?>
            <span style="color:var(--amber);font-size:12px"><?= stars($ap['my_rating']) ?></span>
            <?php endif; ?>
        </td>
    </tr>
    <?php endwhile; endif; ?>
    </tbody></table>
</div>
</div>

<!-- ══ MY PROGRESS ════════════════════════════════════════════ -->
<div id="section-progress" class="section <?= $activeSection==='progress'?'active':'' ?>">
<?php
$tdcHours=(float)q($conn,"SELECT COALESCE(SUM(s.hours_covered),0) AS h FROM sessions s JOIN appointments a ON a.appointment_id=s.appointment_id JOIN instructor_courses ic ON ic.course_id=a.course_id WHERE s.student_id=$student_id AND ic.course_type IN('TDC Face-to-Face','TDC Online') AND s.status='completed'")->fetch_assoc()['h'];
$pdcHours=(float)q($conn,"SELECT COALESCE(SUM(s.hours_covered),0) AS h FROM sessions s JOIN appointments a ON a.appointment_id=s.appointment_id JOIN instructor_courses ic ON ic.course_id=a.course_id WHERE s.student_id=$student_id AND ic.course_type LIKE 'PDC%' AND s.status='completed'")->fetch_assoc()['h'];
$tdcPct=min(100,round($tdcHours/15*100));
$tdcAvgGrade=q($conn,"SELECT ROUND(AVG(grade),1) AS ag FROM sessions s JOIN appointments a ON a.appointment_id=s.appointment_id JOIN instructor_courses ic ON ic.course_id=a.course_id WHERE s.student_id=$student_id AND ic.course_type LIKE 'TDC%' AND s.grade IS NOT NULL")->fetch_assoc()['ag'];
$overallGrade=q($conn,"SELECT tdc_overall_grade FROM appointments a JOIN instructor_courses ic ON ic.course_id=a.course_id WHERE a.student_id=$student_id AND ic.course_type LIKE 'TDC%' AND a.status='completed' AND a.tdc_overall_grade IS NOT NULL ORDER BY a.completed_at DESC LIMIT 1")->fetch_assoc();
?>
<div class="panel mb-3">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-road"></i> Training Progress</div></div>
    <div style="padding:20px;display:flex;flex-direction:column;gap:20px">
        <!-- TDC -->
        <div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
                <div style="font-size:13px;font-weight:600"><span class="tag tag-amber me-2">TDC</span> Theoretical Driving Course <small style="color:var(--muted);font-size:11px">(Sat & Sun sessions)</small></div>
                <div style="font-size:12px;color:var(--muted)"><?= $tdcHours ?>/15 hrs</div>
            </div>
            <div class="prog-bar" style="height:12px">
                <div class="prog-fill" style="width:<?= $tdcPct ?>%;background:<?= $tdcDone?'var(--green)':'var(--accent)' ?>"></div>
            </div>
            <?php if($tdcDone): ?>
            <div style="margin-top:8px;display:flex;gap:12px;align-items:center;flex-wrap:wrap">
                <span class="cert-badge"><i class="fa-solid fa-certificate"></i> TDC Complete — PDC Unlocked</span>
                <?php if($overallGrade&&$overallGrade['tdc_overall_grade']): $gc=$overallGrade['tdc_overall_grade']>=80?'var(--green)':($overallGrade['tdc_overall_grade']>=60?'var(--amber)':'var(--red)'); ?>
                <div style="font-size:12px">Overall Grade: <strong style="color:<?= $gc ?>"><?= $overallGrade['tdc_overall_grade'] ?>/100</strong></div>
                <?php endif; ?>
                <?php if($tdcAvgGrade): $gc=$tdcAvgGrade>=80?'var(--green)':($tdcAvgGrade>=60?'var(--amber)':'var(--red)'); ?><div style="font-size:12px">Session Avg: <strong style="color:<?= $gc ?>"><?= $tdcAvgGrade ?>/100</strong></div><?php endif; ?>
            </div>
            <?php else: ?><div style="font-size:11px;color:var(--muted);margin-top:4px"><?= 15-$tdcHours ?> more hours needed to unlock PDC</div><?php endif; ?>
        </div>
        <!-- PDC -->
        <div style="<?= !$tdcDone?'opacity:.4':'' ?>">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
                <div style="font-size:13px;font-weight:600">
                    <?php if(!$tdcDone): ?><i class="fa-solid fa-lock me-2" style="color:var(--red)"></i><?php endif; ?>
                    <span class="tag tag-green me-2">PDC</span> Practical Driving Course
                </div>
                <div style="font-size:12px;color:var(--muted)"><?= $pdcHours ?> hrs completed</div>
            </div>
            <?php if(!$tdcDone): ?><div style="font-size:11px;color:var(--red)">Complete TDC first</div>
            <?php else: ?><div class="prog-bar" style="height:12px"><div class="prog-fill" style="width:<?= min(100,round($pdcHours/8*100)) ?>%;background:var(--green)"></div></div><?php endif; ?>
        </div>
    </div>
</div>

<!-- Session log with grades -->
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-list-check"></i> Session History & Grades</div></div>
    <table class="tbl"><thead><tr><th>Date</th><th>Course</th><th>Session #</th><th>Instructor</th><th>Hours</th><th>Grade</th><th>Performance</th><th>Improvements</th></tr></thead><tbody>
    <?php $mySessions->data_seek(0); if($mySessions->num_rows===0): ?>
    <tr><td colspan="8" style="color:var(--muted);text-align:center;padding:24px">No session records yet.</td></tr>
    <?php else: while($ss=$mySessions->fetch_assoc()): $tc=$cColors[$ss['course_type']]??'tag-gray'; $gc=$ss['grade']?($ss['grade']>=80?'var(--green)':($ss['grade']>=60?'var(--amber)':'var(--red)')):''; ?>
    <tr>
        <td><?= date('M d, Y',strtotime($ss['session_date'])) ?></td>
        <td><span class="tag <?= $tc ?>"><?= htmlspecialchars($ss['course_type']) ?></span></td>
        <td>Day <?= $ss['session_number']??'—' ?></td>
        <td><?= htmlspecialchars($ss['instr_name']) ?></td>
        <td><?= $ss['hours_covered'] ?> hrs</td>
        <td><?php if($ss['grade']): ?>
            <strong style="color:<?= $gc ?>"><?= $ss['grade'] ?>/100</strong>
            <div class="grade-bar"><div class="grade-fill" style="width:<?= $ss['grade'] ?>%;background:<?= $gc ?>"></div></div>
        <?php else: ?><span style="color:var(--muted);font-size:11px">Not graded</span><?php endif; ?></td>
        <td><?php if($ss['performance_rating']): ?><span style="color:var(--amber)"><?= stars($ss['performance_rating']) ?></span><?php else: ?>—<?php endif; ?></td>
        <td style="font-size:11px;color:var(--muted)"><?= $ss['improvements']?nl2br(htmlspecialchars($ss['improvements'])):'—' ?></td>
    </tr>
    <?php endwhile; endif; ?>
    </tbody></table>
</div>
</div>

<!-- ══ NOTIFICATIONS ══════════════════════════════════════════ -->
<div id="section-messages" class="section <?= $activeSection==='messages'?'active':'' ?>">
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="fa-solid fa-bell"></i> Notifications</div>
        <form method="POST"><input type="hidden" name="action" value="mark_read_student">
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

<!-- ══ PROFILE ════════════════════════════════════════════════ -->
<div id="section-profile" class="section <?= $activeSection==='profile'?'active':'' ?>">
<?php
$hasCertNow=(int)q($conn,"SELECT COUNT(*) AS c FROM tdc_certificates WHERE student_id=$student_id")->fetch_assoc()['c'];
?>
<div class="panel mb-3">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-user"></i> My Profile</div></div>
    <div style="padding:20px;display:flex;gap:16px;align-items:center">
        <div style="width:60px;height:60px;border-radius:50%;background:var(--accent);display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:700;color:#fff;flex-shrink:0"><?= strtoupper(substr($student['fullname'],0,1)) ?></div>
        <div>
            <div style="font-size:18px;font-weight:700"><?= htmlspecialchars($student['fullname']) ?></div>
            <div style="font-size:12px;color:var(--muted)"><?= htmlspecialchars($student['email']) ?></div>
            <div style="font-size:11px;color:var(--muted);margin-top:2px">Member since <?= date('F Y',strtotime($student['created_at'])) ?></div>
            <?php if($hasCertNow): ?><div style="margin-top:8px"><span class="cert-badge"><i class="fa-solid fa-certificate"></i> TDC Certificate Holder</span></div><?php endif; ?>
        </div>
    </div>
</div>
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-pen"></i> Edit Profile</div></div>
    <div style="padding:20px">
    <form method="POST">
        <input type="hidden" name="action" value="update_profile">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
            <div>
                <label class="form-label">Full Name *</label>
                <input type="text" name="fullname" class="di" value="<?= htmlspecialchars($student['fullname']) ?>" required>
            </div>
            <div>
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="di" value="<?= htmlspecialchars($student['email']) ?>" required>
            </div>
            <div>
                <label class="form-label">Mobile Number</label>
                <input type="text" name="mobile" class="di" value="<?= htmlspecialchars($student['mobile']??'') ?>" placeholder="+63 9XX XXX XXXX">
            </div>
            <div>
                <label class="form-label">Address</label>
                <input type="text" name="address" class="di" value="<?= htmlspecialchars($student['address']??'') ?>" placeholder="City, Province">
            </div>
        </div>
        <button type="submit" class="be"><i class="fa-solid fa-save"></i> Save Profile</button>
        <span style="font-size:11px;color:var(--muted);margin-left:12px">To change password, use Forgot Password on the login page.</span>
    </form>
    </div>
</div>
</div>

</div><!-- /main -->

<!-- ══ BOOK MODAL ═════════════════════════════════════════════ -->
<div id="bookModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:200;align-items:center;justify-content:center">
<div style="background:var(--bg2);border:1px solid var(--border);border-radius:12px;width:480px;max-width:95vw;padding:24px">
    <div style="font-size:15px;font-weight:600;margin-bottom:16px"><i class="fa-solid fa-calendar-plus me-2" style="color:var(--accent)"></i>Book Appointment</div>
    <form method="POST" action="<?= $selfFile ?>?section=my_appointments">
    <input type="hidden" name="action" value="book_appointment">
    <input type="hidden" name="instructor_id" id="bm_instr_id">
    <input type="hidden" name="course_id" id="bm_course_id">
    <div id="bm_tdc_notice" style="display:none;margin-bottom:12px" class="flash flash-ok">
        <i class="fa-solid fa-calendar-week me-2"></i>TDC requires exactly <strong>15 hours</strong>. Start date must be a <strong>Saturday or Sunday</strong>.
    </div>
    <div style="margin-bottom:12px">
        <label class="form-label">Course</label>
        <div id="bm_course_name" style="font-size:13px;font-weight:600"></div>
    </div>
    <div style="margin-bottom:12px">
        <label class="form-label">Preferred Start Date * <span id="bm_day_hint" style="color:var(--amber);font-size:10px"></span></label>
        <input type="date" name="start_date" id="bm_start_date" class="di" required min="<?= date('Y-m-d',strtotime('+1 day')) ?>">
    </div>
    <div id="bm_hours_wrap" style="margin-bottom:12px">
        <label class="form-label">Number of Hours (PDC)</label>
        <input type="number" name="total_hours" class="di" min="1" max="40" value="8">
    </div>
    <div style="margin-bottom:12px">
        <label class="form-label">Note to Instructor (optional)</label>
        <textarea name="student_note" class="di" rows="2" placeholder="Any special requests…"></textarea>
    </div>
    <div style="display:flex;gap:8px;justify-content:flex-end">
        <button type="button" class="bds" onclick="closeBookModal()">Cancel</button>
        <button type="submit" class="be"><i class="fa-solid fa-paper-plane"></i> Send Request</button>
    </div>
    </form>
</div></div>

<!-- ══ RATE MODAL ═════════════════════════════════════════════ -->
<div id="rateModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:200;align-items:center;justify-content:center">
<div style="background:var(--bg2);border:1px solid var(--border);border-radius:12px;width:400px;max-width:95vw;padding:24px">
    <div style="font-size:15px;font-weight:600;margin-bottom:4px"><i class="fa-solid fa-star me-2" style="color:var(--amber)"></i>Rate Your Instructor</div>
    <div id="rate_instr_name" style="font-size:12px;color:var(--muted);margin-bottom:16px"></div>
    <form method="POST">
    <input type="hidden" name="action" value="rate_instructor">
    <input type="hidden" name="appointment_id" id="rm_appt_id">
    <div style="margin-bottom:16px">
        <label class="form-label">Your Rating *</label>
        <div class="stars-input">
            <?php for($i=5;$i>=1;$i--): ?>
            <input type="radio" name="rating" id="star<?= $i ?>" value="<?= $i ?>" <?= $i===5?'required':'' ?> style="display:none">
            <label for="star<?= $i ?>">★</label>
            <?php endfor; ?>
        </div>
    </div>
    <div style="margin-bottom:16px">
        <label class="form-label">Comment (optional)</label>
        <textarea name="comment" class="di" rows="3" placeholder="How was your experience?"></textarea>
    </div>
    <div style="display:flex;gap:8px;justify-content:flex-end">
        <button type="button" class="bds" onclick="closeRateModal()">Cancel</button>
        <button type="submit" class="be"><i class="fa-solid fa-paper-plane"></i> Submit</button>
    </div>
    </form>
</div></div>

<script>
const T={dashboard:'Dashboard',instructors:'Browse Instructors',view_instr:'Instructor Profile',
  my_appointments:'My Appointments',progress:'My Progress',messages:'Notifications',profile:'Profile'};

function showSection(n){
    document.querySelectorAll('.section').forEach(s=>s.classList.remove('active'));
    document.querySelectorAll('.nav-item').forEach(x=>x.classList.remove('active'));
    const el=document.getElementById('section-'+n);
    if(el)el.classList.add('active');
    document.getElementById('page-title-text').textContent=T[n]||'Dashboard';
    document.querySelectorAll('.nav-item').forEach(x=>{
        if(x.getAttribute('onclick')&&x.getAttribute('onclick').includes("'"+n+"'"))x.classList.add('active');
    });
    const u=new URL(window.location.href);
    u.searchParams.set('section',n);
    if(n!=='view_instr')u.searchParams.delete('view_instr');
    window.history.pushState({},'',u);
}

function viewInstructor(id){
    window.location.href='<?= $selfFile ?>?view_instr='+id+'&section=view_instr';
}

function handleSearch(v){
    const a=document.querySelector('.section.active');
    if(!a)return;
    a.querySelectorAll('.instr-card').forEach(c=>{
        c.style.display=c.textContent.toLowerCase().includes(v.toLowerCase())?'':'none';
    });
}

let _isTDCBook=false;
function openBookModal(instrId,courseId,courseType,pricePerHr,isTDC){
    _isTDCBook=isTDC;
    document.getElementById('bm_instr_id').value=instrId;
    document.getElementById('bm_course_id').value=courseId;
    document.getElementById('bm_course_name').textContent=courseType;
    document.getElementById('bm_tdc_notice').style.display=isTDC?'block':'none';
    document.getElementById('bm_hours_wrap').style.display=isTDC?'none':'block';
    document.getElementById('bm_day_hint').textContent=isTDC?'(Saturday or Sunday only)':'';
    const dateInput=document.getElementById('bm_start_date');
    dateInput.removeEventListener('change',validateTDCBookDay);
    if(isTDC)dateInput.addEventListener('change',validateTDCBookDay);
    document.getElementById('bookModal').style.display='flex';
}
function validateTDCBookDay(){
    const d=new Date(this.value+'T00:00:00');
    const day=d.getDay();
    if(day!==0&&day!==6){
        alert('TDC sessions must start on a Saturday or Sunday.');
        this.value='';
    }
}
function closeBookModal(){document.getElementById('bookModal').style.display='none';}

function openRateModal(apptId,instrName){
    document.getElementById('rm_appt_id').value=apptId;
    document.getElementById('rate_instr_name').textContent='Instructor: '+instrName;
    document.getElementById('rateModal').style.display='flex';
}
function closeRateModal(){document.getElementById('rateModal').style.display='none';}

['bookModal','rateModal'].forEach(id=>{
    document.getElementById(id).addEventListener('click',function(e){if(e.target===this)this.style.display='none';});
});
</script>
</body>
</html>
