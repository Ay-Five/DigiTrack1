<?php
require '../includes/db.php';

//comment anay
//require 'admin_auth.php';

// ── FILTERS ────────────────────────────────────────────────────
$filterInstructor = isset($_GET['instructor_id']) ? (int)$_GET['instructor_id'] : 0;
$filterStudent    = isset($_GET['student_id'])    ? (int)$_GET['student_id']    : 0;
$filterStatus     = isset($_GET['status'])        ? $_GET['status']             : '';
$filterDate       = isset($_GET['date'])          ? $_GET['date']               : '';

// ── BUILD WHERE CLAUSE ─────────────────────────────────────────
$where   = [];
$where[] = "1=1";
if ($filterInstructor) $where[] = "s.instructor_id = $filterInstructor";
if ($filterStudent)    $where[] = "s.student_id = $filterStudent";
if ($filterStatus)     $where[] = "s.status = '" . $conn->real_escape_string($filterStatus) . "'";
if ($filterDate)       $where[] = "s.schedule_date = '" . $conn->real_escape_string($filterDate) . "'";
$whereSQL = implode(' AND ', $where);

// ── SUMMARY COUNTS ─────────────────────────────────────────────
$totalSchedules   = $conn->query("SELECT COUNT(*) AS cnt FROM schedules")->fetch_assoc()['cnt'];
$todaySchedules   = $conn->query("SELECT COUNT(*) AS cnt FROM schedules WHERE schedule_date = CURDATE()")->fetch_assoc()['cnt'];
$completedCount   = $conn->query("SELECT COUNT(*) AS cnt FROM schedules WHERE status = 'completed'")->fetch_assoc()['cnt'];
$cancelledCount   = $conn->query("SELECT COUNT(*) AS cnt FROM schedules WHERE status = 'cancelled'")->fetch_assoc()['cnt'];

