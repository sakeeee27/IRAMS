<?php
session_start();
if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit;
}
include 'db.php';

$today = date('Y-m-d');

$start_date = $_GET['start_date'] ?? ($_GET['date'] ?? $today);
$end_date   = $_GET['end_date'] ?? $start_date;
$sel_dept   = isset($_GET['dept']) ? (int)$_GET['dept'] : 0;

$selected_employee_ids = $_GET['employees'] ?? [];
if(!is_array($selected_employee_ids)){
    $selected_employee_ids = [$selected_employee_ids];
}
$selected_employee_ids = array_values(array_unique(array_filter(array_map('intval', $selected_employee_ids))));

function valid_date_or_today($value, $fallback){
    $dt = DateTime::createFromFormat('Y-m-d', $value);
    return ($dt && $dt->format('Y-m-d') === $value) ? $value : $fallback;
}

$start_date = valid_date_or_today($start_date, $today);
$end_date   = valid_date_or_today($end_date, $start_date);

if(strtotime($end_date) < strtotime($start_date)){
    $end_date = $start_date;
}

function bind_stmt_params($stmt, $types, &$params){
    $refs = [$types];
    foreach($params as $key => $value){
        $refs[] = &$params[$key];
    }
    return call_user_func_array([$stmt, 'bind_param'], $refs);
}

function fmt_duration($secs){
    if($secs === null || $secs <= 0) return '-';
    $h = floor($secs / 3600);
    $m = floor(($secs % 3600) / 60);
    return "{$h}h {$m}m";
}

function fmt_time($t){
    if(!$t) return '-';
    return date('h:i:s A', strtotime($t));
}

function fmt_date_range($start, $end){
    if($start === $end){
        return date('F j, Y', strtotime($start));
    }
    return date('F j, Y', strtotime($start)) . ' to ' . date('F j, Y', strtotime($end));
}

// Departments
$depts = [];
$dr = $conn->query("SELECT id, name FROM departments ORDER BY name");
while($d = $dr->fetch_assoc()){
    $depts[] = $d;
}

