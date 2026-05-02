<?php

require '../includes/db.php';
require 'student_auth.php';

$student_id = isset($_SESSION['student_id']) ? (int)$_SESSION['student_id'] : 1;
$successMsg = ''; $errorMsg = '';
$selfFile = htmlspecialchars(basename($_SERVER['PHP_SELF'] ?? 'student_dashboard21.php'));

function schedDateLabel(array $row): string {
    if (!empty($row['slot_date'])) return date('M d, Y', strtotime($row['slot_date']));
    return $row['day_of_week'] ?? '—';
}
function schedHoursLabel(array $row): string {
    if (!empty($row['slot_hrs'])) return rtrim(rtrim(number_format((float)$row['slot_hrs'], 1, '.', ''), '0'), '.') . ' hrs';
    if (!empty($row['duration_hrs'])) return rtrim(rtrim(number_format((float)$row['duration_hrs'], 1, '.', ''), '0'), '.') . ' hrs';
    if (!empty($row['course_type'])) return strpos($row['course_type'], 'TDC') === 0 ? '15 hrs' : '8 hrs';
    return '—';
}
function schedPriceLabel(array $row): string {
    $price = $row['slot_price'] ?? $row['price'] ?? null;
    return ($price !== null && $price !== '' && (float)$price > 0) ? '₱' . number_format((float)$price, 0) : 'Free';
}
function schedSlotsLeft(array $row): int {
    $max = (int)($row['max_students'] ?? 0);
    $enrolled = (int)($row['enrolled_cnt'] ?? 0);
    return max(0, $max - $enrolled);
}


// ── ENROL REQUEST ─────────────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'enrol') {
    $schid = (int)($_POST['schedule_id'] ?? 0);
    $note  = $conn->real_escape_string(trim($_POST['student_note'] ?? ''));
    if (!$schid) { $errorMsg = 'Invalid schedule.'; }
    else {
        $sch = $conn->query("SELECT cs.*,ic.course_type,ic.price,ic.duration_hrs,i.fullname AS iname,(SELECT COUNT(*) FROM enrolment_requests WHERE schedule_id=cs.schedule_id AND status NOT IN('rejected','cancelled')) AS enrolled_cnt FROM course_schedules cs JOIN instructor_courses ic ON ic.course_id=cs.course_id JOIN instructors i ON i.instructor_id=cs.instructor_id WHERE cs.schedule_id=$schid AND cs.is_active=1")->fetch_assoc();
        if (!$sch) { $errorMsg = 'This schedule is no longer available.'; }
        else {
            $already = $conn->query("SELECT request_id FROM enrolment_requests WHERE schedule_id=$schid AND student_id=$student_id AND status NOT IN('rejected','cancelled')")->fetch_assoc();
            $maxStudents = (int)($sch['max_students'] ?? 0);
            $enrolledCnt = (int)($sch['enrolled_cnt'] ?? 0);
            if ($already) { $errorMsg = 'You already have an active request for this schedule.'; }
            elseif ($maxStudents > 0 && $enrolledCnt >= $maxStudents) { $errorMsg = 'This instructor course is already booked or full.'; }
            else {
                $conn->query("INSERT INTO enrolment_requests(schedule_id,student_id,student_note)VALUES($schid,$student_id,'$note')");
                $successMsg = 'Enrolment request sent! Waiting for instructor approval.';
            }
        }
    }
}

// ── REQUEST OFFERED COURSE WITH DATE ─────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'request_offered_course') {
    $instrId      = (int)($_POST['instructor_id'] ?? 0);
    $courseId     = (int)($_POST['course_id'] ?? 0);
    $requestedDateRaw = trim($_POST['requested_date'] ?? '');
    $note         = $conn->real_escape_string(trim($_POST['student_note'] ?? ''));

    if (!$instrId || !$courseId || !$requestedDateRaw) {
        $errorMsg = 'Please choose a course and date.';
    } else {
        $requestedTs = strtotime($requestedDateRaw);
        $requestedDate = $requestedTs ? date('Y-m-d', $requestedTs) : '';
        $todayDate = date('Y-m-d');
        $dayNum = $requestedTs ? (int)date('N', $requestedTs) : 0;

        if (!$requestedDate || $requestedDate < $todayDate) {
            $errorMsg = 'Please select a valid future date.';
        } elseif ($dayNum === 7) {
            $errorMsg = 'Sunday is not available. Please choose Monday to Saturday.';
        } else {
            $course = $conn->query("SELECT ic.*,i.fullname AS iname FROM instructor_courses ic JOIN instructors i ON i.instructor_id=ic.instructor_id WHERE ic.course_id=$courseId AND ic.instructor_id=$instrId AND ic.is_active=1")->fetch_assoc();
            if (!$course) {
                $errorMsg = 'This offered course is no longer available.';
            } else {
                $dup = $conn->query("SELECT er.request_id FROM enrolment_requests er JOIN course_schedules cs ON cs.schedule_id=er.schedule_id WHERE er.student_id=$student_id AND cs.instructor_id=$instrId AND cs.course_id=$courseId AND cs.slot_date='$requestedDate' AND er.status NOT IN('rejected','cancelled') LIMIT 1")->fetch_assoc();
                if ($dup) {
                    $errorMsg = 'You already have an active request for this course on that date.';
                } else {
                    $schedule = $conn->query("SELECT cs.*, (SELECT COUNT(*) FROM enrolment_requests WHERE schedule_id=cs.schedule_id AND status NOT IN('rejected','cancelled')) AS enrolled_cnt FROM course_schedules cs WHERE cs.instructor_id=$instrId AND cs.course_id=$courseId AND cs.slot_date='$requestedDate' ORDER BY cs.is_active DESC, cs.schedule_id ASC LIMIT 1")->fetch_assoc();
                    $scheduleId = 0;

                    if ($schedule) {
                        $maxStudents = (int)($schedule['max_students'] ?? 0);
                        $enrolledCnt = (int)($schedule['enrolled_cnt'] ?? 0);
                        if ($enrolledCnt > 0 || ($maxStudents > 0 && $enrolledCnt >= $maxStudents)) {
                            $errorMsg = 'This instructor course date is already booked or full. Please choose another date.';
                        } else {
                            $scheduleId = (int)$schedule['schedule_id'];
                        }
                    } else {
                        $dayName = $conn->real_escape_string(date('l', $requestedTs));
                        $slotHrs = !empty($course['duration_hrs']) ? (float)$course['duration_hrs'] : ((strpos((string)($course['course_type'] ?? ''), 'TDC') === 0) ? 15 : 8);
                        $slotPriceSql = ($course['price'] !== null && $course['price'] !== '') ? (string)(float)$course['price'] : 'NULL';
                        $scheduleNotes = $conn->real_escape_string('Requested by student for approval');
                        $conn->query("INSERT INTO course_schedules(instructor_id,course_id,day_of_week,slot_date,slot_hrs,slot_price,max_students,notes,is_active) VALUES($instrId,$courseId,'$dayName','$requestedDate',$slotHrs,$slotPriceSql,1,'$scheduleNotes',0)");
                        $scheduleId = (int)$conn->insert_id;
                    }

                    if (!$errorMsg && $scheduleId > 0) {
                        $conn->query("INSERT INTO enrolment_requests(schedule_id,student_id,student_note)VALUES($scheduleId,$student_id,'$note')");
                        $reqDateLabel = date('M d, Y', strtotime($requestedDate));
                        $msg = $conn->real_escape_string("New booking request for {$course['course_type']} on {$reqDateLabel} from a student.");
                        $conn->query("INSERT INTO notifications(user_id,user_type,message,type,is_read) VALUES($instrId,'instructor','$msg','booking_request',0)");
                        $successMsg = 'Course request sent! Waiting for instructor approval.';
                    }
                }
            }
        }
    }
}

// ── CANCEL REQUEST ────────────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'cancel_enrol') {
    $rid = (int)($_POST['request_id'] ?? 0);
    $conn->query("UPDATE enrolment_requests SET status='cancelled' WHERE request_id=$rid AND student_id=$student_id AND status='pending'");
    $successMsg = 'Request cancelled.';
}

// ── SUBMIT RATING ─────────────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'submit_rating') {
    $rid    = (int)($_POST['request_id'] ?? 0);
    $rating = (int)($_POST['rating']     ?? 0);
    $desc   = $conn->real_escape_string(trim($_POST['description'] ?? ''));
    // Map 4-option choice to 1-4 star value (Bad=1,Poor=2,Good=3,Excellent=4→stored as 2/3/4/5 to keep 5-star scale)
    $ratingLabels = [1=>'Bad',2=>'Poor',3=>'Good',4=>'Excellent'];
    $ratingMap    = [1=>1,2=>2,3=>3,4=>5]; // Bad→1★ Poor→2★ Good→3★ Excellent→5★
    if ($rating < 1 || $rating > 4) { $errorMsg = 'Please select a rating option.'; }
    elseif (!$rid) { $errorMsg = 'Invalid session.'; }
    else {
        $bk = $conn->query("SELECT er.student_id,cs.instructor_id,ic.course_type FROM enrolment_requests er JOIN course_schedules cs ON cs.schedule_id=er.schedule_id JOIN instructor_courses ic ON ic.course_id=cs.course_id WHERE er.request_id=$rid AND er.student_id=$student_id AND er.status='done'")->fetch_assoc();
        if (!$bk) { $errorMsg = 'Session not found or not completed yet.'; }
        else {
            $ex = $conn->query("SELECT rating_id FROM session_ratings WHERE request_id=$rid")->fetch_assoc();
            if ($ex) { $errorMsg = 'You already rated this session.'; }
            else {
                $starVal    = $ratingMap[$rating];
                $labelVal   = $conn->real_escape_string($ratingLabels[$rating]);
                $courseType = $conn->real_escape_string($bk['course_type']);
                // Store label as prefix in description if description is empty
                $fullDesc = $desc ? $conn->real_escape_string($labelVal.': '.$desc) : $conn->real_escape_string($labelVal);
                $conn->query("INSERT INTO session_ratings(request_id,student_id,instructor_id,rating,description,course_type)VALUES($rid,$student_id,{$bk['instructor_id']},$starVal,'$fullDesc','$courseType')");
                $successMsg = 'Rating submitted! Thank you.';
            }
        }
    }
}

// ── MARK NOTIFICATIONS READ ────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'mark_read_student') {
    $conn->query("UPDATE notifications SET is_read=1 WHERE user_id=$student_id AND user_type='student'");
}

// ── STUDENT DATA ──────────────────────────────────────────────────
$sr         = $conn->query("SELECT * FROM students WHERE student_id=$student_id")->fetch_assoc();
$fullname   = htmlspecialchars($sr['fullname']  ?? 'Student');
$email      = htmlspecialchars($sr['email']     ?? '');
$isVerified = (bool)($sr['is_verified']         ?? 0);
$joinDate   = isset($sr['created_at']) ? date('F d, Y', strtotime($sr['created_at'])) : '—';
$firstName  = explode(' ', trim($fullname))[0];
$sW         = array_filter(explode(' ', trim($fullname)));
$initials   = strtoupper(substr(implode('', array_map(fn($w) => $w[0], $sW)), 0, 2));

// ── STATS ─────────────────────────────────────────────────────────
$totalReqs   = $conn->query("SELECT COUNT(*) AS c FROM enrolment_requests WHERE student_id=$student_id")->fetch_assoc()['c'];
$approvedCnt = $conn->query("SELECT COUNT(*) AS c FROM enrolment_requests WHERE student_id=$student_id AND status='approved'")->fetch_assoc()['c'];
$doneCnt     = $conn->query("SELECT COUNT(*) AS c FROM enrolment_requests WHERE student_id=$student_id AND status='done'")->fetch_assoc()['c'];
$pendingCnt  = $conn->query("SELECT COUNT(*) AS c FROM enrolment_requests WHERE student_id=$student_id AND status='pending'")->fetch_assoc()['c'];
$unratedCnt  = $conn->query("SELECT COUNT(*) AS c FROM enrolment_requests er WHERE er.student_id=$student_id AND er.status='done' AND NOT EXISTS(SELECT 1 FROM session_ratings WHERE request_id=er.request_id)")->fetch_assoc()['c'];

// ── MY ENROLMENTS ─────────────────────────────────────────────────
$myEnrols = $conn->query("SELECT er.*,cs.day_of_week,cs.slot_date,cs.slot_hrs,cs.slot_price,cs.max_students,ic.course_type,ic.price,ic.duration_hrs,i.fullname AS iname,i.instructor_id,(SELECT rating FROM session_ratings WHERE request_id=er.request_id LIMIT 1) AS my_rating FROM enrolment_requests er JOIN course_schedules cs ON cs.schedule_id=er.schedule_id JOIN instructor_courses ic ON ic.course_id=cs.course_id JOIN instructors i ON i.instructor_id=cs.instructor_id WHERE er.student_id=$student_id ORDER BY FIELD(er.status,'approved','pending','done','rejected','cancelled'),COALESCE(cs.slot_date,'9999-12-31') ASC,er.requested_at DESC");

// ── ALL INSTRUCTORS WITH ACTIVE SCHEDULES ─────────────────────────
$allInstructors = $conn->query("SELECT DISTINCT i.instructor_id,i.fullname,i.email,i.certificate_image,(SELECT COUNT(*) FROM course_schedules WHERE instructor_id=i.instructor_id AND is_active=1) AS open_scheds,(SELECT COUNT(*) FROM course_schedules cs2 WHERE cs2.instructor_id=i.instructor_id AND cs2.is_active=1 AND (SELECT COUNT(*) FROM enrolment_requests er2 WHERE er2.schedule_id=cs2.schedule_id AND er2.status NOT IN('rejected','cancelled')) < cs2.max_students) AS available_scheds,(SELECT COUNT(*) FROM course_schedules cs3 WHERE cs3.instructor_id=i.instructor_id AND cs3.is_active=1 AND (SELECT COUNT(*) FROM enrolment_requests er3 WHERE er3.schedule_id=cs3.schedule_id AND er3.status NOT IN('rejected','cancelled')) > 0) AS booked_scheds,(SELECT COUNT(*) FROM instructor_courses ic3 WHERE ic3.instructor_id=i.instructor_id AND ic3.is_active=1) AS offered_courses_count,(SELECT GROUP_CONCAT(DISTINCT ic2.course_type ORDER BY ic2.course_type SEPARATOR '|') FROM instructor_courses ic2 WHERE ic2.instructor_id=i.instructor_id AND ic2.is_active=1) AS courses,(SELECT ROUND(AVG(rating),1) FROM session_ratings WHERE instructor_id=i.instructor_id) AS avg_rating,(SELECT COUNT(*) FROM session_ratings WHERE instructor_id=i.instructor_id) AS rating_count FROM instructors i WHERE EXISTS(SELECT 1 FROM instructor_courses ic WHERE ic.instructor_id=i.instructor_id AND ic.is_active=1) ORDER BY available_scheds DESC, open_scheds DESC, offered_courses_count DESC");