// ── SCHEDULES LIST ─────────────────────────────────────────────
$scheduleResult = $conn->query("
    SELECT
        s.schedule_id,
        s.schedule_date,
        s.start_time,
        s.end_time,
        s.lesson_type,
        s.status,
        s.notes,
        i.fullname  AS instructor_name,
        i.instructor_id,
        st.fullname AS student_name,
        st.student_id
    FROM schedules s
    JOIN instructors i  ON i.instructor_id = s.instructor_id
    JOIN students    st ON st.student_id   = s.student_id
    WHERE $whereSQL
    ORDER BY s.schedule_date DESC, s.start_time ASC
");

// ── DROPDOWN DATA ──────────────────────────────────────────────
$instructorsList = $conn->query("SELECT instructor_id, fullname FROM instructors ORDER BY fullname ASC");
$studentsList    = $conn->query("SELECT student_id, fullname FROM students ORDER BY fullname ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedules – Digitract</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/startbootstrap-sb-admin-2/4.1.4/css/sb-admin-2.min.css">
    <style>
        :root {
            --navy:  #0b1437;
            --blue:  #1a3a8f;
            --accent:#2f80ed;
            --gold:  #f5a623;
        }
        body { font-family: 'DM Sans', sans-serif; background: #f4f6fc; }

        /* ── Sidebar ── */
        .sidebar { background: linear-gradient(180deg, #0b1437 0%, #1a3a8f 100%) !important; }
        .sidebar .nav-item .nav-link { color: rgba(255,255,255,.65) !important; font-family: 'DM Sans', sans-serif; }
        .sidebar .nav-item .nav-link:hover,
        .sidebar .nav-item.active .nav-link { color:#fff !important; background:rgba(47,128,237,.25) !important; border-radius:8px; }
        .sidebar-brand { background:rgba(0,0,0,.15) !important; }
        .sidebar-brand-text { font-family:'Syne',sans-serif !important; font-weight:800 !important; font-size:20px !important; }
        .sidebar-brand-text span { color:var(--gold); }
        .sidebar-divider { border-top:1px solid rgba(255,255,255,.1) !important; }
        .sidebar-heading { color:rgba(255,255,255,.4) !important; font-size:10px !important; letter-spacing:1.5px; }

        /* ── Topbar ── */
        .topbar { box-shadow:0 2px 12px rgba(0,0,0,.07); background:#fff !important; }
        .topbar-divider { border-right:1px solid #e5e7eb !important; }
        .topbar-logout {
            display:flex; align-items:center; gap:6px;
            color:#ef4444 !important; font-size:13px; font-weight:500;
            padding:6px 14px; border-radius:8px; transition:background .2s; text-decoration:none;
        }
        .topbar-logout:hover { background:#fee2e2; color:#dc2626 !important; text-decoration:none; }

        /* ── Cards ── */
        .card { border:none !important; border-radius:14px !important; box-shadow:0 2px 16px rgba(0,0,0,.07) !important; }
        .card-header { border-radius:14px 14px 0 0 !important; border-bottom:1px solid rgba(0,0,0,.06) !important; }

        /* ── Stat cards ── */
        .stat-card { border-radius:14px; padding:22px; color:#fff; position:relative; overflow:hidden; }
        .stat-card .icon { position:absolute; right:18px; top:50%; transform:translateY(-50%); font-size:48px; opacity:.15; }
        .stat-card h2 { font-family:'Syne',sans-serif; font-size:30px; font-weight:800; margin-bottom:4px; }
        .stat-card p  { font-size:13px; opacity:.8; margin:0; }
        .bg-grad-blue   { background:linear-gradient(135deg,#2f80ed,#1a6fd8); }
        .bg-grad-green  { background:linear-gradient(135deg,#10b981,#059669); }
        .bg-grad-orange { background:linear-gradient(135deg,#f5a623,#e08e10); }
        .bg-grad-red    { background:linear-gradient(135deg,#ef4444,#dc2626); }

        /* ── Table ── */
        .table thead th {
            font-family:'Syne',sans-serif; font-size:11px; letter-spacing:.8px;
            color:#718096; border-bottom:2px solid #f0f0f0; background:#fafafa;
            white-space:nowrap;
        }
        .table td { vertical-align:middle; font-size:14px; }

        /* ── Status badges ── */
        .badge-status    { padding:4px 11px; border-radius:100px; font-size:11px; font-weight:600; white-space:nowrap; }
        .badge-scheduled { background:#dbeafe; color:#1e40af; }
        .badge-completed { background:#d1fae5; color:#065f46; }
        .badge-cancelled { background:#fee2e2; color:#991b1b; }
        .badge-no_show   { background:#fef3c7; color:#92400e; }

        /* ── Avatar ── */
        .avatar {
            width:32px; height:32px; border-radius:50%;
            display:flex; align-items:center; justify-content:center;
            font-weight:700; font-size:12px; color:#fff; flex-shrink:0;
        }

        /* ── Filter bar ── */
        .filter-bar { background:#fff; border-radius:14px; padding:16px 20px; box-shadow:0 2px 16px rgba(0,0,0,.07); margin-bottom:24px; }
        .filter-bar .form-control, .filter-bar .form-select {
            border-radius:8px; border:1px solid #e5e7eb; font-size:13px; height:38px;
        }
        .filter-bar label { font-size:11px; font-weight:600; color:#718096; letter-spacing:.6px; margin-bottom:4px; display:block; }

        /* ── Page heading ── */
        .page-heading h1 { font-family:'Syne',sans-serif; font-size:26px; font-weight:700; color:#0b1437; }
        .page-heading p  { color:#718096; font-size:14px; }

        /* ── Empty state ── */
        .empty-state { padding:60px 20px; text-align:center; color:#9ca3af; }
        .empty-state i { font-size:48px; margin-bottom:16px; display:block; opacity:.4; }
        .empty-state p { font-size:14px; margin:0; }

        /* ── Lesson type chip ── */
        .lesson-chip {
            background:#f0f4ff; color:#2f80ed;
            padding:3px 10px; border-radius:100px; font-size:11px; font-weight:600;
        }
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
                    <a class="collapse-item active" href="schedules.php">View All Schedules</a>
                    <a class="collapse-item" href="workload.php">Workload Distribution</a>
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
                <div class="page-heading mb-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h1><i class="fas fa-calendar-days mr-2" style="color:#2f80ed;"></i>Schedules</h1>
                        <p>All instructor–student lesson schedules in one place.</p>
                    </div>
                </div>

                <!-- ── STAT CARDS ── -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="stat-card bg-grad-blue">
                            <p>Total Schedules</p>
                            <h2><?= $totalSchedules ?></h2>
                            <div class="icon"><i class="fas fa-calendar-alt"></i></div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="stat-card bg-grad-orange">
                            <p>Today's Lessons</p>
                            <h2><?= $todaySchedules ?></h2>
                            <div class="icon"><i class="fas fa-calendar-day"></i></div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="stat-card bg-grad-green">
                            <p>Completed</p>
                            <h2><?= $completedCount ?></h2>
                            <div class="icon"><i class="fas fa-circle-check"></i></div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="stat-card bg-grad-red">
                            <p>Cancelled</p>
                            <h2><?= $cancelledCount ?></h2>
                            <div class="icon"><i class="fas fa-circle-xmark"></i></div>
                        </div>
                    </div>
                </div>

                <!-- ── FILTER BAR ── -->
                <div class="filter-bar">
                    <form method="GET" action="schedules.php">
                        <div class="row align-items-end">
                            <div class="col-md-3 mb-2 mb-md-0">
                                <label>INSTRUCTOR</label>
                                <select name="instructor_id" class="form-control">
                                    <option value="">All Instructors</option>
                                    <?php
                                    $instructorsList->data_seek(0);
                                    while ($ins = $instructorsList->fetch_assoc()):
                                    ?>
                                        <option value="<?= $ins['instructor_id'] ?>"
                                            <?= $filterInstructor == $ins['instructor_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($ins['fullname']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-2 mb-md-0">
                                <label>STUDENT</label>
                                <select name="student_id" class="form-control">
                                    <option value="">All Students</option>
                                    <?php
                                    $studentsList->data_seek(0);
                                    while ($stu = $studentsList->fetch_assoc()):
                                    ?>
                                        <option value="<?= $stu['student_id'] ?>"
                                            <?= $filterStudent == $stu['student_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($stu['fullname']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-2 mb-2 mb-md-0">
                                <label>STATUS</label>
                                <select name="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="scheduled"  <?= $filterStatus === 'scheduled'  ? 'selected' : '' ?>>Scheduled</option>
                                    <option value="completed"  <?= $filterStatus === 'completed'  ? 'selected' : '' ?>>Completed</option>
                                    <option value="cancelled"  <?= $filterStatus === 'cancelled'  ? 'selected' : '' ?>>Cancelled</option>
                                    <option value="no_show"    <?= $filterStatus === 'no_show'    ? 'selected' : '' ?>>No Show</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-2 mb-md-0">
                                <label>DATE</label>
                                <input type="date" name="date" class="form-control"
                                       value="<?= htmlspecialchars($filterDate) ?>">
                            </div>
                            <div class="col-md-2 d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm flex-grow-1" style="border-radius:8px; height:38px;">
                                    <i class="fas fa-filter mr-1"></i> Filter
                                </button>
                                <a href="schedules.php" class="btn btn-outline-secondary btn-sm" style="border-radius:8px; height:38px; display:flex; align-items:center;">
                                    <i class="fas fa-rotate-left"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- ── SCHEDULE TABLE ── -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold" style="font-family:'Syne',sans-serif; color:#0b1437;">
                            <i class="fas fa-list text-primary mr-2"></i>
                            Schedule List
                            <?php if ($scheduleResult): ?>
                                <span class="text-muted font-weight-normal" style="font-size:12px; font-family:'DM Sans',sans-serif;">
                                    — <?= $scheduleResult->num_rows ?> record<?= $scheduleResult->num_rows !== 1 ? 's' : '' ?>
                                </span>
                            <?php endif; ?>
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>INSTRUCTOR</th>
                                        <th>STUDENT</th>
                                        <th>DATE</th>
                                        <th>TIME</th>
                                        <th>LESSON TYPE</th>
                                        <th>STATUS</th>
                                        <th>NOTES</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $avatarColors = ['#2f80ed','#10b981','#f5a623','#8b5cf6','#ef4444','#06b6d4','#f43f5e'];
                                if (!$scheduleResult || $scheduleResult->num_rows === 0):
                                ?>
                                    <tr>
                                        <td colspan="8">
                                            <div class="empty-state">
                                                <i class="fas fa-calendar-xmark"></i>
                                                <p>No schedules found<?= ($filterInstructor || $filterStudent || $filterStatus || $filterDate) ? ' matching your filters' : ' yet' ?>.</p>
                                                <?php if ($filterInstructor || $filterStudent || $filterStatus || $filterDate): ?>
                                                    <a href="schedules.php" class="btn btn-sm btn-outline-primary mt-3" style="border-radius:8px;">Clear Filters</a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: $rowNum = 1; while ($row = $scheduleResult->fetch_assoc()):
                                    // Instructor avatar
                                    $iWords    = array_filter(explode(' ', trim($row['instructor_name'])));
                                    $iInitials = strtoupper(implode('', array_map(fn($w) => $w[0], $iWords)));
                                    $iInitials = substr($iInitials, 0, 2);
                                    $iColor    = $avatarColors[($row['instructor_id'] - 1) % count($avatarColors)];

                                    // Student avatar
                                    $sWords    = array_filter(explode(' ', trim($row['student_name'])));
                                    $sInitials = strtoupper(implode('', array_map(fn($w) => $w[0], $sWords)));
                                    $sInitials = substr($sInitials, 0, 2);
                                    $sColor    = $avatarColors[($row['student_id']) % count($avatarColors)];

                                    // Date & time
                                    $dateFormatted  = date('M d, Y',  strtotime($row['schedule_date']));
                                    $dayName        = date('l',        strtotime($row['schedule_date']));
                                    $startFormatted = date('g:i A',    strtotime($row['start_time']));
                                    $endFormatted   = date('g:i A',    strtotime($row['end_time']));

                                    // Status badge
                                    $badgeClass = [
                                        'scheduled' => 'badge-scheduled',
                                        'completed' => 'badge-completed',
                                        'cancelled' => 'badge-cancelled',
                                        'no_show'   => 'badge-no_show',
                                    ][$row['status']] ?? 'badge-scheduled';

                                    $statusLabel = ucfirst(str_replace('_', ' ', $row['status']));
                                ?>
                                    <tr>
                                        <td style="color:#9ca3af; font-size:12px;"><?= $rowNum++ ?></td>

                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar mr-2" style="background:<?= $iColor ?>;"><?= $iInitials ?></div>
                                                <div>
                                                    <div style="font-weight:600; font-size:13px;"><?= htmlspecialchars($row['instructor_name']) ?></div>
                                                    <div style="font-size:11px; color:#9ca3af;">Instructor</div>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar mr-2" style="background:<?= $sColor ?>;"><?= $sInitials ?></div>
                                                <div>
                                                    <div style="font-weight:600; font-size:13px;"><?= htmlspecialchars($row['student_name']) ?></div>
                                                    <div style="font-size:11px; color:#9ca3af;">Student</div>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <div style="font-size:13px; font-weight:600; color:#0b1437;"><?= $dateFormatted ?></div>
                                            <div style="font-size:11px; color:#9ca3af;"><?= $dayName ?></div>
                                        </td>

                                        <td style="white-space:nowrap;">
                                            <i class="fas fa-clock mr-1" style="color:#2f80ed; font-size:11px;"></i>
                                            <span style="font-size:13px;"><?= $startFormatted ?> – <?= $endFormatted ?></span>
                                        </td>

                                        <td>
                                            <?php if ($row['lesson_type']): ?>
                                                <span class="lesson-chip"><?= htmlspecialchars($row['lesson_type']) ?></span>
                                            <?php else: ?>
                                                <span style="color:#d1d5db; font-size:12px;">—</span>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <span class="badge-status <?= $badgeClass ?>"><?= $statusLabel ?></span>
                                        </td>

                                        <td style="max-width:160px;">
                                            <?php if ($row['notes']): ?>
                                                <span style="font-size:12px; color:#718096;" title="<?= htmlspecialchars($row['notes']) ?>">
                                                    <?= htmlspecialchars(mb_strimwidth($row['notes'], 0, 40, '…')) ?>
                                                </span>
                                            <?php else: ?>
                                                <span style="color:#d1d5db; font-size:12px;">—</span>
                                            <?php endif; ?>
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
