<?php
//Forgot Password
session_start();

if (isset($_GET['action']) && $_GET['action'] === 'forgot') {
    $_SESSION['allow_forgot'] = true;
    header("Location: forgot_password.php");
    exit();
}

header("Location: student_portal.php");
exit();