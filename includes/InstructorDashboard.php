<?php
require_once __DIR__ . '/BaseDashboard.php';
require_once __DIR__ . '/Notifiable.php';

/**
 * InstructorDashboard.php
 *
 * INHERITANCE   → extends BaseDashboard (gets db, userId, redirect, notify, etc.)
 * ABSTRACTION   → fulfills handlePost() and loadData() contracts
 * POLYMORPHISM  → implements Notifiable (same interface as Admin and Student)
 * ENCAPSULATION → private action methods; instructor-specific logic hidden inside
 */
class InstructorDashboard extends BaseDashboard implements Notifiable
{
    private const SELF_FILE = 'instructor_dashboard.php';
    private const USER_TYPE = 'instructor';

    public function __construct(Database $db, int $instructorId)
    {
        parent::__construct($db, $instructorId);
    }

    // ── Notifiable interface (POLYMORPHISM) ───────────────────────────────

    public function getUnreadCount(): int
    {
        return $this->db->count(
            "SELECT COUNT(*) AS c FROM notifications
             WHERE user_id={$this->userId} AND user_type='instructor' AND is_read=0"
        );
    }

    public function markNotificationsRead(): void
    {
        $this->db->query(
            "UPDATE notifications SET is_read=1
             WHERE user_id={$this->userId} AND user_type='instructor'"
        );
    }

    public function getNotifications(int $limit = 30): array
    {
        return $this->db->rows(
            "SELECT * FROM notifications
             WHERE user_id={$this->userId} AND user_type='instructor'
             ORDER BY created_at DESC LIMIT $limit"
        );
    }

    // ── Abstract method: handlePost (ABSTRACTION fulfilled) ───────────────

    public function handlePost(string $action): void
    {
        match ($action) {
            'approve_appointment' => $this->approveAppointment(),
            'reject_appointment'  => $this->rejectAppointment(),
            'add_session'         => $this->addSession(),
            'log_all_sessions'    => $this->logAllSessions(),
            'cancel_all_appointments' => $this->cancelAllAppointments(),
            'no_show'             => $this->markNoShow(),
            'rate_student'        => $this->rateStudent(),
            'add_course'          => $this->addCourse(),
            'edit_course'         => $this->editCourse(),
            'update_course'       => $this->updateCourse(),
            'toggle_course'       => $this->toggleCourse(),
            'delete_course'       => $this->deleteCourse(),
            'update_profile'      => $this->updateProfile(),
            'change_password'     => $this->changePassword(),
            'mark_read'           => $this->markReadAndRedirect(),
            'send_message'        => $this->sendMessage(),
            'mark_conv_read'      => $this->markConvRead(),
            default               => null,
        };
    }

    // ── Abstract method: loadData (ABSTRACTION fulfilled) ─────────────────

