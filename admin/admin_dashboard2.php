<?php
session_start();
if(!isset($_SESSION['admin_id'])){header('Location: admin_login.php');exit;}
$admin_id=(int)$_SESSION['admin_id'];
require_once '../includes/db.php';

$today=date('Y-m-d');
$selfFile=basename(__FILE__);

function q($c,$s){return $c->query($s);}
function esc($c,$v){return $c->real_escape_string($v);}
$cColors=['TDC Face-to-Face'=>'tag-amber','TDC Online'=>'tag-blue',
          'PDC Car Manual'=>'tag-green','PDC Car Automatic'=>'tag-teal',
          'PDC Motorcycle Manual'=>'tag-coral','PDC Motorcycle Automatic'=>'tag-purple'];

$activeSection='dashboard';
if(!empty($_GET['section']))$activeSection=preg_replace('/[^a-z_]/','',$_GET['section']);
$viewStudentId=(int)($_GET['view_student']??0);
$viewInstructorId=(int)($_GET['view_instructor']??0);

// ── POST ──────────────────────────────────────────────────────
if($_SERVER['REQUEST_METHOD']==='POST'){
    $action=$_POST['action']??'';

    if($action==='add_instructor'){
        $name=esc($conn,$_POST['fullname']??'');
        $email=esc($conn,$_POST['email']??'');
        $pass=password_hash($_POST['password']??'', PASSWORD_BCRYPT);
        $conn->query("INSERT INTO instructors (fullname,email,password) VALUES ('$name','$email','$pass')");
        $_SESSION['flash']='Instructor added.';
        header("Location: $selfFile?section=instructors"); exit;
    }
    if($action==='delete_instructor'){
        $id=(int)($_POST['instructor_id']??0);
        $conn->query("DELETE FROM instructors WHERE instructor_id=$id");
        $_SESSION['flash']='Instructor deleted.';
        header("Location: $selfFile?section=instructors"); exit;
    }
    if($action==='delete_student'){
        $id=(int)($_POST['student_id']??0);
        $conn->query("DELETE FROM students WHERE student_id=$id");
        $_SESSION['flash']='Student deleted.';
        header("Location: $selfFile?section=students"); exit;
    }
    if($action==='delete_appointment'){
        $appt_id=(int)($_POST['appointment_id']??0);
        $conn->query("DELETE FROM appointments WHERE appointment_id=$appt_id");
        $_SESSION['flash']='Appointment deleted.';
        header("Location: $selfFile?section=appointments"); exit;
    }
    if($action==='force_complete'){
        $appt_id=(int)($_POST['appointment_id']??0);
        $aRow=q($conn,"SELECT * FROM appointments WHERE appointment_id=$appt_id")->fetch_assoc();
        if($aRow){
            $conn->query("UPDATE appointments SET status='completed',completed_at=NOW() WHERE appointment_id=$appt_id");
            $conn->query("INSERT INTO notifications (user_id,user_type,message,type) VALUES ({$aRow['student_id']},'student','Your appointment has been marked complete by admin.','admin_action')");
        }
        header("Location: $selfFile?section=appointments"); exit;
    }
    if($action==='announce'){
        $msg=esc($conn,$_POST['message']??'');
        $sendTo=in_array($_POST['send_to']??'',['students','instructors','everyone'])?$_POST['send_to']:'students';
        $count=0;
        if($sendTo==='students'||$sendTo==='everyone'){
            $res=q($conn,"SELECT student_id FROM students WHERE is_verified=1");
            while($s=$res->fetch_assoc()){
                $conn->query("INSERT INTO notifications (user_id,user_type,message,type) VALUES ({$s['student_id']},'student','📢 Admin: $msg','announcement')");
                $count++;
            }
        }
        if($sendTo==='instructors'||$sendTo==='everyone'){
            $res=q($conn,"SELECT instructor_id FROM instructors");
            while($i=$res->fetch_assoc()){
                $conn->query("INSERT INTO notifications (user_id,user_type,message,type) VALUES ({$i['instructor_id']},'instructor','📢 Admin: $msg','announcement')");
                $count++;
            }
        }
        $_SESSION['flash']="Announcement sent to {$count} recipient(s).";
        header("Location: $selfFile?section=communication"); exit;
    }
    if($action==='mark_admin_read'){
        $conn->query("UPDATE notifications SET is_read=1 WHERE user_id=$admin_id AND user_type='admin'");
        header("Location: $selfFile?section=messages"); exit;
    }
}

// ── stats ─────────────────────────────────────────────────────
$totalStudents=(int)q($conn,"SELECT COUNT(*) AS c FROM students")->fetch_assoc()['c'];
$totalInstructors=(int)q($conn,"SELECT COUNT(*) AS c FROM instructors")->fetch_assoc()['c'];
$pendingAppts=(int)q($conn,"SELECT COUNT(*) AS c FROM appointments WHERE status='pending'")->fetch_assoc()['c'];
$completedAppts=(int)q($conn,"SELECT COUNT(*) AS c FROM appointments WHERE status='completed'")->fetch_assoc()['c'];
$certsIssued=(int)q($conn,"SELECT COUNT(*) AS c FROM tdc_certificates")->fetch_assoc()['c'];
$avgRating=q($conn,"SELECT ROUND(AVG(rating),1) AS r FROM instructor_ratings")->fetch_assoc()['r'];

// Analytics
$todayLogins=(int)q($conn,"SELECT (SELECT COUNT(*) FROM student_login_history WHERE DATE(login_datetime)='$today' AND status='success') + (SELECT COUNT(*) FROM instructor_login_history WHERE DATE(login_time)='$today' AND login_status='success') + (SELECT COUNT(*) FROM admin_login_history WHERE DATE(login_time)='$today' AND login_status='success') AS c")->fetch_assoc()['c'];
$monthLogins=(int)q($conn,"SELECT (SELECT COUNT(*) FROM student_login_history WHERE YEAR(login_datetime)=YEAR(NOW()) AND MONTH(login_datetime)=MONTH(NOW()) AND status='success') + (SELECT COUNT(*) FROM instructor_login_history WHERE YEAR(login_time)=YEAR(NOW()) AND MONTH(login_time)=MONTH(NOW()) AND login_status='success') AS c")->fetch_assoc()['c'];
$newStudentsMonth=(int)q($conn,"SELECT COUNT(*) AS c FROM students WHERE YEAR(created_at)=YEAR(NOW()) AND MONTH(created_at)=MONTH(NOW())")->fetch_assoc()['c'];
$revenueMonth=q($conn,"SELECT COALESCE(SUM(total_price),0) AS r FROM appointments WHERE status IN('completed','in_progress') AND YEAR(requested_at)=YEAR(NOW()) AND MONTH(requested_at)=MONTH(NOW())")->fetch_assoc()['r'];

// Top & low rated instructors
$topInstructors=q($conn,"SELECT i.instructor_id,i.fullname,ROUND(AVG(ir.rating),1) AS avg_r,COUNT(ir.rating_id) AS cnt,(SELECT COUNT(*) FROM appointments WHERE instructor_id=i.instructor_id AND status='completed') AS done FROM instructor_ratings ir JOIN instructors i ON i.instructor_id=ir.instructor_id GROUP BY i.instructor_id ORDER BY avg_r DESC,cnt DESC LIMIT 5");
$lowInstructors=q($conn,"SELECT i.instructor_id,i.fullname,ROUND(AVG(ir.rating),1) AS avg_r,COUNT(ir.rating_id) AS cnt FROM instructor_ratings ir JOIN instructors i ON i.instructor_id=ir.instructor_id GROUP BY i.instructor_id HAVING cnt>=1 ORDER BY avg_r ASC,cnt DESC LIMIT 5");

// Top & low students by avg grade this month
$thisMonth=date('Y-m');
$topStudents=q($conn,"SELECT s.student_id,s.fullname,ROUND(AVG(sess.grade),1) AS avg_grade,COUNT(sess.session_id) AS sess_cnt FROM sessions sess JOIN students s ON s.student_id=sess.student_id WHERE sess.grade IS NOT NULL AND DATE_FORMAT(sess.session_date,'%Y-%m')='$thisMonth' GROUP BY s.student_id ORDER BY avg_grade DESC LIMIT 5");
$lowStudents=q($conn,"SELECT s.student_id,s.fullname,ROUND(AVG(sess.grade),1) AS avg_grade,COUNT(sess.session_id) AS sess_cnt FROM sessions sess JOIN students s ON s.student_id=sess.student_id WHERE sess.grade IS NOT NULL AND DATE_FORMAT(sess.session_date,'%Y-%m')='$thisMonth' GROUP BY s.student_id ORDER BY avg_grade ASC LIMIT 5");

$flash=$_SESSION['flash']??null; unset($_SESSION['flash']);
$scmS=['pending'=>'bkp','approved'=>'bksu','in_progress'=>'bkb','completed'=>'bkg','rejected'=>'bkr','cancelled'=>'bkd'];
$scmL=['pending'=>'Pending','approved'=>'Approved','in_progress'=>'In Progress','completed'=>'Completed','rejected'=>'Rejected','cancelled'=>'Cancelled'];
$uploadDir='../assets/uploads/certificates/';

// ── Analytics chart data ──────────────────────────────────────
$barLabels=[]; $barAppts=[];
for($m=5;$m>=0;$m--){
    $barLabels[]=date('M Y',strtotime("-$m months"));
    $ym=date('Y-m',strtotime("-$m months"));
    $barAppts[]=(int)q($conn,"SELECT COUNT(*) AS c FROM appointments WHERE DATE_FORMAT(requested_at,'%Y-%m')='$ym'")->fetch_assoc()['c'];
}
$doughRows=q($conn,"SELECT ic.course_type,COUNT(*) AS c FROM appointments a JOIN instructor_courses ic ON ic.course_id=a.course_id GROUP BY ic.course_type ORDER BY c DESC");
$dLabels=[]; $dData=[];
while($dr=$doughRows->fetch_assoc()){$dLabels[]=$dr['course_type'];$dData[]=(int)$dr['c'];}

// Failed attempts per student / instructor
$sFailMap=[];
$sfRes=q($conn,"SELECT student_id,COUNT(*) AS fc FROM student_login_history WHERE status='failed' GROUP BY student_id");
while($sf=$sfRes->fetch_assoc()) $sFailMap[(int)$sf['student_id']]=(int)$sf['fc'];
$iFailMap=[];
$ifRes=q($conn,"SELECT instructor_id,COUNT(*) AS fc FROM instructor_login_history WHERE login_status='failed' GROUP BY instructor_id");
while($inf=$ifRes->fetch_assoc()) $iFailMap[(int)$inf['instructor_id']]=(int)$inf['fc'];

