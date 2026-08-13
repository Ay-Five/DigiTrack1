<?php
require_once __DIR__ . '/BaseDashboard.php';
require_once __DIR__ . '/Notifiable.php';
require_once __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;
if (!isset($GLOBALS['__motosafe_dotenv_loaded'])) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->safeLoad();
    $GLOBALS['__motosafe_dotenv_loaded'] = true;
}

/**
 * AdminDashboard.php
 *
 * OOP PILLARS DEMONSTRATED:
 *
 * 1. INHERITANCE   — extends BaseDashboard
 *    Gets $this->db, $this->userId, redirect(), notify(),
 *    buildMonthLabels(), etc. for free.
 *
 * 2. ABSTRACTION   — implements the two abstract methods required by BaseDashboard:
 *    handlePost()  and  loadData()
 *    The parent class guarantees these exist without knowing the details.
 *
 * 3. POLYMORPHISM  — implements Notifiable interface
 *    Any code that receives a Notifiable object can call
 *    getUnreadCount() / markNotificationsRead() / getNotifications()
 *    without knowing it's talking to an Admin specifically.
 *
 * 4. ENCAPSULATION — all admin-specific logic and state is private/protected.
 *    Raw SQL, redirect targets, and action names are hidden inside this class.
 */
class AdminDashboard extends BaseDashboard implements Notifiable
{
    // ── Private constants: encapsulate magic strings ──────────────────────
    private const SELF_FILE   = 'admin_dashboard.php';
    private const USER_TYPE   = 'admin';

    // ── Constructor ───────────────────────────────────────────────────────

    public function __construct(Database $db, int $adminId)
    {
        parent::__construct($db, $adminId); // call BaseDashboard constructor
    }

    // ── Notifiable interface (POLYMORPHISM) ───────────────────────────────

    public function getUnreadCount(): int
    {
        return $this->db->count(
            "SELECT COUNT(*) AS c FROM notifications
             WHERE user_id={$this->userId} AND user_type='admin' AND is_read=0"
        );
    }

    public function markNotificationsRead(): void
    {
        $this->db->query(
            "UPDATE notifications SET is_read=1
             WHERE user_id={$this->userId} AND user_type='admin'"
        );
    }

    public function getNotifications(int $limit = 30): array
    {
        return $this->db->rows(
            "SELECT * FROM notifications
             WHERE user_id={$this->userId} AND user_type='admin'
             ORDER BY created_at DESC LIMIT $limit"
        );
    }

    // ── Abstract method: handlePost (ABSTRACTION fulfilled) ───────────────

    public function handlePost(string $action): void
    {
        match ($action) {
            'add_instructor'      => $this->addInstructor(),
            'delete_instructor'   => $this->deleteInstructor(),
            'delete_student'      => $this->deleteStudent(),
            'delete_appointment'  => $this->deleteAppointment(),
            'force_complete'      => $this->forceComplete(),
            'announce'            => $this->sendAnnouncement(),
            'mark_admin_read'     => $this->markAdminRead(),
            'assign_student'      => $this->assignStudent(),
            'reassign_appointment'=> $this->reassignAppointment(),
            'admin_edit_course'   => $this->adminEditCourse(),
            'admin_edit_session'  => $this->adminEditSession(),
            'resync_schedule_slots' => $this->resyncScheduleSlots(),
            'change_password'     => $this->changePassword(),
            'add_student'         => $this->addStudent(),
            'edit_student'        => $this->editStudent(),
            'toggle_student'      => $this->toggleStudent(),
            'add_admin'           => $this->addAdmin(),
            'edit_admin'          => $this->editAdmin(),
            'toggle_admin'        => $this->toggleAdmin(),
            'delete_admin'        => $this->deleteAdmin(),
            'reset_admin_pwd'     => $this->resetAdminPwd(),
            default               => null,
        };
    }

    // ── Abstract method: loadData (ABSTRACTION fulfilled) ─────────────────

