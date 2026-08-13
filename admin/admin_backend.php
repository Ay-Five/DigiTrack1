<?php
/**
 * admin/admin_backend.php
 *
 * OOP PILLARS DEMONSTRATED:
 *  - ENCAPSULATION  : Database class wraps mysqli
 *  - INHERITANCE    : AdminDashboard extends BaseDashboard
 *  - ABSTRACTION    : handlePost() / loadData() contracts
 *  - POLYMORPHISM   : same handlePost() call works for Admin, Instructor, Student
 *
 * IMPORTANT: $conn and q() are kept available so the view (admin_dashboard2.php)
 * works completely unchanged — it still calls q($conn,...) directly in the HTML.
 */

session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin_login.php');
    exit;
}
$admin_id = (int)$_SESSION['admin_id'];

// ── 1. Load raw connection (keeps $conn + q() alive for the view) ─────────────
require_once '../includes/db.php'; // gives us $conn, $host, $user, $password, $database

// ── 2. Load OOP layer ─────────────────────────────────────────────────────────
require_once '../includes/Database.php';
require_once '../includes/BaseDashboard.php';
require_once '../includes/Notifiable.php';
require_once '../includes/AdminDashboard.php';

// OOP PILLAR: ENCAPSULATION — mysqli hidden inside Database class
$db = new Database($host, $user, $password, $database);

// OOP PILLAR: INHERITANCE + ABSTRACTION — AdminDashboard extends BaseDashboard
$dashboard = new AdminDashboard($db, $admin_id);

// OOP PILLAR: POLYMORPHISM — same method name works for all 3 roles
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dashboard->handlePost($_POST['action'] ?? '');
}

// Load all data via OOP class
$dashboard->loadData();
$data = $dashboard->getData();

// ── 3. Keep helper functions the view uses ────────────────────────────────────
if (!function_exists('q')) {
    function q($c, $s) { return $c->query($s); }
}
if (!function_exists('esc')) {
    function esc($c, $v) { return $c->real_escape_string($v); }
}

// ── 4. Expose all variables the view expects ──────────────────────────────────
$today    = date('Y-m-d');
$selfFile = basename($_SERVER['PHP_SELF']);

$cColors = [
    'TDC Face-to-Face'        => 'tag-amber',
    'TDC Online'              => 'tag-blue',
    'PDC Car (Manual/Automatic)'       => 'tag-green',
    'PDC Motorcycle (Manual/Automatic)' => 'tag-teal',
    'PDC Both (Car & Motorcycle)'      => 'tag-purple',
    // legacy names kept for safety
    'PDC Car'                 => 'tag-green',
    'PDC Motorcycle'          => 'tag-coral',
];

$scmS = ['pending'=>'bkp','approved'=>'bksu','in_progress'=>'bkb',
          'completed'=>'bkg','rejected'=>'bkr','cancelled'=>'bkd'];
$scmL = ['pending'=>'Pending','approved'=>'Approved','in_progress'=>'In Progress',
          'completed'=>'Completed','rejected'=>'Rejected','cancelled'=>'Cancelled'];

$uploadDir = '../assets/uploads/certificates/';

$activeSection    = preg_replace('/[^a-z_]/', '', $_GET['section'] ?? 'dashboard');
$viewStudentId    = (int)($_GET['view_student']    ?? 0);
$viewInstructorId = (int)($_GET['view_instructor'] ?? 0);

// Flash
$flash    = $data['flash']    ?? null;
$flashErr = $data['flashErr'] ?? null;

// Stats (from OOP loadData)
$totalStudents    = $data['totalStudents']    ?? 0;
$totalInstructors = $data['totalInstructors'] ?? 0;
$pendingAppts     = $data['pendingAppts']     ?? 0;
$completedAppts   = $data['completedAppts']   ?? 0;
$avgRating        = $data['avgRating']        ?? 0;
$totalSessions    = $data['totalSessions']    ?? 0;
$totalHoursTaught = $data['totalHoursTaught'] ?? 0;
$todayLogins      = $data['todayLogins']      ?? 0;
$monthLogins      = $data['monthLogins']      ?? 0;
$newStudentsMonth = $data['newStudentsMonth'] ?? 0;

