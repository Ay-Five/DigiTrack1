<?php
session_start();
if (!isset($_SESSION['instructor_id'])) { header('Location: instructor_login.php'); exit; }
$instructor_id = (int)$_SESSION['instructor_id'];
require_once '../includes/db.php';

$today = date('Y-m-d');
$selfFile = basename(__FILE__);

function q($c,$s){return $c->query($s);}
function esc($c,$v){return $c->real_escape_string($v);}
$cColors=['TDC Face-to-Face'=>'tag-amber','TDC Online'=>'tag-blue',
          'PDC Car Manual'=>'tag-green','PDC Car Automatic'=>'tag-teal',
          'PDC Motorcycle Manual'=>'tag-coral','PDC Motorcycle Automatic'=>'tag-purple'];
function stars($n){$s='';for($i=1;$i<=5;$i++)$s.=$i<=$n?'★':'☆';return $s;}

$activeSection='dashboard';
if(!empty($_GET['section']))$activeSection=preg_replace('/[^a-z_]/','',$_GET['section']);

// ── POST handlers ─────────────────────────────────────────────
if($_SERVER['REQUEST_METHOD']==='POST'){
    $action=$_POST['action']??'';

    // Approve / Reject appointment
    if($action==='approve_appointment'){
        $appt_id=(int)($_POST['appointment_id']??0);
        $note=esc($conn,$_POST['instructor_note']??'');
        $conn->query("UPDATE appointments SET status='approved',instructor_note='$note',reviewed_at=NOW() WHERE appointment_id=$appt_id AND instructor_id=$instructor_id AND status='pending'");
        $aRow=q($conn,"SELECT a.*,s.student_id,ic.course_type FROM appointments a JOIN students s ON s.student_id=a.student_id JOIN instructor_courses ic ON ic.course_id=a.course_id WHERE a.appointment_id=$appt_id")->fetch_assoc();
        if($aRow){$conn->query("INSERT INTO notifications (user_id,user_type,message,type) VALUES ({$aRow['student_id']},'student','Your appointment for {$aRow['course_type']} has been APPROVED!','appointment_approved')");}
        $_SESSION['flash']='Appointment approved.';
        header("Location: $selfFile?section=appointments"); exit;
    }
    if($action==='reject_appointment'){
        $appt_id=(int)($_POST['appointment_id']??0);
        $note=esc($conn,$_POST['instructor_note']??'');
        $conn->query("UPDATE appointments SET status='rejected',instructor_note='$note',reviewed_at=NOW() WHERE appointment_id=$appt_id AND instructor_id=$instructor_id AND status='pending'");
        $aRow=q($conn,"SELECT a.*,s.student_id,ic.course_type FROM appointments a JOIN students s ON s.student_id=a.student_id JOIN instructor_courses ic ON ic.course_id=a.course_id WHERE a.appointment_id=$appt_id")->fetch_assoc();
        if($aRow){$conn->query("INSERT INTO notifications (user_id,user_type,message,type) VALUES ({$aRow['student_id']},'student','Your appointment for {$aRow['course_type']} was declined.','appointment_rejected')");}
        $_SESSION['flash']='Appointment rejected.';
        header("Location: $selfFile?section=appointments"); exit;
    }

    // Add session with grade
    if($action==='add_session'){
        $appt_id=(int)($_POST['appointment_id']??0);
        $sess_date=esc($conn,$_POST['session_date']??$today);
        $hrs=max(0.5,min(8,(float)($_POST['hours_covered']??1)));
        $grade=(int)($_POST['grade']??0);
        $grade=($grade>=1&&$grade<=100)?$grade:null;
        $improvements=esc($conn,$_POST['improvements']??'');
        $perf_notes=esc($conn,$_POST['performance_notes']??'');
        $conn->query("UPDATE appointments SET status='in_progress' WHERE appointment_id=$appt_id AND instructor_id=$instructor_id AND status='approved'");
        $aRow=q($conn,"SELECT * FROM appointments WHERE appointment_id=$appt_id AND instructor_id=$instructor_id")->fetch_assoc();
        if($aRow){
            $isTDC=q($conn,"SELECT course_type FROM instructor_courses WHERE course_id={$aRow['course_id']}")->fetch_assoc()['course_type'];
            $sessType=stripos($isTDC,'TDC')===0?'TDC':'PDC';
            $sessNum=(int)q($conn,"SELECT COUNT(*)+1 AS n FROM sessions WHERE appointment_id=$appt_id")->fetch_assoc()['n'];
            $gradeSQL=$grade===null?'NULL':$grade;
            $conn->query("INSERT INTO sessions (appointment_id,session_type,session_number,student_id,instructor_id,session_date,hours_covered,status,grade,improvements,performance_notes)
                VALUES ($appt_id,'$sessType',$sessNum,{$aRow['student_id']},$instructor_id,'$sess_date',$hrs,'completed',$gradeSQL,'$improvements','$perf_notes')");
            $doneHrs=(float)q($conn,"SELECT COALESCE(SUM(hours_covered),0) AS h FROM sessions WHERE appointment_id=$appt_id AND status='completed'")->fetch_assoc()['h'];
            if($doneHrs>=$aRow['total_hours']){
                $conn->query("UPDATE appointments SET status='completed',completed_at=NOW() WHERE appointment_id=$appt_id");
                // Compute overall grade if TDC
                if($sessType==='TDC'){
                    $overallGrade=(float)q($conn,"SELECT ROUND(AVG(grade),1) AS ag FROM sessions WHERE appointment_id=$appt_id AND grade IS NOT NULL")->fetch_assoc()['ag'];
                    if($overallGrade>0){
                        $conn->query("UPDATE appointments SET tdc_overall_grade=ROUND($overallGrade) WHERE appointment_id=$appt_id");
                    }
                }
                $conn->query("INSERT INTO notifications (user_id,user_type,message,type) VALUES ({$aRow['student_id']},'student','Your course is complete! Please rate your instructor.','course_completed')");
            }
        }
        $_SESSION['flash']='Session logged.';
        header("Location: $selfFile?section=appointments"); exit;
    }

    // Rate student performance for a session
    if($action==='rate_student'){
        $sess_id=(int)($_POST['session_id']??0);
        $rating=max(1,min(5,(int)($_POST['performance_rating']??5)));
        $notes=esc($conn,$_POST['performance_notes']??'');
        $grade=(int)($_POST['grade']??0);
        $grade=($grade>=1&&$grade<=100)?$grade:null;
        $improvements=esc($conn,$_POST['improvements']??'');
        $gradeSQL=$grade===null?'NULL':$grade;
        $conn->query("UPDATE sessions SET performance_rating=$rating,performance_notes='$notes',grade=$gradeSQL,improvements='$improvements' WHERE session_id=$sess_id AND instructor_id=$instructor_id");
        // Recalculate overall TDC grade if TDC session
        $sessRow=q($conn,"SELECT s.*,ic.course_type FROM sessions s JOIN appointments a ON a.appointment_id=s.appointment_id JOIN instructor_courses ic ON ic.course_id=a.course_id WHERE s.session_id=$sess_id")->fetch_assoc();
        if($sessRow&&stripos($sessRow['course_type'],'TDC')===0){
            $overallGrade=(float)q($conn,"SELECT ROUND(AVG(grade),1) AS ag FROM sessions WHERE appointment_id={$sessRow['appointment_id']} AND grade IS NOT NULL")->fetch_assoc()['ag'];
            if($overallGrade>0)$conn->query("UPDATE appointments SET tdc_overall_grade=ROUND($overallGrade) WHERE appointment_id={$sessRow['appointment_id']}");
        }
        $_SESSION['flash']='Session updated.';
        header("Location: $selfFile?section=performance"); exit;
    }

    // Issue TDC certificate with delivery method
    if($action==='issue_certificate'){
        $appt_id=(int)($_POST['appointment_id']??0);
        $cert_num=esc($conn,$_POST['cert_number']??'');
        $delivery=in_array($_POST['delivery_method']??'',['online','face_to_face'])?$_POST['delivery_method']:'face_to_face';
        $aRow=q($conn,"SELECT * FROM appointments WHERE appointment_id=$appt_id AND instructor_id=$instructor_id AND status='completed'")->fetch_assoc();
        if($aRow){
            // Handle uploaded cert image for online delivery
            $certImage='NULL';
            if($delivery==='online'&&isset($_FILES['cert_image'])&&$_FILES['cert_image']['error']===0){
                $ext=strtolower(pathinfo($_FILES['cert_image']['name'],PATHINFO_EXTENSION));
                if(in_array($ext,['jpg','jpeg','png','gif','webp'])){
                    $uploadPath='../assets/uploads/certs/';
                    if(!is_dir($uploadPath))mkdir($uploadPath,0755,true);
                    $fname='tcert_'.$aRow['student_id'].'_'.time().'.'.$ext;
                    if(move_uploaded_file($_FILES['cert_image']['tmp_name'],$uploadPath.$fname)){
                        $certImage="'$fname'";
                    }
                }
            }
            $conn->query("INSERT IGNORE INTO tdc_certificates (student_id,appointment_id,issued_by,cert_number,delivery_method,cert_image) VALUES ({$aRow['student_id']},$appt_id,$instructor_id,'$cert_num','$delivery',$certImage)");
            $msg=$delivery==='online'?'Your TDC Certificate has been issued online! PDC is now unlocked.':'Your TDC Certificate is ready! Please collect it in person. PDC is now unlocked.';
            $conn->query("INSERT INTO notifications (user_id,user_type,message,type) VALUES ({$aRow['student_id']},'student','$msg','certificate_issued')");
        }
        $_SESSION['flash']='Certificate issued.';
        header("Location: $selfFile?section=performance"); exit;
    }

    // Manage courses
    if($action==='add_course'){
        $ct=esc($conn,$_POST['course_type']??'');
        $desc=esc($conn,$_POST['description']??'');
        $phr=(float)($_POST['price_per_hr']??0);
        $conn->query("INSERT INTO instructor_courses (instructor_id,course_type,description,price_per_hr) VALUES ($instructor_id,'$ct','$desc',$phr)");
        $_SESSION['flash']='Course added.';
        header("Location: $selfFile?section=courses"); exit;
    }
    if($action==='toggle_course'){
        $cid=(int)($_POST['course_id']??0);
        $conn->query("UPDATE instructor_courses SET is_active=1-is_active WHERE course_id=$cid AND instructor_id=$instructor_id");
        header("Location: $selfFile?section=courses"); exit;
    }
    if($action==='delete_course'){
        $cid=(int)($_POST['course_id']??0);
        $conn->query("DELETE FROM instructor_courses WHERE course_id=$cid AND instructor_id=$instructor_id");
        header("Location: $selfFile?section=courses"); exit;
    }

    // Update profile
    if($action==='update_profile'){
        $name=esc($conn,$_POST['fullname']??'');
        $email=esc($conn,$_POST['email']??'');
        $mobile=esc($conn,$_POST['mobile']??'');
        $bio=esc($conn,$_POST['bio']??'');
        // Handle certificate upload
        $certSQL='';
        if(isset($_FILES['certificate_image'])&&$_FILES['certificate_image']['error']===0){
            $ext=strtolower(pathinfo($_FILES['certificate_image']['name'],PATHINFO_EXTENSION));
            if(in_array($ext,['jpg','jpeg','png','gif','webp','pdf'])){
                $uploadPath='../assets/uploads/certificates/';
                if(!is_dir($uploadPath))mkdir($uploadPath,0755,true);
                $fname='cert_'.$instructor_id.'_'.time().'.'.$ext;
                if(move_uploaded_file($_FILES['certificate_image']['tmp_name'],$uploadPath.$fname)){
                    $certSQL=",certificate_image='$fname'";
                }
            }
        }
        $conn->query("UPDATE instructors SET fullname='$name',email='$email',mobile='$mobile',bio='$bio',modified_date=NOW()$certSQL WHERE instructor_id=$instructor_id");
        $_SESSION['flash']='Profile updated.';
        header("Location: $selfFile?section=profile"); exit;
    }

    // Mark notifications read
    if($action==='mark_read'){
        $conn->query("UPDATE notifications SET is_read=1 WHERE user_id=$instructor_id AND user_type='instructor'");
        header("Location: $selfFile?section=messages"); exit;
    }
}

// ── data ──────────────────────────────────────────────────────
$instructor=q($conn,"SELECT * FROM instructors WHERE instructor_id=$instructor_id")->fetch_assoc();
$unreadCnt=(int)q($conn,"SELECT COUNT(*) AS c FROM notifications WHERE user_id=$instructor_id AND user_type='instructor' AND is_read=0")->fetch_assoc()['c'];

$pendingAppts=q($conn,"
    SELECT a.*,ic.course_type,s.fullname AS sname,s.email AS semail
    FROM appointments a
    JOIN instructor_courses ic ON ic.course_id=a.course_id
    JOIN students s ON s.student_id=a.student_id
    WHERE a.instructor_id=$instructor_id AND a.status='pending'
    ORDER BY a.start_date ASC, a.requested_at ASC
");

$allAppts=q($conn,"
    SELECT a.*,ic.course_type,s.fullname AS sname,
           (SELECT SUM(hours_covered) FROM sessions WHERE appointment_id=a.appointment_id AND status='completed') AS hours_done,
           tc.cert_id
    FROM appointments a
    JOIN instructor_courses ic ON ic.course_id=a.course_id
    JOIN students s ON s.student_id=a.student_id
    LEFT JOIN tdc_certificates tc ON tc.appointment_id=a.appointment_id
    WHERE a.instructor_id=$instructor_id
    ORDER BY a.start_date DESC, a.requested_at DESC
");

$myStudents=q($conn,"
    SELECT DISTINCT s.student_id,s.fullname,s.email,
        (SELECT ic.course_type FROM appointments a2 JOIN instructor_courses ic ON ic.course_id=a2.course_id
         WHERE a2.instructor_id=$instructor_id AND a2.student_id=s.student_id ORDER BY a2.requested_at DESC LIMIT 1) AS latest_course,
        (SELECT a3.status FROM appointments a3 WHERE a3.instructor_id=$instructor_id AND a3.student_id=s.student_id ORDER BY a3.requested_at DESC LIMIT 1) AS latest_status
    FROM students s
    WHERE EXISTS(SELECT 1 FROM appointments a WHERE a.instructor_id=$instructor_id AND a.student_id=s.student_id AND a.status IN('approved','in_progress','completed','pending'))
    ORDER BY s.fullname ASC
");

$unratedSessions=q($conn,"
    SELECT sess.*,a.course_id,ic.course_type,s.fullname AS sname
    FROM sessions sess
    JOIN appointments a ON a.appointment_id=sess.appointment_id
    JOIN instructor_courses ic ON ic.course_id=a.course_id
    JOIN students s ON s.student_id=sess.student_id
    WHERE sess.instructor_id=$instructor_id AND sess.status='completed' AND (sess.performance_rating IS NULL OR sess.grade IS NULL)
    AND ic.course_type LIKE 'TDC%'
    ORDER BY sess.session_date DESC
");

$myCourses=q($conn,"SELECT * FROM instructor_courses WHERE instructor_id=$instructor_id ORDER BY course_type");
$notifs=q($conn,"SELECT * FROM notifications WHERE user_id=$instructor_id AND user_type='instructor' ORDER BY created_at DESC LIMIT 30");

$statPending=(int)q($conn,"SELECT COUNT(*) AS c FROM appointments WHERE instructor_id=$instructor_id AND status='pending'")->fetch_assoc()['c'];
$statActive=(int)q($conn,"SELECT COUNT(*) AS c FROM appointments WHERE instructor_id=$instructor_id AND status IN('approved','in_progress')")->fetch_assoc()['c'];
$statDone=(int)q($conn,"SELECT COUNT(*) AS c FROM appointments WHERE instructor_id=$instructor_id AND status='completed'")->fetch_assoc()['c'];
$avgRating=q($conn,"SELECT ROUND(AVG(rating),1) AS r FROM instructor_ratings WHERE instructor_id=$instructor_id")->fetch_assoc()['r'];

// Top 5 students by grade for this instructor
$topStudents=q($conn,"SELECT s.student_id,s.fullname,ROUND(AVG(sess.grade),1) AS avg_grade,COUNT(sess.session_id) AS sess_cnt FROM sessions sess JOIN students s ON s.student_id=sess.student_id WHERE sess.instructor_id=$instructor_id AND sess.grade IS NOT NULL AND DATE_FORMAT(sess.session_date,'%Y-%m')=DATE_FORMAT(NOW(),'%Y-%m') GROUP BY s.student_id ORDER BY avg_grade DESC LIMIT 5");

$flash=$_SESSION['flash']??null; unset($_SESSION['flash']);
$scmS=['pending'=>'bkp','approved'=>'bksu','in_progress'=>'bkb','completed'=>'bkg','rejected'=>'bkr','cancelled'=>'bkd'];
$scmL=['pending'=>'Pending','approved'=>'Approved','in_progress'=>'In Progress','completed'=>'Completed','rejected'=>'Rejected','cancelled'=>'Cancelled'];
$uploadDir='../assets/uploads/certificates/';
$courseTypeOpts=['TDC Face-to-Face','TDC Online','PDC Car Manual','PDC Car Automatic','PDC Motorcycle Manual','PDC Motorcycle Automatic'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Instructor Dashboard – ABROT</title>
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
.profile-avatar{width:72px;height:72px;border-radius:50%;background:var(--accent);display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:700;color:#fff;flex-shrink:0}
.grade-chip{display:inline-flex;align-items:center;justify-content:center;padding:2px 10px;border-radius:20px;font-size:12px;font-weight:600}
.grade-bar{height:6px;background:var(--border);border-radius:3px;overflow:hidden;margin-top:3px}
.grade-fill{height:100%;border-radius:3px}
</style>
</head>
<body>
<div class="sidebar">
    <div class="brand"><i class="fa-solid fa-chalkboard-user me-2"></i>ABROT Instructor</div>
    <a class="nav-item <?= $activeSection==='dashboard'?'active':'' ?>" onclick="showSection('dashboard')" href="#">
        <i class="fa-solid fa-gauge"></i> Dashboard</a>
    <a class="nav-item <?= $activeSection==='appointments'?'active':'' ?>" onclick="showSection('appointments')" href="#">
        <i class="fa-solid fa-calendar-check"></i> Appointments
        <?php if($statPending>0): ?><span class="tag tag-amber"><?= $statPending ?></span><?php endif; ?></a>
    <a class="nav-item <?= $activeSection==='students'?'active':'' ?>" onclick="showSection('students')" href="#">
        <i class="fa-solid fa-users"></i> My Students</a>
    <a class="nav-item <?= $activeSection==='performance'?'active':'' ?>" onclick="showSection('performance')" href="#">
        <i class="fa-solid fa-star"></i> Performance
        <?php if($unratedSessions->num_rows>0): ?><span class="tag tag-amber"><?= $unratedSessions->num_rows ?></span><?php endif; ?></a>
    <a class="nav-item <?= $activeSection==='courses'?'active':'' ?>" onclick="showSection('courses')" href="#">
        <i class="fa-solid fa-book-open"></i> My Courses</a>
    <a class="nav-item <?= $activeSection==='messages'?'active':'' ?>" onclick="showSection('messages')" href="#">
        <i class="fa-solid fa-bell"></i> Notifications
        <?php if($unreadCnt>0): ?><span class="tag tag-amber"><?= $unreadCnt ?></span><?php endif; ?></a>
    <a class="nav-item <?= $activeSection==='profile'?'active':'' ?>" onclick="showSection('profile')" href="#">
        <i class="fa-solid fa-user-circle"></i> Profile</a>
    <div style="flex:1"></div>
    <a class="nav-item" href="instructor_logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
</div>

<div class="main">
<div class="page-header">
    <div class="page-title" id="page-title-text">Dashboard</div>
    <div style="font-size:12px;color:var(--muted)">Welcome, <?= htmlspecialchars($instructor['fullname']) ?></div>
</div>

<?php if($flash): ?><div class="flash flash-ok"><i class="fa-solid fa-circle-check me-2"></i><?= htmlspecialchars($flash) ?></div><?php endif; ?>

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
</div>

<?php if($statPending>0): ?>
<div class="panel">
    <div class="panel-head"><div class="panel-title" style="color:var(--amber)"><i class="fa-solid fa-hourglass-half"></i> Pending Approval</div></div>
    <table class="tbl"><thead><tr><th>Student</th><th>Course</th><th>Start Date</th><th>Hours</th><th>Total Price</th><th></th></tr></thead><tbody>
    <?php $pendingAppts->data_seek(0); while($pa=$pendingAppts->fetch_assoc()): $tc=$cColors[$pa['course_type']]??'tag-gray'; ?>
    <tr>
        <td><?= htmlspecialchars($pa['sname']) ?></td>
        <td><span class="tag <?= $tc ?>"><?= htmlspecialchars($pa['course_type']) ?></span></td>
        <td><?= date('M d, Y',strtotime($pa['start_date'])) ?></td>
        <td><?= $pa['total_hours'] ?> hrs</td>
        <td>₱<?= number_format($pa['total_price'],2) ?></td>
        <td style="display:flex;gap:6px">
            <button class="be" style="font-size:11px" onclick="openApproveModal(<?= $pa['appointment_id'] ?>,'<?= addslashes($pa['sname']) ?>',true)"><i class="fa-solid fa-check"></i> Approve</button>
            <button class="ber" style="font-size:11px" onclick="openApproveModal(<?= $pa['appointment_id'] ?>,'<?= addslashes($pa['sname']) ?>',false)">Reject</button>
        </td>
    </tr>
    <?php endwhile; ?>
    </tbody></table>
</div>
<?php endif; ?>
</div>

<!-- ══ APPOINTMENTS ═══════════════════════════════════════════ -->
<div id="section-appointments" class="section <?= $activeSection==='appointments'?'active':'' ?>">
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-calendar-check"></i> All Appointments</div></div>
    <table class="tbl"><thead><tr><th>Student</th><th>Course</th><th>Start Date</th><th>Progress</th><th>TDC Grade</th><th>Status</th><th>Certificate</th><th>Actions</th></tr></thead><tbody>
    <?php $allAppts->data_seek(0); if($allAppts->num_rows===0): ?>
    <tr><td colspan="8" style="color:var(--muted);text-align:center;padding:24px">No appointments yet.</td></tr>
    <?php else: while($ap=$allAppts->fetch_assoc()):
        $tc=$cColors[$ap['course_type']]??'tag-gray';
        $hoursDone=(float)($ap['hours_done']??0);
        $pct=min(100,round($hoursDone/$ap['total_hours']*100));
        $isTDC=stripos($ap['course_type'],'TDC')===0;
    ?>
    <tr>
        <td><?= htmlspecialchars($ap['sname']) ?></td>
        <td><span class="tag <?= $tc ?>"><?= htmlspecialchars($ap['course_type']) ?></span></td>
        <td><?= date('M d, Y',strtotime($ap['start_date'])) ?></td>
        <td style="min-width:100px">
            <div class="prog-bar"><div class="prog-fill" style="width:<?= $pct ?>%"></div></div>
            <div style="font-size:10px;color:var(--muted);margin-top:2px"><?= $hoursDone ?>/<?= $ap['total_hours'] ?> hrs</div>
        </td>
        <td><?php if($isTDC&&isset($ap['tdc_overall_grade'])&&$ap['tdc_overall_grade']): $gc=$ap['tdc_overall_grade']>=80?'var(--green)':($ap['tdc_overall_grade']>=60?'var(--amber)':'var(--red)'); ?>
            <strong style="color:<?= $gc ?>"><?= $ap['tdc_overall_grade'] ?>/100</strong>
        <?php elseif($isTDC): ?>—<?php else: ?><span style="color:var(--muted);font-size:11px">N/A</span><?php endif; ?></td>
        <td>
            <?php if($ap['status']==='pending'): ?>
            <div style="display:flex;gap:4px">
                <button class="be" style="font-size:10px;padding:4px 8px" onclick="openApproveModal(<?= $ap['appointment_id'] ?>,'<?= addslashes($ap['sname']) ?>',true)">Approve</button>
                <button class="ber" style="font-size:10px;padding:4px 8px" onclick="openApproveModal(<?= $ap['appointment_id'] ?>,'<?= addslashes($ap['sname']) ?>',false)">Reject</button>
            </div>
            <?php else: ?><span class="<?= $scmS[$ap['status']]??'bkd' ?>"><?= $scmL[$ap['status']]??$ap['status'] ?></span><?php endif; ?>
        </td>
        <td><?php if($ap['cert_id']): ?><span class="cert-badge"><i class="fa-solid fa-certificate"></i> Issued</span><?php else: ?>—<?php endif; ?></td>
        <td style="display:flex;gap:4px;flex-wrap:wrap">
            <?php if(in_array($ap['status'],['approved','in_progress'])): ?>
            <button class="bds" style="font-size:10px" onclick="openSessionModal(<?= $ap['appointment_id'] ?>,<?= $ap['student_id'] ?>,'<?= addslashes($ap['sname']) ?>','<?= $isTDC?'TDC':'PDC' ?>')">
                <i class="fa-solid fa-plus"></i> Log Session
            </button>
            <?php endif; ?>
            <?php if($ap['status']==='completed' && $isTDC && !$ap['cert_id']): ?>
            <button class="be" style="font-size:10px;background:var(--amber);color:#000" onclick="openCertModal(<?= $ap['appointment_id'] ?>, '<?= addslashes($ap['sname']) ?>')">
                <i class="fa-solid fa-certificate"></i> Issue Cert
            </button>
            <?php endif; ?>
        </td>
    </tr>
    <?php endwhile; endif; ?>
    </tbody></table>
</div>
</div>

<!-- ══ MY STUDENTS ════════════════════════════════════════════ -->
<div id="section-students" class="section <?= $activeSection==='students'?'active':'' ?>">

<!-- Top Students -->
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-trophy" style="color:var(--amber)"></i> Top 5 Students This Month</div></div>
    <table class="tbl"><thead><tr><th>#</th><th>Student</th><th>Avg Grade</th><th>Sessions</th></tr></thead><tbody>
    <?php $topStudents->data_seek(0); $rank=1; if($topStudents->num_rows===0): ?>
    <tr><td colspan="4" style="color:var(--muted);text-align:center;padding:16px">No grades this month.</td></tr>
    <?php else: while($ts=$topStudents->fetch_assoc()): $medals=['🥇','🥈','🥉','4️⃣','5️⃣']; $gc=$ts['avg_grade']>=80?'var(--green)':($ts['avg_grade']>=60?'var(--amber)':'var(--red)'); ?>
    <tr><td><?= $medals[$rank-1]??$rank ?></td><td><?= htmlspecialchars($ts['fullname']) ?></td><td style="color:<?= $gc ?>;font-weight:600"><?= $ts['avg_grade'] ?>/100</td><td><?= $ts['sess_cnt'] ?></td></tr>
    <?php $rank++; endwhile; endif; ?>
    </tbody></table>
</div>

<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-users"></i> My Students</div></div>
    <table class="tbl"><thead><tr><th>Student</th><th>Email</th><th>Latest Course</th><th>Status</th><th>Sessions</th><th>Avg Grade</th></tr></thead><tbody>
    <?php $myStudents->data_seek(0); if($myStudents->num_rows===0): ?>
    <tr><td colspan="6" style="color:var(--muted);text-align:center;padding:24px">No students yet.</td></tr>
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
    </tr>
    <?php endwhile; endif; ?>
    </tbody></table>
</div>
</div>

<!-- ══ PERFORMANCE ════════════════════════════════════════════ -->
<div id="section-performance" class="section <?= $activeSection==='performance'?'active':'' ?>">

<?php $unratedSessions->data_seek(0); if($unratedSessions->num_rows>0): ?>
<div class="panel">
    <div class="panel-head"><div class="panel-title" style="color:var(--amber)"><i class="fa-solid fa-star-half-stroke"></i> TDC Sessions Needing Grade / Rating</div></div>
    <table class="tbl"><thead><tr><th>Student</th><th>Course</th><th>Session Date</th><th>Session #</th><th>Hours</th><th>Action</th></tr></thead><tbody>
    <?php $unratedSessions->data_seek(0); while($us=$unratedSessions->fetch_assoc()): $tc=$cColors[$us['course_type']]??'tag-gray'; ?>
    <tr>
        <td><?= htmlspecialchars($us['sname']) ?></td>
        <td><span class="tag <?= $tc ?>"><?= htmlspecialchars($us['course_type']) ?></span></td>
        <td><?= date('M d, Y',strtotime($us['session_date'])) ?></td>
        <td>Day <?= $us['session_number']??'?' ?></td>
        <td><?= $us['hours_covered'] ?> hrs</td>
        <td><button class="be" style="font-size:11px" onclick="openPerfModal(<?= $us['session_id'] ?>,'<?= addslashes($us['sname']) ?>','<?= date('M d, Y',strtotime($us['session_date'])) ?>',<?= $us['session_number']??1 ?>)">
            <i class="fa-solid fa-pencil"></i> Grade & Rate
        </button></td>
    </tr>
    <?php endwhile; ?>
    </tbody></table>
</div>
<?php endif; ?>

<!-- All sessions with ratings -->
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-list-check"></i> All Session Records</div></div>
    <table class="tbl"><thead><tr><th>Date</th><th>Student</th><th>Course</th><th>Session #</th><th>Hours</th><th>Grade (TDC)</th><th>Rating</th><th>Improvements</th></tr></thead><tbody>
    <?php
    $allSess=q($conn,"
        SELECT sess.*,ic.course_type,s.fullname AS sname
        FROM sessions sess
        JOIN appointments a ON a.appointment_id=sess.appointment_id
        JOIN instructor_courses ic ON ic.course_id=a.course_id
        JOIN students s ON s.student_id=sess.student_id
        WHERE sess.instructor_id=$instructor_id
        ORDER BY sess.session_date DESC
    ");
    if($allSess->num_rows===0): ?><tr><td colspan="8" style="color:var(--muted);text-align:center;padding:24px">No sessions yet.</td></tr>
    <?php else: while($ss=$allSess->fetch_assoc()): $tc=$cColors[$ss['course_type']]??'tag-gray'; $gc=$ss['grade']?($ss['grade']>=80?'var(--green)':($ss['grade']>=60?'var(--amber)':'var(--red)')):''; ?>
    <tr>
        <td><?= date('M d',strtotime($ss['session_date'])) ?></td>
        <td><?= htmlspecialchars($ss['sname']) ?></td>
        <td><span class="tag <?= $tc ?>"><?= htmlspecialchars($ss['course_type']) ?></span></td>
        <td>Day <?= $ss['session_number']??'—' ?></td>
        <td><?= $ss['hours_covered'] ?> hrs</td>
        <td><?= $ss['grade']?'<strong style="color:'.$gc.'">'.$ss['grade'].'/100</strong>':'<span style="color:var(--muted)">—</span>' ?></td>
        <td>
            <?php if($ss['performance_rating']): ?>
            <span style="color:var(--amber)"><?= str_repeat('★',$ss['performance_rating']).str_repeat('☆',5-$ss['performance_rating']) ?></span>
            <?php else: ?><span class="bkp">Not rated</span><?php endif; ?>
        </td>
        <td style="font-size:11px;color:var(--muted);max-width:150px"><?= $ss['improvements']?htmlspecialchars(substr($ss['improvements'],0,60)).'…':'—' ?></td>
    </tr>
    <?php endwhile; endif; ?>
    </tbody></table>
</div>

<!-- Completed TDC appointments awaiting certificate -->
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-certificate" style="color:var(--amber)"></i> TDC Complete – Issue Certificates</div></div>
    <?php $certPending=q($conn,"SELECT a.*,s.fullname AS sname,a.tdc_overall_grade FROM appointments a JOIN students s ON s.student_id=a.student_id JOIN instructor_courses ic ON ic.course_id=a.course_id WHERE a.instructor_id=$instructor_id AND a.status='completed' AND ic.course_type LIKE 'TDC%' AND NOT EXISTS(SELECT 1 FROM tdc_certificates WHERE appointment_id=a.appointment_id)");
    if($certPending->num_rows===0): ?><div style="padding:16px;font-size:12px;color:var(--muted)">No pending certificates.</div>
    <?php else: while($cp=$certPending->fetch_assoc()): $gc=($cp['tdc_overall_grade']&&$cp['tdc_overall_grade']>=80)?'var(--green)':(($cp['tdc_overall_grade']&&$cp['tdc_overall_grade']>=60)?'var(--amber)':'var(--red)'); ?>
    <div style="padding:12px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px">
        <div style="flex:1">
            <div style="font-weight:600;font-size:13px"><?= htmlspecialchars($cp['sname']) ?></div>
            <div style="font-size:11px;color:var(--muted)">Completed <?= date('M d, Y',strtotime($cp['completed_at'])) ?></div>
            <?php if($cp['tdc_overall_grade']): ?><div style="font-size:11px;margin-top:3px">Overall Grade: <strong style="color:<?= $gc ?>"><?= $cp['tdc_overall_grade'] ?>/100</strong></div><?php endif; ?>
        </div>
        <button class="be" style="background:var(--amber);color:#000;font-size:11px" onclick="openCertModal(<?= $cp['appointment_id'] ?>,'<?= addslashes($cp['sname']) ?>')">
            <i class="fa-solid fa-certificate"></i> Issue Cert
        </button>
    </div>
    <?php endwhile; endif; ?>
</div>

<!-- Instructor ratings received -->
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-medal"></i> Student Reviews of You</div></div>
    <?php $myRatings=q($conn,"SELECT ir.*,s.fullname AS sname,ic.course_type FROM instructor_ratings ir JOIN students s ON s.student_id=ir.student_id JOIN appointments a ON a.appointment_id=ir.appointment_id JOIN instructor_courses ic ON ic.course_id=a.course_id WHERE ir.instructor_id=$instructor_id ORDER BY ir.rated_at DESC");
    if($myRatings->num_rows===0): ?><div style="padding:20px;text-align:center;color:var(--muted);font-size:12px">No reviews yet.</div>
    <?php else: while($rv=$myRatings->fetch_assoc()): ?>
    <div style="padding:12px 16px;border-bottom:1px solid var(--border)">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
            <div style="font-size:12px;font-weight:600"><?= htmlspecialchars($rv['sname']) ?></div>
            <span class="tag <?= $cColors[$rv['course_type']]??'tag-gray' ?>"><?= htmlspecialchars($rv['course_type']) ?></span>
            <div style="color:var(--amber);font-size:12px;margin-left:auto"><?= stars($rv['rating']) ?></div>
            <div style="font-size:10px;color:var(--muted)"><?= date('M d, Y',strtotime($rv['rated_at'])) ?></div>
        </div>
        <?php if($rv['comment']): ?><div style="font-size:12px;color:var(--muted)"><?= nl2br(htmlspecialchars($rv['comment'])) ?></div><?php endif; ?>
    </div>
    <?php endwhile; endif; ?>
</div>
</div>

<!-- ══ MY COURSES ═════════════════════════════════════════════ -->
<div id="section-courses" class="section <?= $activeSection==='courses'?'active':'' ?>">
<div class="panel mb-3">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-plus"></i> Add New Course</div></div>
    <div style="padding:16px">
    <form method="POST" style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:10px;align-items:end">
        <input type="hidden" name="action" value="add_course">
        <div><label class="form-label">Course Type</label>
            <select name="course_type" class="di" required>
                <option value="">— Select —</option>
                <?php foreach($courseTypeOpts as $opt): ?>
                <option value="<?= $opt ?>"><?= $opt ?></option>
                <?php endforeach; ?>
            </select></div>
        <div><label class="form-label">Price per Hour (₱)</label>
            <input type="number" name="price_per_hr" class="di" min="0" step="0.01" required placeholder="e.g. 134.00"></div>
        <div><label class="form-label">Description (optional)</label>
            <input type="text" name="description" class="di" placeholder="Short description"></div>
        <button type="submit" class="be" style="height:36px"><i class="fa-solid fa-plus"></i> Add</button>
    </form>
    </div>
</div>
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-book-open"></i> My Courses</div></div>
    <table class="tbl"><thead><tr><th>Course Type</th><th>Price/Hr</th><th>TDC Hrs</th><th>Description</th><th>Status</th><th></th></tr></thead><tbody>
    <?php $myCourses->data_seek(0); if($myCourses->num_rows===0): ?>
    <tr><td colspan="6" style="color:var(--muted);text-align:center;padding:24px">No courses yet. Add one above.</td></tr>
    <?php else: while($c=$myCourses->fetch_assoc()): $tc=$cColors[$c['course_type']]??'tag-gray'; $isTDC=stripos($c['course_type'],'TDC')===0; ?>
    <tr>
        <td><span class="tag <?= $tc ?>"><?= htmlspecialchars($c['course_type']) ?></span></td>
        <td>₱<?= number_format($c['price_per_hr'],2) ?></td>
        <td><?= $isTDC ? '<strong>15 hrs fixed</strong>' : 'Flexible' ?></td>
        <td style="font-size:11px;color:var(--muted)"><?= $c['description']?htmlspecialchars($c['description']):'—' ?></td>
        <td><?= $c['is_active'] ? '<span class="bkg">Active</span>' : '<span class="bkd">Inactive</span>' ?></td>
        <td style="display:flex;gap:4px">
            <form method="POST"><input type="hidden" name="action" value="toggle_course"><input type="hidden" name="course_id" value="<?= $c['course_id'] ?>">
            <button type="submit" class="bds" style="font-size:11px"><?= $c['is_active']?'Deactivate':'Activate' ?></button></form>
            <form method="POST" onsubmit="return confirm('Delete this course?')"><input type="hidden" name="action" value="delete_course"><input type="hidden" name="course_id" value="<?= $c['course_id'] ?>">
            <button type="submit" class="ber" style="font-size:11px">Delete</button></form>
        </td>
    </tr>
    <?php endwhile; endif; ?>
    </tbody></table>
</div>
</div>

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
            <div>
                <label class="form-label">Full Name *</label>
                <input type="text" name="fullname" class="di" value="<?= htmlspecialchars($instructor['fullname']) ?>" required>
            </div>
            <div>
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="di" value="<?= htmlspecialchars($instructor['email']) ?>" required>
            </div>
            <div>
                <label class="form-label">Mobile Number</label>
                <input type="text" name="mobile" class="di" value="<?= htmlspecialchars($instructor['mobile']??'') ?>" placeholder="+63 9XX XXX XXXX">
            </div>
            <div>
                <label class="form-label">Upload New Certificate (Image/PDF)</label>
                <input type="file" name="certificate_image" class="di" accept="image/*,.pdf">
                <?php if($certOk): ?><div style="font-size:10px;color:var(--green);margin-top:4px"><i class="fa-solid fa-check me-1"></i>Certificate uploaded. Upload new to replace.</div><?php endif; ?>
            </div>
            <div style="grid-column:1/-1">
                <label class="form-label">Bio / Short Description</label>
                <textarea name="bio" class="di" rows="3" placeholder="Describe your experience, specializations, teaching style…"><?= htmlspecialchars($instructor['bio']??'') ?></textarea>
            </div>
        </div>
        <div style="margin-top:16px;display:flex;align-items:center;gap:12px">
            <button type="submit" class="be"><i class="fa-solid fa-save"></i> Save Profile</button>
            <span style="font-size:11px;color:var(--muted)">To change password, use the Forgot Password flow on the login page.</span>
        </div>
    </form>
    </div>
</div>

<?php if($certOk): ?>
<div class="panel">
    <div class="panel-head"><div class="panel-title"><i class="fa-solid fa-id-card"></i> Current Certificate</div></div>
    <div style="padding:16px">
        <img src="<?= htmlspecialchars('../assets/uploads/certificates/'.$instructor['certificate_image']) ?>" style="max-height:200px;border-radius:8px;cursor:pointer;border:1px solid var(--border)" onclick="window.open(this.src)">
        <div style="font-size:11px;color:var(--muted);margin-top:8px">Click to view full size</div>
    </div>
</div>
<?php endif; ?>
</div>

</div><!-- /main -->

<!-- ══ APPROVE/REJECT MODAL ════════════════════════════════════ -->
<div id="approveModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:200;align-items:center;justify-content:center">
<div style="background:var(--bg2);border:1px solid var(--border);border-radius:12px;width:420px;max-width:95vw;padding:24px">
    <div style="font-size:15px;font-weight:600;margin-bottom:4px" id="am_title"></div>
    <div id="am_sname" style="font-size:12px;color:var(--muted);margin-bottom:16px"></div>
    <form method="POST" id="am_form">
    <input type="hidden" name="appointment_id" id="am_appt_id">
    <input type="hidden" name="action" id="am_action">
    <div style="margin-bottom:12px">
        <label class="form-label">Note to Student (optional)</label>
        <textarea name="instructor_note" class="di" rows="3" placeholder="Add a note…"></textarea>
    </div>
    <div style="display:flex;gap:8px;justify-content:flex-end">
        <button type="button" class="bds" onclick="closeApproveModal()">Cancel</button>
        <button type="submit" class="be" id="am_btn"></button>
    </div>
    </form>
</div></div>

<!-- ══ LOG SESSION MODAL ════════════════════════════════════════ -->
<div id="sessionModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:200;align-items:center;justify-content:center">
<div style="background:var(--bg2);border:1px solid var(--border);border-radius:12px;width:460px;max-width:95vw;padding:24px;overflow-y:auto;max-height:90vh">
    <div style="font-size:15px;font-weight:600;margin-bottom:4px"><i class="fa-solid fa-plus me-2" style="color:var(--accent)"></i>Log Session</div>
    <div id="sm_sname" style="font-size:12px;color:var(--muted);margin-bottom:16px"></div>
    <form method="POST">
    <input type="hidden" name="action" value="add_session">
    <input type="hidden" name="appointment_id" id="sm_appt_id">
    <div style="margin-bottom:12px">
        <label class="form-label">Session Date</label>
        <!-- TDC: only Saturday & Sunday allowed -->
        <input type="date" name="session_date" id="sm_date" class="di" value="<?= $today ?>" required>
        <div id="sm_day_hint" style="font-size:10px;color:var(--amber);margin-top:3px;display:none"><i class="fa-solid fa-info-circle"></i> TDC sessions are only available on Saturdays and Sundays.</div>
    </div>
    <div style="margin-bottom:12px">
        <label class="form-label">Hours Covered</label>
        <input type="number" name="hours_covered" class="di" min="0.5" max="8" step="0.5" value="3" required>
    </div>
    <!-- TDC Grade fields -->
    <div id="sm_tdc_fields">
        <div style="margin-bottom:12px">
            <label class="form-label">Session Grade (1–100) <span style="color:var(--accent)">TDC only</span></label>
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
    <div style="display:flex;gap:8px;justify-content:flex-end">
        <button type="button" class="bds" onclick="closeSessionModal()">Cancel</button>
        <button type="submit" class="be"><i class="fa-solid fa-check"></i> Log Session</button>
    </div>
    </form>
</div></div>

<!-- ══ PERFORMANCE RATING MODAL ═════════════════════════════════ -->
<div id="perfModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:200;align-items:center;justify-content:center">
<div style="background:var(--bg2);border:1px solid var(--border);border-radius:12px;width:440px;max-width:95vw;padding:24px;overflow-y:auto;max-height:90vh">
    <div style="font-size:15px;font-weight:600;margin-bottom:4px"><i class="fa-solid fa-star me-2" style="color:var(--amber)"></i>Grade & Rate Session</div>
    <div id="pm_label" style="font-size:12px;color:var(--muted);margin-bottom:16px"></div>
    <form method="POST">
    <input type="hidden" name="action" value="rate_student">
    <input type="hidden" name="session_id" id="pm_sess_id">
    <div style="margin-bottom:12px">
        <label class="form-label">Numeric Grade (1–100) *</label>
        <input type="number" name="grade" class="di" min="1" max="100" required placeholder="e.g. 88">
        <div style="font-size:10px;color:var(--muted);margin-top:3px">80–100: Excellent | 60–79: Good | Below 60: Needs Improvement</div>
    </div>
    <div style="margin-bottom:12px">
        <label class="form-label">Suggested Improvements</label>
        <textarea name="improvements" class="di" rows="2" placeholder="What should the student improve?"></textarea>
    </div>
    <div style="margin-bottom:12px">
        <label class="form-label">Performance Rating (1–5 stars) *</label>
        <div style="display:flex;gap:6px">
            <?php for($i=1;$i<=5;$i++): ?>
            <label style="cursor:pointer;font-size:26px;color:var(--border)" onclick="setPerfRating(<?= $i ?>)" id="pstar<?= $i ?>">★</label>
            <?php endfor; ?>
        </div>
        <input type="hidden" name="performance_rating" id="pm_rating" required>
    </div>
    <div style="margin-bottom:12px">
        <label class="form-label">Performance Notes</label>
        <textarea name="performance_notes" class="di" rows="2" placeholder="Additional feedback…"></textarea>
    </div>
    <div style="display:flex;gap:8px;justify-content:flex-end">
        <button type="button" class="bds" onclick="closePerfModal()">Cancel</button>
        <button type="submit" class="be"><i class="fa-solid fa-paper-plane"></i> Save</button>
    </div>
    </form>
</div></div>

<!-- ══ CERT MODAL ════════════════════════════════════════════════ -->
<div id="certModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:200;align-items:center;justify-content:center">
<div style="background:var(--bg2);border:1px solid var(--border);border-radius:12px;width:440px;max-width:95vw;padding:24px">
    <div style="font-size:15px;font-weight:600;margin-bottom:4px"><i class="fa-solid fa-certificate me-2" style="color:var(--amber)"></i>Issue TDC Certificate</div>
    <div id="cm_sname" style="font-size:12px;color:var(--muted);margin-bottom:16px"></div>
    <form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="action" value="issue_certificate">
    <input type="hidden" name="appointment_id" id="cm_appt_id">
    <div style="margin-bottom:12px">
        <label class="form-label">Certificate Number (optional)</label>
        <input type="text" name="cert_number" class="di" placeholder="e.g. TDC-2026-001">
    </div>
    <div style="margin-bottom:12px">
        <label class="form-label">Delivery Method *</label>
        <div style="display:flex;gap:12px;margin-top:6px">
            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:13px">
                <input type="radio" name="delivery_method" value="online" onchange="toggleCertUpload(true)" style="accent-color:var(--accent)">
                <i class="fa-solid fa-globe" style="color:var(--accent)"></i> Online (upload image)
            </label>
            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:13px">
                <input type="radio" name="delivery_method" value="face_to_face" checked onchange="toggleCertUpload(false)" style="accent-color:var(--accent)">
                <i class="fa-solid fa-handshake" style="color:var(--green)"></i> Face-to-Face
            </label>
        </div>
    </div>
    <div id="cert_image_wrap" style="margin-bottom:12px;display:none">
        <label class="form-label">Certificate Image (JPG/PNG) *</label>
        <input type="file" name="cert_image" class="di" accept="image/*" id="cert_image_input">
        <div style="font-size:10px;color:var(--muted);margin-top:3px">This image will be sent to the student as their certificate.</div>
    </div>
    <div style="display:flex;gap:8px;justify-content:flex-end">
        <button type="button" class="bds" onclick="closeCertModal()">Cancel</button>
        <button type="submit" class="be" style="background:var(--amber);color:#000"><i class="fa-solid fa-certificate"></i> Issue Certificate</button>
    </div>
    </form>
</div></div>

<script>
const T={dashboard:'Dashboard',appointments:'Appointments',students:'My Students',
  performance:'Performance',courses:'My Courses',messages:'Notifications',profile:'Profile'};

function showSection(n){
    document.querySelectorAll('.section').forEach(s=>s.classList.remove('active'));
    document.querySelectorAll('.nav-item').forEach(x=>x.classList.remove('active'));
    const el=document.getElementById('section-'+n);
    if(el)el.classList.add('active');
    document.getElementById('page-title-text').textContent=T[n]||n;
    document.querySelectorAll('.nav-item').forEach(x=>{
        if(x.getAttribute('onclick')&&x.getAttribute('onclick').includes("'"+n+"'"))x.classList.add('active');
    });
    const u=new URL(window.location.href);
    u.searchParams.set('section',n);
    window.history.pushState({},'',u);
}

let _sessionIsTDC=false;
function openApproveModal(id,sname,approve){
    document.getElementById('am_appt_id').value=id;
    document.getElementById('am_action').value=approve?'approve_appointment':'reject_appointment';
    document.getElementById('am_title').textContent=approve?'Approve Appointment':'Reject Appointment';
    document.getElementById('am_sname').textContent='Student: '+sname;
    document.getElementById('am_btn').innerHTML=approve?'<i class="fa-solid fa-check me-1"></i>Approve':'Reject';
    document.getElementById('am_btn').style.background=approve?'var(--accent)':'#e55';
    document.getElementById('approveModal').style.display='flex';
}
function closeApproveModal(){document.getElementById('approveModal').style.display='none';}

function openSessionModal(apptId,studentId,sname,type){
    _sessionIsTDC=(type==='TDC');
    document.getElementById('sm_appt_id').value=apptId;
    document.getElementById('sm_sname').textContent='Student: '+sname+' | '+type+' Session';
    document.getElementById('sm_tdc_fields').style.display=_sessionIsTDC?'block':'none';
    document.getElementById('sm_day_hint').style.display=_sessionIsTDC?'block':'none';
    // Enforce Sat/Sun only for TDC
    const dateInput=document.getElementById('sm_date');
    dateInput.removeEventListener('change',validateTDCDay);
    if(_sessionIsTDC)dateInput.addEventListener('change',validateTDCDay);
    document.getElementById('sessionModal').style.display='flex';
}
function validateTDCDay(){
    const d=new Date(this.value+'T00:00:00');
    const day=d.getDay();
    if(day!==0&&day!==6){
        alert('TDC sessions must be on Saturday or Sunday only.');
        this.value='';
    }
}
function closeSessionModal(){document.getElementById('sessionModal').style.display='none';}

function openPerfModal(sessId,sname,date,sessNum){
    document.getElementById('pm_sess_id').value=sessId;
    document.getElementById('pm_label').textContent='Student: '+sname+' | '+date+' (Day '+sessNum+')';
    document.getElementById('pm_rating').value='';
    for(let i=1;i<=5;i++)document.getElementById('pstar'+i).style.color='var(--border)';
    document.getElementById('perfModal').style.display='flex';
}
function closePerfModal(){document.getElementById('perfModal').style.display='none';}
function setPerfRating(n){
    document.getElementById('pm_rating').value=n;
    for(let i=1;i<=5;i++)document.getElementById('pstar'+i).style.color=i<=n?'var(--amber)':'var(--border)';
}

function openCertModal(apptId,sname){
    document.getElementById('cm_appt_id').value=apptId;
    document.getElementById('cm_sname').textContent='Student: '+sname;
    document.getElementById('cert_image_wrap').style.display='none';
    document.getElementById('certModal').style.display='flex';
}
function closeCertModal(){document.getElementById('certModal').style.display='none';}
function toggleCertUpload(show){
    document.getElementById('cert_image_wrap').style.display=show?'block':'none';
    document.getElementById('cert_image_input').required=show;
}

['approveModal','sessionModal','perfModal','certModal'].forEach(id=>{
    document.getElementById(id).addEventListener('click',function(e){if(e.target===this)this.style.display='none';});
});
</script>
</body>
</html>