    public function loadData(): void
    {
        $db = $this->db;
        $t  = $this->today;
        $ym = date('Y-m');

        // ── Core stats ────────────────────────────────────────────────────
        $this->data['totalStudents']    = $db->count("SELECT COUNT(*) AS c FROM students");
        $this->data['totalInstructors'] = $db->count("SELECT COUNT(*) AS c FROM instructors");
        $this->data['pendingAppts']     = $db->count("SELECT COUNT(*) AS c FROM appointments WHERE status='pending'");
        $this->data['completedAppts']   = $db->count("SELECT COUNT(*) AS c FROM appointments WHERE status='completed'");
        $this->data['totalSessions']    = $db->count("SELECT COUNT(*) AS c FROM sessions WHERE status='completed'");
        $this->data['avgRating']        = $db->scalar("SELECT ROUND(AVG(rating),1) AS r FROM instructor_ratings", 'r');
        $this->data['totalHoursTaught'] = (float)($db->scalar("SELECT COALESCE(SUM(hours_covered),0) AS h FROM sessions WHERE status='completed'", 'h') ?? 0);

        // ── Analytics ─────────────────────────────────────────────────────
        $this->data['todayLogins'] = $db->count(
            "SELECT (SELECT COUNT(*) FROM student_login_history WHERE DATE(login_datetime)='$t' AND status='success')
                  + (SELECT COUNT(*) FROM instructor_login_history WHERE DATE(login_time)='$t' AND login_status='success')
                  + (SELECT COUNT(*) FROM admin_login_history WHERE DATE(login_time)='$t' AND login_status='success') AS c"
        );
        $this->data['monthLogins'] = $db->count(
            "SELECT (SELECT COUNT(*) FROM student_login_history WHERE YEAR(login_datetime)=YEAR(NOW()) AND MONTH(login_datetime)=MONTH(NOW()) AND status='success')
                  + (SELECT COUNT(*) FROM instructor_login_history WHERE YEAR(login_time)=YEAR(NOW()) AND MONTH(login_time)=MONTH(NOW()) AND login_status='success') AS c"
        );
        $this->data['newStudentsMonth'] = $db->count(
            "SELECT COUNT(*) AS c FROM students WHERE YEAR(created_at)=YEAR(NOW()) AND MONTH(created_at)=MONTH(NOW())"
        );

        // ── Rankings ──────────────────────────────────────────────────────
        $this->data['topInstructors'] = $db->rows(
            "SELECT i.instructor_id,i.fullname,ROUND(AVG(ir.rating),1) AS avg_r,COUNT(ir.rating_id) AS cnt,
                    (SELECT COUNT(*) FROM appointments WHERE instructor_id=i.instructor_id AND status='completed') AS done
             FROM instructor_ratings ir
             JOIN instructors i ON i.instructor_id=ir.instructor_id
             GROUP BY i.instructor_id ORDER BY avg_r DESC,cnt DESC LIMIT 5"
        );
        $this->data['lowInstructors'] = $db->rows(
            "SELECT i.instructor_id,i.fullname,ROUND(AVG(ir.rating),1) AS avg_r,COUNT(ir.rating_id) AS cnt
             FROM instructor_ratings ir
             JOIN instructors i ON i.instructor_id=ir.instructor_id
             GROUP BY i.instructor_id HAVING cnt>=1 ORDER BY avg_r ASC,cnt DESC LIMIT 5"
        );
        $this->data['topStudents'] = $db->rows(
            "SELECT s.student_id,s.fullname,ROUND(AVG(sess.grade),1) AS avg_grade,COUNT(sess.session_id) AS sess_cnt
             FROM sessions sess JOIN students s ON s.student_id=sess.student_id
             WHERE sess.grade IS NOT NULL AND DATE_FORMAT(sess.session_date,'%Y-%m')='$ym'
             GROUP BY s.student_id ORDER BY avg_grade DESC LIMIT 5"
        );
        $this->data['lowStudents'] = $db->rows(
            "SELECT s.student_id,s.fullname,ROUND(AVG(sess.grade),1) AS avg_grade,COUNT(sess.session_id) AS sess_cnt
             FROM sessions sess JOIN students s ON s.student_id=sess.student_id
             WHERE sess.grade IS NOT NULL AND DATE_FORMAT(sess.session_date,'%Y-%m')='$ym'
             GROUP BY s.student_id ORDER BY avg_grade ASC LIMIT 5"
        );

        // ── Chart data ────────────────────────────────────────────────────
        $months = $this->buildMonthLabels();
        $newStudents = $apptCompleted = $apptPending = $apptCancelled = [];
        foreach ($months as $label) {
            $ym2 = date('Y-m', strtotime($label));
            $newStudents[]   = $db->count("SELECT COUNT(*) AS c FROM students WHERE DATE_FORMAT(created_at,'%Y-%m')='$ym2'");
            $apptCompleted[] = $db->count("SELECT COUNT(*) AS c FROM appointments WHERE status='completed' AND DATE_FORMAT(requested_at,'%Y-%m')='$ym2'");
            $apptPending[]   = $db->count("SELECT COUNT(*) AS c FROM appointments WHERE status IN('pending','approved','in_progress') AND DATE_FORMAT(requested_at,'%Y-%m')='$ym2'");
            $apptCancelled[] = $db->count("SELECT COUNT(*) AS c FROM appointments WHERE status IN('cancelled','rejected') AND DATE_FORMAT(requested_at,'%Y-%m')='$ym2'");
        }
        $this->data['chartMonths']        = $months;
        $this->data['chartNewStudents']   = $newStudents;
        $this->data['chartApptCompleted'] = $apptCompleted;
        $this->data['chartApptPending']   = $apptPending;
        $this->data['chartApptCancelled'] = $apptCancelled;

        // ── Course completion counts (donut chart) ────────────────────────
        $this->data['tdcDoneCount']   = $db->count("SELECT COUNT(*) AS c FROM appointments a JOIN instructor_courses ic ON ic.course_id=a.course_id WHERE a.status='completed' AND ic.course_type LIKE 'TDC%'");
        $this->data['pdcDoneCount']   = $db->count("SELECT COUNT(*) AS c FROM appointments a JOIN instructor_courses ic ON ic.course_id=a.course_id WHERE a.status='completed' AND ic.course_type LIKE 'PDC%'");
        $this->data['tdcActiveCount'] = $db->count("SELECT COUNT(*) AS c FROM appointments a JOIN instructor_courses ic ON ic.course_id=a.course_id WHERE a.status IN('approved','in_progress') AND ic.course_type LIKE 'TDC%'");
        $this->data['pdcActiveCount'] = $db->count("SELECT COUNT(*) AS c FROM appointments a JOIN instructor_courses ic ON ic.course_id=a.course_id WHERE a.status IN('approved','in_progress') AND ic.course_type LIKE 'PDC%'");

        // ── Grade distribution ────────────────────────────────────────────
        $buckets = ['90-100' => 0, '80-89' => 0, '70-79' => 0, '60-69' => 0, '<60' => 0];
        foreach ($db->rows("SELECT grade FROM sessions WHERE grade IS NOT NULL AND status='completed'") as $row) {
            $g = (int)$row['grade'];
            if ($g >= 90)      $buckets['90-100']++;
            elseif ($g >= 80)  $buckets['80-89']++;
            elseif ($g >= 70)  $buckets['70-79']++;
            elseif ($g >= 60)  $buckets['60-69']++;
            else               $buckets['<60']++;
        }
        $this->data['gradeBuckets'] = $buckets;

        // ── Gender distribution ───────────────────────────────────────────
        $total = $this->data['totalStudents'];
        $male  = $db->count("SELECT COUNT(*) AS c FROM students WHERE gender='Male'");
        $female= $db->count("SELECT COUNT(*) AS c FROM students WHERE gender='Female'");
        $this->data['maleCount']        = $male;
        $this->data['femaleCount']      = $female;
        $this->data['otherGenderCount'] = max(0, $total - $male - $female);

        // ── Sidebar live stats ────────────────────────────────────────────
        $this->data['sbActiveNow']     = $db->count("SELECT COUNT(*) AS c FROM appointments WHERE status IN('approved','in_progress')");
        $this->data['sbUnverified']    = $db->count("SELECT COUNT(*) AS c FROM students WHERE is_verified=0");
        $this->data['sbSessionsToday'] = $db->count("SELECT COUNT(*) AS c FROM sessions WHERE session_date='$t'");

        // ── Students list ─────────────────────────────────────────────────
        $this->data['students'] = $db->rows(
            "SELECT s.*,
               (SELECT CASE WHEN COALESCE(SUM(sess.hours_covered),0)>=15 THEN 1 ELSE 0 END
                FROM sessions sess JOIN appointments a ON a.appointment_id=sess.appointment_id
                JOIN instructor_courses ic ON ic.course_id=a.course_id
                WHERE sess.student_id=s.student_id AND ic.course_type LIKE 'TDC%' AND sess.status='completed') AS tdc_done,
               (SELECT COUNT(*) FROM appointments a JOIN instructor_courses ic ON ic.course_id=a.course_id
                WHERE a.student_id=s.student_id AND ic.course_type LIKE 'PDC%' AND a.status='completed') AS pdc_done
             FROM students s ORDER BY s.created_at DESC"
        );

        // ── Instructors list ──────────────────────────────────────────────
        $this->data['instructors'] = $db->rows(
            "SELECT i.*,
               (SELECT COUNT(*) FROM instructor_courses WHERE instructor_id=i.instructor_id AND is_active=1) AS course_cnt,
               (SELECT ROUND(AVG(rating),1) FROM instructor_ratings WHERE instructor_id=i.instructor_id) AS avg_r,
               (SELECT COUNT(*) FROM appointments WHERE instructor_id=i.instructor_id) AS appt_cnt
             FROM instructors i ORDER BY i.fullname ASC"
        );

        // ── Appointments list ─────────────────────────────────────────────
        $this->data['appointments'] = $db->rows(
            "SELECT a.*,s.fullname AS sname,i.fullname AS iname,ic.course_type,
                    (SELECT SUM(hours_covered) FROM sessions WHERE appointment_id=a.appointment_id AND status='completed') AS hdone
             FROM appointments a
             JOIN students s ON s.student_id=a.student_id
             JOIN instructors i ON i.instructor_id=a.instructor_id
             JOIN instructor_courses ic ON ic.course_id=a.course_id
             ORDER BY a.requested_at DESC"
        );

        // ── Recent student logins ─────────────────────────────────────────
        $this->data['recentLogins'] = $db->rows(
            "SELECT slh.*,s.fullname FROM student_login_history slh
             JOIN students s ON s.student_id=slh.student_id
             ORDER BY slh.login_datetime DESC LIMIT 10"
        );

        // ── Failed login counts per student ───────────────────────────────
        $sFailMap = [];
        foreach ($db->rows("SELECT student_id,COUNT(*) AS fc FROM student_login_history WHERE status='failed' GROUP BY student_id") as $row) {
            $sFailMap[(int)$row['student_id']] = (int)$row['fc'];
        }
        $this->data['sFailMap'] = $sFailMap;

        // ── Announcement recipients ───────────────────────────────────────
        $this->data['stuRecipCount']     = $db->count("SELECT COUNT(*) AS c FROM students WHERE is_verified=1");
        $this->data['insRecipCount']     = $db->count("SELECT COUNT(*) AS c FROM instructors");
        $this->data['allStudentsList']   = $db->rows("SELECT student_id,fullname FROM students WHERE is_verified=1 ORDER BY fullname");
        $this->data['allInstructorsList']= $db->rows("SELECT instructor_id,fullname FROM instructors ORDER BY fullname");

        // ── Recent announcements ──────────────────────────────────────────
        $this->data['recentAnnouncements'] = $db->rows(
            "SELECT n.*,
               CASE WHEN n.user_type='student'    THEN (SELECT fullname FROM students    WHERE student_id=n.user_id)
                    WHEN n.user_type='instructor'  THEN (SELECT fullname FROM instructors WHERE instructor_id=n.user_id)
               END AS recip
             FROM notifications n WHERE n.type='announcement'
             ORDER BY n.created_at DESC LIMIT 50"
        );

        // ── Course breakdown (reports) ────────────────────────────────────
        $this->data['courseReport'] = $db->rows(
            "SELECT ic.course_type,COUNT(*) AS total,
                    SUM(a.status='completed') AS done,
                    SUM(a.status IN('approved','in_progress')) AS active,
                    SUM(a.status='pending') AS pending
             FROM appointments a JOIN instructor_courses ic ON ic.course_id=a.course_id
             GROUP BY ic.course_type ORDER BY ic.course_type"
        );

        // ── Current admin's own profile ─────────────────────────────────────
        $this->data['currentAdmin'] = $db->row("SELECT * FROM admins WHERE admin_id={$this->userId}");

        // ── Admins list (Admin Management) ──────────────────────────────────
        $this->data['allAdmins'] = $db->rows("SELECT * FROM admins ORDER BY fullname ASC");

        // ── Flash messages ────────────────────────────────────────────────
        $this->data['flash']    = $_SESSION['flash'] ?? null;    unset($_SESSION['flash']);
        $this->data['flashErr'] = $_SESSION['flash_err'] ?? null; unset($_SESSION['flash_err']);
    }

