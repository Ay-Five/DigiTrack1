<?php
/**
 * student/student_backend.php
 *
 * OOP PILLARS DEMONSTRATED:
 *  - ENCAPSULATION  : Database class wraps mysqli
 *  - INHERITANCE    : StudentDashboard extends BaseDashboard
 *  - ABSTRACTION    : handlePost() / loadData() contracts
 *  - POLYMORPHISM   : same handlePost() call works for Admin, Instructor, Student
 *
 * $conn and q() are kept available so student_dashboard22.php works unchanged.
 */

session_start();
if (!isset($_SESSION['student_id'])) {
    header('Location: ../student_login.php');
    exit;
}
$student_id = (int)$_SESSION['student_id'];

// ── 1. Raw connection — keeps $conn + q() alive for the view ─────────────────
require_once '../includes/db.php'; // gives $conn, $host, $user, $password, $database

// ── 2. OOP layer ──────────────────────────────────────────────────────────────
require_once '../includes/Database.php';
require_once '../includes/BaseDashboard.php';
require_once '../includes/Notifiable.php';
require_once '../includes/StudentDashboard.php';

// OOP PILLAR: ENCAPSULATION
$db = new Database($host, $user, $password, $database);

// OOP PILLAR: INHERITANCE + ABSTRACTION
$dashboard = new StudentDashboard($db, $student_id);

// OOP PILLAR: POLYMORPHISM
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dashboard->handlePost($_POST['action'] ?? '');
}

$dashboard->loadData();
$data = $dashboard->getData();

