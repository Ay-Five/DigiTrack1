<?php
$instructor_id = isset($_SESSION['instructor_id']) ? (int)$_SESSION['instructor_id'] : 1;
$uploadDir = '../assets/uploads/certificates/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$successMsg = ''; $errorMsg = '';

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
function schedBookedCount(array $row): int {
    return (int)($row['pc'] ?? 0) + (int)($row['ac'] ?? 0) + (int)($row['dc'] ?? 0);
}

function notifyStudent($conn, $sid, $msg, $type) {
    $m = $conn->real_escape_string($msg);
    $conn->query("INSERT INTO notifications(user_id,user_type,message,type,is_read)VALUES($sid,'student','$m','$type',0)");
}

// ── COURSE ACTIONS ────────────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'add_course') {
    $ct    = $conn->real_escape_string($_POST['course_type'] ?? '');
    $desc  = $conn->real_escape_string(trim($_POST['description'] ?? ''));
    $dur   = ($_POST['duration_hrs'] ?? '') !== '' ? (float)$_POST['duration_hrs'] : 'NULL';
    $price = ($_POST['price']        ?? '') !== '' ? (float)$_POST['price']        : 'NULL';
    if (!$ct) { $errorMsg = 'Please select a course type.'; }
    else {
        $ex = $conn->query("SELECT course_id FROM instructor_courses WHERE instructor_id=$instructor_id AND course_type='$ct'")->fetch_assoc();
        if ($ex) $errorMsg = 'You already have this course listed.';
        else { $conn->query("INSERT INTO instructor_courses(instructor_id,course_type,description,duration_hrs,price)VALUES($instructor_id,'$ct','$desc',$dur,$price)"); $successMsg = 'Course added!'; }
    }
}
if (isset($_POST['action']) && $_POST['action'] === 'update_course') {
    $cid   = (int)$_POST['course_id'];
    $desc  = $conn->real_escape_string(trim($_POST['uc_description'] ?? ''));
    $dur   = ($_POST['uc_duration_hrs'] ?? '') !== '' ? (float)$_POST['uc_duration_hrs'] : 'NULL';
    $price = ($_POST['uc_price']        ?? '') !== '' ? (float)$_POST['uc_price']        : 'NULL';
    $conn->query("UPDATE instructor_courses SET description='$desc',duration_hrs=$dur,price=$price WHERE course_id=$cid AND instructor_id=$instructor_id");
    $successMsg = 'Course updated.';
}
if (isset($_POST['action']) && $_POST['action'] === 'delete_course') {
    $cid = (int)$_POST['course_id'];
    $conn->query("DELETE FROM instructor_courses WHERE course_id=$cid AND instructor_id=$instructor_id");
    $successMsg = 'Course removed.';
}

// ── SCHEDULE ACTIONS ──────────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'add_schedule') {
    $cid        = (int)($_POST['course_id'] ?? 0);
    $slotDate   = $conn->real_escape_string($_POST['slot_date'] ?? '');
    $maxs       = (int)($_POST['max_students'] ?? 5);
    $notes      = $conn->real_escape_string(trim($_POST['notes'] ?? ''));
    $slot_price = ($_POST['slot_price'] ?? '') !== '' ? (float)$_POST['slot_price'] : 'NULL';
    $dayNum     = $slotDate ? (int)date('N', strtotime($slotDate)) : 0;
    $day        = $slotDate ? $conn->real_escape_string(date('l', strtotime($slotDate))) : '';
    $ctRow      = $cid ? $conn->query("SELECT course_type FROM instructor_courses WHERE course_id=$cid")->fetch_assoc() : null;
    $ctStr      = $ctRow['course_type'] ?? '';
    $slotHrs    = (strpos($ctStr,'TDC')===0) ? 15 : 8;
    if (!$cid || !$slotDate) { $errorMsg = 'Please fill in all required fields.'; }
    elseif ($dayNum === 7) { $errorMsg = 'Sunday is not available. Please pick Monday to Saturday.'; }
    else {
        $conn->query("INSERT INTO course_schedules(instructor_id,course_id,day_of_week,slot_date,slot_hrs,slot_price,max_students,notes)VALUES($instructor_id,$cid,'$day','$slotDate',$slotHrs,$slot_price,$maxs,'$notes')");
        $successMsg = 'Schedule posted!';
    }
}
if (isset($_POST['action']) && $_POST['action'] === 'edit_schedule') {
    $schid      = (int)$_POST['schedule_id'];
    $maxs       = (int)($_POST['max_students'] ?? 5);
    $notes      = $conn->real_escape_string(trim($_POST['notes'] ?? ''));
    $slot_price = ($_POST['slot_price'] ?? '') !== '' ? (float)$_POST['slot_price'] : 'NULL';
    $conn->query("UPDATE course_schedules SET max_students=$maxs,notes='$notes',slot_price=$slot_price WHERE schedule_id=$schid AND instructor_id=$instructor_id");
    $successMsg = 'Schedule updated.';
}
if (isset($_POST['action']) && $_POST['action'] === 'toggle_schedule') {
    $schid = (int)$_POST['schedule_id'];
    $conn->query("UPDATE course_schedules SET is_active=!is_active WHERE schedule_id=$schid AND instructor_id=$instructor_id");
    $successMsg = 'Schedule status updated.';
}
if (isset($_POST['action']) && $_POST['action'] === 'delete_schedule') {
    $schid = (int)$_POST['schedule_id'];
    $conn->query("DELETE FROM course_schedules WHERE schedule_id=$schid AND instructor_id=$instructor_id");
    $successMsg = 'Schedule removed.';
}