// Recipient counts for announcements send-to selector
$stuRecipCount=(int)q($conn,"SELECT COUNT(*) AS c FROM students WHERE is_verified=1")->fetch_assoc()['c'];
$insRecipCount=(int)q($conn,"SELECT COUNT(*) AS c FROM instructors")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Dashboard – ABROT</title>
<link href="../assets/img/logo.png" rel="icon">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root{--bg:#0f1117;--bg2:#181c27;--bg3:#1e2436;--border:#2a3147;--accent:#7c6af7;--green:#3cb97a;--amber:#f59e0b;--red:#e55;--text:#e2e5f0;--muted:#8490a8}
*{box-sizing:border-box;margin:0;padding:0}
body{background:var(--bg);color:var(--text);font-family:'Segoe UI',sans-serif;min-height:100vh;display:flex}
.sidebar{width:220px;background:var(--bg2);border-right:1px solid var(--border);display:flex;flex-direction:column;padding:20px 0;flex-shrink:0;position:fixed;height:100vh;overflow-y:auto;z-index:100}
.brand{padding:0 18px 20px;font-size:15px;font-weight:700;color:var(--accent);border-bottom:1px solid var(--border);margin-bottom:12px}
.nav-item{display:flex;align-items:center;gap:10px;padding:10px 18px;color:var(--muted);font-size:13px;cursor:pointer;text-decoration:none;border:none;background:none;width:100%;text-align:left;transition:.15s}
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
@media(max-width:900px){.duo-grid{grid-template-columns:1fr}}
.tag{display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500}
.tag-amber{background:rgba(245,158,11,.15);color:#f59e0b}
.tag-blue{background:rgba(59,130,246,.15);color:#60a5fa}
.tag-green{background:rgba(59,185,122,.15);color:#3cb97a}
.tag-teal{background:rgba(20,184,166,.15);color:#2dd4bf}
.tag-coral{background:rgba(239,68,68,.15);color:#f87171}
.tag-purple{background:rgba(124,106,247,.15);color:#a78bfa}
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
.prog-bar{height:8px;background:var(--border);border-radius:4px;overflow:hidden}
.prog-fill{height:100%;background:var(--accent);border-radius:4px}
.cert-badge{background:rgba(245,158,11,.12);border:1px solid rgba(245,158,11,.3);color:#f59e0b;padding:4px 10px;border-radius:20px;font-size:11px;display:inline-flex;align-items:center;gap:5px}
.rank-medal{font-size:14px;width:22px;text-align:center}
.grade-bar{height:6px;background:var(--border);border-radius:3px;overflow:hidden;margin-top:3px}
.grade-fill{height:100%;border-radius:3px}
.section-divider{font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;padding:8px 18px 4px;margin-top:4px}
</style>
</head>
<body>

<!-- showSection must be defined BEFORE sidebar onclick attributes fire -->
<script>
var T={dashboard:'Dashboard',students:'Students',instructors:'Instructors',
  appointments:'Appointments',certificates:'Certificates',
  reports:'Reports',communication:'Announcements',
  student_profile:'Student Profile',instructor_profile:'Instructor Profile'};

function showSection(n){
    document.querySelectorAll('.section').forEach(function(el){
        el.classList.remove('active');
        el.style.display='none';
    });
    var el=document.getElementById('section-'+n);
    if(el){ el.classList.add('active'); el.style.display='block'; }
    document.querySelectorAll('.nav-item').forEach(function(nav){
        nav.classList.remove('active');
        var oc=nav.getAttribute('onclick')||'';
        if(oc.indexOf("'"+n+"'")>-1) nav.classList.add('active');
    });
    var ti=document.getElementById('page-title-text');
    if(ti) ti.textContent=T[n]||n;
    if(n==='dashboard') setTimeout(initCharts,80);
    return false;
}
</script>

<div class="sidebar">
    <div class="brand"><i class="fa-solid fa-shield-halved me-2"></i>ABROT Admin</div>
    <a class="nav-item <?= $activeSection==='dashboard'?'active':'' ?>" onclick="showSection('dashboard')" href="javascript:void(0)">
        <i class="fa-solid fa-gauge"></i> Dashboard</a>
    <div class="section-divider">Management</div>
    <a class="nav-item <?= in_array($activeSection,['students','student_profile'])?'active':'' ?>" onclick="showSection('students')" href="javascript:void(0)">
        <i class="fa-solid fa-user-graduate"></i> Students</a>
    <a class="nav-item <?= in_array($activeSection,['instructors','instructor_profile'])?'active':'' ?>" onclick="showSection('instructors')" href="javascript:void(0)">
        <i class="fa-solid fa-chalkboard-user"></i> Instructors</a>
    <a class="nav-item <?= $activeSection==='appointments'?'active':'' ?>" onclick="showSection('appointments')" href="javascript:void(0)">
        <i class="fa-solid fa-calendar-check"></i> Appointments
        <?php if($pendingAppts>0): ?><span class="tag tag-amber"><?= $pendingAppts ?></span><?php endif; ?></a>
    <a class="nav-item <?= $activeSection==='certificates'?'active':'' ?>" onclick="showSection('certificates')" href="javascript:void(0)">
        <i class="fa-solid fa-certificate"></i> Certificates</a>
    <div class="section-divider">Insights</div>
    <a class="nav-item <?= $activeSection==='reports'?'active':'' ?>" onclick="showSection('reports')" href="javascript:void(0)">
        <i class="fa-solid fa-chart-bar"></i> Reports</a>
    <a class="nav-item <?= $activeSection==='communication'?'active':'' ?>" onclick="showSection('communication')" href="javascript:void(0)">
        <i class="fa-solid fa-bullhorn"></i> Announcements</a>
    <div style="flex:1"></div>
    <a class="nav-item" href="admin_logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
</div>

<div class="main">
<div class="page-header">
    <div class="page-title" id="page-title-text">Dashboard</div>
    <div style="font-size:12px;color:var(--muted)"><?= $today ?></div>
</div>

<?php if($flash): ?><div class="flash flash-ok"><i class="fa-solid fa-circle-check me-2"></i><?= htmlspecialchars($flash) ?></div><?php endif; ?>

<!-- ══ DASHBOARD ══════════════════════════════════════════════ -->
<div id="section-dashboard" class="section <?= $activeSection==='dashboard'?'active':'' ?>">
<div class="stats-row">
    <div class="stat-card" onclick="showSection('students')">
        <div class="stat-num"><?= $totalStudents ?></div><div class="stat-label">Total Students</div>
    </div>
    <div class="stat-card" onclick="showSection('instructors')">
        <div class="stat-num"><?= $totalInstructors ?></div><div class="stat-label">Instructors</div>
    </div>
    <div class="stat-card" onclick="showSection('appointments')">
        <div class="stat-num" style="color:var(--amber)"><?= $pendingAppts ?></div><div class="stat-label">Pending Appts</div>
    </div>
    <div class="stat-card" onclick="showSection('appointments')">
        <div class="stat-num" style="color:var(--green)"><?= $completedAppts ?></div><div class="stat-label">Completed</div>
    </div>
    <div class="stat-card" onclick="showSection('certificates')">
        <div class="stat-num" style="color:var(--amber)"><?= $certsIssued ?></div><div class="stat-label">TDC Certs</div>
    </div>
    <div class="stat-card" onclick="showSection('dashboard')">
        <div class="stat-num" style="color:var(--green)"><?= $todayLogins ?></div><div class="stat-label">Today's Logins</div>
    </div>
</div>

<!-- Top & Low Instructors -->
<div class="duo-grid">
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-trophy" style="color:var(--amber)"></i> Top 5 Rated Instructors</div>
        <a class="bds" onclick="showSection('instructors')" href="javascript:void(0)" style="font-size:11px">View All</a>
    </div>
    <table class="tbl"><thead><tr><th>#</th><th>Instructor</th><th>Rating</th><th>Reviews</th></tr></thead><tbody>
    <?php $topInstructors->data_seek(0); $rank=1; if($topInstructors->num_rows===0): ?>
    <tr><td colspan="4" style="color:var(--muted);text-align:center;padding:20px">No ratings yet.</td></tr>
    <?php else: while($ti=$topInstructors->fetch_assoc()): $medals=['🥇','🥈','🥉','4️⃣','5️⃣']; ?>
    <tr>
        <td><span class="rank-medal"><?= $medals[$rank-1]??$rank ?></span></td>
        <td><a href="<?= $selfFile ?>?section=instructor_profile&view_instructor=<?= $ti['instructor_id'] ?>" style="color:var(--text);text-decoration:none"><?= htmlspecialchars($ti['fullname']) ?></a></td>
        <td style="color:var(--amber)">★ <?= $ti['avg_r'] ?></td>
        <td><?= $ti['cnt'] ?></td>
    </tr>
    <?php $rank++; endwhile; endif; ?>
    </tbody></table>
</div>
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-arrow-trend-down" style="color:var(--red)"></i> Low Rated Instructors</div></div>
    <table class="tbl"><thead><tr><th>#</th><th>Instructor</th><th>Rating</th><th>Reviews</th></tr></thead><tbody>
    <?php $lowInstructors->data_seek(0); $rank=1; if($lowInstructors->num_rows===0): ?>
    <tr><td colspan="4" style="color:var(--muted);text-align:center;padding:20px">No ratings yet.</td></tr>
    <?php else: while($li=$lowInstructors->fetch_assoc()): ?>
    <tr>
        <td><span style="color:var(--muted);font-size:12px"><?= $rank ?></span></td>
        <td><a href="<?= $selfFile ?>?section=instructor_profile&view_instructor=<?= $li['instructor_id'] ?>" style="color:var(--text);text-decoration:none"><?= htmlspecialchars($li['fullname']) ?></a></td>
        <td style="color:var(--red)">★ <?= $li['avg_r'] ?></td>
        <td><?= $li['cnt'] ?></td>
    </tr>
    <?php $rank++; endwhile; endif; ?>
    </tbody></table>
</div>
</div>

<!-- Top & Low Students This Month -->
<div class="duo-grid">
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-star" style="color:var(--amber)"></i> Top 5 Students – <?= date('F Y') ?></div>
        <small style="color:var(--muted);font-size:10px">Resets monthly</small>
    </div>
    <table class="tbl"><thead><tr><th>#</th><th>Student</th><th>Avg Grade</th><th>Sessions</th></tr></thead><tbody>
    <?php $topStudents->data_seek(0); $rank=1; if($topStudents->num_rows===0): ?>
    <tr><td colspan="4" style="color:var(--muted);text-align:center;padding:20px">No grades this month.</td></tr>
    <?php else: while($ts=$topStudents->fetch_assoc()): $medals=['🥇','🥈','🥉','4️⃣','5️⃣']; $gc=$ts['avg_grade']>=80?'var(--green)':($ts['avg_grade']>=60?'var(--amber)':'var(--red)'); ?>
    <tr>
        <td><span class="rank-medal"><?= $medals[$rank-1]??$rank ?></span></td>
        <td><a href="<?= $selfFile ?>?section=student_profile&view_student=<?= $ts['student_id'] ?>" style="color:var(--text);text-decoration:none"><?= htmlspecialchars($ts['fullname']) ?></a></td>
        <td style="color:<?= $gc ?>;font-weight:600"><?= $ts['avg_grade'] ?>/100</td>
        <td><?= $ts['sess_cnt'] ?></td>
    </tr>
    <?php $rank++; endwhile; endif; ?>
    </tbody></table>
</div>
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-arrow-trend-down" style="color:var(--red)"></i> Low Performing Students – <?= date('F Y') ?></div>
        <small style="color:var(--muted);font-size:10px">Resets monthly</small>
    </div>
    <table class="tbl"><thead><tr><th>#</th><th>Student</th><th>Avg Grade</th><th>Sessions</th></tr></thead><tbody>
    <?php $lowStudents->data_seek(0); $rank=1; if($lowStudents->num_rows===0): ?>
    <tr><td colspan="4" style="color:var(--muted);text-align:center;padding:20px">No grades this month.</td></tr>
    <?php else: while($ls=$lowStudents->fetch_assoc()): ?>
    <tr>
        <td><span style="color:var(--muted);font-size:12px"><?= $rank ?></span></td>
        <td><a href="<?= $selfFile ?>?section=student_profile&view_student=<?= $ls['student_id'] ?>" style="color:var(--text);text-decoration:none"><?= htmlspecialchars($ls['fullname']) ?></a></td>
        <td style="color:var(--red);font-weight:600"><?= $ls['avg_grade'] ?>/100</td>
        <td><?= $ls['sess_cnt'] ?></td>
    </tr>
    <?php $rank++; endwhile; endif; ?>
    </tbody></table>
</div>
</div>

<!-- Recent appointments -->
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-clock"></i> Recent Appointments</div>
        <a class="be" onclick="showSection('appointments')" href="javascript:void(0)" style="font-size:11px">View All</a>
    </div>
    <table class="tbl"><thead><tr><th>Student</th><th>Instructor</th><th>Course</th><th>Start Date</th><th>Status</th></tr></thead><tbody>
    <?php $recentA=q($conn,"SELECT a.*,s.fullname AS sname,i.fullname AS iname,ic.course_type FROM appointments a JOIN students s ON s.student_id=a.student_id JOIN instructors i ON i.instructor_id=a.instructor_id JOIN instructor_courses ic ON ic.course_id=a.course_id ORDER BY a.requested_at DESC LIMIT 8");
    if($recentA->num_rows===0): ?><tr><td colspan="5" style="color:var(--muted);text-align:center;padding:20px">No appointments yet.</td></tr>
    <?php else: while($ra=$recentA->fetch_assoc()): $tc=$cColors[$ra['course_type']]??'tag-gray'; ?>
    <tr>
        <td><?= htmlspecialchars($ra['sname']) ?></td>
        <td><?= htmlspecialchars($ra['iname']) ?></td>
        <td><span class="tag <?= $tc ?>"><?= htmlspecialchars($ra['course_type']) ?></span></td>
        <td><?= date('M d, Y',strtotime($ra['start_date'])) ?></td>
        <td><span class="<?= $scmS[$ra['status']]??'bkd' ?>"><?= $scmL[$ra['status']]??$ra['status'] ?></span></td>
    </tr>
    <?php endwhile; endif; ?>
    </tbody></table>
</div>

<!-- ══ ANALYTICS (embedded in Dashboard) ════════════════════ -->
<div style="font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin:20px 0 10px;padding-bottom:6px;border-bottom:1px solid var(--border)">
    <i class="fa-solid fa-chart-line me-2" style="color:var(--accent)"></i>Analytics
</div>
<div class="analytics-grid" style="margin-bottom:16px">
    <div class="analytic-card"><div class="analytic-num" style="color:var(--accent)"><?= $todayLogins ?></div><div class="analytic-label"><i class="fa-solid fa-right-to-bracket me-1"></i>Today's Logins</div><div class="analytic-sub">All users</div></div>
    <div class="analytic-card"><div class="analytic-num" style="color:var(--green)"><?= $monthLogins ?></div><div class="analytic-label"><i class="fa-solid fa-calendar me-1"></i>Monthly Logins</div><div class="analytic-sub"><?= date('F Y') ?></div></div>
    <div class="analytic-card"><div class="analytic-num" style="color:var(--amber)"><?= $newStudentsMonth ?></div><div class="analytic-label"><i class="fa-solid fa-user-plus me-1"></i>New Students</div><div class="analytic-sub">This month</div></div>
    <div class="analytic-card"><div class="analytic-num" style="color:var(--green)">₱<?= number_format($revenueMonth,0) ?></div><div class="analytic-label"><i class="fa-solid fa-peso-sign me-1"></i>Revenue</div><div class="analytic-sub">This month</div></div>
    <div class="analytic-card"><div class="analytic-num" style="color:var(--accent)"><?= $totalStudents ?></div><div class="analytic-label"><i class="fa-solid fa-users me-1"></i>Total Students</div><div class="analytic-sub">Registered</div></div>
    <div class="analytic-card"><div class="analytic-num" style="color:var(--amber)"><?= $certsIssued ?></div><div class="analytic-label"><i class="fa-solid fa-certificate me-1"></i>Certs Issued</div><div class="analytic-sub">TDC total</div></div>
</div>

<div class="duo-grid" style="margin-bottom:16px">
    <div class="panel">
        <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-chart-bar me-1"></i> Appointments – Last 6 Months</div></div>
        <div style="padding:16px;height:220px;position:relative"><canvas id="chartBarAppts"></canvas></div>
    </div>
    <div class="panel">
        <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-chart-pie me-1"></i> Course Distribution</div></div>
        <div style="padding:16px;display:flex;align-items:center;gap:16px;height:220px">
            <div style="flex:0 0 160px;height:160px;position:relative"><canvas id="chartDoughCourse"></canvas></div>
            <div id="doughLegend" style="flex:1;font-size:11px;line-height:2.2"></div>
        </div>
    </div>
</div>

<div class="duo-grid" style="margin-bottom:16px">
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-user-graduate"></i> Student Login Activity</div></div>
    <div style="overflow-x:auto">
    <table class="tbl"><thead><tr><th>Student</th><th>IP</th><th>Device</th><th>Status</th><th>⚠ Failed</th><th>Time</th></tr></thead><tbody>
    <?php $sLogins=q($conn,"SELECT slh.*,s.fullname FROM student_login_history slh JOIN students s ON s.student_id=slh.student_id ORDER BY slh.login_datetime DESC LIMIT 15");
    while($sl=$sLogins->fetch_assoc()):
        $ua=$sl['device_info']??''; $isMob=(bool)preg_match('/Mobile|Android|iPhone|iPad/i',$ua);
        $browser='Other';
        if(stripos($ua,'Edg')!==false)$browser='Edge';
        elseif(stripos($ua,'Chrome')!==false)$browser='Chrome';
        elseif(stripos($ua,'Firefox')!==false)$browser='Firefox';
        elseif(stripos($ua,'Safari')!==false)$browser='Safari';
        $fc=$sFailMap[(int)$sl['student_id']]??0;
    ?>
    <tr>
        <td style="font-size:12px"><?= htmlspecialchars($sl['fullname']) ?></td>
        <td style="font-size:10px;color:var(--muted);font-family:monospace"><?= htmlspecialchars($sl['ip_address']??'—') ?></td>
        <td style="font-size:10px;color:var(--muted)"><?= $isMob?'<i class="fa-solid fa-mobile-screen"></i>':'<i class="fa-solid fa-desktop"></i>' ?> <?= $browser ?></td>
        <td><?= $sl['status']==='success'?'<span class="bkg" style="font-size:10px">✓ OK</span>':'<span class="bkr" style="font-size:10px">✗ Fail</span>' ?></td>
        <td style="text-align:center"><?php if($fc>0): ?><span style="background:rgba(229,85,85,.15);color:var(--red);border-radius:8px;padding:1px 8px;font-size:11px;font-weight:600"><?= $fc ?></span><?php else: ?>—<?php endif; ?></td>
        <td style="font-size:10px;color:var(--muted);white-space:nowrap"><?= date('M d, g:i A',strtotime($sl['login_datetime'])) ?></td>
    </tr>
    <?php endwhile; ?>
    </tbody></table></div>
</div>
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-chalkboard-user"></i> Instructor Login Activity</div></div>
    <div style="overflow-x:auto">
    <table class="tbl"><thead><tr><th>Instructor</th><th>IP</th><th>City</th><th>Status</th><th>⚠ Failed</th><th>Time</th></tr></thead><tbody>
    <?php $iLogins=q($conn,"SELECT ilh.*,i.fullname FROM instructor_login_history ilh JOIN instructors i ON i.instructor_id=ilh.instructor_id ORDER BY ilh.login_time DESC LIMIT 15");
    while($il=$iLogins->fetch_assoc()): $fc=$iFailMap[(int)$il['instructor_id']]??0; ?>
    <tr>
        <td style="font-size:12px"><?= htmlspecialchars($il['fullname']) ?></td>
        <td style="font-size:10px;color:var(--muted);font-family:monospace"><?= htmlspecialchars($il['ip_address']??'—') ?></td>
        <td style="font-size:10px;color:var(--muted)"><?= $il['city']?htmlspecialchars($il['city']):'—' ?></td>
        <td><?= $il['login_status']==='success'?'<span class="bkg" style="font-size:10px">✓ OK</span>':'<span class="bkr" style="font-size:10px">✗ Fail</span>' ?></td>
        <td style="text-align:center"><?php if($fc>0): ?><span style="background:rgba(229,85,85,.15);color:var(--red);border-radius:8px;padding:1px 8px;font-size:11px;font-weight:600"><?= $fc ?></span><?php else: ?>—<?php endif; ?></td>
        <td style="font-size:10px;color:var(--muted);white-space:nowrap"><?= date('M d, g:i A',strtotime($il['login_time'])) ?></td>
    </tr>
    <?php endwhile; ?>
    </tbody></table></div>
</div>
</div>

<!-- ══ RANKINGS (embedded in Dashboard) ═════════════════════ -->
<div style="font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin:4px 0 10px;padding-bottom:6px;border-bottom:1px solid var(--border)">
    <i class="fa-solid fa-ranking-star me-2" style="color:var(--amber)"></i>Rankings – <?= date('F Y') ?>
    <span style="font-size:10px;font-weight:400;margin-left:8px">Student grades reset monthly</span>
</div>
<div class="duo-grid">
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-trophy" style="color:var(--amber)"></i> Top 5 Highest Rated Instructors</div><span style="font-size:10px;color:var(--muted)">All-time</span></div>
    <table class="tbl"><thead><tr><th>#</th><th>Instructor</th><th>Avg Rating</th><th>Reviews</th></tr></thead><tbody>
    <?php $topInstructors->data_seek(0); $rank=1; $medals=['🥇','🥈','🥉','4️⃣','5️⃣'];
    if($topInstructors->num_rows===0): ?><tr><td colspan="4" style="color:var(--muted);text-align:center;padding:16px">No ratings yet.</td></tr>
    <?php else: while($ti=$topInstructors->fetch_assoc()): ?>
    <tr>
        <td><?= $medals[$rank-1]??$rank ?></td>
        <td><a href="<?= $selfFile ?>?section=instructor_profile&view_instructor=<?= $ti['instructor_id'] ?>" style="color:var(--text);text-decoration:none"><?= htmlspecialchars($ti['fullname']) ?></a></td>
        <td><span style="color:var(--amber);font-weight:600">★ <?= $ti['avg_r'] ?></span><div style="height:4px;background:var(--border);border-radius:2px;margin-top:3px;width:70px"><div style="height:4px;background:var(--amber);border-radius:2px;width:<?= round($ti['avg_r']/5*100) ?>%"></div></div></td>
        <td style="color:var(--muted)"><?= $ti['cnt'] ?></td>
    </tr>
    <?php $rank++; endwhile; endif; ?>
    </tbody></table>
</div>
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-arrow-trend-down" style="color:var(--red)"></i> Top 5 Lowest Rated Instructors</div><span style="font-size:10px;color:var(--muted)">Min 1 review</span></div>
    <table class="tbl"><thead><tr><th>#</th><th>Instructor</th><th>Avg Rating</th><th>Reviews</th></tr></thead><tbody>
    <?php $lowInstructors->data_seek(0); $rank=1;
    if($lowInstructors->num_rows===0): ?><tr><td colspan="4" style="color:var(--muted);text-align:center;padding:16px">No ratings yet.</td></tr>
    <?php else: while($li=$lowInstructors->fetch_assoc()): ?>
    <tr>
        <td style="color:var(--muted)"><?= $rank ?></td>
        <td><a href="<?= $selfFile ?>?section=instructor_profile&view_instructor=<?= $li['instructor_id'] ?>" style="color:var(--text);text-decoration:none"><?= htmlspecialchars($li['fullname']) ?></a></td>
        <td><span style="color:var(--red);font-weight:600">★ <?= $li['avg_r'] ?></span><div style="height:4px;background:var(--border);border-radius:2px;margin-top:3px;width:70px"><div style="height:4px;background:var(--red);border-radius:2px;width:<?= round($li['avg_r']/5*100) ?>%"></div></div></td>
        <td style="color:var(--muted)"><?= $li['cnt'] ?></td>
    </tr>
    <?php $rank++; endwhile; endif; ?>
    </tbody></table>
</div>
</div>
<div class="duo-grid">
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-star" style="color:var(--amber)"></i> Top 5 Highest Student Grades</div><small style="color:var(--muted);font-size:10px">Resets monthly</small></div>
    <table class="tbl"><thead><tr><th>#</th><th>Student</th><th>Avg Grade</th><th>Sessions</th></tr></thead><tbody>
    <?php $topStudents->data_seek(0); $rank=1;
    if($topStudents->num_rows===0): ?><tr><td colspan="4" style="color:var(--muted);text-align:center;padding:16px">No grades this month.</td></tr>
    <?php else: while($ts=$topStudents->fetch_assoc()): $gc=$ts['avg_grade']>=80?'var(--green)':($ts['avg_grade']>=60?'var(--amber)':'var(--red)'); ?>
    <tr>
        <td><?= $medals[$rank-1]??$rank ?></td>
        <td><a href="<?= $selfFile ?>?section=student_profile&view_student=<?= $ts['student_id'] ?>" style="color:var(--text);text-decoration:none"><?= htmlspecialchars($ts['fullname']) ?></a></td>
        <td><span style="color:<?= $gc ?>;font-weight:600"><?= $ts['avg_grade'] ?>/100</span><div class="grade-bar" style="width:80px;margin-top:3px"><div class="grade-fill" style="width:<?= $ts['avg_grade'] ?>%;background:<?= $gc ?>"></div></div></td>
        <td style="color:var(--muted)"><?= $ts['sess_cnt'] ?></td>
    </tr>
    <?php $rank++; endwhile; endif; ?>
    </tbody></table>
</div>
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-arrow-trend-down" style="color:var(--red)"></i> Top 5 Lowest Student Grades</div><small style="color:var(--muted);font-size:10px">Resets monthly</small></div>
    <table class="tbl"><thead><tr><th>#</th><th>Student</th><th>Avg Grade</th><th>Sessions</th></tr></thead><tbody>
    <?php $lowStudents->data_seek(0); $rank=1;
    if($lowStudents->num_rows===0): ?><tr><td colspan="4" style="color:var(--muted);text-align:center;padding:16px">No grades this month.</td></tr>
    <?php else: while($ls=$lowStudents->fetch_assoc()): ?>
    <tr>
        <td style="color:var(--muted)"><?= $rank ?></td>
        <td><a href="<?= $selfFile ?>?section=student_profile&view_student=<?= $ls['student_id'] ?>" style="color:var(--text);text-decoration:none"><?= htmlspecialchars($ls['fullname']) ?></a></td>
        <td><span style="color:var(--red);font-weight:600"><?= $ls['avg_grade'] ?>/100</span><div class="grade-bar" style="width:80px;margin-top:3px"><div class="grade-fill" style="width:<?= $ls['avg_grade'] ?>%;background:var(--red)"></div></div></td>
        <td style="color:var(--muted)"><?= $ls['sess_cnt'] ?></td>
    </tr>
    <?php $rank++; endwhile; endif; ?>
    </tbody></table>
</div>
</div>

</div>

<!-- ══ STUDENTS ══════════════════════════════════════════════ -->
<div id="section-students" class="section <?= $activeSection==='students'?'active':'' ?>">

<!-- Top & Low Students Inline -->
<div class="duo-grid">
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-trophy" style="color:var(--amber)"></i> Top 5 Students This Month</div></div>
    <table class="tbl"><thead><tr><th>#</th><th>Student</th><th>Avg Grade</th></tr></thead><tbody>
    <?php $topStudents->data_seek(0); $rank=1; if($topStudents->num_rows===0): ?>
    <tr><td colspan="3" style="color:var(--muted);text-align:center;padding:16px">No grades yet.</td></tr>
    <?php else: while($ts=$topStudents->fetch_assoc()): $medals=['🥇','🥈','🥉','4️⃣','5️⃣']; $gc=$ts['avg_grade']>=80?'var(--green)':($ts['avg_grade']>=60?'var(--amber)':'var(--red)'); ?>
    <tr><td><?= $medals[$rank-1]??$rank ?></td><td><?= htmlspecialchars($ts['fullname']) ?></td><td style="color:<?= $gc ?>;font-weight:600"><?= $ts['avg_grade'] ?></td></tr>
    <?php $rank++; endwhile; endif; ?>
    </tbody></table>
</div>
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-arrow-trend-down" style="color:var(--red)"></i> Low Performing This Month</div></div>
    <table class="tbl"><thead><tr><th>#</th><th>Student</th><th>Avg Grade</th></tr></thead><tbody>
    <?php $lowStudents->data_seek(0); $rank=1; if($lowStudents->num_rows===0): ?>
    <tr><td colspan="3" style="color:var(--muted);text-align:center;padding:16px">No grades yet.</td></tr>
    <?php else: while($ls=$lowStudents->fetch_assoc()): ?>
    <tr><td><?= $rank ?></td><td><?= htmlspecialchars($ls['fullname']) ?></td><td style="color:var(--red);font-weight:600"><?= $ls['avg_grade'] ?></td></tr>
    <?php $rank++; endwhile; endif; ?>
    </tbody></table>
</div>
</div>

<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-user-graduate"></i> All Students</div></div>
    <table class="tbl"><thead><tr><th>Name</th><th>Email</th><th>Mobile</th><th>Verified</th><th>TDC Done</th><th>PDC Done</th><th>TDC Cert</th><th>Joined</th><th></th></tr></thead><tbody>
    <?php $students=q($conn,"
        SELECT s.*,
          (SELECT COUNT(*) FROM appointments a JOIN instructor_courses ic ON ic.course_id=a.course_id WHERE a.student_id=s.student_id AND ic.course_type IN('TDC Face-to-Face','TDC Online') AND a.status='completed') AS tdc_done,
          (SELECT COUNT(*) FROM appointments a JOIN instructor_courses ic ON ic.course_id=a.course_id WHERE a.student_id=s.student_id AND ic.course_type LIKE 'PDC%' AND a.status='completed') AS pdc_done,
          (SELECT COUNT(*) FROM tdc_certificates WHERE student_id=s.student_id) AS has_cert
        FROM students s ORDER BY s.created_at DESC
    ");
    if($students->num_rows===0): ?><tr><td colspan="9" style="color:var(--muted);text-align:center;padding:20px">No students.</td></tr>
    <?php else: while($st=$students->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($st['fullname']) ?></td>
        <td style="font-size:11px"><?= htmlspecialchars($st['email']) ?></td>
        <td style="font-size:11px"><?= $st['mobile']?htmlspecialchars($st['mobile']):'—' ?></td>
        <td><?= $st['is_verified'] ? '<span class="bkg">Yes</span>' : '<span class="bkp">No</span>' ?></td>
        <td><?= $st['tdc_done'] ? '<span class="bkg">✓</span>' : '—' ?></td>
        <td><?= $st['pdc_done'] ? '<span class="bkg">✓</span>' : '—' ?></td>
        <td><?= $st['has_cert'] ? '<span class="cert-badge"><i class="fa-solid fa-certificate"></i> Yes</span>' : '—' ?></td>
        <td style="font-size:11px;color:var(--muted)"><?= date('M d, Y',strtotime($st['created_at'])) ?></td>
        <td style="display:flex;gap:4px">
            <a class="bds" style="font-size:11px;text-decoration:none" href="<?= $selfFile ?>?section=student_profile&view_student=<?= $st['student_id'] ?>">View</a>
            <form method="POST" style="display:inline" onsubmit="return confirm('Delete this student?')">
                <input type="hidden" name="action" value="delete_student">
                <input type="hidden" name="student_id" value="<?= $st['student_id'] ?>">
                <button type="submit" class="ber" style="font-size:11px">Delete</button>
            </form>
        </td>
    </tr>
    <?php endwhile; endif; ?>
    </tbody></table>
</div>
</div>

<!-- Student Profile -->
<div id="section-student_profile" class="section <?= $activeSection==='student_profile'?'active':'' ?>">
<?php if($viewStudentId): $sp=q($conn,"SELECT * FROM students WHERE student_id=$viewStudentId")->fetch_assoc(); ?>
<a href="<?= $selfFile ?>?section=students" class="bds mb-3" style="display:inline-flex;align-items:center;gap:6px"><i class="fa-solid fa-arrow-left"></i> Back to Students</a>
<?php if($sp): ?>
<div class="panel" style="margin-top:12px">
    <div class="panel-head"><div class="panel-title"><?= htmlspecialchars($sp['fullname']) ?></div></div>
    <div style="padding:16px;display:grid;grid-template-columns:1fr 1fr;gap:12px">
        <div><span style="font-size:11px;color:var(--muted)">Email</span><div style="font-size:13px"><?= htmlspecialchars($sp['email']) ?></div></div>
        <div><span style="font-size:11px;color:var(--muted)">Mobile</span><div style="font-size:13px"><?= isset($sp['mobile'])&&$sp['mobile']?htmlspecialchars($sp['mobile']):'—' ?></div></div>
        <div><span style="font-size:11px;color:var(--muted)">Joined</span><div style="font-size:13px"><?= date('F d, Y',strtotime($sp['created_at'])) ?></div></div>
        <div><span style="font-size:11px;color:var(--muted)">Verified</span><div><?= $sp['is_verified']?'<span class="bkg">Yes</span>':'<span class="bkp">No</span>' ?></div></div>
    </div>
</div>

<!-- Student Grading This Month -->
<?php
$studentGrades=q($conn,"SELECT sess.*,ic.course_type,DATE_FORMAT(sess.session_date,'%b %d') AS date_fmt FROM sessions sess JOIN appointments a ON a.appointment_id=sess.appointment_id JOIN instructor_courses ic ON ic.course_id=a.course_id WHERE sess.student_id=$viewStudentId AND sess.grade IS NOT NULL ORDER BY sess.session_date DESC LIMIT 20");
$avgGrade=q($conn,"SELECT ROUND(AVG(grade),1) AS ag FROM sessions WHERE student_id=$viewStudentId AND grade IS NOT NULL AND DATE_FORMAT(session_date,'%Y-%m')='$thisMonth'")->fetch_assoc()['ag'];
?>
<?php if($studentGrades->num_rows>0): ?>
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="fa-solid fa-graduation-cap"></i> Session Grades</div>
        <?php if($avgGrade): $gc=$avgGrade>=80?'var(--green)':($avgGrade>=60?'var(--amber)':'var(--red)'); ?>
        <div style="font-size:12px">This month avg: <strong style="color:<?= $gc ?>"><?= $avgGrade ?>/100</strong></div>
        <?php endif; ?>
    </div>
    <table class="tbl"><thead><tr><th>Date</th><th>Course</th><th>Grade</th><th>Improvements</th></tr></thead><tbody>
    <?php while($sg=$studentGrades->fetch_assoc()): $gc=$sg['grade']>=80?'var(--green)':($sg['grade']>=60?'var(--amber)':'var(--red)'); ?>
    <tr>
        <td><?= $sg['date_fmt'] ?></td>
        <td><span class="tag <?= $cColors[$sg['course_type']]??'tag-gray' ?>"><?= htmlspecialchars($sg['course_type']) ?></span></td>
        <td><strong style="color:<?= $gc ?>"><?= $sg['grade'] ?>/100</strong><div class="grade-bar"><div class="grade-fill" style="width:<?= $sg['grade'] ?>%;background:<?= $gc ?>"></div></div></td>
        <td style="font-size:11px;color:var(--muted)"><?= $sg['improvements']?htmlspecialchars($sg['improvements']):'—' ?></td>
    </tr>
    <?php endwhile; ?>
    </tbody></table>
</div>
<?php endif; ?>

<div class="panel">
    <div class="panel-head"><div class="panel-title">Appointment History</div></div>
    <table class="tbl"><thead><tr><th>Course</th><th>Instructor</th><th>Start Date</th><th>Hours</th><th>Status</th></tr></thead><tbody>
    <?php $sappts=q($conn,"SELECT a.*,ic.course_type,i.fullname AS iname,(SELECT SUM(hours_covered) FROM sessions WHERE appointment_id=a.appointment_id AND status='completed') AS hdone FROM appointments a JOIN instructor_courses ic ON ic.course_id=a.course_id JOIN instructors i ON i.instructor_id=a.instructor_id WHERE a.student_id=$viewStudentId ORDER BY a.requested_at DESC");
    if($sappts->num_rows===0): ?><tr><td colspan="5" style="color:var(--muted);text-align:center;padding:16px">No appointments.</td></tr>
    <?php else: while($sa=$sappts->fetch_assoc()): $tc=$cColors[$sa['course_type']]??'tag-gray'; ?>
    <tr>
        <td><span class="tag <?= $tc ?>"><?= htmlspecialchars($sa['course_type']) ?></span></td>
        <td><?= htmlspecialchars($sa['iname']) ?></td>
        <td><?= date('M d, Y',strtotime($sa['start_date'])) ?></td>
        <td><?= (float)($sa['hdone']??0) ?>/<?= $sa['total_hours'] ?> hrs</td>
        <td><span class="<?= $scmS[$sa['status']]??'bkd' ?>"><?= $scmL[$sa['status']]??$sa['status'] ?></span></td>
    </tr>
    <?php endwhile; endif; ?>
    </tbody></table>
</div>
<?php endif; endif; ?>
</div>

<!-- ══ INSTRUCTORS ════════════════════════════════════════════ -->
<div id="section-instructors" class="section <?= $activeSection==='instructors'?'active':'' ?>">

<!-- Top & Low Instructors Inline -->
<div class="duo-grid">
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-trophy" style="color:var(--amber)"></i> Top 5 Rated Instructors</div></div>
    <table class="tbl"><thead><tr><th>#</th><th>Instructor</th><th>Rating</th><th>Reviews</th></tr></thead><tbody>
    <?php $topInstructors->data_seek(0); $rank=1; if($topInstructors->num_rows===0): ?>
    <tr><td colspan="4" style="color:var(--muted);text-align:center;padding:16px">No ratings yet.</td></tr>
    <?php else: while($ti=$topInstructors->fetch_assoc()): $medals=['🥇','🥈','🥉','4️⃣','5️⃣']; ?>
    <tr><td><?= $medals[$rank-1]??$rank ?></td><td><?= htmlspecialchars($ti['fullname']) ?></td><td style="color:var(--amber)">★ <?= $ti['avg_r'] ?></td><td><?= $ti['cnt'] ?></td></tr>
    <?php $rank++; endwhile; endif; ?>
    </tbody></table>
</div>
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-arrow-trend-down" style="color:var(--red)"></i> Low Rated Instructors</div></div>
    <table class="tbl"><thead><tr><th>#</th><th>Instructor</th><th>Rating</th></tr></thead><tbody>
    <?php $lowInstructors->data_seek(0); $rank=1; if($lowInstructors->num_rows===0): ?>
    <tr><td colspan="3" style="color:var(--muted);text-align:center;padding:16px">No ratings yet.</td></tr>
    <?php else: while($li=$lowInstructors->fetch_assoc()): ?>
    <tr><td><?= $rank ?></td><td><?= htmlspecialchars($li['fullname']) ?></td><td style="color:var(--red)">★ <?= $li['avg_r'] ?></td></tr>
    <?php $rank++; endwhile; endif; ?>
    </tbody></table>
</div>
</div>

<div class="panel mb-3">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-plus"></i> Add Instructor</div></div>
    <div style="padding:16px">
    <form method="POST" style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:10px;align-items:end">
        <input type="hidden" name="action" value="add_instructor">
        <div><label class="form-label">Full Name</label><input type="text" name="fullname" class="di" required></div>
        <div><label class="form-label">Email</label><input type="email" name="email" class="di" required></div>
        <div><label class="form-label">Password</label><input type="password" name="password" class="di" required></div>
        <button type="submit" class="be" style="height:36px"><i class="fa-solid fa-plus"></i> Add</button>
    </form>
    </div>
</div>
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-chalkboard-user"></i> All Instructors</div></div>
    <table class="tbl"><thead><tr><th>Name</th><th>Email</th><th>Mobile</th><th>Courses</th><th>Avg Rating</th><th>Appointments</th><th>Certified</th><th></th></tr></thead><tbody>
    <?php $instrs=q($conn,"
        SELECT i.*,
          (SELECT COUNT(*) FROM instructor_courses WHERE instructor_id=i.instructor_id AND is_active=1) AS course_cnt,
          (SELECT ROUND(AVG(rating),1) FROM instructor_ratings WHERE instructor_id=i.instructor_id) AS avg_r,
          (SELECT COUNT(*) FROM appointments WHERE instructor_id=i.instructor_id) AS appt_cnt
        FROM instructors i ORDER BY i.fullname ASC
    ");
    if($instrs->num_rows===0): ?><tr><td colspan="8" style="color:var(--muted);text-align:center;padding:20px">No instructors.</td></tr>
    <?php else: while($ins=$instrs->fetch_assoc()): $certOk=!empty($ins['certificate_image'])&&file_exists($uploadDir.$ins['certificate_image']); ?>
    <tr>
        <td><?= htmlspecialchars($ins['fullname']) ?></td>
        <td style="font-size:11px"><?= htmlspecialchars($ins['email']) ?></td>
        <td style="font-size:11px"><?= isset($ins['mobile'])&&$ins['mobile']?htmlspecialchars($ins['mobile']):'—' ?></td>
        <td><?= $ins['course_cnt'] ?> active</td>
        <td><?= $ins['avg_r'] ? '★ '.$ins['avg_r'] : '—' ?></td>
        <td><?= $ins['appt_cnt'] ?></td>
        <td><?= $certOk ? '<span class="cert-badge"><i class="fa-solid fa-certificate"></i> Yes</span>' : '—' ?></td>
        <td style="display:flex;gap:4px">
            <a class="bds" style="font-size:11px;text-decoration:none" href="<?= $selfFile ?>?section=instructor_profile&view_instructor=<?= $ins['instructor_id'] ?>">View</a>
            <form method="POST" style="display:inline" onsubmit="return confirm('Delete this instructor? This also deletes their appointments.')">
                <input type="hidden" name="action" value="delete_instructor">
                <input type="hidden" name="instructor_id" value="<?= $ins['instructor_id'] ?>">
                <button type="submit" class="ber" style="font-size:11px">Delete</button>
            </form>
        </td>
    </tr>
    <?php endwhile; endif; ?>
    </tbody></table>
</div>
</div>

<!-- Instructor Profile -->
<div id="section-instructor_profile" class="section <?= $activeSection==='instructor_profile'?'active':'' ?>">
<?php if($viewInstructorId): $ip=q($conn,"SELECT * FROM instructors WHERE instructor_id=$viewInstructorId")->fetch_assoc(); ?>
<a href="<?= $selfFile ?>?section=instructors" class="bds mb-3" style="display:inline-flex;align-items:center;gap:6px"><i class="fa-solid fa-arrow-left"></i> Back to Instructors</a>
<?php if($ip): $certOk=!empty($ip['certificate_image'])&&file_exists($uploadDir.$ip['certificate_image']); ?>
<div class="panel" style="margin-top:12px">
    <div class="panel-head"><div class="panel-title"><?= htmlspecialchars($ip['fullname']) ?></div>
    <?php
    $instrRating=q($conn,"SELECT ROUND(AVG(rating),1) AS r,COUNT(*) AS c FROM instructor_ratings WHERE instructor_id=$viewInstructorId")->fetch_assoc();
    if($instrRating['r']): ?><div style="color:var(--amber)">★ <?= $instrRating['r'] ?> <span style="font-size:11px;color:var(--muted)">(<?= $instrRating['c'] ?> reviews)</span></div><?php endif; ?>
    </div>
    <div style="padding:16px;display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">
        <div><span style="font-size:11px;color:var(--muted)">Email</span><div><?= htmlspecialchars($ip['email']) ?></div></div>
        <div><span style="font-size:11px;color:var(--muted)">Mobile</span><div><?= isset($ip['mobile'])&&$ip['mobile']?htmlspecialchars($ip['mobile']):'—' ?></div></div>
        <div><span style="font-size:11px;color:var(--muted)">Joined</span><div><?= date('M d, Y',strtotime($ip['created_at'])) ?></div></div>
        <?php if(isset($ip['bio'])&&$ip['bio']): ?><div style="grid-column:1/-1"><span style="font-size:11px;color:var(--muted)">Bio</span><div style="font-size:12px"><?= nl2br(htmlspecialchars($ip['bio'])) ?></div></div><?php endif; ?>
        <?php if($certOk): ?><div style="grid-column:1/-1"><span style="font-size:11px;color:var(--muted)">Certificate</span>
        <div><img src="<?= htmlspecialchars('../assets/uploads/certificates/'.$ip['certificate_image']) ?>" style="max-height:100px;border-radius:6px;cursor:pointer;display:block;margin-top:6px" onclick="window.open(this.src)"></div></div><?php endif; ?>
    </div>
</div>
<div class="panel">
    <div class="panel-head"><div class="panel-title">Courses Offered</div></div>
    <table class="tbl"><thead><tr><th>Course Type</th><th>Price/Hr</th><th>Status</th></tr></thead><tbody>
    <?php $icr=q($conn,"SELECT * FROM instructor_courses WHERE instructor_id=$viewInstructorId ORDER BY course_type"); while($c=$icr->fetch_assoc()): $tc=$cColors[$c['course_type']]??'tag-gray'; ?>
    <tr>
        <td><span class="tag <?= $tc ?>"><?= htmlspecialchars($c['course_type']) ?></span></td>
        <td>₱<?= number_format($c['price_per_hr'],2) ?></td>
        <td><?= $c['is_active']?'<span class="bkg">Active</span>':'<span class="bkd">Inactive</span>' ?></td>
    </tr>
    <?php endwhile; ?>
    </tbody></table>
</div>
<?php endif; endif; ?>
</div>

<!-- ══ APPOINTMENTS ══════════════════════════════════════════ -->
<div id="section-appointments" class="section <?= $activeSection==='appointments'?'active':'' ?>">
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-calendar-check"></i> All Appointments</div></div>
    <table class="tbl"><thead><tr><th>Student</th><th>Instructor</th><th>Course</th><th>Start Date</th><th>Hours</th><th>Price</th><th>TDC Grade</th><th>Status</th><th>Actions</th></tr></thead><tbody>
    <?php $apts=q($conn,"SELECT a.*,s.fullname AS sname,i.fullname AS iname,ic.course_type,(SELECT SUM(hours_covered) FROM sessions WHERE appointment_id=a.appointment_id AND status='completed') AS hdone FROM appointments a JOIN students s ON s.student_id=a.student_id JOIN instructors i ON i.instructor_id=a.instructor_id JOIN instructor_courses ic ON ic.course_id=a.course_id ORDER BY a.requested_at DESC");
    if($apts->num_rows===0): ?><tr><td colspan="9" style="color:var(--muted);text-align:center;padding:20px">No appointments.</td></tr>
    <?php else: while($ap=$apts->fetch_assoc()): $tc=$cColors[$ap['course_type']]??'tag-gray'; ?>
    <tr>
        <td><?= htmlspecialchars($ap['sname']) ?></td>
        <td><?= htmlspecialchars($ap['iname']) ?></td>
        <td><span class="tag <?= $tc ?>"><?= htmlspecialchars($ap['course_type']) ?></span></td>
        <td><?= date('M d, Y',strtotime($ap['start_date'])) ?></td>
        <td><?= (float)($ap['hdone']??0) ?>/<?= $ap['total_hours'] ?> hrs</td>
        <td>₱<?= number_format($ap['total_price'],2) ?></td>
        <td><?php if(isset($ap['tdc_overall_grade'])&&$ap['tdc_overall_grade']): $gc=$ap['tdc_overall_grade']>=80?'var(--green)':($ap['tdc_overall_grade']>=60?'var(--amber)':'var(--red)'); ?><strong style="color:<?= $gc ?>"><?= $ap['tdc_overall_grade'] ?>/100</strong><?php else: ?>—<?php endif; ?></td>
        <td><span class="<?= $scmS[$ap['status']]??'bkd' ?>"><?= $scmL[$ap['status']]??$ap['status'] ?></span></td>
        <td style="display:flex;gap:4px;flex-wrap:wrap">
        <?php if(in_array($ap['status'],['approved','in_progress'])): ?>
        <form method="POST" onsubmit="return confirm('Force-complete this appointment?')" style="display:inline">
            <input type="hidden" name="action" value="force_complete">
            <input type="hidden" name="appointment_id" value="<?= $ap['appointment_id'] ?>">
            <button type="submit" class="bds" style="font-size:11px">Force Complete</button>
        </form>
        <?php endif; ?>
        <form method="POST" onsubmit="return confirm('Permanently delete this appointment?')" style="display:inline">
            <input type="hidden" name="action" value="delete_appointment">
            <input type="hidden" name="appointment_id" value="<?= $ap['appointment_id'] ?>">
            <button type="submit" class="ber" style="font-size:11px"><i class="fa-solid fa-trash"></i></button>
        </form>
        </td>
    </tr>
    <?php endwhile; endif; ?>
    </tbody></table>
</div>
</div>

<!-- ══ CERTIFICATES ══════════════════════════════════════════ -->
<div id="section-certificates" class="section <?= $activeSection==='certificates'?'active':'' ?>">
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-certificate"></i> TDC Certificates Issued</div></div>
    <table class="tbl"><thead><tr><th>Student</th><th>Issued By</th><th>Cert Number</th><th>Delivery</th><th>Issued At</th><th>Cert Image</th></tr></thead><tbody>
    <?php $certs=q($conn,"SELECT tc.*,s.fullname AS sname,i.fullname AS iname FROM tdc_certificates tc JOIN students s ON s.student_id=tc.student_id JOIN instructors i ON i.instructor_id=tc.issued_by ORDER BY tc.issued_at DESC");
    if($certs->num_rows===0): ?><tr><td colspan="6" style="color:var(--muted);text-align:center;padding:20px">No certificates issued yet.</td></tr>
    <?php else: while($c=$certs->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($c['sname']) ?></td>
        <td><?= htmlspecialchars($c['iname']) ?></td>
        <td><?= $c['cert_number']?htmlspecialchars($c['cert_number']):'—' ?></td>
        <td><?php
            $dm=$c['delivery_method']??'face_to_face';
            if($dm==='online'): ?><span class="bkb"><i class="fa-solid fa-globe me-1"></i>Online</span>
            <?php else: ?><span class="bkd"><i class="fa-solid fa-handshake me-1"></i>Face-to-Face</span><?php endif; ?>
        </td>
        <td><?= date('M d, Y',strtotime($c['issued_at'])) ?></td>
        <td><?php if(!empty($c['cert_image'])&&file_exists('../assets/uploads/certs/'.$c['cert_image'])): ?>
            <a href="../assets/uploads/certs/<?= htmlspecialchars($c['cert_image']) ?>" target="_blank" class="bds" style="font-size:11px"><i class="fa-solid fa-image"></i> View</a>
        <?php else: ?>—<?php endif; ?></td>
    </tr>
    <?php endwhile; endif; ?>
    </tbody></table>
</div>
</div>

<!-- ══ ANALYTICS ═════════════════════════════════════════════ -->
<div id="section-analytics" class="section <?= $activeSection==='analytics'?'active':'' ?>">

<!-- Stat cards -->
<div class="analytics-grid">
    <div class="analytic-card">
        <div class="analytic-num" style="color:var(--accent)"><?= $todayLogins ?></div>
        <div class="analytic-label"><i class="fa-solid fa-right-to-bracket me-1"></i>Today's Logins</div>
        <div class="analytic-sub">All users</div>
    </div>
    <div class="analytic-card">
        <div class="analytic-num" style="color:var(--green)"><?= $monthLogins ?></div>
        <div class="analytic-label"><i class="fa-solid fa-calendar me-1"></i>Monthly Logins</div>
        <div class="analytic-sub"><?= date('F Y') ?></div>
    </div>
    <div class="analytic-card">
        <div class="analytic-num" style="color:var(--amber)"><?= $newStudentsMonth ?></div>
        <div class="analytic-label"><i class="fa-solid fa-user-plus me-1"></i>New Students</div>
        <div class="analytic-sub">This month</div>
    </div>
    <div class="analytic-card">
        <div class="analytic-num" style="color:var(--green)">₱<?= number_format($revenueMonth,0) ?></div>
        <div class="analytic-label"><i class="fa-solid fa-peso-sign me-1"></i>Revenue</div>
        <div class="analytic-sub">This month (active+done)</div>
    </div>
    <div class="analytic-card">
        <div class="analytic-num" style="color:var(--accent)"><?= $totalStudents ?></div>
        <div class="analytic-label"><i class="fa-solid fa-users me-1"></i>Total Students</div>
        <div class="analytic-sub">Registered</div>
    </div>
    <div class="analytic-card">
        <div class="analytic-num" style="color:var(--amber)"><?= $certsIssued ?></div>
        <div class="analytic-label"><i class="fa-solid fa-certificate me-1"></i>Certs Issued</div>
        <div class="analytic-sub">TDC total</div>
    </div>
</div>

<!-- Charts row -->
<div class="duo-grid" style="margin-bottom:16px">
    <div class="panel">
        <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-chart-bar me-1"></i> Appointments – Last 6 Months</div></div>
        <div style="padding:16px;height:240px;position:relative">
            <canvas id="chartBarAppts"></canvas>
        </div>
    </div>
    <div class="panel">
        <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-chart-pie me-1"></i> Course Distribution</div></div>
        <div style="padding:16px;display:flex;align-items:center;gap:16px;height:240px">
            <div style="flex:0 0 180px;height:180px;position:relative">
                <canvas id="chartDoughCourse"></canvas>
            </div>
            <div id="doughLegend" style="flex:1;font-size:11px;line-height:2.2;overflow:auto"></div>
        </div>
    </div>
</div>

<!-- Login tables with IP / device / failed attempts -->
<div class="duo-grid">
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-user-graduate"></i> Student Login Activity</div></div>
    <div style="overflow-x:auto">
    <table class="tbl"><thead><tr><th>Student</th><th>IP</th><th>Device</th><th>Status</th><th title="Total failed attempts">⚠ Failed</th><th>Time</th></tr></thead><tbody>
    <?php $sLogins=q($conn,"SELECT slh.*,s.fullname FROM student_login_history slh JOIN students s ON s.student_id=slh.student_id ORDER BY slh.login_datetime DESC LIMIT 20");
    while($sl=$sLogins->fetch_assoc()):
        $ua=$sl['device_info']??'';
        $isMob=(bool)preg_match('/Mobile|Android|iPhone|iPad/i',$ua);
        $browser='Other';
        if(stripos($ua,'Edg')!==false)$browser='Edge';
        elseif(stripos($ua,'Chrome')!==false)$browser='Chrome';
        elseif(stripos($ua,'Firefox')!==false)$browser='Firefox';
        elseif(stripos($ua,'Safari')!==false)$browser='Safari';
        $fc=$sFailMap[(int)$sl['student_id']]??0;
    ?>
    <tr>
        <td style="font-size:12px"><?= htmlspecialchars($sl['fullname']) ?></td>
        <td style="font-size:10px;color:var(--muted);font-family:monospace"><?= htmlspecialchars($sl['ip_address']??'—') ?></td>
        <td style="font-size:10px;color:var(--muted)"><?= $isMob?'<i class="fa-solid fa-mobile-screen"></i>':'<i class="fa-solid fa-desktop"></i>' ?> <?= $browser ?></td>
        <td><?= $sl['status']==='success'?'<span class="bkg" style="font-size:10px">✓ OK</span>':'<span class="bkr" style="font-size:10px">✗ Fail</span>' ?></td>
        <td style="text-align:center"><?php if($fc>0): ?><span style="background:rgba(229,85,85,.15);color:var(--red);border-radius:8px;padding:1px 8px;font-size:11px;font-weight:600"><?= $fc ?></span><?php else: ?>—<?php endif; ?></td>
        <td style="font-size:10px;color:var(--muted);white-space:nowrap"><?= date('M d, g:i A',strtotime($sl['login_datetime'])) ?></td>
    </tr>
    <?php endwhile; ?>
    </tbody></table>
    </div>
</div>
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-chalkboard-user"></i> Instructor Login Activity</div></div>
    <div style="overflow-x:auto">
    <table class="tbl"><thead><tr><th>Instructor</th><th>IP</th><th>City</th><th>Status</th><th title="Total failed attempts">⚠ Failed</th><th>Time</th></tr></thead><tbody>
    <?php $iLogins=q($conn,"SELECT ilh.*,i.fullname FROM instructor_login_history ilh JOIN instructors i ON i.instructor_id=ilh.instructor_id ORDER BY ilh.login_time DESC LIMIT 20");
    while($il=$iLogins->fetch_assoc()):
        $fc=$iFailMap[(int)$il['instructor_id']]??0;
    ?>
    <tr>
        <td style="font-size:12px"><?= htmlspecialchars($il['fullname']) ?></td>
        <td style="font-size:10px;color:var(--muted);font-family:monospace"><?= htmlspecialchars($il['ip_address']??'—') ?></td>
        <td style="font-size:10px;color:var(--muted)"><?= $il['city']?htmlspecialchars($il['city']):'—' ?></td>
        <td><?= $il['login_status']==='success'?'<span class="bkg" style="font-size:10px">✓ OK</span>':'<span class="bkr" style="font-size:10px">✗ Fail</span>' ?></td>
        <td style="text-align:center"><?php if($fc>0): ?><span style="background:rgba(229,85,85,.15);color:var(--red);border-radius:8px;padding:1px 8px;font-size:11px;font-weight:600"><?= $fc ?></span><?php else: ?>—<?php endif; ?></td>
        <td style="font-size:10px;color:var(--muted);white-space:nowrap"><?= date('M d, g:i A',strtotime($il['login_time'])) ?></td>
    </tr>
    <?php endwhile; ?>
    </tbody></table>
    </div>
</div>
</div>
</div>

<!-- ══ RANKINGS ══════════════════════════════════════════════ -->
<div id="section-rankings" class="section <?= $activeSection==='rankings'?'active':'' ?>">
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
    <div style="font-size:13px;font-weight:600"><i class="fa-solid fa-ranking-star me-2" style="color:var(--amber)"></i>Rankings – <?= date('F Y') ?></div>
    <small style="color:var(--muted);font-size:11px"><i class="fa-solid fa-rotate me-1"></i>Student grades reset monthly</small>
</div>

<!-- Instructor Rankings -->
<div style="font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px">Instructors – by Rating</div>
<div class="duo-grid">
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="fa-solid fa-trophy" style="color:var(--amber)"></i> Top 5 Highest Rated</div>
        <span style="font-size:10px;color:var(--muted)">All-time</span>
    </div>
    <table class="tbl"><thead><tr><th>#</th><th>Instructor</th><th>Avg Rating</th><th>Reviews</th><th>Sessions Done</th></tr></thead><tbody>
    <?php $topInstructors->data_seek(0); $rank=1;
    if($topInstructors->num_rows===0): ?>
    <tr><td colspan="5" style="color:var(--muted);text-align:center;padding:20px">No ratings yet.</td></tr>
    <?php else: $medals=['🥇','🥈','🥉','4️⃣','5️⃣'];
    while($ti=$topInstructors->fetch_assoc()): ?>
    <tr>
        <td><span class="rank-medal"><?= $medals[$rank-1]??$rank ?></span></td>
        <td><a href="<?= $selfFile ?>?section=instructor_profile&view_instructor=<?= $ti['instructor_id'] ?>" style="color:var(--text);text-decoration:none"><?= htmlspecialchars($ti['fullname']) ?></a></td>
        <td><span style="color:var(--amber);font-weight:600">★ <?= $ti['avg_r'] ?></span>
            <div style="height:4px;background:var(--border);border-radius:2px;margin-top:3px;width:80px"><div style="height:4px;background:var(--amber);border-radius:2px;width:<?= round($ti['avg_r']/5*100) ?>%"></div></div>
        </td>
        <td style="color:var(--muted)"><?= $ti['cnt'] ?></td>
        <td style="color:var(--muted)"><?= $ti['done'] ?></td>
    </tr>
    <?php $rank++; endwhile; endif; ?>
    </tbody></table>
</div>
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="fa-solid fa-arrow-trend-down" style="color:var(--red)"></i> Top 5 Lowest Rated</div>
        <span style="font-size:10px;color:var(--muted)">All-time · min 1 review</span>
    </div>
    <table class="tbl"><thead><tr><th>#</th><th>Instructor</th><th>Avg Rating</th><th>Reviews</th></tr></thead><tbody>
    <?php $lowInstructors->data_seek(0); $rank=1;
    if($lowInstructors->num_rows===0): ?>
    <tr><td colspan="4" style="color:var(--muted);text-align:center;padding:20px">No ratings yet.</td></tr>
    <?php else: while($li=$lowInstructors->fetch_assoc()): ?>
    <tr>
        <td><span style="color:var(--muted);font-size:12px"><?= $rank ?></span></td>
        <td><a href="<?= $selfFile ?>?section=instructor_profile&view_instructor=<?= $li['instructor_id'] ?>" style="color:var(--text);text-decoration:none"><?= htmlspecialchars($li['fullname']) ?></a></td>
        <td><span style="color:var(--red);font-weight:600">★ <?= $li['avg_r'] ?></span>
            <div style="height:4px;background:var(--border);border-radius:2px;margin-top:3px;width:80px"><div style="height:4px;background:var(--red);border-radius:2px;width:<?= round($li['avg_r']/5*100) ?>%"></div></div>
        </td>
        <td style="color:var(--muted)"><?= $li['cnt'] ?></td>
    </tr>
    <?php $rank++; endwhile; endif; ?>
    </tbody></table>
</div>
</div>

<!-- Student Rankings -->
<div style="font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin:4px 0 8px">Students – by Session Grade (<?= date('F Y') ?>)</div>
<div class="duo-grid">
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="fa-solid fa-star" style="color:var(--amber)"></i> Top 5 Highest Grades</div>
        <small style="color:var(--muted);font-size:10px"><i class="fa-solid fa-rotate me-1"></i>Resets monthly</small>
    </div>
    <table class="tbl"><thead><tr><th>#</th><th>Student</th><th>Avg Grade</th><th>Sessions</th></tr></thead><tbody>
    <?php $topStudents->data_seek(0); $rank=1;
    if($topStudents->num_rows===0): ?>
    <tr><td colspan="4" style="color:var(--muted);text-align:center;padding:20px">No grades recorded this month.</td></tr>
    <?php else: $medals=['🥇','🥈','🥉','4️⃣','5️⃣'];
    while($ts=$topStudents->fetch_assoc()): $gc=$ts['avg_grade']>=80?'var(--green)':($ts['avg_grade']>=60?'var(--amber)':'var(--red)'); ?>
    <tr>
        <td><span class="rank-medal"><?= $medals[$rank-1]??$rank ?></span></td>
        <td><a href="<?= $selfFile ?>?section=student_profile&view_student=<?= $ts['student_id'] ?>" style="color:var(--text);text-decoration:none"><?= htmlspecialchars($ts['fullname']) ?></a></td>
        <td>
            <span style="color:<?= $gc ?>;font-weight:600"><?= $ts['avg_grade'] ?>/100</span>
            <div class="grade-bar" style="width:90px;margin-top:3px"><div class="grade-fill" style="width:<?= $ts['avg_grade'] ?>%;background:<?= $gc ?>"></div></div>
        </td>
        <td style="color:var(--muted)"><?= $ts['sess_cnt'] ?></td>
    </tr>
    <?php $rank++; endwhile; endif; ?>
    </tbody></table>
</div>
<div class="panel">
    <div class="panel-head">
        <div class="panel-title"><i class="fa-solid fa-arrow-trend-down" style="color:var(--red)"></i> Top 5 Lowest Grades</div>
        <small style="color:var(--muted);font-size:10px"><i class="fa-solid fa-rotate me-1"></i>Resets monthly</small>
    </div>
    <table class="tbl"><thead><tr><th>#</th><th>Student</th><th>Avg Grade</th><th>Sessions</th></tr></thead><tbody>
    <?php $lowStudents->data_seek(0); $rank=1;
    if($lowStudents->num_rows===0): ?>
    <tr><td colspan="4" style="color:var(--muted);text-align:center;padding:20px">No grades recorded this month.</td></tr>
    <?php else: while($ls=$lowStudents->fetch_assoc()): ?>
    <tr>
        <td><span style="color:var(--muted);font-size:12px"><?= $rank ?></span></td>
        <td><a href="<?= $selfFile ?>?section=student_profile&view_student=<?= $ls['student_id'] ?>" style="color:var(--text);text-decoration:none"><?= htmlspecialchars($ls['fullname']) ?></a></td>
        <td>
            <span style="color:var(--red);font-weight:600"><?= $ls['avg_grade'] ?>/100</span>
            <div class="grade-bar" style="width:90px;margin-top:3px"><div class="grade-fill" style="width:<?= $ls['avg_grade'] ?>%;background:var(--red)"></div></div>
        </td>
        <td style="color:var(--muted)"><?= $ls['sess_cnt'] ?></td>
    </tr>
    <?php $rank++; endwhile; endif; ?>
    </tbody></table>
</div>
</div>
</div>

<!-- ══ REPORTS ════════════════════════════════════════════════ -->
<div id="section-reports" class="section <?= $activeSection==='reports'?'active':'' ?>">
<div class="panel mb-3">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-chart-pie"></i> Course Breakdown</div></div>
    <table class="tbl"><thead><tr><th>Course Type</th><th>Total</th><th>Completed</th><th>In Progress</th><th>Pending</th></tr></thead><tbody>
    <?php $courseReport=q($conn,"SELECT ic.course_type,COUNT(*) AS total,SUM(a.status='completed') AS done,SUM(a.status IN('approved','in_progress')) AS active,SUM(a.status='pending') AS pending FROM appointments a JOIN instructor_courses ic ON ic.course_id=a.course_id GROUP BY ic.course_type ORDER BY ic.course_type");
    while($cr=$courseReport->fetch_assoc()): $tc=$cColors[$cr['course_type']]??'tag-gray'; ?>
    <tr>
        <td><span class="tag <?= $tc ?>"><?= htmlspecialchars($cr['course_type']) ?></span></td>
        <td><?= $cr['total'] ?></td>
        <td><?= $cr['done'] ?></td>
        <td><?= $cr['active'] ?></td>
        <td><?= $cr['pending'] ?></td>
    </tr>
    <?php endwhile; ?>
    </tbody></table>
</div>
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-users"></i> Student Progress Summary</div></div>
    <table class="tbl"><thead><tr><th>Student</th><th>TDC Done</th><th>PDC Done</th><th>Cert</th><th>Avg Grade (TDC)</th><th>Avg Perf</th></tr></thead><tbody>
    <?php $sProgress=q($conn,"
        SELECT s.student_id,s.fullname,
          (SELECT COUNT(*) FROM appointments a JOIN instructor_courses ic ON ic.course_id=a.course_id WHERE a.student_id=s.student_id AND ic.course_type IN('TDC Face-to-Face','TDC Online') AND a.status='completed') AS tdc_done,
          (SELECT COUNT(*) FROM appointments a JOIN instructor_courses ic ON ic.course_id=a.course_id WHERE a.student_id=s.student_id AND ic.course_type LIKE 'PDC%' AND a.status='completed') AS pdc_done,
          (SELECT COUNT(*) FROM tdc_certificates WHERE student_id=s.student_id) AS cert,
          (SELECT ROUND(AVG(grade),1) FROM sessions WHERE student_id=s.student_id AND grade IS NOT NULL) AS avg_grade,
          (SELECT ROUND(AVG(performance_rating),1) FROM sessions WHERE student_id=s.student_id AND performance_rating IS NOT NULL) AS avg_perf
        FROM students s ORDER BY s.fullname ASC
    ");
    while($sp=$sProgress->fetch_assoc()): $gc=$sp['avg_grade']?($sp['avg_grade']>=80?'var(--green)':($sp['avg_grade']>=60?'var(--amber)':'var(--red)')):''; ?>
    <tr>
        <td><?= htmlspecialchars($sp['fullname']) ?></td>
        <td><?= $sp['tdc_done'] ? '<span class="bkg">✓</span>' : '—' ?></td>
        <td><?= $sp['pdc_done'] ? '<span class="bkg">✓</span>' : '—' ?></td>
        <td><?= $sp['cert'] ? '<span class="cert-badge"><i class="fa-solid fa-certificate"></i></span>' : '—' ?></td>
        <td><?= $sp['avg_grade'] ? '<strong style="color:'.$gc.'">'.$sp['avg_grade'].'/100</strong>' : '—' ?></td>
        <td><?= $sp['avg_perf'] ? '★ '.$sp['avg_perf'] : '—' ?></td>
    </tr>
    <?php endwhile; ?>
    </tbody></table>
</div>
</div>

<!-- ══ ANNOUNCEMENTS ════════════════════════════════════════ -->
<div id="section-communication" class="section <?= $activeSection==='communication'?'active':'' ?>">
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-bullhorn"></i> Send Announcement</div></div>
    <div style="padding:16px">
    <form method="POST">
        <input type="hidden" name="action" value="announce">
        <div style="margin-bottom:14px">
            <label class="form-label">Send To</label>
            <div style="display:flex;gap:10px;flex-wrap:wrap">
                <?php
                $stoOptions=[
                    'students'    =>['fa-user-graduate','Students',   'var(--accent)', $stuRecipCount],
                    'instructors' =>['fa-chalkboard-user','Instructors','var(--green)', $insRecipCount],
                    'everyone'    =>['fa-users',          'Everyone',  'var(--amber)',  $stuRecipCount+$insRecipCount],
                ];
                foreach($stoOptions as $val=>[$ico,$lbl,$clr,$cnt2]): ?>
                <label style="flex:1;min-width:120px;cursor:pointer">
                    <input type="radio" name="send_to" value="<?= $val ?>" <?= $val==='students'?'checked':'' ?> class="sto-radio" style="display:none">
                    <div class="sto-btn" data-val="<?= $val ?>" data-color="<?= $clr ?>" style="border:2px solid var(--border);border-radius:8px;padding:10px 14px;display:flex;align-items:center;gap:8px;transition:.15s;user-select:none">
                        <i class="fa-solid <?= $ico ?>" style="color:<?= $clr ?>"></i>
                        <span style="font-size:13px;font-weight:500"><?= $lbl ?></span>
                        <span style="margin-left:auto;background:rgba(255,255,255,.06);border-radius:10px;padding:1px 8px;font-size:11px;color:var(--muted)"><?= $cnt2 ?></span>
                    </div>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        <div style="margin-bottom:14px">
            <label class="form-label">Message</label>
            <textarea name="message" class="di" rows="4" required placeholder="Type your announcement…"></textarea>
        </div>
        <button type="submit" class="be"><i class="fa-solid fa-paper-plane me-1"></i> Send Announcement</button>
    </form>
    </div>
</div>

<!-- Recent announcements log -->
<?php $recentAnn=q($conn,"SELECT n.*,CASE WHEN n.user_type='student' THEN (SELECT fullname FROM students WHERE student_id=n.user_id) WHEN n.user_type='instructor' THEN (SELECT fullname FROM instructors WHERE instructor_id=n.user_id) END AS recip FROM notifications WHERE type='announcement' ORDER BY created_at DESC LIMIT 30");
if($recentAnn->num_rows>0): ?>
<div class="panel" style="margin-top:16px">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-clock-rotate-left me-1"></i> Recent Announcements</div></div>
    <table class="tbl"><thead><tr><th>Message</th><th>Recipient</th><th>Type</th><th>Read</th><th>Sent</th></tr></thead><tbody>
    <?php while($ra=$recentAnn->fetch_assoc()): $isInst=$ra['user_type']==='instructor'; ?>
    <tr>
        <td style="max-width:260px;font-size:12px"><?= htmlspecialchars(substr(str_replace('📢 Admin: ','',$ra['message']),0,80)) ?><?= strlen($ra['message'])>88?'…':'' ?></td>
        <td style="font-size:12px"><?= htmlspecialchars($ra['recip']??'—') ?></td>
        <td><span class="tag <?= $isInst?'tag-green':'tag-blue' ?>"><?= $isInst?'Instructor':'Student' ?></span></td>
        <td><?= $ra['is_read']?'<span class="bkg" style="font-size:10px">✓</span>':'<span style="font-size:10px;color:var(--muted)">—</span>' ?></td>
        <td style="font-size:10px;color:var(--muted);white-space:nowrap"><?= date('M d, g:i A',strtotime($ra['created_at'])) ?></td>
    </tr>
    <?php endwhile; ?>
    </tbody></table>
</div>
<?php endif; ?>
</div>

</div><!-- /main -->

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// On load: apply correct section visibility after full DOM is ready
(function(){
    document.querySelectorAll('.section').forEach(function(el){
        el.style.display = el.classList.contains('active') ? 'block' : 'none';
    });
    var dash=document.getElementById('section-dashboard');
    if(dash && dash.style.display==='block') setTimeout(initCharts,80);
})();

var chartsOk=false;
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
    Chart.defaults.color=muted;
    Chart.defaults.font.family='Segoe UI,sans-serif';
    Chart.defaults.font.size=11;
    var barC=document.getElementById('chartBarAppts');
    if(barC){new Chart(barC,{type:'bar',data:{labels:<?= json_encode($barLabels) ?>,datasets:[{label:'Appointments',data:<?= json_encode($barAppts) ?>,backgroundColor:accent+'bb',borderColor:accent,borderWidth:1.5,borderRadius:5,borderSkipped:false}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{grid:{color:border+'55'},ticks:{color:muted}},y:{grid:{color:border+'55'},ticks:{color:muted,precision:0,stepSize:1},beginAtZero:true}}}});}
    var doughC=document.getElementById('chartDoughCourse');
    if(doughC){
        var pal=[accent,green,amber,red,'#a78bfa','#38bdf8','#fb923c','#f472b6'];
        var dL=<?= json_encode($dLabels) ?>;
        var dD=<?= json_encode($dData) ?>;
        var dC=dL.map(function(_,i){return pal[i%pal.length];});
        new Chart(doughC,{type:'doughnut',data:{labels:dL,datasets:[{data:dD,backgroundColor:dC,borderColor:bg,borderWidth:3,hoverOffset:6}]},options:{responsive:true,maintainAspectRatio:false,cutout:'65%',plugins:{legend:{display:false}}}});
        var leg=document.getElementById('doughLegend');
        if(leg&&dL.length){leg.innerHTML=dL.map(function(l,i){return '<div style="display:flex;align-items:center;gap:6px;margin-bottom:4px"><span style="display:inline-block;width:9px;height:9px;border-radius:50%;background:'+dC[i]+';flex-shrink:0"></span><span style="color:#e2e5f0;font-size:11px">'+l+'</span><span style="margin-left:auto;font-weight:600;color:#e2e5f0">'+dD[i]+'</span></div>';}).join('');}
    }
}

function refreshSto(){
    var radios=document.querySelectorAll('.sto-radio');
    for(var i=0;i<radios.length;i++){
        var r=radios[i],lbl=r.closest('label');
        if(!lbl)continue;
        var btn=lbl.querySelector('.sto-btn');
        if(!btn)continue;
        if(r.checked){btn.style.borderColor=btn.getAttribute('data-color');btn.style.background='rgba(255,255,255,.04)';}
        else{btn.style.borderColor='var(--border)';btn.style.background='';}
    }
}
refreshSto();
</script>
</body>
</html>
