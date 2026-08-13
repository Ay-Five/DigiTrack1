<?php
session_start();

require_once '../includes/db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

date_default_timezone_set('Asia/Manila');

//============ Location =================
function redirectTo($location) {
    header("Location: {$location}");
    exit();
}

//============ Create OTP =================
function generateOtp($length = 6) {
    return str_pad((string) random_int(0, 999999), $length, '0', STR_PAD_LEFT);
}

//============ Login History =================
function saveLoginHistory(
    $conn,
    $instructor_id,
    $ip_address,
    $user_agent,
    $latitude,
    $longitude,
    $city,
    $region,
    $country,
    $maps_link,
    $login_status,
    $remarks
) {
    $sql = "INSERT INTO instructor_login_history
            (instructor_id, ip_address, user_agent, latitude, longitude, city, region, country, maps_link, login_status, remarks)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "issssssssss",
        $instructor_id,
        $ip_address,
        $user_agent,
        $latitude,
        $longitude,
        $city,
        $region,
        $country,
        $maps_link,
        $login_status,
        $remarks
    );
    $stmt->execute();
    $stmt->close();
}

//============ Password History =================
function savePasswordHistory(
    $conn,
    $instructor_id,
    $change_type,
    $ip_address,
    $user_agent,
    $latitude,
    $longitude,
    $city,
    $region,
    $country,
    $maps_link
) {
    $checkTable = $conn->query("SHOW TABLES LIKE 'instructor_password_history'");

    if ($checkTable && $checkTable->num_rows > 0) {
        $sql = "INSERT INTO instructor_password_history
                (instructor_id, change_type, ip_address, user_agent, latitude, longitude, city, region, country, maps_link)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "isssssssss",
            $instructor_id,
            $change_type,
            $ip_address,
            $user_agent,
            $latitude,
            $longitude,
            $city,
            $region,
            $country,
            $maps_link
        );
        $stmt->execute();
        $stmt->close();
    }
}

//============ OTP History =================
function saveOtpHistory($conn, $instructor_id, $otp, $purpose, $expires_at, $status = 'sent') {
    $checkOtpTable = $conn->query("SHOW TABLES LIKE 'instructor_otp_history'");

    if ($checkOtpTable && $checkOtpTable->num_rows > 0) {
        $sql = "INSERT INTO instructor_otp_history
                (instructor_id, otp_code, purpose, expires_at, status)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issss", $instructor_id, $otp, $purpose, $expires_at, $status);
        $stmt->execute();
        $stmt->close();
    }
}

//============ OTP History Status =================
function updateOtpHistoryStatus($conn, $instructor_id, $otp, $status, $purpose = 'login_verification', $verified = false) {
    $checkOtpTable = $conn->query("SHOW TABLES LIKE 'instructor_otp_history'");

    if (!($checkOtpTable && $checkOtpTable->num_rows > 0)) {
        return;
    }

    if ($verified) {
        $sql = "UPDATE instructor_otp_history
                SET status = ?, verified_at = NOW()
                WHERE instructor_id = ? AND otp_code = ? AND purpose = ? AND status = 'sent'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("siss", $status, $instructor_id, $otp, $purpose);
    } else {
        $sql = "UPDATE instructor_otp_history
                SET status = ?
                WHERE instructor_id = ? AND otp_code = ? AND purpose = ? AND status = 'sent'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("siss", $status, $instructor_id, $otp, $purpose);
    }

    $stmt->execute();
    $stmt->close();
}

