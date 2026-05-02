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
    $admin_id,
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
    $sql = "INSERT INTO admin_login_history 
            (admin_id, ip_address, user_agent, latitude, longitude, city, region, country, maps_link, login_status, remarks)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "issssssssss",
            $admin_id,
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

//============ OTP History =================
function saveOtpHistory($conn, $admin_id, $otp, $purpose, $expires_at, $status = 'sent') {
    $checkOtpTable = $conn->query("SHOW TABLES LIKE 'admin_otp_history'");
    if ($checkOtpTable && $checkOtpTable->num_rows > 0) {
        $sql = "INSERT INTO admin_otp_history 
                (admin_id, otp_code, purpose, expires_at, status)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issss", $admin_id, $otp, $purpose, $expires_at, $status);
        $stmt->execute();
        $stmt->close();
    }
}

//============ OTP History Status =================
function updateOtpHistoryStatus($conn, $admin_id, $otp, $status, $verified = false) {
    $checkOtpTable = $conn->query("SHOW TABLES LIKE 'admin_otp_history'");
    if (!($checkOtpTable && $checkOtpTable->num_rows > 0)) {
        return;
    }

    if ($verified) {
        $sql = "UPDATE admin_otp_history
                SET status = ?, verified_at = NOW()
                WHERE admin_id = ? AND otp_code = ? AND purpose = 'login_verification' AND status = 'sent'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sis", $status, $admin_id, $otp);
    } else {
        $sql = "UPDATE admin_otp_history
                SET status = ?
                WHERE admin_id = ? AND otp_code = ? AND purpose = 'login_verification' AND status = 'sent'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sis", $status, $admin_id, $otp);
    }

    $stmt->execute();
    $stmt->close();
}

//============ OTP Email =================
function sendOtpEmail($email, $fullname, $otp, $type = 'login') {
    $mail = new PHPMailer(true);

    $subject = $type === 'login'
        ? "MotoSafe Admin Login OTP"
        : "MotoSafe Admin OTP";

    $heading = $type === 'login'
        ? "Admin Login Verification"
        : "Admin Verification";

    $description = $type === 'login'
        ? "Use the OTP below to complete your <strong style='color:#ffffff;'>MotoSafe Admin Portal</strong> login."
        : "Use the OTP below to continue your verification.";

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
          <p style='margin:0;color:#b8c4e0;font-size:15px;line-height:1.7;'>
            {$description}
          </p>
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
        $fromName  = $_ENV['MAIL_FROM_NAME'] ?? 'MotoSafe Admin Security';

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

//============ Admin Login =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {

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
        redirectTo("admin_portal.php");
    }

    $stmt = $conn->prepare("SELECT admin_id, fullname, email, password FROM admins WHERE email = ? LIMIT 1");
    if (!$stmt) {
        $_SESSION['error'] = "Database error.";
        redirectTo("admin_portal.php");
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        $stmt->close();
        $_SESSION['error'] = "Admin account not found.";
        redirectTo("admin_portal.php");
    }

    $admin = $result->fetch_assoc();
    $stmt->close();

    if (!password_verify($password, $admin['password'])) {
        saveLoginHistory(
            $conn,
            $admin['admin_id'],
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
        redirectTo("admin_portal.php");
    }

    $otp = generateOtp(6);
    $otp_expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    $_SESSION['pending_admin_id'] = $admin['admin_id'];
    $_SESSION['pending_admin_fullname'] = $admin['fullname'];
    $_SESSION['pending_admin_email'] = $admin['email'];
    $_SESSION['pending_admin_latitude'] = $latitude;
    $_SESSION['pending_admin_longitude'] = $longitude;
    $_SESSION['pending_admin_city'] = $city;
    $_SESSION['pending_admin_region'] = $region;
    $_SESSION['pending_admin_country'] = $country;
    $_SESSION['pending_admin_maps_link'] = $maps_link;
    $_SESSION['pending_admin_ip'] = $ip_address;
    $_SESSION['pending_admin_user_agent'] = $user_agent;
    $_SESSION['pending_admin_otp'] = $otp;
    $_SESSION['pending_admin_otp_expires'] = $otp_expires_at;

    saveOtpHistory(
        $conn,
        $admin['admin_id'],
        $otp,
        'login_verification',
        $otp_expires_at,
        'sent'
    );

    $emailSent = sendOtpEmail($admin['email'], $admin['fullname'], $otp, 'login');

    if (!$emailSent) {
        $_SESSION['error'] = "OTP email could not be sent.";
        redirectTo("admin_portal.php");
    }

    saveLoginHistory(
        $conn,
        $admin['admin_id'],
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
    redirectTo("admin_verify_otp.php");
}

//============ Verify OTP =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_admin_otp'])) {
    $entered_otp = trim($_POST['otp'] ?? '');

    if (
        !isset($_SESSION['pending_admin_id']) ||
        !isset($_SESSION['pending_admin_otp']) ||
        !isset($_SESSION['pending_admin_otp_expires'])
    ) {
        $_SESSION['error'] = "Session expired. Please log in again.";
        redirectTo("admin_portal.php");
    }

    if ($entered_otp === '') {
        $_SESSION['error'] = "Please enter the OTP.";
        redirectTo("admin_verify_otp.php");
    }

    $pending_admin_id = $_SESSION['pending_admin_id'];
    $session_otp = $_SESSION['pending_admin_otp'];
    $otp_expires = $_SESSION['pending_admin_otp_expires'];

    if (strtotime($otp_expires) < time()) {
        updateOtpHistoryStatus($conn, $pending_admin_id, $session_otp, 'expired');

        unset(
            $_SESSION['pending_admin_id'],
            $_SESSION['pending_admin_fullname'],
            $_SESSION['pending_admin_email'],
            $_SESSION['pending_admin_latitude'],
            $_SESSION['pending_admin_longitude'],
            $_SESSION['pending_admin_ip'],
            $_SESSION['pending_admin_user_agent'],
            $_SESSION['pending_admin_otp'],
            $_SESSION['pending_admin_otp_expires']
        );

        $_SESSION['error'] = "OTP expired. Please log in again.";
        redirectTo("admin_portal.php");
    }

    if ($entered_otp !== $session_otp) {
        updateOtpHistoryStatus($conn, $pending_admin_id, $session_otp, 'failed');
        $_SESSION['error'] = "Invalid OTP.";
        redirectTo("admin_verify_otp.php");
    }

    updateOtpHistoryStatus($conn, $pending_admin_id, $session_otp, 'used', true);

    session_regenerate_id(true);

    $_SESSION['admin_id'] = $_SESSION['pending_admin_id'];
    $_SESSION['admin_fullname'] = $_SESSION['pending_admin_fullname'];
    $_SESSION['admin_email'] = $_SESSION['pending_admin_email'];
    $_SESSION['admin_logged_in'] = true;

    unset(
        $_SESSION['pending_admin_id'],
        $_SESSION['pending_admin_fullname'],
        $_SESSION['pending_admin_email'],
        $_SESSION['pending_admin_latitude'],
        $_SESSION['pending_admin_longitude'],
        $_SESSION['pending_admin_ip'],
        $_SESSION['pending_admin_user_agent'],
        $_SESSION['pending_admin_otp'],
        $_SESSION['pending_admin_otp_expires']
    );

    $_SESSION['success'] = "Login successful.";
    redirectTo("admin_dashboard2.php");
}

redirectTo("admin_portal.php");
?>