// Employee dropdown options
$employee_options = [];
$opt_result = $conn->query("
    SELECT users.id, users.employee_id, users.name, departments.name AS department
    FROM users
    LEFT JOIN departments ON users.department_id = departments.id
    ORDER BY users.surname, users.first_name, users.name
");
while($emp = $opt_result->fetch_assoc()){
    $employee_options[] = $emp;
}

// Date list
$date_range = [];
$period_start = new DateTime($start_date);
$period_end   = new DateTime($end_date);
$period_end->modify('+1 day');

foreach(new DatePeriod($period_start, new DateInterval('P1D'), $period_end) as $dt){
    $date_range[] = $dt->format('Y-m-d');
}

// Employees included in report
$emp_where = [];
$emp_types = '';
$emp_params = [];

if($sel_dept > 0){
    $emp_where[] = "users.department_id = ?";
    $emp_types .= "i";
    $emp_params[] = $sel_dept;
}

if(!empty($selected_employee_ids)){
    $placeholders = implode(',', array_fill(0, count($selected_employee_ids), '?'));
    $emp_where[] = "users.id IN ($placeholders)";
    $emp_types .= str_repeat('i', count($selected_employee_ids));
    foreach($selected_employee_ids as $id){
        $emp_params[] = $id;
    }
}

$emp_where_sql = !empty($emp_where) ? "WHERE " . implode(" AND ", $emp_where) : "";

$emp_stmt = $conn->prepare("
    SELECT users.id, users.employee_id, users.name, users.surname, users.first_name,
           users.position, users.photo, departments.name AS department
    FROM users
    LEFT JOIN departments ON users.department_id = departments.id
    $emp_where_sql
    ORDER BY users.surname, users.first_name, users.name
");

if($emp_types !== ''){
    bind_stmt_params($emp_stmt, $emp_types, $emp_params);
}

$emp_stmt->execute();
$emp_result = $emp_stmt->get_result();

$employee_rows = [];
while($emp = $emp_result->fetch_assoc()){
    $employee_rows[] = $emp;
}

// Attendance map
$attendance_map = [];
$employee_ids_for_query = array_column($employee_rows, 'id');

if(!empty($employee_ids_for_query)){
    $shift_start = $start_date . ' 04:00:00';
    $shift_end   = date('Y-m-d', strtotime($end_date . ' +1 day')) . ' 03:00:00';

    $att_placeholders = implode(',', array_fill(0, count($employee_ids_for_query), '?'));
    $att_types = 'ss' . str_repeat('i', count($employee_ids_for_query));
    $att_params = [$shift_start, $shift_end];

    foreach($employee_ids_for_query as $id){
        $att_params[] = (int)$id;
    }

    $att_stmt = $conn->prepare("
        SELECT
            attendance.user_id,
            DATE(
                CASE
                    WHEN TIME(attendance.time) < '04:00:00'
                    THEN DATE_SUB(attendance.time, INTERVAL 1 DAY)
                    ELSE attendance.time
                END
            ) AS shift_date,
            MIN(CASE WHEN attendance.status = 'IN' THEN attendance.time END) AS first_in,
            MAX(CASE WHEN attendance.status = 'OUT' THEN attendance.time END) AS last_out,
            COUNT(attendance.id) AS total_scans
        FROM attendance
        WHERE attendance.time >= ?
          AND attendance.time <= ?
          AND attendance.user_id IN ($att_placeholders)
        GROUP BY attendance.user_id, shift_date
    ");

    bind_stmt_params($att_stmt, $att_types, $att_params);
    $att_stmt->execute();
    $att_result = $att_stmt->get_result();

    while($att = $att_result->fetch_assoc()){
        $attendance_map[(int)$att['user_id']][$att['shift_date']] = $att;
    }
}

// Build report rows: one employee per date
$rows = [];
$total_hours = 0;
$present_count = 0;
$absent_count = 0;

foreach($date_range as $report_date){
    foreach($employee_rows as $emp){
        $att = $attendance_map[(int)$emp['id']][$report_date] ?? [
            'first_in' => null,
            'last_out' => null,
            'total_scans' => 0,
        ];

        $hours_worked = null;
        if(!empty($att['first_in']) && !empty($att['last_out'])){
            $diff = strtotime($att['last_out']) - strtotime($att['first_in']);
            if($diff > 0){
                $hours_worked = $diff;
                $total_hours += $diff;
            }
        }

        if((int)$att['total_scans'] > 0){
            $present_count++;
        } else {
            $absent_count++;
        }

        $rows[] = array_merge($emp, [
            'report_date' => $report_date,
            'first_in' => $att['first_in'],
            'last_out' => $att['last_out'],
            'total_scans' => (int)$att['total_scans'],
            'hours_worked' => $hours_worked,
        ]);
    }
}

$dept_name = 'All Departments';
if($sel_dept > 0){
    foreach($depts as $d){
        if((int)$d['id'] === $sel_dept){
            $dept_name = $d['name'];
            break;
        }
    }
}

$employee_filter_label = empty($selected_employee_ids)
    ? 'All Employees'
    : count($selected_employee_ids) . ' Selected Employee(s)';

// Export
if(isset($_GET['export'])){
    $filename = "Summary_" . $start_date . "_to_" . $end_date . ".xls";

    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Cache-Control: max-age=0");
    ?>
<html>
<head>
<meta charset="UTF-8">
<style>
body{font-family:Arial,sans-serif;font-size:11pt;}
table{border-collapse:collapse;width:100%;}
th{background:#1e40af;color:white;padding:8px 12px;border:1px solid #1e40af;font-size:11pt;}
td{padding:7px 12px;border:1px solid #cbd5e1;font-size:10pt;}
tr:nth-child(even) td{background:#f1f5f9;}
.title{font-size:16pt;font-weight:bold;color:#1e293b;margin-bottom:4px;}
.sub{font-size:10pt;color:#64748b;margin-bottom:16px;}
</style>
</head>
<body>
<p class="title">Attendance Summary</p>
<p class="sub">
Date Range: <strong><?= htmlspecialchars(fmt_date_range($start_date, $end_date)) ?></strong>
&nbsp;|&nbsp; Department: <strong><?= htmlspecialchars($dept_name) ?></strong>
&nbsp;|&nbsp; Employees: <strong><?= htmlspecialchars($employee_filter_label) ?></strong>
&nbsp;|&nbsp; Generated: <strong><?= date('F j, Y h:i A') ?></strong>
&nbsp;|&nbsp; By: <strong><?= htmlspecialchars($_SESSION['admin_name'] ?? $_SESSION['admin_user']) ?></strong>
</p>

<table>
<thead>
<tr>
    <th>#</th>
    <th>Date</th>
    <th>Photo</th>
    <th>Employee ID</th>
    <th>Name</th>
    <th>Position</th>
    <th>Department</th>
    <th>First IN</th>
    <th>Last OUT</th>
    <th>Hours Worked</th>
    <th>Total Scans</th>
</tr>
</thead>
<tbody>
<?php foreach($rows as $i => $r): ?>
<tr>
    <td><?= $i + 1 ?></td>
    <td><?= htmlspecialchars(date('F j, Y', strtotime($r['report_date']))) ?></td>
    <td><?= htmlspecialchars($r['employee_id'] ?? '-') ?></td>
    <td><strong><?= htmlspecialchars($r['name']) ?></strong></td>
    <td><?= htmlspecialchars($r['position'] ?? '-') ?></td>
    <td><?= htmlspecialchars($r['department'] ?? '-') ?></td>
    <td><?= fmt_time($r['first_in']) ?></td>
    <td><?= fmt_time($r['last_out']) ?></td>
    <td><?= fmt_duration($r['hours_worked']) ?></td>
    <td><?= (int)$r['total_scans'] ?></td>
</tr>
<?php endforeach; ?>
</tbody>
<tfoot>
<tr>
    <td colspan="8" style="text-align:right;font-weight:bold;background:#f8fafc;">Total Present Records: <?= $present_count ?></td>
    <td style="font-weight:bold;background:#f8fafc;"><?= fmt_duration($total_hours) ?></td>
    <td style="background:#f8fafc;"></td>
</tr>
</tfoot>
</table>
</body>
</html>
    <?php
    exit;
}

// ── Page variables for header ──
$page_title = "Summary Report";
$page_type  = "admin";
$extra_css  = <<<'ADMINCSS'
    :root {
        --bg:#0f172a; --surface:#1e293b; --surface2:#0f172a; --border:#334155;
        --text:#e2e8f0; --text-muted:#64748b; --text-sub:#94a3b8;
        --text-strong:#f1f5f9; --text-td:#cbd5e1; --nav-hover:#0f172a;
        --input-bg:#0f172a; --row-hover:rgba(255,255,255,0.02);
        --shadow:0 4px 24px rgba(0,0,0,0.4);
    }
    html.light {
        --bg:#f1f5f9; --surface:#ffffff; --surface2:#f8fafc; --border:#e2e8f0;
        --text:#1e293b; --text-muted:#94a3b8; --text-sub:#64748b;
        --text-strong:#0f172a; --text-td:#334155; --nav-hover:#f1f5f9;
        --input-bg:#f8fafc; --row-hover:rgba(0,0,0,0.02);
        --shadow:0 4px 24px rgba(0,0,0,0.08);
    }
    *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
    body { background:var(--bg); color:var(--text); font-family:Arial,sans-serif; }

    .sidebar { width:240px; min-height:100vh; background:var(--surface); border-right:1px solid var(--border); position:fixed; top:0; left:0; display:flex; flex-direction:column; z-index:100; }
    .sidebar-brand { padding:24px 20px 20px; border-bottom:1px solid var(--border); }
    .sidebar-brand h2 { font-size:11px; font-weight:bold; color:var(--text-strong); letter-spacing:1px; text-transform:uppercase; margin:0; }
    .sidebar-brand p { font-size:11px; color:var(--text-muted); margin:4px 0 0; }
    .sidebar-nav { flex:1; padding:16px 0; }
    .nav-label { font-size:10px; font-weight:bold; color:var(--text-muted); text-transform:uppercase; letter-spacing:1.5px; padding:12px 20px 6px; }
    .nav-item { display:flex; align-items:center; gap:12px; padding:11px 20px; color:var(--text-sub); text-decoration:none; font-size:14px; border-left:3px solid transparent; transition:background 0.15s,color 0.15s; }
    .nav-item:hover { background:var(--nav-hover); color:var(--text); }
    .nav-item.active { background:var(--nav-hover); color:#38bdf8; border-left-color:#38bdf8; font-weight:bold; }
    .nav-icon { font-size:16px; width:20px; text-align:center; }
    .sidebar-footer { padding:16px 20px; border-top:1px solid var(--border); }
    .admin-info { margin-bottom:10px; }
    .admin-info span { font-size:11px; color:var(--text-muted); display:block; }
    .admin-info strong { font-size:13px; color:var(--text); }
    .btn-logout { display:block; text-align:center; padding:8px; border:1px solid var(--border); border-radius:8px; color:#ef4444; text-decoration:none; font-size:12px; font-weight:bold; }
    .btn-logout:hover { background:rgba(239,68,68,0.1); }

    .theme-toggle { display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; padding:8px 12px; background:var(--surface2); border:1px solid var(--border); border-radius:8px; }
    .theme-toggle-label { font-size:12px; color:var(--text-sub); display:flex; align-items:center; gap:6px; }
    .toggle-switch { position:relative; width:40px; height:22px; cursor:pointer; }
    .toggle-switch input { opacity:0; width:0; height:0; }
    .toggle-track { position:absolute; inset:0; background:#334155; border-radius:999px; transition:background 0.3s; }
    .toggle-switch input:checked + .toggle-track { background:#0ea5e9; }
    .toggle-thumb { position:absolute; top:3px; left:3px; width:16px; height:16px; background:white; border-radius:50%; transition:transform 0.3s; }
    .toggle-switch input:checked ~ .toggle-thumb { transform:translateX(18px); }

    .main { margin-left:240px; padding:28px; min-height:100vh; }
    .page-header { margin-bottom:24px; padding-bottom:16px; border-bottom:1px solid var(--border); display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:12px; }
    .page-header h1 { font-size:20px; font-weight:bold; color:var(--text-strong); margin:0; }
    .page-header p { font-size:13px; color:var(--text-muted); margin:4px 0 0; }

    .stat-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:28px; }
    .stat-card { background:var(--surface); border:1px solid var(--border); border-radius:14px; padding:20px; box-shadow:var(--shadow); }
    .stat-icon { font-size:24px; margin-bottom:10px; }
    .stat-val { font-size:30px; font-weight:bold; color:var(--text-strong); line-height:1; margin-bottom:4px; }
    .stat-lbl { font-size:12px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; }

    .panel { background:var(--surface); border:1px solid var(--border); border-radius:14px; overflow:hidden; margin-bottom:24px; box-shadow:var(--shadow); }
    .panel-header { padding:16px 20px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; }
    .panel-title { font-size:14px; font-weight:bold; color:var(--text-strong); }

    .ctrl-input { background:var(--input-bg); border:1px solid var(--border); border-radius:8px; color:var(--text); padding:7px 12px; font-size:13px; outline:none; transition:background 0.3s,border-color 0.2s; }
    .ctrl-input:focus { border-color:#3b82f6; }
    .ctrl-input::placeholder { color:var(--text-muted); }

    .tbl { width:100%; border-collapse:collapse; }
    .tbl thead tr { background:var(--surface2); }
    .tbl th { padding:12px 16px; font-size:11px; font-weight:bold; text-transform:uppercase; letter-spacing:1px; color:var(--text-muted); text-align:left; white-space:nowrap; }
    .tbl td { padding:12px 16px; font-size:13px; color:var(--text-td); border-top:1px solid var(--border); vertical-align:middle; }
    .tbl tbody tr:hover { background:var(--row-hover); }
    .emp-photo { width:38px; height:38px; border-radius:50%; object-fit:cover; border:2px solid var(--border); object-position:top; }

    .badge-in  { background:rgba(34,197,94,0.15);  color:#22c55e; border:1px solid rgba(34,197,94,0.25);  padding:3px 10px; border-radius:999px; font-size:11px; font-weight:bold; }
    .badge-out { background:rgba(239,68,68,0.15);  color:#ef4444; border:1px solid rgba(239,68,68,0.25);  padding:3px 10px; border-radius:999px; font-size:11px; font-weight:bold; }
    .badge-partial { background:rgba(234,179,8,0.15); color:#eab308; border:1px solid rgba(234,179,8,0.25); padding:3px 10px; border-radius:999px; font-size:11px; font-weight:bold; }

    .hours-pill { background:rgba(56,189,248,0.12); color:#38bdf8; border:1px solid rgba(56,189,248,0.25); padding:3px 10px; border-radius:999px; font-size:12px; font-weight:bold; }

    html.light .tbl td, html.light .tbl td span { color:var(--text-strong); }

    .btn-export { background:linear-gradient(135deg,#1e40af,#0ea5e9); border:none; border-radius:8px; color:white; font-size:12px; font-weight:bold; padding:8px 16px; cursor:pointer; text-decoration:none; }
    .btn-export:hover { opacity:0.9; color:white; }

    @media(max-width:900px){ .stat-grid{ grid-template-columns:1fr 1fr; } }
ADMINCSS;
include 'includes/header.php';
?>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-brand">
        <img id="siteLogo" src="irams.png" style="width:200px;height:auto;margin-bottom:6px;display:block;">
        <h2>IRAMS Admin</h2>
        <p>INSPI RFID ATTENDANCE SYSTEM</p>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-label">Main</div>
        <a href="admin.php?page=dashboard"  class="nav-item"><span class="nav-icon">&#9741;</span>  Dashboard</a>
        <a href="admin.php?page=employees"  class="nav-item"><span class="nav-icon">&#128100;</span> Employees</a>
        <a href="admin.php?page=attendance" class="nav-item"><span class="nav-icon">&#128203;</span> Attendance Log</a>
        <a href="summary_report.php"        class="nav-item active"><span class="nav-icon">&#128196;</span> Summary Report</a>
        <div class="nav-label" style="margin-top:8px;">System</div>
        <a href="index.php" target="_blank" class="nav-item"><span class="nav-icon">&#127760;</span> Live Dashboard</a>
    </nav>
    <div class="sidebar-footer">
        <div class="admin-info">
            <span>Logged in as</span>
            <strong><?= htmlspecialchars($_SESSION['admin_name'] ?? $_SESSION['admin_user']) ?></strong>
        </div>
        <div class="theme-toggle">
            <span class="theme-toggle-label" id="themeLabel">&#127769; Dark Mode</span>
            <label class="toggle-switch">
                <input type="checkbox" id="themeToggle">
                <div class="toggle-track"></div>
                <div class="toggle-thumb"></div>
            </label>
        </div>
        <a href="logout.php" class="btn-logout">&#128274; Logout</a>
    </div>
</div>

<!-- MAIN -->
<div class="main">

<div class="page-header">
    <div>
        <h1>&#128196; Daily Summary Report</h1>
        <p>First Time-In and Last Time-Out per employee — with computed hours worked.</p>
    </div>
    <!-- FILTERS FORM -->
    <!-- FILTERS FORM -->
<form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
    <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>" class="ctrl-input">
    <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>" class="ctrl-input">

    <select name="dept" class="ctrl-input">
        <option value="0">All Departments</option>
        <?php foreach($depts as $d): ?>
        <option value="<?= $d['id'] ?>" <?= $sel_dept == $d['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($d['name']) ?>
        </option>
        <?php endforeach; ?>
    </select>

    <select name="employees[]" multiple size="5" class="ctrl-input" style="min-width:260px;">
        <?php foreach($employee_options as $emp): ?>
        <option value="<?= $emp['id'] ?>" <?= in_array((int)$emp['id'], $selected_employee_ids, true) ? 'selected' : '' ?>>
            <?= htmlspecialchars(($emp['employee_id'] ? $emp['employee_id'] . ' - ' : '') . $emp['name'] . ' (' . ($emp['department'] ?? 'No Department') . ')') ?>
        </option>
        <?php endforeach; ?>
    </select>

    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    <button type="submit" name="export" value="1" class="btn-export">Export Excel</button>
</form>
</div>

<!-- STAT CARDS -->
<?php
$total_incomplete = count(array_filter($rows, fn($r) => $r['total_scans'] > 0 && (!$r['first_in'] || !$r['last_out'])));
$avg_hours        = $present_count > 0 ? $total_hours / $present_count : 0;
$total_employees  = count($rows);
?>
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon">&#128100;</div>
        <div class="stat-val"><?= $total_employees ?></div>
        <div class="stat-lbl">Total Employees</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">&#128994;</div>
        <div class="stat-val" style="color:#22c55e;"><?= $present_count ?></div>
        <div class="stat-lbl">Present</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">&#128336;</div>
        <div class="stat-val" style="font-size:22px;color:#38bdf8;"><?= fmt_duration($total_hours) ?></div>
        <div class="stat-lbl">Total Hours Logged</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">&#9888;</div>
        <div class="stat-val" style="color:#eab308;"><?= $total_incomplete ?></div>
        <div class="stat-lbl">Incomplete Records</div>
    </div>
</div>
<!-- TABLE -->
<div class="panel">
    <div class="panel-header">
        <span class="panel-title">
            &#128203; Summary for <?= htmlspecialchars(fmt_date_range($start_date, $end_date)) ?>
            <?php if($sel_dept > 0):
                $dn = array_filter($depts, fn($d) => $d['id'] == $sel_dept);
                $dn = reset($dn);
            ?>
            &nbsp;&mdash;&nbsp; <?= htmlspecialchars($dn['name'] ?? '') ?>
            <?php endif; ?>
        </span>
        <input type="text" id="tableSearch" class="ctrl-input" placeholder="&#128269; Search..." style="min-width:200px;">
    </div>
    <div class="table-responsive">
    <table class="tbl" id="summaryTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Photo</th>
                <th>Employee ID</th>
                <th>Name</th>
                <th>Position</th>
                <th>Department</th>
                <th>First IN</th>
                <th>Last OUT</th>
                <th>Hours Worked</th>
                <th>Scans</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php if(empty($rows)): ?>
        <tr><td colspan="11" style="text-align:center;padding:40px;color:var(--text-muted);">No attendance records found for this date.</td></tr>
        <?php else: ?>
        <?php foreach($rows as $i => $r):
            $has_in  = !empty($r['first_in']);
            $has_out = !empty($r['last_out']);
            if($r['total_scans'] == 0)   $rec_status = 'absent';
            elseif($has_in && $has_out)  $rec_status = 'complete';
            elseif($has_in && !$has_out) $rec_status = 'partial';
            else                         $rec_status = 'out_only';
        ?>
        <tr>
            <td style="color:var(--text-muted);"><?= $i+1 ?></td>
            <td><?= htmlspecialchars(date('M j, Y', strtotime($r['report_date']))) ?></td>
            <td><img src="<?= htmlspecialchars($r['photo'] ?? 'default.png') ?>" class="emp-photo" onerror="this.src='default.png'"></td>
            <td><?= htmlspecialchars($r['employee_id'] ?? '—') ?></td>
            <td style="font-weight:bold;color:var(--text-strong);"><?= htmlspecialchars($r['name']) ?></td>
            <td><?= htmlspecialchars($r['position'] ?? '—') ?></td>
            <td><?= htmlspecialchars($r['department'] ?? '—') ?></td>
            <td style="color:#22c55e;font-weight:bold;"><?= fmt_time($r['first_in']) ?></td>
            <td style="color:#ef4444;font-weight:bold;"><?= fmt_time($r['last_out']) ?></td>
            <td>
                <?php if($r['hours_worked']): ?>
                <span class="hours-pill"><?= fmt_duration($r['hours_worked']) ?></span>
                <?php else: ?>
                <span style="color:var(--text-muted);">—</span>
                <?php endif; ?>
            </td>
            <td style="text-align:center;"><?= $r['total_scans'] ?></td>
            <td>
                <?php if($r['total_scans'] == 0): ?>
                    <span style="background:rgba(100,116,139,0.15);color:#94a3b8;border:1px solid rgba(100,116,139,0.25);padding:3px 10px;border-radius:999px;font-size:11px;font-weight:bold;">Absent</span>
                <?php elseif($rec_status === 'complete'): ?>
                    <span class="badge-in">Complete</span>
                <?php elseif($rec_status === 'partial'): ?>
                    <span style="background:rgba(234,179,8,0.15);color:#eab308;border:1px solid rgba(234,179,8,0.25);padding:3px 10px;border-radius:999px;font-size:11px;font-weight:bold;">No OUT</span>
                <?php else: ?>
                    <span class="badge-out">No IN</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
    <div style="padding:10px 16px;font-size:12px;color:var(--text-muted);border-top:1px solid var(--border);">
       Showing <?= count($rows) ?> record(s) &mdash; <?= htmlspecialchars(fmt_date_range($start_date, $end_date)) ?>
    </div>
</div>

</div><!-- /main -->

<script>
// ── Theme ──
(function(){
    function applyTheme(t){
        const html=document.documentElement,toggle=document.getElementById('themeToggle'),label=document.getElementById('themeLabel'),logo=document.getElementById('siteLogo');
        if(t==='light'){html.classList.add('light');toggle.checked=true;label.innerHTML='&#9728;&#65039; Light Mode';if(logo)logo.src='irams.png';}
        else{html.classList.remove('light');toggle.checked=false;label.innerHTML='&#127769; Dark Mode';if(logo)logo.src='iramswhite.png';}
        localStorage.setItem('rfid_theme',t);
    }
    applyTheme(localStorage.getItem('rfid_theme')||'dark');
    document.getElementById('themeToggle').addEventListener('change',function(){ applyTheme(this.checked?'light':'dark'); });
})();

// ── Table search ──
document.getElementById('tableSearch').addEventListener('input', function(){
    const term = this.value.toLowerCase();
    document.querySelectorAll('#summaryTable tbody tr').forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(term) ? '' : 'none';
    });
});
</script>

<?php include 'includes/footer.php'; ?>