//============ OTP Email =================
function sendOtpEmail($email, $fullname, $otp, $type = 'login') {
    $mail = new PHPMailer(true);

    if ($type === 'login') {
        $subject = "MotoSafe Instructor Login OTP";
        $heading = "Instructor Login Verification";
        $description = "Use the OTP below to complete your <strong style='color:#ffffff;'>MotoSafe Instructor Portal</strong> login.";
    } elseif ($type === 'reset') {
        $subject = "MotoSafe Instructor Password Reset OTP";
        $heading = "Password Reset Verification";
        $description = "Use the OTP below to reset your <strong style='color:#ffffff;'>MotoSafe Instructor Portal</strong> password.";
    } else {
        $subject = "MotoSafe Instructor OTP";
        $heading = "Instructor Verification";
        $description = "Use the OTP below to continue your verification.";
    }

    $body = "
    <div style='margin:0;padding:40px 20px;background-color:#0a0f1e;font-family:Arial,sans-serif;'>
      <div style='
        max-width:600px;
        margin:0 auto;
        background:linear-gradient(145deg,#0d1530 0%,#111a35 50%,#0a1228 100%);
        border-radius:16px;
        border:1px solid rgba(255,255,255,0.08);
        box-shadow:0 25px 60px rgba(0,0,0,0.6),0 0 0 1px rgba(255,193,7,0.08);
        overflow:hidden;
      '>
        <div style='height:4px;background:linear-gradient(90deg,#ffc107,#ff9800,#ffc107);'></div>

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

        <div style='height:1px;margin:0 40px;background:linear-gradient(90deg,transparent,rgba(255,193,7,0.2),transparent);'></div>

        <div style='padding:32px 40px 16px;text-align:center;'>
          <h2 style='margin:0 0 12px;color:#ffffff;font-size:24px;'>{$heading}</h2>
          <p style='margin:0;color:#b8c4e0;font-size:15px;line-height:1.7;'>{$description}</p>
        </div>

        <div style='padding:10px 40px 30px;text-align:center;'>
          <div style='
            display:inline-block;
            padding:16px 30px;
            border-radius:14px;
            background:rgba(255,193,7,0.08);
            border:1px solid rgba(255,193,7,0.25);
            color:#ffc107;
            font-size:34px;
            font-weight:800;
            letter-spacing:8px;
          '>{$otp}</div>

          <p style='margin:20px 0 0;color:#d3dcf3;font-size:14px;line-height:1.7;'>
            This OTP is valid for <strong style='color:#ffffff;'>10 minutes</strong> only.
          </p>

          <p style='margin:14px 0 0;color:#8ea0c8;font-size:13px;line-height:1.7;'>
            If this wasn't you, please secure the account immediately.
          </p>
        </div>

        <div style='padding:0 40px 30px;text-align:center;'>
          <div style='height:1px;background:linear-gradient(90deg,transparent,rgba(255,193,7,0.18),transparent);margin-bottom:18px;'></div>
          <p style='margin:0;color:#7f90b3;font-size:12px;line-height:1.6;'>
            This is an automated security message from MotoSafe.
          </p>
        </div>
      </div>
    </div>";

    try {
        $mail->isSMTP();
        $mail->Host       = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['MAIL_USERNAME'];
        $mail->Password   = $_ENV['MAIL_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $_ENV['MAIL_PORT'] ?? 587;

        $fromEmail = $_ENV['MAIL_FROM_ADDRESS'] ?? $_ENV['MAIL_USERNAME'];
        $fromName  = $_ENV['MAIL_FROM_NAME'] ?? 'MotoSafe Instructor Security';

        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($email, $fullname);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        return $mail->send();
    } catch (Exception $e) {
        return false;
    }
}

//============ Instructor Login =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['instructor_login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $latitude = isset($_POST['latitude']) && $_POST['latitude'] !== '' ? trim($_POST['latitude']) : null;
    $longitude = isset($_POST['longitude']) && $_POST['longitude'] !== '' ? trim($_POST['longitude']) : null;
    $city = isset($_POST['city']) && $_POST['city'] !== '' ? trim($_POST['city']) : null;
    $region = isset($_POST['region']) && $_POST['region'] !== '' ? trim($_POST['region']) : null;
    $country = isset($_POST['country']) && $_POST['country'] !== '' ? trim($_POST['country']) : null;
    $maps_link = isset($_POST['maps_link']) && $_POST['maps_link'] !== '' ? trim($_POST['maps_link']) : null;

    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';

    if ($email === '' || $password === '') {
        $_SESSION['error'] = "Please enter your email and password.";
        redirectTo("instructor_portal.php");
    }

    $stmt = $conn->prepare("SELECT instructor_id, fullname, email, password FROM instructors WHERE email = ? LIMIT 1");
    if (!$stmt) {
        $_SESSION['error'] = "Database error.";
        redirectTo("instructor_portal.php");
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        $stmt->close();
        $_SESSION['error'] = "Instructor account not found.";
        redirectTo("instructor_portal.php");
    }

    $instructor = $result->fetch_assoc();
    $stmt->close();

    if (!password_verify($password, $instructor['password'])) {
        saveLoginHistory(
            $conn,
            $instructor['instructor_id'],
            $ip_address,
            $user_agent,
            $latitude,
            $longitude,
            $city,
            $region,
            $country,
            $maps_link,
            'failed',
            'Wrong password'
        );

        $_SESSION['error'] = "Incorrect password.";
        redirectTo("instructor_portal.php");
    }

    $otp = generateOtp(6);
    $otp_expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    $_SESSION['pending_instructor_id'] = $instructor['instructor_id'];
    $_SESSION['pending_instructor_fullname'] = $instructor['fullname'];
    $_SESSION['pending_instructor_email'] = $instructor['email'];
    $_SESSION['pending_instructor_latitude'] = $latitude;
    $_SESSION['pending_instructor_longitude'] = $longitude;
    $_SESSION['pending_instructor_city'] = $city;
    $_SESSION['pending_instructor_region'] = $region;
    $_SESSION['pending_instructor_country'] = $country;
    $_SESSION['pending_instructor_maps_link'] = $maps_link;
    $_SESSION['pending_instructor_ip'] = $ip_address;
    $_SESSION['pending_instructor_user_agent'] = $user_agent;
    $_SESSION['pending_instructor_otp'] = $otp;
    $_SESSION['pending_instructor_otp_expires'] = $otp_expires_at;

    saveOtpHistory(
        $conn,
        $instructor['instructor_id'],
        $otp,
        'login_verification',
        $otp_expires_at,
        'sent'
    );

    $emailSent = sendOtpEmail($instructor['email'], $instructor['fullname'], $otp, 'login');

    if (!$emailSent) {
        $_SESSION['error'] = "OTP email could not be sent.";
        redirectTo("instructor_portal.php");
    }

    saveLoginHistory(
        $conn,
        $instructor['instructor_id'],
        $ip_address,
        $user_agent,
        $latitude,
        $longitude,
        $city,
        $region,
        $country,
        $maps_link,
        'success',
        'Password verified, OTP sent'
    );

    $_SESSION['success'] = "OTP sent to your email.";
    redirectTo("instructor_verify_otp.php");
}

//============ Verify OTP =================
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    (isset($_POST['verify_instructor_otp']) || isset($_POST['verify_reset_otp']))
) {
    $entered_otp = trim($_POST['otp'] ?? '');

    //==================================================
    // LOGIN OTP VERIFICATION
    //==================================================
    if (isset($_POST['verify_instructor_otp'])) {
        if (
            !isset($_SESSION['pending_instructor_id']) ||
            !isset($_SESSION['pending_instructor_otp']) ||
            !isset($_SESSION['pending_instructor_otp_expires'])
        ) {
            $_SESSION['error'] = "Session expired. Please log in again.";
            redirectTo("instructor_portal.php");
        }

        if ($entered_otp === '') {
            $_SESSION['error'] = "Please enter the OTP.";
            redirectTo("instructor_verify_otp.php");
        }

        $pending_instructor_id = $_SESSION['pending_instructor_id'];
        $session_otp = $_SESSION['pending_instructor_otp'];
        $otp_expires = $_SESSION['pending_instructor_otp_expires'];

        if (strtotime($otp_expires) < time()) {
            updateOtpHistoryStatus($conn, $pending_instructor_id, $session_otp, 'expired', 'login_verification');

            unset(
                $_SESSION['pending_instructor_id'],
                $_SESSION['pending_instructor_fullname'],
                $_SESSION['pending_instructor_email'],
                $_SESSION['pending_instructor_latitude'],
                $_SESSION['pending_instructor_longitude'],
                $_SESSION['pending_instructor_city'],
                $_SESSION['pending_instructor_region'],
                $_SESSION['pending_instructor_country'],
                $_SESSION['pending_instructor_maps_link'],
                $_SESSION['pending_instructor_ip'],
                $_SESSION['pending_instructor_user_agent'],
                $_SESSION['pending_instructor_otp'],
                $_SESSION['pending_instructor_otp_expires']
            );

            $_SESSION['error'] = "OTP expired. Please log in again.";
            redirectTo("instructor_portal.php");
        }

        if ($entered_otp !== $session_otp) {
            updateOtpHistoryStatus($conn, $pending_instructor_id, $session_otp, 'failed', 'login_verification');
            $_SESSION['error'] = "Invalid OTP.";
            redirectTo("instructor_verify_otp.php");
        }

        updateOtpHistoryStatus($conn, $pending_instructor_id, $session_otp, 'used', 'login_verification', true);

        session_regenerate_id(true);

        $_SESSION['instructor_id'] = $_SESSION['pending_instructor_id'];
        $_SESSION['instructor_fullname'] = $_SESSION['pending_instructor_fullname'];
        $_SESSION['instructor_email'] = $_SESSION['pending_instructor_email'];
        $_SESSION['instructor_logged_in'] = true;

        unset(
            $_SESSION['pending_instructor_id'],
            $_SESSION['pending_instructor_fullname'],
            $_SESSION['pending_instructor_email'],
            $_SESSION['pending_instructor_latitude'],
            $_SESSION['pending_instructor_longitude'],
            $_SESSION['pending_instructor_city'],
            $_SESSION['pending_instructor_region'],
            $_SESSION['pending_instructor_country'],
            $_SESSION['pending_instructor_maps_link'],
            $_SESSION['pending_instructor_ip'],
            $_SESSION['pending_instructor_user_agent'],
            $_SESSION['pending_instructor_otp'],
            $_SESSION['pending_instructor_otp_expires']
        );

        $_SESSION['success'] = "Login successful.";
        redirectTo("instructor_dashboard.php");
    }

    //==================================================
    // FORGOT PASSWORD OTP VERIFICATION
    //==================================================
    if (isset($_POST['verify_reset_otp'])) {
        if (
            !isset($_SESSION['reset_instructor_id']) ||
            !isset($_SESSION['reset_instructor_email']) ||
            !isset($_SESSION['reset_instructor_otp']) ||
            !isset($_SESSION['reset_instructor_otp_expires'])
        ) {
            $_SESSION['error'] = "Session expired.";
            redirectTo("instructor_portal.php");
        }

        if ($entered_otp === '') {
            $_SESSION['error'] = "Please enter the OTP.";
            redirectTo("instructor_verify_otp.php");
        }

        $reset_instructor_id = $_SESSION['reset_instructor_id'];
        $reset_session_otp = $_SESSION['reset_instructor_otp'];
        $reset_otp_expires = $_SESSION['reset_instructor_otp_expires'];

        if (strtotime($reset_otp_expires) < time()) {
            updateOtpHistoryStatus($conn, $reset_instructor_id, $reset_session_otp, 'expired', 'password_reset');

            unset(
                $_SESSION['reset_instructor_id'],
                $_SESSION['reset_instructor_email'],
                $_SESSION['reset_instructor_otp'],
                $_SESSION['reset_instructor_otp_expires'],
                $_SESSION['reset_verified'],
                $_SESSION['reset_instructor_latitude'],
                $_SESSION['reset_instructor_longitude'],
                $_SESSION['reset_instructor_city'],
                $_SESSION['reset_instructor_region'],
                $_SESSION['reset_instructor_country'],
                $_SESSION['reset_instructor_maps_link'],
                $_SESSION['reset_instructor_ip'],
                $_SESSION['reset_instructor_user_agent']
            );

            $_SESSION['allow_forgot'] = true;
            $_SESSION['error'] = "OTP expired. Please try again.";
            redirectTo("instructor_forgot_password.php");
        }

        if ($entered_otp !== $reset_session_otp) {
            updateOtpHistoryStatus($conn, $reset_instructor_id, $reset_session_otp, 'failed', 'password_reset');
            $_SESSION['error'] = "Invalid OTP.";
            redirectTo("instructor_verify_otp.php");
        }

        updateOtpHistoryStatus($conn, $reset_instructor_id, $reset_session_otp, 'used', 'password_reset', true);

        $_SESSION['reset_verified'] = true;

        unset(
            $_SESSION['reset_instructor_otp'],
            $_SESSION['reset_instructor_otp_expires']
        );

        $_SESSION['success'] = "OTP verified. Set your new password.";
        redirectTo("instructor_reset_password.php");
    }
}

