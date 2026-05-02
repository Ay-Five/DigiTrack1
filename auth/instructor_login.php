<?php
// auth/instructor_login.php
session_start();
require_once '../includes/db.php';   // PDO $pdo
require_once '../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['inst_login'])) {
    header("Location: ../index.php"); exit;
}

$email    = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$password = trim($_POST['password'] ?? '');

// ── Rate limiting (5 tries / 10 min per IP) ──
$ip = $_SERVER['REMOTE_ADDR'];
if (!isset($_SESSION['inst_attempts'])) { $_SESSION['inst_attempts'] = 0; $_SESSION['inst_attempt_time'] = time(); }
if (time() - $_SESSION['inst_attempt_time'] > 600) { $_SESSION['inst_attempts'] = 0; $_SESSION['inst_attempt_time'] = time(); }
if ($_SESSION['inst_attempts'] >= 5) {
    flash_error('Too many failed attempts. Please wait 10 minutes.', 'inst');
    header("Location: ../index.php"); exit;
}

if (!$email || !$password) {
    flash_error('Please fill in all fields.', 'inst');
    header("Location: ../index.php"); exit;
}

try {
    // ── Fetch instructor by email ──
    $stmt = $pdo->prepare("
        SELECT id, name, email, password_hash, status, is_approved
        FROM instructors
        WHERE email = ?
        LIMIT 1
    ");
    $stmt->execute([$email]);
    $inst = $stmt->fetch();

    if (!$inst) {
        $_SESSION['inst_attempts']++;
        flash_error('No account found with that email address.', 'inst');
        header("Location: ../index.php"); exit;
    }

    // ── Check admin approval first ──
    if (!$inst['is_approved']) {
        flash_error('Your account is pending admin approval. You will be notified once verified.', 'inst');
        header("Location: ../index.php"); exit;
    }

    // ── Check active status ──
    if ($inst['status'] === 'inactive') {
        flash_error('Your account has been deactivated. Please contact the admin.', 'inst');
        header("Location: ../index.php"); exit;
    }

    // ── Verify password ──
    if (!password_verify($password, $inst['password_hash'])) {
        $_SESSION['inst_attempts']++;
        flash_error('Incorrect password. Please try again.', 'inst');
        header("Location: ../index.php"); exit;
    }

    // ── Success — establish session ──
    $_SESSION['inst_attempts'] = 0;
    session_regenerate_id(true);

    $_SESSION['inst_logged_in'] = true;
    $_SESSION['inst_id']        = $inst['id'];
    $_SESSION['inst_name']      = $inst['name'];
    $_SESSION['inst_email']     = $inst['email'];
    $_SESSION['inst_expire']    = time() + (30 * 60);

    // ── Log the login ──
    $pdo->prepare("UPDATE instructors SET last_login = NOW() WHERE id = ?")->execute([$inst['id']]);

    header("Location: ../instructor/dashboard.php"); exit;

} catch (PDOException $e) {
    error_log("Instructor login DB error: " . $e->getMessage());
    flash_error('A system error occurred. Please try again.', 'inst');
    header("Location: ../index.php"); exit;
}
