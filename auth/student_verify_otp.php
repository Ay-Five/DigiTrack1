<?php
// auth/student_verify_otp.php
// Receives JSON: { email, otp, action:'login'|'signup' }
// Returns JSON:  { success:bool, message:string, redirect?:string }

session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

$email  = filter_var(trim($data['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$otp    = preg_replace('/\D/', '', $data['otp'] ?? '');
$action = $data['action'] ?? '';

// ── Basic validation ──
if (!$email || strlen($otp) !== 6 || !in_array($action, ['login','signup'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

// ── Retrieve stored OTP ──
$stored = $_SESSION['student_otp'] ?? null;

if (
    !$stored ||
    $stored['email']  !== $email ||
    $stored['action'] !== $action
) {
    echo json_encode(['success' => false, 'message' => 'OTP session expired. Please request a new one.']);
    exit;
}

// ── Check expiry ──
if (time() > $stored['expiry']) {
    unset($_SESSION['student_otp']);
    echo json_encode(['success' => false, 'message' => 'OTP has expired. Please request a new one.']);
    exit;
}

// ── Verify OTP ──
if (!password_verify($otp, $stored['hash'])) {
    echo json_encode(['success' => false, 'message' => 'Incorrect OTP. Please try again.']);
    exit;
}

// ── OTP correct — clear it immediately ──
unset($_SESSION['student_otp']);

try {
    if ($action === 'login') {
        // ── Fetch existing student ──
        $stmt = $pdo->prepare("SELECT id, name, email, status FROM students WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $student = $stmt->fetch();

        if (!$student || $student['status'] === 'inactive') {
            echo json_encode(['success' => false, 'message' => 'Account not found or inactive.']);
            exit;
        }

        // Log login
        $pdo->prepare("UPDATE students SET last_login = NOW() WHERE id = ?")->execute([$student['id']]);

        // Set session
        session_regenerate_id(true);
        $_SESSION['stu_logged_in'] = true;
        $_SESSION['stu_id']        = $student['id'];
        $_SESSION['stu_name']      = $student['name'];
        $_SESSION['stu_email']     = $student['email'];
        $_SESSION['stu_expire']    = time() + (30 * 60);

        echo json_encode(['success' => true, 'redirect' => '../student/dashboard.php']);

    } else {
        // ── SIGNUP: create new student ──
        $name  = $stored['name'];
        $phone = $stored['phone'];

        // Double-check email not taken (race condition guard)
        $check = $pdo->prepare("SELECT id FROM students WHERE email = ? LIMIT 1");
        $check->execute([$email]);
        if ($check->fetch()) {
            echo json_encode(['success' => false, 'message' => 'An account with that Gmail already exists. Please sign in.']);
            exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO students (name, email, phone, status, created_at)
            VALUES (?, ?, ?, 'active', NOW())
        ");
        $stmt->execute([$name, $email, $phone]);
        $newId = $pdo->lastInsertId();

        session_regenerate_id(true);
        $_SESSION['stu_logged_in'] = true;
        $_SESSION['stu_id']        = $newId;
        $_SESSION['stu_name']      = $name;
        $_SESSION['stu_email']     = $email;
        $_SESSION['stu_expire']    = time() + (30 * 60);

        echo json_encode(['success' => true, 'redirect' => '../student/dashboard.php']);
    }

} catch (PDOException $e) {
    error_log("Student OTP verify DB error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'A system error occurred. Please try again.']);
}
