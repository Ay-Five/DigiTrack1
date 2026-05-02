<?php
// includes/helpers.php

/**
 * Store a flash error + which modal to re-open after redirect.
 * $modal: 'inst' | 'stu' | 'admin'
 */
function flash_error(string $msg, string $modal = 'inst'): void {
    $_SESSION['flash_error'] = $msg;
    $_SESSION['open_modal']  = $modal;
}

function flash_success(string $msg, string $modal = 'inst'): void {
    $_SESSION['flash_success'] = $msg;
    $_SESSION['open_modal']    = $modal;
}

/**
 * Redirect with HTTP header (safe wrapper)
 */
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

/**
 * Check instructor session guard. Call at top of every instructor page.
 */
function require_instructor(): void {
    if (
        empty($_SESSION['inst_logged_in']) ||
        empty($_SESSION['inst_expire']) ||
        time() > $_SESSION['inst_expire']
    ) {
        session_destroy();
        header("Location: /index.php");
        exit;
    }
    // Extend
    $_SESSION['inst_expire'] = time() + 1800;
}

/**
 * Check student session guard.
 */
function require_student(): void {
    if (
        empty($_SESSION['stu_logged_in']) ||
        empty($_SESSION['stu_expire']) ||
        time() > $_SESSION['stu_expire']
    ) {
        session_destroy();
        header("Location: /index.php");
        exit;
    }
    $_SESSION['stu_expire'] = time() + 1800;
}
