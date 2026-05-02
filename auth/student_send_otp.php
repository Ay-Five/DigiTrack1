<?php
// auth/student_send_otp.php
// Receives JSON: { email, name, phone, action:'login'|'signup' }
// Returns JSON:  { success:bool, message:string }

session_start();
require_once '../includes/db.php';
require_once '../includes/helpers.php';
require_once '../includes/mailer.php';   // PHPMailer wrapper

header('Content-Type: application/json');

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

$email  = filter_var(trim($data['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$name   = htmlspecialchars(trim($data['name']  ?? ''));
$phone  = htmlspecialchars(trim($data['phone'] ?? ''));
$action = in_array($data['action'] ?? '', ['login','signup']) ? $data['action'] : '';

// ── Validate Gmail ──
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !str_ends_with(strtolower($email), '@gmail.com')) {
    echo json_encode(['success' => false, 'message' => 'Please use a valid Gmail address.']);
    exit;
}
if (!$action) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

// ── Rate limit: max 3 OTP requests per 10 min per IP ──
$ip = $_SERVER['REMOTE_ADDR'];
$key = 'otp_rate_' . md5($ip . $email);
if (!isset($_SESSION[$key])) { $_SESSION[$key] = ['count' => 0, 'time' => time()]; }
if (time() - $_SESSION[$key]['time'] > 600) { $_SESSION[$key] = ['count' => 0, 'time' => time()]; }
if ($_SESSION[$key]['count'] >= 3) {
    echo json_encode(['success' => false, 'message' => 'Too many OTP requests. Please wait 10 minutes.']);
    exit;
}

try {
    if ($action === 'login') {
        // ── LOGIN: email must already exist ──
        $stmt = $pdo->prepare("SELECT id, name, status FROM students WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $student = $stmt->fetch();
        if (!$student) {
            echo json_encode(['success' => false, 'message' => 'No student account found with that Gmail. Please sign up first.']);
            exit;
        }
        if ($student['status'] === 'inactive') {
            echo json_encode(['success' => false, 'message' => 'Your account is inactive. Please contact the admin.']);
            exit;
        }
        $name = $student['name'];

    } else {
        // ── SIGNUP: email must NOT exist ──
        $stmt = $pdo->prepare("SELECT id FROM students WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'A student account with that Gmail already exists. Please sign in instead.']);
            exit;
        }
        if (strlen($name) < 2) {
            echo json_encode(['success' => false, 'message' => 'Please enter your full name.']);
            exit;
        }
    }

    // ── Generate 6-digit OTP ──
    $otp     = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $otpHash = password_hash($otp, PASSWORD_BCRYPT);
    $expiry  = time() + 300; // 5 minutes

    // ── Store OTP in session (no DB table needed) ──
    $_SESSION['student_otp'] = [
        'email'  => $email,
        'name'   => $name,
        'phone'  => $phone,
        'action' => $action,
        'hash'   => $otpHash,
        'expiry' => $expiry,
    ];
    $_SESSION[$key]['count']++;

    // ── Send email ──
    $sent = send_otp_email($email, $name, $otp);
    if (!$sent) {
        echo json_encode(['success' => false, 'message' => 'Failed to send OTP email. Please check your Gmail address and try again.']);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'OTP sent successfully.']);

} catch (PDOException $e) {
    error_log("Student OTP DB error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'A system error occurred. Please try again.']);
}
