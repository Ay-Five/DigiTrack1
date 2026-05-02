<?php
// auth/instructor_register.php
session_start();
require_once '../includes/db.php';
require_once '../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['inst_register'])) {
    header("Location: ../index.php"); exit;
}

$name    = htmlspecialchars(trim($_POST['name'] ?? ''));
$email   = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$license = htmlspecialchars(trim($_POST['license_number'] ?? ''));
$phone   = htmlspecialchars(trim($_POST['phone'] ?? ''));
$password = trim($_POST['password'] ?? '');

// ── Validation ──
$errors = [];
if (strlen($name) < 2)        $errors[] = 'Please enter your full name.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
if (strlen($license) < 4)     $errors[] = 'Please enter a valid LTO license number.';
if (strlen($password) < 8)    $errors[] = 'Password must be at least 8 characters.';
if (!preg_match('/[A-Z]/', $password)) $errors[] = 'Password must contain at least one uppercase letter.';
if (!preg_match('/[0-9]/', $password)) $errors[] = 'Password must contain at least one number.';

if ($errors) {
    flash_error(implode(' ', $errors), 'inst');
    header("Location: ../index.php"); exit;
}

try {
    // ── Check email uniqueness ──
    $check = $pdo->prepare("SELECT id FROM instructors WHERE email = ? LIMIT 1");
    $check->execute([$email]);
    if ($check->fetch()) {
        flash_error('An account with that email already exists. Try signing in instead.', 'inst');
        header("Location: ../index.php"); exit;
    }

    // ── Check license uniqueness ──
    $checkLic = $pdo->prepare("SELECT id FROM instructors WHERE license_number = ? LIMIT 1");
    $checkLic->execute([$license]);
    if ($checkLic->fetch()) {
        flash_error('That license number is already registered.', 'inst');
        header("Location: ../index.php"); exit;
    }

    // ── Insert new instructor (pending approval) ──
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    $stmt = $pdo->prepare("
        INSERT INTO instructors (name, email, password_hash, phone, license_number, status, is_approved, created_at)
        VALUES (?, ?, ?, ?, ?, 'active', 0, NOW())
    ");
    $stmt->execute([$name, $email, $hash, $phone, $license]);

    $newId = $pdo->lastInsertId();

    // ── Notify admin (optional email — configure mailer in helpers.php) ──
    // notify_admin_new_instructor($name, $email, $license);

    flash_success("Registration submitted! Your account is pending admin approval. We'll notify you at {$email} once approved.", 'inst');
    header("Location: ../index.php"); exit;

} catch (PDOException $e) {
    error_log("Instructor register DB error: " . $e->getMessage());
    flash_error('A system error occurred. Please try again.', 'inst');
    header("Location: ../index.php"); exit;
}