    public function loadData(): void
    {
        $db  = $this->db;
        $uid = $this->userId;
        $t   = $this->today;

        // ── Core stats ────────────────────────────────────────────────────
        $this->data['statPending'] = $db->count("SELECT COUNT(*) AS c FROM appointments WHERE instructor_id=$uid AND status='pending'");
        $this->data['statActive']  = $db->count("SELECT COUNT(*) AS c FROM appointments WHERE instructor_id=$uid AND status IN('approved','in_progress')");
        $this->data['statDone']    = $db->count("SELECT COUNT(*) AS c FROM appointments WHERE instructor_id=$uid AND status='completed'");
        $this->data['avgRating']   = $db->scalar("SELECT ROUND(AVG(rating),1) AS r FROM instructor_ratings WHERE instructor_id=$uid", 'r');

        // ── Instructor profile ────────────────────────────────────────────
        $this->data['instructor'] = $db->row("SELECT * FROM instructors WHERE instructor_id=$uid");

        // ── Notifications ─────────────────────────────────────────────────
        $this->data['unreadCnt']   = $this->getUnreadCount();
        $this->data['notifs']      = $this->getNotifications();

        // ── Unread messages per student ───────────────────────────────────
        $unreadMsgMap = [];
        foreach ($db->rows("SELECT sender_id,COUNT(*) AS cnt FROM messages WHERE receiver_id=$uid AND receiver_type='instructor' AND is_read=0 AND sender_type='student' GROUP BY sender_id") as $row) {
            $unreadMsgMap[(int)$row['sender_id']] = (int)$row['cnt'];
        }
        $this->data['unreadMsgMap']   = $unreadMsgMap;
        $this->data['totalUnreadMsg'] = array_sum($unreadMsgMap);
        $this->data['chatStudentId']  = (int)($_GET['chat_student'] ?? 0);

        // ── Appointments ──────────────────────────────────────────────────
        $this->data['pendingAppts'] = $db->rows(
            "SELECT a.*,ic.course_type,s.fullname AS sname,s.email AS semail
             FROM appointments a
             JOIN instructor_courses ic ON ic.course_id=a.course_id
             JOIN students s ON s.student_id=a.student_id
             WHERE a.instructor_id=$uid AND a.status='pending'
             ORDER BY a.start_date ASC, a.requested_at ASC"
        );
        $this->data['allAppts'] = $db->rows(
            "SELECT a.*,ic.course_type,s.fullname AS sname,
                    (SELECT SUM(hours_covered) FROM sessions WHERE appointment_id=a.appointment_id AND status='completed') AS hours_done
             FROM appointments a
             JOIN instructor_courses ic ON ic.course_id=a.course_id
             JOIN students s ON s.student_id=a.student_id
             WHERE a.instructor_id=$uid
             ORDER BY a.start_date DESC, a.requested_at DESC"
        );

        // ── My students ───────────────────────────────────────────────────
        $this->data['myStudents'] = $db->rows(
            "SELECT DISTINCT s.student_id,s.fullname,s.email,
               (SELECT ic.course_type FROM appointments a2 JOIN instructor_courses ic ON ic.course_id=a2.course_id
                WHERE a2.instructor_id=$uid AND a2.student_id=s.student_id ORDER BY a2.requested_at DESC LIMIT 1) AS latest_course,
               (SELECT a3.status FROM appointments a3 WHERE a3.instructor_id=$uid AND a3.student_id=s.student_id ORDER BY a3.requested_at DESC LIMIT 1) AS latest_status
             FROM students s
             WHERE EXISTS(SELECT 1 FROM appointments a WHERE a.instructor_id=$uid AND a.student_id=s.student_id AND a.status IN('approved','in_progress','completed','pending'))
             ORDER BY s.fullname ASC"
        );

        // ── Unrated sessions ──────────────────────────────────────────────
        $this->data['unratedSessions'] = $db->rows(
            "SELECT sess.*,a.course_id,ic.course_type,s.fullname AS sname
             FROM sessions sess
             JOIN appointments a ON a.appointment_id=sess.appointment_id
             JOIN instructor_courses ic ON ic.course_id=a.course_id
             JOIN students s ON s.student_id=sess.student_id
             WHERE sess.instructor_id=$uid AND sess.status='completed'
               AND (sess.performance_rating IS NULL OR sess.grade IS NULL)
               AND ic.course_type LIKE 'TDC%'
             ORDER BY sess.session_date DESC"
        );

        // ── Courses ───────────────────────────────────────────────────────
        $this->data['myCourses'] = $db->rows(
            "SELECT * FROM instructor_courses WHERE instructor_id=$uid ORDER BY course_type"
        );

        // ── All sessions ──────────────────────────────────────────────────
        $this->data['allSessions'] = $db->rows(
            "SELECT s.*,st.fullname AS sname,ic.course_type
             FROM sessions s
             JOIN students st ON st.student_id=s.student_id
             JOIN appointments a ON a.appointment_id=s.appointment_id
             JOIN instructor_courses ic ON ic.course_id=a.course_id
             WHERE s.instructor_id=$uid
             ORDER BY s.session_date DESC LIMIT 50"
        );

        // ── Top students this month ───────────────────────────────────────
        $this->data['topStudents'] = $db->rows(
            "SELECT s.student_id,s.fullname,ROUND(AVG(sess.grade),1) AS avg_grade,COUNT(sess.session_id) AS sess_cnt
             FROM sessions sess JOIN students s ON s.student_id=sess.student_id
             WHERE sess.instructor_id=$uid AND sess.grade IS NOT NULL
               AND DATE_FORMAT(sess.session_date,'%Y-%m')=DATE_FORMAT(NOW(),'%Y-%m')
             GROUP BY s.student_id ORDER BY avg_grade DESC LIMIT 5"
        );

        // ── Chart data ────────────────────────────────────────────────────
        $months = $this->buildMonthLabels();
        $sessPerMonth = [];
        foreach ($months as $label) {
            $ym = date('Y-m', strtotime($label));
            $sessPerMonth[] = (float)($db->scalar(
                "SELECT COALESCE(SUM(hours_covered),0) AS h FROM sessions
                 WHERE instructor_id=$uid AND status='completed' AND DATE_FORMAT(session_date,'%Y-%m')='$ym'", 'h') ?? 0);
        }
        $this->data['instrMonths']      = $months;
        $this->data['instrSessPerMonth']= $sessPerMonth;

        $buckets = ['90-100' => 0, '80-89' => 0, '70-79' => 0, '60-69' => 0, '<60' => 0];
        foreach ($db->rows("SELECT grade FROM sessions WHERE instructor_id=$uid AND grade IS NOT NULL AND status='completed'") as $row) {
            $g = (int)$row['grade'];
            if ($g >= 90)     $buckets['90-100']++;
            elseif ($g >= 80) $buckets['80-89']++;
            elseif ($g >= 70) $buckets['70-79']++;
            elseif ($g >= 60) $buckets['60-69']++;
            else              $buckets['<60']++;
        }
        $this->data['instrGradeBuckets'] = $buckets;

        // ── Sidebar stats ─────────────────────────────────────────────────
        $this->data['sbTotalStudents']  = $db->count("SELECT COUNT(DISTINCT student_id) AS c FROM appointments WHERE instructor_id=$uid AND status IN('approved','in_progress','completed')");
        $this->data['sbTotalHrsTaught'] = (float)($db->scalar("SELECT COALESCE(SUM(hours_covered),0) AS h FROM sessions WHERE instructor_id=$uid AND status='completed'", 'h') ?? 0);
        $this->data['sbMonthHrs']       = (float)($db->scalar("SELECT COALESCE(SUM(hours_covered),0) AS h FROM sessions WHERE instructor_id=$uid AND status='completed' AND DATE_FORMAT(session_date,'%Y-%m')=DATE_FORMAT(NOW(),'%Y-%m')", 'h') ?? 0);
        $this->data['sbTotalSess']      = $db->count("SELECT COUNT(*) AS c FROM sessions WHERE instructor_id=$uid AND status='completed'");

        // ── Flash ─────────────────────────────────────────────────────────
        $this->data['flash'] = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);
    }

    // ── Private action methods (ENCAPSULATION) ────────────────────────────

    private function approveAppointment(): void
    {
        $id   = (int)($_POST['appointment_id'] ?? 0);
        $note = $this->db->escape($_POST['instructor_note'] ?? '');
        $uid  = $this->userId;
        $this->db->query("UPDATE appointments SET status='approved',instructor_note='$note',reviewed_at=NOW() WHERE appointment_id=$id AND instructor_id=$uid AND status='pending'");
        $aRow = $this->db->row("SELECT a.*,s.student_id,ic.course_type FROM appointments a JOIN students s ON s.student_id=a.student_id JOIN instructor_courses ic ON ic.course_id=a.course_id WHERE a.appointment_id=$id");
        if ($aRow) $this->notify($aRow['student_id'], 'student', "Your appointment for {$aRow['course_type']} has been APPROVED!", 'appointment_approved');
        $this->redirect(self::SELF_FILE . '?section=appointments', 'Appointment approved.');
    }

    private function rejectAppointment(): void
    {
        $id   = (int)($_POST['appointment_id'] ?? 0);
        $note = $this->db->escape($_POST['instructor_note'] ?? '');
        $uid  = $this->userId;
        $this->db->query("UPDATE appointments SET status='rejected',instructor_note='$note',reviewed_at=NOW() WHERE appointment_id=$id AND instructor_id=$uid AND status='pending'");
        $aRow = $this->db->row("SELECT a.*,s.student_id,ic.course_type FROM appointments a JOIN students s ON s.student_id=a.student_id JOIN instructor_courses ic ON ic.course_id=a.course_id WHERE a.appointment_id=$id");
        if ($aRow) $this->notify($aRow['student_id'], 'student', "Your appointment for {$aRow['course_type']} was declined.", 'appointment_rejected');
        $this->redirect(self::SELF_FILE . '?section=appointments', 'Appointment rejected.');
    }

    /** Compute the last day of a course package given its start date and type. */
    private function packageEndDate(string $startDate, string $courseType): string
    {
        $isTDC   = stripos($courseType, 'TDC') === 0;
        $isCombo = stripos($courseType, 'Both') !== false;
        $days    = ($isTDC || $isCombo) ? 3 : 2;
        return date('Y-m-d', strtotime($startDate . ' +' . ($days - 1) . ' days'));
    }

    private function addSession(): void
    {
        $uid     = $this->userId;
        $appt_id = (int)($_POST['appointment_id'] ?? 0);
        $grade   = (int)($_POST['grade'] ?? 0);
        $grade   = ($grade >= 1 && $grade <= 100) ? $grade : null;
        $improve = $this->db->escape($_POST['improvements'] ?? '');
        $notes   = $this->db->escape($_POST['performance_notes'] ?? '');
        $hrsRaw  = $_POST['hours_covered'] ?? '';
        $carHrsRaw  = $_POST['car_hours'] ?? '';
        $motoHrsRaw = $_POST['moto_hours'] ?? '';

        $aRow = $this->db->row("SELECT a.*,ic.course_type FROM appointments a JOIN instructor_courses ic ON ic.course_id=a.course_id WHERE a.appointment_id=$appt_id AND a.instructor_id=$uid AND a.status IN('approved','in_progress')");
        if (!$aRow) {
            $this->redirect(self::SELF_FILE . '?section=appointments', 'Appointment not found or already completed.', true);
        }

        // One log per appointment/package — the whole package's hours are logged at once, on its last day.
        $already = $this->db->count("SELECT COUNT(*) AS c FROM sessions WHERE appointment_id=$appt_id AND status='completed'");
        if ($already > 0) {
            $this->redirect(self::SELF_FILE . '?section=appointments', 'This package has already been logged.', true);
        }

        $sessType = stripos($aRow['course_type'], 'TDC') === 0 ? 'TDC' : 'PDC';
        $sessDate = $this->packageEndDate($aRow['start_date'], $aRow['course_type']);
        $isCombo  = stripos($aRow['course_type'], 'Both') !== false;

        $carHoursSql = 'NULL';
        $motoHoursSql = 'NULL';
        if ($isCombo) {
            // PDC Both logs Car and Motorcycle hours SEPARATELY (default 8 each) —
            // the actual split, not always an even 16/2 divide, so an instructor who
            // spent more time on one vehicle can reflect that accurately.
            $carHrs  = ($carHrsRaw !== '' && is_numeric($carHrsRaw) && $carHrsRaw >= 0) ? (float)$carHrsRaw : 8.0;
            $motoHrs = ($motoHrsRaw !== '' && is_numeric($motoHrsRaw) && $motoHrsRaw >= 0) ? (float)$motoHrsRaw : 8.0;
            $hrs = $carHrs + $motoHrs;
            $carHoursSql  = $carHrs;
            $motoHoursSql = $motoHrs;
        } else {
            // Defaults to the fixed package total, but the instructor can adjust actual hours delivered.
            $hrs = ($hrsRaw !== '' && is_numeric($hrsRaw) && $hrsRaw > 0) ? (float)$hrsRaw : (float)$aRow['total_hours'];
        }

        // ── TDC is cumulative across multiple bookings (15 hours total required).
        // A grade can only be recorded once this session's hours actually push the
        // student's running TDC total to 15 or more — a partial log (e.g. only
        // 14/15 reached) cannot be graded yet, matching "instructor cannot grade
        // them" until the requirement is genuinely met. ─────────────────────────
        $tdcNowComplete = false;
        if ($sessType === 'TDC') {
            $priorTdcHours = (float)($this->db->scalar(
                "SELECT COALESCE(SUM(sess.hours_covered),0) AS h FROM sessions sess
                 JOIN appointments a2 ON a2.appointment_id=sess.appointment_id
                 JOIN instructor_courses ic2 ON ic2.course_id=a2.course_id
                 WHERE sess.student_id={$aRow['student_id']} AND ic2.course_type LIKE 'TDC%' AND sess.status='completed'",
                'h'
            ) ?? 0);
            $tdcNowComplete = ($priorTdcHours + $hrs) >= 15;
            if (!$tdcNowComplete) {
                $grade = null; // not enough cumulative hours yet — cannot grade
            }
        }
        $gradeSQL = $grade === null ? 'NULL' : $grade;

        $this->db->query("INSERT INTO sessions (appointment_id,session_type,session_number,student_id,instructor_id,session_date,hours_covered,pdc_car_hours,pdc_moto_hours,status,grade,improvements,performance_notes) VALUES ($appt_id,'$sessType',1,{$aRow['student_id']},$uid,'$sessDate',$hrs,$carHoursSql,$motoHoursSql,'completed',$gradeSQL,'$improve','$notes')");

        $this->db->query("UPDATE appointments SET status='completed',completed_at=NOW()" .
            ($sessType === 'TDC' && $grade !== null ? ",tdc_overall_grade=$grade" : "") .
            " WHERE appointment_id=$appt_id");

        // TDC completion only informs the student of their grade — no instructor
        // rating prompt. PDC completion is the only point where the student is
        // asked to rate their instructor.
        if ($sessType === 'TDC') {
            $notifyMsg = $tdcNowComplete
                ? 'Your TDC course is complete! Check your grade in My Progress.'
                : 'Your TDC session was logged, but you still need more hours to complete the 15-hour requirement. Please book another TDC package to finish.';
        } else {
            $notifyMsg = 'Your PDC course is complete! Please rate your instructor.';
        }
        $this->notify($aRow['student_id'], 'student', $notifyMsg, 'course_completed');
        $this->redirect(self::SELF_FILE . '?section=appointments', 'Session logged — package complete.');
    }

    /** Bulk-log every appointment whose package ends on the given date. */
    private function logAllSessions(): void
    {
        $uid     = $this->userId;
        $endDate = $this->db->escape($_POST['end_date'] ?? $this->today);

        $appts = $this->db->rows(
            "SELECT a.*, ic.course_type FROM appointments a
             JOIN instructor_courses ic ON ic.course_id=a.course_id
             WHERE a.instructor_id=$uid AND a.status IN('approved','in_progress')
               AND DATE_ADD(a.start_date, INTERVAL (CASE WHEN ic.course_type LIKE 'TDC%' OR ic.course_type LIKE '%Both%' THEN 2 ELSE 1 END) DAY) = '$endDate'
               AND NOT EXISTS(SELECT 1 FROM sessions WHERE appointment_id=a.appointment_id AND status='completed')"
        );

        $count = 0;
        foreach ($appts as $aRow) {
            $sessType = stripos($aRow['course_type'], 'TDC') === 0 ? 'TDC' : 'PDC';
            $hrs = (float)$aRow['total_hours'];
            $this->db->query("INSERT INTO sessions (appointment_id,session_type,session_number,student_id,instructor_id,session_date,hours_covered,status) VALUES ({$aRow['appointment_id']},'$sessType',1,{$aRow['student_id']},$uid,'$endDate',$hrs,'completed')");
            $this->db->query("UPDATE appointments SET status='completed',completed_at=NOW() WHERE appointment_id={$aRow['appointment_id']}");
            $notifyMsg = ($sessType === 'TDC')
                ? 'Your TDC course is complete! Check your grade in My Progress.'
                : 'Your PDC course is complete! Please rate your instructor.';
            $this->notify($aRow['student_id'], 'student', $notifyMsg, 'course_completed');
            $count++;
        }
        $this->redirect(self::SELF_FILE . '?section=appointments', "$count session(s) logged for $endDate. Set individual grades from the Performance section.");
    }

    /** Bulk-cancel every appointment starting on the given date. */
    private function cancelAllAppointments(): void
    {
        $uid    = $this->userId;
        $date   = $this->db->escape($_POST['target_date'] ?? '');
        $reason = $this->db->escape(trim($_POST['cancel_reason'] ?? ''));

        $appts = $this->db->rows(
            "SELECT a.*, ic.course_type FROM appointments a
             JOIN instructor_courses ic ON ic.course_id=a.course_id
             WHERE a.instructor_id=$uid AND a.status IN('approved','in_progress') AND a.start_date='$date'"
        );

        $count = 0;
        foreach ($appts as $aRow) {
            $this->db->query("UPDATE appointments SET status='cancelled', instructor_note='$reason' WHERE appointment_id={$aRow['appointment_id']}");
            $msg = "Your {$aRow['course_type']} booking on $date was cancelled by your instructor" . ($reason ? ": $reason" : ".");
            $this->notify($aRow['student_id'], 'student', $this->db->escape($msg), 'instructor_cancelled');
            $count++;
        }
        $this->redirect(self::SELF_FILE . '?section=appointments', "$count appointment(s) on $date cancelled.");
    }

    private function markNoShow(): void
    {
        $uid    = $this->userId;
        $apptId = (int)($_POST['appointment_id'] ?? 0);
        $date   = $this->db->escape($_POST['session_date'] ?? date('Y-m-d'));
        $note   = $this->db->escape(trim($_POST['no_show_note'] ?? ''));

        // Verify appointment belongs to this instructor and is active
        $appt = $this->db->row(
            "SELECT a.*, ic.course_type, s.fullname AS sname, s.student_id
             FROM appointments a
             JOIN instructor_courses ic ON ic.course_id=a.course_id
             JOIN students s ON s.student_id=a.student_id
             WHERE a.appointment_id=$apptId AND a.instructor_id=$uid
               AND a.status IN('approved','in_progress')"
        );
        if (!$appt) {
            $this->redirect(self::SELF_FILE . '?section=appointments', 'Appointment not found.', true);
        }

        // Get the next session number
        $sessNum = ($this->db->count(
            "SELECT COUNT(*) AS c FROM sessions WHERE appointment_id=$apptId"
        ) ?? 0) + 1;

        $sessType = stripos($appt['course_type'], 'TDC') === 0 ? 'TDC' : 'PDC';

        $this->db->query(
            "INSERT INTO sessions (appointment_id,student_id,instructor_id,session_date,session_number,
             hours_covered,session_type,status,performance_notes)
             VALUES ($apptId,{$appt['student_id']},$uid,'$date',$sessNum,0,'$sessType','no_show','$note')"
        );

        // Notify student
        $ctype = $this->db->escape($appt['course_type']);
        $msg   = "Your instructor marked you as No Show for your $ctype session on $date." .
                 ($note ? " Remarks: $note" : '');
        $this->notify($appt['student_id'], 'student', $this->db->escape($msg), 'no_show');

        $this->redirect(self::SELF_FILE . '?section=appointments', 'Student marked as No Show.');
    }

