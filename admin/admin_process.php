<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;
require __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();
date_default_timezone_set('Asia/Manila');

$admin_id  = isset($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : 1;
$successMsg = ''; $errorMsg = '';

// ======== Helper FUnctions ========
function esc($conn, $v) { 
    return $conn->real_escape_string(trim($v)); 
}

function q($conn, $sql) { 
    return $conn->query($sql); 
}

function initials($name) {
    $w = array_filter(explode(' ', trim($name)));
    return strtoupper(substr(implode('', array_map(fn($x)=>$x[0], $w)), 0, 2));
}

$activeSection = $_GET['section'] ?? 'dashboard';
if (isset($_GET['view_student'])) $activeSection = 'student_profile';
if (isset($_GET['view_instructor'])) $activeSection = 'instructor_profile';

//========================= POST ACTIONS =====================================

// ======== Add Student ========
if (isset($_POST['action']) && $_POST['action'] === 'add_student') {
    $fn = esc($conn, $_POST['fullname'] ?? '');
    $em = esc($conn, $_POST['email'] ?? '');
    $pw = password_hash(trim($_POST['password'] ?? ''), PASSWORD_DEFAULT);
    if (!$fn || !$em) { $errorMsg = 'Name and email are required.'; }
    else {
        $ex = q($conn, "SELECT student_id FROM students WHERE email='$em'")->fetch_assoc();
        if ($ex) { $errorMsg = 'Email already registered.'; }
        else {
            q($conn, "INSERT INTO students(fullname,email,password_hash,is_verified) VALUES('$fn','$em','$pw',0)");
            $successMsg = 'Student added successfully.';
        }
    }
    $activeSection = 'students';
}

// ======== Edit Student ========
if (isset($_POST['action']) && $_POST['action'] === 'edit_student') {
    $sid = (int)($_POST['student_id'] ?? 0);
    $fn  = esc($conn, $_POST['fullname'] ?? '');
    $em  = esc($conn, $_POST['email'] ?? '');
    $ver = (int)($_POST['is_verified'] ?? 0);
    q($conn, "UPDATE students SET fullname='$fn',email='$em',is_verified=$ver WHERE student_id=$sid");
    $successMsg = 'Student updated.';
    $activeSection = 'students';
}

// ======== Delete Student ========
if (($_POST['action'] ?? '') === 'delete_student') {
    $sid = (int)($_POST['student_id'] ?? 0);

    if ($sid > 0) {
        q($conn, "DELETE FROM students WHERE student_id = $sid");
        $successMsg = 'Student deleted.';
        $activeSection = 'students';
    }
}

// ======== Add Instructor ========
if (isset($_POST['action']) && $_POST['action'] === 'add_instructor') {
    $fn_raw = trim($_POST['fullname'] ?? '');
    $em_raw = trim($_POST['email'] ?? '');

    $fn = esc($conn, $fn_raw);
    $em = esc($conn, $em_raw);

    if (!$fn_raw || !$em_raw) {
        $errorMsg = 'Name and email are required.';
    } elseif (!filter_var($em_raw, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = 'Invalid email address.';
    } else {
        $ex = q($conn, "SELECT instructor_id FROM instructors WHERE email='$em'")->fetch_assoc();

        if ($ex) {
            $errorMsg = 'Email already registered.';
        } else {
            $plainPassword = generatePassword(10);
            $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

            $sent = sendInstructorPassword($em_raw, $fn_raw, $plainPassword);

            if ($sent) {
                q($conn, "INSERT INTO instructors(fullname,email,password,password_sent)
                          VALUES('$fn','$em','$hashedPassword',1)");
                $successMsg = 'Instructor added successfully and password was sent to email.';
            } else {
                $errorMsg = 'Instructor was not added because the email failed to send.';
            }
        }
    }

    $activeSection = 'instructors';
}

function generatePassword($length = 10) {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$%';
    $password = '';
    $max = strlen($chars) - 1;

    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, $max)];
    }

    return $password;
}