// ── ENROLMENT ACTIONS ─────────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'approve_request') {
    $rid  = (int)($_POST['request_id'] ?? 0);
    $note = trim($_POST['instructor_note'] ?? '');

    $conn->begin_transaction();

    try {
        $reqSql = "
            SELECT 
                er.*,
                cs.day_of_week,
                cs.slot_date,
                cs.slot_hrs,
                cs.max_students,
                cs.instructor_id,
                ic.course_type
            FROM enrolment_requests er
            JOIN course_schedules cs 
                ON cs.schedule_id = er.schedule_id
            JOIN instructor_courses ic 
                ON ic.course_id = cs.course_id
            WHERE er.request_id = $rid
            LIMIT 1
            FOR UPDATE
        ";
        $reqRes = $conn->query($reqSql);
        $req = $reqRes ? $reqRes->fetch_assoc() : null;

        if (!$req) {
            throw new Exception('Enrolment request not found.');
        }

        if (($req['status'] ?? '') !== 'pending') {
            throw new Exception('Only pending requests can be approved.');
        }

        $countSql = "
            SELECT COUNT(*) AS c
            FROM enrolment_requests
            WHERE schedule_id = {$req['schedule_id']}
              AND status = 'approved'
            FOR UPDATE
        ";
        $countRes = $conn->query($countSql);
        $activeCount = (int)(($countRes && $countRes->fetch_assoc())['c'] ?? 0);

        $maxStudents = (int)($req['max_students'] ?? 0);

        if ($maxStudents > 0 && $activeCount >= $maxStudents) {
            throw new Exception('This schedule is already booked or full.');
        }

        $noteEsc = $conn->real_escape_string($note);

        $updateSql = "
            UPDATE enrolment_requests
            SET 
                status = 'approved',
                instructor_note = '$noteEsc',
                reviewed_at = NOW()
            WHERE request_id = $rid
              AND status = 'pending'
        ";
        if (!$conn->query($updateSql)) {
            throw new Exception('Failed to approve request.');
        }

        $conn->query("
            UPDATE course_schedules
            SET is_active = 1
            WHERE schedule_id = {$req['schedule_id']}
              AND instructor_id = $instructor_id
        ");

        $iname = $fullname ?? 'Your instructor';
        $slotHrsNotif = (int)($req['slot_hrs'] ?? 0);
        $slotDateNotif = !empty($req['slot_date'])
            ? date('M d, Y', strtotime($req['slot_date']))
            : $req['day_of_week'];

        $message = "Your enrolment for {$req['course_type']} ({$slotDateNotif}, {$slotHrsNotif} hrs) with Instructor {$iname} has been APPROVED!";
        if ($note !== '') {
            $message .= " Note: $note";
        }

        notifyStudent(
            $conn,
            $req['student_id'],
            $message,
            'enrolment_approved'
        );

        $conn->commit();
        $successMsg = 'Request approved. Student notified.';
    } catch (Exception $e) {
        $conn->rollback();
        $errorMsg = $e->getMessage();
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'reject_request') {
    $rid  = (int)$_POST['request_id'];
    $note = $conn->real_escape_string(trim($_POST['instructor_note'] ?? ''));
    $req  = $conn->query("SELECT er.*,ic.course_type FROM enrolment_requests er JOIN course_schedules cs ON cs.schedule_id=er.schedule_id JOIN instructor_courses ic ON ic.course_id=cs.course_id WHERE er.request_id=$rid")->fetch_assoc();
    if ($req) {
        $conn->query("UPDATE enrolment_requests SET status='rejected',instructor_note='$note',reviewed_at=NOW() WHERE request_id=$rid");
        $iname = $conn->real_escape_string($fullname ?? 'Your instructor');
        notifyStudent($conn, $req['student_id'], "Your enrolment for {$req['course_type']} with Instructor $iname was not approved." . ($note ? " Reason: $note" : ''), 'enrolment_rejected');
        $successMsg = 'Request rejected.';
    }
}
if (isset($_POST['action']) && $_POST['action'] === 'mark_done') {
    $rid = (int)$_POST['request_id'];
    $conn->query("UPDATE enrolment_requests SET status='done',done_at=NOW() WHERE request_id=$rid");
    $req = $conn->query("SELECT er.student_id,ic.course_type FROM enrolment_requests er JOIN course_schedules cs ON cs.schedule_id=er.schedule_id JOIN instructor_courses ic ON ic.course_id=cs.course_id WHERE er.request_id=$rid")->fetch_assoc();
    if ($req) notifyStudent($conn, $req['student_id'], "Your session for {$req['course_type']} is done! Please rate your experience.", 'session_done');
    $successMsg = 'Session marked as done!';
}
if (isset($_POST['action']) && $_POST['action'] === 'mark_all_read') {
    $conn->query("UPDATE notifications SET is_read=1 WHERE user_id=$instructor_id AND user_type='instructor'");
}

// ── SAVE INSTRUCTOR NOTE ON A STUDENT SESSION ─────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'save_note') {
    $rid  = (int)($_POST['request_id'] ?? 0);
    $note = $conn->real_escape_string(trim($_POST['instructor_note'] ?? ''));
    $conn->query("UPDATE enrolment_requests er JOIN course_schedules cs ON cs.schedule_id=er.schedule_id SET er.instructor_note='$note' WHERE er.request_id=$rid AND cs.instructor_id=$instructor_id");
    $successMsg = 'Note saved.';
    $activeSection = 'students';
}

