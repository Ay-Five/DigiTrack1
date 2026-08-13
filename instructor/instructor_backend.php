<?php
/**
 * instructor/instructor_backend.php
 *
 * OOP PILLARS DEMONSTRATED:
 *  - ENCAPSULATION  : Database class wraps mysqli
 *  - INHERITANCE    : InstructorDashboard extends BaseDashboard
 *  - ABSTRACTION    : handlePost() / loadData() contracts
 *  - POLYMORPHISM   : same handlePost() call works for Admin, Instructor, Student
 *
 * $conn and q() are kept available so instructor_dashboard1.php works unchanged.
 */

session_start();
if (!isset($_SESSION['instructor_id'])) {
    header('Location: ../instructor_login.php');
    exit;
}
$instructor_id = (int)$_SESSION['instructor_id'];

// ── Raw cancel-by-instructor handler (intercept before OOP layer) ─────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'cancel_appointment_instructor') {
    require_once '../includes/db.php';
    $apptId = (int)($_POST['appointment_id'] ?? 0);
    $reason = trim($_POST['cancel_reason'] ?? '');
    $safeReason = $conn->real_escape_string($reason);

    // Only allow cancel if: belongs to this instructor, status=approved, start_date is in the future
    $appt = $conn->query(
        "SELECT a.*, ic.course_type, s.fullname AS sname, s.student_id AS sid
         FROM appointments a
         JOIN instructor_courses ic ON ic.course_id=a.course_id
         JOIN students s ON s.student_id=a.student_id
         WHERE a.appointment_id=$apptId AND a.instructor_id=$instructor_id
           AND a.status IN('approved','in_progress')"
    )->fetch_assoc();

    if ($appt) {
        $conn->query("UPDATE appointments SET status='cancelled', instructor_note='$safeReason' WHERE appointment_id=$apptId");

        // Free up the schedule slot
        $courseId = (int)$appt['course_id'];
        $date = $appt['start_date'];
        $conn->query("UPDATE course_schedules SET booked_count=GREATEST(0,booked_count-1) WHERE course_id=$courseId AND schedule_date='$date'");

        // Notify student
        $sid = (int)$appt['sid'];
        $iname = $conn->real_escape_string($_SESSION['instructor_name'] ?? 'Your instructor');
        $ctype = $conn->real_escape_string($appt['course_type']);
        $reasonText = $reason ? ": $reason" : '.';
        $notifMsg = $conn->real_escape_string("❌ Your $ctype booking on $date was cancelled by $iname$reasonText");
        $conn->query("INSERT INTO notifications (user_id,user_type,message,type,created_at) VALUES ($sid,'student','$notifMsg','instructor_cancelled',NOW())");

        $_SESSION['flash'] = 'Appointment cancelled and student has been notified.';
    } else {
        $_SESSION['flash'] = 'Cannot cancel: appointment is already completed or does not belong to you.';
    }
    header('Location: instructor_dashboard.php?section=appointments');
    exit;
}

// ── 1. Raw connection — keeps $conn + q() alive for the view ─────────────────
require_once '../includes/db.php'; // gives $conn, $host, $user, $password, $database

// ── 2. OOP layer ──────────────────────────────────────────────────────────────
require_once '../includes/Database.php';
require_once '../includes/BaseDashboard.php';
require_once '../includes/Notifiable.php';
require_once '../includes/InstructorDashboard.php';

// OOP PILLAR: ENCAPSULATION
$db = new Database($host, $user, $password, $database);

// OOP PILLAR: INHERITANCE + ABSTRACTION
$dashboard = new InstructorDashboard($db, $instructor_id);

// OOP PILLAR: POLYMORPHISM
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dashboard->handlePost($_POST['action'] ?? '');
}

$dashboard->loadData();
$data = $dashboard->getData();

// ── 3. Helper functions the view uses ────────────────────────────────────────
if (!function_exists('q'))    { function q($c,$s){return $c->query($s);} }
if (!function_exists('esc'))  { function esc($c,$v){return $c->real_escape_string($v);} }
if (!function_exists('stars')){ function stars($n){$s='';for($i=1;$i<=5;$i++)$s.=$i<=$n?'★':'☆';return $s;} }
if (!function_exists('anonName')){
    function anonName($name){
        $name = trim((string)$name);
        if ($name === '') return 'Student';
        $len = mb_strlen($name);
        $visible = mb_substr($name, 0, min(3, $len));
        $rest = $len > 3 ? str_repeat('*', $len - 3) : '';
        return $visible . $rest;
    }
}