function sendInstructorPassword($toEmail, $toName, $plainPassword) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['MAIL_USERNAME'];
        $mail->Password   = $_ENV['MAIL_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom($_ENV['MAIL_USERNAME'], 'MotoSafe');
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = 'Your Instructor Account Password';
        $mail->Body    = "
            <p>Hello <b>{$toName}</b>,</p>
            <p>Your instructor account has been created.</p>
            <p><b>Email:</b> {$toEmail}</p>
            <p><b>Password:</b> {$plainPassword}</p>
            <p>Please log in and change your password after your first login.</p>
        ";

        $mail->AltBody = "Hello {$toName},\n\n"
            . "Your instructor account has been created.\n"
            . "Email: {$toEmail}\n"
            . "Password: {$plainPassword}\n\n"
            . "Please log in and change your password after your first login.";

        return $mail->send();
    } catch (Exception $e) {
        return false;
    }
}


// ======== Edit Instructor ========
if (isset($_POST['action']) && $_POST['action'] === 'edit_instructor') {
    $iid = (int)($_POST['instructor_id'] ?? 0);
    $fn  = esc($conn, $_POST['fullname'] ?? '');
    $em  = esc($conn, $_POST['email'] ?? '');
    q($conn, "UPDATE instructors SET fullname='$fn',email='$em',modified_date=NOW() WHERE instructor_id=$iid");
    $successMsg = 'Instructor updated.';
    $activeSection = 'instructors';
}

// ======== Delete Instructor ========
if (($_POST['action'] ?? '') === 'delete_instructor') {
    $iid = (int)($_POST['instructor_id'] ?? 0);

    if ($iid > 0) {
        q($conn, "DELETE FROM instructors WHERE instructor_id = $iid");

        $successMsg = 'Instructor deleted.';
        $activeSection = 'instructors';
    }
}

//========================= ADMIN =====================================