    // ── Private action methods (ENCAPSULATION) ────────────────────────────

    private function addInstructor(): void
    {
        $name  = $this->db->escape($_POST['fullname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $emailSafe = $this->db->escape($email);
        $tempPassword = $this->generateTempPassword();
        $pass  = password_hash($tempPassword, PASSWORD_BCRYPT);
        $this->db->query("INSERT INTO instructors (fullname,email,password) VALUES ('$name','$emailSafe','$pass')");
        $this->sendWelcomeEmail($email, $_POST['fullname'] ?? '', $tempPassword, '../instructor/instructor_portal.php', 'Instructor');
        $this->redirect(self::SELF_FILE . '?section=instructors', 'Instructor added and welcome email sent.');
    }

    private function deleteInstructor(): void
    {
        $id = (int)($_POST['instructor_id'] ?? 0);
        $this->db->query("DELETE FROM instructors WHERE instructor_id=$id");
        $this->redirect(self::SELF_FILE . '?section=instructors', 'Instructor deleted.');
    }

    private function deleteStudent(): void
    {
        $id = (int)($_POST['student_id'] ?? 0);
        $this->db->query("DELETE FROM students WHERE student_id=$id");
        $this->redirect(self::SELF_FILE . '?section=students', 'Student deleted.');
    }

    // ── Admin's own password change ────────────────────────────────────────
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

        $row = $this->db->row("SELECT password FROM admins WHERE admin_id=$uid");
        if (!$row || !password_verify($current, $row['password'])) {
            $this->redirect(self::SELF_FILE . '?section=profile', 'Current password is incorrect.', true);
        }

        $hashed = password_hash($new, PASSWORD_BCRYPT);
        $this->db->query("UPDATE admins SET password='$hashed' WHERE admin_id=$uid");
        $this->redirect(self::SELF_FILE . '?section=profile', 'Password changed successfully.');
    }

    // ── Student Management ───────────────────────────────────────────────
    /**
     * Generate a system-generated 6-digit numeric temporary password.
     * Never shown in the UI — only emailed once to the new account holder.
     */
    private function generateTempPassword(): string
    {
        return (string)random_int(100000, 999999);
    }

    /**
     * Send a "Welcome / Your Staff or Student Account" email containing the
     * temporary password, matching the OTP email design used elsewhere.
     */
    private function sendWelcomeEmail(string $email, string $fullname, string $tempPassword, string $portalUrl, string $roleLabel): bool
    {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['MAIL_USERNAME'] ?? '';
            $mail->Password   = $_ENV['MAIL_PASSWORD'] ?? '';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $_ENV['MAIL_PORT'] ?? 587;

            $fromEmail = $_ENV['MAIL_FROM_ADDRESS'] ?? $_ENV['MAIL_USERNAME'] ?? '';
            $fromName  = $_ENV['MAIL_FROM_NAME'] ?? 'MotoSafe Admin Security';

            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($email, $fullname);
            $mail->isHTML(true);
            $mail->Subject = "MotoSafe – Your $roleLabel Account";

            $safeEmail = htmlspecialchars($email);
            $safePass  = htmlspecialchars($tempPassword);
            $safeUrl   = htmlspecialchars($portalUrl);

            $mail->Body = "
            <div style='margin:0;padding:40px 20px;background-color:#0a0f1e;font-family:Arial,sans-serif;'>
              <div style='max-width:600px;margin:0 auto;background:linear-gradient(145deg,#0d1530 0%,#111a35 50%,#0a1228 100%);border-radius:16px;border:1px solid rgba(255,255,255,0.08);box-shadow:0 25px 60px rgba(0,0,0,0.6),0 0 0 1px rgba(255,193,7,0.08);overflow:hidden;'>
                <div style='height:4px;background:linear-gradient(90deg,#ffc107,#ff9800,#ffc107);'></div>
                <div style='padding:32px 40px 20px;text-align:center;'>
                  <div style='display:inline-block;background:linear-gradient(135deg,#1a2850,#0d1530);border:1px solid rgba(255,193,7,0.25);border-radius:12px;padding:10px 20px;'>
                    <span style='font-size:22px;font-weight:900;color:#ffffff;letter-spacing:-0.5px;'>Moto</span><span style='font-size:22px;font-weight:900;color:#ffc107;letter-spacing:-0.5px;'>Safe</span>
                  </div>
                </div>
                <div style='height:1px;margin:0 40px;background:linear-gradient(90deg,transparent,rgba(255,193,7,0.2),transparent);'></div>
                <div style='padding:32px 40px 16px;text-align:center;'>
                  <h2 style='margin:0 0 12px;color:#ffffff;font-size:24px;'>Welcome to MotoSafe</h2>
                  <p style='margin:0;color:#b8c4e0;font-size:15px;line-height:1.7;'>
                    Hello, an administrator has created a <strong style='color:#ffffff;'>$roleLabel</strong> account for you. Use the temporary password below to log in. You will be required to set a new password immediately after logging in.
                  </p>
                </div>
                <div style='padding:20px 40px;text-align:left;'>
                  <table style='width:100%;border-collapse:collapse;font-size:14px;color:#d3dcf3;'>
                    <tr><td style='padding:6px 0;color:#8ea0c8;'>Email</td><td style='padding:6px 0;text-align:right;'><a href='mailto:$safeEmail' style='color:#4da3ff;text-decoration:none;'>$safeEmail</a></td></tr>
                    <tr><td style='padding:6px 0;color:#8ea0c8;'>Temporary Password</td><td style='padding:6px 0;text-align:right;'><span style='background:rgba(255,193,7,0.12);border:1px solid rgba(255,193,7,0.3);color:#ffc107;padding:4px 10px;border-radius:6px;font-weight:700;letter-spacing:1px;'>$safePass</span></td></tr>
                  </table>
                </div>
                <div style='padding:0 40px 30px;text-align:center;'>
                  <a href='$safeUrl' style='display:inline-block;background:#ffffff;color:#0a0f1e;font-weight:700;padding:12px 28px;border-radius:10px;text-decoration:none;font-size:14px;'>Log In Now</a>
                </div>
                <div style='padding:0 40px 30px;text-align:center;'>
                  <div style='height:1px;background:linear-gradient(90deg,transparent,rgba(255,193,7,0.18),transparent);margin-bottom:18px;'></div>
                  <p style='margin:0;color:#7f90b3;font-size:12px;line-height:1.6;'>
                    For security, this temporary password should be changed as soon as you log in — you'll be prompted automatically.<br>
                    If you did not expect this email, please contact your administrator.
                  </p>
                </div>
              </div>
            </div>";

            return $mail->send();
        } catch (Exception $e) {
            return false;
        }
    }