    private function rateStudent(): void
    {
        $uid     = $this->userId;
        $sessId  = (int)($_POST['session_id'] ?? 0);
        $notes   = $this->db->escape($_POST['performance_notes'] ?? '');
        $improve = $this->db->escape($_POST['improvements'] ?? '');

        $sessRow = $this->db->row("SELECT s.*,ic.course_type FROM sessions s JOIN appointments a ON a.appointment_id=s.appointment_id JOIN instructor_courses ic ON ic.course_id=a.course_id WHERE s.session_id=$sessId AND s.instructor_id=$uid");
        if (!$sessRow) {
            $this->redirect(self::SELF_FILE . '?section=performance', 'Session not found.', true);
        }
        $isTDC = stripos($sessRow['course_type'], 'TDC') === 0;

        if ($isTDC) {
            // TDC: grade only — no instructor star-rating of the student for TDC sessions.
            // Grading is blocked until the student's cumulative TDC hours reach 15.
            $tdcHoursLogged = (float)($this->db->scalar(
                "SELECT COALESCE(SUM(sess.hours_covered),0) AS h FROM sessions sess
                 JOIN appointments a2 ON a2.appointment_id=sess.appointment_id
                 JOIN instructor_courses ic2 ON ic2.course_id=a2.course_id
                 WHERE sess.student_id={$sessRow['student_id']} AND ic2.course_type LIKE 'TDC%' AND sess.status='completed'",
                'h'
            ) ?? 0);
            if ($tdcHoursLogged < 15) {
                $this->redirect(self::SELF_FILE . '?section=performance', 'This student has only logged ' . $tdcHoursLogged . '/15 TDC hours — grading unlocks once they reach 15.', true);
            }

            $grade = (int)($_POST['grade'] ?? 0);
            $grade = ($grade >= 1 && $grade <= 100) ? $grade : null;
            $gradeSQL = $grade === null ? 'NULL' : $grade;
            $this->db->query("UPDATE sessions SET grade=$gradeSQL,performance_rating=NULL,performance_notes='$notes',improvements='$improve' WHERE session_id=$sessId AND instructor_id=$uid");

            $og = (float)($this->db->scalar("SELECT ROUND(AVG(grade),1) AS ag FROM sessions WHERE appointment_id={$sessRow['appointment_id']} AND grade IS NOT NULL", 'ag') ?? 0);
            if ($og > 0) $this->db->query("UPDATE appointments SET tdc_overall_grade=ROUND($og) WHERE appointment_id={$sessRow['appointment_id']}");
        } else {
            // PDC: star rating only — no numeric grade for PDC sessions.
            $rating = max(1, min(5, (int)($_POST['performance_rating'] ?? 5)));
            $this->db->query("UPDATE sessions SET performance_rating=$rating,grade=NULL,performance_notes='$notes',improvements='$improve' WHERE session_id=$sessId AND instructor_id=$uid");
        }

        $this->redirect(self::SELF_FILE . '?section=performance', 'Session updated.');
    }

