<?php
session_start();
require_once '../includes/db.php'; 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Jenssegers\Agent\Agent;
require __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();
date_default_timezone_set('Asia/Manila');

//========= Email OTP =====================
function sendOtpEmail($email, $otp, $type = 'signup') {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['MAIL_USERNAME'];
        $mail->Password = $_ENV['MAIL_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('abrotcarl@gmail.com', 'MotoSafe - Instructor Scheduling & Performance Monitoring');
        $mail->addAddress($email);
        $mail->isHTML(true);

        $template_start = "
        <div style='margin:0;padding:40px 20px;background-color:#0a0f1e;font-family:Arial,sans-serif;'>
          <div style='
            max-width:600px;margin:0 auto;
            background:linear-gradient(145deg,#0d1530 0%,#111a35 50%,#0a1228 100%);
            border-radius:16px;
            border:1px solid rgba(255,255,255,0.08);
            box-shadow:0 25px 60px rgba(0,0,0,0.6),0 0 0 1px rgba(255,193,7,0.08);
            overflow:hidden;
          '>
            <!-- top accent bar -->
            <div style='height:4px;background:linear-gradient(90deg,#ffc107,#ff9800,#ffc107);'></div>

            <!-- logo -->
            <div style='padding:32px 40px 20px;text-align:center;'>
              <div style='
                display:inline-block;
                background:linear-gradient(135deg,#1a2850,#0d1530);
                border:1px solid rgba(255,193,7,0.25);
                border-radius:12px;
                padding:10px 20px;
              '>
                <span style='font-size:22px;font-weight:900;color:#ffffff;letter-spacing:-0.5px;'>Moto</span><span style='font-size:22px;font-weight:900;color:#ffc107;letter-spacing:-0.5px;'>Safe</span>
              </div>
            </div>

            <!-- gold divider -->
            <div style='height:1px;margin:0 40px;background:linear-gradient(90deg,transparent,rgba(255,193,7,0.2),transparent);'></div>

            <!-- dynamic content -->
            <div style='padding:32px 40px 16px;text-align:center;'>
        ";
        $template_start = "
            <div style='margin:0;padding:24px 12px;background-color:#0a0f1e;font-family:Arial,sans-serif;'>
            <div style='
                max-width:600px;
                width:100%;
                margin:0 auto;
                background:linear-gradient(145deg,#0d1530 0%,#111a35 50%,#0a1228 100%);
                border-radius:16px;
                border:1px solid rgba(255,255,255,0.08);
                box-shadow:0 25px 60px rgba(0,0,0,0.6),0 0 0 1px rgba(255,193,7,0.08);
                overflow:hidden;
            '>
                <!-- top accent bar -->
                <div style='height:4px;background:linear-gradient(90deg,#ffc107,#ff9800,#ffc107);'></div>

                <!-- logo -->
                <div style='padding:28px 24px 18px;text-align:center;'>
                <div style='
                    display:inline-block;
                    background:linear-gradient(135deg,#1a2850,#0d1530);
                    border:1px solid rgba(255,193,7,0.25);
                    border-radius:12px;
                    padding:10px 20px;
                '>
                    <span style='font-size:22px;font-weight:900;color:#ffffff;letter-spacing:-0.5px;'>Moto</span><span style='font-size:22px;font-weight:900;color:#ffc107;letter-spacing:-0.5px;'>Safe</span>
                </div>
                </div>

                <!-- gold divider -->
                <div style='height:1px;margin:0 24px;background:linear-gradient(90deg,transparent,rgba(255,193,7,0.2),transparent);'></div>

                <!-- dynamic content -->
                <div style='padding:28px 24px 16px;text-align:center;'>
        ";
        $template_end = "
            </div>

            <!-- OTP box -->
            <div style='padding:8px 24px 24px;text-align:center;'>
            <div style='
                display:inline-block;
                width:100%;
                max-width:320px;
                background:linear-gradient(135deg,#1a2850,#0f1c3a);
                border:2px solid rgba(255,193,7,0.4);
                border-radius:14px;
                padding:20px 16px;
                box-sizing:border-box;
            '>
                <p style='color:rgba(255,193,7,0.7);font-size:11px;font-weight:700;letter-spacing:3px;text-transform:uppercase;margin:0 0 10px 0;'>VERIFICATION CODE</p>
                <p style='color:#ffc107;font-size:40px;font-weight:900;letter-spacing:10px;margin:0;text-shadow:0 0 30px rgba(255,193,7,0.4);line-height:1;word-break:break-all;'>$otp</p>
            </div>
            </div>

            <!-- expiry notice -->
            <div style='margin:0 24px 24px;background:rgba(255,193,7,0.06);border:1px solid rgba(255,193,7,0.15);border-radius:10px;padding:14px 16px;text-align:center;'>
            <p style='color:rgba(255,255,255,0.55);font-size:13px;margin:0;line-height:1.6;'>
                ⏳ This code expires in <strong style='color:#ffc107;'>10 minutes</strong>.<br>Do not share it with anyone.
            </p>
            </div>

            <!-- subtle divider -->
            <div style='height:1px;margin:0 24px;background:linear-gradient(90deg,transparent,rgba(255,255,255,0.08),transparent);'></div>

            <!-- footer -->
            <div style='padding:20px 24px 24px;text-align:center;'>
            <p style='color:rgba(255,255,255,0.3);font-size:11px;letter-spacing:1px;text-transform:uppercase;margin:0 0 4px 0;'>Driving School Management Platform</p>
            <p style='color:rgba(255,255,255,0.18);font-size:11px;margin:0;'>— The MotoSafe Support Team —</p>
            </div>

            <!-- bottom accent -->
            <div style='height:2px;background:linear-gradient(90deg,transparent,rgba(255,193,7,0.3),transparent);'></div>
            </div>
            </div>"
        ;

        switch ($type) {

            case 'signup':
                $mail->Subject = 'Welcome to MotoSafe – Email Verification';
                $mail->Body = $template_start . "
                    <div style='display:inline-block;background:rgba(255,193,7,0.1);border:1px solid rgba(255,193,7,0.3);border-radius:50px;padding:6px 16px;margin-bottom:18px;'>
                    <span style='color:#ffc107;font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;'>● NEW ACCOUNT</span>
                    </div>
                    <h1 style='color:#ffffff;font-size:22px;font-weight:900;margin:0 0 12px;letter-spacing:-0.5px;line-height:1.3;'>
                    Welcome to <span style='color:#ffc107;'>MotoSafe</span>
                    </h1>
                    <p style='color:rgba(255,255,255,0.55);font-size:15px;line-height:1.7;margin:0;'>
                    Thank you for signing up.<br>
                    Use the code below to verify your email and complete registration.
                    </p>
                " . $template_end;
                break;

            case 'login':
                $mail->Subject = 'MotoSafe – Login Verification Code';
                $mail->Body = $template_start . "
                    <div style='display:inline-block;background:rgba(59,130,246,0.1);border:1px solid rgba(59,130,246,0.3);border-radius:50px;padding:6px 16px;margin-bottom:18px;'>
                    <span style='color:#60a5fa;font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;'>● LOGIN ATTEMPT</span>
                    </div>
                    <h1 style='color:#ffffff;font-size:22px;font-weight:900;margin:0 0 12px;letter-spacing:-0.5px;line-height:1.3;'>
                    Verify Your <span style='color:#ffc107;'>Login</span>
                    </h1>
                    <p style='color:rgba(255,255,255,0.55);font-size:15px;line-height:1.7;margin:0 0 10px;'>
                    A login attempt was detected on your account.<br>
                    Enter the code below to confirm it was you.
                    </p>
                    <p style='color:rgba(255,100,100,0.8);font-size:13px;margin:0;line-height:1.6;'>
                    🔒 If this wasn't you, change your password immediately.
                    </p>
                " . $template_end;
                break;

            case 'password_reset':
                $mail->Subject = 'MotoSafe – Password Reset Request';
                $mail->Body = $template_start . "
                    <div style='display:inline-block;background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.25);border-radius:50px;padding:6px 16px;margin-bottom:18px;'>
                    <span style='color:#f87171;font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;'>● PASSWORD RESET</span>
                    </div>
                    <h1 style='color:#ffffff;font-size:22px;font-weight:900;margin:0 0 12px;letter-spacing:-0.5px;line-height:1.3;'>
                    Reset Your <span style='color:#ffc107;'>Password</span>
                    </h1>
                    <p style='color:rgba(255,255,255,0.55);font-size:15px;line-height:1.7;margin:0 0 10px;'>
                    We received a request to reset your password.<br>
                    Use the verification code below to proceed.
                    </p>
                    <p style='color:rgba(255,255,255,0.3);font-size:13px;margin:0;line-height:1.6;'>
                    Didn't request this? <a href='#' style='color:#ffc107;text-decoration:none;'>Contact MotoSafe Support</a> or ignore this email.
                    </p>
                " . $template_end;
                break;
        }
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $e->getMessage());
        return false;
    }
}

//========= Create OTP ====================
function createOtp($conn, $student_id, $email, $type) {
    $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $otp_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    try {
        error_log("CREATED OTP: " . $otp . " for " . $email . " type: " . $type);

        $invalidate_stmt = $conn->prepare("
            UPDATE student_otp_history
            SET is_used = 1
            WHERE student_id = ? AND otp_type = ? AND is_used = 0
        ");

        if (!$invalidate_stmt) {
            error_log("Invalidate prepare failed: " . $conn->error);
            return false;
        }

        $invalidate_stmt->bind_param("is", $student_id, $type);
        $invalidate_stmt->execute();

        $stmt = $conn->prepare("
            INSERT INTO student_otp_history
            (student_id, otp, otp_type, otp_expiry, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");

        if (!$stmt) {
            error_log("Insert prepare failed: " . $conn->error);
            return false;
        }

        $stmt->bind_param("isss", $student_id, $otp, $type, $otp_expiry);

        if ($stmt->execute() && sendOtpEmail($email, $otp, $type)) {
            return true;
        }

        error_log("OTP insert or email send failed.");
        return false;

    } catch (Exception $e) {
        error_log("createOtp exception: " . $e->getMessage());
        return false;
    }
}

//========= Verify OTP ====================
function verifyOtp($conn, $email, $otp, $type) {
    error_log("Verifying OTP - Email: $email, Type: $type, OTP: $otp");

    $stmt = $conn->prepare("
        SELECT 
            s.student_id,
            s.fullname,
            o.otp_history_id,
            o.otp_expiry,
            o.is_used,
            o.created_at
        FROM students s
        INNER JOIN student_otp_history o ON s.student_id = o.student_id
        WHERE s.email = ?
          AND o.otp = ?
          AND o.otp_type = ?
          AND o.is_used = 0
          AND o.otp_expiry > NOW()
        ORDER BY o.created_at DESC
        LIMIT 1
    ");

    if (!$stmt) {
        error_log("Prepare failed in verifyOtp: " . $conn->error);
        return false;
    }

    $stmt->bind_param("sss", $email, $otp, $type);

    if (!$stmt->execute()) {
        error_log("Execute failed in verifyOtp: " . $stmt->error);
        $stmt->close();
        return false;
    }

    $result = $stmt->get_result();
    $stmt->close();

    return $result;
}

if (isset($_POST['verify_otp'])) {
    $email = trim($_SESSION['verify_email'] ?? '');
    $otp   = preg_replace('/\D/', '', trim($_POST['otp'] ?? ''));
    $type  = trim($_SESSION['verification_type'] ?? '');

    error_log("Verification attempt - Type: $type, Email: $email, OTP: $otp");

    if ($email === '' || $otp === '' || $type === '') {
        $_SESSION['error'] = "Missing verification data.";
        header("Location: verify_otp.php");
        exit();
    }

    if (strlen($otp) !== 6) {
        $_SESSION['error'] = "OTP must be 6 digits.";
        header("Location: verify_otp.php");
        exit();
    }

    $allowedTypes = ['signup', 'login', 'password_reset'];
    if (!in_array($type, $allowedTypes, true)) {
        $_SESSION['error'] = "Invalid verification type.";
        header("Location: verify_otp.php");
        exit();
    }

    $result = verifyOtp($conn, $email, $otp, $type);

    if ($result && $result->num_rows === 1) {
        $student = $result->fetch_assoc();

        $conn->begin_transaction();

        try {
            $update_stmt = $conn->prepare("
                UPDATE student_otp_history
                SET is_used = 1
                WHERE otp_history_id = ?
            ");

            if (!$update_stmt) {
                throw new Exception("Failed to prepare OTP update: " . $conn->error);
            }

            $otpHistoryId = (int)$student['otp_history_id'];
            $update_stmt->bind_param("i", $otpHistoryId);

            if (!$update_stmt->execute()) {
                throw new Exception("Failed to update OTP usage: " . $update_stmt->error);
            }

            $update_stmt->close();

            switch ($type) {
                case 'login':
                    $_SESSION['student_id'] = $student['student_id'];
                    $_SESSION['fullname']   = $student['fullname'];

                    $device_info = getDeviceInfo();
                    $ip_address = getStudentIP();
                    recordLoginAttempt($conn, $student['student_id'], $ip_address, $device_info, 'success');

                    unset($_SESSION['login_verification']);
                    unset($_SESSION['verify_email']);
                    unset($_SESSION['verification_type']);

                    $conn->commit();
                    header("Location: student_dashboard.php");
                    exit();

                case 'signup':
                    $verify_stmt = $conn->prepare("
                        UPDATE students
                        SET is_verified = 1
                        WHERE student_id = ?
                    ");

                    if (!$verify_stmt) {
                        throw new Exception("Failed to prepare student verification update: " . $conn->error);
                    }

                    $studentId = (int)$student['student_id'];
                    $verify_stmt->bind_param("i", $studentId);

                    if (!$verify_stmt->execute()) {
                        throw new Exception("Failed to update student verification: " . $verify_stmt->error);
                    }

                    $verify_stmt->close();

                    unset($_SESSION['verify_email']);
                    unset($_SESSION['verification_type']);

                    $conn->commit();

                    $_SESSION['success'] = "Account verified successfully. Please login.";
                    header("Location: student_portal.php");
                    exit();

                    case 'password_reset':
                        $_SESSION['password_reset_verified'] = true;
                        $_SESSION['password_reset_email'] = $email;

                        unset($_SESSION['verify_email']);
                        unset($_SESSION['verification_type']);
                        unset($_SESSION['password_reset']);

                        $conn->commit();
                        header("Location: student_reset_password.php");
                        exit();

                default:
                    throw new Exception("Unsupported verification type.");
            }

        } catch (Exception $e) {
            $conn->rollback();
            error_log("Verification failed: " . $e->getMessage());

            $_SESSION['error'] = "Verification failed. Please try again.";
            header("Location: verify_otp.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Invalid or expired OTP. Please check the code or request a new one.";
        header("Location: verify_otp.php");
        exit();
    }
}

//========= Signup =====================
if (isset($_POST['student_register'])) {

    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match";
        header("Location: ../index.php");
        exit();
    }

    // check if email exists
    $stmt = $conn->prepare("SELECT student_id FROM students WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    if ($stmt->get_result()->num_rows > 0) {
        $_SESSION['error'] = "Email already exists";
        header("Location: student_portal.php");
        exit();
    }

    $conn->begin_transaction();

    try {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("
            INSERT INTO students (fullname, email, password_hash, is_verified)
            VALUES (?, ?, ?, 0)
        ");

        $stmt->bind_param("sss", $fullname, $email, $hashed_password);

        if ($stmt->execute()) {

            $student_id = $conn->insert_id;

            $_SESSION['verify_email'] = $email;
            $_SESSION['verification_type'] = 'signup';
            $_SESSION['pending_verification'] = true;

            if (createOtp($conn, $student_id, $email, 'signup')) {

                $conn->commit();

                $_SESSION['success'] = "Account created successfully. Please verify your email.";

                header("Location: verify_otp.php");
                exit();
            } 
            else {
                throw new Exception("OTP creation failed");
            }

        } else {
            throw new Exception("Student insert failed");
        }

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Registration failed. Please try again.";
        header("Location: ../index.php");
        exit();
    }
}

//========= Resent OTP =================
if (isset($_POST['resend_otp'])) {
    $email = $_SESSION['verify_email'] ?? '';
    $type = $_SESSION['verification_type'] ?? 'signup';

    if (empty($email)) {
        $_SESSION['error'] = "Session expired. Please try again.";
        header("Location: verify_otp.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT student_id FROM students WHERE email = ?");
    if (!$stmt) {
        $_SESSION['error'] = "Database error.";
        header("Location: verify_otp.php");
        exit();
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $student_id = $row['student_id'];

        if (createOtp($conn, $student_id, $email, $type)) {
            $_SESSION['success'] = "A new OTP has been sent to your email.";
        } else {
            $_SESSION['error'] = "Failed to resend OTP.";
        }
    } else {
        $_SESSION['error'] = "Student account not found.";
    }

    header("Location: verify_otp.php");
    exit();
}

//========= IP Address =================
function getStudentIP() {
    $ip = 'UNKNOWN';
    $ipKeys = [
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    ];

    foreach ($ipKeys as $key) {
        if (!empty($_SERVER[$key])) {
            $ipList = explode(',', $_SERVER[$key]);
            foreach ($ipList as $ipEntry) {
                $ipEntry = trim($ipEntry);
                if (filter_var($ipEntry, FILTER_VALIDATE_IP)) {
                    $ip = $ipEntry;
                    break 2;
                }
            }
        }
    }

    return ($ip === '::1') ? '127.0.0.1' : $ip;
}
 
//========= Device Info =================
function getDeviceInfo() {
    $agent = new Agent();

    $device = $agent->device(); // e.g., iPhone
    $platform = $agent->platform(); // e.g., Windows
    $platformVersion = $agent->version($platform);
    $browser = $agent->browser(); // e.g., Chrome
    $browserVersion = $agent->version($browser);

    return "$device | $platform $platformVersion | $browser $browserVersion";
}

// ========= Helper Functions ============
function checkLoginAttempts($conn, $student_id) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS attempt_count
        FROM student_login_history
        WHERE status = 'failed'
          AND student_id = ?
          AND login_datetime > DATE_SUB(NOW(), INTERVAL 1 MINUTE)
    ");

    if (!$stmt) {
        $_SESSION['error'] = "System error while checking login attempts.";
        return 0;
    }

    $stmt->bind_param("i", $student_id);

    if (!$stmt->execute()) {
        $_SESSION['error'] = "System error while checking login attempts.";
        $stmt->close();
        return 0;
    }

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    return (int)($row['attempt_count'] ?? 0);
}

function recordLoginAttempt($conn, $student_id, $ip_address, $device_info, $status) {
    $stmt = $conn->prepare("
        INSERT INTO student_login_history
        (student_id, ip_address, device_info, status, login_datetime)
        VALUES (?, ?, ?, ?, NOW())
    ");

    if (!$stmt) {
        $_SESSION['error'] = "System error while saving login history.";
        return false;
    }

    $stmt->bind_param("isss", $student_id, $ip_address, $device_info, $status);
    $success = $stmt->execute();
    $stmt->close();

    return $success;
}

function clearLoginAttempts($conn, $student_id) {
    $stmt = $conn->prepare("
        DELETE FROM student_login_history
        WHERE student_id = ?
          AND status = 'failed'
    ");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("i", $student_id);
    $success = $stmt->execute();
    $stmt->close();

    return $success;
}

function recordLockout($conn, $student_id, $ip_address, $device_info, $lockout_type = '1min') {
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS recent
        FROM student_lockout_history
        WHERE student_id = ?
          AND lockout_time > DATE_SUB(NOW(), INTERVAL 1 MINUTE)
    ");

    if (!$stmt) {
        $_SESSION['error'] = "System error while recording lockout.";
        return false;
    }

    $stmt->bind_param("i", $student_id);

    if (!$stmt->execute()) {
        $_SESSION['error'] = "System error while recording lockout.";
        $stmt->close();
        return false;
    }

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if ((int)($row['recent'] ?? 0) === 0) {
        $stmt = $conn->prepare("
            INSERT INTO student_lockout_history
            (student_id, ip_address, device_info, lockout_type, lockout_time)
            VALUES (?, ?, ?, ?, NOW())
        ");

        if (!$stmt) {
            $_SESSION['error'] = "System error while saving lockout history.";
            return false;
        }

        $stmt->bind_param("isss", $student_id, $ip_address, $device_info, $lockout_type);

        if (!$stmt->execute()) {
            $_SESSION['error'] = "System error while saving lockout history.";
            $stmt->close();
            return false;
        }

        $stmt->close();
    }

    return true;
}

function isStudentLockedOut($conn, $student_id) {
    $stmt = $conn->prepare("
        SELECT lockout_type, lockout_time
        FROM student_lockout_history
        WHERE student_id = ?
        ORDER BY lockout_time DESC
        LIMIT 1
    ");

    if (!$stmt) {
        $_SESSION['error'] = "System error while checking account lockout.";
        return false;
    }

    $stmt->bind_param("i", $student_id);

    if (!$stmt->execute()) {
        $_SESSION['error'] = "System error while checking account lockout.";
        $stmt->close();
        return false;
    }

    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $stmt->close();

        $lockout_time = strtotime($row['lockout_time']);
        $lockout_type = $row['lockout_type'];

        if ($lockout_type === '1hr' && (time() - $lockout_time) < 3600) {
            return true;
        }

        if ($lockout_type === '1min' && (time() - $lockout_time) < 60) {
            return true;
        }
    } else {
        $stmt->close();
    }

    return false;
}

function countRecentLockouts($conn, $student_id) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM student_lockout_history
        WHERE student_id = ?
          AND lockout_time > DATE_SUB(NOW(), INTERVAL 10 MINUTE)
          AND lockout_type = '1min'
    ");

    if (!$stmt) {
        $_SESSION['error'] = "System error while checking recent lockouts.";
        return 0;
    }

    $stmt->bind_param("i", $student_id);

    if (!$stmt->execute()) {
        $_SESSION['error'] = "System error while checking recent lockouts.";
        $stmt->close();
        return 0;
    }

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    return (int)($row['total'] ?? 0);
}

//========= Student Login =================
if (isset($_POST['student_login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Email and password are required.";
        header("Location: student_portal.php");
        exit();
    }

    $device_info = getDeviceInfo();
    $ip_address = getStudentIP();

    $stmt = $conn->prepare("SELECT * FROM students WHERE email = ? LIMIT 1");

    if (!$stmt) {
        $_SESSION['error'] = "System error. Please try again later.";
        header("Location: student_portal.php");
        exit();
    }

    $stmt->bind_param("s", $email);

    if (!$stmt->execute()) {
        $stmt->close();
        $_SESSION['error'] = "System error. Please try again later.";
        header("Location: student_portal.php");
        exit();
    }

    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        $stmt->close();
        $_SESSION['error'] = "Invalid email or password.";
        header("Location: student_portal.php");
        exit();
    }

    $student = $result->fetch_assoc();
    $stmt->close();

    $student_id = (int)$student['student_id'];

    // Check if account is locked
    if (isStudentLockedOut($conn, $student_id)) {
        $_SESSION['error'] = "Your account is locked due to too many failed login attempts.";
        header("Location: student_portal.php");
        exit();
    }

    // Check password first
    if (!password_verify($password, $student['password_hash'])) {
        recordLoginAttempt($conn, $student_id, $ip_address, $device_info, 'failed');

        $attempts = checkLoginAttempts($conn, $student_id);

        if ($attempts >= 5) {
            $recentLockouts = countRecentLockouts($conn, $student_id);
            $lockoutType = ($recentLockouts >= 3) ? '1hr' : '1min';

            recordLockout($conn, $student_id, $ip_address, $device_info, $lockoutType);

            $_SESSION['error'] = ($lockoutType === '1hr')
                ? "Account locked for 1 hour due to repeated failed attempts."
                : "Account locked temporarily. Try again in 1 minute.";

            header("Location: student_portal.php");
            exit();
        }

        $_SESSION['error'] = "Invalid email or password.";
        header("Location: student_portal.php");
        exit();
    }

    // If password is correct but account is not verified
    if ((int)$student['is_verified'] !== 1) {
        $_SESSION['verify_email'] = $student['email'];
        $_SESSION['verification_type'] = 'signup';
        $_SESSION['pending_verification'] = true;

        if (createOtp($conn, $student_id, $student['email'], 'signup')) {
            $_SESSION['success'] = "Your account is not yet verified. We sent a verification OTP to your email.";
            header("Location: verify_otp.php");
            exit();
        } else {
            $_SESSION['error'] = "Your account is not verified, and we could not send a verification OTP. Please try again.";
            header("Location: student_portal.php");
            exit();
        }
    }

    // Verified account: continue login OTP
    clearLoginAttempts($conn, $student_id);

    $_SESSION['verify_email'] = $student['email'];
    $_SESSION['verification_type'] = 'login';
    $_SESSION['login_verification'] = true;

    if (createOtp($conn, $student_id, $student['email'], 'login')) {
        $_SESSION['success'] = "OTP sent to your email. Please verify your login.";
        header("Location: verify_otp.php");
        exit();
    } else {
        $_SESSION['error'] = "Failed to send OTP. Please try again.";
        header("Location: student_portal.php");
        exit();
    }
}

//========= Forgot Password ===============
if (isset($_POST['forgot_password'])) {
    $email = trim($_POST['email']);
    
    $stmt = $conn->prepare("SELECT student_id FROM students WHERE email = ? AND is_verified = 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION['verification_type'] = 'password_reset'; 
        if (createOtp($conn, $user['student_id'], $email, 'password_reset')) {
            $_SESSION['verify_email'] = $email;
            $_SESSION['password_reset'] = true;
            header("Location: verify_otp.php");
            exit();
        }
    }
    
    $_SESSION['error'] = "The email you entered is not registered. Please try again.";
    header("Location: forgot_password.php");
    exit();
}

//========= Reset Password ================
if (isset($_POST['reset_password'])) {
    if (
        !isset($_SESSION['password_reset_verified']) ||
        $_SESSION['password_reset_verified'] !== true ||
        !isset($_SESSION['password_reset_email'])
    ) {
        $_SESSION['error'] = "Unauthorized password reset attempt.";
        header("Location: forgot_password.php");
        exit();
    }

    $email = trim($_SESSION['password_reset_email']);
    $new_password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    if (empty($new_password) || empty($confirm_password)) {
        $_SESSION['error'] = "Please fill in all password fields.";
        header("Location: reset_password.php");
        exit();
    }

    // Validate password format
    $password_pattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/";
    if (!preg_match($password_pattern, $new_password)) {
        $_SESSION['error'] = "Password must contain at least 8 characters, one uppercase, one lowercase, one number, and one special character.";
        header("Location: reset_password.php");
        exit();
    }

    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: reset_password.php");
        exit();
    }

    $conn->begin_transaction();

    try {
        // Get student
        $student_stmt = $conn->prepare("SELECT student_id FROM students WHERE email = ? LIMIT 1");
        if (!$student_stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $student_stmt->bind_param("s", $email);

        if (!$student_stmt->execute()) {
            throw new Exception("Execute failed: " . $student_stmt->error);
        }

        $student_result = $student_stmt->get_result();
        $student = $student_result->fetch_assoc();
        $student_stmt->close();

        if (!$student) {
            throw new Exception("Student not found.");
        }

        $student_id = (int)$student['student_id'];

        // Hash and update password_hash column
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        $update_stmt = $conn->prepare("UPDATE students SET password_hash = ? WHERE student_id = ?");
        if (!$update_stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $update_stmt->bind_param("si", $hashed_password, $student_id);

        if (!$update_stmt->execute()) {
            throw new Exception("Failed to update password: " . $update_stmt->error);
        }

        $update_stmt->close();

        // Mark all unused password reset OTPs as used
        $clear_otp_stmt = $conn->prepare("
            UPDATE student_otp_history
            SET is_used = 1
            WHERE student_id = ?
              AND otp_type = 'password_reset'
              AND is_used = 0
        ");
        if (!$clear_otp_stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $clear_otp_stmt->bind_param("i", $student_id);

        if (!$clear_otp_stmt->execute()) {
            throw new Exception("Failed to update OTP history: " . $clear_otp_stmt->error);
        }

        $clear_otp_stmt->close();

        // Log password reset event
        $ip_address = getStudentIP();
        $device_info = getDeviceInfo();

        $log_stmt = $conn->prepare("
            INSERT INTO student_password_history (student_id, change_type, ip_address, device_info)
            VALUES (?, 'reset', ?, ?)
        ");
        if (!$log_stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $log_stmt->bind_param("iss", $student_id, $ip_address, $device_info);

        if (!$log_stmt->execute()) {
            throw new Exception("Failed to log password history: " . $log_stmt->error);
        }

        $log_stmt->close();

        $conn->commit();

        // Clear reset session
        unset($_SESSION['password_reset_verified']);
        unset($_SESSION['password_reset_email']);
        unset($_SESSION['password_reset']);
        unset($_SESSION['verify_email']);
        unset($_SESSION['verification_type']);

        $_SESSION['success'] = "Password reset successfully. Please login with your new password.";
        header("Location: student_portal.php");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Password reset failed: " . $e->getMessage());
        $_SESSION['error'] = "Failed to reset password. Please try again.";
        header("Location: reset_password.php");
        exit();
    }
}
?>