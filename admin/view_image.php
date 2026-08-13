<?php
session_start();

// Must be logged in as any role
$loggedIn = isset($_SESSION['admin_id']) || isset($_SESSION['instructor_id']) || isset($_SESSION['student_id']);
if (!$loggedIn) {
    header('Location: ../index.php');
    exit;
}

$role = isset($_SESSION['admin_id']) ? 'admin' : (isset($_SESSION['instructor_id']) ? 'instructor' : 'student');
$isStudent = $role === 'student';

// Allowed base directories — only serve files from these folders
$allowedDirs = [
    'certs'        => realpath(__DIR__ . '/../assets/uploads/certs'),
    'certificates' => realpath(__DIR__ . '/../assets/uploads/certificates'),
    'student_ids'  => realpath(__DIR__ . '/../assets/uploads/student_ids'),
];

$type = preg_replace('/[^a-z_]/', '', $_GET['type'] ?? '');
$file = basename($_GET['file'] ?? '');
$back = $_GET['back'] ?? 'javascript:history.back()';

// Validate type
if (!isset($allowedDirs[$type])) {
    http_response_code(400); exit('Invalid type.');
}

$baseDir = $allowedDirs[$type];
$fullPath = $baseDir . DIRECTORY_SEPARATOR . $file;
$realPath = realpath($fullPath);

// Security: must be inside allowed dir and exist
if (!$realPath || strpos($realPath, $baseDir) !== 0 || !file_exists($realPath)) {
    http_response_code(404); exit('File not found.');
}

$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
$allowedExts = ['jpg','jpeg','png','webp','gif'];
if (!in_array($ext, $allowedExts)) {
    http_response_code(403); exit('Not an image.');
}

$imgSrc = '../assets/uploads/' . $type . '/' . rawurlencode($file);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>View Image – ABROT</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{background:#0f1117;color:#e2e5f0;font-family:'Segoe UI',sans-serif;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:20px;padding:20px}
.toolbar{display:flex;gap:12px;align-items:center;background:#181c27;border:1px solid #2a3147;border-radius:10px;padding:12px 20px}
.btn{display:inline-flex;align-items:center;gap:8px;padding:8px 18px;border-radius:7px;font-size:13px;font-weight:500;cursor:pointer;border:none;text-decoration:none;transition:.15s}
.btn-back{background:transparent;border:1px solid #2a3147;color:#8490a8}.btn-back:hover{background:rgba(255,255,255,.05);color:#e2e5f0}
.btn-download{background:#3cb97a;color:#fff}.btn-download:hover{opacity:.85}
.img-wrap{max-width:90vw;max-height:80vh;border-radius:10px;overflow:hidden;border:2px solid #2a3147;box-shadow:0 8px 40px rgba(0,0,0,.6)}
.img-wrap img{display:block;max-width:90vw;max-height:80vh;object-fit:contain}
.label{font-size:11px;color:#8490a8;text-align:center}
</style>
</head>
<body>
<div class="toolbar">
    <a href="<?= htmlspecialchars($back) ?>" class="btn btn-back"><i class="fa-solid fa-arrow-left"></i> Back</a>
    <?php if($isStudent): ?>
    <a href="<?= htmlspecialchars($imgSrc) ?>" download="<?= htmlspecialchars($file) ?>" class="btn btn-download"><i class="fa-solid fa-download"></i> Download</a>
    <?php endif; ?>
    <span style="font-size:12px;color:#8490a8"><?= htmlspecialchars($file) ?></span>
</div>
<div class="img-wrap">
    <img src="<?= htmlspecialchars($imgSrc) ?>" alt="Image">
</div>
<div class="label">Click Back to return to the previous page</div>
</body>
</html>