    private function addCourse(): void
    {
        $uid   = $this->userId;
        $ct    = $this->db->escape($_POST['course_type'] ?? '');
        $desc  = $this->db->escape($_POST['description'] ?? '');
        $reqs  = $this->db->escape($_POST['requirements'] ?? '');
        $phr   = (float)($_POST['price_per_hr'] ?? 0);
        $limitRaw = $_POST['student_limit'] ?? '';
        $limit = ($limitRaw !== '' && (int)$limitRaw > 0) ? (int)$limitRaw : 'NULL';

        // ── Duplicate course check ────────────────────────────────────────────
        $exists = $this->db->count(
            "SELECT COUNT(*) AS c FROM instructor_courses
             WHERE instructor_id=$uid AND course_type='$ct'"
        );
        if ($exists > 0) {
            $this->redirect(self::SELF_FILE . '?section=courses',
                "This course has already been posted. You can edit the existing $ct course instead.", true);
        }

        $this->db->query("INSERT INTO instructor_courses (instructor_id,course_type,description,requirements,price_per_hr,student_limit) VALUES ($uid,'$ct','$desc','$reqs',$phr,$limit)");
        $this->redirect(self::SELF_FILE . '?section=courses', 'Course added.');
    }

    private function editCourse(): void
    {
        // Redirects to courses section with edit_course param — modal opens in view
        $cid = (int)($_POST['course_id'] ?? 0);
        $this->redirect(self::SELF_FILE . '?section=courses&edit_course=' . $cid);
    }