// ======== Add Schedule ========
if (($_POST['action'] ?? '') === 'admin_add_schedule') {
    try {
        $cid   = (int)($_POST['course_id'] ?? 0);
        $day   = esc($conn, $_POST['day_of_week'] ?? '');
        $st    = esc($conn, $_POST['start_time'] ?? '');
        $et    = esc($conn, $_POST['end_time'] ?? '');
        $maxs  = (int)($_POST['max_students'] ?? 5);
        $iid   = (int)($_POST['instructor_id'] ?? 0);
        $notes = esc($conn, $_POST['notes'] ?? '');

        if (!$cid || !$day || !$st || !$et || !$iid) {
            throw new Exception('All fields required.');
        }

        if ($st >= $et) {
            throw new Exception('End time must be after start time.');
        }

        $conflict = q($conn, "
            SELECT schedule_id 
            FROM course_schedules 
            WHERE instructor_id = $iid 
            AND day_of_week = '$day' 
            AND is_active = 1 
            AND start_time < '$et' 
            AND end_time > '$st'
        ")->fetch_assoc();

        if ($conflict) {
            throw new Exception('Time conflict: instructor already has a schedule in that slot.');
        }

        q($conn, "
            INSERT INTO course_schedules
            (instructor_id, course_id, day_of_week, start_time, end_time, max_students, notes) 
            VALUES ($iid, $cid, '$day', '$st', '$et', $maxs, '$notes')
        ");

        $successMsg = 'Schedule created.';
    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
    }

    $activeSection = 'scheduling';
}

// ======== Delete Schedule ========
if (($_POST['action'] ?? '') === 'admin_delete_schedule') {
    try {
        $schid = (int)($_POST['schedule_id'] ?? 0);

        if ($schid <= 0) {
            throw new Exception('Invalid schedule.');
        }

        q($conn, "DELETE FROM course_schedules WHERE schedule_id = $schid");

        $successMsg = 'Schedule deleted.';
    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
    }

    $activeSection = 'scheduling';
}

// ======== Change Password ========
if (isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $cur  = trim($_POST['current_password'] ?? '');
    $new  = trim($_POST['new_password'] ?? '');
    $conf = trim($_POST['confirm_password'] ?? '');
    $adm2 = q($conn, "SELECT password FROM admins WHERE admin_id=$admin_id")->fetch_assoc();
    if (!password_verify($cur, $adm2['password'])) { $errorMsg = 'Current password is incorrect.'; }
    elseif (strlen($new) < 6) { $errorMsg = 'New password must be at least 6 characters.'; }
    elseif ($new !== $conf) { $errorMsg = 'Passwords do not match.'; }
    else {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        q($conn, "UPDATE admins SET password='$hash' WHERE admin_id=$admin_id");
        $successMsg = 'Password updated successfully.';
    }
    $activeSection = 'settings';
}

// ======== Record Payment ========
if (isset($_POST['action']) && $_POST['action'] === 'record_payment') {
    $rid    = (int)($_POST['request_id'] ?? 0);
    $amount = (float)($_POST['amount'] ?? 0);
    $method = esc($conn, $_POST['method'] ?? 'Cash');
    $notes  = esc($conn, $_POST['notes'] ?? '');
    if (!$rid || $amount <= 0) { $errorMsg = 'Invalid payment details.'; }
    else {
        // Store in instructor_note field as a payment record (no separate payments table assumed)
        $now = date('Y-m-d H:i:s');
        q($conn, "INSERT INTO payments(request_id,amount,method,notes,paid_at) VALUES($rid,$amount,'$method','$notes','$now')");
        $successMsg = 'Payment recorded: ₱' . number_format($amount, 2);
    }
    $activeSection = 'payments';
}

// ======== Post Accouncement ========
if (isset($_POST['action']) && $_POST['action'] === 'post_announcement') {
    $msg    = esc($conn, $_POST['message'] ?? '');
    $target = $_POST['target'] ?? 'all';
    if (!$msg) { $errorMsg = 'Message cannot be empty.'; }
    else {
        $sent = 0;
        if ($target === 'students' || $target === 'all') {
            $res = q($conn, "SELECT student_id FROM students");
            while ($r = $res->fetch_assoc()) {
                q($conn, "INSERT INTO notifications(user_id,user_type,message,type,is_read) VALUES({$r['student_id']},'student','📢 Admin Announcement: ".addslashes($msg)."','announcement',0)");
                $sent++;
            }
        }
        if ($target === 'instructors' || $target === 'all') {
            $res = q($conn, "SELECT instructor_id FROM instructors");
            while ($r = $res->fetch_assoc()) {
                q($conn, "INSERT INTO notifications(user_id,user_type,message,type,is_read) VALUES({$r['instructor_id']},'instructor','📢 Admin Announcement: ".addslashes($msg)."','announcement',0)");
                $sent++;
            }
        }
        $successMsg = "Announcement sent to $sent user".($sent!=1?'s':'').".";
    }
    $activeSection = 'dashboard';
}

//========================= DATE QUERIES =====================================

// Admin info
$adminRow  = q($conn, "SELECT * FROM admins WHERE admin_id=$admin_id")->fetch_assoc();
$adminName = htmlspecialchars($adminRow['fullname'] ?? 'Admin');
$adminEmail= htmlspecialchars($adminRow['email'] ?? '');
$adminIn   = initials($adminRow['fullname'] ?? 'Admin');
$firstName = explode(' ', trim($adminName))[0];

// Overview stats
$totalStudents    = (int)q($conn, "SELECT COUNT(*) AS c FROM students")->fetch_assoc()['c'];
$verifiedStudents = (int)q($conn, "SELECT COUNT(*) AS c FROM students WHERE is_verified=1")->fetch_assoc()['c'];
$totalInstructors = (int)q($conn, "SELECT COUNT(*) AS c FROM instructors")->fetch_assoc()['c'];
$activeScheds     = (int)q($conn, "SELECT COUNT(*) AS c FROM course_schedules WHERE is_active=1")->fetch_assoc()['c'];
$pendingEnrols    = (int)q($conn, "SELECT COUNT(*) AS c FROM enrolment_requests WHERE status='pending'")->fetch_assoc()['c'];
$doneSessions     = (int)q($conn, "SELECT COUNT(*) AS c FROM enrolment_requests WHERE status='done'")->fetch_assoc()['c'];
$totalEnrols      = (int)q($conn, "SELECT COUNT(*) AS c FROM enrolment_requests")->fetch_assoc()['c'];

// Chart: daily logins last 7 days
$chartLabels = []; $chartCounts = [];
for ($i=6;$i>=0;$i--) {
    $cnt = (int)q($conn, "SELECT COUNT(*) AS c FROM student_login_history WHERE status='success' AND DATE(login_datetime)=DATE(DATE_SUB(NOW(),INTERVAL $i DAY))")->fetch_assoc()['c'];
    $chartLabels[] = date('D', strtotime("-$i days"));
    $chartCounts[] = $cnt;
}
$jLabels = json_encode($chartLabels); $jCounts = json_encode($chartCounts);

// Enrolment by status for doughnut
$enrByStatus = [];
foreach (['pending','approved','done','rejected','cancelled'] as $st2) {
    $enrByStatus[$st2] = (int)q($conn, "SELECT COUNT(*) AS c FROM enrolment_requests WHERE status='$st2'")->fetch_assoc()['c'];
}

// Recent activity
$recentLogins = q($conn, "SELECT s.fullname,slh.login_datetime,slh.status FROM student_login_history slh JOIN students s ON s.student_id=slh.student_id ORDER BY slh.login_datetime DESC LIMIT 6");

// Students list
$studentsList = q($conn, "SELECT st.*,(SELECT COUNT(*) FROM enrolment_requests WHERE student_id=st.student_id AND status='done') AS done_cnt,(SELECT COUNT(*) FROM enrolment_requests WHERE student_id=st.student_id AND status='approved') AS app_cnt FROM students st ORDER BY st.created_at DESC");

// Instructors list
$instructorsList = q($conn, "SELECT i.*,(SELECT ROUND(AVG(rating),1) FROM session_ratings WHERE instructor_id=i.instructor_id) AS avg_rating,(SELECT COUNT(*) FROM session_ratings WHERE instructor_id=i.instructor_id) AS total_ratings,(SELECT COUNT(*) FROM course_schedules WHERE instructor_id=i.instructor_id AND is_active=1) AS active_scheds,(SELECT COUNT(*) FROM enrolment_requests er JOIN course_schedules cs ON cs.schedule_id=er.schedule_id WHERE cs.instructor_id=i.instructor_id AND er.status='done') AS done_sess FROM instructors i ORDER BY i.created_at DESC");

// All courses for schedule form
$allCourses = q($conn, "SELECT ic.*,i.fullname AS iname FROM instructor_courses ic JOIN instructors i ON i.instructor_id=ic.instructor_id WHERE ic.is_active=1 ORDER BY i.fullname,ic.course_type");

// All instructors for dropdown
$instrDropdown = q($conn, "SELECT instructor_id,fullname FROM instructors ORDER BY fullname ASC");

// Schedules list
$schedulesList = q($conn, "SELECT cs.*,ic.course_type,ic.price,i.fullname AS iname,(SELECT COUNT(*) FROM enrolment_requests WHERE schedule_id=cs.schedule_id AND status='approved') AS app_cnt,(SELECT COUNT(*) FROM enrolment_requests WHERE schedule_id=cs.schedule_id AND status='pending') AS pend_cnt,(SELECT COUNT(*) FROM enrolment_requests WHERE schedule_id=cs.schedule_id AND status='done') AS done_cnt FROM course_schedules cs JOIN instructor_courses ic ON ic.course_id=cs.course_id JOIN instructors i ON i.instructor_id=cs.instructor_id ORDER BY FIELD(cs.day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'),cs.start_time ASC");

// Payments — check if table exists
$paymentsExist = q($conn, "SHOW TABLES LIKE 'payments'")->num_rows > 0;
$paymentsList  = $paymentsExist ? q($conn, "SELECT p.*,er.status AS er_status,st.fullname AS sname,ic.course_type,ic.price AS course_price FROM payments p JOIN enrolment_requests er ON er.request_id=p.request_id JOIN course_schedules cs ON cs.schedule_id=er.schedule_id JOIN instructor_courses ic ON ic.course_id=cs.course_id JOIN students st ON st.student_id=er.student_id ORDER BY p.paid_at DESC LIMIT 50") : null;
$totalRevenue  = $paymentsExist ? (float)(q($conn, "SELECT SUM(amount) AS t FROM payments")->fetch_assoc()['t'] ?? 0) : 0;
$monthRevenue  = $paymentsExist ? (float)(q($conn, "SELECT SUM(amount) AS t FROM payments WHERE MONTH(paid_at)=MONTH(NOW()) AND YEAR(paid_at)=YEAR(NOW())")->fetch_assoc()['t'] ?? 0) : 0;

// Unpaid enrolments (approved but no payment)
$unpaidList = $paymentsExist ? q($conn, "SELECT er.*,st.fullname AS sname,ic.course_type,ic.price,i.fullname AS iname FROM enrolment_requests er JOIN course_schedules cs ON cs.schedule_id=er.schedule_id JOIN instructor_courses ic ON ic.course_id=cs.course_id JOIN students st ON st.student_id=er.student_id JOIN instructors i ON i.instructor_id=cs.instructor_id WHERE er.status IN('approved','done') AND NOT EXISTS(SELECT 1 FROM payments WHERE request_id=er.request_id) ORDER BY er.reviewed_at DESC") : null;


// Rating leaderboards
$topRatedStudents = q($conn, "SELECT st.student_id, st.fullname, ROUND(AVG(sr.student_rating),1) AS avg_student_rating, COUNT(sr.rating_id) AS total_student_reviews, (SELECT ic.course_type FROM enrolment_requests er2 JOIN course_schedules cs2 ON cs2.schedule_id=er2.schedule_id JOIN instructor_courses ic ON ic.course_id=cs2.course_id WHERE er2.student_id=st.student_id ORDER BY er2.requested_at DESC LIMIT 1) AS latest_course FROM session_ratings sr JOIN students st ON st.student_id=sr.student_id WHERE sr.student_rating IS NOT NULL GROUP BY st.student_id HAVING total_student_reviews >= 1 ORDER BY avg_student_rating DESC, total_student_reviews DESC, st.fullname ASC LIMIT 5");
$lowRatedStudents = q($conn, "SELECT st.student_id, st.fullname, ROUND(AVG(sr.student_rating),1) AS avg_student_rating, COUNT(sr.rating_id) AS total_student_reviews, SUM(CASE WHEN sr.student_rating <= 2 THEN 1 ELSE 0 END) AS needs_improvement_count, (SELECT ic.course_type FROM enrolment_requests er2 JOIN course_schedules cs2 ON cs2.schedule_id=er2.schedule_id JOIN instructor_courses ic ON ic.course_id=cs2.course_id WHERE er2.student_id=st.student_id ORDER BY er2.requested_at DESC LIMIT 1) AS latest_course FROM session_ratings sr JOIN students st ON st.student_id=sr.student_id WHERE sr.student_rating IS NOT NULL GROUP BY st.student_id HAVING total_student_reviews >= 1 ORDER BY avg_student_rating ASC, needs_improvement_count DESC, total_student_reviews DESC, st.fullname ASC LIMIT 5");
$topRatedInstructors = q($conn, "SELECT i.instructor_id, i.fullname, ROUND(AVG(sr.rating),1) AS avg_rating, COUNT(sr.rating_id) AS total_ratings, (SELECT ic.course_type FROM instructor_courses ic WHERE ic.instructor_id=i.instructor_id AND ic.is_active=1 ORDER BY ic.course_id ASC LIMIT 1) AS main_course FROM session_ratings sr JOIN instructors i ON i.instructor_id=sr.instructor_id WHERE sr.rating IS NOT NULL GROUP BY i.instructor_id HAVING total_ratings >= 1 ORDER BY avg_rating DESC, total_ratings DESC, i.fullname ASC LIMIT 5");
$lowRatedInstructors = q($conn, "SELECT i.instructor_id, i.fullname, ROUND(AVG(sr.rating),1) AS avg_rating, COUNT(sr.rating_id) AS total_ratings, (SELECT ic.course_type FROM instructor_courses ic WHERE ic.instructor_id=i.instructor_id AND ic.is_active=1 ORDER BY ic.course_id ASC LIMIT 1) AS main_course FROM session_ratings sr JOIN instructors i ON i.instructor_id=sr.instructor_id WHERE sr.rating IS NOT NULL GROUP BY i.instructor_id HAVING total_ratings >= 1 ORDER BY avg_rating ASC, total_ratings DESC, i.fullname ASC LIMIT 5");

// Report data
$topInstructors = q($conn, "SELECT i.fullname,ROUND(AVG(sr.rating),1) AS avg_r,COUNT(sr.rating_id) AS reviews,COUNT(DISTINCT er.request_id) AS sessions FROM instructors i LEFT JOIN session_ratings sr ON sr.instructor_id=i.instructor_id LEFT JOIN enrolment_requests er ON er.student_id=sr.student_id AND er.status='done' GROUP BY i.instructor_id ORDER BY avg_r DESC LIMIT 10");
$courseStats    = q($conn, "SELECT ic.course_type,COUNT(er.request_id) AS total,SUM(er.status='done') AS done,SUM(er.status='approved') AS approved,SUM(er.status='pending') AS pending FROM instructor_courses ic LEFT JOIN course_schedules cs ON cs.course_id=ic.course_id LEFT JOIN enrolment_requests er ON er.schedule_id=cs.schedule_id GROUP BY ic.course_type ORDER BY total DESC");

// Admin accounts
$adminsList = q($conn, "SELECT * FROM admins ORDER BY created_at DESC");

// Login history (admin)
$adminLoginHistory = q($conn, "SELECT * FROM admins WHERE admin_id=$admin_id")->fetch_assoc();

// Detailed profile views
$viewStudentId = isset($_GET['view_student']) ? (int)$_GET['view_student'] : 0;
$viewStudentProfile = null;
$viewStudentSessions = null;
$viewStudentLogins = null;
if ($viewStudentId > 0) {
    $viewStudentProfile = q($conn, "SELECT st.*,
        (SELECT COUNT(*) FROM enrolment_requests WHERE student_id=st.student_id) AS total_requests,
        (SELECT COUNT(*) FROM enrolment_requests WHERE student_id=st.student_id AND status='approved') AS approved_cnt,
        (SELECT COUNT(*) FROM enrolment_requests WHERE student_id=st.student_id AND status='pending') AS pending_cnt,
        (SELECT COUNT(*) FROM enrolment_requests WHERE student_id=st.student_id AND status='done') AS done_cnt,
        (SELECT ROUND(AVG(sr.student_rating),1) FROM session_ratings sr WHERE sr.student_id=st.student_id AND sr.student_rating IS NOT NULL) AS avg_student_rating,
        (SELECT COUNT(*) FROM session_ratings sr WHERE sr.student_id=st.student_id AND sr.student_rating IS NOT NULL) AS total_student_reviews,
        (SELECT COUNT(*) FROM session_ratings sr WHERE sr.student_id=st.student_id AND sr.student_rating <= 2) AS needs_improvement_count
        FROM students st WHERE st.student_id=$viewStudentId LIMIT 1")->fetch_assoc();

    $viewStudentSessions = q($conn, "SELECT er.request_id, er.status, er.requested_at, er.reviewed_at, er.done_at, er.student_note, er.instructor_note,
        cs.slot_date, cs.day_of_week, cs.slot_hrs, cs.slot_price,
        ic.course_type, ic.price, ic.duration_hrs,
        i.fullname AS instructor_name, i.instructor_id,
        sr.student_rating, sr.student_feedback, sr.rating AS instructor_rating, sr.description AS instructor_review
        FROM enrolment_requests er
        JOIN course_schedules cs ON cs.schedule_id=er.schedule_id
        JOIN instructor_courses ic ON ic.course_id=cs.course_id
        JOIN instructors i ON i.instructor_id=cs.instructor_id
        LEFT JOIN session_ratings sr ON sr.request_id=er.request_id
        WHERE er.student_id=$viewStudentId
        ORDER BY COALESCE(cs.slot_date,'9999-12-31') DESC, er.requested_at DESC LIMIT 25");

    $viewStudentLogins = q($conn, "SELECT * FROM student_login_history WHERE student_id=$viewStudentId ORDER BY login_datetime DESC LIMIT 10");
}

$viewInstructorId = isset($_GET['view_instructor']) ? (int)$_GET['view_instructor'] : 0;
$viewInstructorProfile = null;
$viewInstructorCourses = null;
$viewInstructorSchedules = null;
$viewInstructorReviews = null;
$viewInstructorStudents = null;
if ($viewInstructorId > 0) {
    $viewInstructorProfile = q($conn, "SELECT i.*,
        (SELECT ROUND(AVG(sr.rating),1) FROM session_ratings sr WHERE sr.instructor_id=i.instructor_id AND sr.rating IS NOT NULL AND sr.rating > 0) AS avg_rating,
        (SELECT COUNT(*) FROM session_ratings sr WHERE sr.instructor_id=i.instructor_id AND sr.rating IS NOT NULL AND sr.rating > 0) AS total_ratings,
        (SELECT COUNT(*) FROM course_schedules cs WHERE cs.instructor_id=i.instructor_id AND cs.is_active=1) AS active_scheds,
        (SELECT COUNT(*) FROM enrolment_requests er JOIN course_schedules cs ON cs.schedule_id=er.schedule_id WHERE cs.instructor_id=i.instructor_id AND er.status='done') AS done_sess,
        (SELECT COUNT(DISTINCT er.student_id) FROM enrolment_requests er JOIN course_schedules cs ON cs.schedule_id=er.schedule_id WHERE cs.instructor_id=i.instructor_id AND er.status IN('approved','done')) AS total_students
        FROM instructors i WHERE i.instructor_id=$viewInstructorId LIMIT 1")->fetch_assoc();

    $viewInstructorCourses = q($conn, "SELECT ic.*,
        (SELECT COUNT(*) FROM course_schedules cs WHERE cs.course_id=ic.course_id AND cs.instructor_id=ic.instructor_id) AS total_slots,
        (SELECT COUNT(*) FROM enrolment_requests er JOIN course_schedules cs ON cs.schedule_id=er.schedule_id WHERE cs.course_id=ic.course_id AND cs.instructor_id=ic.instructor_id AND er.status='done') AS done_sessions
        FROM instructor_courses ic WHERE ic.instructor_id=$viewInstructorId ORDER BY ic.created_at DESC");

    $viewInstructorSchedules = q($conn, "SELECT cs.*, ic.course_type, ic.duration_hrs, ic.price,
        (SELECT COUNT(*) FROM enrolment_requests WHERE schedule_id=cs.schedule_id AND status='pending') AS pending_cnt,
        (SELECT COUNT(*) FROM enrolment_requests WHERE schedule_id=cs.schedule_id AND status='approved') AS approved_cnt,
        (SELECT COUNT(*) FROM enrolment_requests WHERE schedule_id=cs.schedule_id AND status='done') AS done_cnt
        FROM course_schedules cs
        JOIN instructor_courses ic ON ic.course_id=cs.course_id
        WHERE cs.instructor_id=$viewInstructorId
        ORDER BY COALESCE(cs.slot_date,'9999-12-31') DESC, cs.schedule_id DESC LIMIT 20");

    $viewInstructorReviews = q($conn, "SELECT sr.*, st.fullname AS student_name, ic.course_type, cs.slot_date
        FROM session_ratings sr
        JOIN students st ON st.student_id=sr.student_id
        JOIN enrolment_requests er ON er.request_id=sr.request_id
        JOIN course_schedules cs ON cs.schedule_id=er.schedule_id
        JOIN instructor_courses ic ON ic.course_id=cs.course_id
        WHERE sr.instructor_id=$viewInstructorId AND sr.rating IS NOT NULL AND sr.rating > 0
        ORDER BY sr.created_at DESC LIMIT 12");

    $viewInstructorStudents = q($conn, "SELECT st.student_id, st.fullname, st.email, COUNT(er.request_id) AS total_sessions,
        SUM(er.status='done') AS done_cnt,
        ROUND(AVG(sr.student_rating),1) AS avg_student_rating
        FROM students st
        JOIN enrolment_requests er ON er.student_id=st.student_id
        JOIN course_schedules cs ON cs.schedule_id=er.schedule_id
        LEFT JOIN session_ratings sr ON sr.request_id=er.request_id
        WHERE cs.instructor_id=$viewInstructorId
        GROUP BY st.student_id
        ORDER BY done_cnt DESC, total_sessions DESC, st.fullname ASC LIMIT 15");
}

$days      = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
$cColors   = ['TDC Face-to-Face'=>'tag-blue','TDC Online'=>'tag-green','PDC Car'=>'tag-amber','PDC Car Manual'=>'tag-amber','PDC Car Automatic'=>'tag-amber','PDC Motorcycle'=>'tag-purple','PDC Motorcycle Manual'=>'tag-purple','PDC Motorcycle Automatic'=>'tag-purple'];
$dayColors = ['Monday'=>'#3d7fff','Tuesday'=>'#10b981','Wednesday'=>'#f59e0b','Thursday'=>'#c084fc','Friday'=>'#f43f5e','Saturday'=>'#06b6d4'];
$accentColors = ['#3d7fff','#10b981','#f59e0b','#c084fc','#f43f5e','#06b6d4'];
?>