// ── POST ANNOUNCEMENT TO ALL ENROLLED STUDENTS ────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'post_announcement') {
    $msg = $conn->real_escape_string(trim($_POST['announcement'] ?? ''));
    if (!$msg) { $errorMsg = 'Please enter an announcement.'; }
    else {
        $sids = $conn->query("SELECT DISTINCT er.student_id FROM enrolment_requests er JOIN course_schedules cs ON cs.schedule_id=er.schedule_id WHERE cs.instructor_id=$instructor_id AND er.status IN('approved','pending')");
        $sent = 0;
        while ($s = $sids->fetch_assoc()) {
            notifyStudent($conn, $s['student_id'], "📢 Announcement from Instructor $fullname: $msg", 'announcement');
            $sent++;
        }
        $successMsg = $sent > 0 ? "Announcement sent to $sent student".($sent!=1?'s':'')."." : 'No enrolled students to notify.';
    }
    $activeSection = 'communication';
}

// ── SEND DIRECT MESSAGE TO A STUDENT ─────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'send_message') {
    $sid = (int)($_POST['student_id'] ?? 0);
    $msg = $conn->real_escape_string(trim($_POST['message'] ?? ''));
    if (!$sid || !$msg) { $errorMsg = 'Please select a student and write a message.'; }
    else {
        $check = $conn->query("SELECT er.student_id FROM enrolment_requests er JOIN course_schedules cs ON cs.schedule_id=er.schedule_id WHERE cs.instructor_id=$instructor_id AND er.student_id=$sid AND er.status IN('approved','pending') LIMIT 1")->fetch_assoc();
        if (!$check) { $errorMsg = 'Student not found in your enrolled list.'; }
        else { notifyStudent($conn, $sid, "💬 Message from Instructor $fullname: $msg", 'message'); $successMsg = 'Message sent!'; }
    }
    $activeSection = 'communication';
}