    private function updateCourse(): void
    {
        $uid      = $this->userId;
        $cid      = (int)($_POST['course_id'] ?? 0);
        $desc     = $this->db->escape($_POST['description'] ?? '');
        $reqs     = $this->db->escape($_POST['requirements'] ?? '');
        $phr      = (float)($_POST['price_per_hr'] ?? 0);
        $limitRaw = $_POST['student_limit'] ?? '';
        $limit    = ($limitRaw !== '' && (int)$limitRaw > 0) ? (int)$limitRaw : 'NULL';

        // Update the course itself
        $this->db->query("UPDATE instructor_courses SET description='$desc',requirements='$reqs',price_per_hr=$phr,student_limit=$limit WHERE course_id=$cid AND instructor_id=$uid");

        // Sync student_limit into all future course_schedules rows for this course
        // so the admin workload view always reflects the current limit
        $this->db->query("UPDATE course_schedules SET student_limit=$limit WHERE course_id=$cid AND schedule_date >= CURDATE()");

        $this->redirect(self::SELF_FILE . '?section=courses', 'Course updated.');
    }

    private function toggleCourse(): void
    {
        $cid = (int)($_POST['course_id'] ?? 0);
        $uid = $this->userId;
        $this->db->query("UPDATE instructor_courses SET is_active=1-is_active WHERE course_id=$cid AND instructor_id=$uid");
        $this->redirect(self::SELF_FILE . '?section=courses');
    }

