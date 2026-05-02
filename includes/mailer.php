<?php
// includes/mailer.php
// Requires PHPMailer — install via composer:
//   composer require phpmailer/phpmailer
// Or download manually and place in /vendor/phpmailer/

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Auto-detect composer autoloader or manual include
$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
} else {
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
}

// ─── MAIL CONFIGURATION ─────────────────────────────
// Replace with your Gmail App Password (not your real password).
// Go to: Google Account → Security → 2-Step Verification → App Passwords
define('MAIL_HOST',     'smtp.gmail.com');
define('MAIL_PORT',     587);
define('MAIL_USERNAME', 'your_school_email@gmail.com');  // ← change this
define('MAIL_PASSWORD', 'your_gmail_app_password');       // ← change this
define('MAIL_FROM',     'your_school_email@gmail.com');   // ← change this
define('MAIL_FROM_NAME','Digitract – Driving School System');
// ────────────────────────────────────────────────────

/**
 * Send a 6-digit OTP to a student's Gmail.
 *
 * @param string $toEmail   Recipient Gmail
 * @param string $toName    Recipient name
 * @param string $otp       6-digit OTP string
 * @return bool             true on success
 */
function send_otp_email(string $toEmail, string $toName, string $otp): bool {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;

        // Recipients
        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($toEmail, $toName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Digitract OTP Code';
        $mail->Body    = otp_email_template($toName, $otp);
        $mail->AltBody = "Hi $toName,\n\nYour Digitract OTP code is: $otp\n\nThis code expires in 5 minutes.\n\nDo not share this code with anyone.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer error: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * HTML email template for OTP
 */
function otp_email_template(string $name, string $otp): string {
    $digits = str_split($otp);
    $boxes  = '';
    foreach ($digits as $d) {
        $boxes .= "<td style='padding:0 5px'><div style='width:48px;height:56px;background:#1a3a8f;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:800;color:#ffffff;font-family:monospace;text-align:center;line-height:56px;'>$d</div></td>";
    }
    return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f4f6fc;font-family:sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6fc;padding:40px 0;">
  <tr><td align="center">
    <table width="480" cellpadding="0" cellspacing="0" style="background:#0b1437;border-radius:20px;overflow:hidden;">

      <!-- Header -->
      <tr><td style="background:linear-gradient(135deg,#1e4db7,#0b1437);padding:32px 40px;text-align:center;">
        <p style="margin:0;font-size:26px;font-weight:800;color:#ffffff;letter-spacing:-0.5px;">🚗 Digi<span style="color:#f59e0b">tract</span></p>
        <p style="margin:8px 0 0;font-size:13px;color:rgba(255,255,255,0.5);">Instructor Scheduling & Performance System</p>
      </td></tr>

      <!-- Body -->
      <tr><td style="padding:40px;">
        <p style="margin:0 0 8px;font-size:16px;color:rgba(255,255,255,0.8);">Hi <strong style="color:#fff;">$name</strong>,</p>
        <p style="margin:0 0 28px;font-size:14px;color:rgba(255,255,255,0.5);line-height:1.6;">Use the code below to verify your identity. This OTP expires in <strong style="color:#f59e0b;">5 minutes</strong>.</p>

        <!-- OTP digits -->
        <table cellpadding="0" cellspacing="0" style="margin:0 auto 28px;">
          <tr>$boxes</tr>
        </table>

        <p style="margin:0 0 24px;font-size:13px;color:rgba(255,255,255,0.4);text-align:center;">
          🔒 Never share this code with anyone — Digitract staff will never ask for your OTP.
        </p>

        <hr style="border:none;border-top:1px solid rgba(255,255,255,0.08);margin:0 0 24px;">
        <p style="margin:0;font-size:12px;color:rgba(255,255,255,0.3);text-align:center;">
          If you did not request this, you can safely ignore this email.
        </p>
      </td></tr>

    </table>
  </td></tr>
</table>
</body>
</html>
HTML;
}