// ── CERTIFICATE ───────────────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'upload_certificate') {
    if (empty($_FILES['certificate']['name'])) { $errorMsg = 'Please select an image.'; }
    else {
        $file = $_FILES['certificate']; $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','webp'])) $errorMsg = 'Only JPG/PNG/WEBP.';
        elseif ($file['size'] > 5*1024*1024) $errorMsg = 'Max 5MB.';
        elseif ($file['error'] !== UPLOAD_ERR_OK) $errorMsg = 'Upload failed.';
        else {
            $old = $conn->query("SELECT certificate_image FROM instructors WHERE instructor_id=$instructor_id")->fetch_assoc();
            if (!empty($old['certificate_image']) && file_exists($uploadDir.$old['certificate_image'])) unlink($uploadDir.$old['certificate_image']);
            $fn = 'cert_'.$instructor_id.'_'.time().'.'.$ext;
            if (move_uploaded_file($file['tmp_name'], $uploadDir.$fn)) { $conn->query("UPDATE instructors SET certificate_image='".addslashes($fn)."',modified_date=NOW() WHERE instructor_id=$instructor_id"); $successMsg = 'Certificate uploaded.'; }
            else $errorMsg = 'Failed to save.';
        }
    }
}
if (isset($_POST['action']) && $_POST['action'] === 'delete_certificate') {
    $old = $conn->query("SELECT certificate_image FROM instructors WHERE instructor_id=$instructor_id")->fetch_assoc();
    if (!empty($old['certificate_image']) && file_exists($uploadDir.$old['certificate_image'])) unlink($uploadDir.$old['certificate_image']);
    $conn->query("UPDATE instructors SET certificate_image=NULL,modified_date=NOW() WHERE instructor_id=$instructor_id");
    $successMsg = 'Certificate removed.';
}

// ── FETCH DATA ────────────────────────────────────────────────────
$instrRow    = $conn->query("SELECT * FROM instructors WHERE instructor_id=$instructor_id")->fetch_assoc();
$fullname    = htmlspecialchars($instrRow['fullname']     ?? 'Instructor');
$email       = htmlspecialchars($instrRow['email']        ?? '');
$certImage   = $instrRow['certificate_image']             ?? null;
$joinDate    = isset($instrRow['created_at'])     ? date('F d, Y', strtotime($instrRow['created_at']))                    : '—';
$modified    = !empty($instrRow['modified_date']) ? date('M d, Y g:i A', strtotime($instrRow['modified_date']))           : 'Never';
$firstName   = explode(' ', trim($fullname))[0];
$iWords      = array_filter(explode(' ', trim($fullname)));
$initials    = strtoupper(substr(implode('', array_map(fn($w) => $w[0], $iWords)), 0, 2));

$totalStudents = $conn->query("SELECT COUNT(DISTINCT er.student_id) AS c FROM enrolment_requests er JOIN course_schedules cs ON cs.schedule_id=er.schedule_id WHERE cs.instructor_id=$instructor_id AND er.status IN('approved','done')")->fetch_assoc()['c'];
$pendingReqs   = $conn->query("SELECT COUNT(*) AS c FROM enrolment_requests er JOIN course_schedules cs ON cs.schedule_id=er.schedule_id WHERE cs.instructor_id=$instructor_id AND er.status='pending'")->fetch_assoc()['c'];
$doneSessions  = $conn->query("SELECT COUNT(*) AS c FROM enrolment_requests er JOIN course_schedules cs ON cs.schedule_id=er.schedule_id WHERE cs.instructor_id=$instructor_id AND er.status='done'")->fetch_assoc()['c'];
$avgRating     = $conn->query("SELECT ROUND(AVG(rating),1) AS a FROM session_ratings WHERE instructor_id=$instructor_id")->fetch_assoc()['a'] ?? 0;
$activeScheds  = $conn->query("SELECT COUNT(*) AS c FROM course_schedules WHERE instructor_id=$instructor_id AND is_active=1")->fetch_assoc()['c'];
$totalRatings  = $conn->query("SELECT COUNT(*) AS c FROM session_ratings WHERE instructor_id=$instructor_id")->fetch_assoc()['c'];
$fiveStars     = $conn->query("SELECT COUNT(*) AS c FROM session_ratings WHERE instructor_id=$instructor_id AND rating=5")->fetch_assoc()['c'];