    private function deleteCourse(): void
    {
        $cid = (int)($_POST['course_id'] ?? 0);
        $uid = $this->userId;
        $this->db->query("DELETE FROM instructor_courses WHERE course_id=$cid AND instructor_id=$uid");
        $this->redirect(self::SELF_FILE . '?section=courses');
    }

    private function updateProfile(): void
    {
        $uid    = $this->userId;
        $name   = $this->db->escape($_POST['fullname'] ?? '');
        $email  = $this->db->escape($_POST['email'] ?? '');
        $mobile = $this->db->escape($_POST['mobile'] ?? '');
        $bio    = $this->db->escape($_POST['bio'] ?? '');
        $certSQL = '';
        if (isset($_FILES['certificate_image']) && $_FILES['certificate_image']['error'] === 0) {
            $ext = strtolower(pathinfo($_FILES['certificate_image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','gif','webp','pdf'])) {
                $path = '../assets/uploads/certificates/';
                if (!is_dir($path)) mkdir($path, 0755, true);
                $fname = 'cert_' . $uid . '_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['certificate_image']['tmp_name'], $path . $fname)) $certSQL = ",certificate_image='$fname'";
            }
        }
        $this->db->query("UPDATE instructors SET fullname='$name',email='$email',mobile='$mobile',bio='$bio',modified_date=NOW()$certSQL WHERE instructor_id=$uid");
        $this->redirect(self::SELF_FILE . '?section=profile', 'Profile updated.');
    }

