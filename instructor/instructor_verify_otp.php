<?php
    session_start();?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>MotoSafe – Intructor Verify OTP</title>
    <link href="../assets/img/logo.png" rel="icon">
    <link href="../assets/img/logo.png" rel="apple-touch-icon">
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;600;700;900&family=Barlow+Condensed:wght@700;900&display=swap" rel="stylesheet"/>
    <link href="../assets/css/verify.css" rel="stylesheet">
</head>
<body>
    <div class="bg-canvas"></div>
        <main>
            <div class="otp-card">
                <div class="success-overlay" id="successOverlay">
                <div class="checkmark-ring">✓</div>
                <div class="success-title">Identity <span>Verified!</span></div>
                <p class="success-sub">You're all set. Welcome to MotoSafe.</p>
                <button class="btn-continue">Continue to Dashboard →</button>
                </div>
                <div class="card-main" id="cardMain">
                <div class="badge"><span class="badge-dot"></span> OTP Verification</div>
                <h1 class="card-title">Verify Your<br><span>Identity.</span></h1>
                <?php
                    $email = $_SESSION['pending_instructor_email']
                        ?? $_SESSION['reset_instructor_email']
                        ?? '';

                    if ($email !== '' && strpos($email, '@') !== false) {
                        [$name, $domain] = explode('@', $email, 2);

                        $visibleLength = min(3, strlen($name));
                        $visible = substr($name, 0, $visibleLength);
                        $masked = str_repeat('*', max(strlen($name) - $visibleLength, 3));

                        $maskedEmail = $visible . $masked . '@' . $domain;
                    } else {
                        $maskedEmail = $email;
                    }
                ?>
                <p class="card-sub">
                    We sent a 6-digit code to<br>
                    <strong><?= htmlspecialchars($maskedEmail) ?></strong> — enter it below to continue.
                </p>
                <form method="POST" action="instructor_account_process.php">
                <div class="otp-inputs" id="otpInputs">
                    <input class="otp-digit" type="text" maxlength="1" id="d0" inputmode="numeric" autocomplete="one-time-code"/>
                    <input class="otp-digit" type="text" maxlength="1" id="d1" inputmode="numeric"/>
                    <input class="otp-digit" type="text" maxlength="1" id="d2" inputmode="numeric"/>
                    <input class="otp-digit" type="text" maxlength="1" id="d3" inputmode="numeric"/>
                    <input class="otp-digit" type="text" maxlength="1" id="d4" inputmode="numeric"/>
                    <input class="otp-digit" type="text" maxlength="1" id="d5" inputmode="numeric"/>
                    <input type="hidden" name="otp" id="otpHidden">
                </div>
                <div class="otp-progress" id="progressDots">
                    <div class="progress-dot" id="pd0"></div>
                    <div class="progress-dot" id="pd1"></div>
                    <div class="progress-dot" id="pd2"></div>
                    <div class="progress-dot" id="pd3"></div>
                    <div class="progress-dot" id="pd4"></div>
                    <div class="progress-dot" id="pd5"></div>
                </div>
                <input type="hidden" name="latitude" id="latitude">
                <input type="hidden" name="longitude" id="longitude">
                <input type="hidden" name="city" id="city">
                <input type="hidden" name="region" id="region">
                <input type="hidden" name="country" id="country">
                <input type="hidden" name="maps_link" id="maps_link">

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="message error">
                        <?= htmlspecialchars($_SESSION['error']); ?>
                        <?php unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="message success">
                        <?= htmlspecialchars($_SESSION['success']); ?>
                        <?php unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>
                <?php
                    $otpButtonName = isset($_SESSION['reset_instructor_email']) ? 'verify_reset_otp' : 'verify_instructor_otp';
                ?>
                <button type="submit" name="<?= $otpButtonName ?>" class="btn-verify" id="verifyBtn" disabled>
                    Verify OTP
                </button>
                <div class="resend-row">
                    Didn't receive it?&nbsp;
                    <button id="resendBtn" disabled onclick="startResend()">Resend code</button>
                    <span class="countdown" id="countdown">(0:30)</span>
                </div>
                <p class="spam-note">
                    Please check your <strong>Spam</strong> or <strong>Junk</strong> folder if you don't see the email.
                </p>
                </form>
                <div class="divider"><hr/><span>OR</span><hr/></div>
                <a href="../index.php" class="back-link">← Back to login</a>
            </div>
        </div>
    </main>
    <script src="../assets/js/right-click.js"></script>
    <script src="../assets/js/verify.js"></script>
    <script src="../assets/js/geolocation.js"></script>
</body>
</html>