$myCourses     = $conn->query("SELECT ic.*,(SELECT COUNT(*) FROM course_schedules cs WHERE cs.course_id=ic.course_id AND cs.instructor_id=$instructor_id AND cs.is_active=1) AS posted_scheds FROM instructor_courses ic WHERE ic.instructor_id=$instructor_id ORDER BY ic.course_type ASC");
$mySchedules   = $conn->query("SELECT cs.*,ic.course_type,ic.price,ic.duration_hrs,(SELECT COUNT(*) FROM enrolment_requests WHERE schedule_id=cs.schedule_id AND status='pending') AS pc,(SELECT COUNT(*) FROM enrolment_requests WHERE schedule_id=cs.schedule_id AND status='approved') AS ac,(SELECT COUNT(*) FROM enrolment_requests WHERE schedule_id=cs.schedule_id AND status='done') AS dc FROM course_schedules cs JOIN instructor_courses ic ON ic.course_id=cs.course_id WHERE cs.instructor_id=$instructor_id ORDER BY COALESCE(cs.slot_date,'9999-12-31') ASC, cs.schedule_id ASC");
$todayDay      = date('l');
$todayScheds   = $conn->query("SELECT cs.*,ic.course_type,ic.price,ic.duration_hrs,(SELECT COUNT(*) FROM enrolment_requests WHERE schedule_id=cs.schedule_id AND status='pending') AS pc,(SELECT COUNT(*) FROM enrolment_requests WHERE schedule_id=cs.schedule_id AND status='approved') AS ac,(SELECT COUNT(*) FROM enrolment_requests WHERE schedule_id=cs.schedule_id AND status='done') AS dc FROM course_schedules cs JOIN instructor_courses ic ON ic.course_id=cs.course_id WHERE cs.instructor_id=$instructor_id AND cs.day_of_week='$todayDay' AND cs.is_active=1 ORDER BY COALESCE(cs.slot_date,'9999-12-31') ASC");
$enrolReqs     = $conn->query("SELECT er.*,cs.day_of_week,cs.slot_date,cs.slot_hrs,cs.slot_price,cs.max_students,ic.course_type,ic.price,st.fullname AS sname,st.email AS semail,st.student_id FROM enrolment_requests er JOIN course_schedules cs ON cs.schedule_id=er.schedule_id JOIN instructor_courses ic ON ic.course_id=cs.course_id JOIN students st ON st.student_id=er.student_id WHERE cs.instructor_id=$instructor_id ORDER BY FIELD(er.status,'pending','approved','done','rejected','cancelled'),COALESCE(cs.slot_date,'9999-12-31') ASC,er.requested_at DESC");
$ratings       = $conn->query("SELECT sr.*,st.fullname AS sname,ic.course_type FROM session_ratings sr JOIN enrolment_requests er ON er.request_id=sr.request_id JOIN course_schedules cs ON cs.schedule_id=er.schedule_id JOIN instructor_courses ic ON ic.course_id=cs.course_id JOIN students st ON st.student_id=sr.student_id WHERE sr.instructor_id=$instructor_id ORDER BY sr.created_at DESC LIMIT 20");
$loginHistory  = $conn->query("SELECT * FROM instructor_login_history WHERE instructor_id=$instructor_id ORDER BY login_time DESC LIMIT 8");
$totalLogins   = $conn->query("SELECT COUNT(*) AS c FROM instructor_login_history WHERE instructor_id=$instructor_id AND login_status='success'")->fetch_assoc()['c'];
$failedLogins  = $conn->query("SELECT COUNT(*) AS c FROM instructor_login_history WHERE instructor_id=$instructor_id AND login_status='failed'")->fetch_assoc()['c'];
$lastLogin     = $conn->query("SELECT login_time,city FROM instructor_login_history WHERE instructor_id=$instructor_id AND login_status='success' ORDER BY login_time DESC LIMIT 1")->fetch_assoc();
$myNotifs      = $conn->query("SELECT * FROM notifications WHERE user_id=$instructor_id AND user_type='instructor' ORDER BY created_at DESC LIMIT 20");
$unreadNotifs  = $conn->query("SELECT COUNT(*) AS c FROM notifications WHERE user_id=$instructor_id AND user_type='instructor' AND is_read=0")->fetch_assoc()['c'];

