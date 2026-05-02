<?php
require '../includes/db.php';
// require 'admin_auth.php';

// ── WORKLOAD DATA ───────────────────────────────────────────────
// Per instructor: total schedules, unique students, completed, cancelled, upcoming
$workloadResult = $conn->query("
    SELECT
        i.instructor_id,
        i.fullname                                          AS instructor_name,
        i.email,
        i.created_at,
        COUNT(s.schedule_id)                               AS total_schedules,
        COUNT(DISTINCT s.student_id)                       AS total_students,
        SUM(s.status = 'scheduled')                        AS upcoming,
        SUM(s.status = 'completed')                        AS completed,
        SUM(s.status = 'cancelled')                        AS cancelled,
        SUM(s.status = 'no_show')                          AS no_show,
        SUM(s.schedule_date = CURDATE())                   AS today,
        MAX(s.schedule_date)                               AS last_schedule
    FROM instructors i
    LEFT JOIN schedules s ON s.instructor_id = i.instructor_id
    GROUP BY i.instructor_id
    ORDER BY total_schedules DESC, i.fullname ASC
");

// ── SUMMARY STATS ───────────────────────────────────────────────
$totalInstructors = $conn->query("SELECT COUNT(*) AS cnt FROM instructors")->fetch_assoc()['cnt'];
$totalSchedules   = $conn->query("SELECT COUNT(*) AS cnt FROM schedules")->fetch_assoc()['cnt'];
$busiest          = $conn->query("
    SELECT i.fullname, COUNT(s.schedule_id) AS cnt
    FROM instructors i
    LEFT JOIN schedules s ON s.instructor_id = i.instructor_id
    GROUP BY i.instructor_id
    ORDER BY cnt DESC LIMIT 1
")->fetch_assoc();
$avgLoad = $totalInstructors > 0 ? round($totalSchedules / $totalInstructors, 1) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workload Distribution – Digitract</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/startbootstrap-sb-admin-2/4.1.4/css/sb-admin-2.min.css">
    <style>
        :root { --navy:#0b1437; --blue:#1a3a8f; --accent:#2f80ed; --gold:#f5a623; }
        body { font-family:'DM Sans',sans-serif; background:#f4f6fc; }

        /* Sidebar */
        .sidebar { background:linear-gradient(180deg,#0b1437 0%,#1a3a8f 100%) !important; }
        .sidebar .nav-item .nav-link { color:rgba(255,255,255,.65) !important; font-family:'DM Sans',sans-serif; }
        .sidebar .nav-item .nav-link:hover,
        .sidebar .nav-item.active .nav-link { color:#fff !important; background:rgba(47,128,237,.25) !important; border-radius:8px; }
        .sidebar-brand { background:rgba(0,0,0,.15) !important; }
        .sidebar-brand-text { font-family:'Syne',sans-serif !important; font-weight:800 !important; font-size:20px !important; }
        .sidebar-brand-text span { color:var(--gold); }
        .sidebar-divider { border-top:1px solid rgba(255,255,255,.1) !important; }
        .sidebar-heading { color:rgba(255,255,255,.4) !important; font-size:10px !important; letter-spacing:1.5px; }

        /* Topbar */
        .topbar { box-shadow:0 2px 12px rgba(0,0,0,.07); background:#fff !important; }
        .topbar-divider { border-right:1px solid #e5e7eb !important; }
        .topbar-logout {
            display:flex; align-items:center; gap:6px;
            color:#ef4444 !important; font-size:13px; font-weight:500;
            padding:6px 14px; border-radius:8px; transition:background .2s; text-decoration:none;
        }
        .topbar-logout:hover { background:#fee2e2; color:#dc2626 !important; text-decoration:none; }

        /* Cards */
        .card { border:none !important; border-radius:14px !important; box-shadow:0 2px 16px rgba(0,0,0,.07) !important; }
        .card-header { border-radius:14px 14px 0 0 !important; border-bottom:1px solid rgba(0,0,0,.06) !important; }

        /* Stat cards */
        .stat-card { border-radius:14px; padding:22px; color:#fff; position:relative; overflow:hidden; }
        .stat-card .icon { position:absolute; right:18px; top:50%; transform:translateY(-50%); font-size:48px; opacity:.15; }
        .stat-card h2 { font-family:'Syne',sans-serif; font-size:30px; font-weight:800; margin-bottom:4px; }
        .stat-card p  { font-size:13px; opacity:.8; margin:0; }
        .stat-card .sub { font-size:11px; opacity:.7; margin-top:6px; }
        .bg-grad-blue   { background:linear-gradient(135deg,#2f80ed,#1a6fd8); }
        .bg-grad-green  { background:linear-gradient(135deg,#10b981,#059669); }
        .bg-grad-orange { background:linear-gradient(135deg,#f5a623,#e08e10); }
        .bg-grad-purple { background:linear-gradient(135deg,#8b5cf6,#7c3aed); }

        /* Table */
        .table thead th {
            font-family:'Syne',sans-serif; font-size:11px; letter-spacing:.8px;
            color:#718096; border-bottom:2px solid #f0f0f0; background:#fafafa; white-space:nowrap;
        }
        .table td { vertical-align:middle; font-size:14px; }
        .table tbody tr:hover { background:#f8faff; }

        /* Avatar */
        .avatar {
            width:36px; height:36px; border-radius:50%;
            display:flex; align-items:center; justify-content:center;
            font-weight:700; font-size:13px; color:#fff; flex-shrink:0;
        }

        /* Workload bar */
        .workload-bar-wrap { display:flex; align-items:center; gap:10px; min-width:140px; }
        .workload-bar-track { flex:1; height:8px; background:#f0f4ff; border-radius:100px; overflow:hidden; }
        .workload-bar-fill  { height:100%; border-radius:100px; transition:width .4s ease; }

        /* Count pill */
        .count-pill {
            display:inline-flex; align-items:center; justify-content:center;
            min-width:28px; height:22px; padding:0 8px;
            border-radius:100px; font-size:12px; font-weight:700;
        }
        .pill-blue   { background:#dbeafe; color:#1e40af; }
        .pill-green  { background:#d1fae5; color:#065f46; }
        .pill-orange { background:#fef3c7; color:#92400e; }
        .pill-red    { background:#fee2e2; color:#991b1b; }
        .pill-gray   { background:#f3f4f6; color:#6b7280; }
        .pill-purple { background:#ede9fe; color:#5b21b6; }

        /* Load level badge */
        .load-badge { padding:4px 10px; border-radius:100px; font-size:11px; font-weight:600; }
        .load-high   { background:#fee2e2; color:#991b1b; }
        .load-medium { background:#fef3c7; color:#92400e; }
        .load-low    { background:#d1fae5; color:#065f46; }
        .load-none   { background:#f3f4f6; color:#6b7280; }

        /* Page heading */
        .page-heading h1 { font-family:'Syne',sans-serif; font-size:26px; font-weight:700; color:#0b1437; }
        .page-heading p  { color:#718096; font-size:14px; }

        /* Empty state */
        .empty-state { padding:60px 20px; text-align:center; color:#9ca3af; }
        .empty-state i { font-size:48px; margin-bottom:16px; display:block; opacity:.4; }
        .empty-state p { font-size:14px; margin:0; }
    </style>
</head>
<body id="page-top">
<div id="wrapper">

    <!-- ═══ SIDEBAR ═══ -->
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="dashboard.php">
            <div class="sidebar-brand-icon">🚗</div>
            <div class="sidebar-brand-text mx-3">Digi<span>tract</span></div>
        </a>
        <hr class="sidebar-divider my-0">

        <li class="nav-item">
            <a class="nav-link" href="dashboard.php">
                <i class="fas fa-fw fa-tachometer-alt"></i><span>Dashboard</span>
            </a>
        </li>
        <hr class="sidebar-divider">
        <div class="sidebar-heading">Management</div>

        <li class="nav-item active">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseSchedule">
                <i class="fas fa-fw fa-calendar-days"></i><span>Schedules</span>
            </a>
            <div id="collapseSchedule" class="collapse show" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="schedules.php">View All Schedules</a>
                    <a class="collapse-item active" href="workload.php">Workload Distribution</a>
                </div>
            </div>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="instructors.php">
                <i class="fas fa-fw fa-chalkboard-user"></i><span>Instructors</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="students.php">
                <i class="fas fa-fw fa-user-graduate"></i><span>Students</span>
            </a>
        </li>

        <hr class="sidebar-divider">
        <div class="sidebar-heading">Monitoring</div>

        <li class="nav-item">
            <a class="nav-link" href="compliance.php">
                <i class="fas fa-fw fa-certificate"></i><span>Compliance</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="performance.php">
                <i class="fas fa-fw fa-chart-line"></i><span>Performance</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="feedback.php">
                <i class="fas fa-fw fa-comments"></i><span>Feedback</span>
            </a>
        </li>

        <hr class="sidebar-divider">
        <div class="sidebar-heading">Reports</div>

        <li class="nav-item">
            <a class="nav-link" href="reports.php">
                <i class="fas fa-fw fa-file-chart-column"></i><span>Generate Reports</span>
            </a>
        </li>

        <hr class="sidebar-divider d-none d-md-block">
        <div class="text-center d-none d-md-inline">
            <button class="rounded-circle border-0" id="sidebarToggle"></button>
        </div>
    </ul>
    <!-- ═══ END SIDEBAR ═══ -->

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">

            <!-- ═══ TOPBAR ═══ -->
            <nav class="navbar navbar-expand navbar-light topbar mb-4 static-top shadow">
                <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                    <i class="fa fa-bars"></i>
                </button>
                <ul class="navbar-nav ml-auto align-items-center">
                    <li class="nav-item d-none d-sm-flex align-items-center mr-2">
                        <span style="font-size:13px; color:#718096;">
                            Logged in as <strong style="color:#0b1437;"><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></strong>
                        </span>
                    </li>
                    <div class="topbar-divider d-none d-sm-block"></div>
                    <li class="nav-item">
                        <a href="logout.php" class="topbar-logout">
                            <i class="fas fa-sign-out-alt"></i>
                            <span class="d-none d-sm-inline">Log Out</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <!-- ═══ END TOPBAR ═══ -->

            <div class="container-fluid">

                <!-- Page heading -->
                <div class="page-heading mb-4">
                    <h1><i class="fas fa-chart-bar mr-2" style="color:#2f80ed;"></i>Workload Distribution</h1>
                    <p>Overview of how schedules and students are distributed across instructors.</p>
                </div>

                <!-- ── STAT CARDS ── -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="stat-card bg-grad-blue">
                            <p>Total Instructors</p>
                            <h2><?= $totalInstructors ?></h2>
                            <div class="sub">Active in system</div>
                            <div class="icon"><i class="fas fa-chalkboard-user"></i></div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="stat-card bg-grad-green">
                            <p>Total Schedules</p>
                            <h2><?= $totalSchedules ?></h2>
                            <div class="sub">Across all instructors</div>
                            <div class="icon"><i class="fas fa-calendar-alt"></i></div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="stat-card bg-grad-orange">
                            <p>Avg. Load</p>
                            <h2><?= $avgLoad ?></h2>
                            <div class="sub">Schedules per instructor</div>
                            <div class="icon"><i class="fas fa-scale-balanced"></i></div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="stat-card bg-grad-purple">
                            <p>Busiest Instructor</p>
                            <h2 style="font-size:18px; margin-top:4px;">
                                <?= $busiest && $busiest['cnt'] > 0 ? htmlspecialchars(explode(' ', $busiest['fullname'])[0]) : '—' ?>
                            </h2>
                            <div class="sub">
                                <?= $busiest && $busiest['cnt'] > 0 ? $busiest['cnt'] . ' schedule(s)' : 'No schedules yet' ?>
                            </div>
                            <div class="icon"><i class="fas fa-star"></i></div>
                        </div>
                    </div>
                </div>

                <!-- ── WORKLOAD TABLE ── -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold" style="font-family:'Syne',sans-serif; color:#0b1437;">
                            <i class="fas fa-table text-primary mr-2"></i>
                            Instructor Workload Breakdown
                            <span class="text-muted font-weight-normal" style="font-size:12px; font-family:'DM Sans',sans-serif;">
                                — <?= $totalInstructors ?> instructor<?= $totalInstructors != 1 ? 's' : '' ?>
                            </span>
                        </h6>
                        <small class="text-muted" style="font-size:11px;">
                            Load level: <span style="color:#ef4444;">■</span> High (&gt;10)
                            <span style="color:#f5a623;">■</span> Medium (5–10)
                            <span style="color:#10b981;">■</span> Low (1–4)
                            <span style="color:#9ca3af;">■</span> None
                        </small>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th style="width:40px;">#</th>
                                        <th>INSTRUCTOR</th>
                                        <th class="text-center">TOTAL<br>SCHEDULES</th>
                                        <th class="text-center">STUDENTS<br>ASSIGNED</th>
                                        <th class="text-center">TODAY</th>
                                        <th class="text-center">UPCOMING</th>
                                        <th class="text-center">COMPLETED</th>
                                        <th class="text-center">CANCELLED</th>
                                        <th class="text-center">NO SHOW</th>
                                        <th>SCHEDULE LOAD</th>
                                        <th class="text-center">LOAD LEVEL</th>
                                        <th>LAST SCHEDULE</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $avatarColors = ['#2f80ed','#10b981','#f5a623','#8b5cf6','#ef4444','#06b6d4','#f43f5e'];
                                $maxSchedules = 1; // avoid divide by zero

                                // First pass: find max for bar scaling
                                $rows = [];
                                while ($row = $workloadResult->fetch_assoc()) {
                                    $rows[] = $row;
                                    if ($row['total_schedules'] > $maxSchedules) $maxSchedules = $row['total_schedules'];
                                }

                                if (empty($rows)):
                                ?>
                                    <tr>
                                        <td colspan="12">
                                            <div class="empty-state">
                                                <i class="fas fa-user-slash"></i>
                                                <p>No instructors registered yet.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: foreach ($rows as $idx => $row):
                                    $initials = strtoupper(implode('', array_map(
                                        fn($w) => $w[0],
                                        array_filter(explode(' ', trim($row['instructor_name'])))
                                    )));
                                    $initials = substr($initials, 0, 2);
                                    $color    = $avatarColors[$idx % count($avatarColors)];

                                    $total    = (int)$row['total_schedules'];
                                    $barPct   = $maxSchedules > 0 ? round(($total / $maxSchedules) * 100) : 0;

                                    // Bar color based on load
                                    if ($total === 0)     { $barColor = '#d1d5db'; $loadClass = 'load-none';   $loadLabel = 'No Load'; }
                                    elseif ($total <= 4)  { $barColor = '#10b981'; $loadClass = 'load-low';    $loadLabel = 'Low'; }
                                    elseif ($total <= 10) { $barColor = '#f5a623'; $loadClass = 'load-medium'; $loadLabel = 'Medium'; }
                                    else                  { $barColor = '#ef4444'; $loadClass = 'load-high';   $loadLabel = 'High'; }

                                    $lastSched = $row['last_schedule']
                                        ? date('M d, Y', strtotime($row['last_schedule']))
                                        : '—';
                                ?>
                                    <tr>
                                        <td style="color:#9ca3af; font-size:12px;"><?= $idx + 1 ?></td>

                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar mr-2" style="background:<?= $color ?>;"><?= $initials ?></div>
                                                <div>
                                                    <div style="font-weight:600; font-size:13px; color:#0b1437;">
                                                        <?= htmlspecialchars($row['instructor_name']) ?>
                                                    </div>
                                                    <div style="font-size:11px; color:#9ca3af;">
                                                        <?= htmlspecialchars($row['email']) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="text-center">
                                            <span class="count-pill pill-blue"><?= $total ?></span>
                                        </td>

                                        <td class="text-center">
                                            <span class="count-pill pill-purple"><?= (int)$row['total_students'] ?></span>
                                        </td>

                                        <td class="text-center">
                                            <span class="count-pill <?= $row['today'] > 0 ? 'pill-orange' : 'pill-gray' ?>">
                                                <?= (int)$row['today'] ?>
                                            </span>
                                        </td>

                                        <td class="text-center">
                                            <span class="count-pill pill-blue"><?= (int)$row['upcoming'] ?></span>
                                        </td>

                                        <td class="text-center">
                                            <span class="count-pill pill-green"><?= (int)$row['completed'] ?></span>
                                        </td>

                                        <td class="text-center">
                                            <span class="count-pill pill-red"><?= (int)$row['cancelled'] ?></span>
                                        </td>

                                        <td class="text-center">
                                            <span class="count-pill pill-gray"><?= (int)$row['no_show'] ?></span>
                                        </td>

                                        <td>
                                            <div class="workload-bar-wrap">
                                                <div class="workload-bar-track">
                                                    <div class="workload-bar-fill"
                                                         style="width:<?= $barPct ?>%; background:<?= $barColor ?>;"></div>
                                                </div>
                                                <span style="font-size:12px; color:#718096; min-width:28px;"><?= $total ?></span>
                                            </div>
                                        </td>

                                        <td class="text-center">
                                            <span class="load-badge <?= $loadClass ?>"><?= $loadLabel ?></span>
                                        </td>

                                        <td style="font-size:12px; color:#718096; white-space:nowrap;"><?= $lastSched ?></td>
                                    </tr>
                                <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <footer class="sticky-footer bg-white">
            <div class="container my-auto">
                <div class="copyright text-center my-auto">
                    <span style="font-size:13px; color:#9ca3af;">
                        © <?= date('Y') ?> <strong style="color:#0b1437;">Digitract</strong> — Instructor Scheduling &amp; Performance Monitoring System
                    </span>
                </div>
            </div>
        </footer>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/startbootstrap-sb-admin-2/4.1.4/js/sb-admin-2.min.js"></script>
</body>
</html>