    private function changePassword(): void
    {
        $uid     = $this->userId;
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($current === '' || $new === '' || $confirm === '') {
            $this->redirect(self::SELF_FILE . '?section=profile', 'Please fill in all password fields.', true);
        }
        if ($new !== $confirm) {
            $this->redirect(self::SELF_FILE . '?section=profile', 'New passwords do not match.', true);
        }
        $pattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/";
        if (!preg_match($pattern, $new)) {
            $this->redirect(self::SELF_FILE . '?section=profile', 'New password must be at least 8 characters with uppercase, lowercase, number, and special character.', true);
        }

        $row = $this->db->row("SELECT password FROM instructors WHERE instructor_id=$uid");
        if (!$row || !password_verify($current, $row['password'])) {
            $this->redirect(self::SELF_FILE . '?section=profile', 'Current password is incorrect.', true);
        }

        $hashed = password_hash($new, PASSWORD_BCRYPT);
        $this->db->query("UPDATE instructors SET password='$hashed' WHERE instructor_id=$uid");
        $this->redirect(self::SELF_FILE . '?section=profile', 'Password changed successfully.');
    }

    private function markReadAndRedirect(): void
    {
        $this->markNotificationsRead();
        $this->redirect(self::SELF_FILE . '?section=messages');
    }