// Chart data
$chartMonths        = $data['chartMonths']        ?? [];
$chartNewStudents   = $data['chartNewStudents']   ?? [];
$chartApptCompleted = $data['chartApptCompleted'] ?? [];
$chartApptPending   = $data['chartApptPending']   ?? [];
$chartApptCancelled = $data['chartApptCancelled'] ?? [];
$tdcDoneCount       = $data['tdcDoneCount']       ?? 0;
$pdcDoneCount       = $data['pdcDoneCount']       ?? 0;
$tdcActiveCount     = $data['tdcActiveCount']     ?? 0;
$pdcActiveCount     = $data['pdcActiveCount']     ?? 0;
$gradeBuckets       = $data['gradeBuckets']       ?? [];
$maleCount          = $data['maleCount']          ?? 0;
$femaleCount        = $data['femaleCount']        ?? 0;
$otherGenderCount   = $data['otherGenderCount']   ?? 0;

// Sidebar stats
$sbActiveNow     = $data['sbActiveNow']     ?? 0;
$sbUnverified    = $data['sbUnverified']    ?? 0;
$sbSessionsToday = $data['sbSessionsToday'] ?? 0;
$sbAvgRatingSb   = $avgRating;
$sbRatColor      = $sbAvgRatingSb >= 4 ? '#3cb97a' : ($sbAvgRatingSb >= 3 ? '#f59e0b' : '#e55');

// ── 5. Rankings — returned as plain arrays from OOP, but the view calls
//       ->data_seek(0) and ->num_rows on them. We re-run these as real
//       mysqli queries so the view works with zero changes. ─────────────────────
$thisMonth       = date('Y-m');
$topInstructors  = q($conn, "SELECT i.instructor_id,i.fullname,ROUND(AVG(ir.rating),1) AS avg_r,COUNT(ir.rating_id) AS cnt,(SELECT COUNT(*) FROM appointments WHERE instructor_id=i.instructor_id AND status='completed') AS done FROM instructor_ratings ir JOIN instructors i ON i.instructor_id=ir.instructor_id GROUP BY i.instructor_id HAVING cnt>=1 AND avg_r>=3.5 ORDER BY avg_r DESC,cnt DESC LIMIT 5");
$lowInstructors  = q($conn, "SELECT i.instructor_id,i.fullname,ROUND(AVG(ir.rating),1) AS avg_r,COUNT(ir.rating_id) AS cnt FROM instructor_ratings ir JOIN instructors i ON i.instructor_id=ir.instructor_id GROUP BY i.instructor_id HAVING cnt>=1 AND avg_r<=3.5 ORDER BY avg_r ASC,cnt DESC LIMIT 5");
$topStudents     = q($conn, "SELECT s.student_id,s.fullname,ROUND(AVG(sess.grade),1) AS avg_grade,COUNT(sess.session_id) AS sess_cnt FROM sessions sess JOIN students s ON s.student_id=sess.student_id WHERE sess.grade IS NOT NULL AND DATE_FORMAT(sess.session_date,'%Y-%m')='$thisMonth' GROUP BY s.student_id HAVING avg_grade>=75 ORDER BY avg_grade DESC LIMIT 5");
$lowStudents     = q($conn, "SELECT s.student_id,s.fullname,ROUND(AVG(sess.grade),1) AS avg_grade,COUNT(sess.session_id) AS sess_cnt FROM sessions sess JOIN students s ON s.student_id=sess.student_id WHERE sess.grade IS NOT NULL AND DATE_FORMAT(sess.session_date,'%Y-%m')='$thisMonth' GROUP BY s.student_id HAVING avg_grade<75 ORDER BY avg_grade ASC LIMIT 5");