// ── 4. Variables the view expects ─────────────────────────────────────────────
$today    = date('Y-m-d');
$selfFile = basename($_SERVER['PHP_SELF']);

$cColors = [
    'TDC Face-to-Face'         => 'tag-amber',
    'TDC Online'               => 'tag-blue',
    'PDC Car (Manual/Automatic)'       => 'tag-green',
    'PDC Motorcycle (Manual/Automatic)' => 'tag-teal',
    'PDC Both (Car & Motorcycle)'      => 'tag-purple',
];

$scmS = ['pending'=>'bkp','approved'=>'bksu','in_progress'=>'bkb',
          'completed'=>'bkg','rejected'=>'bkr','cancelled'=>'bkd','no_show'=>'bkr'];
$scmL = ['pending'=>'Pending','approved'=>'Booked','in_progress'=>'In Progress',
          'completed'=>'Completed','rejected'=>'Rejected','cancelled'=>'Cancelled','no_show'=>'No Show'];

$uploadDir      = '../assets/uploads/certificates/';
$courseTypeOpts = ['TDC Face-to-Face','TDC Online','PDC Car (Manual/Automatic)','PDC Motorcycle (Manual/Automatic)','PDC Both (Car & Motorcycle)'];

$activeSection = preg_replace('/[^a-z_]/', '', $_GET['section'] ?? 'dashboard');
if (!empty($_GET['chat_student']) && $activeSection === 'dashboard') $activeSection = 'chat';
$viewStudentId = (int)($_GET['view_student'] ?? 0);
if ($viewStudentId > 0 && $activeSection === 'dashboard') $activeSection = 'student_profile';

// Flash
$flash    = $_SESSION['flash']     ?? null; unset($_SESSION['flash']);
$flashErr = $_SESSION['flash_err'] ?? null; unset($_SESSION['flash_err']);

// ── 5. Re-run all queries the view uses as real mysqli result objects ─────────

$instructor = q($conn,"SELECT * FROM instructors WHERE instructor_id=$instructor_id")->fetch_assoc();