// ── VIEW SPECIFIC INSTRUCTOR ──────────────────────────────────────
$viewInstrId = isset($_GET['view_instr']) ? (int)$_GET['view_instr'] : 0;
$viewInstr   = null; $viewScheds = null; $viewCourses = null;
if ($viewInstrId) {
    $viewInstr  = $conn->query("SELECT i.*,(SELECT ROUND(AVG(rating),1) FROM session_ratings WHERE instructor_id=i.instructor_id) AS avg_rating,(SELECT COUNT(*) FROM session_ratings WHERE instructor_id=i.instructor_id) AS rating_count,(SELECT COUNT(*) FROM instructor_courses ic WHERE ic.instructor_id=i.instructor_id AND ic.is_active=1) AS offered_courses_count,(SELECT GROUP_CONCAT(DISTINCT ic.course_type ORDER BY ic.course_type SEPARATOR '|') FROM instructor_courses ic WHERE ic.instructor_id=i.instructor_id AND ic.is_active=1) AS offered_courses FROM instructors i WHERE instructor_id=$viewInstrId")->fetch_assoc();
    $viewScheds = $conn->query("SELECT cs.*,ic.course_type,ic.price,ic.duration_hrs,ic.description AS course_desc,(SELECT COUNT(*) FROM enrolment_requests WHERE schedule_id=cs.schedule_id AND status NOT IN('rejected','cancelled')) AS enrolled_cnt,(SELECT request_id FROM enrolment_requests WHERE schedule_id=cs.schedule_id AND student_id=$student_id AND status NOT IN('rejected','cancelled') LIMIT 1) AS my_rid,(SELECT status FROM enrolment_requests WHERE schedule_id=cs.schedule_id AND student_id=$student_id AND status NOT IN('rejected','cancelled') LIMIT 1) AS my_status,(SELECT reviewed_at FROM enrolment_requests WHERE schedule_id=cs.schedule_id AND student_id=$student_id AND status='approved' LIMIT 1) AS reviewed_at FROM course_schedules cs JOIN instructor_courses ic ON ic.course_id=cs.course_id WHERE cs.instructor_id=$viewInstrId AND cs.is_active=1 ORDER BY COALESCE(cs.slot_date,'9999-12-31') ASC, cs.schedule_id ASC");
    $viewCourses = $conn->query("SELECT ic.*, (SELECT COUNT(*) FROM course_schedules cs WHERE cs.instructor_id=ic.instructor_id AND cs.course_id=ic.course_id AND cs.is_active=1) AS open_scheds, (SELECT COUNT(*) FROM enrolment_requests er JOIN course_schedules cs2 ON cs2.schedule_id=er.schedule_id WHERE cs2.instructor_id=ic.instructor_id AND cs2.course_id=ic.course_id AND er.status NOT IN('rejected','cancelled')) AS booked_cnt FROM instructor_courses ic WHERE ic.instructor_id=$viewInstrId AND ic.is_active=1 ORDER BY ic.course_type ASC");
}

// ── AVAILABLE SCHEDULES / OPEN SLOTS ───────────────────────────────
$availableSchedules = $conn->query("SELECT cs.*,ic.course_type,ic.price,ic.duration_hrs,ic.description AS course_desc,i.fullname AS iname,i.instructor_id,(SELECT COUNT(*) FROM enrolment_requests WHERE schedule_id=cs.schedule_id AND status NOT IN('rejected','cancelled')) AS enrolled_cnt,(SELECT request_id FROM enrolment_requests WHERE schedule_id=cs.schedule_id AND student_id=$student_id AND status NOT IN('rejected','cancelled') LIMIT 1) AS my_rid,(SELECT status FROM enrolment_requests WHERE schedule_id=cs.schedule_id AND student_id=$student_id AND status NOT IN('rejected','cancelled') LIMIT 1) AS my_status FROM course_schedules cs JOIN instructor_courses ic ON ic.course_id=cs.course_id JOIN instructors i ON i.instructor_id=cs.instructor_id WHERE cs.is_active=1 AND (SELECT COUNT(*) FROM enrolment_requests WHERE schedule_id=cs.schedule_id AND status NOT IN('rejected','cancelled')) < cs.max_students ORDER BY COALESCE(cs.slot_date,'9999-12-31') ASC, i.fullname ASC");

// ── NOTIFICATIONS ─────────────────────────────────────────────────
$myNotifs  = $conn->query("SELECT * FROM notifications WHERE user_id=$student_id AND user_type='student' ORDER BY created_at DESC LIMIT 30");
$unreadN   = $conn->query("SELECT COUNT(*) AS c FROM notifications WHERE user_id=$student_id AND user_type='student' AND is_read=0")->fetch_assoc()['c'];

// ── UPCOMING SESSIONS (approved, ordered by day) ──────────────────
$todayDay       = date('l');
$dayOrderSQL    = "FIELD(cs.day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday')";
$upcomingScheds = $conn->query("SELECT er.*,cs.day_of_week,cs.slot_date,cs.slot_hrs,cs.slot_price,cs.max_students,ic.course_type,ic.price,i.fullname AS iname,i.instructor_id FROM enrolment_requests er JOIN course_schedules cs ON cs.schedule_id=er.schedule_id JOIN instructor_courses ic ON ic.course_id=cs.course_id JOIN instructors i ON i.instructor_id=cs.instructor_id WHERE er.student_id=$student_id AND er.status='approved' ORDER BY COALESCE(cs.slot_date,'9999-12-31') ASC, er.reviewed_at DESC");

// ── COMPLETED SESSIONS WITH INSTRUCTOR FEEDBACK ───────────────────
$completedEnrols = $conn->query("SELECT er.*,cs.day_of_week,cs.slot_date,cs.slot_hrs,cs.slot_price,cs.max_students,ic.course_type,ic.duration_hrs,i.fullname AS iname,i.instructor_id,(SELECT rating FROM session_ratings WHERE request_id=er.request_id LIMIT 1) AS my_rating,(SELECT description FROM session_ratings WHERE request_id=er.request_id LIMIT 1) AS my_review FROM enrolment_requests er JOIN course_schedules cs ON cs.schedule_id=er.schedule_id JOIN instructor_courses ic ON ic.course_id=cs.course_id JOIN instructors i ON i.instructor_id=cs.instructor_id WHERE er.student_id=$student_id AND er.status='done' ORDER BY er.done_at DESC");

// ── INSTRUCTORS I'VE WORKED WITH + THEIR NOTES ────────────────────
$myInstructors = $conn->query("SELECT DISTINCT i.instructor_id,i.fullname,(SELECT COUNT(*) FROM enrolment_requests er2 JOIN course_schedules cs2 ON cs2.schedule_id=er2.schedule_id WHERE cs2.instructor_id=i.instructor_id AND er2.student_id=$student_id AND er2.status='done') AS done_with,(SELECT er3.instructor_note FROM enrolment_requests er3 JOIN course_schedules cs3 ON cs3.schedule_id=er3.schedule_id WHERE cs3.instructor_id=i.instructor_id AND er3.student_id=$student_id AND er3.instructor_note IS NOT NULL AND er3.instructor_note!='' ORDER BY er3.done_at DESC LIMIT 1) AS latest_note FROM instructors i WHERE EXISTS(SELECT 1 FROM enrolment_requests er JOIN course_schedules cs ON cs.schedule_id=er.schedule_id WHERE cs.instructor_id=i.instructor_id AND er.student_id=$student_id AND er.status IN('approved','done')) ORDER BY i.fullname ASC");

// ── LOGIN HISTORY ─────────────────────────────────────────────────
$loginHistory = $conn->query("SELECT * FROM student_login_history WHERE student_id=$student_id ORDER BY login_datetime DESC LIMIT 8");
$totalLogins  = $conn->query("SELECT COUNT(*) AS c FROM student_login_history WHERE student_id=$student_id AND status='success'")->fetch_assoc()['c'];
$failedLogins = $conn->query("SELECT COUNT(*) AS c FROM student_login_history WHERE student_id=$student_id AND status='failed'")->fetch_assoc()['c'];
$lastLogin    = $conn->query("SELECT login_datetime FROM student_login_history WHERE student_id=$student_id AND status='success' ORDER BY login_datetime DESC LIMIT 1")->fetch_assoc();

// ── ACTIVE SECTION ────────────────────────────────────────────────
$activeSection = 'dashboard';
if ($viewInstrId) $activeSection = 'view_instr';
elseif (isset($_GET['section'])) $activeSection = $_GET['section'];
$p2s = ['enrol'=>'available_schedules','request_offered_course'=>'view_instr','cancel_enrol'=>'schedule','submit_rating'=>'progress','mark_read_student'=>'messages'];
if (isset($_POST['action']) && isset($p2s[$_POST['action']])) $activeSection = $p2s[$_POST['action']];