// Announcement dropdowns — also need to be mysqli result objects
$allStudentsList    = q($conn, "SELECT student_id,fullname FROM students WHERE is_verified=1 ORDER BY fullname");
$allInstructorsList = q($conn, "SELECT instructor_id,fullname FROM instructors ORDER BY fullname");
$stuRecipCount      = $data['stuRecipCount'] ?? 0;
$insRecipCount      = $data['insRecipCount'] ?? 0;

// Failed login map (used in the login history table)
$sFailMap = [];
$sfRes = q($conn, "SELECT student_id,COUNT(*) AS fc FROM student_login_history WHERE status='failed' GROUP BY student_id");
while ($sf = $sfRes->fetch_assoc()) $sFailMap[(int)$sf['student_id']] = (int)$sf['fc'];

// ── Override: appointments are now auto-approved (no more pending) ────────────
// Re-map pendingAppts to active (approved + in_progress) for the stat card
$pendingAppts = (int)q($conn,"SELECT COUNT(*) AS c FROM appointments WHERE status IN('approved','in_progress')")->fetch_assoc()['c'];

// Rebuild chartApptPending to reflect active (approved+in_progress) per month
$chartApptPending = [];
foreach ($chartMonths as $label) {
    $ym = date('Y-m', strtotime($label));
    $chartApptPending[] = (int)q($conn,"SELECT COUNT(*) AS c FROM appointments WHERE status IN('approved','in_progress') AND DATE_FORMAT(start_date,'%Y-%m')='$ym'")->fetch_assoc()['c'];
}

// ── Change Password / Admin Management / Student Management additions ────────
$currentAdmin = q($conn, "SELECT * FROM admins WHERE admin_id=$admin_id")->fetch_assoc();
$allAdmins    = q($conn, "SELECT * FROM admins ORDER BY fullname ASC");

// ── Calendar: ALL instructors' appointments, expanded across their full
// package date range, aggregated per date+instructor+course for the admin
// Calendar section (shows instructor, course, and student count per day). ────
$calAppts = q($conn,"SELECT a.start_date,a.status,ic.course_type,i.instructor_id,i.fullname AS iname,s.fullname AS sname
    FROM appointments a
    JOIN instructor_courses ic ON ic.course_id=a.course_id
    JOIN instructors i ON i.instructor_id=a.instructor_id
    JOIN students s ON s.student_id=a.student_id
    WHERE a.status IN('approved','in_progress','completed')
    ORDER BY a.start_date DESC");
$calMap = []; // date => [ {instructor_id, iname, course_type, students:[names], status}, ... ]
while ($ca = $calAppts->fetch_assoc()) {
    $isTDC   = stripos($ca['course_type'], 'TDC') === 0;
    $isCombo = stripos($ca['course_type'], 'Both') !== false;
    $pkgDays = ($isTDC || $isCombo) ? 3 : 2;
    for ($d = 0; $d < $pkgDays; $d++) {
        $dt  = date('Y-m-d', strtotime($ca['start_date'] . " +$d days"));
        $key = $ca['instructor_id'] . '|' . $ca['course_type'] . '|' . $ca['status'];
        if (!isset($calMap[$dt][$key])) {
            $calMap[$dt][$key] = [
                'instructor_id' => (int)$ca['instructor_id'],
                'iname'         => $ca['iname'],
                'course_type'   => $ca['course_type'],
                'status'        => $ca['status'],
                'students'      => [],
            ];
        }
        if (!in_array($ca['sname'], $calMap[$dt][$key]['students'])) {
            $calMap[$dt][$key]['students'][] = $ca['sname'];
        }
    }
}
// Re-index each date's entries as a plain array (drop the composite keys) for clean JSON
foreach ($calMap as $dt => $entries) { $calMap[$dt] = array_values($entries); }