$unreadCnt = (int)q($conn,"SELECT COUNT(*) AS c FROM notifications
    WHERE user_id=$instructor_id AND user_type='instructor' AND is_read=0")->fetch_assoc()['c'];

$myStudents = q($conn,"SELECT DISTINCT s.student_id,s.fullname,s.email,a.appointment_id,ic.course_type,a.status
    FROM appointments a
    JOIN students s ON s.student_id=a.student_id
    JOIN instructor_courses ic ON ic.course_id=a.course_id
    WHERE a.instructor_id=$instructor_id ORDER BY s.fullname");

$unreadMsgMap = [];
$umRes = q($conn,"SELECT sender_id,COUNT(*) AS cnt FROM messages
    WHERE receiver_id=$instructor_id AND receiver_type='instructor'
      AND is_read=0 AND sender_type='student' GROUP BY sender_id");
while($um=$umRes->fetch_assoc()) $unreadMsgMap[(int)$um['sender_id']]=(int)$um['cnt'];
$totalUnreadMsg = array_sum($unreadMsgMap);
$chatStudentId = (int)($_GET['chat_student'] ?? 0);

$pendingAppts = q($conn,"SELECT a.*,ic.course_type,s.fullname AS sname,s.email AS semail
    FROM appointments a
    JOIN instructor_courses ic ON ic.course_id=a.course_id
    JOIN students s ON s.student_id=a.student_id
    WHERE a.instructor_id=$instructor_id AND a.status='pending'
    ORDER BY a.start_date ASC, a.requested_at ASC");

$allAppts = q($conn,"SELECT a.*,ic.course_type,s.fullname AS sname,
    (SELECT SUM(hours_covered) FROM sessions WHERE appointment_id=a.appointment_id AND status='completed') AS hours_done
    FROM appointments a
    JOIN instructor_courses ic ON ic.course_id=a.course_id
    JOIN students s ON s.student_id=a.student_id
    WHERE a.instructor_id=$instructor_id
    ORDER BY a.start_date DESC, a.requested_at DESC");

$myStudents = q($conn,"SELECT DISTINCT s.student_id,s.fullname,s.email,
    (SELECT ic.course_type FROM appointments a2 JOIN instructor_courses ic ON ic.course_id=a2.course_id
     WHERE a2.instructor_id=$instructor_id AND a2.student_id=s.student_id ORDER BY a2.requested_at DESC LIMIT 1) AS latest_course,
    (SELECT a3.status FROM appointments a3 WHERE a3.instructor_id=$instructor_id AND a3.student_id=s.student_id ORDER BY a3.requested_at DESC LIMIT 1) AS latest_status
    FROM students s
    WHERE EXISTS(SELECT 1 FROM appointments a WHERE a.instructor_id=$instructor_id AND a.student_id=s.student_id AND a.status IN('approved','in_progress','completed','pending'))
       OR EXISTS(SELECT 1 FROM messages m
              WHERE ((m.sender_id=$instructor_id AND m.sender_type='instructor' AND m.receiver_id=s.student_id AND m.receiver_type='student')
                  OR (m.receiver_id=$instructor_id AND m.receiver_type='instructor' AND m.sender_id=s.student_id AND m.sender_type='student')))
    ORDER BY s.fullname ASC");

$unratedSessions = q($conn,"SELECT sess.*,a.course_id,ic.course_type,s.fullname AS sname
    FROM sessions sess
    JOIN appointments a ON a.appointment_id=sess.appointment_id
    JOIN instructor_courses ic ON ic.course_id=a.course_id
    JOIN students s ON s.student_id=sess.student_id
    WHERE sess.instructor_id=$instructor_id AND sess.status='completed'
      AND (
          (ic.course_type LIKE 'TDC%' AND sess.grade IS NULL)
          OR (ic.course_type LIKE 'PDC%' AND sess.performance_rating IS NULL)
      )
    ORDER BY sess.session_date DESC");

$myCourses = q($conn,"SELECT * FROM instructor_courses WHERE instructor_id=$instructor_id ORDER BY course_type");
$notifs    = q($conn,"SELECT * FROM notifications WHERE user_id=$instructor_id AND user_type='instructor' ORDER BY created_at DESC LIMIT 30");

$statPending = 0; // No more pending — all bookings are auto-approved
$statActive  = (int)q($conn,"SELECT COUNT(*) AS c FROM appointments WHERE instructor_id=$instructor_id AND status IN('approved','in_progress')")->fetch_assoc()['c'];
$statDone    = (int)q($conn,"SELECT COUNT(*) AS c FROM appointments WHERE instructor_id=$instructor_id AND status='completed'")->fetch_assoc()['c'];
$avgRating   = q($conn,"SELECT ROUND(AVG(rating),1) AS r FROM instructor_ratings WHERE instructor_id=$instructor_id")->fetch_assoc()['r'];

// Appointments split by package END date (the day logging is due) for the monitoring layout.
// end_date = start_date + (2 days for TDC/Both, 1 day for single-vehicle PDC)
// end_date always reflects the REAL package end date (for accurate display).
// Under TESTING_MODE, only the today/future/pending BUCKETING is relaxed so
// every active appointment is immediately loggable, without corrupting the
// dates shown to the instructor.
$endDateExpr = "DATE_ADD(a.start_date, INTERVAL (CASE WHEN ic.course_type LIKE 'TDC%' OR ic.course_type LIKE '%Both%' THEN 2 ELSE 1 END) DAY)";
$todayBucketCond = BaseDashboard::TESTING_MODE ? "1=1" : "$endDateExpr = '$today'";
$futureBucketCond = BaseDashboard::TESTING_MODE ? "1=0" : "$endDateExpr > '$today'";
$pendingBucketCond = BaseDashboard::TESTING_MODE ? "1=0" : "$endDateExpr < '$today'";

// TODAY: package end date = today (or ALL active, under testing mode), can log session / cancel
$todayAppts = q($conn,"SELECT a.*,ic.course_type,s.fullname AS sname,
    $endDateExpr AS end_date,
    (SELECT SUM(hours_covered) FROM sessions WHERE appointment_id=a.appointment_id AND status='completed') AS hours_done,
    (SELECT COALESCE(SUM(sess2.hours_covered),0) FROM sessions sess2
     JOIN appointments a3 ON a3.appointment_id=sess2.appointment_id
     JOIN instructor_courses ic3 ON ic3.course_id=a3.course_id
     WHERE sess2.student_id=a.student_id AND ic3.course_type LIKE 'TDC%' AND sess2.status='completed') AS tdc_prior_hours
    FROM appointments a
    JOIN instructor_courses ic ON ic.course_id=a.course_id
    JOIN students s ON s.student_id=a.student_id
    WHERE a.instructor_id=$instructor_id AND a.status IN('approved','in_progress')
      AND $todayBucketCond
    ORDER BY a.requested_at ASC");

// FUTURE/ONGOING: package end date > today, cannot log session yet (empty under testing mode)
$futureAppts = q($conn,"SELECT a.*,ic.course_type,s.fullname AS sname,
    $endDateExpr AS end_date,
    (SELECT SUM(hours_covered) FROM sessions WHERE appointment_id=a.appointment_id AND status='completed') AS hours_done
    FROM appointments a
    JOIN instructor_courses ic ON ic.course_id=a.course_id
    JOIN students s ON s.student_id=a.student_id
    WHERE a.instructor_id=$instructor_id AND a.status IN('approved','in_progress')
      AND $futureBucketCond
    ORDER BY a.start_date ASC");

// PENDING SESSION LOGS: package end date has passed with no session logged yet (empty under testing mode)
$pendingSessionLogs = q($conn,"SELECT a.*,ic.course_type,s.fullname AS sname,
    $endDateExpr AS end_date,
    (SELECT COALESCE(SUM(sess2.hours_covered),0) FROM sessions sess2
     JOIN appointments a3 ON a3.appointment_id=sess2.appointment_id
     JOIN instructor_courses ic3 ON ic3.course_id=a3.course_id
     WHERE sess2.student_id=a.student_id AND ic3.course_type LIKE 'TDC%' AND sess2.status='completed') AS tdc_prior_hours
    FROM appointments a
    JOIN instructor_courses ic ON ic.course_id=a.course_id
    JOIN students s ON s.student_id=a.student_id
    WHERE a.instructor_id=$instructor_id AND a.status IN('approved','in_progress')
      AND $pendingBucketCond
      AND NOT EXISTS (
          SELECT 1 FROM sessions sess
          WHERE sess.appointment_id=a.appointment_id AND sess.status='completed'
      )
    ORDER BY $endDateExpr ASC");

// Keep ongoingAppts as alias for futureAppts (used elsewhere in the view)
$ongoingAppts = $futureAppts;

// Recent appointments (completed/cancelled)
$recentAppts = q($conn,"SELECT a.*,ic.course_type,s.fullname AS sname,
    (SELECT SUM(hours_covered) FROM sessions WHERE appointment_id=a.appointment_id AND status='completed') AS hours_done
    FROM appointments a
    JOIN instructor_courses ic ON ic.course_id=a.course_id
    JOIN students s ON s.student_id=a.student_id
    WHERE a.instructor_id=$instructor_id AND a.status IN('completed','cancelled','rejected')
    ORDER BY a.start_date DESC,a.requested_at DESC LIMIT 100");

// Sessions grouped by appointment_id (for ongoing cards by day)
$sessionsByAppt = [];
$_oids = [];
$_oRes = q($conn,"SELECT appointment_id FROM appointments WHERE instructor_id=$instructor_id AND status IN('approved','in_progress')");
while($_r = $_oRes->fetch_assoc()) $_oids[] = (int)$_r['appointment_id'];
if (!empty($_oids)) {
    $_sRes = q($conn,"SELECT * FROM sessions WHERE appointment_id IN(".implode(',',$_oids).") ORDER BY session_date ASC,session_number ASC");
    while($_sr = $_sRes->fetch_assoc()) $sessionsByAppt[(int)$_sr['appointment_id']][] = $_sr;
}

$topStudents = q($conn,"SELECT s.student_id,s.fullname,ROUND(AVG(sess.grade),1) AS avg_grade,COUNT(sess.session_id) AS sess_cnt
    FROM sessions sess JOIN students s ON s.student_id=sess.student_id
    WHERE sess.instructor_id=$instructor_id AND sess.grade IS NOT NULL
      AND DATE_FORMAT(sess.session_date,'%Y-%m')=DATE_FORMAT(NOW(),'%Y-%m')
    GROUP BY s.student_id ORDER BY avg_grade DESC LIMIT 5");

// Chart data
$instrMonths=[]; $instrSessPerMonth=[];
for($m=5;$m>=0;$m--){
    $instrMonths[]=date('M Y',strtotime("-$m months"));
    $ym=date('Y-m',strtotime("-$m months"));
    $instrSessPerMonth[]=(float)q($conn,"SELECT COALESCE(SUM(hours_covered),0) AS h FROM sessions
        WHERE instructor_id=$instructor_id AND status='completed' AND DATE_FORMAT(session_date,'%Y-%m')='$ym'")->fetch_assoc()['h'];
}
$instrGradeBuckets=['90-100'=>0,'80-89'=>0,'70-79'=>0,'60-69'=>0,'<60'=>0];
$igRes=q($conn,"SELECT grade FROM sessions WHERE instructor_id=$instructor_id AND grade IS NOT NULL AND status='completed'");
while($ig=$igRes->fetch_assoc()){
    $g=(int)$ig['grade'];
    if($g>=90)$instrGradeBuckets['90-100']++;
    elseif($g>=80)$instrGradeBuckets['80-89']++;
    elseif($g>=70)$instrGradeBuckets['70-79']++;
    elseif($g>=60)$instrGradeBuckets['60-69']++;
    else $instrGradeBuckets['<60']++;
}
$instrTDCSess=(int)q($conn,"SELECT COUNT(*) AS c FROM sessions WHERE instructor_id=$instructor_id AND session_type='TDC' AND status='completed'")->fetch_assoc()['c'];
$instrPDCSess=(int)q($conn,"SELECT COUNT(*) AS c FROM sessions WHERE instructor_id=$instructor_id AND session_type='PDC' AND status='completed'")->fetch_assoc()['c'];

// Sidebar stats
$sbTotalStudents =(int)q($conn,"SELECT COUNT(DISTINCT student_id) AS c FROM appointments WHERE instructor_id=$instructor_id AND status IN('approved','in_progress','completed')")->fetch_assoc()['c'];
$sbTotalHrsTaught=(float)q($conn,"SELECT COALESCE(SUM(hours_covered),0) AS h FROM sessions WHERE instructor_id=$instructor_id AND status='completed'")->fetch_assoc()['h'];
$sbMonthHrs      =(float)q($conn,"SELECT COALESCE(SUM(hours_covered),0) AS h FROM sessions WHERE instructor_id=$instructor_id AND status='completed' AND DATE_FORMAT(session_date,'%Y-%m')=DATE_FORMAT(NOW(),'%Y-%m')")->fetch_assoc()['h'];
$sbAvgRatingSb   = $avgRating ?? 0;
$sbRatColor      = $sbAvgRatingSb >= 4 ? '#3cb97a' : ($sbAvgRatingSb >= 3 ? '#f59e0b' : '#e55');

// ── My Calendar: all of this instructor's appointments expanded across their
// full package date range, for the "My Calendar" section. ────────────────────
$calAppts = q($conn,"SELECT a.*,ic.course_type,s.fullname AS sname
    FROM appointments a
    JOIN instructor_courses ic ON ic.course_id=a.course_id
    JOIN students s ON s.student_id=a.student_id
    WHERE a.instructor_id=$instructor_id AND a.status IN('approved','in_progress','completed')
    ORDER BY a.start_date DESC");
$calMap = []; // date => [ {course_type, sname, status}, ... ]
while ($ca = $calAppts->fetch_assoc()) {
    $isTDC   = stripos($ca['course_type'], 'TDC') === 0;
    $isCombo = stripos($ca['course_type'], 'Both') !== false;
    $pkgDays = ($isTDC || $isCombo) ? 3 : 2;
    for ($d = 0; $d < $pkgDays; $d++) {
        $dt = date('Y-m-d', strtotime($ca['start_date'] . " +$d days"));
        $calMap[$dt][] = [
            'appointment_id' => (int)$ca['appointment_id'],
            'course_type'    => $ca['course_type'],
            'sname'          => $ca['sname'],
            'status'         => $ca['status'],
        ];
    }
}
