<?php
    require '../includes/db.php';
    require 'admin_auth.php';
    require 'admin_process.php';

    if (!function_exists('adminSchedDateLabel')) {
        function adminSchedDateLabel(array $row): string {
            if (!empty($row['slot_date'])) return date('M d, Y', strtotime($row['slot_date']));
            return $row['day_of_week'] ?? '—';
        }
    }
    if (!function_exists('adminSchedHoursLabel')) {
        function adminSchedHoursLabel(array $row): string {
            if (!empty($row['slot_hrs'])) return rtrim(rtrim(number_format((float)$row['slot_hrs'], 1, '.', ''), '0'), '.') . ' hrs';
            if (!empty($row['duration_hrs'])) return rtrim(rtrim(number_format((float)$row['duration_hrs'], 1, '.', ''), '0'), '.') . ' hrs';
            if (!empty($row['start_time']) && !empty($row['end_time'])) return date('g:i A', strtotime($row['start_time'])) . ' – ' . date('g:i A', strtotime($row['end_time']));
            if (!empty($row['course_type'])) return strpos($row['course_type'], 'TDC') === 0 ? '15 hrs' : '8 hrs';
            return '—';
        }
    }
    if (!function_exists('adminSchedPriceLabel')) {
        function adminSchedPriceLabel(array $row): string {
            $price = $row['slot_price'] ?? $row['price'] ?? null;
            return ($price !== null && $price !== '' && (float)$price > 0) ? '₱' . number_format((float)$price, 0) : 'Free';
        }
    }

    $adminPendingList = $conn->query("SELECT er.request_id, er.status, er.requested_at, er.student_note, st.fullname AS sname, i.fullname AS iname,
        ic.course_type, ic.duration_hrs, ic.price, cs.schedule_id, cs.day_of_week, cs.slot_date, cs.slot_hrs, cs.slot_price, cs.max_students, cs.is_active,
        (SELECT COUNT(*) FROM enrolment_requests WHERE schedule_id=cs.schedule_id AND status NOT IN('rejected','cancelled')) AS enrolled_cnt
        FROM enrolment_requests er
        JOIN course_schedules cs ON cs.schedule_id=er.schedule_id
        JOIN instructor_courses ic ON ic.course_id=cs.course_id
        JOIN students st ON st.student_id=er.student_id
        JOIN instructors i ON i.instructor_id=cs.instructor_id
        WHERE er.status='pending'
        ORDER BY COALESCE(cs.slot_date,'9999-12-31') ASC, er.requested_at ASC
        LIMIT 12");

    $adminRecentApproved = $conn->query("SELECT er.request_id, er.status, er.reviewed_at, st.fullname AS sname, i.fullname AS iname,
        ic.course_type, ic.duration_hrs, ic.price, cs.schedule_id, cs.day_of_week, cs.slot_date, cs.slot_hrs, cs.slot_price, cs.max_students, cs.is_active,
        (SELECT COUNT(*) FROM enrolment_requests WHERE schedule_id=cs.schedule_id AND status NOT IN('rejected','cancelled')) AS enrolled_cnt
        FROM enrolment_requests er
        JOIN course_schedules cs ON cs.schedule_id=er.schedule_id
        JOIN instructor_courses ic ON ic.course_id=cs.course_id
        JOIN students st ON st.student_id=er.student_id
        JOIN instructors i ON i.instructor_id=cs.instructor_id
        WHERE er.status='approved'
        ORDER BY er.reviewed_at DESC, er.request_id DESC
        LIMIT 8");

    $adminScheduleView = $conn->query("SELECT cs.schedule_id, cs.instructor_id, cs.course_id, cs.day_of_week, cs.slot_date, cs.slot_hrs, cs.slot_price, cs.max_students, cs.notes, cs.is_active,
        i.fullname AS iname, ic.course_type, ic.duration_hrs, ic.price,
        (SELECT COUNT(*) FROM enrolment_requests WHERE schedule_id=cs.schedule_id AND status='pending') AS pending_cnt,
        (SELECT COUNT(*) FROM enrolment_requests WHERE schedule_id=cs.schedule_id AND status='approved') AS approved_cnt,
        (SELECT COUNT(*) FROM enrolment_requests WHERE schedule_id=cs.schedule_id AND status='done') AS done_cnt,
        (SELECT COUNT(*) FROM enrolment_requests WHERE schedule_id=cs.schedule_id AND status NOT IN('rejected','cancelled')) AS enrolled_cnt
        FROM course_schedules cs
        JOIN instructors i ON i.instructor_id=cs.instructor_id
        JOIN instructor_courses ic ON ic.course_id=cs.course_id
        ORDER BY COALESCE(cs.slot_date,'9999-12-31') ASC, i.fullname ASC, cs.schedule_id ASC");

    $adminPendingCount = 0;
    $adminApprovedCount = 0;
    $adminBookedCount = 0;
    $adminFullCount = 0;
    $adminScheduleCount = 0;
    $adminScheduleRows = [];
    if ($adminScheduleView) {
        while ($row = $adminScheduleView->fetch_assoc()) {
            $row['pending_cnt'] = (int)($row['pending_cnt'] ?? 0);
            $row['approved_cnt'] = (int)($row['approved_cnt'] ?? 0);
            $row['done_cnt'] = (int)($row['done_cnt'] ?? 0);
            $row['enrolled_cnt'] = (int)($row['enrolled_cnt'] ?? 0);
            $row['max_students'] = (int)($row['max_students'] ?? 0);
            $adminPendingCount += $row['pending_cnt'];
            $adminApprovedCount += $row['approved_cnt'];
            if ($row['enrolled_cnt'] > 0) $adminBookedCount++;
            if ($row['max_students'] > 0 && $row['enrolled_cnt'] >= $row['max_students']) $adminFullCount++;
            $adminScheduleRows[] = $row;
        }
        $adminScheduleCount = count($adminScheduleRows);
    }

    // ── RATING LEADERBOARDS ──────────────────────────────────────────────
    // Top 5 best-rated instructors
    $topInstructors = $conn->query("SELECT i.instructor_id, i.fullname, ROUND(AVG(sr.rating),1) AS avg_rating, COUNT(sr.rating_id) AS total_ratings, (SELECT ic.course_type FROM instructor_courses ic WHERE ic.instructor_id=i.instructor_id AND ic.is_active=1 ORDER BY ic.course_id ASC LIMIT 1) AS main_course FROM session_ratings sr JOIN instructors i ON i.instructor_id=sr.instructor_id GROUP BY i.instructor_id HAVING total_ratings >= 1 ORDER BY avg_rating DESC, total_ratings DESC LIMIT 5");
    // Top 5 worst-rated instructors
    $worstInstructors = $conn->query("SELECT i.instructor_id, i.fullname, ROUND(AVG(sr.rating),1) AS avg_rating, COUNT(sr.rating_id) AS total_ratings, (SELECT ic.course_type FROM instructor_courses ic WHERE ic.instructor_id=i.instructor_id AND ic.is_active=1 ORDER BY ic.course_id ASC LIMIT 1) AS main_course FROM session_ratings sr JOIN instructors i ON i.instructor_id=sr.instructor_id GROUP BY i.instructor_id HAVING total_ratings >= 1 ORDER BY avg_rating ASC, total_ratings DESC LIMIT 5");
    // Top 5 best-rated students (students who gave consistent high ratings — proxy: high completion + sessions done)
    $topStudents = $conn->query("SELECT st.student_id, st.fullname, COUNT(er.request_id) AS done_sessions, ROUND(AVG(sr.rating),1) AS avg_given, (SELECT ic.course_type FROM enrolment_requests er2 JOIN course_schedules cs2 ON cs2.schedule_id=er2.schedule_id JOIN instructor_courses ic ON ic.course_id=cs2.course_id WHERE er2.student_id=st.student_id AND er2.status='done' ORDER BY er2.done_at DESC LIMIT 1) AS latest_course FROM students st JOIN enrolment_requests er ON er.student_id=st.student_id AND er.status='done' LEFT JOIN session_ratings sr ON sr.request_id=er.request_id GROUP BY st.student_id HAVING done_sessions >= 1 ORDER BY done_sessions DESC, avg_given DESC LIMIT 5");
    // Worst 5 students (least sessions done / most cancellations)
    $worstStudents = $conn->query("SELECT st.student_id, st.fullname, COUNT(CASE WHEN er.status='done' THEN 1 END) AS done_sessions, COUNT(CASE WHEN er.status='cancelled' THEN 1 END) AS cancelled_sessions, COUNT(CASE WHEN er.status='rejected' THEN 1 END) AS rejected_sessions, (SELECT ic.course_type FROM enrolment_requests er2 JOIN course_schedules cs2 ON cs2.schedule_id=er2.schedule_id JOIN instructor_courses ic ON ic.course_id=cs2.course_id WHERE er2.student_id=st.student_id ORDER BY er2.requested_at DESC LIMIT 1) AS latest_course FROM students st JOIN enrolment_requests er ON er.student_id=st.student_id GROUP BY st.student_id HAVING (COUNT(CASE WHEN er.status='cancelled' THEN 1 END) + COUNT(CASE WHEN er.status='rejected' THEN 1 END)) > 0 ORDER BY (COUNT(CASE WHEN er.status='cancelled' THEN 1 END) + COUNT(CASE WHEN er.status='rejected' THEN 1 END)) DESC, COUNT(CASE WHEN er.status='done' THEN 1 END) ASC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>MotoSafe – Admin</title>
    <link href="../assets/img/logo.png" rel="icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>

<body>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>
    <div class="app-wrapper">
        <!---- Sidebar ---->
        <aside class="sidebar" id="sidebar">
            <div class="logo">
                <div class="logo-icon"><img src="../assets/img/logo.png" alt="MotoSafe"></div>Moto<span>Safe</span>
            </div>
            <div class="nav-label">Main</div>
            <a class="nav-item <?= $activeSection==='dashboard'?'active':'' ?>" href="#"
                onclick="showSection('dashboard');return false;"><i class="fa-solid fa-gauge-high"></i> Overview</a>
            <div class="nav-label">Management</div>
            <a class="nav-item <?= $activeSection==='students'?'active':'' ?>" href="#"
                onclick="showSection('students');return false;"><i class="fa-solid fa-user-graduate"></i> Students <span
                    style="margin-left:auto;background:rgba(61,127,255,.2);color:#7daeff;padding:1px 7px;border-radius:999px;font-size:10px;font-weight:700;"><?= $totalStudents ?></span></a>
            <a class="nav-item <?= $activeSection==='instructors'?'active':'' ?>" href="#"
                onclick="showSection('instructors');return false;"><i class="fa-solid fa-chalkboard-user"></i>
                Instructors <span
                    style="margin-left:auto;background:rgba(192,132,252,.2);color:#c084fc;padding:1px 7px;border-radius:999px;font-size:10px;font-weight:700;"><?= $totalInstructors ?></span></a>
            <a class="nav-item <?= $activeSection==='scheduling'?'active':'' ?>" href="#"
                onclick="showSection('scheduling');return false;"><i class="fa-solid fa-calendar-days"></i> Scheduling
                <?php if($pendingEnrols>0):?><span
                    style="margin-left:auto;background:rgba(245,158,11,.2);color:#fbbf5a;padding:1px 7px;border-radius:999px;font-size:10px;font-weight:700;"><?= $pendingEnrols ?></span><?php endif;?></a>
            <a class="nav-item <?= $activeSection==='payments'?'active':'' ?>" href="#"
                onclick="showSection('payments');return false;"><i class="fa-solid fa-credit-card"></i> Payments</a>
            <div class="nav-label">Analytics</div>
            <a class="nav-item <?= $activeSection==='reports'?'active':'' ?>" href="#"
                onclick="showSection('reports');return false;"><i class="fa-solid fa-chart-bar"></i> Reports</a>
            <div class="nav-label">System</div>
            <a class="nav-item <?= $activeSection==='settings'?'active':'' ?>" href="#"
                onclick="showSection('settings');return false;"><i class="fa-solid fa-gear"></i> Settings</a>
            <div class="sidebar-bottom">
                <div class="user-row">
                    <div class="avatar"><?= $adminIn ?></div>
                    <div>
                        <div class="user-name"><?= $adminName ?></div>
                        <div class="user-role">Administrator</div>
                    </div>
                </div>
                <a class="nav-item" href="../admin/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Sign Out</a>
            </div>
        </aside>

        <div class="sidebar-push">
            <div class="topbar">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-btn d-lg-none" onclick="openSidebar()"><i class="fa-solid fa-bars"></i></div>
                    <div id="page-title-text" class="page-title">Overview</div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span style="font-size:12px;color:var(--muted);"><?= date('l, F d Y') ?></span>
                </div>
            </div>

            <div class="content">
                <!-- ══════════════════════════════════════════════════
                                    OVERVIEW
                    ══════════════════════════════════════════════════ -->
                <div id="section-dashboard" class="section <?= $activeSection==='dashboard'?'active':'' ?>">
                    <div class="hero-strip">
                        <div class="hero-text">
                            <div class="greeting"><?= date('l') ?>, <?= date('F Y') ?></div>
                            <h2>Good <?= date('H')<12?'morning':(date('H')<18?'afternoon':'evening') ?>,
                                <?= $firstName ?> 👋</h2>
                            <p>You're managing <strong><?= $totalStudents ?></strong> students and
                                <strong><?= $totalInstructors ?></strong> instructors.
                                <?= $pendingEnrols>0?"<span style='color:var(--amber);'>$pendingEnrols enrolment request".($pendingEnrols>1?'s':'')." awaiting approval.</span>":"Everything looks good today." ?>
                            </p>
                        </div>
                    </div>
                    <!---- Stat cards ---->
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-xl-3">
                            <div class="stat-card" onclick="showSection('students')"
                                style="cursor:pointer;border-color:rgba(61,127,255,.2);">
                                <div class="stat-top">
                                    <div class="stat-label">Total Students</div>
                                    <div class="stat-icon" style="color:var(--accent);"><i
                                            class="fa-solid fa-user-graduate"></i></div>
                                </div>
                                <div class="stat-value"><?= $totalStudents ?></div>
                                <p class="stat-sub"><?= $verifiedStudents ?> verified ·
                                    <?= $totalStudents-$verifiedStudents ?> unverified</p>
                            </div>
                        </div>
                        <div class="col-6 col-xl-3">
                            <div class="stat-card" onclick="showSection('instructors')"
                                style="cursor:pointer;border-color:rgba(192,132,252,.2);">
                                <div class="stat-top">
                                    <div class="stat-label">Total Instructors</div>
                                    <div class="stat-icon" style="color:var(--purple);"><i
                                            class="fa-solid fa-chalkboard-user"></i></div>
                                </div>
                                <div class="stat-value"><?= $totalInstructors ?></div>
                                <p class="stat-sub"><?= $activeScheds ?> active schedule slots</p>
                            </div>
                        </div>
                        <div class="col-6 col-xl-3">
                            <div class="stat-card" onclick="showSection('scheduling')"
                                style="cursor:pointer;border-color:rgba(245,158,11,.2);">
                                <div class="stat-top">
                                    <div class="stat-label">Pending Enrolments</div>
                                    <div class="stat-icon" style="color:var(--amber);"><i
                                            class="fa-solid fa-hourglass-half"></i></div>
                                </div>
                                <div class="stat-value"><?= $pendingEnrols ?></div>
                                <p class="stat-sub"><?= $totalEnrols ?> total requests</p>
                            </div>
                        </div>
                        <div class="col-6 col-xl-3">
                            <div class="stat-card" onclick="showSection('reports')"
                                style="cursor:pointer;border-color:rgba(16,185,129,.2);">
                                <div class="stat-top">
                                    <div class="stat-label">Sessions Done</div>
                                    <div class="stat-icon" style="color:var(--green);"><i
                                            class="fa-solid fa-flag-checkered"></i></div>
                                </div>
                                <div class="stat-value"><?= $doneSessions ?></div>
                                <p class="stat-sub">Completed driving sessions</p>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3">
                        <!-- Charts -->
                        <div class="col-12 col-lg-8">
                            <div class="panel">
                                <div class="panel-head">
                                    <div>
                                        <div class="panel-title"><i class="fa-solid fa-chart-line me-2"
                                                style="color:var(--accent);"></i>Student Logins — Last 7 Days</div>
                                    </div>
                                </div>
                                <div style="padding:16px 20px;"><canvas id="loginChart" height="80"></canvas></div>
                            </div>
                            <div class="panel" style="padding:0;">
                                <div class="panel-head" style="padding:14px 20px;">
                                    <div>
                                        <div class="panel-title"><i class="fa-solid fa-clock me-2"
                                                style="color:var(--amber);"></i>Recent Activity</div>
                                    </div>
                                </div>
                                <?php if(!$recentLogins||$recentLogins->num_rows===0): ?>
                                <div class="empty-state"><i class="fa-solid fa-clock"></i>
                                    <p>No recent logins.</p>
                                </div>
                                <?php else: while($act=$recentLogins->fetch_assoc()): $isOk=$act['status']==='success'; ?>
                                <div
                                    style="padding:10px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px;">
                                    <?php $ai=initials($act['fullname']); ?>
                                    <div
                                        style="width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg,var(--accent),#1a4fb0);display:grid;place-items:center;font-family:var(--font-display);font-size:11px;font-weight:800;flex-shrink:0;">
                                        <?= $ai ?></div>
                                    <div style="flex:1;">
                                        <div style="font-size:13px;font-weight:600;">
                                            <?= htmlspecialchars($act['fullname']) ?></div>
                                        <div style="font-size:11px;color:var(--muted);">
                                            <?= date('M d, g:i A',strtotime($act['login_datetime'])) ?></div>
                                    </div>
                                    <span
                                        class="tag <?= $isOk?'tag-green':'tag-red' ?>"><?= ucfirst($act['status']) ?></span>
                                </div>
                                <?php endwhile; endif; ?>
                            </div>
                        </div>
                        <div class="col-12 col-lg-4">
                            <!-- Doughnut chart -->
                            <div class="panel">
                                <div class="panel-head">
                                    <div>
                                        <div class="panel-title"><i class="fa-solid fa-chart-pie me-2"
                                                style="color:var(--purple);"></i>Enrolments by Status</div>
                                    </div>
                                </div>
                                <div style="padding:16px;"><canvas id="enrolChart" height="140"></canvas></div>
                                <div style="padding:0 16px 16px;display:grid;grid-template-columns:1fr 1fr;gap:6px;">
                                    <?php foreach(['pending'=>['var(--amber)','Pending'],'approved'=>['var(--accent)','Approved'],'done'=>['var(--green)','Done'],'rejected'=>['var(--red)','Rejected']] as $st2=>[$col,$lbl]): ?>
                                    <div style="background:var(--surface-3);border-radius:8px;padding:8px 10px;">
                                        <div style="font-size:10px;color:var(--muted);margin-bottom:2px;"><?= $lbl ?>
                                        </div>
                                        <div
                                            style="font-family:var(--font-display);font-size:18px;font-weight:800;color:<?= $col ?>;">
                                            <?= $enrByStatus[$st2] ?></div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <!-- Quick announcement -->
                            <div class="panel">
                                <div class="panel-head">
                                    <div class="panel-title"><i class="fa-solid fa-bullhorn me-2"
                                            style="color:var(--amber);"></i>Post Announcement</div>
                                </div>
                                <div class="panel-body">
                                    <form method="POST" action="admin_dashboard2.php?section=dashboard">
                                        <input type="hidden" name="action" value="post_announcement">
                                        <div style="margin-bottom:12px;"><label class="fl">Message</label><textarea
                                                name="message" class="di dt" style="min-height:72px;"
                                                placeholder="Type your announcement…" required></textarea></div>
                                        <div style="margin-bottom:14px;"><label class="fl">Send To</label>
                                            <select name="target" class="di ds">
                                                <option value="all">All (Students + Instructors)</option>
                                                <option value="students">Students Only</option>
                                                <option value="instructors">Instructors Only</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="bp w-100"><i
                                                class="fa-solid fa-paper-plane me-2"></i>Send</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── RATING LEADERBOARDS ────────────────────────────────── -->
                <div style="margin-top:24px;">
                    <div style="font-family:var(--font-display);font-size:16px;font-weight:800;margin-bottom:14px;display:flex;align-items:center;gap:8px;">
                        <i class="fa-solid fa-ranking-star" style="color:var(--amber);"></i> Instructor Rating Overview
                    </div>
                    <div class="row g-3 mb-4">
                        <!-- Top 5 Instructors -->
                        <div class="col-12 col-lg-6">
                            <div class="panel" style="padding:0;">
                                <div class="panel-head" style="padding:14px 20px;">
                                    <div class="panel-title"><i class="fa-solid fa-trophy me-2" style="color:var(--amber);"></i>Top 5 Best-Rated Instructors</div>
                                </div>
                                <?php if(!$topInstructors||$topInstructors->num_rows===0): ?>
                                <div class="empty-state" style="padding:30px;"><i class="fa-solid fa-star"></i><p>No ratings yet.</p></div>
                                <?php else: $rank=1; while($row=$topInstructors->fetch_assoc()):
                                $w=array_filter(explode(' ',trim($row['fullname']))); $ini=strtoupper(substr(implode('',array_map(fn($x)=>$x[0],$w)),0,2));
                                $rankColors=['#f59e0b','#9ca3af','#cd7c4b','var(--muted-2)','var(--muted-2)'];
                                $rc=$rankColors[$rank-1]??'var(--muted-2)'; ?>
                                <div style="padding:12px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px;">
                                    <div style="width:26px;text-align:center;font-family:var(--font-display);font-size:14px;font-weight:800;color:<?= $rc ?>;">#<?= $rank ?></div>
                                    <div style="width:34px;height:34px;border-radius:9px;background:linear-gradient(135deg,var(--accent),#1a4fb0);display:grid;place-items:center;font-family:var(--font-display);font-size:11px;font-weight:800;flex-shrink:0;"><?= $ini ?></div>
                                    <div style="flex:1;">
                                        <div style="font-weight:700;font-size:13px;"><?= htmlspecialchars($row['fullname']) ?></div>
                                        <?php if($row['main_course']): ?><span style="font-size:10px;background:rgba(61,127,255,.12);color:var(--accent);border:1px solid rgba(61,127,255,.2);border-radius:999px;padding:1px 7px;font-weight:600;"><?= htmlspecialchars($row['main_course']) ?></span><?php endif; ?>
                                    </div>
                                    <div style="text-align:right;flex-shrink:0;">
                                        <div style="font-size:15px;font-family:var(--font-display);font-weight:800;color:var(--amber);"><?= $row['avg_rating'] ?> <i class="fa-solid fa-star" style="font-size:12px;"></i></div>
                                        <div style="font-size:11px;color:var(--muted);"><?= $row['total_ratings'] ?> review<?= $row['total_ratings']!=1?'s':'' ?></div>
                                    </div>
                                </div>
                                <?php $rank++; endwhile; endif; ?>
                            </div>
                        </div>
                        <!-- Worst 5 Instructors -->
                        <div class="col-12 col-lg-6">
                            <div class="panel" style="padding:0;">
                                <div class="panel-head" style="padding:14px 20px;">
                                    <div class="panel-title"><i class="fa-solid fa-arrow-trend-down me-2" style="color:var(--red);"></i>5 Lowest-Rated Instructors</div>
                                </div>
                                <?php if(!$worstInstructors||$worstInstructors->num_rows===0): ?>
                                <div class="empty-state" style="padding:30px;"><i class="fa-solid fa-star"></i><p>No ratings yet.</p></div>
                                <?php else: $rank=1; while($row=$worstInstructors->fetch_assoc()):
                                $w=array_filter(explode(' ',trim($row['fullname']))); $ini=strtoupper(substr(implode('',array_map(fn($x)=>$x[0],$w)),0,2)); ?>
                                <div style="padding:12px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px;">
                                    <div style="width:26px;text-align:center;font-family:var(--font-display);font-size:14px;font-weight:800;color:var(--muted-2);">#<?= $rank ?></div>
                                    <div style="width:34px;height:34px;border-radius:9px;background:linear-gradient(135deg,#4b5563,#374151);display:grid;place-items:center;font-family:var(--font-display);font-size:11px;font-weight:800;flex-shrink:0;"><?= $ini ?></div>
                                    <div style="flex:1;">
                                        <div style="font-weight:700;font-size:13px;"><?= htmlspecialchars($row['fullname']) ?></div>
                                        <?php if($row['main_course']): ?><span style="font-size:10px;background:rgba(244,63,94,.1);color:var(--red);border:1px solid rgba(244,63,94,.2);border-radius:999px;padding:1px 7px;font-weight:600;"><?= htmlspecialchars($row['main_course']) ?></span><?php endif; ?>
                                    </div>
                                    <div style="text-align:right;flex-shrink:0;">
                                        <div style="font-size:15px;font-family:var(--font-display);font-weight:800;color:var(--red);"><?= $row['avg_rating'] ?> <i class="fa-solid fa-star" style="font-size:12px;"></i></div>
                                        <div style="font-size:11px;color:var(--muted);"><?= $row['total_ratings'] ?> review<?= $row['total_ratings']!=1?'s':'' ?></div>
                                    </div>
                                </div>
                                <?php $rank++; endwhile; endif; ?>
                            </div>
                        </div>
                    </div>

                    <div style="font-family:var(--font-display);font-size:16px;font-weight:800;margin-bottom:14px;display:flex;align-items:center;gap:8px;">
                        <i class="fa-solid fa-user-graduate" style="color:var(--accent);"></i> Student Performance Overview
                    </div>
                    <div class="row g-3">
                        <!-- Top 5 Students -->
                        <div class="col-12 col-lg-6">
                            <div class="panel" style="padding:0;">
                                <div class="panel-head" style="padding:14px 20px;">
                                    <div class="panel-title"><i class="fa-solid fa-medal me-2" style="color:var(--green);"></i>Top 5 Students (Most Sessions Done)</div>
                                </div>
                                <?php if(!$topStudents||$topStudents->num_rows===0): ?>
                                <div class="empty-state" style="padding:30px;"><i class="fa-solid fa-user-graduate"></i><p>No completed sessions yet.</p></div>
                                <?php else: $rank=1; while($row=$topStudents->fetch_assoc()):
                                $w=array_filter(explode(' ',trim($row['fullname']))); $ini=strtoupper(substr(implode('',array_map(fn($x)=>$x[0],$w)),0,2));
                                $rankColors=['#f59e0b','#9ca3af','#cd7c4b','var(--muted-2)','var(--muted-2)'];
                                $rc=$rankColors[$rank-1]??'var(--muted-2)'; ?>
                                <div style="padding:12px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px;">
                                    <div style="width:26px;text-align:center;font-family:var(--font-display);font-size:14px;font-weight:800;color:<?= $rc ?>;">#<?= $rank ?></div>
                                    <div style="width:34px;height:34px;border-radius:9px;background:linear-gradient(135deg,var(--green),#059669);display:grid;place-items:center;font-family:var(--font-display);font-size:11px;font-weight:800;flex-shrink:0;"><?= $ini ?></div>
                                    <div style="flex:1;">
                                        <div style="font-weight:700;font-size:13px;"><?= htmlspecialchars($row['fullname']) ?></div>
                                        <?php if($row['latest_course']): ?><span style="font-size:10px;background:rgba(16,185,129,.1);color:var(--green);border:1px solid rgba(16,185,129,.2);border-radius:999px;padding:1px 7px;font-weight:600;"><?= htmlspecialchars($row['latest_course']) ?></span><?php endif; ?>
                                    </div>
                                    <div style="text-align:right;flex-shrink:0;">
                                        <div style="font-size:15px;font-family:var(--font-display);font-weight:800;color:var(--green);"><?= $row['done_sessions'] ?> <i class="fa-solid fa-flag-checkered" style="font-size:12px;"></i></div>
                                        <div style="font-size:11px;color:var(--muted);">sessions done</div>
                                        <?php if($row['avg_given']): ?><div style="font-size:11px;color:var(--amber);"><i class="fa-solid fa-star" style="font-size:9px;"></i> Avg given: <?= $row['avg_given'] ?></div><?php endif; ?>
                                    </div>
                                </div>
                                <?php $rank++; endwhile; endif; ?>
                            </div>
                        </div>
                        <!-- Worst 5 Students -->
                        <div class="col-12 col-lg-6">
                            <div class="panel" style="padding:0;">
                                <div class="panel-head" style="padding:14px 20px;">
                                    <div class="panel-title"><i class="fa-solid fa-triangle-exclamation me-2" style="color:var(--amber);"></i>5 Students with Most Cancellations</div>
                                </div>
                                <?php if(!$worstStudents||$worstStudents->num_rows===0): ?>
                                <div class="empty-state" style="padding:30px;"><i class="fa-solid fa-user-graduate"></i><p>No issues found.</p></div>
                                <?php else: $rank=1; while($row=$worstStudents->fetch_assoc()):
                                $w=array_filter(explode(' ',trim($row['fullname']))); $ini=strtoupper(substr(implode('',array_map(fn($x)=>$x[0],$w)),0,2)); ?>
                                <div style="padding:12px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px;">
                                    <div style="width:26px;text-align:center;font-family:var(--font-display);font-size:14px;font-weight:800;color:var(--muted-2);">#<?= $rank ?></div>
                                    <div style="width:34px;height:34px;border-radius:9px;background:linear-gradient(135deg,rgba(244,63,94,.3),rgba(244,63,94,.1));display:grid;place-items:center;font-family:var(--font-display);font-size:11px;font-weight:800;flex-shrink:0;color:var(--red);"><?= $ini ?></div>
                                    <div style="flex:1;">
                                        <div style="font-weight:700;font-size:13px;"><?= htmlspecialchars($row['fullname']) ?></div>
                                        <?php if($row['latest_course']): ?><span style="font-size:10px;background:rgba(244,63,94,.08);color:var(--red);border:1px solid rgba(244,63,94,.2);border-radius:999px;padding:1px 7px;font-weight:600;"><?= htmlspecialchars($row['latest_course']) ?></span><?php endif; ?>
                                    </div>
                                    <div style="text-align:right;flex-shrink:0;">
                                        <div style="font-size:13px;font-family:var(--font-display);font-weight:800;color:var(--red);"><?= (int)$row['cancelled_sessions'] + (int)$row['rejected_sessions'] ?> <i class="fa-solid fa-ban" style="font-size:11px;"></i></div>
                                        <div style="font-size:11px;color:var(--muted);"><?= $row['cancelled_sessions'] ?> cancelled · <?= $row['rejected_sessions'] ?> rejected</div>
                                        <div style="font-size:11px;color:var(--green);"><i class="fa-solid fa-flag-checkered" style="font-size:9px;"></i> <?= $row['done_sessions'] ?> done</div>
                                    </div>
                                </div>
                                <?php $rank++; endwhile; endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════
                                    STUDENTS
                    ══════════════════════════════════════════════════ -->
                <div id="section-students" class="section <?= $activeSection==='students'?'active':'' ?>">
                    <div class="row g-3 mb-3">
                        <div class="col-6 col-md-3">
                            <div class="stat-card">
                                <div class="stat-top">
                                    <div class="stat-label">Total</div>
                                    <div class="stat-icon" style="color:var(--accent);">
                                        <i class="fa-solid fa-users"></i>
                                    </div>
                                </div>
                                <div class="stat-value"><?= $totalStudents ?></div>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <div class="stat-card">
                                <div class="stat-top">
                                    <div class="stat-label">Verified</div>
                                    <div class="stat-icon" style="color:var(--green);">
                                        <i class="fa-solid fa-circle-check"></i>
                                    </div>
                                </div>
                                <div class="stat-value"><?= $verifiedStudents ?></div>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <div class="stat-card">
                                <div class="stat-top">
                                    <div class="stat-label">Unverified</div>
                                    <div class="stat-icon" style="color:var(--amber);">
                                        <i class="fa-solid fa-clock"></i>
                                    </div>
                                </div>
                                <div class="stat-value"><?= $totalStudents-$verifiedStudents ?></div>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <div class="stat-card">
                                <div class="stat-top">
                                    <div class="stat-label">With Sessions Done</div>
                                    <div class="stat-icon" style="color:var(--purple);">
                                        <i class="fa-solid fa-flag-checkered"></i>
                                    </div>
                                </div>
                                <div class="stat-value">
                                    <?= (int)q($conn,"SELECT COUNT(DISTINCT student_id) AS c FROM enrolment_requests WHERE status='done'")->fetch_assoc()['c'] ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-lg-12">
                            <div class="panel" style="padding:0;">
                                <div class="panel-head" style="padding:14px 20px;">
                                    <!-- LEFT -->
                                    <div>
                                        <div class="panel-title">All Students</div>
                                        <div class="panel-sub"><?= $totalStudents ?> registered</div>
                                    </div>

                                    <!-- RIGHT (SEARCH) -->
                                    <div class="panel-search">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                        <input type="text" id="search" placeholder="Search students..." />
                                    </div>
                                </div>

                                <div class="tbl-wrap">
                                    <table class="tbl">
                                        <thead>
                                            <tr>
                                                <th>Student</th>
                                                <th>Email</th>
                                                <th>Joined</th>
                                                <th>Status</th>
                                                <th>Sessions</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="searchTable">
                                            <?php $ci=0; $studentsList->data_seek(0); if($studentsList->num_rows===0): ?>
                                            <tr>
                                                <td colspan="6" class="ec">No students yet.</td>
                                            </tr>
                                            <?php else: while($s=$studentsList->fetch_assoc()): $si=initials($s['fullname']); $ac=$accentColors[$ci++%count($accentColors)]; ?>
                                            <tr>
                                                <td>
                                                    <div style="display:flex;align-items:center;gap:9px;">
                                                        <div
                                                            style="width:32px;height:32px;border-radius:8px;background:<?= $ac ?>;display:grid;place-items:center;font-family:var(--font-display);font-size:11px;font-weight:800;flex-shrink:0;">
                                                            <?= $si ?>
                                                        </div>
                                                        <div style="font-weight:600;font-size:13px;">
                                                            <?= htmlspecialchars($s['fullname']) ?>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td style="color:var(--muted);font-size:12px;">
                                                    <?= htmlspecialchars($s['email']) ?>
                                                </td>

                                                <td style="color:var(--muted);font-size:12px;">
                                                    <?= date('M d, Y', strtotime($s['created_at'])) ?>
                                                </td>

                                                <td>
                                                    <?= $s['is_verified'] ? '<span class="tag tag-green">Verified</span>' : '<span class="tag tag-amber">Unverified</span>' ?>
                                                </td>

                                                <td style="font-size:12px;">
                                                    <span class="bkd me-1"><?= $s['done_cnt'] ?> done</span>
                                                    <?php if($s['app_cnt']>0): ?>
                                                    <span class="bka"><?= $s['app_cnt'] ?> upcoming</span>
                                                    <?php endif; ?>
                                                </td>

                                                <td>
                                                    <div style="display:flex;gap:6px;">
                                                        <button class="be" style="padding:4px 10px;font-size:11px;"
                                                            onclick="openEditStudent(<?= $s['student_id'] ?>,'<?= htmlspecialchars(addslashes($s['fullname'])) ?>','<?= htmlspecialchars(addslashes($s['email'])) ?>',<?= $s['is_verified'] ?>)">
                                                            <i class="fa-solid fa-pen"></i>
                                                        </button>

                                                        <form method="POST"
                                                            action="admin_dashboard2.php?section=students"
                                                            onsubmit="return confirm('Delete <?= htmlspecialchars(addslashes($s['fullname'])) ?> and all their data?');"
                                                            style="display:inline;">
                                                            <input type="hidden" name="action" value="delete_student">
                                                            <input type="hidden" name="student_id"
                                                                value="<?= $s['student_id'] ?>">
                                                            <button type="submit" class="bd"
                                                                style="padding:4px 10px;font-size:11px;">
                                                                <i class="fa-solid fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endwhile; endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════
                                    INSTRUCTORS
                    ══════════════════════════════════════════════════ -->
                <div id="section-instructors" class="section <?= $activeSection==='instructors'?'active':'' ?>">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="panel" style="padding:0;">
                                <div class="panel-head" style="padding:14px 20px;">
                                    <div>
                                        <div class="panel-title">All Instructors</div>
                                        <div class="panel-sub"><?= $totalInstructors ?> registered</div>
                                    </div>
                                    <button type="button" class="add" onclick="openAddInstructorModal()">
                                        <i class="fa-solid fa-plus"></i>
                                        Add Instructor
                                    </button>
                                </div>
                                <div class="tbl-wrap">
                                    <table class="tbl">
                                        <thead>
                                            <tr>
                                                <th>Instructor</th>
                                                <th>Email</th>
                                                <th>Joined</th>
                                                <th>Rating</th>
                                                <th>Slots</th>
                                                <th>Sessions Done</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $ci=0; $instructorsList->data_seek(0); if($instructorsList->num_rows===0): ?>
                                            <tr>
                                                <td colspan="7" class="ec">No instructors yet.</td>
                                            </tr>
                                            <?php else: while($ins=$instructorsList->fetch_assoc()): $ii=initials($ins['fullname']); $ac=$accentColors[$ci++%count($accentColors)]; ?>
                                            <tr>
                                                <td>
                                                    <div style="display:flex;align-items:center;gap:9px;">
                                                        <div
                                                            style="width:32px;height:32px;border-radius:8px;background:<?= $ac ?>;display:grid;place-items:center;font-family:var(--font-display);font-size:11px;font-weight:800;flex-shrink:0;">
                                                            <?= $ii ?>
                                                        </div>
                                                        <div style="font-weight:600;font-size:13px;">
                                                            <?= htmlspecialchars($ins['fullname']) ?>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td style="color:var(--muted);font-size:12px;">
                                                    <?= htmlspecialchars($ins['email']) ?>
                                                </td>

                                                <td style="color:var(--muted);font-size:12px;">
                                                    <?= date('M d, Y', strtotime($ins['created_at'])) ?>
                                                </td>

                                                <td>
                                                    <?php if($ins['avg_rating']): ?>
                                                    <span style="color:var(--amber);">★ <?= $ins['avg_rating'] ?></span>
                                                    <span style="font-size:11px;color:var(--muted);">
                                                        (<?= $ins['total_ratings'] ?>)
                                                    </span>
                                                    <?php else: ?>
                                                    <span style="color:var(--muted);font-size:12px;">—</span>
                                                    <?php endif; ?>
                                                </td>

                                                <td>
                                                    <span class="bkd"><?= $ins['active_scheds'] ?> active</span>
                                                </td>

                                                <td
                                                    style="font-family:var(--font-display);font-weight:700;font-size:15px;">
                                                    <?= $ins['done_sess'] ?>
                                                </td>

                                                <td>
                                                    <div style="display:flex;gap:6px;align-items:center;">
                                                        <button class="be" style="padding:4px 10px;font-size:11px;"
                                                            onclick="openEditInstr(<?= $ins['instructor_id'] ?>,'<?= htmlspecialchars(addslashes($ins['fullname'])) ?>','<?= htmlspecialchars(addslashes($ins['email'])) ?>')">
                                                            <i class="fa-solid fa-pen"></i>
                                                        </button>

                                                        <form method="POST"
                                                            action="admin_dashboard2.php?section=instructors"
                                                            onsubmit="return confirm('Delete instructor and ALL their courses/schedules?');"
                                                            style="display:inline;">
                                                            <input type="hidden" name="action"
                                                                value="delete_instructor">
                                                            <input type="hidden" name="instructor_id"
                                                                value="<?= $ins['instructor_id'] ?>">
                                                            <button type="submit" class="bd"
                                                                style="padding:4px 10px;font-size:11px;">
                                                                <i class="fa-solid fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endwhile; endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <!---- Add Instructor ---->
                <div id="addInstructorModal" class="modal-overlay" onclick="closeAddInstructorModal(event)">
                    <div class="modal-card">
                        <div class="modal-head">
                            <div class="modal-title">Add Instructor</div>
                            <button type="button" class="icon-btn" onclick="closeAddInstructorModal()">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>

                        <div class="modal-body">
                            <form method="POST" action="admin_dashboard2.php?section=instructors">
                                <input type="hidden" name="action" value="add_instructor">

                                <div style="margin-bottom:14px;">
                                    <label class="fl">Full Name</label>
                                    <input type="text" name="fullname" class="di" placeholder="e.g. Maria Santos"
                                        required>
                                </div>

                                <div style="margin-bottom:18px;">
                                    <label class="fl">Email</label>
                                    <input type="email" name="email" class="di" placeholder="instructor@email.com"
                                        required>
                                </div>

                                <div style="display:flex;justify-content:flex-end;gap:10px;">
                                    <button type="submit" class="add">
                                        <i class="fa-solid fa-user-plus me-2"></i>Add Instructor
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- ══════════════════════════════════════════════════
                                    SCHEDULING
                    ══════════════════════════════════════════════════ -->
                <div id="section-scheduling" class="section <?= $activeSection==='scheduling'?'active':'' ?>">
                    <div class="row g-3 mb-3">
                        <div class="col-6 col-xl-3">
                            <div class="stat-card" style="border-color:rgba(245,158,11,.2);">
                                <div class="stat-top">
                                    <div class="stat-label">Pending Requests</div>
                                    <div class="stat-icon" style="color:var(--amber);"><i class="fa-solid fa-hourglass-half"></i></div>
                                </div>
                                <div class="stat-value"><?= $adminPendingCount ?></div>
                                <p class="stat-sub">Student bookings awaiting instructor approval</p>
                            </div>
                        </div>
                        <div class="col-6 col-xl-3">
                            <div class="stat-card" style="border-color:rgba(61,127,255,.2);">
                                <div class="stat-top">
                                    <div class="stat-label">Approved Bookings</div>
                                    <div class="stat-icon" style="color:var(--accent);"><i class="fa-solid fa-circle-check"></i></div>
                                </div>
                                <div class="stat-value"><?= $adminApprovedCount ?></div>
                                <p class="stat-sub">Already approved by instructors</p>
                            </div>
                        </div>
                        <div class="col-6 col-xl-3">
                            <div class="stat-card" style="border-color:rgba(16,185,129,.2);">
                                <div class="stat-top">
                                    <div class="stat-label">Booked Schedules</div>
                                    <div class="stat-icon" style="color:var(--green);"><i class="fa-solid fa-calendar-check"></i></div>
                                </div>
                                <div class="stat-value"><?= $adminBookedCount ?></div>
                                <p class="stat-sub">Schedules with at least 1 active booking</p>
                            </div>
                        </div>
                        <div class="col-6 col-xl-3">
                            <div class="stat-card" style="border-color:rgba(244,63,94,.2);">
                                <div class="stat-top">
                                    <div class="stat-label">Full Slots</div>
                                    <div class="stat-icon" style="color:var(--red);"><i class="fa-solid fa-users-slash"></i></div>
                                </div>
                                <div class="stat-value"><?= $adminFullCount ?></div>
                                <p class="stat-sub">Schedules that cannot accept more bookings</p>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-xl-6">
                            <div class="panel" style="padding:0;border-color:rgba(245,158,11,.3);">
                                <div class="panel-head" style="padding:14px 20px;">
                                    <div>
                                        <div class="panel-title"><i class="fa-solid fa-inbox me-2" style="color:var(--amber);"></i>Pending / Requested Course Bookings</div>
                                        <div class="panel-sub">Includes requests made from offered courses with a selected date</div>
                                    </div>
                                </div>
                                <?php if(!$adminPendingList || $adminPendingList->num_rows===0): ?>
                                <div class="empty-state"><i class="fa-solid fa-inbox"></i><p>No pending requests right now.</p></div>
                                <?php else: while($pl=$adminPendingList->fetch_assoc()): $tc=$cColors[$pl['course_type']]??'tag-blue'; ?>
                                <div style="padding:12px 16px;border-bottom:1px solid var(--border);font-size:12px;">
                                    <div style="display:flex;justify-content:space-between;gap:10px;align-items:flex-start;flex-wrap:wrap;">
                                        <div>
                                            <div style="font-weight:700;"><?= htmlspecialchars($pl['sname']) ?></div>
                                            <div style="color:var(--muted);margin-top:3px;line-height:1.5;">
                                                <span class="tag <?= $tc ?>" style="font-size:10px;"><?= htmlspecialchars($pl['course_type']) ?></span>
                                                with <?= htmlspecialchars($pl['iname']) ?> · <?= adminSchedDateLabel($pl) ?> · <?= adminSchedHoursLabel($pl) ?>
                                            </div>
                                            <?php if(!empty($pl['student_note'])): ?>
                                            <div style="margin-top:6px;color:var(--muted);">Student note: <?= htmlspecialchars($pl['student_note']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div style="text-align:right;min-width:140px;">
                                            <?= !empty($pl['is_active']) ? '<span class="tag tag-green" style="font-size:10px;">Posted Slot</span>' : '<span class="tag tag-amber" style="font-size:10px;">Requested Date Slot</span>' ?>
                                            <div style="font-size:11px;color:var(--muted);margin-top:6px;">Booked <?= (int)$pl['enrolled_cnt'] ?>/<?= (int)$pl['max_students'] ?></div>
                                            <div style="font-size:11px;color:var(--muted);margin-top:2px;">Requested <?= date('M d, Y · g:i A', strtotime($pl['requested_at'])) ?></div>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; endif; ?>
                            </div>
                        </div>

                        <div class="col-12 col-xl-6">
                            <div class="panel" style="padding:0;border-color:rgba(61,127,255,.25);">
                                <div class="panel-head" style="padding:14px 20px;">
                                    <div>
                                        <div class="panel-title"><i class="fa-solid fa-circle-check me-2" style="color:var(--accent);"></i>Recently Approved</div>
                                        <div class="panel-sub">Approved requests now show in student schedule and instructor booked schedules</div>
                                    </div>
                                </div>
                                <?php if(!$adminRecentApproved || $adminRecentApproved->num_rows===0): ?>
                                <div class="empty-state"><i class="fa-solid fa-circle-check"></i><p>No approved requests yet.</p></div>
                                <?php else: while($ap=$adminRecentApproved->fetch_assoc()): $tc=$cColors[$ap['course_type']]??'tag-blue'; ?>
                                <div style="padding:12px 16px;border-bottom:1px solid var(--border);font-size:12px;">
                                    <div style="display:flex;justify-content:space-between;gap:10px;align-items:flex-start;flex-wrap:wrap;">
                                        <div>
                                            <div style="font-weight:700;"><?= htmlspecialchars($ap['sname']) ?></div>
                                            <div style="color:var(--muted);margin-top:3px;line-height:1.5;">
                                                <span class="tag <?= $tc ?>" style="font-size:10px;"><?= htmlspecialchars($ap['course_type']) ?></span>
                                                with <?= htmlspecialchars($ap['iname']) ?> · <?= adminSchedDateLabel($ap) ?> · <?= adminSchedHoursLabel($ap) ?>
                                            </div>
                                        </div>
                                        <div style="text-align:right;min-width:140px;">
                                            <span class="tag tag-blue" style="font-size:10px;">Approved</span>
                                            <div style="font-size:11px;color:var(--muted);margin-top:6px;">Approved <?= !empty($ap['reviewed_at']) ? date('M d, Y', strtotime($ap['reviewed_at'])) : '—' ?></div>
                                            <div style="font-size:11px;color:var(--muted);margin-top:2px;"><?= adminSchedPriceLabel($ap) ?></div>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; endif; ?>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="panel" style="padding:0;">
                                <div class="panel-head" style="padding:14px 20px;">
                                    <div>
                                        <div class="panel-title">All Schedule Slots</div>
                                        <div class="panel-sub"><?= $adminScheduleCount ?> total · supports posted schedules and requested-date slots</div>
                                    </div>
                                    <button type="button" class="add" data-bs-toggle="modal"
                                        data-bs-target="#addScheduleModal">
                                        <i class="fa-solid fa-calendar-plus me-2"></i>Add Schedule
                                    </button>
                                </div>

                                <div class="tbl-wrap">
                                    <table class="tbl">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Instructor</th>
                                                <th>Course</th>
                                                <th>Hours</th>
                                                <th>Bookings</th>
                                                <th>Price</th>
                                                <th>Type</th>
                                                <th>Status</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if(empty($adminScheduleRows)): ?>
                                            <tr>
                                                <td colspan="9" class="ec">No schedules yet.</td>
                                            </tr>
                                            <?php else: foreach($adminScheduleRows as $s):
                                                $dc=$dayColors[$s['day_of_week']]??'var(--accent)';
                                                $tc=$cColors[$s['course_type']]??'tag-blue';
                                                $isFull = (int)$s['max_students'] > 0 && (int)$s['enrolled_cnt'] >= (int)$s['max_students'];
                                                $isBooked = (int)$s['enrolled_cnt'] > 0;
                                                $slotType = (!empty($s['notes']) && stripos($s['notes'],'Requested by student') !== false) ? 'Requested date' : 'Posted schedule';
                                                if ($isFull) $statusHtml = '<span class="tag tag-red" style="font-size:10px;">Full</span>';
                                                elseif ($isBooked) $statusHtml = '<span class="tag tag-blue" style="font-size:10px;">Booked</span>';
                                                elseif (!empty($s['is_active'])) $statusHtml = '<span class="tag tag-green" style="font-size:10px;">Open</span>';
                                                else $statusHtml = '<span class="bkc" style="font-size:10px;">Hidden</span>';
                                            ?>
                                            <tr>
                                                <td>
                                                    <div style="display:flex;flex-direction:column;gap:4px;">
                                                        <span style="background:<?= $dc ?>22;color:<?= $dc ?>;border:1px solid <?= $dc ?>44;border-radius:999px;padding:2px 9px;font-size:11px;font-weight:700;width:max-content;">
                                                            <?= adminSchedDateLabel($s) ?>
                                                        </span>
                                                        <span style="font-size:11px;color:var(--muted);"><?= htmlspecialchars($s['day_of_week'] ?? '—') ?></span>
                                                    </div>
                                                </td>
                                                <td style="font-size:13px;font-weight:600;"><?= htmlspecialchars($s['iname']) ?></td>
                                                <td>
                                                    <span class="tag <?= $tc ?>" style="font-size:10px;"><?= htmlspecialchars($s['course_type']) ?></span>
                                                </td>
                                                <td style="font-size:12px;color:var(--muted);white-space:nowrap;"><?= adminSchedHoursLabel($s) ?></td>
                                                <td style="font-size:12px;">
                                                    <span class="bka" style="font-size:10px;"><?= (int)$s['approved_cnt'] ?> approved</span>
                                                    <span class="bkp" style="font-size:10px;margin-left:4px;"><?= (int)$s['pending_cnt'] ?> pending</span>
                                                    <span class="bkd" style="font-size:10px;margin-left:4px;"><?= (int)$s['done_cnt'] ?> done</span>
                                                    <div style="font-size:11px;color:var(--muted);margin-top:6px;">Total <?= (int)$s['enrolled_cnt'] ?>/<?= (int)$s['max_students'] ?></div>
                                                </td>
                                                <td style="color:var(--green);font-size:12px;"><?= adminSchedPriceLabel($s) ?></td>
                                                <td style="font-size:12px;color:var(--muted);"><?= $slotType ?></td>
                                                <td><?= $statusHtml ?></td>
                                                <td class="text-end">
                                                    <form method="POST" action="admin_dashboard2.php?section=scheduling"
                                                        onsubmit="return confirm('Delete this schedule and all its enrolments?');"
                                                        style="display:inline;">
                                                        <input type="hidden" name="action"
                                                            value="admin_delete_schedule">
                                                        <input type="hidden" name="schedule_id"
                                                            value="<?= $s['schedule_id'] ?>">
                                                        <button type="submit" class="bd"
                                                            style="padding:4px 10px;font-size:11px;">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            <?php endforeach; endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Add Schedule Modal -->
                <div class="modal-overlay" id="addScheduleModal" aria-hidden="true"
                    onclick="closeAddScheduleModal(event)">
                    <div class="modal-card-ins" style="max-width:720px;">
                        <div class="modal-head">
                            <div class="modal-title">
                                <i class="fa-solid fa-calendar-plus me-2" style="color:var(--accent);"></i>
                                Create Schedule
                            </div>
                            <button type="button" class="icon-btn" onclick="closeAddScheduleModal()" aria-label="Close">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>

                        <form method="POST" action="admin_dashboard2.php?section=scheduling">
                            <div class="modal-body">
                                <input type="hidden" name="action" value="admin_add_schedule">

                                <div class="mb-3">
                                    <label class="fl">Instructor</label>
                                    <select name="instructor_id" class="di ds" required>
                                        <option value="">— Select instructor —</option>
                                        <?php $instrDropdown->data_seek(0); while($id=$instrDropdown->fetch_assoc()): ?>
                                        <option value="<?= $id['instructor_id'] ?>">
                                            <?= htmlspecialchars($id['fullname']) ?>
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="fl">Course</label>
                                    <select name="course_id" class="di ds" required>
                                        <option value="">— Select course —</option>
                                        <?php $allCourses->data_seek(0); while($ac=$allCourses->fetch_assoc()): ?>
                                        <option value="<?= $ac['course_id'] ?>">
                                            <?= htmlspecialchars($ac['iname']) ?> —
                                            <?= htmlspecialchars($ac['course_type']) ?>
                                            <?= $ac['price'] ? ' (₱'.number_format($ac['price'],0).')' : '' ?>
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="fl">Day of Week</label>
                                    <select name="day_of_week" class="di ds" required>
                                        <option value="">— Select day —</option>
                                        <?php foreach($days as $d): ?>
                                        <option value="<?= $d ?>"><?= $d ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-12 col-md-6">
                                        <label class="fl">Start Time</label>
                                        <select name="start_time" class="di ds" required>
                                            <option value="">— Select —</option>
                                            <?php for($h=5;$h<=21;$h++): foreach([0,30] as $mi): $v=sprintf('%02d:%02d:00',$h,$mi); ?>
                                            <option value="<?= $v ?>">
                                                <?= date('g:i A',strtotime("$h:".sprintf('%02d',$mi))) ?>
                                            </option>
                                            <?php endforeach; endfor; ?>
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label class="fl">End Time</label>
                                        <select name="end_time" class="di ds" required>
                                            <option value="">— Select —</option>
                                            <?php for($h=5;$h<=22;$h++): foreach([0,30] as $mi): $v=sprintf('%02d:%02d:00',$h,$mi); ?>
                                            <option value="<?= $v ?>">
                                                <?= date('g:i A',strtotime("$h:".sprintf('%02d',$mi))) ?>
                                            </option>
                                            <?php endforeach; endfor; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="fl">Max Students</label>
                                    <select name="max_students" class="di ds">
                                        <?php for($n=1;$n<=20;$n++): ?>
                                        <option value="<?= $n ?>" <?= $n===5?'selected':'' ?>><?= $n ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="fl">Notes (optional)</label>
                                    <textarea name="notes" class="di dt" style="min-height:90px;"
                                        placeholder="Any special instructions…"></textarea>
                                </div>

                                <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:18px;">
                                    <button type="button" class="bs" onclick="closeAddScheduleModal()">Cancel</button>
                                    <button type="submit" class="bp">
                                        <i class="fa-solid fa-calendar-plus me-2"></i>Create Schedule
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════
                                    PAYMENTS
                    ══════════════════════════════════════════════════ -->
                <div id="section-payments" class="section <?= $activeSection==='payments'?'active':'' ?>">
                    <!-- STAT CARDS -->
                    <div class="row g-3 mb-4">

                        <div class="col-6 col-xl-3">
                            <div class="stat-card" style="border-color:rgba(16,185,129,.2);">
                                <div class="stat-top">
                                    <div class="stat-label">Total Revenue</div>
                                    <div class="stat-icon" style="color:var(--green);">
                                        <i class="fa-solid fa-peso-sign"></i>
                                    </div>
                                </div>
                                <div class="stat-value">₱1000</div>
                                <p class="stat-sub">All time earnings</p>
                            </div>
                        </div>

                        <div class="col-6 col-xl-3">
                            <div class="stat-card" style="border-color:rgba(61,127,255,.2);">
                                <div class="stat-top">
                                    <div class="stat-label">This Month</div>
                                    <div class="stat-icon" style="color:var(--accent);">
                                        <i class="fa-solid fa-calendar"></i>
                                    </div>
                                </div>
                                <div class="stat-value">₱100000</div>
                                <p class="stat-sub"><?= date('F Y') ?></p>
                            </div>
                        </div>

                        <div class="col-6 col-xl-3">
                            <div class="stat-card" style="border-color:rgba(244,63,94,.2);">
                                <div class="stat-top">
                                    <div class="stat-label">Unpaid</div>
                                    <div class="stat-icon" style="color:var(--red);">
                                        <i class="fa-solid fa-circle-exclamation"></i>
                                    </div>
                                </div>
                                <div class="stat-value">6</div>
                                <p class="stat-sub">Sessions unpaid</p>
                            </div>
                        </div>

                        <div class="col-6 col-xl-3">
                            <div class="stat-card" style="border-color:rgba(192,132,252,.2);">
                                <div class="stat-top">
                                    <div class="stat-label">Transactions</div>
                                    <div class="stat-icon" style="color:var(--purple);">
                                        <i class="fa-solid fa-receipt"></i>
                                    </div>
                                </div>
                                <div class="stat-value">20</div>
                                <p class="stat-sub">Payments recorded</p>
                            </div>
                        </div>

                    </div>

                    <div class="row g-3">

                        <!-- LEFT: PAYMENTS TABLE -->
                        <div class="col-12 col-lg-8">

                            <div class="panel" style="padding:0;">
                                <div class="panel-head" style="padding:14px 20px;">
                                    <div class="panel-title">
                                        <i class="fa-solid fa-receipt me-2" style="color:var(--green);"></i>
                                        Payment Records
                                    </div>
                                </div>

                                <div class="tbl-wrap">
                                    <table class="tbl">
                                        <thead>
                                            <tr>
                                                <th>Student</th>
                                                <th>Course</th>
                                                <th>Amount</th>
                                                <th>Method</th>
                                                <th>Date</th>
                                                <th>Notes</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                            <tr>
                                                <td style="font-weight:600;">Juan Dela Cruz</td>
                                                <td><span class="tag tag-blue">Driving</span></td>
                                                <td style="color:var(--green);font-weight:700;">₱4,500.00</td>
                                                <td><span class="tag tag-cyan">GCash</span></td>
                                                <td style="font-size:12px;color:var(--muted);">Mar 20, 2026 3:00 PM</td>
                                                <td style="font-size:12px;color:var(--muted);">Full payment</td>
                                            </tr>

                                            <tr>
                                                <td style="font-weight:600;">Maria Santos</td>
                                                <td><span class="tag tag-blue">Motorcycle</span></td>
                                                <td style="color:var(--green);font-weight:700;">₱3,000.00</td>
                                                <td><span class="tag tag-cyan">Cash</span></td>
                                                <td style="font-size:12px;color:var(--muted);">Mar 18, 2026 10:00 AM
                                                </td>
                                                <td style="font-size:12px;color:var(--muted);">—</td>
                                            </tr>

                                            <tr>
                                                <td style="font-weight:600;">Pedro Reyes</td>
                                                <td><span class="tag tag-blue">Defensive Driving</span></td>
                                                <td style="color:var(--green);font-weight:700;">₱5,000.00</td>
                                                <td><span class="tag tag-cyan">Bank Transfer</span></td>
                                                <td style="font-size:12px;color:var(--muted);">Mar 15, 2026 1:30 PM</td>
                                                <td style="font-size:12px;color:var(--muted);">Partial payment</td>
                                            </tr>
                                            <tr>
                                                <td style="font-weight:600;">Juan Dela Cruz</td>
                                                <td><span class="tag tag-blue">Driving</span></td>
                                                <td style="color:var(--green);font-weight:700;">₱4,500.00</td>
                                                <td><span class="tag tag-cyan">GCash</span></td>
                                                <td style="font-size:12px;color:var(--muted);">Mar 20, 2026 3:00 PM</td>
                                                <td style="font-size:12px;color:var(--muted);">Full payment</td>
                                            </tr>

                                            <tr>
                                                <td style="font-weight:600;">Maria Santos</td>
                                                <td><span class="tag tag-blue">Motorcycle</span></td>
                                                <td style="color:var(--green);font-weight:700;">₱3,000.00</td>
                                                <td><span class="tag tag-cyan">Cash</span></td>
                                                <td style="font-size:12px;color:var(--muted);">Mar 18, 2026 10:00 AM
                                                </td>
                                                <td style="font-size:12px;color:var(--muted);">—</td>
                                            </tr>

                                            <tr>
                                                <td style="font-weight:600;">Pedro Reyes</td>
                                                <td><span class="tag tag-blue">Defensive Driving</span></td>
                                                <td style="color:var(--green);font-weight:700;">₱5,000.00</td>
                                                <td><span class="tag tag-cyan">Bank Transfer</span></td>
                                                <td style="font-size:12px;color:var(--muted);">Mar 15, 2026 1:30 PM</td>
                                                <td style="font-size:12px;color:var(--muted);">Partial payment</td>
                                            </tr>
                                            <tr>
                                                <td style="font-weight:600;">Juan Dela Cruz</td>
                                                <td><span class="tag tag-blue">Driving</span></td>
                                                <td style="color:var(--green);font-weight:700;">₱4,500.00</td>
                                                <td><span class="tag tag-cyan">GCash</span></td>
                                                <td style="font-size:12px;color:var(--muted);">Mar 20, 2026 3:00 PM</td>
                                                <td style="font-size:12px;color:var(--muted);">Full payment</td>
                                            </tr>

                                            <tr>
                                                <td style="font-weight:600;">Maria Santos</td>
                                                <td><span class="tag tag-blue">Motorcycle</span></td>
                                                <td style="color:var(--green);font-weight:700;">₱3,000.00</td>
                                                <td><span class="tag tag-cyan">Cash</span></td>
                                                <td style="font-size:12px;color:var(--muted);">Mar 18, 2026 10:00 AM
                                                </td>
                                                <td style="font-size:12px;color:var(--muted);">—</td>
                                            </tr>

                                            <tr>
                                                <td style="font-weight:600;">Pedro Reyes</td>
                                                <td><span class="tag tag-blue">Defensive Driving</span></td>
                                                <td style="color:var(--green);font-weight:700;">₱5,000.00</td>
                                                <td><span class="tag tag-cyan">Bank Transfer</span></td>
                                                <td style="font-size:12px;color:var(--muted);">Mar 15, 2026 1:30 PM</td>
                                                <td style="font-size:12px;color:var(--muted);">Partial payment</td>
                                            </tr>
                                            <tr>
                                                <td style="font-weight:600;">Juan Dela Cruz</td>
                                                <td><span class="tag tag-blue">Driving</span></td>
                                                <td style="color:var(--green);font-weight:700;">₱4,500.00</td>
                                                <td><span class="tag tag-cyan">GCash</span></td>
                                                <td style="font-size:12px;color:var(--muted);">Mar 20, 2026 3:00 PM</td>
                                                <td style="font-size:12px;color:var(--muted);">Full payment</td>
                                            </tr>

                                            <tr>
                                                <td style="font-weight:600;">Maria Santos</td>
                                                <td><span class="tag tag-blue">Motorcycle</span></td>
                                                <td style="color:var(--green);font-weight:700;">₱3,000.00</td>
                                                <td><span class="tag tag-cyan">Cash</span></td>
                                                <td style="font-size:12px;color:var(--muted);">Mar 18, 2026 10:00 AM
                                                </td>
                                                <td style="font-size:12px;color:var(--muted);">—</td>
                                            </tr>

                                            <tr>
                                                <td style="font-weight:600;">Pedro Reyes</td>
                                                <td><span class="tag tag-blue">Defensive Driving</span></td>
                                                <td style="color:var(--green);font-weight:700;">₱5,000.00</td>
                                                <td><span class="tag tag-cyan">Bank Transfer</span></td>
                                                <td style="font-size:12px;color:var(--muted);">Mar 15, 2026 1:30 PM</td>
                                                <td style="font-size:12px;color:var(--muted);">Partial payment</td>
                                            </tr>
                                            <tr>
                                                <td style="font-weight:600;">Juan Dela Cruz</td>
                                                <td><span class="tag tag-blue">Driving</span></td>
                                                <td style="color:var(--green);font-weight:700;">₱4,500.00</td>
                                                <td><span class="tag tag-cyan">GCash</span></td>
                                                <td style="font-size:12px;color:var(--muted);">Mar 20, 2026 3:00 PM</td>
                                                <td style="font-size:12px;color:var(--muted);">Full payment</td>
                                            </tr>

                                            <tr>
                                                <td style="font-weight:600;">Maria Santos</td>
                                                <td><span class="tag tag-blue">Motorcycle</span></td>
                                                <td style="color:var(--green);font-weight:700;">₱3,000.00</td>
                                                <td><span class="tag tag-cyan">Cash</span></td>
                                                <td style="font-size:12px;color:var(--muted);">Mar 18, 2026 10:00 AM
                                                </td>
                                                <td style="font-size:12px;color:var(--muted);">—</td>
                                            </tr>

                                            <tr>
                                                <td style="font-weight:600;">Pedro Reyes</td>
                                                <td><span class="tag tag-blue">Defensive Driving</span></td>
                                                <td style="color:var(--green);font-weight:700;">₱5,000.00</td>
                                                <td><span class="tag tag-cyan">Bank Transfer</span></td>
                                                <td style="font-size:12px;color:var(--muted);">Mar 15, 2026 1:30 PM</td>
                                                <td style="font-size:12px;color:var(--muted);">Partial payment</td>
                                            </tr>
                                            <tr>
                                                <td style="font-weight:600;">Juan Dela Cruz</td>
                                                <td><span class="tag tag-blue">Driving</span></td>
                                                <td style="color:var(--green);font-weight:700;">₱4,500.00</td>
                                                <td><span class="tag tag-cyan">GCash</span></td>
                                                <td style="font-size:12px;color:var(--muted);">Mar 20, 2026 3:00 PM</td>
                                                <td style="font-size:12px;color:var(--muted);">Full payment</td>
                                            </tr>

                                            <tr>
                                                <td style="font-weight:600;">Maria Santos</td>
                                                <td><span class="tag tag-blue">Motorcycle</span></td>
                                                <td style="color:var(--green);font-weight:700;">₱3,000.00</td>
                                                <td><span class="tag tag-cyan">Cash</span></td>
                                                <td style="font-size:12px;color:var(--muted);">Mar 18, 2026 10:00 AM
                                                </td>
                                                <td style="font-size:12px;color:var(--muted);">—</td>
                                            </tr>

                                            <tr>
                                                <td style="font-weight:600;">Pedro Reyes</td>
                                                <td><span class="tag tag-blue">Defensive Driving</span></td>
                                                <td style="color:var(--green);font-weight:700;">₱5,000.00</td>
                                                <td><span class="tag tag-cyan">Bank Transfer</span></td>
                                                <td style="font-size:12px;color:var(--muted);">Mar 15, 2026 1:30 PM</td>
                                                <td style="font-size:12px;color:var(--muted);">Partial payment</td>
                                            </tr>

                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>

                        <!-- RIGHT SIDE -->
                        <div class="col-12 col-lg-4">

                            <!-- REVENUE -->
                            <div class="panel">
                                <div class="panel-head">
                                    <div class="panel-title">
                                        <i class="fa-solid fa-chart-line me-2" style="color:var(--green);"></i>
                                        Revenue Overview
                                    </div>
                                </div>

                                <div style="padding:16px;">
                                    <canvas id="revenueChart" height="140"></canvas>
                                </div>

                                <!-- optional quick stats -->
                                <div style="padding:0 16px 16px;display:grid;grid-template-columns:1fr 1fr;gap:6px;">
                                    <div style="background:var(--surface-3);border-radius:8px;padding:8px 10px;">
                                        <div style="font-size:10px;color:var(--muted);margin-bottom:2px;">Today</div>
                                        <div
                                            style="font-family:var(--font-display);font-size:18px;font-weight:800;color:var(--green);">
                                            ₱2,500
                                        </div>
                                    </div>

                                    <div style="background:var(--surface-3);border-radius:8px;padding:8px 10px;">
                                        <div style="font-size:10px;color:var(--muted);margin-bottom:2px;">This Week
                                        </div>
                                        <div
                                            style="font-family:var(--font-display);font-size:18px;font-weight:800;color:var(--accent);">
                                            ₱10,000
                                        </div>
                                    </div>

                                    <div style="background:var(--surface-3);border-radius:8px;padding:8px 10px;">
                                        <div style="font-size:10px;color:var(--muted);margin-bottom:2px;">This Month
                                        </div>
                                        <div
                                            style="font-family:var(--font-display);font-size:18px;font-weight:800;color:var(--purple);">
                                            ₱40,000
                                        </div>
                                    </div>

                                    <div style="background:var(--surface-3);border-radius:8px;padding:8px 10px;">
                                        <div style="font-size:10px;color:var(--muted);margin-bottom:2px;">Total</div>
                                        <div
                                            style="font-family:var(--font-display);font-size:18px;font-weight:800;color:var(--green);">
                                            ₱120,000
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- QUICK RECORD -->
                            <div class="panel">
                                <div class="panel-head">
                                    <div class="panel-title">
                                        <i class="fa-solid fa-plus me-2" style="color:var(--green);"></i>
                                        Quick Record Payment
                                    </div>
                                </div>

                                <div class="panel-body">
                                    <form method="POST" action="admin_dashboard2.php?section=payments">
                                        <input type="hidden" name="action" value="record_payment">

                                        <div style="margin-bottom:12px;">
                                            <label class="fl">Amount</label>
                                            <input type="number" name="amount" class="di" required>
                                        </div>

                                        <div style="margin-bottom:12px;">
                                            <label class="fl">Method</label>
                                            <select name="method" class="di ds">
                                                <option>Cash</option>
                                                <option>GCash</option>
                                                <option>Bank</option>
                                            </select>
                                        </div>

                                        <button type="submit" class="bp w-100">
                                            <i class="fa-solid fa-check me-2"></i>Save Payment
                                        </button>
                                    </form>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>

                <!-- ══════════════════════════════════════════════════
                                    REPORTS
                    ══════════════════════════════════════════════════ -->
                <div id="section-reports" class="section <?= $activeSection==='reports'?'active':'' ?>">
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-3">
                            <div class="stat-card">
                                <div class="stat-top">
                                    <div class="stat-label">Total Enrolments</div>
                                    <div class="stat-icon" style="color:var(--accent);"><i
                                            class="fa-solid fa-calendar-check"></i></div>
                                </div>
                                <div class="stat-value"><?= $totalEnrols ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-card">
                                <div class="stat-top">
                                    <div class="stat-label">Sessions Done</div>
                                    <div class="stat-icon" style="color:var(--green);"><i
                                            class="fa-solid fa-flag-checkered"></i></div>
                                </div>
                                <div class="stat-value"><?= $doneSessions ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-card">
                                <div class="stat-top">
                                    <div class="stat-label">Completion Rate</div>
                                    <div class="stat-icon" style="color:var(--amber);"><i
                                            class="fa-solid fa-chart-line"></i></div>
                                </div>
                                <div class="stat-value"><?= $totalEnrols>0?round($doneSessions/$totalEnrols*100):0 ?>%
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-card">
                                <div class="stat-top">
                                    <div class="stat-label">Active Schedules</div>
                                    <div class="stat-icon" style="color:var(--purple);"><i
                                            class="fa-solid fa-calendar-days"></i></div>
                                </div>
                                <div class="stat-value"><?= $activeScheds ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <!-- Instructor performance -->
                        <div class="col-12 col-lg-6">
                            <div class="panel" style="padding:0;">
                                <div class="panel-head" style="padding:14px 20px;">
                                    <div>
                                        <div class="panel-title"><i class="fa-solid fa-trophy me-2"
                                                style="color:var(--amber);"></i>Instructor Performance</div>
                                    </div>
                                </div>
                                <div class="tbl-wrap">
                                    <table class="tbl">
                                        <thead>
                                            <tr>
                                                <th>Instructor</th>
                                                <th>Avg Rating</th>
                                                <th>Reviews</th>
                                                <th>Sessions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $topInstructors->data_seek(0); if($topInstructors->num_rows===0): ?>
                                            <tr>
                                                <td colspan="4" class="ec">No data yet.</td>
                                            </tr>
                                            <?php else: $ci=0; while($ti=$topInstructors->fetch_assoc()): $ii=initials($ti['fullname']); $ac=$accentColors[$ci++%count($accentColors)]; ?>
                                            <tr>
                                                <td>
                                                    <div style="display:flex;align-items:center;gap:8px;">
                                                        <div
                                                            style="width:28px;height:28px;border-radius:7px;background:<?= $ac ?>;display:grid;place-items:center;font-family:var(--font-display);font-size:10px;font-weight:800;">
                                                            <?= $ii ?></div><span
                                                            style="font-weight:600;font-size:13px;"><?= htmlspecialchars($ti['fullname']) ?></span>
                                                    </div>
                                                </td>
                                                <td><?php if($ti['avg_r']): ?><span style="color:var(--amber);">★
                                                        <?= $ti['avg_r'] ?></span><?php else: ?><span
                                                        style="color:var(--muted);">—</span><?php endif; ?></td>
                                                <td style="color:var(--muted);"><?= $ti['reviews'] ?></td>
                                                <td style="font-weight:700;"><?= $ti['sessions'] ?></td>
                                            </tr>
                                            <?php endwhile; endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Course stats -->
                        <div class="col-12 col-lg-6">
                            <div class="panel" style="padding:0;">
                                <div class="panel-head" style="padding:14px 20px;">
                                    <div>
                                        <div class="panel-title"><i class="fa-solid fa-book-open me-2"
                                                style="color:var(--accent);"></i>Course Statistics</div>
                                    </div>
                                </div>
                                <div class="tbl-wrap">
                                    <table class="tbl">
                                        <thead>
                                            <tr>
                                                <th>Course Type</th>
                                                <th>Total</th>
                                                <th>Done</th>
                                                <th>Active</th>
                                                <th>Pending</th>
                                                <th>Rate</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $courseStats->data_seek(0); if($courseStats->num_rows===0): ?><tr>
                                                <td colspan="6" class="ec">No data yet.</td>
                                            </tr>
                                            <?php else: while($cs=$courseStats->fetch_assoc()): $tc=$cColors[$cs['course_type']]??'tag-blue'; $rate=$cs['total']>0?round($cs['done']/$cs['total']*100):0; ?>
                                            <tr>
                                                <td><span class="tag <?= $tc ?>"
                                                        style="font-size:10px;"><?= htmlspecialchars($cs['course_type']) ?></span>
                                                </td>
                                                <td style="font-weight:700;"><?= $cs['total'] ?></td>
                                                <td><span class="bkd"><?= $cs['done'] ?></span></td>
                                                <td><span class="bka"><?= $cs['approved'] ?></span></td>
                                                <td><span class="bkp"><?= $cs['pending'] ?></span></td>
                                                <td>
                                                    <div style="display:flex;align-items:center;gap:8px;">
                                                        <div class="prog-track" style="width:60px;">
                                                            <div class="prog-fill"
                                                                style="width:<?= $rate ?>%;background:var(--green);">
                                                            </div>
                                                        </div><span
                                                            style="font-size:11px;color:var(--muted);"><?= $rate ?>%</span>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endwhile; endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Student progress overview -->
                        <div class="col-12">
                            <div class="panel" style="padding:0;">
                                <div class="panel-head" style="padding:14px 20px;">
                                    <div>
                                        <div class="panel-title"><i class="fa-solid fa-user-graduate me-2"
                                                style="color:var(--accent);"></i>Student Progress Overview</div>
                                        <div class="panel-sub">All students · sessions done vs enrolled</div>
                                    </div>
                                </div>
                                <div class="tbl-wrap">
                                    <table class="tbl">
                                        <thead>
                                            <tr>
                                                <th>Student</th>
                                                <th>Email</th>
                                                <th>Verified</th>
                                                <th>Done</th>
                                                <th>Upcoming</th>
                                                <th>Pending</th>
                                                <th>Progress</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sprogress=q($conn,"SELECT st.student_id,st.fullname,st.email,st.is_verified,SUM(er.status='done') AS done,SUM(er.status='approved') AS approved,SUM(er.status='pending') AS pending,COUNT(er.request_id) AS total FROM students st LEFT JOIN enrolment_requests er ON er.student_id=st.student_id GROUP BY st.student_id ORDER BY done DESC,st.fullname ASC");
                                            if($sprogress->num_rows===0): ?><tr>
                                                <td colspan="7" class="ec">No students yet.</td>
                                            </tr>
                                            <?php else: $ci=0; while($sp=$sprogress->fetch_assoc()): $si=initials($sp['fullname']); $ac=$accentColors[$ci++%count($accentColors)]; $rate=$sp['total']>0?round($sp['done']/$sp['total']*100):0; ?>
                                            <tr>
                                                <td>
                                                    <div style="display:flex;align-items:center;gap:8px;">
                                                        <div
                                                            style="width:28px;height:28px;border-radius:7px;background:<?= $ac ?>;display:grid;place-items:center;font-family:var(--font-display);font-size:10px;font-weight:800;">
                                                            <?= $si ?></div><span
                                                            style="font-weight:600;"><?= htmlspecialchars($sp['fullname']) ?></span>
                                                    </div>
                                                </td>
                                                <td style="font-size:12px;color:var(--muted);">
                                                    <?= htmlspecialchars($sp['email']) ?></td>
                                                <td><?= $sp['is_verified']?'<span class="tag tag-green" style="font-size:10px;">Yes</span>':'<span class="tag tag-amber" style="font-size:10px;">No</span>' ?>
                                                </td>
                                                <td><span class="bkd"><?= (int)$sp['done'] ?></span></td>
                                                <td><span class="bka"><?= (int)$sp['approved'] ?></span></td>
                                                <td><span class="bkp"><?= (int)$sp['pending'] ?></span></td>
                                                <td>
                                                    <div
                                                        style="display:flex;align-items:center;gap:8px;min-width:100px;">
                                                        <div class="prog-track" style="flex:1;">
                                                            <div class="prog-fill"
                                                                style="width:<?= $rate ?>%;background:var(--green);">
                                                            </div>
                                                        </div><span
                                                            style="font-size:11px;color:var(--muted);white-space:nowrap;"><?= $rate ?>%</span>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endwhile; endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════
                                    SETTINGS
                    ══════════════════════════════════════════════════ -->
                <div id="section-settings" class="section <?= $activeSection==='settings'?'active':'' ?>">
                    <div class="row g-3">
                        <div class="col-12 col-lg-5">
                            <div class="panel">
                                <div class="panel-head">
                                    <div>
                                        <div class="panel-title"><i class="fa-solid fa-user-shield me-2"
                                                style="color:var(--accent);"></i>Admin Accounts</div>
                                        <div class="panel-sub"><?= $adminsList?$adminsList->num_rows:0 ?> admins</div>
                                    </div>
                                </div>
                                <div class="tbl-wrap">
                                    <table class="tbl">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Created</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if(!$adminsList||$adminsList->num_rows===0): ?><tr>
                                                <td colspan="3" class="ec">No admin accounts found.</td>
                                            </tr>
                                            <?php else: while($adm=$adminsList->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <div style="display:flex;align-items:center;gap:8px;">
                                                        <div
                                                            style="width:28px;height:28px;border-radius:7px;background:linear-gradient(135deg,var(--red),#b91c1c);display:grid;place-items:center;font-family:var(--font-display);font-size:10px;font-weight:800;">
                                                            <?= initials($adm['fullname']) ?></div><span
                                                            style="font-weight:600;"><?= htmlspecialchars($adm['fullname']) ?><?php if($adm['admin_id']==$admin_id): ?>
                                                            <span class="tag tag-blue"
                                                                style="font-size:9px;margin-left:4px;">You</span><?php endif; ?></span>
                                                    </div>
                                                </td>
                                                <td style="font-size:12px;color:var(--muted);">
                                                    <?= htmlspecialchars($adm['email']) ?></td>
                                                <td style="font-size:12px;color:var(--muted);">
                                                    <?= date('M d, Y',strtotime($adm['created_at'])) ?></td>
                                            </tr>
                                            <?php endwhile; endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Change password -->
                            <div class="panel">
                                <div class="panel-head">
                                    <div>
                                        <div class="panel-title"><i class="fa-solid fa-key me-2"
                                                style="color:var(--amber);"></i>Change My Password</div>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    <form method="POST" action="admin_dashboard2.php?section=settings">
                                        <input type="hidden" name="action" value="change_password">
                                        <div style="margin-bottom:14px;"><label class="fl">Current
                                                Password</label><input type="password" name="current_password"
                                                class="di" required></div>
                                        <div style="margin-bottom:14px;"><label class="fl">New Password</label><input
                                                type="password" name="new_password" class="di" required></div>
                                        <div style="margin-bottom:16px;"><label class="fl">Confirm New
                                                Password</label><input type="password" name="confirm_password"
                                                class="di" required></div>
                                        <button type="submit" class="bp"><i class="fa-solid fa-lock me-2"></i>Update
                                            Password</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-lg-7">
                            <!-- System info -->
                            <div class="panel">
                                <div class="panel-head">
                                    <div>
                                        <div class="panel-title"><i class="fa-solid fa-circle-info me-2"
                                                style="color:var(--cyan);"></i>System Overview</div>
                                    </div>
                                </div>
                                <div class="ir">
                                    <div class="ii" style="color:var(--accent);"><i
                                            class="fa-solid fa-user-graduate"></i>
                                    </div>
                                    <div>
                                        <div class="il">Total Students</div>
                                        <div class="iv"><?= $totalStudents ?> registered · <?= $verifiedStudents ?>
                                            verified</div>
                                    </div>
                                </div>
                                <div class="ir">
                                    <div class="ii" style="color:var(--purple);"><i
                                            class="fa-solid fa-chalkboard-user"></i>
                                    </div>
                                    <div>
                                        <div class="il">Total Instructors</div>
                                        <div class="iv"><?= $totalInstructors ?> registered</div>
                                    </div>
                                </div>
                                <div class="ir">
                                    <div class="ii" style="color:var(--amber);"><i
                                            class="fa-solid fa-calendar-days"></i>
                                    </div>
                                    <div>
                                        <div class="il">Schedule Slots</div>
                                        <div class="iv"><?= $activeScheds ?> active</div>
                                    </div>
                                </div>
                                <div class="ir">
                                    <div class="ii" style="color:var(--green);"><i
                                            class="fa-solid fa-flag-checkered"></i>
                                    </div>
                                    <div>
                                        <div class="il">Sessions Completed</div>
                                        <div class="iv"><?= $doneSessions ?> done sessions</div>
                                    </div>
                                </div>
                                <div class="ir">
                                    <div class="ii" style="color:var(--red);"><i class="fa-solid fa-hourglass-half"></i>
                                    </div>
                                    <div>
                                        <div class="il">Pending Enrolments</div>
                                        <div class="iv"><?= $pendingEnrols ?> awaiting action</div>
                                    </div>
                                </div>
                                <div class="ir">
                                    <div class="ii" style="color:var(--cyan);"><i class="fa-solid fa-server"></i></div>
                                    <div>
                                        <div class="il">PHP Version</div>
                                        <div class="iv"><?= PHP_VERSION ?></div>
                                    </div>
                                </div>
                                <div class="ir">
                                    <div class="ii" style="color:var(--muted-2);"><i class="fa-solid fa-database"></i>
                                    </div>
                                    <div>
                                        <div class="il">Server Time</div>
                                        <div class="iv"><?= date('F d, Y · g:i A') ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Login history for current admin -->
                            <div class="panel" style="padding:0;">
                                <div class="panel-head" style="padding:14px 20px;">
                                    <div>
                                        <div class="panel-title"><i class="fa-solid fa-shield-halved me-2"
                                                style="color:var(--accent);"></i>Recent Student Login Activity</div>
                                    </div>
                                </div>
                                <div class="tbl-wrap">
                                    <table class="tbl">
                                        <thead>
                                            <tr>
                                                <th>Student</th>
                                                <th>Date & Time</th>
                                                <th>Status</th>
                                                <th>Device</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                $allLogins=q($conn,"SELECT s.fullname,slh.login_datetime,slh.status,slh.device_info FROM student_login_history slh JOIN students s ON s.student_id=slh.student_id ORDER BY slh.login_datetime DESC LIMIT 15");
                                                if($allLogins->num_rows===0): ?><tr>
                                                <td colspan="4" class="ec">No login history.</td>
                                            </tr>
                                            <?php else: while($lg=$allLogins->fetch_assoc()): $isS=$lg['status']==='success'; ?>
                                            <tr>
                                                <td style="font-weight:600;"><?= htmlspecialchars($lg['fullname']) ?>
                                                </td>
                                                <td style="font-size:12px;color:var(--muted);">
                                                    <?= date('M d, Y g:i A',strtotime($lg['login_datetime'])) ?></td>
                                                <td><span
                                                        class="tag <?= $isS?'tag-green':'tag-red' ?>"><?= ucfirst($lg['status']) ?></span>
                                                </td>
                                                <td style="font-size:12px;color:var(--muted);">
                                                    <?= htmlspecialchars(mb_strimwidth($lg['device_info']??'—',0,35,'…')) ?>
                                                </td>
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
            </div><!-- /content -->

            <div class="toast-container">
                <?php if($successMsg): ?>
                <div class="toast toast-success">
                    <i class="fa-solid fa-circle-check"></i>
                    <span><?= htmlspecialchars($successMsg) ?></span>
                </div>
                <?php endif; ?>

                <?php if($errorMsg): ?>
                <div class="toast toast-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><?= htmlspecialchars($errorMsg) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div><!-- /sidebar-push -->
    </div><!-- /app-wrapper -->

    <!-- EDIT STUDENT MODAL -->
    <div class="modal fade modal-dark" id="editStudentModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-pen me-2" style="color:var(--accent);"></i>Edit
                        Student</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="admin_dashboard2.php?section=students">
                    <input type="hidden" name="action" value="edit_student"><input type="hidden" name="student_id"
                        id="esStudId">
                    <div class="modal-body">
                        <div style="margin-bottom:14px;"><label class="fl">Full Name</label><input type="text"
                                name="fullname" id="esStudName" class="di" required></div>
                        <div style="margin-bottom:14px;"><label class="fl">Email</label><input type="email" name="email"
                                id="esStudEmail" class="di" required></div>
                        <div style="margin-bottom:4px;"><label class="fl">Verification Status</label><select
                                name="is_verified" id="esStudVer" class="di ds">
                                <option value="0">Not Verified</option>
                                <option value="1">Verified</option>
                            </select></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="bd"
                            data-bs-dismiss="modal">Cancel</button><button type="submit" class="bp">Save
                            Changes</button></div>
                </form>
            </div>
        </div>
    </div>

    <!-- EDIT INSTRUCTOR MODAL -->
    <div class="modal fade modal-dark" id="editInstrModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-pen me-2" style="color:var(--accent);"></i>Edit
                        Instructor</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="admin_dashboard2.php?section=instructors">
                    <input type="hidden" name="action" value="edit_instructor"><input type="hidden" name="instructor_id"
                        id="esInstrId">
                    <div class="modal-body">
                        <div style="margin-bottom:14px;"><label class="fl">Full Name</label><input type="text"
                                name="fullname" id="esInstrName" class="di" required></div>
                        <div style="margin-bottom:4px;"><label class="fl">Email</label><input type="email" name="email"
                                id="esInstrEmail" class="di" required></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="bd"
                            data-bs-dismiss="modal">Cancel</button><button type="submit" class="bp">Save
                            Changes</button></div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/portal.js"></script>
    <script>
    // ── SECTION ROUTING ──────────────────────────────────────────────
    const T = {
        dashboard: 'Overview',
        students: 'Student Management',
        instructors: 'Instructor Management',
        scheduling: 'Scheduling System',
        payments: 'Payment Management',
        reports: 'Reports & Analytics',
        settings: 'System Settings'
    };

    function showSection(n) {
        document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
        document.querySelectorAll('.nav-item').forEach(x => x.classList.remove('active'));
        const el = document.getElementById('section-' + n);
        if (el) el.classList.add('active');
        document.getElementById('page-title-text').textContent = T[n] || 'Admin';
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

    // ── EDIT MODALS ──────────────────────────────────────────────────
    function openEditStudent(id, name, email, ver) {
        document.getElementById('esStudId').value = id;
        document.getElementById('esStudName').value = name;
        document.getElementById('esStudEmail').value = email;
        document.getElementById('esStudVer').value = ver;
        new bootstrap.Modal(document.getElementById('editStudentModal')).show();
    }

    function openEditInstr(id, name, email) {
        document.getElementById('esInstrId').value = id;
        document.getElementById('esInstrName').value = name;
        document.getElementById('esInstrEmail').value = email;
        new bootstrap.Modal(document.getElementById('editInstrModal')).show();
    }

    // ── CHARTS ───────────────────────────────────────────────────────
    const chartDefaults = {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            x: {
                ticks: {
                    color: '#4a5a78',
                    font: {
                        size: 11
                    }
                },
                grid: {
                    display: false
                }
            },
            y: {
                beginAtZero: true,
                ticks: {
                    color: '#4a5a78',
                    font: {
                        size: 11
                    },
                    stepSize: 1
                },
                grid: {
                    color: '#1e2d47'
                }
            }
        }
    };

    new Chart(document.getElementById('loginChart'), {
        type: 'line',
        data: {
            labels: <?= $jLabels ?>,
            datasets: [{
                label: 'Logins',
                data: <?= $jCounts ?>,
                borderColor: '#3d7fff',
                backgroundColor: 'rgba(61,127,255,.08)',
                borderWidth: 2.5,
                pointBackgroundColor: '#3d7fff',
                pointRadius: 4,
                tension: .35,
                fill: true
            }]
        },
        options: chartDefaults
    });

    new Chart(document.getElementById('enrolChart'), {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Approved', 'Done', 'Rejected', 'Cancelled'],
            datasets: [{
                data: [<?= implode(',',$enrByStatus) ?>],
                backgroundColor: ['#f59e0b', '#3d7fff', '#10b981', '#f43f5e', '#4a5a78'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            cutout: '68%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            size: 11
                        },
                        color: '#7a8ba8',
                        padding: 10
                    }
                }
            }
        }
    });
    </script>
    <script>
    function openAddStudentModal() {
        document.getElementById('addStudentModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeAddStudentModal() {
        document.getElementById('addStudentModal').style.display = 'none';
        document.body.style.overflow = '';
    }

    // Close when clicking backdrop
    document.getElementById('addStudentModal').addEventListener('click', function(e) {
        if (e.target === this) closeAddStudentModal();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeAddStudentModal();
    });
    </script>

    <script src="../assets/js/right-click.js"></script>
    <script src="../assets/js/toast.js"></script>
    <script src="../assets/js/search.js"></script>

    <script>
    function openAddInstructorModal() {
        document.getElementById("addInstructorModal").classList.add("active");
        document.body.style.overflow = "hidden";
    }

    function closeAddInstructorModal() {
        const modal = document.getElementById("addInstructorModal");
        modal.classList.remove("active");
        document.body.style.overflow = "";
    }

    function openAddScheduleModal() {
        document.getElementById("addScheduleModal").classList.add("active");
        document.body.style.overflow = "hidden";
    }

    function closeAddScheduleModal() {
        const modal = document.getElementById("addScheduleModal");
        modal.classList.remove("active");
        document.body.style.overflow = "";
    }

    document.addEventListener("keydown", function(e) {
        if (e.key === "Escape") {

            const instructorModal = document.getElementById("addInstructorModal");
            const scheduleModal = document.getElementById("addScheduleModal");

            if (instructorModal?.classList.contains("active")) {
                closeAddInstructorModal();
            }

            if (scheduleModal?.classList.contains("active")) {
                closeAddScheduleModal();
            }
        }
    });
    </script>



</body>

</html>