//============ Forgot Password (Send OTP) =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['forgot_password'])) {
    $email = trim($_POST['email'] ?? '');

    $latitude = isset($_POST['latitude']) && $_POST['latitude'] !== '' ? trim($_POST['latitude']) : null;
    $longitude = isset($_POST['longitude']) && $_POST['longitude'] !== '' ? trim($_POST['longitude']) : null;
    $city = isset($_POST['city']) && $_POST['city'] !== '' ? trim($_POST['city']) : null;
    $region = isset($_POST['region']) && $_POST['region'] !== '' ? trim($_POST['region']) : null;
    $country = isset($_POST['country']) && $_POST['country'] !== '' ? trim($_POST['country']) : null;
    $maps_link = isset($_POST['maps_link']) && $_POST['maps_link'] !== '' ? trim($_POST['maps_link']) : null;

    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';

    if ($email === '') {
        $_SESSION['error'] = "Enter your email.";
        $_SESSION['allow_forgot'] = true;
        redirectTo("instructor_forgot_password.php");
    }

    $stmt = $conn->prepare("SELECT instructor_id, fullname, email FROM instructors WHERE email = ? LIMIT 1");

    if (!$stmt) {
        $_SESSION['error'] = "Database error.";
        $_SESSION['allow_forgot'] = true;
        redirectTo("instructor_forgot_password.php");
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $instructor = $result->fetch_assoc();
        $stmt->close();

        $otp = generateOtp(6);
        $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        $_SESSION['reset_instructor_id'] = $instructor['instructor_id'];
        $_SESSION['reset_instructor_email'] = $instructor['email'];
        $_SESSION['reset_instructor_otp'] = $otp;
        $_SESSION['reset_instructor_otp_expires'] = $expires;

        $_SESSION['reset_instructor_latitude'] = $latitude;
        $_SESSION['reset_instructor_longitude'] = $longitude;
        $_SESSION['reset_instructor_city'] = $city;
        $_SESSION['reset_instructor_region'] = $region;
        $_SESSION['reset_instructor_country'] = $country;
        $_SESSION['reset_instructor_maps_link'] = $maps_link;
        $_SESSION['reset_instructor_ip'] = $ip_address;
        $_SESSION['reset_instructor_user_agent'] = $user_agent;

        saveOtpHistory(
            $conn,
            $instructor['instructor_id'],
            $otp,
            'password_reset',
            $expires,
            'sent'
        );

        $emailSent = sendOtpEmail($instructor['email'], $instructor['fullname'], $otp, 'reset');

        if (!$emailSent) {
            $_SESSION['error'] = "OTP email could not be sent.";
            $_SESSION['allow_forgot'] = true;
            redirectTo("instructor_forgot_password.php");
        }

        $_SESSION['success'] = "OTP sent to your email.";
        redirectTo("instructor_verify_otp.php");
    }

    $stmt->close();
    $_SESSION['success'] = "If the email is registered, an OTP has been sent.";
    redirectTo("instructor_portal.php");
}