// ── ALL ENROLLED STUDENTS ─────────────────────────────────────────
$allStudents = $conn->query("SELECT DISTINCT st.student_id,st.fullname,st.email,st.is_verified,(SELECT COUNT(*) FROM enrolment_requests er2 JOIN course_schedules cs2 ON cs2.schedule_id=er2.schedule_id WHERE cs2.instructor_id=$instructor_id AND er2.student_id=st.student_id AND er2.status IN('approved','pending','done')) AS session_count,(SELECT COUNT(*) FROM enrolment_requests er3 JOIN course_schedules cs3 ON cs3.schedule_id=er3.schedule_id WHERE cs3.instructor_id=$instructor_id AND er3.student_id=st.student_id AND er3.status='done') AS done_count,(SELECT er4.request_id FROM enrolment_requests er4 JOIN course_schedules cs4 ON cs4.schedule_id=er4.schedule_id WHERE cs4.instructor_id=$instructor_id AND er4.student_id=st.student_id ORDER BY er4.requested_at DESC LIMIT 1) AS latest_rid,(SELECT er5.instructor_note FROM enrolment_requests er5 JOIN course_schedules cs5 ON cs5.schedule_id=er5.schedule_id WHERE cs5.instructor_id=$instructor_id AND er5.student_id=st.student_id ORDER BY er5.requested_at DESC LIMIT 1) AS latest_note FROM students st WHERE EXISTS(SELECT 1 FROM enrolment_requests er JOIN course_schedules cs ON cs.schedule_id=er.schedule_id WHERE cs.instructor_id=$instructor_id AND er.student_id=st.student_id AND er.status IN('approved','pending','done')) ORDER BY st.fullname ASC");

// ── COMPLETED SESSIONS FOR PERFORMANCE ───────────────────────────
$completedSessions = $conn->query("SELECT er.*,cs.day_of_week,cs.slot_date,cs.slot_hrs,ic.course_type,st.fullname AS sname,st.student_id,(SELECT rating FROM session_ratings WHERE request_id=er.request_id LIMIT 1) AS student_rating,(SELECT description FROM session_ratings WHERE request_id=er.request_id LIMIT 1) AS student_review FROM enrolment_requests er JOIN course_schedules cs ON cs.schedule_id=er.schedule_id JOIN instructor_courses ic ON ic.course_id=cs.course_id JOIN students st ON st.student_id=er.student_id WHERE cs.instructor_id=$instructor_id AND er.status='done' ORDER BY er.done_at DESC");

// ── ENROLLED STUDENTS FOR MESSAGE DROPDOWN ────────────────────────
$enrolledForMsg = $conn->query("SELECT DISTINCT st.student_id,st.fullname FROM students st JOIN enrolment_requests er ON er.student_id=st.student_id JOIN course_schedules cs ON cs.schedule_id=er.schedule_id WHERE cs.instructor_id=$instructor_id AND er.status IN('approved','pending') ORDER BY st.fullname ASC");

$activeSection = isset($_GET['section']) ? $_GET['section'] : 'dashboard';
$p2s = ['add_course'=>'courses','update_course'=>'courses','delete_course'=>'courses','add_schedule'=>'schedule','edit_schedule'=>'schedule','toggle_schedule'=>'schedule','delete_schedule'=>'schedule','approve_request'=>'requests','reject_request'=>'requests','mark_done'=>'performance','save_note'=>'students','post_announcement'=>'communication','send_message'=>'communication','upload_certificate'=>'profile','delete_certificate'=>'profile','mark_all_read'=>'notifications'];
if (isset($_POST['action']) && isset($p2s[$_POST['action']])) $activeSection = $p2s[$_POST['action']];

$today     = date('Y-m-d'); $dayName = date('l'); $monthYear = date('F Y');
$days      = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
$cColors   = ['TDC Face-to-Face'=>'tag-blue','TDC Online'=>'tag-green','PDC Car'=>'tag-amber','PDC Motorcycle'=>'tag-purple'];
$dayColors = ['Monday'=>'#3d7fff','Tuesday'=>'#10b981','Wednesday'=>'#f59e0b','Thursday'=>'#c084fc','Friday'=>'#f43f5e','Saturday'=>'#06b6d4'];
?>