// ── 3. Helper functions the view uses ────────────────────────────────────────
if (!function_exists('q'))   { function q($c,$s){return $c->query($s);} }
if (!function_exists('esc')) { function esc($c,$v){return $c->real_escape_string($v);} }
if (!function_exists('stars')){ function stars($n){$s='';for($i=1;$i<=5;$i++)$s.=$i<=$n?'★':'☆';return $s;} }
if (!function_exists('anonName')){
    /** GCash-style masking: per word, show first + last letter, mask the middle. */
    function anonName($name){
        $name = trim((string)$name);
        if ($name === '') return 'Student';
        $words = preg_split('/\s+/', $name);
        $masked = array_map(function($w){
            $len = mb_strlen($w);
            if ($len <= 2) return $w; // too short to mask meaningfully
            $first = mb_substr($w, 0, 1);
            $last  = mb_substr($w, -1);
            return $first . str_repeat('*', $len - 2) . $last;
        }, $words);
        return implode(' ', $masked);
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
    // legacy names kept for safety
    'PDC Car'                 => 'tag-green',
    'PDC Motorcycle'          => 'tag-coral',
];

$scmS = ['pending'=>'bkp','approved'=>'bksu','in_progress'=>'bkb',
          'completed'=>'bkg','rejected'=>'bkr','cancelled'=>'bkd','no_show'=>'bkr'];
$scmL = ['pending'=>'Pending','approved'=>'Booked','in_progress'=>'In Progress',
          'completed'=>'Completed','rejected'=>'Rejected','cancelled'=>'Cancelled','no_show'=>'No Show'];

$uploadDir = '../assets/uploads/certificates/';

$activeSection = preg_replace('/[^a-z_]/', '', $_GET['section'] ?? 'dashboard');
if (!empty($_GET['chat_instr']) && $activeSection === 'dashboard') $activeSection = 'chat';
$viewInstrId = (int)($_GET['view_instr'] ?? 0);

// Flash — loadData() already read + unset these from the session into $data,
// so read them from there (matching admin_backend.php's pattern) instead of
// hitting the session a second time, which would always find them already gone.
$flash    = $data['flash']    ?? null;
$flashErr = $data['flashErr'] ?? null;

// ── 5. Re-run queries the view uses as mysqli result objects ──────────────────
// (view calls ->fetch_assoc(), ->num_rows, ->data_seek() on these)

// TDC completion is CUMULATIVE — 15 total logged hours across all TDC sessions,
// not a single appointment reaching 'completed' status (a package can complete
// with fewer hours than required if the instructor logged less than 15).
$tdcHoursLoggedPre = (float)q($conn,"SELECT COALESCE(SUM(sess.hours_covered),0) AS h FROM sessions sess
    JOIN appointments a ON a.appointment_id=sess.appointment_id
    JOIN instructor_courses ic ON ic.course_id=a.course_id
    WHERE sess.student_id=$student_id AND ic.course_type LIKE 'TDC%' AND sess.status='completed'")->fetch_assoc()['h'];
$tdcDone = $tdcHoursLoggedPre >= 15 ? 1 : 0;

$activeInstructor = q($conn,"SELECT a.instructor_id,i.fullname,ic.course_type,a.status
    FROM appointments a
    JOIN instructors i ON i.instructor_id=a.instructor_id
    JOIN instructor_courses ic ON ic.course_id=a.course_id
    WHERE a.student_id=$student_id AND a.status IN('approved','in_progress')
    ORDER BY a.requested_at DESC LIMIT 1")->fetch_assoc();

$student = q($conn,"SELECT * FROM students WHERE student_id=$student_id")->fetch_assoc();

$unreadCnt = (int)q($conn,"SELECT COUNT(*) AS c FROM notifications
    WHERE user_id=$student_id AND user_type='student' AND is_read=0")->fetch_assoc()['c'];

$myInstructors = q($conn,"SELECT DISTINCT i.instructor_id,i.fullname,i.email,
    a.appointment_id,ic.course_type,a.status
    FROM appointments a
    JOIN instructors i ON i.instructor_id=a.instructor_id
    JOIN instructor_courses ic ON ic.course_id=a.course_id
    WHERE a.student_id=$student_id
      AND (
          a.status IN('pending','approved','in_progress','completed')
          OR EXISTS (
              SELECT 1 FROM messages m
              WHERE ((m.sender_id=$student_id AND m.sender_type='student' AND m.receiver_id=i.instructor_id AND m.receiver_type='instructor')
                  OR (m.receiver_id=$student_id AND m.receiver_type='student' AND m.sender_id=i.instructor_id AND m.sender_type='instructor'))
          )
      )
    ORDER BY i.fullname");

$unreadMsgMapS = [];
$umResS = q($conn,"SELECT sender_id,COUNT(*) AS cnt FROM messages
    WHERE receiver_id=$student_id AND receiver_type='student'
      AND is_read=0 AND sender_type='instructor' GROUP BY sender_id");
while($ums=$umResS->fetch_assoc()) $unreadMsgMapS[(int)$ums['sender_id']]=(int)$ums['cnt'];
$totalUnreadMsgS = array_sum($unreadMsgMapS);
$chatInstrId = (int)($_GET['chat_instr'] ?? 0);

$myAppts = q($conn,"SELECT a.*,ic.course_type,ic.price_per_hr,ic.description,ic.requirements,i.fullname AS instr_name,
    ir.rating AS my_rating,
    (SELECT SUM(hours_covered) FROM sessions WHERE appointment_id=a.appointment_id AND status='completed') AS hours_done
    FROM appointments a
    JOIN instructor_courses ic ON ic.course_id=a.course_id
    JOIN instructors i ON i.instructor_id=a.instructor_id
    LEFT JOIN instructor_ratings ir ON ir.appointment_id=a.appointment_id AND ir.student_id=$student_id
    WHERE a.student_id=$student_id ORDER BY a.requested_at DESC");

$allInstructors = q($conn,"SELECT i.instructor_id,i.fullname,i.email,i.certificate_image,
    (SELECT COUNT(*) FROM instructor_courses WHERE instructor_id=i.instructor_id AND is_active=1) AS course_count,
    (SELECT GROUP_CONCAT(DISTINCT course_type ORDER BY course_type SEPARATOR '|')
     FROM instructor_courses WHERE instructor_id=i.instructor_id AND is_active=1) AS courses,
    (SELECT ROUND(AVG(rating),1) FROM instructor_ratings WHERE instructor_id=i.instructor_id) AS avg_rating,
    (SELECT COUNT(*) FROM instructor_ratings WHERE instructor_id=i.instructor_id) AS rating_count
    FROM instructors i
    WHERE EXISTS(SELECT 1 FROM instructor_courses ic WHERE ic.instructor_id=i.instructor_id AND ic.is_active=1)
    ORDER BY avg_rating DESC, i.fullname ASC");

$viewInstr = null;
$instrAllCourses = null;
$viewInstrTotalStudents = 0;
$viewInstrTotalHrs      = 0;
$viewInstrTotalSess     = 0;
if ($viewInstrId > 0) {
    $viewInstr = q($conn,"SELECT i.instructor_id,i.fullname,i.email,i.certificate_image,i.bio,i.created_at,
        COALESCE(i.mobile_number,i.mobile) AS mobile,
        (SELECT ROUND(AVG(rating),1) FROM instructor_ratings WHERE instructor_id=i.instructor_id) AS avg_rating,
        (SELECT COUNT(*) FROM instructor_ratings WHERE instructor_id=i.instructor_id) AS rating_count
        FROM instructors i WHERE i.instructor_id=$viewInstrId")->fetch_assoc();
    $instrAllCourses = q($conn,"SELECT * FROM instructor_courses WHERE instructor_id=$viewInstrId AND is_active=1 ORDER BY course_type");

    // ── Build calendar data for this instructor: busy dates (any course) and
    // per-course slot counts (booked/limit), expanded across each package's
    // full date range so students can see exactly which days are taken. ──
    $instrBusyMap = [];   // date => [ {course_type, count}, ... ]  (for conflict warning)
    $instrSlotMap = [];   // course_id => date => booked count       (for slot-limit display)
    $_busyRes = q($conn,"SELECT a.start_date,a.total_hours,a.course_id,ic.course_type,COUNT(*) AS cnt
        FROM appointments a JOIN instructor_courses ic ON ic.course_id=a.course_id
        WHERE a.instructor_id=$viewInstrId AND a.status IN('approved','in_progress')
          AND a.start_date >= CURDATE()
        GROUP BY a.start_date,a.course_id,ic.course_type,a.total_hours");
    while ($br = $_busyRes->fetch_assoc()) {
        $isTDC   = stripos($br['course_type'], 'TDC') === 0;
        $isCombo = stripos($br['course_type'], 'Both') !== false;
        $pkgDays = ($isTDC || $isCombo) ? 3 : 2;
        for ($d = 0; $d < $pkgDays; $d++) {
            $dt = date('Y-m-d', strtotime($br['start_date'] . " +$d days"));
            $instrBusyMap[$dt][] = ['course_type' => $br['course_type'], 'count' => (int)$br['cnt']];
        }
        // Slot count only applies to the actual start date (package-level booking)
        $instrSlotMap[(int)$br['course_id']][$br['start_date']] = (int)$br['cnt'];
    }

    // Stats for the profile card (Students / Hours Taught / Sessions)
    $viewInstrTotalStudents = (int)q($conn,"SELECT COUNT(DISTINCT student_id) AS c FROM appointments
        WHERE instructor_id=$viewInstrId AND status IN('approved','in_progress','completed')")->fetch_assoc()['c'];
    $viewInstrTotalHrs = (float)q($conn,"SELECT COALESCE(SUM(hours_covered),0) AS h FROM sessions
        WHERE instructor_id=$viewInstrId AND status='completed'")->fetch_assoc()['h'];
    $viewInstrTotalSess = (int)q($conn,"SELECT COUNT(*) AS c FROM sessions
        WHERE instructor_id=$viewInstrId AND status='completed'")->fetch_assoc()['c'];
}

$notifs    = q($conn,"SELECT * FROM notifications WHERE user_id=$student_id AND user_type='student' ORDER BY created_at DESC LIMIT 30");
$mySessions= q($conn,"SELECT s.*,a.course_id,ic.course_type,i.fullname AS instr_name
    FROM sessions s
    JOIN appointments a ON a.appointment_id=s.appointment_id
    JOIN instructor_courses ic ON ic.course_id=a.course_id
    JOIN instructors i ON i.instructor_id=s.instructor_id
    WHERE s.student_id=$student_id ORDER BY s.session_date DESC");

// Progress page vars
$sbTDCHrsPre  = (float)q($conn,"SELECT COALESCE(SUM(s.hours_covered),0) AS h FROM sessions s
    JOIN appointments a ON a.appointment_id=s.appointment_id
    JOIN instructor_courses ic ON ic.course_id=a.course_id
    WHERE s.student_id=$student_id AND s.status='completed' AND ic.course_type LIKE 'TDC%'")->fetch_assoc()['h'];
$sbTotalHrsPre= (float)q($conn,"SELECT COALESCE(SUM(hours_covered),0) AS h FROM sessions
    WHERE student_id=$student_id AND status='completed'")->fetch_assoc()['h'];
$sbPDCHrsPre  = max(0, $sbTotalHrsPre - $sbTDCHrsPre);
$sbTDCProgressPre = min(100, round($sbTDCHrsPre / 15 * 100));
$sbSessionsPre= (int)q($conn,"SELECT COUNT(*) AS c FROM sessions WHERE student_id=$student_id AND status='completed'")->fetch_assoc()['c'];

// ── PDC Car (Manual+Automatic combined) and PDC Motorcycle (Manual+Automatic combined) ──
// PDC Car/Motorcycle courses credit their sessions fully. A Combo (PDC Both) package
// now stores the ACTUAL Car/Motorcycle split the instructor logged (pdc_car_hours /
// pdc_moto_hours), rather than assuming an even 8/8 divide.
$sbPDCCarHrs = (float)q($conn,"SELECT COALESCE(SUM(s.hours_covered),0) AS h FROM sessions s
    JOIN appointments a ON a.appointment_id=s.appointment_id
    JOIN instructor_courses ic ON ic.course_id=a.course_id
    WHERE s.student_id=$student_id AND s.status='completed' AND ic.course_type LIKE 'PDC Car%'")->fetch_assoc()['h'];
$sbPDCMotoHrs = (float)q($conn,"SELECT COALESCE(SUM(s.hours_covered),0) AS h FROM sessions s
    JOIN appointments a ON a.appointment_id=s.appointment_id
    JOIN instructor_courses ic ON ic.course_id=a.course_id
    WHERE s.student_id=$student_id AND s.status='completed' AND ic.course_type LIKE 'PDC Motorcycle%'")->fetch_assoc()['h'];
$sbPDCComboCarHrs = (float)q($conn,"SELECT COALESCE(SUM(s.pdc_car_hours),0) AS h FROM sessions s
    JOIN appointments a ON a.appointment_id=s.appointment_id
    JOIN instructor_courses ic ON ic.course_id=a.course_id
    WHERE s.student_id=$student_id AND s.status='completed' AND ic.course_type LIKE 'PDC Both%'")->fetch_assoc()['h'];
$sbPDCComboMotoHrs = (float)q($conn,"SELECT COALESCE(SUM(s.pdc_moto_hours),0) AS h FROM sessions s
    JOIN appointments a ON a.appointment_id=s.appointment_id
    JOIN instructor_courses ic ON ic.course_id=a.course_id
    WHERE s.student_id=$student_id AND s.status='completed' AND ic.course_type LIKE 'PDC Both%'")->fetch_assoc()['h'];
$sbPDCCarHrs  += $sbPDCComboCarHrs;
$sbPDCMotoHrs += $sbPDCComboMotoHrs;
$sbPDCCarProgress  = min(100, round($sbPDCCarHrs / 8 * 100));
$sbPDCMotoProgress = min(100, round($sbPDCMotoHrs / 8 * 100));

// Chart data
$stuMonths=[]; $stuTDCHrsMonth=[]; $stuPDCHrsMonth=[];
for($m=5;$m>=0;$m--){
    $stuMonths[]=date('M Y',strtotime("-$m months"));
    $ym=date('Y-m',strtotime("-$m months"));
    $stuTDCHrsMonth[]=(float)q($conn,"SELECT COALESCE(SUM(s.hours_covered),0) AS h FROM sessions s
        JOIN appointments a ON a.appointment_id=s.appointment_id
        JOIN instructor_courses ic ON ic.course_id=a.course_id
        WHERE s.student_id=$student_id AND s.status='completed'
          AND ic.course_type LIKE 'TDC%' AND DATE_FORMAT(s.session_date,'%Y-%m')='$ym'")->fetch_assoc()['h'];
    $stuPDCHrsMonth[]=(float)q($conn,"SELECT COALESCE(SUM(s.hours_covered),0) AS h FROM sessions s
        JOIN appointments a ON a.appointment_id=s.appointment_id
        JOIN instructor_courses ic ON ic.course_id=a.course_id
        WHERE s.student_id=$student_id AND s.status='completed'
          AND ic.course_type LIKE 'PDC%' AND DATE_FORMAT(s.session_date,'%Y-%m')='$ym'")->fetch_assoc()['h'];
}
$stuGradeTrend=[]; $stuGradeLabels=[];
$grRes=q($conn,"SELECT grade,session_date,session_number FROM sessions
    WHERE student_id=$student_id AND grade IS NOT NULL AND status='completed'
    ORDER BY session_date ASC,session_id ASC LIMIT 10");
while($gr=$grRes->fetch_assoc()){
    $stuGradeLabels[]='Day '.$gr['session_number'].' '.date('M d',strtotime($gr['session_date']));
    $stuGradeTrend[]=(int)$gr['grade'];
}

// ── Sidebar analytics variables (used by student_dashboard.php sidebar) ───────
$sbTotalHrs     = $sbTotalHrsPre;
$sbTDCHrs       = $sbTDCHrsPre;
$sbPDCHrs       = $sbPDCHrsPre;
$sbTDCProgress  = $sbTDCProgressPre;
$sbSessions     = $sbSessionsPre;
$sbThisMonthHrs = (float)q($conn,"SELECT COALESCE(SUM(hours_covered),0) AS h FROM sessions
    WHERE student_id=$student_id AND status='completed'
    AND DATE_FORMAT(session_date,'%Y-%m')=DATE_FORMAT(NOW(),'%Y-%m')")->fetch_assoc()['h'];
$sbAvgGradeSb   = q($conn,"SELECT ROUND(AVG(grade),1) AS ag FROM sessions
    WHERE student_id=$student_id AND grade IS NOT NULL")->fetch_assoc()['ag'] ?? 0;
$sbGradeColor   = $sbAvgGradeSb >= 80 ? '#3cb97a' : ($sbAvgGradeSb >= 60 ? '#f59e0b' : '#e55');
$sbCoursesDone  = (int)q($conn,"SELECT COUNT(DISTINCT course_id) AS c FROM appointments
    WHERE student_id=$student_id AND status='completed'")->fetch_assoc()['c'];

// Avg grade stat card (used in dashboard section)
$avgGrade = q($conn,"SELECT ROUND(AVG(grade),1) AS ag FROM sessions
    WHERE student_id=$student_id AND grade IS NOT NULL")->fetch_assoc()['ag'] ?? null;

// ── Ongoing & recent appointments for My Appointments section ────────────────
$todayAppts = q($conn,"SELECT a.*,ic.course_type,ic.price_per_hr,ic.description,ic.requirements,i.fullname AS instr_name,
    (SELECT SUM(hours_covered) FROM sessions WHERE appointment_id=a.appointment_id AND status='completed') AS hours_done
    FROM appointments a
    JOIN instructor_courses ic ON ic.course_id=a.course_id
    JOIN instructors i ON i.instructor_id=a.instructor_id
    WHERE a.student_id=$student_id AND a.status IN('approved','in_progress')
      AND a.start_date='$today'
    ORDER BY a.requested_at ASC");

$ongoingAppts = q($conn,"SELECT a.*,ic.course_type,ic.price_per_hr,ic.description,ic.requirements,i.fullname AS instr_name,
    (SELECT SUM(hours_covered) FROM sessions WHERE appointment_id=a.appointment_id AND status='completed') AS hours_done
    FROM appointments a
    JOIN instructor_courses ic ON ic.course_id=a.course_id
    JOIN instructors i ON i.instructor_id=a.instructor_id
    WHERE a.student_id=$student_id AND a.status IN('approved','in_progress')
      AND a.start_date > '$today'
      AND NOT (
          a.start_date < '$today'
          AND NOT EXISTS (SELECT 1 FROM sessions sess WHERE sess.appointment_id=a.appointment_id AND sess.session_date=a.start_date)
      )
    ORDER BY a.start_date ASC");

$recentAppts = q($conn,"SELECT a.*,ic.course_type,ic.price_per_hr,ic.description,ic.requirements,i.fullname AS instr_name,
    ir.rating AS my_rating,
    (SELECT SUM(hours_covered) FROM sessions WHERE appointment_id=a.appointment_id AND status='completed') AS hours_done
    FROM appointments a
    JOIN instructor_courses ic ON ic.course_id=a.course_id
    JOIN instructors i ON i.instructor_id=a.instructor_id
    LEFT JOIN instructor_ratings ir ON ir.appointment_id=a.appointment_id AND ir.student_id=$student_id
    WHERE a.student_id=$student_id AND a.status IN('completed','cancelled','rejected')
    ORDER BY a.requested_at DESC LIMIT 100");

// Sessions grouped by appointment_id (for ongoing cards)
$sessionsByApptS = [];
$_oidS = [];
$_oResS = q($conn,"SELECT appointment_id FROM appointments WHERE student_id=$student_id AND status IN('approved','in_progress')");
while($_r = $_oResS->fetch_assoc()) $_oidS[] = (int)$_r['appointment_id'];
if (!empty($_oidS)) {
    $_sResS = q($conn,"SELECT * FROM sessions WHERE appointment_id IN(".implode(',',$_oidS).") ORDER BY session_date ASC,session_number ASC");
    while($_sr = $_sResS->fetch_assoc()) $sessionsByApptS[(int)$_sr['appointment_id']][] = $_sr;
}

// ── Awaiting Instructor Confirmation ──────────────────────────────────────
// Appointments where the scheduled date has passed and the instructor has not
// yet logged a session (or marked No Show) for that specific date.
$awaitingConfirmation = q($conn,"SELECT a.*,ic.course_type,i.fullname AS instr_name
    FROM appointments a
    JOIN instructor_courses ic ON ic.course_id=a.course_id
    JOIN instructors i ON i.instructor_id=a.instructor_id
    WHERE a.student_id=$student_id AND a.status IN('approved','in_progress')
      AND a.start_date < '$today'
      AND NOT EXISTS (
          SELECT 1 FROM sessions sess
          WHERE sess.appointment_id=a.appointment_id
            AND sess.session_date=a.start_date
      )
    ORDER BY a.start_date ASC");

// Latest no_show session per appointment (for remarks display when applicable)
$noShowRemarksMap = [];
$_nsRes = q($conn,"SELECT sess.appointment_id,sess.performance_notes,sess.session_date FROM sessions sess
    JOIN appointments a ON a.appointment_id=sess.appointment_id
    WHERE a.student_id=$student_id AND sess.status='no_show'
    ORDER BY sess.session_date DESC");
while($_ns = $_nsRes->fetch_assoc()){
    $aid = (int)$_ns['appointment_id'];
    if (!isset($noShowRemarksMap[$aid])) $noShowRemarksMap[$aid] = $_ns;
}

// ── My Calendar: all of this student's appointments expanded across their full
// package date range, for the "My Calendar" section. ─────────────────────────
$calAppts = q($conn,"SELECT a.*,ic.course_type,i.fullname AS instr_name
    FROM appointments a
    JOIN instructor_courses ic ON ic.course_id=a.course_id
    JOIN instructors i ON i.instructor_id=a.instructor_id
    WHERE a.student_id=$student_id AND a.status IN('approved','in_progress','completed')
    ORDER BY a.start_date DESC");
$calMap = []; // date => [ {course_type, instr_name, status, appointment_id}, ... ]
while ($ca = $calAppts->fetch_assoc()) {
    $isTDC   = stripos($ca['course_type'], 'TDC') === 0;
    $isCombo = stripos($ca['course_type'], 'Both') !== false;
    $pkgDays = ($isTDC || $isCombo) ? 3 : 2;
    for ($d = 0; $d < $pkgDays; $d++) {
        $dt = date('Y-m-d', strtotime($ca['start_date'] . " +$d days"));
        $calMap[$dt][] = [
            'appointment_id' => (int)$ca['appointment_id'],
            'course_type'    => $ca['course_type'],
            'instr_name'     => $ca['instr_name'],
            'status'         => $ca['status'],
        ];
    }
}
