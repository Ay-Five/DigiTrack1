<?php
    session_start();
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Access Denied – MotoSafe</title>
    <link href="../assets/img/logo.png" rel="icon">
    <link href="../assets/img/logo.png" rel="apple-touch-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="../assets/css/auth.css" rel="stylesheet">
</head>

<body>
    <div class="bg"></div>
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="card">
        <div class="card-stripe"></div>
        <div class="card-body">
            <a href="admin_portal.php" class="logo">
                <div class="logo-mark">
                    <i class="fas fa-shield"></i>
                </div>
                <span class="logo-text">Moto<span class="gold">Safe</span></span>
            </a>
            <div class="icon-ring">
                <i class="fas fa-lock"></i>
            </div>
            <div class="card-title">Access Denied</div>
            <div class="card-sub">
                You must be logged in to access the Admin Dashboard.<br>
                This area is restricted to authorized personnel only.
            </div>
            <div class="divider"></div>
            <div style="font-size:13px;color:#aaa;margin-bottom:8px">
                Redirecting in <span id="countdown">3</span>s
            </div>
            <div class="progress-track">
                <div class="progress-bar"></div>
            </div>
            <a href="admin_portal.php" class="btn-login">
                <i class="fas fa-arrow-right-to-bracket"></i>
                Go to Login
            </a>
        </div>
    </div>

    <script>
        let t=3;
        let el=document.getElementById('countdown');
        let i=setInterval(()=>{
            t--;
            if(el) el.textContent=t;
            if(t<=0){
                clearInterval(i);
                window.location.href="admin_portal.php";
            }
        },1000);
    </script>
</body>
</html>

<?php
    exit();
    }
?>