//============ Set New Password =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_new_password'])) {
    if (
        !isset($_SESSION['reset_verified']) ||
        $_SESSION['reset_verified'] !== true ||
        !isset($_SESSION['reset_instructor_id'])
    ) {
        $_SESSION['error'] = "Unauthorized.";
        redirectTo("instructor_portal.php");
    }

    $password = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');

    if ($password === '' || $confirm === '') {
        $_SESSION['error'] = "Fill all fields.";
        redirectTo("instructor_reset_password.php");
    }

    if ($password !== $confirm) {
        $_SESSION['error'] = "Passwords do not match.";
        redirectTo("instructor_reset_password.php");
    }

    if (strlen($password) < 8) {
        $_SESSION['error'] = "Minimum 8 characters.";
        redirectTo("instructor_reset_password.php");
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $id = $_SESSION['reset_instructor_id'];

    $stmt = $conn->prepare("UPDATE instructors SET password = ? WHERE instructor_id = ?");

    if (!$stmt) {
        $_SESSION['error'] = "Database error.";
        redirectTo("instructor_reset_password.php");
    }

    $stmt->bind_param("si", $hash, $id);
    $stmt->execute();
    $stmt->close();

    $ip_address = $_SESSION['reset_instructor_ip'] ?? ($_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN');
    $user_agent = $_SESSION['reset_instructor_user_agent'] ?? ($_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN');
    $latitude = $_SESSION['reset_instructor_latitude'] ?? null;
    $longitude = $_SESSION['reset_instructor_longitude'] ?? null;
    $city = $_SESSION['reset_instructor_city'] ?? null;
    $region = $_SESSION['reset_instructor_region'] ?? null;
    $country = $_SESSION['reset_instructor_country'] ?? null;
    $maps_link = $_SESSION['reset_instructor_maps_link'] ?? null;

    savePasswordHistory(
        $conn,
        $id,
        'reset',
        $ip_address,
        $user_agent,
        $latitude,
        $longitude,
        $city,
        $region,
        $country,
        $maps_link
    );

    unset(
        $_SESSION['reset_instructor_id'],
        $_SESSION['reset_instructor_email'],
        $_SESSION['reset_instructor_otp'],
        $_SESSION['reset_instructor_otp_expires'],
        $_SESSION['reset_verified'],
        $_SESSION['reset_instructor_latitude'],
        $_SESSION['reset_instructor_longitude'],
        $_SESSION['reset_instructor_city'],
        $_SESSION['reset_instructor_region'],
        $_SESSION['reset_instructor_country'],
        $_SESSION['reset_instructor_maps_link'],
        $_SESSION['reset_instructor_ip'],
        $_SESSION['reset_instructor_user_agent']
    );

    $_SESSION['success'] = "Password updated successfully.";
    redirectTo("instructor_portal.php");
}

redirectTo("instructor_portal.php");
?>