    private function sendMessage(): void
    {
        $uid    = $this->userId;
        $toId   = (int)($_POST['to_student_id'] ?? 0);
        $body   = $this->db->escape(trim($_POST['body'] ?? ''));
        $apptId = (int)($_POST['appointment_id'] ?? 0);
        $ok     = $this->db->count("SELECT COUNT(*) AS c FROM appointments WHERE student_id=$toId AND instructor_id=$uid AND status IN('pending','approved','in_progress','completed')");
        if ($ok > 0 && $body !== '') {
            $apptCol = $apptId > 0 ? $apptId : 'NULL';
            $this->db->query("INSERT INTO messages (sender_id,sender_type,receiver_id,receiver_type,appointment_id,body) VALUES ($uid,'instructor',$toId,'student',$apptCol,'$body')");
            $this->notify($toId, 'student', '💬 Message from your instructor.', 'message');
        }
        $this->redirect(self::SELF_FILE . "?section=chat&chat_student=$toId");
    }

    private function markConvRead(): void
    {
        $uid = $this->userId;
        $sid = (int)($_POST['student_id'] ?? 0);
        $this->db->query("UPDATE messages SET is_read=1 WHERE receiver_id=$uid AND receiver_type='instructor' AND sender_id=$sid AND sender_type='student'");
        $this->redirect(self::SELF_FILE . "?section=chat&chat_student=$sid");
    }
}