$today     = date('Y-m-d'); $dayName = date('l'); $monthYear = date('F Y');
$days      = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
$cColors   = ['TDC Face-to-Face'=>'tag-blue','TDC Online'=>'tag-green','PDC Car Manual'=>'tag-amber','PDC Car Automatic'=>'tag-amber','PDC Motorcycle Manual'=>'tag-purple','PDC Motorcycle Automatic'=>'tag-purple'];
$dayColors = ['Monday'=>'#3d7fff','Tuesday'=>'#10b981','Wednesday'=>'#f59e0b','Thursday'=>'#c084fc','Friday'=>'#f43f5e','Saturday'=>'#06b6d4'];
$uploadDir = '../assets/uploads/certificates/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>MotoSafe – Student</title>
<link href="../assets/img/logo.png" rel="icon">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
/* ── RESET & VARIABLES ─────────────────────────────────────────── */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --accent:#3d7fff;--green:#10b981;--amber:#f59e0b;--red:#f43f5e;--purple:#c084fc;
  --bg:#0a0f1e;--surface:#0e1525;--surface-2:#111b2e;--surface-3:#1a2540;
  --border:#1e2d47;--border-bright:#2a3f60;
  --text:#e8edf5;--muted:#7a8ba8;--muted-2:#4a5a78;
  --font-body:'DM Sans',sans-serif;--font-display:'Syne',sans-serif;
  --r:14px;--r-lg:18px;--sidebar-w:220px;
}
body{background:var(--bg);color:var(--text);font-family:var(--font-body);font-size:14px;line-height:1.5;min-height:100vh}
a{color:inherit;text-decoration:none}
/* ── LAYOUT ─────────────────────────────────────────────────────── */
.app-wrapper{display:flex;min-height:100vh}
.sidebar{position:fixed;top:0;left:0;width:var(--sidebar-w);height:100vh;background:var(--surface);border-right:1px solid var(--border);display:flex;flex-direction:column;overflow-y:auto;overflow-x:hidden;z-index:100;padding:0 0 16px;transition:transform .25s}
.sidebar-push{margin-left:var(--sidebar-w);flex:1;min-width:0;display:flex;flex-direction:column}
.logo{display:flex;align-items:center;gap:10px;padding:20px 16px 16px;font-family:var(--font-display);font-size:19px;font-weight:800;border-bottom:1px solid var(--border);flex-shrink:0}
.logo span{color:var(--amber)}
.logo-icon img{width:28px;height:28px;border-radius:7px;object-fit:contain}
.nav-label{font-size:10px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--muted-2);padding:16px 16px 6px}
.nav-item{display:flex;align-items:center;gap:10px;padding:9px 16px;border-radius:10px;font-size:13px;font-weight:500;color:var(--muted);cursor:pointer;margin:1px 8px;transition:background .15s,color .15s;text-decoration:none}
.nav-item i{width:16px;text-align:center;flex-shrink:0}
.nav-item:hover{background:var(--surface-2);color:var(--text)}
.nav-item.active{background:rgba(61,127,255,.12);color:var(--accent)}
.sidebar-bottom{margin-top:auto;padding:12px 8px 0;border-top:1px solid var(--border)}
.user-row{display:flex;align-items:center;gap:10px;padding:10px 10px 8px}
.user-name{font-size:13px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.user-role{font-size:11px;color:var(--muted)}
.avatar{width:36px;height:36px;border-radius:9px;background:linear-gradient(135deg,var(--accent),#1a4fb0);display:grid;place-items:center;font-family:var(--font-display);font-size:13px;font-weight:800;flex-shrink:0}
/* ── TOPBAR ─────────────────────────────────────────────────────── */
.topbar{display:flex;align-items:center;justify-content:space-between;padding:14px 24px;background:var(--surface);border-bottom:1px solid var(--border);position:sticky;top:0;z-index:50}
.page-title{font-family:var(--font-display);font-size:16px;font-weight:800}
.search-box{display:flex;align-items:center;gap:8px;background:var(--surface-2);border:1px solid var(--border);border-radius:10px;padding:8px 14px;width:220px}
.search-box input{background:none;border:none;outline:none;color:var(--text);font-family:var(--font-body);font-size:13px;width:100%}
.search-box i{color:var(--muted-2);font-size:12px}
.icon-btn{width:36px;height:36px;border-radius:9px;background:var(--surface-2);border:1px solid var(--border);display:grid;place-items:center;cursor:pointer;color:var(--muted)}
/* ── CONTENT ─────────────────────────────────────────────────────── */
.content{padding:24px;flex:1}
/* ── PANELS ─────────────────────────────────────────────────────── */
.panel{background:var(--surface-2);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;margin-bottom:20px}
.panel:last-child{margin-bottom:0}
.panel-head{display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:1px solid var(--border)}
.panel-title{font-family:var(--font-display);font-size:14px;font-weight:700}
.panel-sub{font-size:12px;color:var(--muted);margin-top:2px}
.panel-body{padding:20px}
/* ── HERO STRIP ─────────────────────────────────────────────────── */
.hero-strip{background:linear-gradient(135deg,#0d1e3a,#091630);border:1px solid rgba(61,127,255,.15);border-radius:var(--r);padding:24px;margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap;position:relative;overflow:hidden}
.hero-strip::before{content:'';position:absolute;right:-40px;top:-40px;width:200px;height:200px;background:radial-gradient(circle,rgba(61,127,255,.12),transparent 70%);border-radius:50%}
.hero-text .greeting{font-size:12px;color:var(--muted);margin-bottom:4px;text-transform:uppercase;letter-spacing:.06em}
.hero-text h2{font-family:var(--font-display);font-size:22px;font-weight:800;margin-bottom:8px}
.hero-text p{font-size:13px;color:var(--muted);margin-bottom:16px}
.hero-quick{display:flex;gap:20px;flex-wrap:wrap}
.hero-quick-item{text-align:center}
.hero-quick-item strong{display:block;font-family:var(--font-display);font-size:22px;font-weight:800;color:var(--accent)}
.hero-quick-item span{font-size:11px;color:var(--muted)}
.hero-session{background:rgba(255,255,255,.04);border:1px solid var(--border-bright);border-radius:12px;padding:14px 16px;display:flex;align-items:center;gap:12px;min-width:180px}
.session-dot{width:10px;height:10px;border-radius:50%;background:var(--green);flex-shrink:0;box-shadow:0 0 8px var(--green)}
.session-info .label{font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:.06em}
.session-info .title{font-family:var(--font-display);font-size:15px;font-weight:800}
.session-info .meta{font-size:12px;color:var(--muted)}
/* ── STAT CARDS ─────────────────────────────────────────────────── */
.stat-card{background:var(--surface-2);border:1px solid var(--border);border-radius:var(--r);padding:20px;height:100%;transition:border-color .2s}
.stat-card:hover{border-color:var(--border-bright)}
.stat-top{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:12px}
.stat-label{font-size:12px;color:var(--muted);font-weight:500}
.stat-icon{font-size:18px;color:var(--muted-2)}
.stat-value{font-family:var(--font-display);font-size:32px;font-weight:800;line-height:1;margin-bottom:6px}
.stat-sub{font-size:12px;color:var(--muted);margin:0}
.warn{color:var(--amber)}
/* ── TABLES ─────────────────────────────────────────────────────── */
.tbl-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch}
.tbl{width:100%;border-collapse:collapse;font-size:13px}
.tbl th{padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:var(--muted-2);text-transform:uppercase;letter-spacing:.06em;border-bottom:1px solid var(--border);white-space:nowrap;background:var(--surface-3)}
.tbl td{padding:12px 16px;border-bottom:1px solid var(--border);vertical-align:middle}
.tbl tbody tr:last-child td{border-bottom:none}
.tbl tbody tr:hover{background:rgba(255,255,255,.02)}
/* ── TABS ────────────────────────────────────────────────────────── */
.tab{background:none;border:none;color:var(--muted);font-family:var(--font-body);font-size:13px;font-weight:500;padding:8px 14px;cursor:pointer;border-bottom:2px solid transparent;transition:color .15s,border-color .15s}
.tab:hover{color:var(--text)}
.tab.active{color:var(--accent);border-bottom-color:var(--accent)}
/* ── TAGS / BADGES ──────────────────────────────────────────────── */
.tag{display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:600;border:1px solid transparent}
.tag-blue{background:rgba(61,127,255,.15);color:#7daeff;border-color:rgba(61,127,255,.25)}
.tag-green{background:rgba(16,185,129,.15);color:#5dddb0;border-color:rgba(16,185,129,.25)}
.tag-amber{background:rgba(245,158,11,.15);color:#fbbf5a;border-color:rgba(245,158,11,.25)}
.tag-purple{background:rgba(192,132,252,.15);color:#d8b4fe;border-color:rgba(192,132,252,.25)}
.tag-red{background:rgba(244,63,94,.12);color:#fb7c96;border-color:rgba(244,63,94,.2)}
/* ── MOBILE SIDEBAR ─────────────────────────────────────────────── */
.sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:99;backdrop-filter:blur(2px)}
@media(max-width:991px){
  .sidebar{transform:translateX(-100%)}
  .sidebar.open{transform:translateX(0)}
  .sidebar-overlay.show{display:block}
  .sidebar-push{margin-left:0}
  .content{padding:16px}
  .hero-strip{padding:18px}
  .hero-text h2{font-size:18px}
}
@media(max-width:575px){
  .content{padding:12px}
  .stat-value{font-size:26px}
  .topbar{padding:12px 16px}
  .tbl th,.tbl td{padding:8px 12px}
}
</style>

<style>
.section{display:none}.section.active{display:block}
.di{background:var(--surface-2);border:1px solid var(--border);border-radius:10px;color:var(--text);font-family:var(--font-body);font-size:13px;padding:10px 14px;width:100%;outline:none;transition:border-color .2s}.di:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(61,127,255,.1)}
.dt{resize:vertical;min-height:80px}.ds option{background:var(--surface-2)}
.fl{font-size:11px;font-weight:600;color:var(--muted-2);letter-spacing:.06em;text-transform:uppercase;display:block;margin-bottom:6px}
.bp{background:var(--accent);color:#fff;border:none;border-radius:10px;padding:10px 20px;font-size:13px;font-weight:600;cursor:pointer;transition:opacity .2s;font-family:var(--font-body)}.bp:hover{opacity:.85}
.bds{background:rgba(244,63,94,.1);color:var(--red);border:1px solid rgba(244,63,94,.2);border-radius:8px;padding:5px 12px;font-size:12px;font-weight:600;cursor:pointer;font-family:var(--font-body)}
.da{border-radius:var(--r);padding:12px 18px;font-size:13px;margin-bottom:20px;display:flex;align-items:center;gap:10px;border:1px solid}
.das{background:rgba(16,185,129,.08);border-color:rgba(16,185,129,.2);color:#5dddb0}
.dad{background:rgba(244,63,94,.08);border-color:rgba(244,63,94,.2);color:#fb7c96}
.ir{display:flex;align-items:center;gap:12px;padding:12px 20px;border-bottom:1px solid var(--border)}.ir:last-child{border-bottom:none}
.ii{width:30px;height:30px;border-radius:8px;background:var(--surface-3);border:1px solid var(--border);display:grid;place-items:center;font-size:12px;color:var(--accent);flex-shrink:0}
.il{font-size:11px;color:var(--muted-2);font-weight:600;letter-spacing:.05em;text-transform:uppercase}.iv{font-size:13px;font-weight:500;margin-top:1px;word-break:break-all}
.instr-card{background:var(--surface-2);border:1px solid var(--border);border-radius:14px;padding:20px;transition:border-color .2s,transform .15s;cursor:pointer}.instr-card:hover{border-color:var(--accent);transform:translateY(-2px)}
.iavt{width:52px;height:52px;border-radius:13px;display:grid;place-items:center;font-family:var(--font-display);font-size:18px;font-weight:800;flex-shrink:0}
.sched-card{background:var(--surface-2);border:1px solid var(--border);border-radius:14px;padding:18px;margin-bottom:10px;transition:border-color .2s}.sched-card:hover{border-color:var(--border-bright)}
.day-pill{display:inline-flex;align-items:center;padding:4px 14px;border-radius:999px;font-size:12px;font-weight:700;font-family:var(--font-display);margin-bottom:10px}
.bk-card{background:var(--surface-2);border:1px solid var(--border);border-radius:14px;padding:18px;margin-bottom:10px}
.bkp{background:rgba(245,158,11,.12);color:#fbbf5a;border:1px solid rgba(245,158,11,.2);padding:4px 10px;border-radius:999px;font-size:11px;font-weight:600}
.bka{background:rgba(61,127,255,.12);color:#7daeff;border:1px solid rgba(61,127,255,.2);padding:4px 10px;border-radius:999px;font-size:11px;font-weight:600}
.bkd{background:rgba(16,185,129,.12);color:#5dddb0;border:1px solid rgba(16,185,129,.2);padding:4px 10px;border-radius:999px;font-size:11px;font-weight:600}
.bkr{background:rgba(244,63,94,.1);color:#fb7c96;border:1px solid rgba(244,63,94,.18);padding:4px 10px;border-radius:999px;font-size:11px;font-weight:600}
.bkc{background:var(--surface-3);color:var(--muted);border:1px solid var(--border);padding:4px 10px;border-radius:999px;font-size:11px;font-weight:600}
.star-i{font-size:24px;cursor:pointer;color:var(--surface-3);transition:color .1s}
.star-i.on{color:var(--amber)}
.stars-sm{color:var(--amber);letter-spacing:2px}
.modal-dark .modal-content{background:var(--surface);border:1px solid var(--border-bright);border-radius:var(--r-lg);color:var(--text)}
.modal-dark .modal-header{border-bottom:1px solid var(--border)}.modal-dark .modal-footer{border-top:1px solid var(--border)}
.modal-dark .modal-title{font-family:var(--font-display);font-size:16px;font-weight:700}.modal-dark .btn-close{filter:invert(1) opacity(.5)}
.sdet{background:var(--surface-2);border:1px solid var(--border);border-radius:12px;padding:16px;margin-bottom:16px}
.sdet-row{display:flex;align-items:center;gap:10px;margin-bottom:8px}.sdet-row:last-child{margin-bottom:0}
.notif-item{padding:12px 20px;border-bottom:1px solid var(--border);display:flex;align-items:flex-start;gap:10px}.notif-item:last-child{border-bottom:none}
.ndot{width:8px;height:8px;border-radius:50%;background:var(--accent);flex-shrink:0;margin-top:5px}.ndot.read{background:var(--muted-2)}
.empty-state{text-align:center;color:var(--muted);padding:40px 20px}.empty-state i{font-size:36px;opacity:.3;display:block;margin-bottom:12px}.empty-state p{font-size:13px;margin:0}
.ec{text-align:center;color:var(--muted);padding:40px !important}
.rate-banner{background:rgba(245,158,11,.07);border:1px solid rgba(245,158,11,.2);border-radius:var(--r);padding:14px 18px;margin-bottom:16px;display:flex;align-items:center;gap:12px}
.hcard{background:linear-gradient(135deg,#0e1e3a,#091630);border:1px solid rgba(61,127,255,.15);border-radius:var(--r);padding:24px;position:relative;overflow:hidden;margin-bottom:20px}
.hdiv{border-top:1px solid rgba(255,255,255,.07);margin:16px 0}
.havt{width:60px;height:60px;border-radius:14px;flex-shrink:0;background:linear-gradient(135deg,var(--accent),#1a4fb0);display:grid;place-items:center;font-family:var(--font-display);font-size:20px;font-weight:800}
.prog-row{margin-bottom:14px}.prog-row:last-child{margin-bottom:0}
.prog-lbl{display:flex;justify-content:space-between;margin-bottom:5px}.prog-lbl span{font-size:12px;color:var(--muted)}.prog-lbl strong{font-size:12px}
.prog-track{height:6px;background:var(--surface-3);border-radius:999px;overflow:hidden}.prog-fill{height:100%;border-radius:999px}
.back-btn{display:inline-flex;align-items:center;gap:8px;color:var(--muted);font-size:13px;font-weight:600;cursor:pointer;margin-bottom:20px;background:none;border:none;padding:0;font-family:var(--font-body)}.back-btn:hover{color:var(--text)}
.cert-thumb{max-width:100%;max-height:200px;border-radius:10px;object-fit:contain;cursor:zoom-in;border:1px solid var(--border)}
</style>
</head>
<body>
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>
<div class="app-wrapper">

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
<div class="logo"><div class="logo-icon"><img src="../assets/img/logo.png" alt="MotoSafe"></div>Moto<span>Safe</span></div>
<div class="nav-label">Main</div>
<a class="nav-item <?= $activeSection==='dashboard'?'active':'' ?>" href="#" onclick="showSection('dashboard');return false;"><i class="fa-solid fa-house"></i> Overview</a>
<div class="nav-label">My Learning</div>
<a class="nav-item <?= $activeSection==='schedule'?'active':'' ?>" href="#" onclick="showSection('schedule');return false;"><i class="fa-solid fa-calendar-days"></i> My Schedule
<?php if($approvedCnt>0):?><span style="margin-left:auto;background:rgba(61,127,255,.2);color:#7daeff;padding:1px 7px;border-radius:999px;font-size:10px;font-weight:700;"><?= $approvedCnt ?></span><?php endif;?>
<?php if($pendingCnt>0):?><span style="margin-left:2px;background:rgba(245,158,11,.2);color:#fbbf5a;padding:1px 7px;border-radius:999px;font-size:10px;font-weight:700;"><?= $pendingCnt ?></span><?php endif;?>
</a>
<a class="nav-item <?= $activeSection==='progress'?'active':'' ?>" href="#" onclick="showSection('progress');return false;"><i class="fa-solid fa-chart-line"></i> My Progress
<?php if($unratedCnt>0):?><span style="margin-left:auto;background:rgba(16,185,129,.2);color:#5dddb0;padding:1px 7px;border-radius:999px;font-size:10px;font-weight:700;"><?= $unratedCnt ?>★</span><?php endif;?>
</a>
<a class="nav-item <?= $activeSection==='messages'?'active':'' ?>" href="#" onclick="showSection('messages');markReadS();return false;"><i class="fa-solid fa-comments"></i> Messages
<?php if($unreadN>0):?><span style="margin-left:auto;background:rgba(244,63,94,.2);color:#fb7c96;padding:1px 7px;border-radius:999px;font-size:10px;font-weight:700;"><?= $unreadN ?></span><?php endif;?>
</a>
<div class="nav-label">Find a Course</div>
<a class="nav-item <?= $activeSection==='available_schedules'?'active':'' ?>" href="#" onclick="showSection('available_schedules');return false;"><i class="fa-solid fa-calendar-plus"></i> Available Schedules</a>
<a class="nav-item <?= ($activeSection==='instructors'||$activeSection==='view_instr')?'active':'' ?>" href="#" onclick="showSection('instructors');return false;"><i class="fa-solid fa-chalkboard-user"></i> Browse Instructors</a>
<div class="nav-label">Account</div>
<a class="nav-item <?= $activeSection==='profile'?'active':'' ?>" href="#" onclick="showSection('profile');return false;"><i class="fa-solid fa-user-circle"></i> My Profile</a>
<div class="sidebar-bottom">
<div class="user-row"><div class="avatar"><?= $initials ?></div><div><div class="user-name"><?= $fullname ?></div><div class="user-role">Student Driver</div></div></div>
<a class="nav-item" href="../admin/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Sign Out</a>
</div>
</aside>

<div class="sidebar-push">
<div class="topbar">
<div class="d-flex align-items-center gap-3">
<div class="icon-btn d-md-none" onclick="openSidebar()"><i class="fa-solid fa-bars"></i></div>
<div id="page-title-text" class="page-title">Overview</div>
</div>
<div class="search-box d-none d-md-flex"><i class="fa-solid fa-magnifying-glass"></i><input type="text" placeholder="Search…" oninput="handleSearch(this.value)"></div>
</div>

<div class="content">
<?php if($successMsg):?><div class="da das"><i class="fa-solid fa-circle-check"></i><?= htmlspecialchars($successMsg) ?></div><?php endif;?>
<?php if($errorMsg):?><div class="da dad"><i class="fa-solid fa-circle-exclamation"></i><?= htmlspecialchars($errorMsg) ?></div><?php endif;?>
<?php if($unratedCnt>0):?>
<div class="rate-banner"><i class="fa-solid fa-star" style="color:var(--amber);font-size:18px;flex-shrink:0;"></i><div style="flex:1;"><strong><?= $unratedCnt ?> session<?= $unratedCnt>1?'s':'' ?></strong> waiting for your rating!</div><a href="#" onclick="showSection('progress');return false;" class="tag tag-amber" style="text-decoration:none;flex-shrink:0;">Rate Now</a></div>
<?php endif;?>

<!-- ══ OVERVIEW ══ -->
<div id="section-dashboard" class="section <?= $activeSection==='dashboard'?'active':'' ?>">
<div class="hero-strip">
<div class="hero-text">
<div class="greeting"><?= $dayName ?>, <?= $monthYear ?></div>
<h2>Good <?= date('H')<12?'morning':(date('H')<18?'afternoon':'evening') ?>, <?= $firstName ?> 👋</h2>
<p><?= $approvedCnt>0 ? "You have <strong>$approvedCnt</strong> confirmed session".($approvedCnt>1?'s':'')." upcoming." : "No confirmed sessions yet. " ?><a href="#" onclick="showSection('instructors');return false;" style="color:var(--accent);">Browse instructors</a>!</p>
<div class="hero-quick">
<div class="hero-quick-item"><strong><?= $approvedCnt ?></strong><span>Confirmed</span></div>
<div class="hero-quick-item"><strong><?= $doneCnt ?></strong><span>Completed</span></div>
<div class="hero-quick-item"><strong><?= $pendingCnt ?></strong><span>Pending</span></div>
</div>
</div>
</div>

<!-- Stat cards -->
<div class="row g-3 mb-4">
<div class="col-6 col-xl-3"><div class="stat-card" onclick="showSection('schedule')" style="cursor:pointer;"><div class="stat-top"><div class="stat-label">Confirmed Sessions</div><div class="stat-icon" style="color:var(--accent);"><i class="fa-solid fa-calendar-check"></i></div></div><div class="stat-value"><?= $approvedCnt ?></div><p class="stat-sub">Upcoming bookings</p></div></div>
<div class="col-6 col-xl-3"><div class="stat-card" onclick="showSection('progress')" style="cursor:pointer;"><div class="stat-top"><div class="stat-label">Completed</div><div class="stat-icon" style="color:var(--green);"><i class="fa-solid fa-flag-checkered"></i></div></div><div class="stat-value"><?= $doneCnt ?></div><p class="stat-sub">Sessions done</p></div></div>
<div class="col-6 col-xl-3"><div class="stat-card"><div class="stat-top"><div class="stat-label">Pending</div><div class="stat-icon" style="color:var(--amber);"><i class="fa-solid fa-hourglass-half"></i></div></div><div class="stat-value"><?= $pendingCnt ?></div><p class="stat-sub"><?= $pendingCnt>0?'<span class="warn">Awaiting approval</span>':'All cleared' ?></p></div></div>
<div class="col-6 col-xl-3"><div class="stat-card" onclick="showSection('progress')" style="cursor:pointer;"><div class="stat-top"><div class="stat-label">Completion Rate</div><div class="stat-icon" style="color:#c084fc;"><i class="fa-solid fa-chart-line"></i></div></div><div class="stat-value"><?= $totalReqs>0?round($doneCnt/$totalReqs*100):0 ?>%</div><p class="stat-sub">Of all requests</p></div></div>
</div>

<div class="row g-3">
<!-- Upcoming sessions -->
<div class="col-12 col-lg-6">
<div class="panel">
<div class="panel-head"><div><div class="panel-title"><i class="fa-solid fa-calendar-days me-2" style="color:var(--accent);"></i>Upcoming Sessions</div><div class="panel-sub">Your confirmed bookings</div></div><a href="#" onclick="showSection('schedule');return false;" class="tag tag-blue" style="text-decoration:none;">View All</a></div>
<?php
$upcomingScheds->data_seek(0);
if($upcomingScheds->num_rows===0): ?>
<div class="empty-state"><i class="fa-solid fa-calendar-days"></i><p>No upcoming sessions. <a href="#" onclick="showSection('instructors');return false;" style="color:var(--accent);">Book one now</a>.</p></div>
<?php else: while($up=$upcomingScheds->fetch_assoc()): $tc=$cColors[$up['course_type']]??'tag-blue'; $dc=$dayColors[$up['day_of_week']]??'var(--accent)'; $isToday=($up['day_of_week']===$todayDay); ?>
<div style="padding:12px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;">
<div>
<div style="display:flex;align-items:center;gap:8px;margin-bottom:3px;">
<span style="background:<?= $dc ?>22;color:<?= $dc ?>;border:1px solid <?= $dc ?>44;border-radius:999px;padding:2px 9px;font-size:11px;font-weight:700;"><?= !empty($up['slot_date']) ? date('D', strtotime($up['slot_date'])) : $up['day_of_week'] ?></span>
<?php if($isToday): ?><span style="font-size:10px;font-weight:700;color:var(--green);">TODAY</span><?php endif; ?>
</div>
<div style="font-weight:700;font-size:13px;"><?= htmlspecialchars($up['course_type']) ?></div>
<div style="font-size:12px;color:var(--muted);margin-top:2px;"><i class="fa-solid fa-chalkboard-user me-1"></i><?= htmlspecialchars($up['iname']) ?> · <i class="fa-solid fa-clock me-1" style="color:var(--accent);"></i><?= schedHoursLabel($up) ?></div>
</div>
<span class="bka" style="flex-shrink:0;"><i class="fa-solid fa-circle-check me-1"></i>Confirmed</span>
</div>
<?php endwhile; endif; ?>
</div>
</div>

<!-- Progress snapshot + recent notifications -->
<div class="col-12 col-lg-6">
<div class="panel mb-3">
<div class="panel-head"><div><div class="panel-title"><i class="fa-solid fa-chart-line me-2" style="color:#c084fc;"></i>Progress Overview</div></div><a href="#" onclick="showSection('progress');return false;" class="tag tag-blue" style="text-decoration:none;">Details</a></div>
<div style="padding:16px 20px;">
<div class="prog-row"><div class="prog-lbl"><span>Completion Rate</span><strong><?= $totalReqs>0?round($doneCnt/$totalReqs*100):0 ?>%</strong></div><div class="prog-track"><div class="prog-fill" style="width:<?= $totalReqs>0?round($doneCnt/$totalReqs*100):0 ?>%;background:linear-gradient(90deg,var(--green),#5dddb0);"></div></div></div>
<div class="prog-row"><div class="prog-lbl"><span>Approval Rate</span><strong><?= $totalReqs>0?round(($approvedCnt+$doneCnt)/$totalReqs*100):0 ?>%</strong></div><div class="prog-track"><div class="prog-fill" style="width:<?= $totalReqs>0?round(($approvedCnt+$doneCnt)/$totalReqs*100):0 ?>%;background:linear-gradient(90deg,var(--accent),#7daeff);"></div></div></div>
<?php
$skills=[['TDC','fa-book','var(--accent)'],['PDC Car','fa-car','var(--amber)'],['PDC Motorcycle','fa-motorcycle','#c084fc']];
foreach($skills as [$sm,$si,$sc]):
  $td=(int)$conn->query("SELECT COUNT(*) AS c FROM enrolment_requests er JOIN course_schedules cs ON cs.schedule_id=er.schedule_id JOIN instructor_courses ic ON ic.course_id=cs.course_id WHERE er.student_id=$student_id AND ic.course_type LIKE '%$sm%' AND er.status IN('approved','done')")->fetch_assoc()['c'];
  $dn=(int)$conn->query("SELECT COUNT(*) AS c FROM enrolment_requests er JOIN course_schedules cs ON cs.schedule_id=er.schedule_id JOIN instructor_courses ic ON ic.course_id=cs.course_id WHERE er.student_id=$student_id AND ic.course_type LIKE '%$sm%' AND er.status='done'")->fetch_assoc()['c'];
  if($td===0) continue;
  $pct=round($dn/$td*100);
?>
<div class="prog-row"><div class="prog-lbl"><span><i class="fa-solid <?= $si ?> me-1" style="color:<?= $sc ?>;"></i><?= $sm ?></span><strong><?= $dn ?>/<?= $td ?></strong></div><div class="prog-track"><div class="prog-fill" style="width:<?= $pct ?>%;background:<?= $sc ?>;"></div></div></div>
<?php endforeach; ?>
</div>
</div>
<!-- Notification snapshot -->
<div class="panel">
<div class="panel-head"><div><div class="panel-title"><i class="fa-solid fa-comments me-2" style="color:var(--accent);"></i>Recent Messages</div><div class="panel-sub"><?= $unreadN ?> unread</div></div><a href="#" onclick="showSection('messages');markReadS();return false;" class="tag tag-blue" style="text-decoration:none;">View All</a></div>
<?php
$notifSnap=$conn->query("SELECT * FROM notifications WHERE user_id=$student_id AND user_type='student' ORDER BY created_at DESC LIMIT 3");
if(!$notifSnap||$notifSnap->num_rows===0): ?>
<div class="empty-state" style="padding:20px;"><p>No messages yet.</p></div>
<?php else: while($n=$notifSnap->fetch_assoc()): ?>
<div class="notif-item" style="padding:10px 16px;">
<div class="ndot <?= $n['is_read']?'read':'' ?>"></div>
<div style="flex:1;margin-left:4px;"><div style="font-size:12px;<?= !$n['is_read']?'font-weight:600;':'' ?>;line-height:1.5;"><?= htmlspecialchars(mb_strimwidth($n['message'],0,80,'…')) ?></div><div style="font-size:10px;color:var(--muted-2);margin-top:2px;"><?= date('M d, g:i A',strtotime($n['created_at'])) ?></div></div>
</div>
<?php endwhile; endif; ?>
</div>
</div>
</div>
</div>

<!-- ══ MY SCHEDULE ══ -->
<div id="section-schedule" class="section <?= $activeSection==='schedule'?'active':'' ?>">
<div class="row g-3">

<!-- Left: Browse / Request -->
<div class="col-12 col-lg-4">
<div class="panel mb-3">
<div class="panel-head"><div><div class="panel-title"><i class="fa-solid fa-plus me-2" style="color:var(--accent);"></i>Book a Session</div><div class="panel-sub">Find an instructor and request a slot</div></div></div>
<div style="padding:20px;text-align:center;">
<i class="fa-solid fa-chalkboard-user" style="font-size:32px;color:var(--accent);opacity:.5;display:block;margin-bottom:12px;"></i>
<div style="font-size:13px;color:var(--muted);line-height:1.6;margin-bottom:16px;">Browse instructors, view their available slots, and send a booking request.</div>
<a href="#" onclick="showSection('instructors');return false;" class="bp" style="display:block;text-align:center;text-decoration:none;"><i class="fa-solid fa-search me-2"></i>Browse Instructors</a>
</div>
</div>
<div class="panel">
<div class="panel-head"><div class="panel-title">Summary</div></div>
<div class="ir"><div class="ii" style="color:var(--accent);"><i class="fa-solid fa-circle-check"></i></div><div><div class="il">Confirmed</div><div class="iv" style="color:var(--accent);font-weight:700;"><?= $approvedCnt ?> upcoming</div></div></div>
<div class="ir"><div class="ii" style="color:var(--amber);"><i class="fa-solid fa-hourglass-half"></i></div><div><div class="il">Pending Approval</div><div class="iv"><?= $pendingCnt ?> request<?= $pendingCnt!=1?'s':'' ?></div></div></div>
<div class="ir"><div class="ii" style="color:var(--green);"><i class="fa-solid fa-flag-checkered"></i></div><div><div class="il">Completed</div><div class="iv"><?= $doneCnt ?> session<?= $doneCnt!=1?'s':'' ?></div></div></div>
</div>
</div>

<!-- Right: Confirmed sessions + pending -->
<div class="col-12 col-lg-8">

<!-- Confirmed sessions grouped by day -->
<div class="panel mb-3" style="padding:0;">
<div class="panel-head" style="padding:16px 20px;"><div><div class="panel-title"><i class="fa-solid fa-circle-check me-2" style="color:var(--accent);"></i>Confirmed Sessions</div><div class="panel-sub"><?= $approvedCnt ?> booking<?= $approvedCnt!=1?'s':'' ?> approved</div></div></div>
<?php
$upcomingScheds->data_seek(0);
if($upcomingScheds->num_rows===0): ?>
<div class="empty-state"><i class="fa-solid fa-calendar-check"></i><p>No confirmed sessions yet. <a href="#" onclick="showSection('instructors');return false;" style="color:var(--accent);">Browse instructors</a> to book your first session.</p></div>
<?php else:
$bsByDay=[];
while($b=$upcomingScheds->fetch_assoc()) $bsByDay[$b['day_of_week']][]=$b;
?>
<div style="padding:8px 16px 16px;">
<?php foreach($days as $day): if(empty($bsByDay[$day]))continue; $dc=$dayColors[$day]??'var(--accent)'; $isToday2=($day===$todayDay); ?>
<div style="margin-bottom:16px;">
<div class="day-pill" style="background:<?= $dc ?>22;color:<?= $dc ?>;border:1px solid <?= $dc ?>44;"><?= $day ?><?php if($isToday2): ?> <span style="font-size:10px;background:<?= $dc ?>;color:#fff;border-radius:999px;padding:1px 6px;margin-left:4px;">Today</span><?php endif; ?></div>
<?php foreach($bsByDay[$day] as $b):
$bW=array_filter(explode(' ',trim($b['iname']))); $bIn=strtoupper(substr(implode('',array_map(fn($w)=>$w[0],$bW)),0,2));
$tc=$cColors[$b['course_type']]??'tag-blue'; ?>
<div style="background:var(--surface-2);border:1px solid var(--border);border-left:3px solid var(--accent);border-radius:12px;padding:14px 16px;margin-bottom:8px;">
<div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-2">
<div class="d-flex align-items-center gap-2">
<div style="width:34px;height:34px;border-radius:9px;background:linear-gradient(135deg,var(--accent),#1a4fb0);display:grid;place-items:center;font-family:var(--font-display);font-size:11px;font-weight:800;flex-shrink:0;"><?= $bIn ?></div>
<div>
<div style="font-weight:700;font-size:13px;"><?= htmlspecialchars($b['iname']) ?></div>
<span class="tag <?= $tc ?>" style="font-size:10px;"><?= htmlspecialchars($b['course_type']) ?></span>
</div>
</div>
<span class="bka"><i class="fa-solid fa-circle-check me-1"></i>Confirmed</span>
</div>
<div style="display:flex;gap:12px;flex-wrap:wrap;font-size:12px;color:var(--muted);margin-bottom:10px;">
<span><i class="fa-solid fa-calendar-week me-1" style="color:var(--accent);"></i><?= schedDateLabel($b) ?></span>
<span><i class="fa-solid fa-clock me-1" style="color:var(--amber);"></i><?= schedHoursLabel($b) ?></span>
<?php if($b['price']): ?><span style="color:var(--green);"><i class="fa-solid fa-peso-sign me-1"></i>₱<?= number_format($b['price'],0) ?></span><?php endif; ?>
</div>
<?php if($b['instructor_note']): ?>
<div style="background:rgba(61,127,255,.07);border:1px solid rgba(61,127,255,.15);border-radius:8px;padding:8px 12px;margin-bottom:8px;font-size:12px;"><i class="fa-solid fa-message me-1" style="color:var(--accent);"></i><?= htmlspecialchars($b['instructor_note']) ?></div>
<?php endif; ?>
<form method="POST" action="<?= $selfFile ?>?section=schedule" onsubmit="return confirm('Cancel this confirmed booking?');" style="display:inline;">
<input type="hidden" name="action" value="cancel_enrol"><input type="hidden" name="request_id" value="<?= $b['request_id'] ?>">
<button type="submit" class="bds" style="font-size:12px;padding:5px 12px;"><i class="fa-solid fa-xmark me-1"></i>Cancel</button>
</form>
</div>
<?php endforeach; ?>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>

<!-- Pending requests -->
<?php if($pendingCnt>0): ?>
<div class="panel mb-3" style="padding:0;border-color:rgba(245,158,11,.3);">
<div class="panel-head" style="padding:16px 20px;"><div><div class="panel-title"><i class="fa-solid fa-hourglass-half me-2" style="color:var(--amber);"></i>Pending Requests</div><div class="panel-sub"><?= $pendingCnt ?> awaiting instructor approval</div></div></div>
<?php
$myEnrols->data_seek(0);
while($bk=$myEnrols->fetch_assoc()):
  if($bk['status']!=='pending') continue;
  $tc=$cColors[$bk['course_type']]??'tag-blue'; $dc=$dayColors[$bk['day_of_week']]??'var(--accent)';
?>
<div style="padding:12px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;">
<div>
<div style="font-weight:700;font-size:13px;"><?= htmlspecialchars($bk['iname']) ?> — <span class="tag <?= $tc ?>" style="font-size:10px;"><?= htmlspecialchars($bk['course_type']) ?></span></div>
<div style="font-size:12px;color:var(--muted);margin-top:3px;">
<span style="background:<?= $dc ?>22;color:<?= $dc ?>;border:1px solid <?= $dc ?>44;border-radius:999px;padding:1px 7px;font-size:10px;font-weight:700;"><?= !empty($bk['slot_date']) ? date('D', strtotime($bk['slot_date'])) : $bk['day_of_week'] ?></span>
<?= schedDateLabel($bk) ?> · <?= schedHoursLabel($bk) ?> · Requested <?= date('M d',strtotime($bk['requested_at'])) ?>
</div>
</div>
<div class="d-flex align-items-center gap-2">
<span class="bkp"><i class="fa-solid fa-hourglass-half me-1"></i>Pending</span>
<form method="POST" action="<?= $selfFile ?>?section=schedule" onsubmit="return confirm('Cancel this request?');" style="display:inline;">
<input type="hidden" name="action" value="cancel_enrol"><input type="hidden" name="request_id" value="<?= $bk['request_id'] ?>">
<button type="submit" class="bds" style="font-size:11px;padding:4px 10px;">Cancel</button>
</form>
</div>
</div>
<?php endwhile; ?>
</div>
<?php endif; ?>

<!-- All enrolments history table -->
<div class="panel" style="padding:0;">
<div class="panel-head" style="padding:16px 20px;"><div><div class="panel-title"><i class="fa-solid fa-list me-2" style="color:var(--muted-2);"></i>Enrolment History</div></div></div>
<div style="display:flex;gap:4px;padding:0 16px 12px;flex-wrap:wrap;">
<button class="tab active" id="bk-all" onclick="filterBk('all',this)">All</button>
<button class="tab" id="bk-approved" onclick="filterBk('approved',this)">Approved</button>
<button class="tab" id="bk-pending" onclick="filterBk('pending',this)">Pending</button>
<button class="tab" id="bk-done" onclick="filterBk('done',this)">Done</button>
<button class="tab" id="bk-rejected" onclick="filterBk('rejected',this)">Rejected</button>
</div>
<?php
$myEnrols->data_seek(0);
if($myEnrols->num_rows===0): ?>
<div class="empty-state"><i class="fa-solid fa-calendar-xmark"></i><p>No enrolments yet.</p></div>
<?php else: ?>
<div id="bkList" style="padding:0 16px 16px;">
<?php
$scm=['pending'=>'bkp','approved'=>'bka','done'=>'bkd','rejected'=>'bkr','cancelled'=>'bkc'];
while($bk=$myEnrols->fetch_assoc()):
$sc=$scm[$bk['status']]??'bkp'; $tc=$cColors[$bk['course_type']]??'tag-blue';
$iW2=array_filter(explode(' ',trim($bk['iname']))); $iIn2=strtoupper(substr(implode('',array_map(fn($w)=>$w[0],$iW2)),0,2));
?>
<div class="bk-card bk-item" data-status="<?= $bk['status'] ?>">
<div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-2">
<div class="d-flex align-items-center gap-3">
<div style="width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,var(--accent),#1a4fb0);display:grid;place-items:center;font-family:var(--font-display);font-size:13px;font-weight:800;flex-shrink:0;"><?= $iIn2 ?></div>
<div><div style="font-weight:700;font-size:13px;"><?= htmlspecialchars($bk['iname']) ?></div><span class="tag <?= $tc ?>" style="font-size:10px;"><?= htmlspecialchars($bk['course_type']) ?></span></div>
</div>
<span class="<?= $sc ?>"><?= ucfirst($bk['status']) ?></span>
</div>
<div style="display:flex;gap:12px;flex-wrap:wrap;font-size:12px;color:var(--muted);margin-bottom:8px;">
<span><i class="fa-solid fa-calendar-week me-1" style="color:var(--accent);"></i><strong><?= schedDateLabel($bk) ?></strong></span>
<span><i class="fa-solid fa-clock me-1" style="color:var(--amber);"></i><?= schedHoursLabel($bk) ?></span>
<?php if($bk['price']): ?><span style="color:var(--green);"><i class="fa-solid fa-peso-sign me-1"></i>₱<?= number_format($bk['price'],0) ?></span><?php endif; ?>
</div>
<?php if($bk['instructor_note']): ?><div style="background:var(--surface-3);border-radius:8px;padding:8px 12px;font-size:12px;color:var(--muted);margin-bottom:8px;"><i class="fa-solid fa-message me-1" style="color:var(--accent);"></i><?= htmlspecialchars($bk['instructor_note']) ?></div><?php endif; ?>
<?php if($bk['status']==='pending'): ?>
<form method="POST" action="<?= $selfFile ?>?section=schedule" onsubmit="return confirm('Cancel?');" style="display:inline;">
<input type="hidden" name="action" value="cancel_enrol"><input type="hidden" name="request_id" value="<?= $bk['request_id'] ?>">
<button type="submit" class="bds" style="font-size:12px;"><i class="fa-solid fa-xmark me-1"></i>Cancel Request</button>
</form>
<?php elseif($bk['status']==='done' && !$bk['my_rating']): ?>
<button class="bp" style="font-size:12px;padding:7px 16px;" onclick="openRate(<?= $bk['request_id'] ?>,'<?= htmlspecialchars(addslashes($bk['iname'])) ?>','<?= htmlspecialchars(addslashes($bk['course_type'])) ?>')"><i class="fa-solid fa-star me-2"></i>Rate Session</button>
<?php elseif($bk['status']==='done' && $bk['my_rating']): ?>
<div style="font-size:13px;color:var(--amber);"><i class="fa-solid fa-star me-1"></i>Your rating: <?= str_repeat('★',$bk['my_rating']).str_repeat('☆',5-$bk['my_rating']) ?></div>
<?php endif; ?>
</div>
<?php endwhile; ?>
</div>
<?php endif; ?>
</div>

</div>
</div>
</div>

<!-- ══ MY PROGRESS ══ -->
<div id="section-progress" class="section <?= $activeSection==='progress'?'active':'' ?>">

<!-- Stats -->
<div class="row g-3 mb-4">
<div class="col-6 col-md-3"><div class="stat-card"><div class="stat-top"><div class="stat-label">Sessions Done</div><div class="stat-icon" style="color:var(--green);"><i class="fa-solid fa-flag-checkered"></i></div></div><div class="stat-value"><?= $doneCnt ?></div><p class="stat-sub">Completed</p></div></div>
<div class="col-6 col-md-3"><div class="stat-card"><div class="stat-top"><div class="stat-label">Completion Rate</div><div class="stat-icon" style="color:var(--accent);"><i class="fa-solid fa-chart-line"></i></div></div><div class="stat-value"><?= $totalReqs>0?round($doneCnt/$totalReqs*100):0 ?>%</div><p class="stat-sub">Of all requests</p></div></div>
<div class="col-6 col-md-3"><div class="stat-card"><div class="stat-top"><div class="stat-label">Instructors</div><div class="stat-icon" style="color:#c084fc;"><i class="fa-solid fa-chalkboard-user"></i></div></div><div class="stat-value"><?= $myInstructors?$myInstructors->num_rows:0 ?></div><p class="stat-sub">Worked with</p></div></div>
<div class="col-6 col-md-3"><div class="stat-card"><div class="stat-top"><div class="stat-label">To Rate</div><div class="stat-icon" style="color:var(--amber);"><i class="fa-solid fa-star"></i></div></div><div class="stat-value"><?= $unratedCnt ?></div><p class="stat-sub"><?= $unratedCnt>0?'<span class="warn">Rate your sessions</span>':'All rated ✓' ?></p></div></div>
</div>

<!-- Skill progress bars -->
<div class="panel mb-3">
<div class="panel-head"><div><div class="panel-title"><i class="fa-solid fa-road me-2" style="color:var(--amber);"></i>Skill Progress</div><div class="panel-sub">Based on your completed sessions</div></div></div>
<div style="padding:20px;">
<?php
$skillDefs=[
  ['Theoretical Knowledge (TDC)','TDC','fa-book','var(--accent)'],
  ['Car Driving (PDC Car)','PDC Car','fa-car','var(--amber)'],
  ['Motorcycle Riding (PDC Motorcycle)','PDC Motorcycle','fa-motorcycle','#c084fc'],
];
$anySkill=false;
foreach($skillDefs as [$label,$match,$icon,$color]):
  $td=(int)$conn->query("SELECT COUNT(*) AS c FROM enrolment_requests er JOIN course_schedules cs ON cs.schedule_id=er.schedule_id JOIN instructor_courses ic ON ic.course_id=cs.course_id WHERE er.student_id=$student_id AND ic.course_type LIKE '%$match%' AND er.status IN('approved','done')")->fetch_assoc()['c'];
  $dn=(int)$conn->query("SELECT COUNT(*) AS c FROM enrolment_requests er JOIN course_schedules cs ON cs.schedule_id=er.schedule_id JOIN instructor_courses ic ON ic.course_id=cs.course_id WHERE er.student_id=$student_id AND ic.course_type LIKE '%$match%' AND er.status='done'")->fetch_assoc()['c'];
  $pct=$td>0?round($dn/$td*100):0;
?>
<div style="background:var(--surface-2);border:1px solid var(--border);border-radius:12px;padding:14px 16px;margin-bottom:10px;">
<div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
<div style="width:34px;height:34px;border-radius:8px;background:<?= $color ?>22;border:1px solid <?= $color ?>44;display:grid;place-items:center;flex-shrink:0;"><i class="fa-solid <?= $icon ?>" style="color:<?= $color ?>;font-size:13px;"></i></div>
<div style="flex:1;"><div style="font-weight:700;font-size:13px;"><?= $label ?></div><div style="font-size:11px;color:var(--muted);"><?= $dn ?> session<?= $dn!=1?'s':'' ?> completed<?= $td>0?' of '.$td.' enrolled':' — not enrolled yet' ?></div></div>
<div style="font-family:var(--font-display);font-size:20px;font-weight:800;color:<?= $color ?>;"><?= $pct ?>%</div>
</div>
<div style="height:8px;background:var(--surface-3);border-radius:999px;overflow:hidden;"><div style="height:100%;width:<?= $pct ?>%;background:<?= $color ?>;border-radius:999px;transition:width .4s;"></div></div>
</div>
<?php $anySkill=$anySkill||$td>0; endforeach;
if(!$anySkill): echo '<div style="font-size:13px;color:var(--muted);text-align:center;padding:12px 0;">Book your first session to start tracking skill progress.</div>'; endif; ?>
</div>
</div>

<!-- Completed lessons with feedback -->
<div class="panel mb-3" style="padding:0;">
<div class="panel-head" style="padding:16px 20px;"><div><div class="panel-title"><i class="fa-solid fa-book-open-reader me-2" style="color:var(--accent);"></i>Lessons Completed</div><div class="panel-sub"><?= $doneCnt ?> session<?= $doneCnt!=1?'s':'' ?> done</div></div></div>
<?php if(!$completedEnrols||$completedEnrols->num_rows===0): ?>
<div class="empty-state"><i class="fa-solid fa-book-open-reader"></i><p>No completed sessions yet. Once your instructor marks a session as done, it will appear here.</p></div>
<?php else: $completedEnrols->data_seek(0); while($ce=$completedEnrols->fetch_assoc()):
$ceW=array_filter(explode(' ',trim($ce['iname']))); $ceIn=strtoupper(substr(implode('',array_map(fn($w)=>$w[0],$ceW)),0,2));
$tc=$cColors[$ce['course_type']]??'tag-blue'; $dc=$dayColors[$ce['day_of_week']]??'var(--accent)'; ?>
<div style="padding:16px 20px;border-bottom:1px solid var(--border);">
<div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-2">
<div class="d-flex align-items-center gap-3">
<div style="width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,var(--green),#059669);display:grid;place-items:center;font-family:var(--font-display);font-size:12px;font-weight:800;flex-shrink:0;"><?= $ceIn ?></div>
<div>
<div style="font-weight:700;font-size:13px;"><?= htmlspecialchars($ce['iname']) ?></div>
<span class="tag <?= $tc ?>" style="font-size:10px;"><?= htmlspecialchars($ce['course_type']) ?></span>
</div>
</div>
<span class="bkd"><i class="fa-solid fa-flag-checkered me-1"></i>Completed<?= $ce['done_at']?' '.date('M d',strtotime($ce['done_at'])):'' ?></span>
</div>
<div style="display:flex;gap:12px;flex-wrap:wrap;font-size:12px;color:var(--muted);margin-bottom:10px;">
<span style="background:<?= $dc ?>22;color:<?= $dc ?>;border:1px solid <?= $dc ?>44;border-radius:999px;padding:1px 8px;font-size:11px;font-weight:700;"><?= $ce['day_of_week'] ?></span>
<span><i class="fa-solid fa-clock me-1" style="color:var(--amber);"></i><?= schedHoursLabel($ce) ?></span>
<span><i class="fa-solid fa-hourglass me-1"></i><?= schedHoursLabel($ce) ?></span>
</div>
<!-- Instructor feedback -->
<?php if($ce['instructor_note']): ?>
<div style="background:rgba(61,127,255,.07);border:1px solid rgba(61,127,255,.15);border-radius:10px;padding:10px 14px;margin-bottom:10px;">
<div style="font-size:10px;font-weight:700;color:var(--accent);text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;"><i class="fa-solid fa-chalkboard-user me-1"></i>Instructor Feedback</div>
<div style="font-size:13px;color:var(--text);line-height:1.6;"><?= htmlspecialchars($ce['instructor_note']) ?></div>
</div>
<?php else: ?>
<div style="background:var(--surface-3);border-radius:8px;padding:8px 12px;margin-bottom:10px;font-size:12px;color:var(--muted-2);"><i class="fa-solid fa-comment-slash me-1"></i>No instructor feedback yet.</div>
<?php endif; ?>
<!-- Rating -->
<?php if($ce['my_rating']): ?>
<div style="font-size:13px;color:var(--amber);"><i class="fa-solid fa-star me-1"></i>Your rating: <?= str_repeat('★',$ce['my_rating']).str_repeat('☆',5-$ce['my_rating']) ?>
<?php if($ce['my_review']): ?><span style="color:var(--muted);font-size:12px;margin-left:8px;">"<?= htmlspecialchars(mb_strimwidth($ce['my_review'],0,80,'…')) ?>"</span><?php endif; ?>
</div>
<?php else: ?>
<button class="bp" style="font-size:12px;padding:7px 16px;" onclick="openRate(<?= $ce['request_id'] ?>,'<?= htmlspecialchars(addslashes($ce['iname'])) ?>','<?= htmlspecialchars(addslashes($ce['course_type'])) ?>')"><i class="fa-solid fa-star me-2"></i>Rate This Session</button>
<?php endif; ?>
</div>
<?php endwhile; endif; ?>
</div>

<!-- Feedback from instructors -->
<?php if($myInstructors&&$myInstructors->num_rows>0): ?>
<div class="panel" style="padding:0;">
<div class="panel-head" style="padding:16px 20px;"><div><div class="panel-title"><i class="fa-solid fa-chalkboard-user me-2" style="color:#c084fc;"></i>Feedback from My Instructors</div></div></div>
<div style="padding:12px 16px 16px;display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:12px;">
<?php $myInstructors->data_seek(0); while($mi=$myInstructors->fetch_assoc()):
$miW=array_filter(explode(' ',trim($mi['fullname']))); $miIn=strtoupper(substr(implode('',array_map(fn($w)=>$w[0],$miW)),0,2)); ?>
<div style="background:var(--surface-2);border:1px solid var(--border);border-radius:14px;padding:16px;">
<div class="d-flex align-items-center gap-3 mb-3">
<div style="width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,var(--accent),#1a4fb0);display:grid;place-items:center;font-family:var(--font-display);font-size:13px;font-weight:800;flex-shrink:0;"><?= $miIn ?></div>
<div><div style="font-weight:700;font-size:14px;"><?= htmlspecialchars($mi['fullname']) ?></div><div style="font-size:12px;color:var(--muted);"><?= $mi['done_with'] ?> session<?= $mi['done_with']!=1?'s':'' ?> completed</div></div>
</div>
<?php if($mi['latest_note']): ?>
<div style="background:rgba(61,127,255,.07);border:1px solid rgba(61,127,255,.15);border-radius:8px;padding:10px 12px;">
<div style="font-size:10px;font-weight:700;color:var(--accent);text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Latest Note</div>
<div style="font-size:13px;color:var(--text);line-height:1.6;"><?= htmlspecialchars($mi['latest_note']) ?></div>
</div>
<?php else: ?>
<div style="font-size:12px;color:var(--muted-2);font-style:italic;">No notes from this instructor yet.</div>
<?php endif; ?>
</div>
<?php endwhile; ?>
</div>
</div>
<?php endif; ?>
</div>

<!-- ══ MESSAGES ══ -->
<div id="section-messages" class="section <?= $activeSection==='messages'?'active':'' ?>">
<div class="row g-3">
<div class="col-12 col-lg-8">
<div class="panel">
<div class="panel-head"><div><div class="panel-title"><i class="fa-solid fa-comments me-2" style="color:var(--accent);"></i>Messages &amp; Announcements</div><div class="panel-sub"><?= $unreadN ?> unread</div></div>
<form method="POST" action="<?= $selfFile ?>?section=messages" style="display:inline;"><input type="hidden" name="action" value="mark_read_student"><button type="submit" class="tag tag-blue" style="border:none;cursor:pointer;">Mark all read</button></form>
</div>
<?php if(!$myNotifs||$myNotifs->num_rows===0): ?>
<div class="empty-state"><i class="fa-solid fa-comments"></i><p>No messages yet. Announcements and messages from your instructor will appear here.</p></div>
<?php else:
$myNotifs->data_seek(0);
$ntypes=['announcement'=>['#f59e0b','fa-bullhorn','Announcement'],'message'=>['#3d7fff','fa-comment-dots','Direct Message'],'enrolment_approved'=>['#10b981','fa-circle-check','Booking Approved'],'enrolment_rejected'=>['#f43f5e','fa-circle-xmark','Booking Rejected'],'session_done'=>['#c084fc','fa-flag-checkered','Session Done'],'booking_cancelled'=>['#f43f5e','fa-ban','Booking Cancelled']];
while($n=$myNotifs->fetch_assoc()):
[$nc,$ni,$nl]=$ntypes[$n['type']??'']??['var(--muted-2)','fa-bell','Notification'];
?>
<div style="padding:14px 20px;border-bottom:1px solid var(--border);display:flex;align-items:flex-start;gap:12px;">
<div style="width:34px;height:34px;border-radius:9px;background:<?= $nc ?>22;border:1px solid <?= $nc ?>44;display:grid;place-items:center;flex-shrink:0;margin-top:1px;">
<i class="fa-solid <?= $ni ?>" style="color:<?= $nc ?>;font-size:13px;"></i>
</div>
<div style="flex:1;min-width:0;">
<div style="display:flex;align-items:center;gap:8px;margin-bottom:3px;">
<span style="font-size:10px;font-weight:700;color:<?= $nc ?>;text-transform:uppercase;letter-spacing:.05em;"><?= $nl ?></span>
<?php if(!$n['is_read']): ?><span style="font-size:10px;font-weight:700;color:var(--accent);">New</span><?php endif; ?>
</div>
<div style="font-size:13px;<?= !$n['is_read']?'font-weight:600;':'' ?>;line-height:1.5;word-break:break-word;"><?= htmlspecialchars($n['message']) ?></div>
<div style="font-size:11px;color:var(--muted-2);margin-top:4px;"><?= date('M d, Y · g:i A',strtotime($n['created_at'])) ?></div>
</div>
</div>
<?php endwhile; endif; ?>
</div>
</div>
<div class="col-12 col-lg-4">
<div class="panel mb-3">
<div class="panel-head"><div class="panel-title"><i class="fa-solid fa-circle-info me-2" style="color:var(--accent);"></i>Message Types</div></div>
<div class="ir"><div class="ii" style="background:rgba(245,158,11,.15);color:#f59e0b;border-color:rgba(245,158,11,.3);"><i class="fa-solid fa-bullhorn"></i></div><div><div class="il">Announcements</div><div class="iv" style="font-size:12px;">From your instructor — schedule changes, reminders</div></div></div>
<div class="ir"><div class="ii" style="background:rgba(61,127,255,.15);color:var(--accent);border-color:rgba(61,127,255,.3);"><i class="fa-solid fa-comment-dots"></i></div><div><div class="il">Direct Messages</div><div class="iv" style="font-size:12px;">Personal messages from your instructor</div></div></div>
<div class="ir"><div class="ii" style="background:rgba(16,185,129,.15);color:var(--green);border-color:rgba(16,185,129,.3);"><i class="fa-solid fa-circle-check"></i></div><div><div class="il">Booking Updates</div><div class="iv" style="font-size:12px;">Approval or rejection of your requests</div></div></div>
<div class="ir"><div class="ii" style="background:rgba(192,132,252,.15);color:#c084fc;border-color:rgba(192,132,252,.3);"><i class="fa-solid fa-flag-checkered"></i></div><div><div class="il">Session Done</div><div class="iv" style="font-size:12px;">Rate your completed sessions</div></div></div>
</div>
<div class="panel">
<div class="panel-head"><div class="panel-title">Summary</div></div>
<div class="ir"><div class="ii"><i class="fa-solid fa-envelope"></i></div><div><div class="il">Total Messages</div><div class="iv"><?= $myNotifs?$myNotifs->num_rows:0 ?></div></div></div>
<div class="ir"><div class="ii" style="color:var(--red);"><i class="fa-solid fa-envelope-open"></i></div><div><div class="il">Unread</div><div class="iv" style="<?= $unreadN>0?'color:var(--red);font-weight:700;':'' ?>"><?= $unreadN ?></div></div></div>
</div>
</div>
</div>
</div>

<!-- ══ AVAILABLE SCHEDULES ══ -->
<div id="section-available_schedules" class="section <?= $activeSection==='available_schedules'?'active':'' ?>"> 
<div class="panel"> 
<div class="panel-head"><div><div class="panel-title"><i class="fa-solid fa-calendar-plus me-2" style="color:var(--accent);"></i>Available Schedules</div><div class="panel-sub">All open slots, including partially booked schedules that still have remaining seats</div></div></div>
<?php if(!$availableSchedules || $availableSchedules->num_rows===0): ?>
<div class="empty-state"><i class="fa-solid fa-calendar-week"></i><p>No available schedules right now.</p></div>
<?php else: ?>
<div style="padding:16px;"> 
<?php $availByDay=[]; $availableSchedules->data_seek(0); while($as=$availableSchedules->fetch_assoc()){ $grpKey=!empty($as['slot_date']) ? date('Y-m-d', strtotime($as['slot_date'])) : ($as['day_of_week'] ?? 'Other'); $availByDay[$grpKey][]=$as; } ?>
<?php foreach($availByDay as $day => $schedGroup): $sample=$schedGroup[0]; $label=!empty($sample['slot_date']) ? date('l, M d, Y', strtotime($sample['slot_date'])) : $day; $colorKey=!empty($sample['slot_date']) ? date('l', strtotime($sample['slot_date'])) : ($sample['day_of_week'] ?? 'Monday'); $dc=$dayColors[$colorKey]??'var(--accent)'; ?>
<div style="margin-bottom:20px;"> 
<div class="day-pill" style="background:<?= $dc ?>22;color:<?= $dc ?>;border:1px solid <?= $dc ?>44;"><?= $label ?></div>
<?php foreach($schedGroup as $s): $tc=$cColors[$s['course_type']]??'tag-blue'; $myS=$s['my_status']; $myR=$s['my_rid']; $remaining=max(0, (int)$s['max_students'] - (int)$s['enrolled_cnt']); ?>
<div class="sched-card"> 
<div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-2"> 
<div>
<div style="font-weight:700;font-size:14px;"><?= htmlspecialchars($s['iname']) ?></div>
<div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:6px;"> 
<span class="tag <?= $tc ?>" style="font-size:11px;"><?= htmlspecialchars($s['course_type']) ?></span>
<span class="tag tag-blue" style="font-size:11px;"><i class="fa-solid fa-users me-1"></i><?= $remaining ?> slot<?= $remaining!=1?'s':'' ?> left</span>
<?php if((int)$s['enrolled_cnt']>0): ?><span class="tag tag-amber" style="font-size:11px;"><i class="fa-solid fa-user-check me-1"></i><?= (int)$s['enrolled_cnt'] ?> booked</span><?php endif; ?>
</div>
</div>
<span style="font-size:12px;color:var(--muted);"><i class="fa-solid fa-clock me-1" style="color:var(--accent);"></i><?= schedHoursLabel($s) ?></span>
</div>
<div style="display:flex;gap:14px;flex-wrap:wrap;font-size:12px;color:var(--muted);margin-bottom:10px;"> 
<?php if($s['price']): ?><span><i class="fa-solid fa-peso-sign me-1" style="color:var(--green);"></i>₱<?= number_format($s['price'],0) ?></span><?php endif; ?>
<?php if($s['duration_hrs']): ?><span><i class="fa-solid fa-hourglass me-1" style="color:var(--accent);"></i><?= $s['duration_hrs'] ?> hrs</span><?php endif; ?>
<span><i class="fa-solid fa-users me-1"></i>Max <?= (int)$s['max_students'] ?></span>
</div>
<?php if($s['course_desc']): ?><div style="font-size:12px;color:var(--muted-2);margin-bottom:10px;line-height:1.5;"><?= htmlspecialchars($s['course_desc']) ?></div><?php endif; ?>
<div class="d-flex align-items-center gap-2 flex-wrap"> 
<a href="#" onclick="viewInstructor(<?= (int)$s['instructor_id'] ?>);return false;" class="tag tag-green" style="text-decoration:none;"><i class="fa-solid fa-user me-1"></i>View Instructor</a>
<?php if(!$myS): ?>
<button class="bp" style="font-size:12px;padding:8px 18px;" onclick="openEnrol(<?= (int)$s['schedule_id'] ?>,'<?= htmlspecialchars(addslashes($s['iname'])) ?>','<?= htmlspecialchars(addslashes($s['course_type'])) ?>','<?= $day ?>','<?= schedHoursLabel($s) ?>','<?= schedPriceLabel($s) ?>')"><i class="fa-solid fa-paper-plane me-2"></i>Enrol Now</button>
<?php elseif($myS==='pending'): ?>
<div class="d-flex align-items-center gap-2"><span class="bkp"><i class="fa-solid fa-hourglass-half me-1"></i>Pending</span><form method="POST" action="<?= $selfFile ?>?section=available_schedules" onsubmit="return confirm('Cancel this request?');" style="display:inline;"><input type="hidden" name="action" value="cancel_enrol"><input type="hidden" name="request_id" value="<?= (int)$myR ?>"><button type="submit" class="bds">Cancel</button></form></div>
<?php elseif($myS==='approved'): ?><span class="bka"><i class="fa-solid fa-circle-check me-1"></i>Approved</span>
<?php elseif($myS==='done'): ?><span class="bkd"><i class="fa-solid fa-flag-checkered me-1"></i>Done</span>
<?php endif; ?>
</div>
</div>
<?php endforeach; ?>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>
</div>

<!-- ══ BROWSE INSTRUCTORS ══ -->
<div id="section-instructors" class="section <?= ($activeSection==='instructors')?'active':'' ?>">
<div style="margin-bottom:16px;font-size:13px;color:var(--muted);">Click an instructor card to view their profile, offered courses, available schedules, and booked schedules.</div>
<?php if($allInstructors->num_rows===0): ?>
<div class="empty-state"><i class="fa-solid fa-chalkboard-user"></i><p>No instructors with offered courses right now.</p></div>
<?php else: ?>
<div class="row g-3">
<?php
$acols=['#3d7fff','#10b981','#f59e0b','#c084fc','#f43f5e','#06b6d4']; $ci=0;
while($ins=$allInstructors->fetch_assoc()):
$iW=array_filter(explode(' ',trim($ins['fullname']))); $iIn=strtoupper(substr(implode('',array_map(fn($w)=>$w[0],$iW)),0,2)); $iC=$acols[$ci++%count($acols)];
$cList=$ins['courses']?explode('|',$ins['courses']):[];
$avgR=$ins['avg_rating']?round($ins['avg_rating'],1):0;
$certOk=$ins['certificate_image']&&file_exists($uploadDir.$ins['certificate_image']);
?>
<div class="col-12 col-md-6 col-xl-4">
<div class="instr-card" onclick="viewInstructor(<?= $ins['instructor_id'] ?>)">
<div class="d-flex align-items-center gap-3 mb-3">
<div class="iavt" style="background:linear-gradient(135deg,<?= $iC ?>,<?= $iC ?>99);"><?= $iIn ?></div>
<div style="flex:1;min-width:0;">
<div style="font-weight:700;font-size:15px;"><?= htmlspecialchars($ins['fullname']) ?></div>
<div style="font-size:12px;color:var(--muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($ins['email']) ?></div>
<?php if($certOk): ?><div style="font-size:11px;color:var(--green);margin-top:3px;"><i class="fa-solid fa-certificate me-1"></i>Certified</div><?php endif; ?>
</div>
</div>
<?php if(!empty($cList)): ?>
<div style="margin-bottom:12px;display:flex;flex-wrap:wrap;gap:4px;">
<?php foreach($cList as $ct): $tc=$cColors[$ct]??'tag-blue'; ?><span class="tag <?= $tc ?>" style="font-size:10px;"><?= htmlspecialchars($ct) ?></span><?php endforeach; ?>
</div>
<?php endif; ?>
<div style="display:flex;gap:10px;">
<div style="flex:1;text-align:center;background:var(--surface-3);border-radius:10px;padding:10px;"><div style="font-family:var(--font-display);font-size:18px;font-weight:800;color:var(--accent);"><?= $ins['available_scheds'] ?></div><div style="font-size:11px;color:var(--muted);">Open Slots</div></div>
<div style="flex:1;text-align:center;background:var(--surface-3);border-radius:10px;padding:10px;"><div style="font-family:var(--font-display);font-size:18px;font-weight:800;color:var(--amber);"><?= $ins['offered_courses_count'] ?></div><div style="font-size:11px;color:var(--muted);">Courses</div></div>
<div style="flex:1;text-align:center;background:var(--surface-3);border-radius:10px;padding:10px;"><div style="font-family:var(--font-display);font-size:18px;font-weight:800;"><?= $ins['booked_scheds'] ?></div><div style="font-size:11px;color:var(--muted);">Booked</div></div>
</div>
</div>
</div>
<?php endwhile; ?>
</div>
<?php endif; ?>
</div>

<!-- ══ VIEW INSTRUCTOR PROFILE ══ -->
<div id="section-view_instr" class="section <?= $activeSection==='view_instr'?'active':'' ?>">
<?php if($viewInstr):
$viW=array_filter(explode(' ',trim($viewInstr['fullname']??''))); $viIn=strtoupper(substr(implode('',array_map(fn($w)=>$w[0],$viW)),0,2));
$certOk2=!empty($viewInstr['certificate_image'])&&file_exists($uploadDir.$viewInstr['certificate_image']);
$viAvg=$viewInstr['avg_rating']?round($viewInstr['avg_rating'],1):0;
$schedsArr=[]; $totalSchedCount=0; if($viewScheds) while($vs=$viewScheds->fetch_assoc()){ $grpKey = !empty($vs['slot_date']) ? date('Y-m-d', strtotime($vs['slot_date'])) : ($vs['day_of_week'] ?? 'Other'); $schedsArr[$grpKey][]=$vs; $totalSchedCount++; }
?>
<button class="back-btn" onclick="showSection('instructors')"><i class="fa-solid fa-arrow-left"></i> Back to Instructors</button>
<div class="row g-3">
<div class="col-12 col-lg-4">
<div class="hcard">
<div class="d-flex align-items-center gap-3 mb-3">
<div style="width:60px;height:60px;border-radius:14px;background:linear-gradient(135deg,var(--accent),#1a4fb0);display:grid;place-items:center;font-family:var(--font-display);font-size:20px;font-weight:800;flex-shrink:0;"><?= $viIn ?></div>
<div>
<div style="font-family:var(--font-display);font-size:17px;font-weight:800;"><?= htmlspecialchars($viewInstr['fullname']??'') ?></div>
<div style="font-size:12px;color:var(--muted);margin-top:2px;"><?= htmlspecialchars($viewInstr['email']??'') ?></div>
<div style="margin-top:8px;display:flex;gap:6px;flex-wrap:wrap;">
<span class="tag tag-amber"><i class="fa-solid fa-chalkboard-user me-1"></i>Instructor</span>
<?php if($certOk2): ?><span class="tag tag-green" style="font-size:11px;"><i class="fa-solid fa-certificate me-1"></i>Certified</span><?php endif; ?>
</div>
</div>
</div>
<div class="hdiv"></div>
<div class="row text-center g-0">
<div class="col-4"><div style="font-family:var(--font-display);font-size:20px;font-weight:800;color:var(--amber);"><?= $viAvg?:0 ?></div><div style="font-size:11px;color:var(--muted);">Rating</div></div>
<div class="col-4"><div style="font-family:var(--font-display);font-size:20px;font-weight:800;"><?= (int)($viewInstr['offered_courses_count'] ?? 0) ?></div><div style="font-size:11px;color:var(--muted);">Courses</div></div>
<div class="col-4"><div style="font-family:var(--font-display);font-size:20px;font-weight:800;color:var(--accent);"><?= $totalSchedCount ?></div><div style="font-size:11px;color:var(--muted);">Slots</div></div>
</div>
</div>
<?php if($viewCourses && $viewCourses->num_rows>0): ?>
<div class="panel mb-3">
<div class="panel-head"><div><div class="panel-title"><i class="fa-solid fa-book me-2" style="color:var(--accent);"></i>Offered Courses</div><div class="panel-sub">Pick your own date and send a request to the instructor</div></div></div>
<div style="padding:12px 16px 16px;display:grid;gap:12px;">
<?php while($oc=$viewCourses->fetch_assoc()): $ocTag=$cColors[$oc['course_type']]??'tag-blue'; ?>
<div style="background:var(--surface-2);border:1px solid var(--border);border-radius:12px;padding:14px;">
<div class="d-flex align-items-start justify-content-between gap-2 flex-wrap mb-2">
<div>
<span class="tag <?= $ocTag ?>" style="font-size:10px;"><?= htmlspecialchars($oc['course_type']) ?></span>
<div style="font-weight:700;font-size:14px;margin-top:8px;"><?= htmlspecialchars($oc['course_type']) ?></div>
</div>
<button class="bp" type="button" style="font-size:12px;padding:7px 14px;" onclick="openCourseRequest(<?= (int)$viewInstr['instructor_id'] ?>,<?= (int)$oc['course_id'] ?>,'<?= htmlspecialchars(addslashes($viewInstr['fullname'] ?? '')) ?>','<?= htmlspecialchars(addslashes($oc['course_type'])) ?>','<?= schedPriceLabel($oc) ?>','<?= schedHoursLabel($oc) ?>')"><i class="fa-solid fa-calendar-plus me-2"></i>Request Date</button>
</div>
<div style="display:flex;gap:12px;flex-wrap:wrap;font-size:12px;color:var(--muted);margin-bottom:8px;">
<span><i class="fa-solid fa-hourglass me-1" style="color:var(--accent);"></i><?= schedHoursLabel($oc) ?></span>
<span><i class="fa-solid fa-peso-sign me-1" style="color:var(--green);"></i><?= schedPriceLabel($oc) ?></span>
<span><i class="fa-solid fa-calendar-check me-1"></i><?= (int)$oc['open_scheds'] ?> posted slot<?= (int)$oc['open_scheds']!=1?'s':'' ?></span>
<span><i class="fa-solid fa-user-check me-1"></i><?= (int)$oc['booked_cnt'] ?> booked</span>
</div>
<?php if(!empty($oc['description'])): ?><div style="font-size:12px;color:var(--muted-2);line-height:1.6;"><?= htmlspecialchars($oc['description']) ?></div><?php endif; ?>
</div>
<?php endwhile; ?>
</div>
</div>
<?php endif; ?>
<?php if($certOk2): ?>
<div class="panel mb-3">
<div class="panel-head"><div class="panel-title"><i class="fa-solid fa-certificate me-2" style="color:var(--amber);"></i>Certificate</div></div>
<div style="padding:16px;"><img src="../assets/uploads/certificates/<?= htmlspecialchars($viewInstr['certificate_image']) ?>" class="cert-thumb" onclick="window.open(this.src)" alt="Certificate"></div>
</div>
<?php endif; ?>
</div>
<div class="col-12 col-lg-8">
<div class="panel">
<div class="panel-head"><div><div class="panel-title">Available Schedules</div><div class="panel-sub">Posted slots and approved course dates</div></div></div>
<?php if(empty($schedsArr)): ?>
<div class="empty-state"><i class="fa-solid fa-calendar-week"></i><p>No active schedules yet. You can still request a date from the offered courses list.</p></div>
<?php else: ?>
<div style="padding:16px;">
<?php foreach($schedsArr as $day => $schedGroup): $sample=$schedGroup[0]; $label=!empty($sample['slot_date']) ? date('l, M d, Y', strtotime($sample['slot_date'])) : $day; $colorKey=!empty($sample['slot_date']) ? date('l', strtotime($sample['slot_date'])) : ($sample['day_of_week'] ?? 'Monday'); $dc=$dayColors[$colorKey]??'var(--accent)'; ?>
<div style="margin-bottom:20px;">
<div class="day-pill" style="background:<?= $dc ?>22;color:<?= $dc ?>;border:1px solid <?= $dc ?>44;"><?= $label ?></div>
<?php foreach($schedGroup as $s): $tc=$cColors[$s['course_type']]??'tag-blue'; $myS=$s['my_status']; $myR=$s['my_rid']; $slotsLeft=schedSlotsLeft($s); $isFull=$slotsLeft<=0; ?>
<div class="sched-card">
<div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-2">
<span class="tag <?= $tc ?>" style="font-size:11px;"><?= htmlspecialchars($s['course_type']) ?></span>
<span style="font-size:12px;color:var(--muted);"><i class="fa-solid fa-clock me-1" style="color:var(--accent);"></i><?= schedHoursLabel($s) ?></span>
</div>
<div style="display:flex;gap:14px;flex-wrap:wrap;font-size:12px;color:var(--muted);margin-bottom:10px;">
<span><i class="fa-solid fa-peso-sign me-1" style="color:var(--green);"></i><?= schedPriceLabel($s) ?></span>
<span><i class="fa-solid fa-users me-1"></i><?= (int)$s['enrolled_cnt'] ?>/<?= (int)$s['max_students'] ?> booked</span>
<span style="color:<?= $isFull ? 'var(--red)' : 'var(--green)' ?>;"><i class="fa-solid fa-user-check me-1"></i><?= $isFull ? 'Full slot' : $slotsLeft.' slot'.($slotsLeft!=1?'s':'').' left' ?></span>
</div>
<?php if($s['course_desc']): ?><div style="font-size:12px;color:var(--muted-2);margin-bottom:10px;line-height:1.5;"><?= htmlspecialchars($s['course_desc']) ?></div><?php endif; ?>
<?php if(!$myS && !$isFull): ?>
<button class="bp" style="font-size:12px;padding:8px 18px;" onclick="openEnrol(<?= $s['schedule_id'] ?>,'<?= htmlspecialchars(addslashes($viewInstr['fullname']??'')) ?>','<?= htmlspecialchars(addslashes($s['course_type'])) ?>','<?= addslashes($label) ?>','<?= schedHoursLabel($s) ?>','<?= schedPriceLabel($s) ?>')"><i class="fa-solid fa-paper-plane me-2"></i>Enrol Now</button>
<?php elseif(!$myS && $isFull): ?>
<span class="bkr"><i class="fa-solid fa-ban me-1"></i>Full slot</span>
<?php elseif($myS==='pending'): ?>
<div class="d-flex align-items-center gap-2"><span class="bkp"><i class="fa-solid fa-hourglass-half me-1"></i>Pending</span><form method="POST" action="<?= $selfFile ?>?section=view_instr&view_instr=<?= (int)$viewInstr['instructor_id'] ?>" onsubmit="return confirm('Cancel this request?');" style="display:inline;"><input type="hidden" name="action" value="cancel_enrol"><input type="hidden" name="request_id" value="<?= $myR ?>"><button type="submit" class="bds">Cancel</button></form></div>
<?php elseif($myS==='approved'): ?><span class="bka"><i class="fa-solid fa-circle-check me-1"></i>Approved!</span><?php if(!empty($s['reviewed_at'])): ?><div style="font-size:11px;color:var(--muted);margin-top:6px;">Approved <?= date('M d, Y',strtotime($s['reviewed_at'])) ?></div><?php endif; ?>
<?php elseif($myS==='done'): ?><span class="bkd"><i class="fa-solid fa-flag-checkered me-1"></i>Done</span>
<?php endif; ?>
</div>
<?php endforeach; ?>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>
</div>
</div>
<?php else: ?><div class="empty-state"><i class="fa-solid fa-chalkboard-user"></i><p>Instructor not found.</p></div><?php endif; ?>
</div>

<!-- REQUEST OFFERED COURSE MODAL -->
<div class="modal fade modal-dark" id="courseRequestModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
<div class="modal-header"><h5 class="modal-title"><i class="fa-solid fa-calendar-plus me-2" style="color:var(--accent);"></i>Request Offered Course</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="POST" action="<?= $selfFile ?>?section=view_instr&view_instr=<?= (int)$viewInstrId ?>"><input type="hidden" name="action" value="request_offered_course"><input type="hidden" name="instructor_id" id="reqInstrId"><input type="hidden" name="course_id" id="reqCourseId">
<div class="modal-body">
<div class="sdet" id="courseReqDetails"></div>
<div style="margin-bottom:14px;"><label class="fl">Preferred Date</label><input type="date" name="requested_date" id="reqCourseDate" class="di" min="<?= date('Y-m-d') ?>" required></div>
<div><label class="fl">Note to Instructor (optional)</label><textarea name="student_note" class="di dt" placeholder="e.g. I prefer this date and I am available the whole day."></textarea></div>
</div>
<div class="modal-footer"><button type="button" class="bds" data-bs-dismiss="modal">Cancel</button><button type="submit" class="bp"><i class="fa-solid fa-paper-plane me-2"></i>Send Request</button></div>
</form>
</div></div></div>

<!-- ══ PROFILE ══ -->
<div id="section-profile" class="section <?= $activeSection==='profile'?'active':'' ?>">
<div class="row g-3">
<div class="col-12 col-lg-4">
<div class="hcard">
<div class="d-flex align-items-center gap-3 mb-3">
<div class="havt"><?= $initials ?></div>
<div><div style="font-family:var(--font-display);font-size:17px;font-weight:800;"><?= $fullname ?></div><div style="font-size:12px;color:var(--muted);margin-top:3px;"><?= $email ?></div><div style="margin-top:8px;"><?= $isVerified?'<span class="tag tag-green"><i class="fa-solid fa-check me-1"></i>Verified</span>':'<span class="tag tag-red"><i class="fa-solid fa-clock me-1"></i>Unverified</span>' ?></div></div>
</div>
<div class="hdiv"></div>
<div class="row text-center g-0">
<div class="col-4"><div style="font-family:var(--font-display);font-size:22px;font-weight:800;"><?= $totalReqs ?></div><div style="font-size:11px;color:var(--muted);">Requests</div></div>
<div class="col-4"><div style="font-family:var(--font-display);font-size:22px;font-weight:800;color:var(--green);"><?= $doneCnt ?></div><div style="font-size:11px;color:var(--muted);">Done</div></div>
<div class="col-4"><div style="font-family:var(--font-display);font-size:22px;font-weight:800;color:var(--accent);"><?= $approvedCnt ?></div><div style="font-size:11px;color:var(--muted);">Confirmed</div></div>
</div>
</div>
<div class="panel mb-3">
<div class="panel-head"><div class="panel-title">Account Details</div></div>
<div class="ir"><div class="ii"><i class="fa-solid fa-user"></i></div><div><div class="il">Full Name</div><div class="iv"><?= $fullname ?></div></div></div>
<div class="ir"><div class="ii" style="color:var(--green);"><i class="fa-solid fa-envelope"></i></div><div><div class="il">Email</div><div class="iv"><?= $email ?></div></div></div>
<div class="ir"><div class="ii" style="color:var(--amber);"><i class="fa-solid fa-calendar-plus"></i></div><div><div class="il">Joined</div><div class="iv"><?= $joinDate ?></div></div></div>
<div class="ir"><div class="ii" style="color:<?= $isVerified?'var(--green)':'var(--red)' ?>;"><i class="fa-solid <?= $isVerified?'circle-check':'clock' ?>"></i></div><div><div class="il">Verification</div><div class="iv"><?= $isVerified?'Email Verified':'Not Yet Verified' ?></div></div></div>
<div class="ir"><div class="ii" style="color:var(--red);"><i class="fa-solid fa-clock"></i></div><div><div class="il">Last Login</div><div class="iv"><?= $lastLogin?date('M d, Y g:i A',strtotime($lastLogin['login_datetime'])):'—' ?></div></div></div>
</div>
<div class="panel">
<div class="panel-head"><div class="panel-title">Progress</div></div>
<div style="padding:16px 20px;">
<div class="prog-row"><div class="prog-lbl"><span>Completion Rate</span><strong><?= $totalReqs>0?round($doneCnt/$totalReqs*100):0 ?>%</strong></div><div class="prog-track"><div class="prog-fill" style="width:<?= $totalReqs>0?round($doneCnt/$totalReqs*100):0 ?>%;background:linear-gradient(90deg,var(--green),#5dddb0);"></div></div></div>
<div class="prog-row"><div class="prog-lbl"><span>Approval Rate</span><strong><?= $totalReqs>0?round(($approvedCnt+$doneCnt)/$totalReqs*100):0 ?>%</strong></div><div class="prog-track"><div class="prog-fill" style="width:<?= $totalReqs>0?round(($approvedCnt+$doneCnt)/$totalReqs*100):0 ?>%;background:linear-gradient(90deg,var(--accent),#7daeff);"></div></div></div>
</div>
</div>
</div>
<div class="col-12 col-lg-8">
<div class="panel mb-3">
<div class="panel-head"><div><div class="panel-title"><i class="fa-solid fa-shield-halved me-2" style="color:var(--accent);"></i>Login History</div></div><div style="font-size:12px;color:var(--muted);"><span style="color:var(--green);font-weight:600;"><?= $totalLogins ?></span> · <span style="color:var(--red);font-weight:600;"><?= $failedLogins ?> failed</span></div></div>
<div class="tbl-wrap"><table class="tbl"><thead><tr><th>Date & Time</th><th>Status</th><th>Device</th><th>IP</th></tr></thead><tbody>
<?php if($loginHistory->num_rows===0): ?><tr><td colspan="4" class="ec">No history.</td></tr>
<?php else: while($lg=$loginHistory->fetch_assoc()): $isS=$lg['status']==='success'; ?>
<tr><td><div style="font-weight:600;"><?= date('M d, Y',strtotime($lg['login_datetime'])) ?></div><div style="font-size:11px;color:var(--muted);"><?= date('g:i A',strtotime($lg['login_datetime'])) ?></div></td><td><span class="tag <?= $isS?'tag-green':'tag-red' ?>"><?= ucfirst($lg['status']) ?></span></td><td style="font-size:12px;color:var(--muted);"><?= htmlspecialchars(mb_strimwidth($lg['device_info']??'—',0,40,'…')) ?></td><td style="font-size:12px;color:var(--muted-2);font-family:monospace;"><?= htmlspecialchars($lg['ip_address']??'—') ?></td></tr>
<?php endwhile; endif; ?>
</tbody></table></div>
</div>
<div class="panel">
<div class="panel-head"><div><div class="panel-title"><i class="fa-solid fa-file-lines me-2" style="color:var(--amber);"></i>Full Enrolment Record</div></div></div>
<div class="tbl-wrap"><table class="tbl"><thead><tr><th>#</th><th>Instructor</th><th>Course</th><th>Day</th><th>Status</th></tr></thead><tbody>
<?php
$fullRec=$conn->query("SELECT er.status,i.fullname AS iname,ic.course_type,cs.day_of_week,cs.slot_date FROM enrolment_requests er JOIN course_schedules cs ON cs.schedule_id=er.schedule_id JOIN instructor_courses ic ON ic.course_id=cs.course_id JOIN instructors i ON i.instructor_id=cs.instructor_id WHERE er.student_id=$student_id ORDER BY er.requested_at DESC");
$scm2=['pending'=>'bkp','approved'=>'bka','done'=>'bkd','rejected'=>'bkr','cancelled'=>'bkc'];
if($fullRec->num_rows===0): ?><tr><td colspan="5" class="ec">No records yet.</td></tr>
<?php else: $rn=1; while($r=$fullRec->fetch_assoc()): $sc2=$scm2[$r['status']]??'bkp'; $tc2=$cColors[$r['course_type']]??'tag-blue'; ?>
<tr><td style="color:var(--muted-2);font-size:12px;"><?= $rn++ ?></td><td><strong><?= htmlspecialchars($r['iname']) ?></strong></td><td><span class="tag <?= $tc2 ?>" style="font-size:10px;"><?= htmlspecialchars($r['course_type']) ?></span></td><td><?= !empty($r['slot_date']) ? date('M d, Y', strtotime($r['slot_date'])) : $r['day_of_week'] ?></td><td><span class="<?= $sc2 ?>"><?= ucfirst($r['status']) ?></span></td></tr>
<?php endwhile; endif; ?>
</tbody></table></div>
</div>
</div>
</div>
</div>

<div style="height:32px;"></div>
</div>
</div>
</div>
<!-- ENROL MODAL -->
<div class="modal fade modal-dark" id="enrolModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
<div class="modal-header"><h5 class="modal-title"><i class="fa-solid fa-paper-plane me-2" style="color:var(--accent);"></i>Enrol in Course</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="POST" action="<?= $selfFile ?>?section=schedule"><input type="hidden" name="action" value="enrol"><input type="hidden" name="schedule_id" id="enrolSchId">
<div class="modal-body">
<div class="sdet" id="enrolDetails"></div>
<div><label class="fl">Note to Instructor (optional)</label><textarea name="student_note" class="di dt" placeholder="e.g. I'm a beginner, prefer morning sessions…"></textarea></div>
</div>
<div class="modal-footer"><button type="button" class="bds" data-bs-dismiss="modal">Cancel</button><button type="submit" class="bp"><i class="fa-solid fa-paper-plane me-2"></i>Send Request</button></div>
</form>
</div></div></div>

<!-- RATE MODAL -->
<div class="modal fade modal-dark" id="rateModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
<div class="modal-header"><h5 class="modal-title"><i class="fa-solid fa-star me-2" style="color:var(--amber);"></i>Rate Your Session</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="POST" action="<?= $selfFile ?>?section=schedule"><input type="hidden" name="action" value="submit_rating"><input type="hidden" name="request_id" id="rateReqId">
<div class="modal-body">
<div class="sdet" id="rateDetails" style="margin-bottom:14px;"></div>
<div style="margin-bottom:18px;">
<label class="fl">How was your session?</label>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;" id="ratingCards">
  <div class="rate-card" data-val="1" onclick="selectRating(1)" style="border:2px solid var(--border);border-radius:12px;padding:14px 12px;cursor:pointer;text-align:center;transition:all .2s;background:var(--surface-2);">
    <div style="font-size:22px;margin-bottom:4px;">😞</div>
    <div style="font-weight:700;font-size:13px;color:var(--red);">Bad</div>
    <div style="font-size:11px;color:var(--muted);margin-top:3px;">Very unsatisfied</div>
  </div>
  <div class="rate-card" data-val="2" onclick="selectRating(2)" style="border:2px solid var(--border);border-radius:12px;padding:14px 12px;cursor:pointer;text-align:center;transition:all .2s;background:var(--surface-2);">
    <div style="font-size:22px;margin-bottom:4px;">😐</div>
    <div style="font-weight:700;font-size:13px;color:var(--amber);">Poor</div>
    <div style="font-size:11px;color:var(--muted);margin-top:3px;">Needs improvement</div>
  </div>
  <div class="rate-card" data-val="3" onclick="selectRating(3)" style="border:2px solid var(--border);border-radius:12px;padding:14px 12px;cursor:pointer;text-align:center;transition:all .2s;background:var(--surface-2);">
    <div style="font-size:22px;margin-bottom:4px;">😊</div>
    <div style="font-weight:700;font-size:13px;color:var(--accent);">Good</div>
    <div style="font-size:11px;color:var(--muted);margin-top:3px;">Satisfied overall</div>
  </div>
  <div class="rate-card" data-val="4" onclick="selectRating(4)" style="border:2px solid var(--border);border-radius:12px;padding:14px 12px;cursor:pointer;text-align:center;transition:all .2s;background:var(--surface-2);">
    <div style="font-size:22px;margin-bottom:4px;">🤩</div>
    <div style="font-weight:700;font-size:13px;color:var(--green);">Excellent</div>
    <div style="font-size:11px;color:var(--muted);margin-top:3px;">Highly recommend!</div>
  </div>
</div>
<input type="hidden" name="rating" id="ratingVal" value="0">
<div id="ratingError" style="font-size:12px;color:var(--red);margin-top:8px;display:none;"><i class="fa-solid fa-circle-exclamation me-1"></i>Please select a rating option.</div>
</div>
<div><label class="fl">Additional Comments <span style="font-weight:400;color:var(--muted);">(optional)</span></label><textarea name="description" class="di dt" placeholder="Share more about your experience…" style="min-height:72px;"></textarea></div>
</div>
<div class="modal-footer"><button type="button" class="bds" data-bs-dismiss="modal">Cancel</button><button type="submit" class="bp" onclick="return validateRating()"><i class="fa-solid fa-paper-plane me-2"></i>Submit Rating</button></div>
</form>
</div></div></div>
<style>
.rate-card:hover{border-color:var(--accent)!important;background:rgba(61,127,255,.07)!important;}
.rate-card.selected[data-val="1"]{border-color:var(--red)!important;background:rgba(244,63,94,.1)!important;}
.rate-card.selected[data-val="2"]{border-color:var(--amber)!important;background:rgba(245,158,11,.1)!important;}
.rate-card.selected[data-val="3"]{border-color:var(--accent)!important;background:rgba(61,127,255,.1)!important;}
.rate-card.selected[data-val="4"]{border-color:var(--green)!important;background:rgba(16,185,129,.1)!important;}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
const T={dashboard:'Overview',schedule:'My Schedule',available_schedules:'Available Schedules',progress:'My Progress',messages:'Messages',instructors:'Browse Instructors',view_instr:'Instructor Profile',profile:'My Profile'};
function showSection(n){document.querySelectorAll('.section').forEach(s=>s.classList.remove('active'));document.querySelectorAll('.nav-item').forEach(x=>x.classList.remove('active'));const el=document.getElementById('section-'+n);if(el)el.classList.add('active');document.getElementById('page-title-text').textContent=T[n]||'Dashboard';document.querySelectorAll('.nav-item').forEach(x=>{if(x.getAttribute('onclick')&&x.getAttribute('onclick').includes("'"+n+"'"))x.classList.add('active');});const u=new URL(window.location.href);u.searchParams.set('section',n);if(n!=='view_instr')u.searchParams.delete('view_instr');window.history.pushState({},'',u);}
function viewInstructor(id){window.location.href='<?= $selfFile ?>?view_instr='+id+'&section=view_instr';}
function openSidebar(){document.getElementById('sidebar').classList.add('open');document.getElementById('sidebarOverlay').classList.add('show');}
function closeSidebar(){document.getElementById('sidebar').classList.remove('open');document.getElementById('sidebarOverlay').classList.remove('show');}
function handleSearch(v){const a=document.querySelector('.section.active');if(!a)return;a.querySelectorAll('.instr-card,.sched-card,.bk-card').forEach(c=>{c.style.display=c.textContent.toLowerCase().includes(v.toLowerCase())?'':'none';});a.querySelectorAll('.tbl tbody tr').forEach(r=>{r.style.display=r.textContent.toLowerCase().includes(v.toLowerCase())?'':'none';});}
function filterBk(s,btn){document.querySelectorAll('#section-my_bookings .tab').forEach(b=>b.classList.remove('active'));if(btn)btn.classList.add('active');else{const el=document.getElementById('bk-'+s);if(el)el.classList.add('active');}document.querySelectorAll('.bk-item').forEach(i=>{i.style.display=(s==='all'||i.dataset.status===s)?'':'none';});}
function openEnrol(id,instr,course,day,time,price){
  document.getElementById('enrolSchId').value=id;
  document.getElementById('enrolDetails').innerHTML=
  '<div class="sdet-row"><i class="fa-solid fa-chalkboard-user" style="color:var(--accent);width:16px;flex-shrink:0;"></i><div><div style="font-size:11px;color:var(--muted-2);text-transform:uppercase;letter-spacing:.05em;">Instructor</div><div style="font-weight:600;font-size:14px;">'+instr+'</div></div></div>'+
  '<div class="sdet-row"><i class="fa-solid fa-book" style="color:#c084fc;width:16px;flex-shrink:0;"></i><div><div style="font-size:11px;color:var(--muted-2);text-transform:uppercase;letter-spacing:.05em;">Course</div><div style="font-weight:600;font-size:14px;">'+course+'</div></div></div>'+
  '<div class="sdet-row"><i class="fa-solid fa-calendar-week" style="color:var(--amber);width:16px;flex-shrink:0;"></i><div><div style="font-size:11px;color:var(--muted-2);text-transform:uppercase;letter-spacing:.05em;">Schedule</div><div style="font-weight:600;font-size:14px;">'+day+' · '+time+'</div></div></div>'+
  '<div class="sdet-row"><i class="fa-solid fa-peso-sign" style="color:var(--green);width:16px;flex-shrink:0;"></i><div><div style="font-size:11px;color:var(--muted-2);text-transform:uppercase;letter-spacing:.05em;">Price</div><div style="font-weight:600;font-size:14px;">'+price+'</div></div></div>';
  new bootstrap.Modal(document.getElementById('enrolModal')).show();
}
function openCourseRequest(instrId,courseId,instr,course,price,hours){
  document.getElementById('reqInstrId').value=instrId;
  document.getElementById('reqCourseId').value=courseId;
  const d=document.getElementById('reqCourseDate'); if(d) d.value='';
  document.getElementById('courseReqDetails').innerHTML=
  '<div class="sdet-row"><i class="fa-solid fa-chalkboard-user" style="color:var(--accent);width:16px;flex-shrink:0;"></i><div><div style="font-size:11px;color:var(--muted-2);text-transform:uppercase;letter-spacing:.05em;">Instructor</div><div style="font-weight:600;font-size:14px;">'+instr+'</div></div></div>'+
  '<div class="sdet-row"><i class="fa-solid fa-book" style="color:#c084fc;width:16px;flex-shrink:0;"></i><div><div style="font-size:11px;color:var(--muted-2);text-transform:uppercase;letter-spacing:.05em;">Course</div><div style="font-weight:600;font-size:14px;">'+course+'</div></div></div>'+
  '<div class="sdet-row"><i class="fa-solid fa-hourglass" style="color:var(--amber);width:16px;flex-shrink:0;"></i><div><div style="font-size:11px;color:var(--muted-2);text-transform:uppercase;letter-spacing:.05em;">Duration</div><div style="font-weight:600;font-size:14px;">'+hours+'</div></div></div>'+
  '<div class="sdet-row"><i class="fa-solid fa-peso-sign" style="color:var(--green);width:16px;flex-shrink:0;"></i><div><div style="font-size:11px;color:var(--muted-2);text-transform:uppercase;letter-spacing:.05em;">Price</div><div style="font-weight:600;font-size:14px;">'+price+'</div></div></div>';
  new bootstrap.Modal(document.getElementById('courseRequestModal')).show();
}

function openRate(rid,instr,course){
  document.getElementById('rateReqId').value=rid;
  document.getElementById('ratingVal').value=0;
  document.getElementById('ratingError').style.display='none';
  document.querySelectorAll('#ratingCards .rate-card').forEach(c=>c.classList.remove('selected'));
  document.getElementById('rateDetails').innerHTML=
  '<div class="sdet-row"><i class="fa-solid fa-chalkboard-user" style="color:var(--accent);width:16px;flex-shrink:0;"></i><div><div style="font-size:11px;color:var(--muted-2);text-transform:uppercase;letter-spacing:.05em;">Instructor</div><div style="font-weight:600;font-size:14px;">'+instr+'</div></div></div>'+
  '<div class="sdet-row"><i class="fa-solid fa-book" style="color:#c084fc;width:16px;flex-shrink:0;"></i><div><div style="font-size:11px;color:var(--muted-2);text-transform:uppercase;letter-spacing:.05em;">Course Booked</div><div style="font-weight:600;font-size:14px;">'+course+'</div></div></div>';
  new bootstrap.Modal(document.getElementById('rateModal')).show();
}
function selectRating(v){
  document.getElementById('ratingVal').value=v;
  document.getElementById('ratingError').style.display='none';
  document.querySelectorAll('#ratingCards .rate-card').forEach(c=>{c.classList.toggle('selected',parseInt(c.dataset.val)===v);});
}
function validateRating(){
  if(!parseInt(document.getElementById('ratingVal').value)){
    document.getElementById('ratingError').style.display='block';
    return false;
  }
  return true;
}
function markReadS(){fetch('<?= $selfFile ?>',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'action=mark_read_student'});}
</script>
</body>
</html>
