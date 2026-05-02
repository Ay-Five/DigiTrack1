<?php
    require '../includes/db.php';
    require 'instructor_auth.php';
    require 'instructor_process.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>MotoSafe – Instructor</title>
    <link href="../assets/img/logo.png" rel="icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
    <style>
        .section {
            display: none
        }

        .section.active {
            display: block
        }

        .hcard {
            background: linear-gradient(135deg, #0e1e3a, #091630);
            border: 1px solid rgba(61, 127, 255, .15);
            border-radius: var(--r);
            padding: 24px;
            position: relative;
            overflow: hidden;
            margin-bottom: 20px
        }

        .hcard::after {
            content: '\f508';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 20px;
            bottom: -10px;
            font-size: 80px;
            color: rgba(61, 127, 255, .05);
            line-height: 1
        }

        .havt {
            width: 60px;
            height: 60px;
            border-radius: 14px;
            flex-shrink: 0;
            background: linear-gradient(135deg, var(--accent), #1a4fb0);
            display: grid;
            place-items: center;
            font-family: var(--font-display);
            font-size: 20px;
            font-weight: 800
        }

        .hdiv {
            border-top: 1px solid rgba(255, 255, 255, .07);
            margin: 16px 0
        }

        .di {
            background: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text);
            font-family: var(--font-body);
            font-size: 13px;
            padding: 10px 14px;
            width: 100%;
            outline: none;
            transition: border-color .2s
        }

        .di:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(61, 127, 255, .1)
        }

        .dt {
            resize: vertical;
            min-height: 80px
        }

        .ds option {
            background: var(--surface-2)
        }

        .fl {
            font-size: 11px;
            font-weight: 600;
            color: var(--muted-2);
            letter-spacing: .06em;
            text-transform: uppercase;
            display: block;
            margin-bottom: 6px
        }

        .bp {
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity .2s;
            font-family: var(--font-body)
        }

        .bp:hover {
            opacity: .85
        }

        .bs {
            background: rgba(16, 185, 129, .15);
            color: var(--green);
            border: 1px solid rgba(16, 185, 129, .25);
            border-radius: 10px;
            padding: 7px 14px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            font-family: var(--font-body)
        }

        .bs:hover {
            background: rgba(16, 185, 129, .25)
        }

        .bd {
            background: rgba(244, 63, 94, .1);
            color: var(--red);
            border: 1px solid rgba(244, 63, 94, .2);
            border-radius: 10px;
            padding: 7px 14px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            font-family: var(--font-body)
        }

        .bd:hover {
            background: rgba(244, 63, 94, .2)
        }

        .be {
            background: rgba(61, 127, 255, .1);
            color: var(--accent);
            border: 1px solid rgba(61, 127, 255, .2);
            border-radius: 10px;
            padding: 7px 14px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            font-family: var(--font-body)
        }

        .be:hover {
            background: rgba(61, 127, 255, .2)
        }

        .bdone {
            background: rgba(16, 185, 129, .1);
            color: var(--green);
            border: 1px solid rgba(16, 185, 129, .2);
            border-radius: 8px;
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            font-family: var(--font-body);
            display: inline-flex;
            align-items: center;
            gap: 6px
        }

        .da {
            border-radius: var(--r);
            padding: 12px 18px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid
        }

        .das {
            background: rgba(16, 185, 129, .08);
            border-color: rgba(16, 185, 129, .2);
            color: #5dddb0
        }

        .dad {
            background: rgba(244, 63, 94, .08);
            border-color: rgba(244, 63, 94, .2);
            color: #fb7c96
        }

        .ir {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            border-bottom: 1px solid var(--border)
        }

        .ir:last-child {
            border-bottom: none
        }

        .ii {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            background: var(--surface-3);
            border: 1px solid var(--border);
            display: grid;
            place-items: center;
            font-size: 12px;
            color: var(--accent);
            flex-shrink: 0
        }

        .il {
            font-size: 11px;
            color: var(--muted-2);
            font-weight: 600;
            letter-spacing: .05em;
            text-transform: uppercase
        }

        .iv {
            font-size: 13px;
            font-weight: 500;
            margin-top: 1px;
            word-break: break-all
        }

        .course-card {
            background: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 18px;
            transition: border-color .2s
        }

        .course-card:hover {
            border-color: var(--border-bright)
        }

        .sched-card {
            background: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 18px;
            margin-bottom: 8px;
            transition: border-color .2s
        }

        .sched-card.inactive {
            opacity: .5
        }

        .day-pill {
            display: inline-flex;
            align-items: center;
            padding: 4px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            font-family: var(--font-display);
            margin-bottom: 10px
        }

        .req-card {
            background: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 18px;
            margin-bottom: 10px
        }

        .bkp {
            background: rgba(245, 158, 11, .12);
            color: #fbbf5a;
            border: 1px solid rgba(245, 158, 11, .2);
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600
        }

        .bka {
            background: rgba(61, 127, 255, .12);
            color: #7daeff;
            border: 1px solid rgba(61, 127, 255, .2);
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600
        }

        .bkd {
            background: rgba(16, 185, 129, .12);
            color: #5dddb0;
            border: 1px solid rgba(16, 185, 129, .2);
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600
        }

        .bkr {
            background: rgba(244, 63, 94, .1);
            color: #fb7c96;
            border: 1px solid rgba(244, 63, 94, .18);
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600
        }

        .bkc {
            background: var(--surface-3);
            color: var(--muted);
            border: 1px solid var(--border);
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600
        }

        .stars {
            color: var(--amber);
            letter-spacing: 2px
        }

        .rc {
            background: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 10px
        }

        .cert-box {
            border: 2px dashed var(--border-bright);
            border-radius: var(--r);
            padding: 24px;
            text-align: center;
            background: var(--surface-2)
        }

        .cert-prev {
            max-width: 100%;
            max-height: 240px;
            border-radius: 10px;
            object-fit: contain;
            cursor: zoom-in
        }

        .fi {
            background: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text);
            font-size: 13px;
            padding: 9px 14px;
            width: 100%;
            outline: none
        }

        .fi::file-selector-button {
            background: var(--surface-3);
            border: 1px solid var(--border);
            color: var(--muted);
            border-radius: 7px;
            padding: 4px 10px;
            font-size: 12px;
            cursor: pointer;
            margin-right: 10px
        }

        .modal-dark .modal-content {
            background: var(--surface);
            border: 1px solid var(--border-bright);
            border-radius: var(--r-lg);
            color: var(--text)
        }

        .modal-dark .modal-header {
            border-bottom: 1px solid var(--border)
        }

        .modal-dark .modal-footer {
            border-top: 1px solid var(--border)
        }

        .modal-dark .modal-title {
            font-family: var(--font-display);
            font-size: 16px;
            font-weight: 700
        }

        .modal-dark .btn-close {
            filter: invert(1) opacity(.5)
        }

        .notif-item {
            padding: 12px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: flex-start;
            gap: 10px
        }

        .notif-item:last-child {
            border-bottom: none
        }

        .ndot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--accent);
            flex-shrink: 0;
            margin-top: 5px
        }

        .ndot.read {
            background: var(--muted-2)
        }

        .empty-state {
            text-align: center;
            color: var(--muted);
            padding: 40px 20px
        }

        .empty-state i {
            font-size: 36px;
            opacity: .3;
            display: block;
            margin-bottom: 12px
        }

        .empty-state p {
            font-size: 13px;
            margin: 0
        }

        .ec {
            text-align: center;
            color: var(--muted);
            padding: 40px !important
        }

        .grp-lbl {
            font-size: 12px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 10px;
            padding: 14px 16px 0
        }

        /* SEARCH */
        .panel-search {
            display: flex;
            align-items: center;
            gap: 8px;

            padding: 7px 12px;
            border-radius: 8px;

            background: var(--surface-2);
            border: 1px solid var(--border);
        }

        .panel-search i {
            font-size: 12px;
            color: var(--muted);
        }

        .panel-search input {
            width: 180px;
            border: none;
            outline: none;
            background: transparent;
            color: var(--text);
            font-size: 12px;
        }

        .panel-search:focus-within {
            border-color: var(--accent);
        }

        .panel-search input:focus {
            width: 240px;
        }

        .panel-head {
            flex-wrap: wrap;
            gap: 10px;
        }

        @media (max-width: 768px) {
            .panel-head {
                align-items: flex-start;
            }

            .panel-search {
                width: 100%;
            }

            .panel-search input {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .panel-search {
                padding: 8px 10px;
            }

            .panel-search input {
                font-size: 13px;
            }
        }
    </style>
</head>

<body>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>
    <div class="app-wrapper">

        <!-- SIDEBAR -->
        <aside class="sidebar" id="sidebar">
            <div class="logo">
                <div class="logo-icon"><img src="../assets/img/logo.png" alt="MotoSafe"></div>Moto<span>Safe</span>
            </div>
            <div class="nav-label">Main</div>
            <a class="nav-item <?= $activeSection==='dashboard'?'active':'' ?>" href="#"
                onclick="showSection('dashboard');return false;"><i class="fa-solid fa-house"></i> Overview</a>
            <div class="nav-label">Teaching</div>
            <a class="nav-item <?= $activeSection==='schedule'?'active':'' ?>" href="#"
                onclick="showSection('schedule');return false;"><i class="fa-solid fa-calendar-days"></i> My Schedule
                <?php if($pendingReqs>0):?><span
                    style="margin-left:auto;background:rgba(245,158,11,.2);color:#fbbf5a;padding:1px 7px;border-radius:999px;font-size:10px;font-weight:700;"><?= $pendingReqs ?></span><?php endif;?></a>
            <a class="nav-item <?= $activeSection==='students'?'active':'' ?>" href="#"
                onclick="showSection('students');return false;"><i class="fa-solid fa-user-graduate"></i> Students
                <?php if($totalStudents>0):?><span
                    style="margin-left:auto;background:rgba(192,132,252,.2);color:#c084fc;padding:1px 7px;border-radius:999px;font-size:10px;font-weight:700;"><?= $totalStudents ?></span><?php endif;?></a>
            <a class="nav-item <?= $activeSection==='performance'?'active':'' ?>" href="#"
                onclick="showSection('performance');return false;"><i class="fa-solid fa-chart-line"></i>
                Performance</a>
            <a class="nav-item <?= $activeSection==='communication'?'active':'' ?>" href="#"
                onclick="showSection('communication');return false;"><i class="fa-solid fa-comments"></i> Communication
                <?php if($unreadNotifs>0):?><span
                    style="margin-left:auto;background:rgba(244,63,94,.2);color:#fb7c96;padding:1px 7px;border-radius:999px;font-size:10px;font-weight:700;"><?= $unreadNotifs ?></span><?php endif;?></a>
            <div class="nav-label">Manage</div>
            <a class="nav-item <?= $activeSection==='courses'?'active':'' ?>" href="#"
                onclick="showSection('courses');return false;"><i class="fa-solid fa-book-open"></i> My Courses</a>
            <a class="nav-item <?= $activeSection==='requests'?'active':'' ?>" href="#"
                onclick="showSection('requests');return false;"><i class="fa-solid fa-inbox"></i> Enrolment Requests
                <?php if($pendingReqs>0):?><span
                    style="margin-left:auto;background:rgba(245,158,11,.2);color:#fbbf5a;padding:1px 7px;border-radius:999px;font-size:10px;font-weight:700;"><?= $pendingReqs ?></span><?php endif;?></a>
            <div class="nav-label">Account</div>
            <a class="nav-item <?= $activeSection==='profile'?'active':'' ?>" href="#"
                onclick="showSection('profile');return false;"><i class="fa-solid fa-user-circle"></i> My Profile</a>
            <div class="sidebar-bottom">
                <div class="user-row">
                    <div class="avatar"><?= $initials ?></div>
                    <div>
                        <div class="user-name"><?= $fullname ?></div>
                        <div class="user-role">Instructor</div>
                    </div>
                </div>
                <a class="nav-item" href="../admin/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Sign
                    Out</a>
            </div>
        </aside>

        <div class="sidebar-push">
            <div class="topbar">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-btn d-md-none" onclick="openSidebar()"><i class="fa-solid fa-bars"></i></div>
                    <div id="page-title-text" class="page-title">Overview</div>
                </div>
                <div class="panel-search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="search" placeholder="Search students..." />
                </div>
            </div>

            <div class="content">
                <?php if($successMsg):?><div class="da das"><i
                        class="fa-solid fa-circle-check"></i><?= htmlspecialchars($successMsg) ?></div><?php endif;?>
                <?php if($errorMsg):?><div class="da dad"><i
                        class="fa-solid fa-circle-exclamation"></i><?= htmlspecialchars($errorMsg) ?></div>
                <?php endif;?>

                <!-- ══ OVERVIEW ══ -->
                <div id="section-dashboard" class="section <?= $activeSection==='dashboard'?'active':'' ?>">
                    <div class="hero-strip">
                        <div class="hero-text">
                            <div class="greeting"><?= $dayName ?>, <?= $monthYear ?></div>
                            <h2>Good <?= date('H')<12?'morning':(date('H')<18?'afternoon':'evening') ?>,
                                <?= $firstName ?> 👋</h2>
                            <p><?= $pendingReqs>0 ? "You have <strong>$pendingReqs</strong> student request".($pendingReqs>1?'s':'')." waiting for your review." : "You're all caught up — no pending requests! 🎉" ?>
                            </p>
                            <div class="hero-quick">
                                <div class="hero-quick-item"><strong><?= $totalStudents ?></strong><span>Students</span>
                                </div>
                                <div class="hero-quick-item"><strong><?= $doneSessions ?></strong><span>Done</span>
                                </div>
                                <div class="hero-quick-item"><strong><?= $avgRating?:0 ?></strong><span>Rating</span>
                                </div>
                            </div>
                        </div>
                        <?php if($pendingReqs>0):?>
                        <div class="hero-session">
                            <div class="session-dot"></div>
                            <div class="session-info">
                                <div class="label">Needs Attention</div>
                                <div class="title"><?= $pendingReqs ?> Pending</div>
                                <div class="meta">Review student requests</div>
                            </div><a href="#" onclick="showSection('requests');return false;"
                                class="tag tag-amber">Review</a>
                        </div>
                        <?php endif;?>
                    </div>

                    <!-- Stat cards -->
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-xl-3">
                            <div class="stat-card" onclick="showSection('students')" style="cursor:pointer;">
                                <div class="stat-top">
                                    <div class="stat-label">My Students</div>
                                    <div class="stat-icon" style="color:#c084fc;"><i
                                            class="fa-solid fa-user-graduate"></i></div>
                                </div>
                                <div class="stat-value"><?= $totalStudents ?></div>
                                <p class="stat-sub">Enrolled or completed</p>
                            </div>
                        </div>
                        <div class="col-6 col-xl-3">
                            <div class="stat-card" onclick="showSection('schedule')" style="cursor:pointer;">
                                <div class="stat-top">
                                    <div class="stat-label">Active Slots</div>
                                    <div class="stat-icon" style="color:var(--accent);"><i
                                            class="fa-solid fa-calendar-days"></i></div>
                                </div>
                                <div class="stat-value"><?= $activeScheds ?></div>
                                <p class="stat-sub">Open for booking</p>
                            </div>
                        </div>
                        <div class="col-6 col-xl-3">
                            <div class="stat-card" onclick="showSection('requests')" style="cursor:pointer;">
                                <div class="stat-top">
                                    <div class="stat-label">Pending Requests</div>
                                    <div class="stat-icon" style="color:var(--amber);"><i class="fa-solid fa-inbox"></i>
                                    </div>
                                </div>
                                <div class="stat-value"><?= $pendingReqs ?></div>
                                <p class="stat-sub">
                                    <?= $pendingReqs>0?'<span class="warn">Needs attention</span>':'All reviewed ✓' ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-6 col-xl-3">
                            <div class="stat-card">
                                <div class="stat-top">
                                    <div class="stat-label">Your Rating</div>
                                    <div class="stat-icon" style="color:var(--amber);"><i class="fa-solid fa-star"></i>
                                    </div>
                                </div>
                                <div class="stat-value"><?= $avgRating?:0 ?><span
                                        style="font-size:14px;color:var(--muted);">/5</span></div>
                                <p class="stat-sub">From <?= $totalRatings ?> review<?= $totalRatings!=1?'s':'' ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <!-- Today's schedule -->
                        <div class="col-12 col-lg-5">
                            <div class="panel">
                                <div class="panel-head">
                                    <div>
                                        <div class="panel-title"><i class="fa-solid fa-sun me-2"
                                                style="color:var(--amber);"></i>Today's Schedule</div>
                                        <div class="panel-sub"><?= $dayName ?></div>
                                    </div><a href="#" onclick="showSection('schedule');return false;"
                                        class="tag tag-blue" style="text-decoration:none;font-size:11px;">Full
                                        Schedule</a>
                                </div>
                                <?php if(!$todayScheds||$todayScheds->num_rows===0): ?>
                                <div class="empty-state"><i class="fa-solid fa-coffee"></i>
                                    <p>No sessions scheduled for today.</p>
                                </div>
                                <?php else: $todayScheds->data_seek(0); while($ts=$todayScheds->fetch_assoc()): $tsc=$cColors[$ts['course_type']]??'tag-blue'; ?>
                                <div
                                    style="padding:12px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;">
                                    <div>
                                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:3px;">
                                            <span class="tag <?= $tsc ?>"
                                                style="font-size:10px;"><?= htmlspecialchars($ts['course_type']) ?></span>
                                        </div>
                                        <div style="font-size:12px;color:var(--muted);"><i
                                                class="fa-solid fa-clock me-1"
                                                style="color:var(--accent);"></i><?= schedHoursLabel($ts) ?></div>
                                    </div>
                                    <div style="display:flex;gap:8px;flex-wrap:wrap;font-size:11px;">
                                        <?php if($ts['ac']>0): ?><span class="bka"><?= $ts['ac'] ?>
                                            confirmed</span><?php endif; ?>
                                        <?php if($ts['pc']>0): ?><span class="bkp"><?= $ts['pc'] ?>
                                            pending</span><?php endif; ?>
                                    </div>
                                </div>
                                <?php endwhile; endif; ?>
                            </div>
                        </div>

                        <!-- Assigned students quick -->
                        <div class="col-12 col-lg-7">
                            <div class="panel">
                                <div class="panel-head">
                                    <div>
                                        <div class="panel-title"><i class="fa-solid fa-user-graduate me-2"
                                                style="color:#c084fc;"></i>Assigned Students</div>
                                        <div class="panel-sub"><?= $totalStudents ?> total</div>
                                    </div><a href="#" onclick="showSection('students');return false;"
                                        class="tag tag-blue" style="text-decoration:none;font-size:11px;">View All</a>
                                </div>
                                <?php
$studSnap=$conn->query("SELECT DISTINCT st.student_id,st.fullname,(SELECT ic.course_type FROM enrolment_requests er2 JOIN course_schedules cs2 ON cs2.schedule_id=er2.schedule_id JOIN instructor_courses ic ON ic.course_id=cs2.course_id WHERE cs2.instructor_id=$instructor_id AND er2.student_id=st.student_id ORDER BY er2.requested_at DESC LIMIT 1) AS latest_course,(SELECT er3.status FROM enrolment_requests er3 JOIN course_schedules cs3 ON cs3.schedule_id=er3.schedule_id WHERE cs3.instructor_id=$instructor_id AND er3.student_id=st.student_id ORDER BY er3.requested_at DESC LIMIT 1) AS latest_status FROM students st WHERE EXISTS(SELECT 1 FROM enrolment_requests er JOIN course_schedules cs ON cs.schedule_id=er.schedule_id WHERE cs.instructor_id=$instructor_id AND er.student_id=st.student_id AND er.status IN('approved','pending','done')) ORDER BY st.fullname ASC LIMIT 6");
if(!$studSnap||$studSnap->num_rows===0): ?>
                                <div class="empty-state"><i class="fa-solid fa-user-graduate"></i>
                                    <p>No students enrolled yet.</p>
                                </div>
                                <?php else: ?>
                                <div
                                    style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1px;background:var(--border);">
                                    <?php while($ss=$studSnap->fetch_assoc()):
$ssW=array_filter(explode(' ',trim($ss['fullname']))); $ssIn=strtoupper(substr(implode('',array_map(fn($w)=>$w[0],$ssW)),0,2));
$stc=$cColors[$ss['latest_course']??'']??'tag-blue';
$ssc=['approved'=>'bka','pending'=>'bkp','done'=>'bkd'][$ss['latest_status']??'']??'bkc'; ?>
                                    <div
                                        style="background:var(--surface);padding:12px 16px;display:flex;align-items:center;gap:10px;">
                                        <div
                                            style="width:36px;height:36px;border-radius:9px;background:linear-gradient(135deg,#c084fc,#7c3aed);display:grid;place-items:center;font-family:var(--font-display);font-size:12px;font-weight:800;flex-shrink:0;">
                                            <?= $ssIn ?></div>
                                        <div style="min-width:0;">
                                            <div
                                                style="font-weight:700;font-size:12px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                                <?= htmlspecialchars($ss['fullname']) ?></div>
                                            <div style="display:flex;gap:4px;margin-top:3px;flex-wrap:wrap;">
                                                <?php if($ss['latest_course']): ?><span class="tag <?= $stc ?>"
                                                    style="font-size:9px;"><?= htmlspecialchars($ss['latest_course']) ?></span><?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Notifications snapshot -->
                        <div class="col-12">
                            <div class="panel">
                                <div class="panel-head">
                                    <div>
                                        <div class="panel-title"><i class="fa-solid fa-bell me-2"
                                                style="color:var(--accent);"></i>Recent Notifications</div>
                                        <div class="panel-sub"><?= $unreadNotifs ?> unread</div>
                                    </div>
                                    <div style="display:flex;gap:8px;">
                                        <a href="#" onclick="showSection('communication');return false;"
                                            class="tag tag-blue" style="text-decoration:none;font-size:11px;">View
                                            All</a>
                                        <form method="POST" action="instructor_dashboard1.php?section=dashboard"
                                            style="display:inline;"><input type="hidden" name="action"
                                                value="mark_all_read"><button type="submit" class="tag"
                                                style="border:none;cursor:pointer;background:var(--surface-3);color:var(--muted);font-size:11px;">Mark
                                                read</button></form>
                                    </div>
                                </div>
                                <?php
$notifSnap=$conn->query("SELECT * FROM notifications WHERE user_id=$instructor_id AND user_type='instructor' ORDER BY created_at DESC LIMIT 4");
if(!$notifSnap||$notifSnap->num_rows===0): ?>
                                <div class="empty-state" style="padding:20px;"><i class="fa-solid fa-bell-slash"></i>
                                    <p>No notifications yet.</p>
                                </div>
                                <?php else: while($n=$notifSnap->fetch_assoc()): ?>
                                <div class="notif-item">
                                    <div class="ndot <?= $n['is_read']?'read':'' ?>"></div>
                                    <div style="flex:1;">
                                        <div style="font-size:13px;<?= !$n['is_read']?'font-weight:600;':'' ?>">
                                            <?= htmlspecialchars($n['message']) ?></div>
                                        <div style="font-size:11px;color:var(--muted-2);margin-top:2px;">
                                            <?= date('M d, g:i A',strtotime($n['created_at'])) ?></div>
                                    </div>
                                </div>
                                <?php endwhile; endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ══ MY SCHEDULE ══ -->
                <div id="section-schedule" class="section <?= $activeSection==='schedule'?'active':'' ?>">
                    <div class="row g-3">
                        <div class="col-12 col-lg-4">
                            <div class="panel">
                                <div class="panel-head">
                                    <div>
                                        <div class="panel-title"><i class="fa-solid fa-plus me-2"
                                                style="color:var(--accent);"></i>Post New Slot</div>
                                        <div class="panel-sub">Add a date students can book</div>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    <form method="POST" action="instructor_dashboard1.php?section=schedule">
                                        <input type="hidden" name="action" value="add_schedule">

                                        <!-- Course select - 4 options -->
                                        <div style="margin-bottom:14px;"><label class="fl">Course</label>
                                            <select name="course_id" id="slotCourseId" class="di ds" required
                                                onchange="onSlotCourseChange(this)">
                                                <option value="">— Select course —</option>
                                                <?php
$myCourses->data_seek(0);
$cg=['TDC'=>[],'PDC Car'=>[],'PDC Motorcycle'=>[]];
while($c=$myCourses->fetch_assoc()){
    if(strpos($c['course_type'],'TDC')===0) $cg['TDC'][]=$c;
    elseif(strpos($c['course_type'],'PDC Car')===0) $cg['PDC Car'][]=$c;
    else $cg['PDC Motorcycle'][]=$c;
}
$cgLabels=['TDC'=>'TDC – Theoretical Driving Course','PDC Car'=>'PDC – Car','PDC Motorcycle'=>'PDC – Motorcycle'];
foreach($cg as $g=>$items): if(empty($items))continue; ?>
                                                <optgroup label="<?= $cgLabels[$g] ?>"><?php foreach($items as $c): ?>
                                                    <option value="<?= $c['course_id'] ?>"
                                                        data-type="<?= htmlspecialchars($c['course_type']) ?>">
                                                        <?= htmlspecialchars($c['course_type']) ?></option>
                                                    <?php endforeach; ?>
                                                </optgroup>
                                                <?php endforeach; ?>
                                            </select>
                                            <?php if($myCourses->num_rows===0): ?><div
                                                style="font-size:11px;color:var(--red);margin-top:4px;"><a href="#"
                                                    onclick="showSection('courses');return false;"
                                                    style="color:var(--accent);">Add a course first</a>.</div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Auto hours display -->
                                        <div id="slotHrsBox" style="display:none;margin-bottom:14px;">
                                            <label class="fl">Duration (auto-set)</label>
                                            <div
                                                style="background:var(--surface-3);border:1px solid var(--border);border-radius:10px;padding:10px 14px;font-size:13px;color:var(--text);display:flex;align-items:center;gap:8px;">
                                                <i class="fa-solid fa-clock" style="color:var(--accent);"></i><span
                                                    id="slotHrsText" style="font-weight:600;">—</span><span
                                                    style="color:var(--muted);font-size:12px;">· fixed per LTO
                                                    rules</span>
                                            </div>
                                        </div>

                                        <!-- Calendar date picker -->
                                        <div style="margin-bottom:6px;"><label class="fl">Date</label>
                                            <input type="date" name="slot_date" id="slotDate" class="di" required
                                                min="<?= date('Y-m-d') ?>" onchange="validateSlotDay(this)">
                                            <div
                                                style="margin-top:6px;background:rgba(61,127,255,.08);border:1px solid rgba(61,127,255,.2);border-radius:8px;padding:7px 12px;display:flex;align-items:center;gap:7px;">
                                                <i class="fa-solid fa-circle-info"
                                                    style="color:var(--accent);font-size:12px;flex-shrink:0;"></i>
                                                <span style="font-size:12px;color:var(--muted);">Only <strong
                                                        style="color:var(--text);">Monday – Saturday</strong> are
                                                    available. Sundays are not bookable.</span>
                                            </div>
                                            <div id="slotDayError"
                                                style="display:none;margin-top:6px;background:rgba(244,63,94,.08);border:1px solid rgba(244,63,94,.25);border-radius:8px;padding:7px 12px;font-size:12px;color:var(--red);">
                                                <i class="fa-solid fa-circle-xmark me-1"></i>Sunday is not available.
                                                Please select another day.
                                            </div>
                                            <div id="slotDayOk"
                                                style="display:none;margin-top:6px;font-size:12px;color:var(--green);">
                                                <i class="fa-solid fa-circle-check me-1"></i><span
                                                    id="slotDayName"></span>
                                            </div>
                                        </div>

                                        <!-- Price -->
                                        <div style="margin-bottom:14px;margin-top:14px;"><label class="fl">Price
                                                (₱)</label>
                                            <input type="number" name="slot_price" class="di" step="0.01" min="0"
                                                placeholder="e.g. 4500">
                                        </div>

                                        <!-- Max Students -->
                                        <div style="margin-bottom:14px;"><label class="fl">Max Students</label>
                                            <select name="max_students" class="di ds"><?php for($n=1;$n<=20;$n++): ?>
                                                <option value="<?= $n ?>" <?= $n===5?'selected':'' ?>><?= $n ?>
                                                    student<?= $n>1?'s':'' ?></option><?php endfor; ?>
                                            </select>
                                        </div>

                                        <!-- Notes -->
                                        <div style="margin-bottom:16px;"><label class="fl">Notes (optional)</label>
                                            <textarea name="notes" class="di dt"
                                                placeholder="e.g. Meet at Gate 2, bring valid ID…"></textarea>
                                        </div>

                                        <button type="submit" id="slotSubmitBtn" class="bp w-100"><i
                                                class="fa-solid fa-calendar-plus me-2"></i>Post Schedule</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-lg-8">
                            <!-- Today highlight -->
                            <?php if($todayScheds&&$todayScheds->num_rows>0): $todayScheds->data_seek(0); ?>
                            <div class="panel mb-3"
                                style="border-color:rgba(16,185,129,.3);background:rgba(16,185,129,.04);">
                                <div class="panel-head">
                                    <div>
                                        <div class="panel-title"><i class="fa-solid fa-sun me-2"
                                                style="color:var(--amber);"></i>Today — <?= $dayName ?></div>
                                        <div class="panel-sub"><?= $todayScheds->num_rows ?>
                                            slot<?= $todayScheds->num_rows!=1?'s':'' ?> scheduled</div>
                                    </div>
                                </div>
                                <div style="padding:4px 16px 12px;">
                                    <?php while($ts=$todayScheds->fetch_assoc()): $tsc=$cColors[$ts['course_type']]??'tag-blue'; ?>
                                    <div
                                        style="background:var(--surface-2);border:1px solid var(--border);border-left:3px solid var(--green);border-radius:10px;padding:12px 14px;margin-bottom:8px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
                                        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                                            <span class="tag <?= $tsc ?>"
                                                style="font-size:11px;"><?= htmlspecialchars($ts['course_type']) ?></span>
                                            <span style="font-size:12px;color:var(--muted);"><i
                                                    class="fa-solid fa-clock me-1"
                                                    style="color:var(--accent);"></i><?= schedHoursLabel($ts) ?></span>
                                            <span style="font-size:12px;color:var(--green);"><i
                                                    class="fa-solid fa-peso-sign me-1"></i><?= schedPriceLabel($ts) ?></span>
                                        </div>
                                        <div style="display:flex;gap:8px;font-size:11px;">
                                            <span style="color:var(--amber);"><i
                                                    class="fa-solid fa-hourglass-half me-1"></i><?= $ts['pc'] ?>
                                                pending</span>
                                            <span style="color:var(--accent);"><i
                                                    class="fa-solid fa-circle-check me-1"></i><?= $ts['ac'] ?>
                                                approved</span>
                                            <span style="color:var(--green);"><i
                                                    class="fa-solid fa-flag-checkered me-1"></i><?= $ts['dc'] ?>
                                                done</span>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Pending requests inline -->
                            <?php if($pendingReqs>0): ?>
                            <div class="panel mb-3" style="border-color:rgba(245,158,11,.3);">
                                <div class="panel-head">
                                    <div>
                                        <div class="panel-title"><i class="fa-solid fa-inbox me-2"
                                                style="color:var(--amber);"></i>Pending Requests</div>
                                        <div class="panel-sub"><?= $pendingReqs ?> awaiting your decision</div>
                                    </div>
                                </div>
                                <div style="padding:8px 16px 12px;">
                                    <?php
$pendOnly=$conn->query("SELECT er.*,cs.day_of_week,cs.slot_date,cs.slot_hrs,cs.max_students,ic.course_type,st.fullname AS sname FROM enrolment_requests er JOIN course_schedules cs ON cs.schedule_id=er.schedule_id JOIN instructor_courses ic ON ic.course_id=cs.course_id JOIN students st ON st.student_id=er.student_id WHERE cs.instructor_id=$instructor_id AND er.status='pending' ORDER BY COALESCE(cs.slot_date,'9999-12-31') ASC, er.requested_at ASC");
while($pr=$pendOnly->fetch_assoc()):
$prW=array_filter(explode(' ',trim($pr['sname']))); $prIn=strtoupper(substr(implode('',array_map(fn($w)=>$w[0],$prW)),0,2));
$prDc=$dayColors[$pr['day_of_week']]??'var(--accent)'; $prtc=$cColors[$pr['course_type']]??'tag-blue'; ?>
                                    <div
                                        style="background:var(--surface-2);border:1px solid var(--border);border-radius:10px;padding:12px 14px;margin-bottom:8px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                                        <div style="display:flex;align-items:center;gap:10px;">
                                            <div
                                                style="width:34px;height:34px;border-radius:8px;background:linear-gradient(135deg,var(--accent),#1a4fb0);display:grid;place-items:center;font-family:var(--font-display);font-size:11px;font-weight:800;flex-shrink:0;">
                                                <?= $prIn ?></div>
                                            <div>
                                                <div style="font-weight:700;font-size:13px;">
                                                    <?= htmlspecialchars($pr['sname']) ?></div>
                                                <div style="display:flex;gap:6px;margin-top:2px;flex-wrap:wrap;">
                                                    <span class="tag <?= $prtc ?>"
                                                        style="font-size:10px;"><?= htmlspecialchars($pr['course_type']) ?></span>
                                                    <span
                                                        style="background:<?= $prDc ?>22;color:<?= $prDc ?>;border:1px solid <?= $prDc ?>44;border-radius:999px;padding:1px 7px;font-size:10px;font-weight:700;"><?= $pr['day_of_week'] ?></span>
                                                    <span
                                                        style="font-size:11px;color:var(--muted);"><?= schedDateLabel($pr) ?>
                                                        ·
                                                        <?= schedHoursLabel($pr) ?></span><?php $prBooked=(int)($conn->query("SELECT COUNT(*) AS c FROM enrolment_requests WHERE schedule_id={$pr['schedule_id']} AND status NOT IN('rejected','cancelled')")->fetch_assoc()['c'] ?? 0); if((int)$pr['max_students']>0): ?><span
                                                        style="font-size:11px;color:<?= $prBooked >= (int)$pr['max_students'] ? 'var(--red)' : 'var(--green)' ?>;"><?= $prBooked ?>/<?= (int)$pr['max_students'] ?>
                                                        booked</span><?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button class="bs" style="padding:5px 12px;font-size:12px;"
                                                onclick="openRM(<?= $pr['request_id'] ?>,'approve')"><i
                                                    class="fa-solid fa-check me-1"></i>Accept</button>
                                            <button class="bd" style="padding:5px 12px;font-size:12px;"
                                                onclick="openRM(<?= $pr['request_id'] ?>,'reject')"><i
                                                    class="fa-solid fa-xmark me-1"></i>Reject</button>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- All schedule slots table -->
                            <div class="panel" style="padding:0;">
                                <div class="panel-head" style="padding:16px 20px;">
                                    <div>
                                        <div class="panel-title"><i class="fa-solid fa-calendar-week me-2"
                                                style="color:var(--muted-2);"></i>All Schedule Slots</div>
                                        <div class="panel-sub"><?= $mySchedules->num_rows ?> total</div>
                                    </div>
                                </div>
                                <?php if($mySchedules->num_rows===0): ?>
                                <div class="empty-state"><i class="fa-solid fa-calendar-days"></i>
                                    <p>No slots yet. Add one using the form.</p>
                                </div>
                                <?php else:
$mySchedules->data_seek(0);
$sbd=[];
while($s=$mySchedules->fetch_assoc()) $sbd[$s['day_of_week']][]=$s;
?>
                                <div class="tbl-wrap">
                                    <table class="tbl">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Course</th>
                                                <th>Hours</th>
                                                <th>Price</th>
                                                <th>Students</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($days as $day): if(empty($sbd[$day]))continue; $dc=$dayColors[$day]??'var(--accent)'; $isToday=($day===$todayDay); foreach($sbd[$day] as $s): $tc=$cColors[$s['course_type']]??'tag-blue'; $sdateDisp=!empty($s['slot_date'])?date('M d, Y',strtotime($s['slot_date'])):$day; ?>
                                            <tr style="<?= $isToday?'background:rgba(16,185,129,.04);':'' ?>">
                                                <td>
                                                    <div style="font-weight:700;font-size:13px;">
                                                        <?= schedDateLabel($s) ?></div>
                                                    <span
                                                        style="background:<?= $dc ?>22;color:<?= $dc ?>;border:1px solid <?= $dc ?>44;border-radius:999px;padding:1px 8px;font-size:10px;font-weight:700;"><?= $day ?></span>
                                                    <?php if($isToday): ?><div
                                                        style="font-size:10px;color:var(--green);font-weight:700;margin-top:2px;">
                                                        TODAY</div><?php endif; ?>
                                                </td>
                                                <td><span class="tag <?= $tc ?>"
                                                        style="font-size:10px;"><?= htmlspecialchars($s['course_type']) ?></span><?php if(!$s['is_active']): ?>
                                                    <div style="font-size:10px;color:var(--muted-2);margin-top:2px;">
                                                        Inactive</div><?php endif; ?>
                                                </td>
                                                <td style="font-size:12px;white-space:nowrap;color:var(--muted);"><i
                                                        class="fa-solid fa-clock me-1"
                                                        style="color:var(--accent);"></i><?= schedHoursLabel($s) ?></td>
                                                <td
                                                    style="font-size:12px;white-space:nowrap;<?= (isset($s['slot_price'])&&$s['slot_price'])?'color:var(--green);font-weight:600;':'color:var(--muted);' ?>">
                                                    <?= (isset($s['slot_price'])&&$s['slot_price'])?'₱'.number_format($s['slot_price'],0):'—' ?>
                                                </td>
                                                <td style="font-size:12px;">
                                                    <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                                        <span
                                                            style="font-size:11px;color:var(--muted);"><?= schedBookedCount($s) ?>/<?= (int)$s['max_students'] ?>
                                                            booked</span>
                                                    </div>
                                                </td>
                                                <td><?php $isFull=schedBookedCount($s) >= (int)$s['max_students'] && (int)$s['max_students']>0; ?><?= !$s['is_active']?'<span class="bkc" style="font-size:10px;">Inactive</span>':($isFull?'<span class="bkr" style="font-size:10px;">Full</span>':'<span class="bkd" style="font-size:10px;">Active</span>') ?>
                                                </td>
                                                <td>
                                                    <div style="display:flex;gap:6px;">
                                                        <button type="button" class="be"
                                                            style="padding:4px 10px;font-size:11px;"
                                                            onclick="openES(<?= $s['schedule_id'] ?>,<?= $s['max_students'] ?>,'<?= htmlspecialchars(addslashes($s['notes']??'')) ?>','<?= $s['slot_price']??'' ?>')"><i
                                                                class="fa-solid fa-pen me-1"></i>Edit</button>
                                                        <form method="POST"
                                                            action="instructor_dashboard1.php?section=schedule"
                                                            onsubmit="return confirm('Delete this slot?');"
                                                            style="display:inline;"><input type="hidden" name="action"
                                                                value="delete_schedule"><input type="hidden"
                                                                name="schedule_id"
                                                                value="<?= $s['schedule_id'] ?>"><button type="submit"
                                                                class="bd" style="padding:4px 10px;font-size:11px;"><i
                                                                    class="fa-solid fa-trash me-1"></i>Delete</button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ══ STUDENTS ══ -->
                <div id="section-students" class="section <?= $activeSection==='students'?'active':'' ?>">
                    <div
                        style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
                        <div>
                            <div style="font-size:17px;font-weight:800;font-family:var(--font-display);">My Students
                            </div>
                            <div style="font-size:13px;color:var(--muted);"><?= $totalStudents ?> enrolled or completed
                            </div>
                        </div>
                    </div>
                    <?php if($allStudents->num_rows===0): ?>
                    <div class="empty-state"><i class="fa-solid fa-user-graduate"></i>
                        <p>No students yet. Once students book your courses and you approve them, they'll appear here.
                        </p>
                    </div>
                    <?php else: ?>
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px;">
                        <?php while($st=$allStudents->fetch_assoc()):
                        $stW=array_filter(explode(' ',trim($st['fullname']))); $stIn=strtoupper(substr(implode('',array_map(fn($w)=>$w[0],$stW)),0,2));
                        $prog=$st['session_count']>0?round($st['done_count']/$st['session_count']*100):0;
                        ?>
                        <div
                            style="background:var(--surface-2);border:1px solid var(--border);border-radius:16px;overflow:hidden;">
                            <!-- Header -->
                            <div style="padding:16px 18px 12px;border-bottom:1px solid var(--border);">
                                <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                                    <div
                                        style="width:44px;height:44px;border-radius:11px;background:linear-gradient(135deg,#c084fc,#7c3aed);display:grid;place-items:center;font-family:var(--font-display);font-size:15px;font-weight:800;flex-shrink:0;">
                                        <?= $stIn ?></div>
                                    <div style="min-width:0;">
                                        <div style="font-weight:700;font-size:14px;">
                                            <?= htmlspecialchars($st['fullname']) ?></div>
                                        <div
                                            style="font-size:12px;color:var(--muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                            <?= htmlspecialchars($st['email']) ?></div>
                                    </div>
                                    <?php if($st['is_verified']): ?><span class="tag tag-green"
                                        style="font-size:10px;flex-shrink:0;margin-left:auto;">Verified</span><?php endif; ?>
                                </div>
                                <!-- Progress -->
                                <div
                                    style="display:flex;justify-content:space-between;font-size:11px;color:var(--muted);margin-bottom:5px;">
                                    <span>Session Progress</span><strong
                                        style="color:var(--text);"><?= $st['done_count'] ?>/<?= $st['session_count'] ?></strong>
                                </div>
                                <div
                                    style="height:6px;background:var(--surface-3);border-radius:999px;overflow:hidden;">
                                    <div
                                        style="height:100%;width:<?= $prog ?>%;background:linear-gradient(90deg,#c084fc,var(--accent));border-radius:999px;">
                                    </div>
                                </div>
                            </div>
                            <!-- Sessions list -->
                            <?php
                    $stSess=$conn->query("SELECT er.request_id,er.status,er.instructor_note,cs.day_of_week,cs.slot_date,cs.slot_hrs,ic.course_type FROM enrolment_requests er JOIN course_schedules cs ON cs.schedule_id=er.schedule_id JOIN instructor_courses ic ON ic.course_id=cs.course_id WHERE cs.instructor_id=$instructor_id AND er.student_id={$st['student_id']} ORDER BY COALESCE(cs.slot_date,'9999-12-31') DESC, er.requested_at DESC LIMIT 3");
                    $scmS=['pending'=>'bkp','approved'=>'bka','done'=>'bkd','rejected'=>'bkr','cancelled'=>'bkc'];
                    while($sess=$stSess->fetch_assoc()): $ssc=$scmS[$sess['status']]??'bkp'; $stc2=$cColors[$sess['course_type']]??'tag-blue'; $dcs=$dayColors[$sess['day_of_week']]??'var(--accent)'; ?>
                                                <div
                                style="padding:8px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap;">
                                <div>
                                    <span class="tag <?= $stc2 ?>"
                                        style="font-size:10px;"><?= htmlspecialchars($sess['course_type']) ?></span>
                                    <div style="font-size:11px;color:var(--muted);margin-top:2px;">
                                        <span
                                            style="background:<?= $dcs ?>22;color:<?= $dcs ?>;border:1px solid <?= $dcs ?>44;border-radius:999px;padding:1px 6px;font-size:10px;font-weight:700;"><?= $sess['day_of_week'] ?></span>
                                        <?= schedHoursLabel($sess) ?>
                                    </div>
                                </div>
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <span class="<?= $ssc ?>"><?= ucfirst($sess['status']) ?></span>
                                    <?php if($sess['status']==='approved'): ?>
                                    <form method="POST" action="instructor_dashboard1.php?section=performance"
                                        onsubmit="return confirm('Mark session as done?');" style="display:inline;">
                                        <input type="hidden" name="action" value="mark_done"><input type="hidden"
                                            name="request_id" value="<?= $sess['request_id'] ?>"><button type="submit"
                                            class="bdone" style="font-size:10px;padding:3px 8px;"><i
                                                class="fa-solid fa-flag-checkered me-1"></i>Done</button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endwhile; ?>
                            <!-- Note -->
                            <div style="padding:12px 18px;">
                                <form method="POST" action="instructor_dashboard1.php?section=students">
                                    <input type="hidden" name="action" value="save_note">
                                    <input type="hidden" name="request_id" value="<?= $st['latest_rid'] ?>">
                                    <label class="fl"><i class="fa-solid fa-note-sticky me-1"
                                            style="color:var(--amber);"></i>Instructor Notes</label>
                                    <textarea name="instructor_note" class="di dt"
                                        style="min-height:56px;font-size:12px;"
                                        placeholder="e.g. Needs work on parking, great highway control…"><?= htmlspecialchars($st['latest_note']??'') ?></textarea>
                                    <button type="submit" class="bp"
                                        style="margin-top:8px;padding:6px 16px;font-size:12px;width:100%;"><i
                                            class="fa-solid fa-save me-1"></i>Save Note</button>
                                </form>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- ══ PERFORMANCE ══ -->
                <div id="section-performance" class="section <?= $activeSection==='performance'?'active':'' ?>">
                    <!-- Stats -->
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-3">
                            <div class="stat-card">
                                <div class="stat-top">
                                    <div class="stat-label">Avg Rating</div>
                                    <div class="stat-icon" style="color:var(--amber);"><i class="fa-solid fa-star"></i>
                                    </div>
                                </div>
                                <div class="stat-value"><?= $avgRating?:0 ?><span
                                        style="font-size:14px;color:var(--muted);">/5</span></div>
                                <p class="stat-sub">Overall</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-card">
                                <div class="stat-top">
                                    <div class="stat-label">Total Reviews</div>
                                    <div class="stat-icon" style="color:var(--green);"><i
                                            class="fa-solid fa-comments"></i></div>
                                </div>
                                <div class="stat-value"><?= $totalRatings ?></div>
                                <p class="stat-sub">From students</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-card">
                                <div class="stat-top">
                                    <div class="stat-label">5-Star Sessions</div>
                                    <div class="stat-icon" style="color:var(--amber);"><i
                                            class="fa-solid fa-trophy"></i></div>
                                </div>
                                <div class="stat-value"><?= $fiveStars ?></div>
                                <p class="stat-sub">Perfect scores</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-card">
                                <div class="stat-top">
                                    <div class="stat-label">Completed</div>
                                    <div class="stat-icon" style="color:#c084fc;"><i
                                            class="fa-solid fa-flag-checkered"></i></div>
                                </div>
                                <div class="stat-value"><?= $doneSessions ?></div>
                                <p class="stat-sub">Total sessions done</p>
                            </div>
                        </div>
                    </div>
                    <!-- Completed sessions table -->
                    <div class="panel" style="padding:0;">
                        <div class="panel-head" style="padding:16px 20px;">
                            <div>
                                <div class="panel-title"><i class="fa-solid fa-flag-checkered me-2"
                                        style="color:var(--green);"></i>Session Records</div>
                                <div class="panel-sub">All completed sessions — add remarks below</div>
                            </div>
                        </div>
                        <?php if(!$completedSessions||$completedSessions->num_rows===0): ?>
                        <div class="empty-state"><i class="fa-solid fa-chart-line"></i>
                            <p>No completed sessions yet. Mark sessions as done to track performance here.</p>
                        </div>
                        <?php else: ?>
                        <div class="tbl-wrap">
                            <table class="tbl">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Course</th>
                                        <th>Schedule</th>
                                        <th>Completed</th>
                                        <th>Student Rating</th>
                                        <th>Your Remarks</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $completedSessions->data_seek(0); while($cs=$completedSessions->fetch_assoc()):
$csW=array_filter(explode(' ',trim($cs['sname']))); $csIn=strtoupper(substr(implode('',array_map(fn($w)=>$w[0],$csW)),0,2));
$cstc=$cColors[$cs['course_type']]??'tag-blue'; $csDc=$dayColors[$cs['day_of_week']]??'var(--accent)';
?>
                                    <tr>
                                        <td>
                                            <div style="display:flex;align-items:center;gap:8px;">
                                                <div
                                                    style="width:30px;height:30px;border-radius:8px;background:linear-gradient(135deg,var(--accent),#1a4fb0);display:grid;place-items:center;font-family:var(--font-display);font-size:10px;font-weight:800;flex-shrink:0;">
                                                    <?= $csIn ?></div>
                                                <div style="font-weight:700;font-size:13px;">
                                                    <?= htmlspecialchars($cs['sname']) ?></div>
                                            </div>
                                        </td>
                                        <td><span class="tag <?= $cstc ?>"
                                                style="font-size:10px;"><?= htmlspecialchars($cs['course_type']) ?></span>
                                        </td>
                                        <td style="font-size:12px;white-space:nowrap;"><span
                                                style="background:<?= $csDc ?>22;color:<?= $csDc ?>;border:1px solid <?= $csDc ?>44;border-radius:999px;padding:1px 8px;font-size:10px;font-weight:700;"><?= schedDateLabel($cs) ?></span>
                                            <div style="color:var(--muted);margin-top:3px;"><?= schedHoursLabel($cs) ?>
                                            </div>
                                        </td>
                                        <td style="font-size:12px;color:var(--muted);">
                                            <?= $cs['done_at']?date('M d, Y',strtotime($cs['done_at'])):'—' ?></td>
                                        <td><?php if($cs['student_rating']): ?><div
                                                style="color:var(--amber);letter-spacing:1px;font-size:13px;">
                                                <?= str_repeat('★',$cs['student_rating']).str_repeat('☆',5-$cs['student_rating']) ?>
                                            </div><?php if($cs['student_review']): ?><div
                                                style="font-size:11px;color:var(--muted);margin-top:2px;max-width:140px;line-height:1.4;">
                                                <?= htmlspecialchars(mb_strimwidth($cs['student_review'],0,60,'…')) ?>
                                            </div><?php endif; ?><?php else:
                                            // Check session_ratings table directly (catches ratings on past-date-not-yet-done sessions)
                                            $srRow=$conn->query("SELECT rating,description FROM session_ratings WHERE request_id=".(int)$cs['request_id']." LIMIT 1");
                                            $srData=$srRow&&$srRow->num_rows>0?$srRow->fetch_assoc():null;
                                            if($srData): ?><div style="color:var(--amber);letter-spacing:1px;font-size:13px;"><?= str_repeat('★',(int)$srData['rating']).str_repeat('☆',5-(int)$srData['rating']) ?></div><?php if($srData['description']): ?><div style="font-size:11px;color:var(--muted);margin-top:2px;max-width:140px;line-height:1.4;"><?= htmlspecialchars(mb_strimwidth($srData['description'],0,60,'…')) ?></div><?php endif; ?><?php else: ?><span
                                                style="font-size:11px;color:var(--muted);">Not rated
                                                yet</span><?php endif; ?><?php endif; ?></td>
                                        <td style="min-width:180px;">
                                            <form method="POST" action="instructor_dashboard1.php?section=performance"
                                                id="remkForm_<?= $cs['request_id'] ?>">
                                                <input type="hidden" name="action" value="save_note">
                                                <input type="hidden" name="request_id" value="<?= $cs['request_id'] ?>">
                                                <textarea name="instructor_note" class="di dt"
                                                    style="min-height:44px;font-size:11px;padding:6px 10px;"
                                                    placeholder="Add remarks…"
                                                    id="remkTA_<?= $cs['request_id'] ?>"><?= htmlspecialchars($cs['instructor_note']??'') ?></textarea>
                                            </form>
                                        </td>
                                        <td><button type="button" class="bp" style="padding:5px 10px;font-size:11px;"
                                                onclick="document.getElementById('remkForm_<?= $cs['request_id'] ?>').submit()"><i
                                                    class="fa-solid fa-save"></i></button></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                    <!-- Reviews received -->
                    <?php if($ratings&&$ratings->num_rows>0): ?>
                    <div style="margin-top:20px;">
                        <div
                            style="font-size:12px;font-weight:700;color:var(--muted-2);text-transform:uppercase;letter-spacing:.06em;margin-bottom:12px;">
                            <i class="fa-solid fa-star me-2" style="color:var(--amber);"></i>Reviews from Students
                        </div>
                        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px;">
                            <?php $ratings->data_seek(0); while($rv=$ratings->fetch_assoc()): $rW=array_filter(explode(' ',trim($rv['sname']))); $rIn=strtoupper(substr(implode('',array_map(fn($w)=>$w[0],$rW)),0,2)); ?>
                            <div class="rc">
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <div
                                        style="width:36px;height:36px;border-radius:9px;background:linear-gradient(135deg,var(--accent),#1a4fb0);display:grid;place-items:center;font-family:var(--font-display);font-size:12px;font-weight:800;flex-shrink:0;">
                                        <?= $rIn ?></div>
                                    <div style="flex:1;">
                                        <div style="font-weight:600;font-size:13px;">
                                            <?= htmlspecialchars($rv['sname']) ?></div>
                                        <?php if($rv['course_type']): ?><span
                                            class="tag <?= $cColors[$rv['course_type']]??'tag-blue' ?>"
                                            style="font-size:10px;"><?= htmlspecialchars($rv['course_type']) ?></span><?php endif; ?>
                                    </div>
                                    <div class="stars">
                                        <?= str_repeat('★',$rv['rating']).str_repeat('☆',5-$rv['rating']) ?></div>
                                </div><?php if($rv['description']): ?><div
                                    style="font-size:13px;color:var(--muted);line-height:1.6;margin-bottom:6px;">
                                    <?= htmlspecialchars($rv['description']) ?></div><?php endif; ?><div
                                    style="font-size:11px;color:var(--muted-2);">
                                    <?= date('M d, Y',strtotime($rv['created_at'])) ?></div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- ══ COMMUNICATION ══ -->
                <div id="section-communication" class="section <?= $activeSection==='communication'?'active':'' ?>">
                    <div class="row g-3">
                        <div class="col-12 col-lg-5">
                            <!-- Announcement -->
                            <div class="panel mb-3">
                                <div class="panel-head">
                                    <div>
                                        <div class="panel-title"><i class="fa-solid fa-bullhorn me-2"
                                                style="color:var(--accent);"></i>Post Announcement</div>
                                        <div class="panel-sub">Sent to all enrolled &amp; pending students</div>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    <form method="POST" action="instructor_dashboard1.php?section=communication">
                                        <input type="hidden" name="action" value="post_announcement">
                                        <div style="margin-bottom:14px;"><label class="fl">Announcement</label><textarea
                                                name="announcement" class="di dt" style="min-height:90px;"
                                                placeholder="e.g. Saturday session moved to 10am. Please bring your documents…"
                                                required></textarea></div>
                                        <?php $enrolledCnt=$conn->query("SELECT COUNT(DISTINCT er.student_id) AS c FROM enrolment_requests er JOIN course_schedules cs ON cs.schedule_id=er.schedule_id WHERE cs.instructor_id=$instructor_id AND er.status IN('approved','pending')")->fetch_assoc()['c']; ?>
                                        <div
                                            style="font-size:12px;color:var(--muted);margin-bottom:14px;background:var(--surface-3);border-radius:8px;padding:8px 12px;">
                                            <i class="fa-solid fa-circle-info me-1"
                                                style="color:var(--accent);"></i>Will notify
                                            <strong><?= $enrolledCnt ?></strong> enrolled
                                            student<?= $enrolledCnt!=1?'s':'' ?>.
                                        </div>
                                        <button type="submit" class="bp w-100"><i
                                                class="fa-solid fa-paper-plane me-2"></i>Send Announcement</button>
                                    </form>
                                </div>
                            </div>
                            <!-- Direct message -->
                            <div class="panel">
                                <div class="panel-head">
                                    <div>
                                        <div class="panel-title"><i class="fa-solid fa-comment-dots me-2"
                                                style="color:var(--green);"></i>Message a Student</div>
                                        <div class="panel-sub">Direct notification to one student</div>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    <form method="POST" action="instructor_dashboard1.php?section=communication">
                                        <input type="hidden" name="action" value="send_message">
                                        <div style="margin-bottom:14px;"><label class="fl">Student</label>
                                            <select name="student_id" class="di ds" required>
                                                <option value="">— Select student —</option>
                                                <?php if($enrolledForMsg): $enrolledForMsg->data_seek(0); while($em=$enrolledForMsg->fetch_assoc()): ?>
                                                <option value="<?= $em['student_id'] ?>">
                                                    <?= htmlspecialchars($em['fullname']) ?></option>
                                                <?php endwhile; endif; ?>
                                            </select>
                                        </div>
                                        <div style="margin-bottom:14px;"><label class="fl">Message</label><textarea
                                                name="message" class="di dt" placeholder="Type your message here…"
                                                required></textarea></div>
                                        <button type="submit" class="bp w-100"><i
                                                class="fa-solid fa-paper-plane me-2"></i>Send Message</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- Notifications log -->
                        <div class="col-12 col-lg-7">
                            <div class="panel">
                                <div class="panel-head">
                                    <div>
                                        <div class="panel-title"><i class="fa-solid fa-bell me-2"
                                                style="color:var(--accent);"></i>Notifications Log</div>
                                        <div class="panel-sub"><?= $unreadNotifs ?> unread</div>
                                    </div>
                                    <form method="POST" action="instructor_dashboard1.php?section=communication"
                                        style="display:inline;"><input type="hidden" name="action"
                                            value="mark_all_read"><button type="submit" class="tag tag-blue"
                                            style="border:none;cursor:pointer;">Mark all read</button></form>
                                </div>
                                <?php if(!$myNotifs||$myNotifs->num_rows===0): ?><div class="empty-state"><i
                                        class="fa-solid fa-bell-slash"></i>
                                    <p>No notifications yet.</p>
                                </div>
                                <?php else:
$myNotifs->data_seek(0);
$ntypes=['announcement'=>['#f59e0b','fa-bullhorn'],'message'=>['#3d7fff','fa-comment-dots'],'enrolment_approved'=>['#10b981','fa-circle-check'],'enrolment_rejected'=>['#f43f5e','fa-circle-xmark'],'session_done'=>['#c084fc','fa-flag-checkered']];
while($n=$myNotifs->fetch_assoc()):
[$nc,$ni]=$ntypes[$n['type']??'']??['var(--muted-2)','fa-bell']; ?>
                                <div class="notif-item">
                                    <div
                                        style="width:28px;height:28px;border-radius:7px;background:<?= $nc ?>22;border:1px solid <?= $nc ?>44;display:grid;place-items:center;flex-shrink:0;">
                                        <i class="fa-solid <?= $ni ?>" style="color:<?= $nc ?>;font-size:12px;"></i>
                                    </div>
                                    <div style="flex:1;margin-left:8px;">
                                        <div
                                            style="font-size:13px;<?= !$n['is_read']?'font-weight:600;':'' ?>;line-height:1.5;">
                                            <?= htmlspecialchars($n['message']) ?></div>
                                        <div style="font-size:11px;color:var(--muted-2);margin-top:3px;">
                                            <?= date('M d, Y · g:i A',strtotime($n['created_at'])) ?><?= !$n['is_read']?' · <span style="color:var(--accent);font-weight:600;">New</span>':'' ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ══ MY COURSES ══ -->
                <div id="section-courses" class="section <?= $activeSection==='courses'?'active':'' ?>">
                    <div class="row g-3">
                        <div class="col-12 col-lg-4">
                            <div class="panel">
                                <div class="panel-head">
                                    <div>
                                        <div class="panel-title"><i class="fa-solid fa-plus me-2"
                                                style="color:var(--accent);"></i>Add Course</div>
                                        <div class="panel-sub">List what you offer</div>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    <form method="POST" action="instructor_dashboard1.php?section=courses">
                                        <input type="hidden" name="action" value="add_course">
                                        <div style="margin-bottom:14px;"><label class="fl">Course Type</label>
                                            <select name="course_type" class="di ds" required>
                                                <option value="">— Select —</option>
                                                <optgroup label="TDC – Theoretical Driving Course">
                                                    <option value="TDC Face-to-Face">TDC Face-to-Face</option>
                                                    <option value="TDC Online">TDC Online</option>
                                                </optgroup>
                                                <optgroup label="PDC – Practical Driving Course">
                                                    <option value="PDC Car">PDC Car (Manual &amp; Automatic)</option>
                                                    <option value="PDC Motorcycle">PDC Motorcycle (Manual &amp;
                                                        Automatic)</option>
                                                </optgroup>
                                            </select>
                                        </div>
                                        <div style="margin-bottom:14px;"><label class="fl">Description</label><textarea
                                                name="description" class="di dt"
                                                placeholder="e.g. Covers city and highway driving…"></textarea></div>
                                        <div class="row g-2 mb-4">
                                            <div class="col-6"><label class="fl">Duration (hrs)</label><input
                                                    type="number" name="duration_hrs" class="di" step="0.5" min="1"
                                                    placeholder="e.g. 15"></div>
                                            <div class="col-6"><label class="fl">Price (₱)</label><input type="number"
                                                    name="price" class="di" step="0.01" min="0" placeholder="e.g. 4500">
                                            </div>
                                        </div>
                                        <button type="submit" class="bp w-100"><i class="fa-solid fa-plus me-2"></i>Add
                                            Course</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-8">
                            <div class="panel">
                                <div class="panel-head">
                                    <div>
                                        <div class="panel-title">My Courses</div>
                                        <div class="panel-sub"><?= $myCourses->num_rows ?> listed</div>
                                    </div>
                                </div>
                                <?php if($myCourses->num_rows===0): ?>
                                <div class="empty-state"><i class="fa-solid fa-book-open"></i>
                                    <p>No courses yet. Add your first course.</p>
                                </div>
                                <?php else:
$myCourses->data_seek(0);
$grps=['TDC'=>[],'PDC Car'=>[],'PDC Motorcycle'=>[]];
while($c=$myCourses->fetch_assoc()){
    if(strpos($c['course_type'],'TDC')===0)$grps['TDC'][]=$c;
    elseif(strpos($c['course_type'],'PDC Car')===0)$grps['PDC Car'][]=$c;
    else $grps['PDC Motorcycle'][]=$c;
}
$gm=['TDC'=>['fa-book','var(--accent)','TDC – Theoretical Driving Course'],'PDC Car'=>['fa-car','var(--amber)','PDC – Car'],'PDC Motorcycle'=>['fa-motorcycle','#c084fc','PDC – Motorcycle']];
foreach($grps as $g=>$items): if(empty($items))continue; [$ico,$col,$lbl]=$gm[$g]; ?>
                                <div class="grp-lbl" style="color:<?= $col ?>;"><i
                                        class="fa-solid <?= $ico ?>"></i><?= $lbl ?></div>
                                <div
                                    style="padding:0 16px 14px;display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:10px;">
                                    <?php foreach($items as $c): $tc=$cColors[$c['course_type']]??'tag-blue'; ?>
                                    <div class="course-card">
                                        <div class="d-flex align-items-start justify-content-between mb-2"><span
                                                class="tag <?= $tc ?>"
                                                style="font-size:11px;"><?= htmlspecialchars($c['course_type']) ?></span><span
                                                class="tag tag-green" style="font-size:10px;">Active</span></div>
                                        <?php if($c['description']): ?><div
                                            style="font-size:12px;color:var(--muted);margin-bottom:8px;line-height:1.5;">
                                            <?= htmlspecialchars($c['description']) ?></div><?php endif; ?>
                                        <div style="display:flex;gap:10px;margin-bottom:12px;flex-wrap:wrap;">
                                            <?php if($c['duration_hrs']): ?><div
                                                style="font-size:12px;color:var(--muted);"><i
                                                    class="fa-solid fa-clock me-1"
                                                    style="color:var(--accent);"></i><?= $c['duration_hrs'] ?> hrs</div>
                                            <?php endif; ?>
                                            <?php if($c['price']): ?><div style="font-size:12px;color:var(--muted);"><i
                                                    class="fa-solid fa-peso-sign me-1"
                                                    style="color:var(--green);"></i>₱<?= number_format($c['price'],0) ?>
                                            </div><?php endif; ?><div style="font-size:12px;color:var(--muted);"><i
                                                    class="fa-solid fa-calendar-days me-1"
                                                    style="color:var(--accent);"></i><?= (int)$c['posted_scheds'] ?>
                                                posted</div>
                                        </div>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <a href="#" class="bs"
                                                style="flex:1;padding:6px 10px;font-size:12px;text-align:center;text-decoration:none;"
                                                onclick="goPostSchedule(<?= $c['course_id'] ?>);return false;"><i
                                                    class="fa-solid fa-calendar-plus me-1"></i>Post Schedule</a>
                                            <button type="button" class="be"
                                                style="flex:1;padding:6px 10px;font-size:12px;"
                                                onclick="openUC(<?= $c['course_id'] ?>,'<?= htmlspecialchars(addslashes($c['course_type'])) ?>','<?= htmlspecialchars(addslashes($c['description']??'')) ?>','<?= $c['duration_hrs']??'' ?>','<?= $c['price']??'' ?>')"><i
                                                    class="fa-solid fa-pen me-1"></i>Edit</button>
                                            <form method="POST" onsubmit="return confirm('Delete this course?');"
                                                style="display:inline;"><input type="hidden" name="action"
                                                    value="delete_course"><input type="hidden" name="course_id"
                                                    value="<?= $c['course_id'] ?>"><button type="submit" class="bd"
                                                    style="padding:6px 12px;"><i class="fa-solid fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endforeach; endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ══ ENROLMENT REQUESTS ══ -->
                <div id="section-requests" class="section <?= $activeSection==='requests'?'active':'' ?>">
                    <div style="display:flex;gap:4px;border-bottom:1px solid var(--border);margin-bottom:20px;">
                        <button class="tab active" onclick="fReq('all',this)">All</button>
                        <button class="tab" onclick="fReq('pending',this)">
                            Pending
                            <?php if($pendingReqs>0): ?>
                            <span
                                style="margin-left:4px;background:rgba(245,158,11,.2);color:#fbbf5a;padding:1px 7px;border-radius:999px;font-size:10px;">
                                <?= $pendingReqs ?>
                            </span>
                            <?php endif; ?>
                        </button>
                        <button class="tab" onclick="fReq('approved',this)">Approved</button>
                        <button class="tab" onclick="fReq('done',this)">Done</button>
                        <button class="tab" onclick="fReq('rejected',this)">Rejected</button>
                    </div>

                    <?php if($enrolReqs->num_rows===0): ?>
                    <div class="empty-state">
                        <i class="fa-solid fa-inbox"></i>
                        <p>No enrolment requests yet.</p>
                    </div>
                    <?php else: ?>
                    <div id="reqList" style="overflow-x:auto;">
                        <table style="width:100%;border-collapse:collapse;font-size:13px;">
                            <thead>
                                <tr
                                    style="border-bottom:2px solid var(--border);text-align:left;background:var(--surface-2);">
                                    <th style="padding:12px 10px;">Student</th>
                                    <th style="padding:12px 10px;">Course</th>
                                    <th style="padding:12px 10px;">Schedule</th>
                                    <th style="padding:12px 10px;">Hours</th>
                                    <th style="padding:12px 10px;">Price</th>
                                    <th style="padding:12px 10px;">Status</th>
                                    <th style="padding:12px 10px;">Requested</th>
                                    <th style="padding:12px 10px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $scm = [
                                    'pending'   => 'bkp',
                                    'approved'  => 'bka',
                                    'done'      => 'bkd',
                                    'rejected'  => 'bkr',
                                    'cancelled' => 'bkc'
                                ];

                                while($bk = $enrolReqs->fetch_assoc()):
                                    $sc = $scm[$bk['status']] ?? 'bkp';
                                    $tc = $cColors[$bk['course_type']] ?? 'tag-blue';
                                ?>
                                <tr class="req-item" data-status="<?= $bk['status'] ?>"
                                    style="border-bottom:1px solid var(--border);vertical-align:top;">
                                    <td style="padding:12px 10px;">
                                        <div style="font-weight:700;font-size:14px;">
                                            <?= htmlspecialchars($bk['sname']) ?>
                                        </div>
                                        <div style="font-size:12px;color:var(--muted);">
                                            <?= htmlspecialchars($bk['semail']) ?>
                                        </div>

                                        <?php if($bk['student_note']): ?>
                                        <div
                                            style="margin-top:8px;background:var(--surface-3);border:1px solid var(--border);border-radius:8px;padding:8px 10px;font-size:12px;color:var(--muted);">
                                            <strong>Student note:</strong><br>
                                            <?= htmlspecialchars($bk['student_note']) ?>
                                        </div>
                                        <?php endif; ?>

                                        <?php if($bk['instructor_note']): ?>
                                        <div style="margin-top:8px;font-size:12px;color:var(--muted-2);">
                                            <strong>Your note:</strong><br>
                                            <?= htmlspecialchars($bk['instructor_note']) ?>
                                        </div>
                                        <?php endif; ?>
                                    </td>

                                    <td style="padding:12px 10px;">
                                        <span class="tag <?= $tc ?>" style="font-size:11px;">
                                            <?= htmlspecialchars($bk['course_type']) ?>
                                        </span>
                                    </td>

                                    <td style="padding:12px 10px;">
                                        <?= !empty($bk['slot_date'])
                                    ? date('M d, Y', strtotime($bk['slot_date']))
                                    : htmlspecialchars($bk['day_of_week']) ?>
                                    </td>

                                    <td style="padding:12px 10px;">
                                        <?= isset($bk['slot_hrs']) && $bk['slot_hrs']
                                    ? $bk['slot_hrs'].' hrs'
                                    : (strpos($bk['course_type'],'TDC')===0 ? '15 hrs' : '8 hrs') ?>
                                    </td>

                                    <td style="padding:12px 10px;">
                                        <?= !empty($bk['price']) ? '₱'.number_format($bk['price'],0) : '-' ?>
                                    </td>

                                    <td style="padding:12px 10px;">
                                        <span class="<?= $sc ?>"><?= ucfirst($bk['status']) ?></span>
                                        <?php if($bk['status']==='done' && !empty($bk['done_at'])): ?>
                                        <div style="margin-top:6px;font-size:12px;color:var(--green);">
                                            Completed <?= date('M d, Y', strtotime($bk['done_at'])) ?>
                                        </div>
                                        <?php elseif($bk['status']==='approved' && !empty($bk['slot_date']) && strtotime($bk['slot_date']) < strtotime('today')): ?>
                                        <div style="margin-top:6px;font-size:11px;color:var(--amber);"><i class="fa-solid fa-clock me-1"></i>Date passed – pending mark</div>
                                        <?php endif; ?>
                                        <?php
                                        // Show student rating if exists, even if not yet marked done (date already past)
                                        $bkRating = null;
                                        if (!empty($bk['request_id'])) {
                                            $bkRatingRow = $conn->query("SELECT rating,description FROM session_ratings WHERE request_id=".(int)$bk['request_id']." LIMIT 1");
                                            if ($bkRatingRow && $bkRatingRow->num_rows > 0) $bkRating = $bkRatingRow->fetch_assoc();
                                        }
                                        if ($bkRating): ?>
                                        <div style="margin-top:8px;background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.2);border-radius:8px;padding:7px 10px;">
                                            <div style="font-size:10px;font-weight:700;color:var(--amber);text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px;"><i class="fa-solid fa-star me-1"></i>Student Rating</div>
                                            <div style="font-size:13px;color:var(--amber);"><?= str_repeat('★',(int)$bkRating['rating']).str_repeat('☆',5-(int)$bkRating['rating']) ?></div>
                                            <?php if($bkRating['description']): ?><div style="font-size:11px;color:var(--muted);margin-top:3px;"><?= htmlspecialchars(mb_strimwidth($bkRating['description'],0,70,'…')) ?></div><?php endif; ?>
                                        </div>
                                        <?php elseif($bk['status']==='done'): ?>
                                        <div style="margin-top:6px;font-size:11px;color:var(--muted-2);"><i class="fa-solid fa-hourglass me-1"></i>Not rated yet</div>
                                        <?php endif; ?>
                                    </td>

                                    <td style="padding:12px 10px;font-size:12px;color:var(--muted-2);">
                                        <?= date('M d, Y', strtotime($bk['requested_at'])) ?>
                                    </td>

                                    <td style="padding:12px 10px;">
                                        <?php if($bk['status']==='pending'): ?>
                                        <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                            <button class="bs" onclick="openRM(<?= $bk['request_id'] ?>,'approve')">
                                                <i class="fa-solid fa-check me-1"></i>Approve
                                            </button>
                                            <button class="bd" onclick="openRM(<?= $bk['request_id'] ?>,'reject')">
                                                <i class="fa-solid fa-xmark me-1"></i>Reject
                                            </button>
                                        </div>

                                        <?php elseif($bk['status']==='approved'): ?>
                                        <form method="POST" action="instructor_dashboard1.php?section=requests"
                                            onsubmit="return confirm('Mark done? Student will be asked to rate.');"
                                            style="display:inline;">
                                            <input type="hidden" name="action" value="mark_done">
                                            <input type="hidden" name="request_id" value="<?= $bk['request_id'] ?>">
                                            <button type="submit" class="bdone">
                                                <i class="fa-solid fa-flag-checkered"></i> Mark as Done
                                            </button>
                                        </form>

                                        <?php elseif($bk['status']==='done'): ?>
                                        <span style="font-size:12px;color:var(--green);">
                                            <i class="fa-solid fa-circle-check me-1"></i>Completed
                                        </span>

                                        <?php elseif($bk['status']==='rejected'): ?>
                                        <span style="font-size:12px;color:#ef4444;">Rejected</span>

                                        <?php elseif($bk['status']==='cancelled'): ?>
                                        <span style="font-size:12px;color:var(--muted-2);">Cancelled</span>
                                        <?php endif; ?>
                                    </td>
                                    <div id="noResults" class="empty-state" style="display:none;">
                                </tr>
                                <?php endwhile; ?>
                                <tr id="noResultsRow" style="display:none;">
                                    <td colspan="8" style="text-align:center;padding:20px;color:var(--muted);">
                                        <p style="margin-top:8px;"> <i class="fa-solid fa-search"></i> 
                                        No matching students found.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- ══ PROFILE ══ -->
                <div id="section-profile" class="section <?= $activeSection==='profile'?'active':'' ?>">
                    <div class="row g-3">
                        <div class="col-12 col-lg-4">
                            <div class="hcard">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="havt"><?= $initials ?></div>
                                    <div>
                                        <div style="font-family:var(--font-display);font-size:17px;font-weight:800;">
                                            <?= $fullname ?></div>
                                        <div style="font-size:12px;color:var(--muted);margin-top:3px;"><?= $email ?>
                                        </div>
                                        <div style="margin-top:8px;"><span class="tag tag-amber"><i
                                                    class="fa-solid fa-chalkboard-user me-1"></i>Instructor</span></div>
                                    </div>
                                </div>
                                <div class="hdiv"></div>
                                <div class="row text-center g-0">
                                    <div class="col-4">
                                        <div style="font-family:var(--font-display);font-size:22px;font-weight:800;">
                                            <?= $totalStudents ?></div>
                                        <div style="font-size:11px;color:var(--muted);">Students</div>
                                    </div>
                                    <div class="col-4">
                                        <div style="font-family:var(--font-display);font-size:22px;font-weight:800;">
                                            <?= $doneSessions ?></div>
                                        <div style="font-size:11px;color:var(--muted);">Done</div>
                                    </div>
                                    <div class="col-4">
                                        <div
                                            style="font-family:var(--font-display);font-size:22px;font-weight:800;color:var(--amber);">
                                            <?= $avgRating?:0 ?></div>
                                        <div style="font-size:11px;color:var(--muted);">Rating</div>
                                    </div>
                                </div>
                            </div>
                            <div class="panel mb-3">
                                <div class="panel-head">
                                    <div class="panel-title">Account Details</div>
                                </div>
                                <div class="ir">
                                    <div class="ii"><i class="fa-solid fa-user"></i></div>
                                    <div>
                                        <div class="il">Full Name</div>
                                        <div class="iv"><?= $fullname ?></div>
                                    </div>
                                </div>
                                <div class="ir">
                                    <div class="ii" style="color:var(--green);"><i class="fa-solid fa-envelope"></i>
                                    </div>
                                    <div>
                                        <div class="il">Email</div>
                                        <div class="iv"><?= $email ?></div>
                                    </div>
                                </div>
                                <div class="ir">
                                    <div class="ii" style="color:var(--amber);"><i
                                            class="fa-solid fa-calendar-plus"></i></div>
                                    <div>
                                        <div class="il">Member Since</div>
                                        <div class="iv"><?= $joinDate ?></div>
                                    </div>
                                </div>
                                <div class="ir">
                                    <div class="ii" style="color:#c084fc;"><i class="fa-solid fa-pen-to-square"></i>
                                    </div>
                                    <div>
                                        <div class="il">Last Modified</div>
                                        <div class="iv"><?= $modified ?></div>
                                    </div>
                                </div>
                                <div class="ir">
                                    <div class="ii" style="color:var(--red);"><i class="fa-solid fa-clock"></i></div>
                                    <div>
                                        <div class="il">Last Login</div>
                                        <div class="iv">
                                            <?= $lastLogin?date('M d, Y g:i A',strtotime($lastLogin['login_time'])).(!empty($lastLogin['city'])?' · '.htmlspecialchars($lastLogin['city']):''):'—' ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-8">
                            <div class="panel mb-3">
                                <div class="panel-head">
                                    <div>
                                        <div class="panel-title"><i class="fa-solid fa-certificate me-2"
                                                style="color:var(--amber);"></i>My Certificate</div>
                                    </div>
                                    <?= ($certImage&&file_exists($uploadDir.$certImage))?'<span class="tag tag-green">Uploaded</span>':'<span class="tag tag-red">Not uploaded</span>' ?>
                                </div>
                                <div class="panel-body">
                                    <?php if($certImage&&file_exists($uploadDir.$certImage)): ?>
                                    <div class="cert-box mb-4">
                                        <div style="font-size:12px;color:var(--muted);margin-bottom:12px;">Click to view
                                            full size</div><img
                                            src="../assets/uploads/certificates/<?= htmlspecialchars($certImage) ?>"
                                            class="cert-prev mb-3" onclick="window.open(this.src)"><br>
                                        <form method="POST" onsubmit="return confirm('Remove?');"
                                            style="display:inline;"><input type="hidden" name="action"
                                                value="delete_certificate"><button type="submit" class="bd"
                                                style="font-size:12px;"><i
                                                    class="fa-solid fa-trash me-1"></i>Remove</button></form>
                                    </div>
                                    <?php else: ?><div class="cert-box mb-4"><i class="fa-solid fa-file-image"
                                            style="font-size:40px;color:var(--muted-2);display:block;margin-bottom:12px;"></i>
                                        <div style="color:var(--muted);font-size:13px;">No certificate uploaded.</div>
                                    </div><?php endif; ?>
                                    <form method="POST" action="instructor_dashboard1.php?section=profile"
                                        enctype="multipart/form-data">
                                        <input type="hidden" name="action" value="upload_certificate">
                                        <div style="margin-bottom:12px;"><label
                                                class="fl"><?= $certImage?'Replace':'Upload' ?>
                                                Certificate</label><input type="file" name="certificate" class="fi"
                                                accept=".jpg,.jpeg,.png,.webp" onchange="prevCert(this)">
                                            <div style="font-size:11px;color:var(--muted-2);margin-top:5px;">
                                                JPG/PNG/WEBP · Max 5MB</div>
                                        </div>
                                        <div id="cpw" style="display:none;margin-bottom:14px;">
                                            <div style="font-size:12px;color:var(--muted);margin-bottom:6px;">Preview:
                                            </div><img id="cpi"
                                                style="max-width:100%;max-height:180px;border-radius:10px;border:1px solid var(--border);"
                                                alt="">
                                        </div>
                                        <button type="submit" class="bp"><i
                                                class="fa-solid fa-upload me-1"></i><?= $certImage?'Replace':'Upload' ?></button>
                                    </form>
                                </div>
                            </div>
                            <div class="panel">
                                <div class="panel-head">
                                    <div>
                                        <div class="panel-title"><i class="fa-solid fa-shield-halved me-2"
                                                style="color:var(--accent);"></i>Login History</div>
                                        <div class="panel-sub">Last 8</div>
                                    </div>
                                    <div style="font-size:12px;color:var(--muted);"><span
                                            style="color:var(--green);font-weight:600;"><?= $totalLogins ?></span> ·
                                        <span style="color:var(--red);font-weight:600;"><?= $failedLogins ?>
                                            failed</span>
                                    </div>
                                </div>
                                <div class="tbl-wrap">
                                    <table class="tbl">
                                        <thead>
                                            <tr>
                                                <th>Date & Time</th>
                                                <th>Status</th>
                                                <th>Location</th>
                                                <th>IP</th>
                                                <th>Remarks</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if($loginHistory->num_rows===0): ?><tr>
                                                <td colspan="5" class="ec">No history.</td>
                                            </tr>
                                            <?php else: while($lg=$loginHistory->fetch_assoc()): $isS=$lg['login_status']==='success'; $loc=array_filter([$lg['city']??'',$lg['region']??'',$lg['country']??'']); $ls=!empty($loc)?htmlspecialchars(implode(', ',$loc)):'—'; ?>
                                            <tr>
                                                <td>
                                                    <div style="font-weight:600;">
                                                        <?= date('M d, Y',strtotime($lg['login_time'])) ?></div>
                                                    <div style="font-size:11px;color:var(--muted);">
                                                        <?= date('g:i A',strtotime($lg['login_time'])) ?></div>
                                                </td>
                                                <td><span
                                                        class="tag <?= $isS?'tag-green':'tag-red' ?>"><?= ucfirst($lg['login_status']) ?></span>
                                                </td>
                                                <td style="font-size:12px;color:var(--muted);">
                                                    <?php if(!empty($lg['maps_link'])): ?><a
                                                        href="<?= htmlspecialchars($lg['maps_link']) ?>" target="_blank"
                                                        style="color:var(--accent);text-decoration:none;"><i
                                                            class="fa-solid fa-location-dot me-1"></i><?= $ls ?></a><?php else: echo $ls; endif; ?>
                                                </td>
                                                <td style="font-size:12px;color:var(--muted-2);font-family:monospace;">
                                                    <?= htmlspecialchars($lg['ip_address']??'—') ?></td>
                                                <td style="font-size:12px;color:var(--muted);">
                                                    <?= htmlspecialchars($lg['remarks']??'—') ?></td>
                                            </tr>
                                            <?php endwhile; endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="height:32px;"></div>
            </div>
        </div>
    </div>
    <!-- UPDATE COURSE MODAL -->
    <div class="modal fade modal-dark" id="ucModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-pen me-2" style="color:var(--accent);"></i>Update
                        Course</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="instructor_dashboard1.php?section=courses"><input type="hidden"
                        name="action" value="update_course"><input type="hidden" name="course_id" id="ucId">
                    <div class="modal-body">
                        <div style="margin-bottom:14px;"><label class="fl">Course</label>
                            <div id="ucLabel" style="font-size:14px;font-weight:600;padding:6px 0;"></div>
                        </div>
                        <div style="margin-bottom:14px;"><label class="fl">Description</label><textarea
                                name="uc_description" id="ucDesc" class="di dt"
                                placeholder="Describe this course…"></textarea></div>
                        <div class="row g-2">
                            <div class="col-6"><label class="fl">Duration (hrs)</label><input type="number"
                                    name="uc_duration_hrs" id="ucDur" class="di" step="0.5" min="1"
                                    placeholder="e.g. 15"></div>
                            <div class="col-6"><label class="fl">Price (₱)</label><input type="number" name="uc_price"
                                    id="ucPrice" class="di" step="0.01" min="0" placeholder="e.g. 4500"></div>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="bd"
                            data-bs-dismiss="modal">Cancel</button><button type="submit" class="bp"><i
                                class="fa-solid fa-save me-1"></i>Save</button></div>
                </form>
            </div>
        </div>
    </div>

    <!-- EDIT SCHEDULE MODAL -->
    <div class="modal fade modal-dark" id="esModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-pen me-2" style="color:var(--accent);"></i>Edit
                        Schedule</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="instructor_dashboard1.php?section=schedule"><input type="hidden"
                        name="action" value="edit_schedule"><input type="hidden" name="schedule_id" id="esId">
                    <div class="modal-body">
                        <div style="margin-bottom:14px;"><label class="fl">Price (₱)</label><input type="number"
                                name="slot_price" id="esPrice" class="di" step="0.01" min="0" placeholder="e.g. 4500">
                        </div>
                        <div style="margin-bottom:14px;"><label class="fl">Max Students</label><select
                                name="max_students" id="esMax" class="di ds"><?php for($n=1;$n<=20;$n++): ?><option
                                    value="<?= $n ?>"><?= $n ?> student<?= $n>1?'s':'' ?></option>
                                <?php endfor; ?></select></div>
                        <div><label class="fl">Notes</label><textarea name="notes" id="esNotes" class="di dt"
                                placeholder="e.g. Meet at Gate 2…"></textarea></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="bd"
                            data-bs-dismiss="modal">Cancel</button><button type="submit" class="bp"><i
                                class="fa-solid fa-save me-1"></i>Save</button></div>
                </form>
            </div>
        </div>
    </div>

    <!-- APPROVE/REJECT MODAL -->
    <div class="modal fade modal-dark" id="rmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rmTitle">Review Request</h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="instructor_dashboard1.php?section=requests"><input type="hidden"
                        name="action" id="rmAction"><input type="hidden" name="request_id" id="rmId">
                    <div class="modal-body"><label class="fl">Note to Student (optional)</label><textarea
                            name="instructor_note" class="di dt"
                            placeholder="e.g. See you at Gate 2 this coming schedule…"></textarea></div>
                    <div class="modal-footer"><button type="button" class="bd"
                            data-bs-dismiss="modal">Cancel</button><button type="submit" class="bp"
                            id="rmBtn">Confirm</button></div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
    const T = {
        dashboard: 'Overview',
        schedule: 'My Schedule',
        students: 'My Students',
        performance: 'Performance',
        communication: 'Communication',
        courses: 'My Courses',
        requests: 'Enrolment Requests',
        profile: 'My Profile'
    };

    function showSection(n) {
        document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
        document.querySelectorAll('.nav-item').forEach(x => x.classList.remove('active'));
        const el = document.getElementById('section-' + n);
        if (el) el.classList.add('active');
        document.getElementById('page-title-text').textContent = T[n] || 'Dashboard';
        document.querySelectorAll('.nav-item').forEach(x => {
            if (x.getAttribute('onclick') && x.getAttribute('onclick').includes("'" + n + "'")) x.classList.add(
                'active');
        });
        const u = new URL(window.location.href);
        u.searchParams.set('section', n);
        window.history.pushState({}, '', u);
    }

    function openSidebar() {
        document.getElementById('sidebar').classList.add('open');
        document.getElementById('sidebarOverlay').classList.add('show');
    }

    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('sidebarOverlay').classList.remove('show');
    }

    function handleSearch(v) {
        const a = document.querySelector('.section.active');
        if (!a) return;
        a.querySelectorAll('.tbl tbody tr').forEach(r => {
            r.style.display = r.textContent.toLowerCase().includes(v.toLowerCase()) ? '' : 'none';
        });
    }

    function fReq(s, btn) {
        document.querySelectorAll('#section-requests .tab').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        document.querySelectorAll('.req-item').forEach(i => {
            i.style.display = (s === 'all' || i.dataset.status === s) ? '' : 'none';
        });
    }

    function openUC(id, type, desc, dur, price) {
        document.getElementById('ucId').value = id;
        document.getElementById('ucLabel').textContent = type;
        document.getElementById('ucDesc').value = desc;
        document.getElementById('ucDur').value = dur || '';
        document.getElementById('ucPrice').value = price || '';
        new bootstrap.Modal(document.getElementById('ucModal')).show();
    }

    function openES(id, max, notes, price) {
        document.getElementById('esId').value = id;
        document.getElementById('esMax').value = max;
        document.getElementById('esNotes').value = notes || '';
        document.getElementById('esPrice').value = price || '';
        new bootstrap.Modal(document.getElementById('esModal')).show();
    }

    function goPostSchedule(courseId) {
        showSection('schedule');
        const sel = document.getElementById('slotCourseId');
        if (sel) {
            sel.value = courseId;
            onSlotCourseChange(sel);
            sel.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }
    }

    function onSlotCourseChange(sel) {
        const box = document.getElementById('slotHrsBox'),
            txt = document.getElementById('slotHrsText');
        if (!sel.value) {
            box.style.display = 'none';
            return;
        }
        const ct = (sel.options[sel.selectedIndex].getAttribute('data-type') || '');
        const hrs = (ct.indexOf('TDC') === 0) ? 15 : 8;
        txt.textContent = hrs + ' hours';
        box.style.display = 'block';
    }

    function validateSlotDay(inp) {
        const dayErr = document.getElementById('slotDayError'),
            dayOk = document.getElementById('slotDayOk'),
            dayNm = document.getElementById('slotDayName'),
            btn = document.getElementById('slotSubmitBtn');
        if (!inp.value) {
            dayErr.style.display = 'none';
            dayOk.style.display = 'none';
            return;
        }
        const d = new Date(inp.value + 'T00:00:00');
        const dow = d.getDay(); /* 0=Sun,6=Sat */
        if (dow === 0) {
            dayErr.style.display = 'block';
            dayOk.style.display = 'none';
            btn.disabled = true;
            inp.style.borderColor = 'var(--red)';
        } else {
            const names = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            dayNm.textContent = names[dow] + ', ' + d.toLocaleDateString('en-PH', {
                month: 'long',
                day: 'numeric',
                year: 'numeric'
            });
            dayErr.style.display = 'none';
            dayOk.style.display = 'block';
            btn.disabled = false;
            inp.style.borderColor = 'var(--green)';
        }
    }

    function openRM(rid, type) {
        document.getElementById('rmId').value = rid;
        document.getElementById('rmAction').value = type + '_request';
        document.getElementById('rmTitle').textContent = type === 'approve' ? '✅ Approve Request' : '❌ Reject Request';
        document.getElementById('rmBtn').textContent = type === 'approve' ? 'Approve' : 'Reject';
        new bootstrap.Modal(document.getElementById('rmModal')).show();
    }

    function prevCert(i) {
        const w = document.getElementById('cpw'),
            img = document.getElementById('cpi');
        if (i.files && i.files[0]) {
            const r = new FileReader();
            r.onload = e => {
                img.src = e.target.result;
                w.style.display = 'block';
            };
            r.readAsDataURL(i.files[0]);
        }
    }

    function markRead() {
        fetch('instructor_dashboard1.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'action=mark_all_read'
        });
    }
    </script>

    <script>
    document.getElementById('search').addEventListener('keyup', function() {
        const searchValue = this.value.toLowerCase();
        const rows = document.querySelectorAll('#reqList .req-item');
        const noResultsRow = document.getElementById('noResultsRow');

        let visibleCount = 0;

        rows.forEach(row => {
            const text = row.innerText.toLowerCase();

            if (text.includes(searchValue)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // show/hide table row
        noResultsRow.style.display = (visibleCount === 0) ? '' : 'none';
    });
    </script>
</body>

</html>