    private function addStudent(): void
    {
        $name  = $this->db->escape($_POST['fullname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $emailSafe = $this->db->escape($email);
        $tempPassword = $this->generateTempPassword();
        $pass  = password_hash($tempPassword, PASSWORD_BCRYPT);

        $exists = $this->db->count("SELECT COUNT(*) AS c FROM students WHERE email='$emailSafe'");
        if ($exists > 0) {
            $this->redirect(self::SELF_FILE . '?section=students', 'A student with this email already exists.', true);
        }

        $this->db->query(
            "INSERT INTO students (fullname,email,password_hash,is_verified,is_active,created_at)
             VALUES ('$name','$emailSafe','$pass',1,1,NOW())"
        );

        $this->sendWelcomeEmail($email, $_POST['fullname'] ?? '', $tempPassword, '../student/student_portal.php', 'Student');
        $this->redirect(self::SELF_FILE . '?section=students', 'Student added and welcome email sent.');
    }

    private function editStudent(): void
    {
        $id     = (int)($_POST['student_id'] ?? 0);
        $name   = $this->db->escape($_POST['fullname'] ?? '');
        $email  = $this->db->escape($_POST['email'] ?? '');
        $mobile = $this->db->escape($_POST['mobile'] ?? '');
        $address= $this->db->escape($_POST['address'] ?? '');
        $gender = in_array($_POST['gender'] ?? '', ['Male','Female','Other']) ? $_POST['gender'] : '';

        $this->db->query(
            "UPDATE students SET fullname='$name',email='$email',mobile='$mobile',address='$address',gender='$gender'
             WHERE student_id=$id"
        );
        $this->redirect(self::SELF_FILE . '?section=students', 'Student updated.');
    }

    private function toggleStudent(): void
    {
        $id = (int)($_POST['student_id'] ?? 0);
        $this->db->query("UPDATE students SET is_active = IF(is_active=1,0,1) WHERE student_id=$id");
        $this->redirect(self::SELF_FILE . '?section=students', 'Student status updated.');
    }

    // ── Admin Management ─────────────────────────────────────────────────
    private function addAdmin(): void
    {
        $name  = $this->db->escape($_POST['fullname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $emailSafe = $this->db->escape($email);
        $tempPassword = $this->generateTempPassword();
        $pass  = password_hash($tempPassword, PASSWORD_BCRYPT);

        $exists = $this->db->count("SELECT COUNT(*) AS c FROM admins WHERE email='$emailSafe'");
        if ($exists > 0) {
            $this->redirect(self::SELF_FILE . '?section=admins', 'An admin with this email already exists.', true);
        }

        $this->db->query(
            "INSERT INTO admins (fullname,email,password,is_active,created_at) VALUES ('$name','$emailSafe','$pass',1,NOW())"
        );
        $this->sendWelcomeEmail($email, $_POST['fullname'] ?? '', $tempPassword, '../admin/admin_portal.php', 'Admin');
        $this->redirect(self::SELF_FILE . '?section=admins', 'Admin added and welcome email sent.');
    }

    private function editAdmin(): void
    {
        $id    = (int)($_POST['admin_id'] ?? 0);
        $name  = $this->db->escape($_POST['fullname'] ?? '');
        $email = $this->db->escape($_POST['email'] ?? '');
        $this->db->query("UPDATE admins SET fullname='$name',email='$email' WHERE admin_id=$id");
        $this->redirect(self::SELF_FILE . '?section=admins', 'Admin updated.');
    }

    private function toggleAdmin(): void
    {
        $id = (int)($_POST['admin_id'] ?? 0);
        if ($id === $this->userId) {
            $this->redirect(self::SELF_FILE . '?section=admins', 'You cannot deactivate your own account.', true);
        }
        $this->db->query("UPDATE admins SET is_active = IF(is_active=1,0,1) WHERE admin_id=$id");
        $this->redirect(self::SELF_FILE . '?section=admins', 'Admin status updated.');
    }

    private function deleteAdmin(): void
    {
        $id = (int)($_POST['admin_id'] ?? 0);
        if ($id === $this->userId) {
            $this->redirect(self::SELF_FILE . '?section=admins', 'You cannot delete your own account.', true);
        }
        $this->db->query("DELETE FROM admins WHERE admin_id=$id");
        $this->redirect(self::SELF_FILE . '?section=admins', 'Admin deleted.');
    }

    private function resetAdminPwd(): void
    {
        $id      = (int)($_POST['admin_id'] ?? 0);
        $newPass = $_POST['new_password'] ?? '';
        if (strlen($newPass) < 8) {
            $this->redirect(self::SELF_FILE . '?section=admins', 'New password must be at least 8 characters.', true);
        }
        $hashed = password_hash($newPass, PASSWORD_BCRYPT);
        $this->db->query("UPDATE admins SET password='$hashed' WHERE admin_id=$id");
        $this->redirect(self::SELF_FILE . '?section=admins', "Admin's password has been reset.");
    }

    /**
     * Recomputes every course_schedules.booked_count from the REAL count of
     * active appointments (pending/approved/in_progress) for that course+date.
     * Fixes any stale counters left behind by past bugs (e.g. deletions that
     * didn't decrement the slot counter) — safe to run anytime.
     */
    private function resyncScheduleSlots(): void
    {
        $rows = $this->db->rows("SELECT course_id, schedule_date FROM course_schedules");
        $fixed = 0;
        foreach ($rows as $r) {
            $courseId = (int)$r['course_id'];
            $date     = $r['schedule_date'];
            $real = $this->db->count(
                "SELECT COUNT(*) AS c FROM appointments
                 WHERE course_id=$courseId AND start_date='$date'
                   AND status IN('pending','approved','in_progress')"
            );
            $this->db->query("UPDATE course_schedules SET booked_count=$real WHERE course_id=$courseId AND schedule_date='$date'");
            $fixed++;
        }
        // Clean up any now-empty rows entirely so they stop cluttering the list.
        $this->db->query("DELETE FROM course_schedules WHERE booked_count=0");

        $this->redirect(self::SELF_FILE . '?section=appointments', "Resynced $fixed schedule slot(s) and removed empty entries.");
    }

    private function deleteAppointment(): void
    {
        $id = (int)($_POST['appointment_id'] ?? 0);
        $appt = $this->db->row("SELECT course_id, start_date, status FROM appointments WHERE appointment_id=$id");
        if ($appt) {
            // Only free the slot if it was actually counted (approved/in_progress/pending),
            // matching the same counter that was incremented at booking time.
            if (in_array($appt['status'], ['pending', 'approved', 'in_progress'])) {
                $this->db->query(
                    "UPDATE course_schedules SET booked_count=GREATEST(0,booked_count-1)
                     WHERE course_id={$appt['course_id']} AND schedule_date='{$appt['start_date']}'"
                );
            }
            $this->db->query("DELETE FROM appointments WHERE appointment_id=$id");
        }
        $this->redirect(self::SELF_FILE . '?section=appointments', 'Appointment deleted.');
    }

    private function forceComplete(): void
    {
        $id   = (int)($_POST['appointment_id'] ?? 0);
        $aRow = $this->db->row("SELECT * FROM appointments WHERE appointment_id=$id");
        if ($aRow) {
            $this->db->query("UPDATE appointments SET status='completed',completed_at=NOW() WHERE appointment_id=$id");
            $this->notify($aRow['student_id'], 'student', 'Your appointment has been marked complete by admin.', 'admin_action');
        }
        $this->redirect(self::SELF_FILE . '?section=appointments');
    }

    private function sendAnnouncement(): void
    {
        $msg      = $this->db->escape($_POST['message'] ?? '');
        $sendTo   = $_POST['send_to'] ?? 'all_students';
        $valid    = ['all_students','all_instructors','everyone','specific_student','specific_instructor'];
        if (!in_array($sendTo, $valid)) $sendTo = 'all_students';
        $specificStudentId    = (int)($_POST['specific_student_id'] ?? 0);
        $specificInstructorId = (int)($_POST['specific_instructor_id'] ?? 0);
        $count      = 0;

        if (in_array($sendTo, ['all_students','everyone'])) {
            foreach ($this->db->rows("SELECT student_id FROM students WHERE is_verified=1") as $s) {
                $this->db->query("INSERT INTO notifications (user_id,user_type,message,type,recipient_target) VALUES ({$s['student_id']},'student','📢 Admin: $msg','announcement','$sendTo')");
                $count++;
            }
        } elseif ($sendTo === 'specific_student' && $specificStudentId > 0) {
            $this->db->query("INSERT INTO notifications (user_id,user_type,message,type,recipient_target) VALUES ($specificStudentId,'student','📢 Admin: $msg','announcement','specific_student')");
            $count = 1;
        }

        if (in_array($sendTo, ['all_instructors','everyone'])) {
            foreach ($this->db->rows("SELECT instructor_id FROM instructors") as $i) {
                $this->db->query("INSERT INTO notifications (user_id,user_type,message,type,recipient_target) VALUES ({$i['instructor_id']},'instructor','📢 Admin: $msg','announcement','$sendTo')");
                $count++;
            }
        } elseif ($sendTo === 'specific_instructor' && $specificInstructorId > 0) {
            $this->db->query("INSERT INTO notifications (user_id,user_type,message,type,recipient_target) VALUES ($specificInstructorId,'instructor','📢 Admin: $msg','announcement','specific_instructor')");
            $count = 1;
        }

        $this->redirect(self::SELF_FILE . '?section=communication', "Announcement sent to {$count} recipient(s).");
    }

    private function assignStudent(): void
    {
        $studentId   = (int)($_POST['student_id']   ?? 0);
        $instructorId= (int)($_POST['instructor_id'] ?? 0);
        $courseId    = (int)($_POST['course_id']     ?? 0);
        $startDate   = $this->db->escape($_POST['start_date'] ?? '');
        $note        = $this->db->escape($_POST['admin_note'] ?? '');

        if (!$studentId || !$instructorId || !$courseId || !$startDate) {
            $this->redirect(self::SELF_FILE . '?section=workload', 'Missing required fields.', true);
        }

        // Get course details
        $course = $this->db->row("SELECT * FROM instructor_courses WHERE course_id=$courseId AND instructor_id=$instructorId AND is_active=1");
        if (!$course) {
            $this->redirect(self::SELF_FILE . '?section=workload', 'Course not found or inactive.', true);
        }

        // ── Block assign if student already has an ongoing appointment (any instructor) ──
        // Use Reassign instead for students who are already in an active course.
        $hasOngoing = $this->db->count(
            "SELECT COUNT(*) AS c FROM appointments
             WHERE student_id=$studentId AND status IN('pending','approved','in_progress')"
        );
        if ($hasOngoing > 0) {
            $this->redirect(self::SELF_FILE . '?section=workload',
                'This student already has an ongoing appointment. Use Reassign instead of Assign for students already in an active course.', true);
        }

        $tdcHoursLogged = (float)($this->db->scalar(
            "SELECT COALESCE(SUM(sess.hours_covered),0) AS h FROM sessions sess
             JOIN appointments a ON a.appointment_id=sess.appointment_id
             JOIN instructor_courses ic ON ic.course_id=a.course_id
             WHERE sess.student_id=$studentId AND ic.course_type LIKE 'TDC%' AND sess.status='completed'",
            'h'
        ) ?? 0);
        $tdcDone = $tdcHoursLogged >= 15 ? 1 : 0;
        if (stripos($course['course_type'], 'PDC') === 0 && !$tdcDone) {
            $this->redirect(self::SELF_FILE . '?section=workload', 'This student must complete TDC (15 hours) before booking PDC.', true);
        }
        if (stripos($course['course_type'], 'TDC') === 0 && $tdcDone) {
            $this->redirect(self::SELF_FILE . '?section=workload', 'This student has already completed the required 15 hours of TDC.', true);
        }

        // Check student not already booked with this instructor on overlapping dates
        $isTDC = stripos($course['course_type'], 'TDC') === 0;
        $isCombo = stripos($course['course_type'], 'Both') !== false;
        if ($isTDC)       { $hrs = 15; $packageDays = 3; }
        elseif ($isCombo) { $hrs = 16; $packageDays = 3; }
        else              { $hrs = 8;  $packageDays = 2; }
        $endDate = date('Y-m-d', strtotime($startDate . ' +' . ($packageDays - 1) . ' days'));

        if ($isTDC && !self::TESTING_MODE) {
            $day = date('N', strtotime($startDate));
            if ($day != 6) {
                $this->redirect(self::SELF_FILE . '?section=workload', 'TDC start date must be a Saturday. The 3-day package runs Saturday through Monday.', true);
            }
        }

        // PDC start-day rules (maximizes weekday usage, Sat-Sun-Mon reserved for TDC):
        // Car/Motorcycle (2-day): Tuesday or Thursday only. Both (3-day): Tuesday or
        // Wednesday only.
        if (!$isTDC && !self::TESTING_MODE) {
            $pdcDay = date('N', strtotime($startDate));
            if ($isCombo) {
                if ($pdcDay != 2 && $pdcDay != 3) {
                    $this->redirect(self::SELF_FILE . '?section=workload',
                        'PDC Both (Car & Motorcycle) can only start on a Tuesday or Wednesday.', true);
                }
            } else {
                if ($pdcDay != 2 && $pdcDay != 4) {
                    $this->redirect(self::SELF_FILE . '?section=workload',
                        'PDC Car or Motorcycle can only start on a Tuesday or Thursday.', true);
                }
            }
        }

        $conflict = $this->db->count(
            "SELECT COUNT(*) AS c FROM appointments a
             JOIN instructor_courses ic ON ic.course_id=a.course_id
             WHERE a.instructor_id=$instructorId AND a.student_id=$studentId
               AND a.status IN('pending','approved','in_progress')
               AND a.start_date <= '$endDate'
               AND DATE_ADD(a.start_date, INTERVAL (CASE WHEN ic.course_type LIKE 'TDC%' OR ic.course_type LIKE '%Both%' THEN 2 ELSE 1 END) DAY) >= '$startDate'"
        );
        if ($conflict > 0) {
            $this->redirect(self::SELF_FILE . '?section=workload', 'This student already has a booking with this instructor that overlaps this date range.', true);
        }

        // Check slot limit (checked only on the package start date)
        if (!empty($course['student_limit'])) {
            $slotLimit = (int)$course['student_limit'];
            $booked    = $this->db->count("SELECT COUNT(*) AS c FROM appointments
                WHERE course_id=$courseId AND start_date='$startDate'
                  AND status IN('pending','approved','in_progress')");
            if ($booked >= $slotLimit) {
                $this->redirect(self::SELF_FILE . '?section=workload',
                    "Date $startDate is fully booked ($slotLimit/$slotLimit). Choose a different date.", true);
            }
        }

        // ── Instructor-side conflict: any overlapping-date appointment with a
        // DIFFERENT course type blocks this assignment — EXCEPT TDC Face-to-Face
        // and TDC Online, which never block each other (independent formats). ──
        if ($isTDC) {
            $conflictClause = "ic.course_type NOT LIKE 'TDC%'";
        } else {
            $conflictClause = "ic.course_type != '" . $this->db->escape($course['course_type']) . "'";
        }
        $otherCourseConflict = $this->db->count(
            "SELECT COUNT(*) AS c FROM appointments a
             JOIN instructor_courses ic ON ic.course_id=a.course_id
             WHERE a.instructor_id=$instructorId
               AND a.status IN('approved','in_progress')
               AND $conflictClause
               AND a.start_date <= '$endDate'
               AND DATE_ADD(a.start_date, INTERVAL (CASE WHEN ic.course_type LIKE 'TDC%' OR ic.course_type LIKE '%Both%' THEN 2 ELSE 1 END) DAY) >= '$startDate'"
        );
        if ($otherCourseConflict > 0) {
            $this->redirect(self::SELF_FILE . '?section=workload',
                "This instructor already has a different course scheduled during $startDate–$endDate. Please choose a different date range.", true);
        }

        $price = (float)$course['price_per_hr']; // flat package price, not multiplied by hours

        // Insert appointment as already approved (admin is assigning directly)
        $adminNote = $note ? "Admin assigned: $note" : 'Assigned by admin.';
        $this->db->query("INSERT INTO appointments (student_id,instructor_id,course_id,start_date,total_hours,total_price,status,student_note,reviewed_at)
            VALUES ($studentId,$instructorId,$courseId,'$startDate',$hrs,$price,'approved','$adminNote',NOW())");

        // Update schedule slot counter
        $slotLimit = !empty($course['student_limit']) ? (int)$course['student_limit'] : 'NULL';
        $this->db->query("INSERT INTO course_schedules (course_id,instructor_id,schedule_date,booked_count,student_limit)
            VALUES ($courseId,$instructorId,'$startDate',1,$slotLimit)
            ON DUPLICATE KEY UPDATE booked_count=booked_count+1");

        // Notify both parties
        $student    = $this->db->row("SELECT fullname FROM students WHERE student_id=$studentId");
        $sname      = $student ? $this->db->escape($student['fullname']) : 'A student';
        $instructor = $this->db->row("SELECT fullname FROM instructors WHERE instructor_id=$instructorId");
        $iname      = $instructor ? $this->db->escape($instructor['fullname']) : 'An instructor';

        $this->notify($studentId,    'student',     "Admin has assigned you to {$iname} for {$course['course_type']} starting $startDate. The appointment is already approved.", 'admin_assign');
        $this->notify($instructorId, 'instructor',  "Admin has assigned student {$sname} to your {$course['course_type']} course starting $startDate.", 'admin_assign');

        $this->redirect(self::SELF_FILE . '?section=workload', "Student assigned to {$iname} successfully. Appointment is pre-approved.");
    }

    private function reassignAppointment(): void
    {
        $apptId      = (int)($_POST['appointment_id'] ?? 0);
        $oldInstrId  = (int)($_POST['old_instructor_id'] ?? 0);
        $newCourseId = (int)($_POST['course_id'] ?? 0);
        $startDate   = $this->db->escape($_POST['start_date'] ?? '');

        if (!$apptId || !$newCourseId || !$startDate) {
            $this->redirect(self::SELF_FILE . '?section=workload', 'Missing required fields for reassignment.', true);
        }

        // Current appointment + its course type
        $appt = $this->db->row(
            "SELECT a.*, ic.course_type AS old_course_type
             FROM appointments a
             JOIN instructor_courses ic ON ic.course_id = a.course_id
             WHERE a.appointment_id = $apptId"
        );
        if (!$appt || !in_array($appt['status'], ['pending', 'approved', 'in_progress'])) {
            $this->redirect(self::SELF_FILE . '?section=workload', 'Appointment not found or already finalized.', true);
        }

        // New course must be active
        $newCourse = $this->db->row("SELECT * FROM instructor_courses WHERE course_id = $newCourseId AND is_active = 1");
        if (!$newCourse) {
            $this->redirect(self::SELF_FILE . '?section=workload', 'Selected course not found or inactive.', true);
        }
        $newInstrId = (int)$newCourse['instructor_id'];

        // Course type must match (admin reassigns to the SAME course type on a new instructor)
        if ($newCourse['course_type'] !== $appt['old_course_type']) {
            $this->redirect(self::SELF_FILE . '?section=workload', 'New course type must match the original (' . $appt['old_course_type'] . ').', true);
        }

        $isTDC   = stripos($newCourse['course_type'], 'TDC') === 0;
        $isCombo = stripos($newCourse['course_type'], 'Both') !== false;
        if ($isTDC)        { $hours = 15; $packageDays = 3; }
        elseif ($isCombo)  { $hours = 16; $packageDays = 3; }
        else               { $hours = 8;  $packageDays = 2; }
        $endDate = date('Y-m-d', strtotime($startDate . ' +' . ($packageDays - 1) . ' days'));

        if (!self::TESTING_MODE) {
            if ($isTDC) {
                $day = date('N', strtotime($startDate));
                if ($day != 6) {
                    $this->redirect(self::SELF_FILE . '?section=workload', 'TDC start date must be a Saturday. The 3-day package runs Saturday through Monday.', true);
                }
            } else {
                $pdcDay = date('N', strtotime($startDate));
                if ($isCombo) {
                    if ($pdcDay != 2 && $pdcDay != 3) {
                        $this->redirect(self::SELF_FILE . '?section=workload', 'PDC Both (Car & Motorcycle) can only start on a Tuesday or Wednesday.', true);
                    }
                } else {
                    if ($pdcDay != 2 && $pdcDay != 4) {
                        $this->redirect(self::SELF_FILE . '?section=workload', 'PDC Car or Motorcycle can only start on a Tuesday or Thursday.', true);
                    }
                }
            }
        }

        $studentId = (int)$appt['student_id'];

        // No-op guard
        if ((int)$appt['instructor_id'] === $newInstrId && (int)$appt['course_id'] === $newCourseId && $appt['start_date'] === $startDate) {
            $this->redirect(self::SELF_FILE . '?section=workload', 'No changes made — same instructor, course, and date.', true);
        }

        // Conflict: student already booked with the new instructor on overlapping dates
        $conflict = $this->db->count(
            "SELECT COUNT(*) AS c FROM appointments a
             JOIN instructor_courses ic ON ic.course_id=a.course_id
             WHERE a.instructor_id=$newInstrId AND a.student_id=$studentId
               AND a.status IN('pending','approved','in_progress')
               AND a.appointment_id <> $apptId
               AND a.start_date <= '$endDate'
               AND DATE_ADD(a.start_date, INTERVAL (CASE WHEN ic.course_type LIKE 'TDC%' OR ic.course_type LIKE '%Both%' THEN 2 ELSE 1 END) DAY) >= '$startDate'"
        );
        if ($conflict > 0) {
            $this->redirect(self::SELF_FILE . '?section=workload', 'This student already has a booking with that instructor that overlaps this date range.', true);
        }

        // Slot limit check on new course/date
        if (!empty($newCourse['student_limit'])) {
            $slotLimit = (int)$newCourse['student_limit'];
            $booked = $this->db->count(
                "SELECT COUNT(*) AS c FROM appointments
                 WHERE course_id=$newCourseId AND start_date='$startDate'
                   AND status IN('pending','approved','in_progress')
                   AND appointment_id <> $apptId"
            );
            if ($booked >= $slotLimit) {
                $this->redirect(self::SELF_FILE . '?section=workload',
                    "New date is fully booked ($slotLimit/$slotLimit). Choose a different date.", true);
            }
        }

        // Instructor-side conflict: any overlapping-date appointment with a DIFFERENT
        // course type blocks this reassignment — except TDC Face-to-Face and TDC
        // Online, which never block each other (independent formats).
        if ($isTDC) {
            $conflictClause = "ic.course_type NOT LIKE 'TDC%'";
        } else {
            $conflictClause = "ic.course_type != '" . $this->db->escape($newCourse['course_type']) . "'";
        }
        $otherCourseConflict = $this->db->count(
            "SELECT COUNT(*) AS c FROM appointments a
             JOIN instructor_courses ic ON ic.course_id=a.course_id
             WHERE a.instructor_id=$newInstrId
               AND a.status IN('approved','in_progress')
               AND a.appointment_id <> $apptId
               AND $conflictClause
               AND a.start_date <= '$endDate'
               AND DATE_ADD(a.start_date, INTERVAL (CASE WHEN ic.course_type LIKE 'TDC%' OR ic.course_type LIKE '%Both%' THEN 2 ELSE 1 END) DAY) >= '$startDate'"
        );
        if ($otherCourseConflict > 0) {
            $this->redirect(self::SELF_FILE . '?section=workload',
                "The new instructor already has a different course scheduled during $startDate–$endDate. Please choose a different date range.", true);
        }

        $price = (float)$newCourse['price_per_hr']; // flat package price, not multiplied by hours

        // Decrement old schedule slot
        $oldCourseId = (int)$appt['course_id'];
        $oldDate     = $appt['start_date'];
        $this->db->query("UPDATE course_schedules SET booked_count=GREATEST(0,booked_count-1) WHERE course_id=$oldCourseId AND schedule_date='$oldDate'");

        // Move the appointment
        $this->db->query(
            "UPDATE appointments
             SET instructor_id=$newInstrId, course_id=$newCourseId, start_date='$startDate',
                 total_hours=$hours, total_price=$price
             WHERE appointment_id=$apptId"
        );

        // Increment new schedule slot
        $slotLimit = !empty($newCourse['student_limit']) ? (int)$newCourse['student_limit'] : 'NULL';
        $this->db->query(
            "INSERT INTO course_schedules (course_id,instructor_id,schedule_date,booked_count,student_limit)
             VALUES ($newCourseId,$newInstrId,'$startDate',1,$slotLimit)
             ON DUPLICATE KEY UPDATE booked_count=booked_count+1"
        );

        // Notify everyone involved
        $oldInstr = $this->db->row("SELECT fullname FROM instructors WHERE instructor_id=$oldInstrId");
        $newInstr = $this->db->row("SELECT fullname FROM instructors WHERE instructor_id=$newInstrId");
        $student  = $this->db->row("SELECT fullname FROM students WHERE student_id=$studentId");
        $oldName  = $oldInstr ? $this->db->escape($oldInstr['fullname']) : 'an instructor';
        $newName  = $newInstr ? $this->db->escape($newInstr['fullname']) : 'an instructor';
        $sName    = $student  ? $this->db->escape($student['fullname'])  : 'A student';
        $ctype    = $newCourse['course_type'];

        $this->notify($oldInstrId, 'instructor', "Admin reassigned student $sName's $ctype appointment to another instructor.", 'admin_reassign');
        $this->notify($newInstrId, 'instructor', "Admin assigned student $sName to your $ctype course starting $startDate.", 'admin_reassign');
        $this->notify($studentId, 'student', "Your $ctype appointment has been reassigned to $newName, starting $startDate.", 'admin_reassign');

        $this->redirect(self::SELF_FILE . '?section=workload', "Appointment reassigned from $oldName to $newName ($startDate).");
    }

    private function adminEditCourse(): void
    {
        $courseId  = (int)($_POST['course_id'] ?? 0);
        $priceRaw  = $_POST['price_per_hr'] ?? '';
        $limitRaw  = $_POST['student_limit'] ?? '';
        $desc      = trim($_POST['description'] ?? '');
        $reqs      = trim($_POST['requirements'] ?? '');

        $course = $this->db->row("SELECT ic.*,i.fullname AS iname FROM instructor_courses ic JOIN instructors i ON i.instructor_id=ic.instructor_id WHERE ic.course_id=$courseId");
        if (!$course) {
            $this->redirect(self::SELF_FILE . '?section=instructor_profile', 'Course not found.', true);
        }
        if ($priceRaw === '' || !is_numeric($priceRaw) || (float)$priceRaw < 0) {
            $this->redirect(self::SELF_FILE . "?section=instructor_profile&view_instructor={$course['instructor_id']}", 'Please provide a valid price.', true);
        }
        $price   = (float)$priceRaw;
        $limit   = ($limitRaw !== '' && (int)$limitRaw > 0) ? (int)$limitRaw : null;
        $descSql = $this->db->escape($desc);
        $reqsSql = $this->db->escape($reqs);
        $limitSql = $limit === null ? 'NULL' : $limit;

        // ── Compare against the OLD values so the notification only mentions
        // fields that actually changed, instead of always listing everything. ──
        $changes = [];
        $oldPrice = (float)$course['price_per_hr'];
        $oldLimit = !empty($course['student_limit']) ? (int)$course['student_limit'] : null;
        $oldDesc  = trim($course['description'] ?? '');
        $oldReqs  = trim($course['requirements'] ?? '');

        if (abs($oldPrice - $price) > 0.001) {
            $changes[] = 'price is now ₱' . number_format($price, 2);
        }
        if ($oldLimit !== $limit) {
            $changes[] = 'student limit is now ' . ($limit !== null ? "$limit/date" : 'unlimited');
        }
        if ($oldDesc !== $desc) {
            $changes[] = 'description was updated';
        }
        if ($oldReqs !== $reqs) {
            $changes[] = 'requirements were updated';
        }

        $this->db->query("UPDATE instructor_courses SET price_per_hr=$price,student_limit=$limitSql,description='$descSql',requirements='$reqsSql' WHERE course_id=$courseId");

        if (!empty($changes)) {
            $msg = "An admin updated your {$course['course_type']} course — " . implode(', ', $changes) . '.';
            $this->notify((int)$course['instructor_id'], 'instructor', $msg, 'admin_course_edit');
        }

        $this->redirect(self::SELF_FILE . "?section=instructor_profile&view_instructor={$course['instructor_id']}", 'Course updated' . (!empty($changes) ? ' and instructor notified.' : '.'));
    }

    private function adminEditSession(): void
    {
        $sessId  = (int)($_POST['session_id'] ?? 0);
        $gradeRaw = $_POST['grade'] ?? '';
        $hoursRaw = $_POST['hours_covered'] ?? '';

        $sess = $this->db->row(
            "SELECT sess.*, ic.course_type, a.student_id, a.appointment_id
             FROM sessions sess
             JOIN appointments a ON a.appointment_id = sess.appointment_id
             JOIN instructor_courses ic ON ic.course_id = a.course_id
             WHERE sess.session_id = $sessId AND sess.status='completed'"
        );
        if (!$sess) {
            $this->redirect(self::SELF_FILE . '?section=appointments', 'Session not found or not editable.', true);
        }
        if ($hoursRaw === '' || !is_numeric($hoursRaw) || (float)$hoursRaw <= 0) {
            $this->redirect(self::SELF_FILE . '?section=appointments', 'Please provide a valid number of hours.', true);
        }

        $isTDC = stripos($sess['course_type'], 'TDC') === 0;
        $hours = (float)$hoursRaw;
        $gradeSql = 'NULL';
        if ($isTDC && $gradeRaw !== '' && is_numeric($gradeRaw) && (int)$gradeRaw >= 1 && (int)$gradeRaw <= 100) {
            $gradeSql = (int)$gradeRaw;
        }

        $this->db->query("UPDATE sessions SET hours_covered=$hours,grade=$gradeSql WHERE session_id=$sessId");

        // Recompute TDC overall grade for the appointment if this was a TDC session
        if ($isTDC) {
            $og = (float)($this->db->scalar("SELECT ROUND(AVG(grade),1) AS ag FROM sessions WHERE appointment_id={$sess['appointment_id']} AND grade IS NOT NULL", 'ag') ?? 0);
            $ogSql = $og > 0 ? round($og) : 'NULL';
            $this->db->query("UPDATE appointments SET tdc_overall_grade=$ogSql WHERE appointment_id={$sess['appointment_id']}");
        }

        $this->notify((int)$sess['student_id'], 'student', "An admin corrected your {$sess['course_type']} session record.", 'admin_session_edit');
        $this->redirect(self::SELF_FILE . '?section=appointments', 'Session updated and student notified.');
    }

    private function markAdminRead(): void
    {
        $this->markNotificationsRead();
        $this->redirect(self::SELF_FILE . '?section=messages');
    }
}
