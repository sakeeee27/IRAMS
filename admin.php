<?php
session_start();
if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit;
}
include 'db.php';

$upload_dir = __DIR__ . '/uploads/';
if(!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

// ── REGISTER ──
if(isset($_POST['save'])){
    $rfid  = $conn->real_escape_string($_POST['rfid_uid']);
    $check = $conn->query("SELECT id FROM users WHERE rfid_uid='$rfid'");
    if($check->num_rows > 0){
        header("Location: admin.php?page=employees&msg=duplicate"); exit;
    }
    $f        = $_FILES['photo'];
    $filename = time()."_".$f['name'];
    $path     = "uploads/".$filename;
    move_uploaded_file($f['tmp_name'], __DIR__."/".$path);

    $emp_id  = !empty($_POST['employee_id'])  ? "'".$conn->real_escape_string($_POST['employee_id'])."'"  : "NULL";
    $bio_id  = !empty($_POST['biometric_id']) ? "'".$conn->real_escape_string($_POST['biometric_id'])."'" : "NULL";
    $mid     = !empty($_POST['middle_name'])  ? "'".$conn->real_escape_string($_POST['middle_name'])."'"  : "NULL";
    $first   = $conn->real_escape_string($_POST['first_name']);
    $surname = $conn->real_escape_string($_POST['surname']);
    $fname   = trim("$first" . ($_POST['middle_name'] ? " {$_POST['middle_name']}" : "") . " $surname");
    $fname   = $conn->real_escape_string($fname);
    $pos     = $conn->real_escape_string($_POST['position']);
    $dept    = $conn->real_escape_string($_POST['department_id']);

    $conn->query("INSERT INTO users (rfid_uid,employee_id,biometric_id,first_name,middle_name,surname,name,position,department_id,photo)
        VALUES ('$rfid',$emp_id,$bio_id,'$first',$mid,'$surname','$fname','$pos','$dept','$path')");

    // Log activity
    $admin_n = $conn->real_escape_string($_SESSION['admin_name'] ?? $_SESSION['admin_user']);
    $conn->query("INSERT INTO activity_log (action, emp_name, admin_name) VALUES ('added','$fname','$admin_n')");

    header("Location: admin.php?page=employees&msg=registered"); exit;
}

// ── UPDATE ──
if(isset($_POST['update'])){
    $id      = $conn->real_escape_string($_POST['id']);
    $rfid    = !empty($_POST['rfid_uid'])      ? "'".$conn->real_escape_string($_POST['rfid_uid'])."'"      : "NULL";
    $emp_id  = !empty($_POST['employee_id'])   ? "'".$conn->real_escape_string($_POST['employee_id'])."'"   : "NULL";
    $bio_id  = !empty($_POST['biometric_id'])  ? "'".$conn->real_escape_string($_POST['biometric_id'])."'"  : "NULL";
    $mid     = !empty($_POST['middle_name'])   ? "'".$conn->real_escape_string($_POST['middle_name'])."'"   : "NULL";
    $first   = $conn->real_escape_string($_POST['first_name']);
    $surname = $conn->real_escape_string($_POST['surname']);
    $fname   = trim("$first" . ($_POST['middle_name'] ? " {$_POST['middle_name']}" : "") . " $surname");
    $fname   = $conn->real_escape_string($fname);
    $pos     = $conn->real_escape_string($_POST['position']);
    $dept    = $conn->real_escape_string($_POST['department_id']);

    if(!empty($_FILES['photo']['name'])){
        $f = $_FILES['photo'];
        $fn = time()."_".$f['name'];
        $p  = "uploads/".$fn;
        move_uploaded_file($f['tmp_name'], __DIR__."/".$p);
        $conn->query("UPDATE users SET rfid_uid=$rfid,employee_id=$emp_id,biometric_id=$bio_id,
            first_name='$first',middle_name=$mid,surname='$surname',name='$fname',
            position='$pos',department_id='$dept',photo='$p' WHERE id='$id'");
    } else {
        $conn->query("UPDATE users SET rfid_uid=$rfid,employee_id=$emp_id,biometric_id=$bio_id,
            first_name='$first',middle_name=$mid,surname='$surname',name='$fname',
            position='$pos',department_id='$dept' WHERE id='$id'");
    }

    // Log activity
    $admin_n = $conn->real_escape_string($_SESSION['admin_name'] ?? $_SESSION['admin_user']);
    $conn->query("INSERT INTO activity_log (action, emp_name, admin_name) VALUES ('edited','$fname','$admin_n')");

    header("Location: admin.php?page=employees&msg=updated"); exit;
}

// ── STATS FOR DASHBOARD ──
$total_emp   = $conn->query("SELECT COUNT(*) c FROM users")->fetch_assoc()['c'];
$total_dept  = $conn->query("SELECT COUNT(*) c FROM departments")->fetch_assoc()['c'];
$today       = date('Y-m-d');
$today_att   = $conn->query("SELECT COUNT(*) c FROM attendance WHERE DATE(time)='$today'")->fetch_assoc()['c'];
$total_att   = $conn->query("SELECT COUNT(*) c FROM attendance")->fetch_assoc()['c'];

$page = $_GET['page'] ?? 'dashboard';
?>
<?php
$page_title = "Admin Panel";
$page_type  = "admin";
$extra_css  = <<<'ADMINCSS'
    /* ══ CSS VARIABLES — DARK (default) ══ */
    :root {
        --bg:        #0f172a;
        --surface:   #1e293b;
        --surface2:  #0f172a;
        --border:    #334155;
        --text:      #e2e8f0;
        --text-muted:#64748b;
        --text-sub:  #94a3b8;
        --text-strong:#f1f5f9;
        --text-td:   #cbd5e1;
        --nav-hover: #0f172a;
        --input-bg:  #0f172a;
        --row-hover: rgba(255,255,255,0.02);
        --shadow:    0 4px 24px rgba(0,0,0,0.4);
    }

    /* ══ CSS VARIABLES — LIGHT ══ */
    html.light {
        --bg:        #f1f5f9;
        --surface:   #ffffff;
        --surface2:  #f8fafc;
        --border:    #e2e8f0;
        --text:      #1e293b;
        --text-muted:#94a3b8;
        --text-sub:  #64748b;
        --text-strong:#0f172a;
        --text-td:   #334155;
        --nav-hover: #f1f5f9;
        --input-bg:  #f8fafc;
        --row-hover: rgba(0,0,0,0.02);
        --shadow:    0 4px 24px rgba(0,0,0,0.08);
    }

    *, *::before, *::after { box-sizing: border-box; margin:0; padding:0; }

    body {
        background: var(--bg);
        color: var(--text);
        font-family: Arial, sans-serif;
        margin: 0;
        transition: background 0.3s, color 0.3s;
    }

    /* ── SIDEBAR ── */
    .sidebar {
        width: 240px;
        min-height: 100vh;
        background: var(--surface);
        border-right: 1px solid var(--border);
        position: fixed;
        top: 0; left: 0;
        display: flex;
        flex-direction: column;
        z-index: 100;
        transition: background 0.3s, border-color 0.3s;
    }

    .sidebar-brand {
        padding: 24px 20px 20px;
        border-bottom: 1px solid var(--border);
    }

    .sidebar-brand h2 {
        font-size: 14px;
        font-weight: bold;
        color: var(--text-strong);
        letter-spacing: 1px;
        text-transform: uppercase;
        margin: 0;
    }

    .sidebar-brand p {
        font-size: 11px;
        color: var(--text-muted);
        margin: 4px 0 0;
    }

    .sidebar-nav { flex: 1; padding: 16px 0; }

    .nav-label {
        font-size: 10px;
        font-weight: bold;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 1.5px;
        padding: 12px 20px 6px;
    }

    .nav-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 11px 20px;
        color: var(--text-sub);
        text-decoration: none;
        font-size: 14px;
        transition: background 0.15s, color 0.15s;
        border-left: 3px solid transparent;
    }

    .nav-item:hover { background: var(--nav-hover); color: var(--text); }

    .nav-item.active {
        background: var(--nav-hover);
        color: #38bdf8;
        border-left-color: #38bdf8;
        font-weight: bold;
    }

    .nav-icon { font-size: 16px; width: 20px; text-align: center; }

    .sidebar-footer {
        padding: 16px 20px;
        border-top: 1px solid var(--border);
    }

    .admin-info { margin-bottom: 10px; }
    .admin-info span { font-size: 11px; color: var(--text-muted); display: block; }
    .admin-info strong { font-size: 13px; color: var(--text); }

    .btn-logout {
        display: block;
        text-align: center;
        padding: 8px;
        border: 1px solid var(--border);
        border-radius: 8px;
        color: #ef4444;
        text-decoration: none;
        font-size: 12px;
        font-weight: bold;
        transition: background 0.2s;
    }
    .btn-logout:hover { background: rgba(239,68,68,0.1); color: #ef4444; }

    /* ── THEME TOGGLE ── */
    .theme-toggle {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
        padding: 8px 12px;
        background: var(--surface2);
        border: 1px solid var(--border);
        border-radius: 8px;
    }

    .theme-toggle-label {
        font-size: 12px;
        color: var(--text-sub);
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .toggle-switch {
        position: relative;
        width: 40px;
        height: 22px;
        cursor: pointer;
    }

    .toggle-switch input { opacity: 0; width: 0; height: 0; }

    .toggle-track {
        position: absolute;
        inset: 0;
        background: #334155;
        border-radius: 999px;
        transition: background 0.3s;
    }

    .toggle-switch input:checked + .toggle-track { background: #0ea5e9; }

    .toggle-thumb {
        position: absolute;
        top: 3px; left: 3px;
        width: 16px; height: 16px;
        background: white;
        border-radius: 50%;
        transition: transform 0.3s;
    }

    .toggle-switch input:checked ~ .toggle-thumb { transform: translateX(18px); }

    /* ── MAIN CONTENT ── */
    .main { margin-left: 240px; padding: 28px; min-height: 100vh; }

    .page-header {
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--border);
    }

    .page-header h1 { font-size: 20px; font-weight: bold; color: var(--text-strong); margin: 0; }
    .page-header p  { font-size: 13px; color: var(--text-muted); margin: 4px 0 0; }

    /* ── STAT CARDS ── */
    .stat-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 28px;
    }

    .stat-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 20px;
        box-shadow: var(--shadow);
        transition: background 0.3s, border-color 0.3s;
    }

    .stat-icon { font-size: 24px; margin-bottom: 10px; }

    .stat-val {
        font-size: 30px;
        font-weight: bold;
        color: var(--text-strong);
        line-height: 1;
        margin-bottom: 4px;
    }

    .stat-lbl {
        font-size: 12px;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    /* ── PANEL ── */
    .panel {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        overflow: hidden;
        margin-bottom: 24px;
        box-shadow: var(--shadow);
        transition: background 0.3s, border-color 0.3s;
    }

    .panel-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
    }

    .panel-title { font-size: 14px; font-weight: bold; color: var(--text-strong); }

    /* ── TABLE ── */
    .tbl { width: 100%; border-collapse: collapse; }

    .tbl thead tr { background: var(--surface2); }

    .tbl th {
        padding: 12px 16px;
        font-size: 11px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--text-muted);
        text-align: left;
        white-space: nowrap;
    }

    .tbl td {
        padding: 12px 16px;
        font-size: 13px;
        color: var(--text-td);
        border-top: 1px solid var(--border);
        vertical-align: middle;
    }

    .tbl tbody tr:hover { background: var(--row-hover); }

    .emp-photo {
        width: 38px; height: 38px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--border);
        object-position: top;
    }

    /* ── BADGES ── */
    .badge-in  { background:rgba(34,197,94,0.15);  color:#22c55e; border:1px solid rgba(34,197,94,0.25);  padding:3px 10px; border-radius:999px; font-size:11px; font-weight:bold; }
    .badge-out { background:rgba(239,68,68,0.15);  color:#ef4444; border:1px solid rgba(239,68,68,0.25);  padding:3px 10px; border-radius:999px; font-size:11px; font-weight:bold; }

    /* ── INPUTS ── */
    .ctrl-input {
        background: var(--input-bg);
        border: 1px solid var(--border);
        border-radius: 8px;
        color: var(--text);
        padding: 7px 12px;
        font-size: 13px;
        outline: none;
        transition: background 0.3s, border-color 0.2s;
    }
    .ctrl-input:focus { border-color: #3b82f6; }
    .ctrl-input::placeholder { color: var(--text-muted); }

    /* ── ALERTS ── */
    .alert-bar {
        padding: 12px 16px;
        border-radius: 10px;
        font-size: 13px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .alert-success { background:rgba(34,197,94,0.1);  border:1px solid rgba(34,197,94,0.25);  color:#22c55e; }
    .alert-info    { background:rgba(56,189,248,0.1);  border:1px solid rgba(56,189,248,0.25);  color:#38bdf8; }
    .alert-warning { background:rgba(234,179,8,0.1);   border:1px solid rgba(234,179,8,0.25);   color:#eab308; }
    .alert-danger  { background:rgba(239,68,68,0.1);   border:1px solid rgba(239,68,68,0.25);   color:#ef4444; }
    .alert-close { cursor:pointer; background:none; border:none; color:inherit; font-size:16px; }

    /* ── ACTIVITY FEED ── */
    .activity-item {
        display:flex; align-items:center; gap:12px;
        padding:11px 0; border-bottom:1px solid var(--border);
    }
    .activity-item:last-child { border-bottom:none; }
    .activity-dot { width:9px;height:9px;border-radius:50%;flex-shrink:0; }
    .dot-in  { background:#22c55e; box-shadow:0 0 6px #22c55e; }
    .dot-out { background:#ef4444; box-shadow:0 0 6px #ef4444; }
    .activity-name { font-size:13px;font-weight:bold;color:var(--text); }
    .activity-time { font-size:11px;color:var(--text-muted);margin-top:2px; }

    /* ── DASHBOARD GRID ── */
    .dash-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; }

    /* ── LIGHT MODE: force text colors on JS-rendered content ── */
    html.light .tbl td,
    html.light .tbl td span,
    html.light .activity-name { color: var(--text-strong); }

    html.light .activity-time,
    html.light .tbl td:not(:first-child) { color: var(--text-td); }

    /* ── LIGHT MODE: Bootstrap modal overrides ── */
    html.light .modal-content { background: var(--surface) !important; color: var(--text) !important; border-color: var(--border) !important; }
    html.light .modal-header,
    html.light .modal-footer  { border-color: var(--border) !important; }
    html.light .form-control,
    html.light .form-select   { background: var(--input-bg) !important; color: var(--text) !important; border-color: var(--border) !important; }
    html.light .form-control::placeholder { color: var(--text-muted) !important; }
    html.light label { color: var(--text-sub) !important; }
    html.light .btn-close-white { filter: invert(1); }

    @media(max-width:900px){
        .stat-grid { grid-template-columns:1fr 1fr; }
        .dash-grid { grid-template-columns:1fr; }
    }

    /* ── EMPLOYEE HOVER POPUP ── */
    .emp-popup {
        position: fixed;
        z-index: 9999;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 20px;
        width: 240px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.4);
        pointer-events: none;
        opacity: 0;
        transform: scale(0.95) translateY(6px);
        transition: opacity 0.18s ease, transform 0.18s ease;
        text-align: center;
    }

    .emp-popup.visible {
        opacity: 1;
        transform: scale(1) translateY(0);
    }

    .emp-popup-photo {
        width: 110px;
        height: 110px;
        border-radius: 12px;
        object-fit: cover;
        object-position: top;
        border: 3px solid var(--border);
        margin: 0 auto 12px;
        display: block;
    }

    .emp-popup-name {
        font-size: 15px;
        font-weight: bold;
        color: var(--text-strong);
        margin-bottom: 4px;
        line-height: 1.3;
    }

    .emp-popup-position {
        font-size: 12px;
        color: var(--text-sub);
        margin-bottom: 10px;
    }

    .emp-popup-divider {
        height: 1px;
        background: var(--border);
        margin: 10px 0;
    }

    .emp-popup-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 11px;
        margin-bottom: 5px;
    }

    .emp-popup-row span:first-child {
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .emp-popup-row span:last-child {
        color: var(--text-strong);
        font-weight: bold;
        text-align: right;
        max-width: 140px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .emp-popup-dept {
        display: inline-block;
        font-size: 11px;
        padding: 3px 10px;
        border-radius: 999px;
        background: rgba(56,189,248,0.12);
        color: #38bdf8;
        border: 1px solid rgba(56,189,248,0.25);
        margin-top: 4px;
    }
ADMINCSS;
include 'includes/header.php';
?>

<body>


<!-- ══════════════ SIDEBAR ══════════════ -->
<div class="sidebar">
    <div class="sidebar-brand">
        <img id="siteLogo" src="irams.png" style="width: 200px; height: auto; margin-bottom: 6px; display: block;">
        <h2 style="font-size:11px;">IRAMS Admin</h2>
        <p>INSPI RFID ATTENDANCE SYSTEM</p>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-label">Main</div>
        <a href="admin.php?page=dashboard"   class="nav-item <?= $page==='dashboard'   ? 'active':'' ?>"><span class="nav-icon">&#9741;</span>  Dashboard</a>
        <a href="admin.php?page=employees"   class="nav-item <?= $page==='employees'   ? 'active':'' ?>"><span class="nav-icon">&#128100;</span> Employees</a>
        <a href="admin.php?page=attendance"  class="nav-item <?= $page==='attendance'  ? 'active':'' ?>"><span class="nav-icon">&#128203;</span> Attendance Log</a>

        <div class="nav-label" style="margin-top:8px;">System</div>
        <a href="index.php" target="_blank"  class="nav-item"><span class="nav-icon">&#127760;</span> Live Dashboard</a>
    </nav>

    <div class="sidebar-footer">
        <div class="admin-info">
            <span>Logged in as</span>
            <strong><?= htmlspecialchars($_SESSION['admin_name'] ?? $_SESSION['admin_user']) ?></strong>
        </div>

        <!-- THEME TOGGLE -->
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

<!-- ══════════════ MAIN ══════════════ -->
<div class="main">

<?php
// ── Alert messages ──
$alerts = [
    'registered' => ['success', '&#10003; Employee registered successfully.'],
    'updated'    => ['info',    '&#10003; Employee updated successfully.'],
    'deleted'    => ['warning', '&#10003; Employee deleted.'],
    'duplicate'  => ['danger',  '&#9888; RFID already registered.'],
];
if(isset($_GET['msg']) && isset($alerts[$_GET['msg']])):
    [$type, $text] = $alerts[$_GET['msg']];
?>
<div class="alert-bar alert-<?= $type ?>">
    <span><?= $text ?></span>
    <button class="alert-close" onclick="this.parentElement.remove()">&#10005;</button>
</div>
<?php endif; ?>

<?php if($page === 'dashboard'): ?>
<!-- ══════════════════════════════════════
     PAGE: DASHBOARD
══════════════════════════════════════ -->
<div class="page-header">
    <h1>&#9741; Dashboard</h1>
    <p>Welcome back, <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?>. Here's today's overview.</p>
</div>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon">&#128100;</div>
        <div class="stat-val"><?= $total_emp ?></div>
        <div class="stat-lbl">Total Employees</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">&#127970;</div>
        <div class="stat-val"><?= $total_dept ?></div>
        <div class="stat-lbl">Departments</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">&#128197;</div>
        <div class="stat-val" style="color:#22c55e;"><?= $today_att ?></div>
        <div class="stat-lbl">Today's Scans</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">&#128203;</div>
        <div class="stat-val"><?= $total_att ?></div>
        <div class="stat-lbl">Total Records</div>
    </div>
</div>

<div class="dash-grid">
    <!-- Recent Activity -->
    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">&#128203; Recent Activity</span>
            <a href="admin.php?page=attendance" style="font-size:12px;color:#38bdf8;text-decoration:none;">View all →</a>
        </div>
        <div style="padding:0 20px;" id="recentFeed"></div>
    </div>

    <!-- Employee List Preview -->
    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">&#128100; Employees</span>
            <a href="admin.php?page=employees" style="font-size:12px;color:#38bdf8;text-decoration:none;">Manage →</a>
        </div>
        <div class="table-responsive">
        <table class="tbl">
            <thead><tr><th>Photo</th><th>Name</th><th>Department</th></tr></thead>
            <tbody>
            <?php
            $r = $conn->query("SELECT users.*, departments.name AS dept FROM users LEFT JOIN departments ON users.department_id=departments.id ORDER BY users.name LIMIT 8");
            while($row = $r->fetch_assoc()):
            ?>
            <tr>
                <td><img src="<?= htmlspecialchars($row['photo']) ?>" class="emp-photo" onerror="this.src='default.png'"></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['dept'] ?? '—') ?></td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<?php elseif($page === 'employees'): ?>
<!-- ══════════════════════════════════════
     PAGE: EMPLOYEES
══════════════════════════════════════ -->
<div class="page-header">
    <h1>&#128100; Employees</h1>
    <p>Manage registered employees and their RFID cards.</p>
</div>

<div class="panel">
    <div class="panel-header">
        <span class="panel-title">Employee List</span>
        <div class="d-flex gap-2 flex-wrap">
            <input type="text" id="searchBox" class="ctrl-input" placeholder="&#128269; Search..." style="min-width:220px;">
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">+ Register Employee</button>
        </div>
    </div>
    <div class="table-responsive">
    <table class="tbl">
        <thead>
        <tr>
            <th>Photo</th><th>Employee ID</th><th>Biometric ID</th>
            <th>First Name</th><th>Middle Name</th><th>Surname</th>
            <th>RFID</th><th>Position</th><th>Department</th><th>Action</th>
        </tr>
        </thead>
        <tbody id="employeeTable">
        <?php
        $res = $conn->query("SELECT users.*, departments.name AS dept_name FROM users LEFT JOIN departments ON users.department_id=departments.id ORDER BY users.surname, users.first_name");
        while($row = $res->fetch_assoc()):
        ?>
        <tr class="emp-row"
            onmouseenter="showPopup(event, <?= json_encode([
                'name'         => $row['name'],
                'first_name'   => $row['first_name'] ?? '',
                'surname'      => $row['surname'] ?? '',
                'position'     => $row['position'] ?? '—',
                'dept_name'    => $row['dept_name'] ?? '—',
                'employee_id'  => $row['employee_id'] ?? '—',
                'biometric_id' => $row['biometric_id'] ?? '—',
                'rfid_uid'     => $row['rfid_uid'] ?? '—',
                'photo'        => $row['photo'] ?? 'default.png',
            ]) ?>)"
            onmouseleave="hidePopup()"
            onmousemove="movePopup(event)">
            <td><img src="<?= htmlspecialchars($row['photo']) ?>" class="emp-photo" onerror="this.src='default.png'"></td>
            <td><?= htmlspecialchars($row['employee_id'] ?? '—') ?></td>
            <td><?= htmlspecialchars($row['biometric_id'] ?? '—') ?></td>
            <td><?= htmlspecialchars($row['first_name'] ?? $row['name']) ?></td>
            <td><?= htmlspecialchars($row['middle_name'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['surname'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['rfid_uid'] ?? '—') ?></td>
            <td><?= htmlspecialchars($row['position'] ?? '—') ?></td>
            <td><?= htmlspecialchars($row['dept_name'] ?? '—') ?></td>
            <td>
                <button class="btn btn-primary btn-sm" style="width:60px" onclick='editUser(<?= json_encode($row) ?>)'>Edit</button>
                <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" style="width:60px" onclick="return confirm('Delete this employee?')">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    </div>
</div>

<?php elseif($page === 'attendance'): ?>
<!-- ══════════════════════════════════════
     PAGE: ATTENDANCE LOG
══════════════════════════════════════ -->
<div class="page-header">
    <h1>&#128203; Attendance Log</h1>
    <p>Full attendance records — filters, search, and live updates.</p>
</div>

<!-- STAT CARDS -->
<div class="stat-grid" style="grid-template-columns:repeat(4,1fr);">
<?php
$s_total = $conn->query("SELECT COUNT(*) c FROM attendance")->fetch_assoc()['c'];
$s_in    = $conn->query("SELECT COUNT(*) c FROM attendance WHERE status='IN'")->fetch_assoc()['c'];
$s_out   = $conn->query("SELECT COUNT(*) c FROM attendance WHERE status='OUT'")->fetch_assoc()['c'];
$s_today = $conn->query("SELECT COUNT(*) c FROM attendance WHERE DATE(time)='$today'")->fetch_assoc()['c'];
?>
    <div class="stat-card"><div class="stat-icon">&#128203;</div><div class="stat-val"><?= $s_total ?></div><div class="stat-lbl">Total Records</div></div>
    <div class="stat-card"><div class="stat-icon">&#128994;</div><div class="stat-val" style="color:#22c55e;"><?= $s_in ?></div><div class="stat-lbl">Total IN</div></div>
    <div class="stat-card"><div class="stat-icon">&#128308;</div><div class="stat-val" style="color:#ef4444;"><?= $s_out ?></div><div class="stat-lbl">Total OUT</div></div>
    <div class="stat-card"><div class="stat-icon">&#128197;</div><div class="stat-val" style="color:#38bdf8;"><?= $s_today ?></div><div class="stat-lbl">Today</div></div>
</div>

<div class="panel">
    <div class="panel-header">
        <span class="panel-title">Records</span>
        <div class="d-flex gap-2 flex-wrap">
            <input type="text"   id="attSearch" class="ctrl-input" placeholder="&#128269; Search name..." style="min-width:200px;">
            <select id="attStatus" class="ctrl-input">
                <option value="">All Status</option>
                <option value="IN">IN</option>
                <option value="OUT">OUT</option>
            </select>
            <input type="date" id="attDate" class="ctrl-input" value="<?= $today ?>">
            <button class="btn btn-outline-secondary btn-sm" onclick="clearAttFilters()">&#10005; Clear</button>
            <button class="btn btn-success btn-sm" onclick="exportAttendance()">&#128190; Export Excel</button>
        </div>
    </div>
    <div class="table-responsive">
    <table class="tbl">
        <thead><tr><th>Employee</th><th>Position</th><th>Department</th><th>Date</th><th>Time</th><th>Status</th></tr></thead>
        <tbody id="attBody"></tbody>
    </table>
    <div style="padding:10px 16px;font-size:12px;color:#475569;border-top:1px solid #0f172a;" id="attCount"></div>
    </div>
</div>
<?php endif; ?>

</div><!-- /main -->

<?php if($page === 'employees'): ?>
<!-- ══ ADD MODAL ══ -->
<div class="modal fade" id="addModal">
<div class="modal-dialog modal-lg">
<div class="modal-content" style="background:#1e293b;border:1px solid #334155;color:#e2e8f0;">
<form method="POST" enctype="multipart/form-data">
<div class="modal-header" style="border-color:#334155;">
    <h5 class="modal-title">Register Employee</h5>
    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<div class="row g-3">
    <div class="col-md-4"><div class="form-floating"><input class="form-control bg-dark text-white border-secondary" name="employee_id" placeholder="Employee ID"><label>Employee ID</label></div></div>
    <div class="col-md-4"><div class="form-floating"><input class="form-control bg-dark text-white border-secondary" name="biometric_id" placeholder="Biometric ID"><label>Biometric ID</label></div></div>
    <div class="col-md-4"><div class="form-floating"><input class="form-control bg-dark text-white border-secondary" name="rfid_uid" placeholder="RFID No." required><label>RFID No. *</label></div></div>
    <div class="col-md-4"><div class="form-floating"><input class="form-control bg-dark text-white border-secondary" name="first_name" placeholder="First Name" required><label>First Name *</label></div></div>
    <div class="col-md-4"><div class="form-floating"><input class="form-control bg-dark text-white border-secondary" name="middle_name" placeholder="Middle Name"><label>Middle Name</label></div></div>
    <div class="col-md-4"><div class="form-floating"><input class="form-control bg-dark text-white border-secondary" name="surname" placeholder="Surname" required><label>Surname *</label></div></div>
    <div class="col-md-6"><div class="form-floating"><input class="form-control bg-dark text-white border-secondary" name="position" placeholder="Position" required><label>Position *</label></div></div>
    <div class="col-md-6"><div class="form-floating">
        <select class="form-select bg-dark text-white border-secondary" name="department_id" required>
            <option value="">Select Department</option>
            <?php $d=$conn->query("SELECT * FROM departments"); while($dr=$d->fetch_assoc()) echo "<option value='{$dr['id']}'>{$dr['name']}</option>"; ?>
        </select><label>Department *</label>
    </div></div>
    <div class="col-12"><label class="form-label fw-bold">Photo *</label><input type="file" class="form-control bg-dark text-white border-secondary" name="photo" accept="image/*" required></div>
</div>
</div>
<div class="modal-footer" style="border-color:#334155;">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
    <button class="btn btn-success" name="save">Register</button>
</div>
</form>
</div></div></div>

<!-- ══ EDIT MODAL ══ -->
<div class="modal fade" id="editModal">
<div class="modal-dialog modal-lg">
<div class="modal-content" style="background:#1e293b;border:1px solid #334155;color:#e2e8f0;">
<form method="POST" enctype="multipart/form-data">
<input type="hidden" name="id" id="edit_id">
<div class="modal-header" style="border-color:#334155;">
    <h5 class="modal-title">Edit Employee</h5>
    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<div class="row g-3">
    <div class="col-md-4"><div class="form-floating"><input class="form-control bg-dark text-white border-secondary" name="employee_id"  id="edit_emp_id"  placeholder="Employee ID"><label>Employee ID</label></div></div>
    <div class="col-md-4"><div class="form-floating"><input class="form-control bg-dark text-white border-secondary" name="biometric_id" id="edit_bio_id"  placeholder="Biometric ID"><label>Biometric ID</label></div></div>
    <div class="col-md-4"><div class="form-floating"><input class="form-control bg-dark text-white border-secondary" name="rfid_uid"     id="edit_rfid"    placeholder="RFID No."><label>RFID No.</label></div></div>
    <div class="col-md-4"><div class="form-floating"><input class="form-control bg-dark text-white border-secondary" name="first_name"  id="edit_first"   placeholder="First Name"><label>First Name</label></div></div>
    <div class="col-md-4"><div class="form-floating"><input class="form-control bg-dark text-white border-secondary" name="middle_name" id="edit_middle"   placeholder="Middle Name"><label>Middle Name</label></div></div>
    <div class="col-md-4"><div class="form-floating"><input class="form-control bg-dark text-white border-secondary" name="surname"     id="edit_surname"  placeholder="Surname"><label>Surname</label></div></div>
    <div class="col-md-6"><div class="form-floating"><input class="form-control bg-dark text-white border-secondary" name="position"    id="edit_position" placeholder="Position"><label>Position</label></div></div>
    <div class="col-md-6"><div class="form-floating">
        <select class="form-select bg-dark text-white border-secondary" name="department_id" id="edit_dept">
            <?php $d=$conn->query("SELECT * FROM departments"); while($dr=$d->fetch_assoc()) echo "<option value='{$dr['id']}'>{$dr['name']}</option>"; ?>
        </select><label>Department</label>
    </div></div>
    <div class="col-12"><label class="form-label fw-bold">Change Photo <small class="text-secondary">(leave blank to keep current)</small></label><input type="file" class="form-control bg-dark text-white border-secondary" name="photo" accept="image/*"></div>
</div>
</div>
<div class="modal-footer" style="border-color:#334155;">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
    <button class="btn btn-primary" name="update">Update</button>
</div>
</form>
</div></div></div>
<?php endif; ?>

<!-- ══ EMPLOYEE HOVER POPUP ══ -->
<div class="emp-popup" id="empPopup">
    <img class="emp-popup-photo" id="popupPhoto" src="default.png" onerror="this.src='default.png'">
    <div class="emp-popup-name"    id="popupName"></div>
    <div class="emp-popup-position" id="popupPosition"></div>
    <div class="emp-popup-dept"    id="popupDept"></div>
    <div class="emp-popup-divider"></div>
    <div class="emp-popup-row"><span>Employee ID</span><span id="popupEmpId"></span></div>
    <div class="emp-popup-row"><span>Biometric ID</span><span id="popupBioId"></span></div>
    <div class="emp-popup-row"><span>RFID</span><span id="popupRfid"></span></div>
</div>

<!-- ══ JS ══ -->
<script>
// ── Edit user ──
function editUser(u){
    document.getElementById('edit_id').value       = u.id            ?? '';
    document.getElementById('edit_emp_id').value   = u.employee_id   ?? '';
    document.getElementById('edit_bio_id').value   = u.biometric_id  ?? '';
    document.getElementById('edit_rfid').value     = u.rfid_uid      ?? '';
    document.getElementById('edit_first').value    = u.first_name    ?? '';
    document.getElementById('edit_middle').value   = u.middle_name   ?? '';
    document.getElementById('edit_surname').value  = u.surname       ?? '';
    document.getElementById('edit_position').value = u.position      ?? '';
    document.getElementById('edit_dept').value     = u.department_id ?? '';
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

// ── Clear add modal on close ──
document.addEventListener('DOMContentLoaded', function(){
    var addModal = document.getElementById('addModal');
    if(addModal) addModal.addEventListener('hidden.bs.modal', function(){ this.querySelector('form').reset(); });
});

// ── Remove ?msg from URL ──
if(window.location.search.includes('msg=')){
    const u = new URL(window.location);
    u.searchParams.delete('msg');
    window.history.replaceState(null,'', u.toString());
}

// ── THEME TOGGLE ──
(function(){
    const html    = document.documentElement;
    const toggle  = document.getElementById('themeToggle');
    const label   = document.getElementById('themeLabel');
    const saved   = localStorage.getItem('rfid_theme') || 'dark';

    function applyTheme(theme){
    const html   = document.documentElement;
    const toggle = document.getElementById("themeToggle");
    const label  = document.getElementById("themeLabel");
    const logo   = document.getElementById("siteLogo");

    if(theme === "light"){
        html.classList.add("light");
        toggle.checked  = true;
        label.innerHTML = "&#9728;&#65039; Light Mode";
        if(logo) logo.src = "irams.png";
    } else {
        html.classList.remove("light");
        toggle.checked  = false;
        label.innerHTML = "&#127769; Dark Mode";
        if(logo) logo.src = "iramswhite.png";
    }
    localStorage.setItem("rfid_theme", theme);
}

    applyTheme(saved);

    toggle.addEventListener('change', function(){
        applyTheme(this.checked ? 'light' : 'dark');
    });
})();

// ── Employee search ──
const searchBox = document.getElementById('searchBox');
if(searchBox){
    searchBox.addEventListener('input', function(){
        const term = this.value.toLowerCase();
        document.querySelectorAll('#employeeTable tr').forEach(row => {
            const text = Array.from(row.querySelectorAll('td')).slice(1).map(td=>td.innerText.toLowerCase()).join(' ');
            row.style.display = text.includes(term) ? '' : 'none';
        });
    });
}

// ── EMPLOYEE HOVER POPUP ──
const popup = document.getElementById('empPopup');
let popupTimeout;

function showPopup(e, emp){
    clearTimeout(popupTimeout);
    document.getElementById('popupPhoto').src       = emp.photo || 'default.png';
    document.getElementById('popupName').innerText      = emp.name || '—';
    document.getElementById('popupPosition').innerText  = emp.position || '—';
    document.getElementById('popupDept').innerText      = emp.dept_name || '—';
    document.getElementById('popupEmpId').innerText     = emp.employee_id || '—';
    document.getElementById('popupBioId').innerText     = emp.biometric_id || '—';
    document.getElementById('popupRfid').innerText      = emp.rfid_uid || '—';
    movePopup(e);
    popup.classList.add('visible');
}

function hidePopup(){
    popupTimeout = setTimeout(() => popup.classList.remove('visible'), 120);
}

function movePopup(e){
    const margin = 18;
    const pw = 240;
    const ph = 310;
    let x = e.clientX + margin;
    let y = e.clientY + margin;
    if(x + pw > window.innerWidth)  x = e.clientX - pw - margin;
    if(y + ph > window.innerHeight) y = e.clientY - ph - margin;
    popup.style.left = x + 'px';
    popup.style.top  = y + 'px';
}

// ── RFID scanner: Enter → next field ──
document.querySelectorAll('input[name="rfid_uid"]').forEach(function(rfidInput){
    rfidInput.addEventListener('keydown', function(e){
        if(e.key === 'Enter'){
            e.preventDefault();
            const modal  = this.closest('.modal-content');
            const fields = Array.from(modal.querySelectorAll('input, select, textarea'));
            const index  = fields.indexOf(this);
            if(index !== -1 && fields[index+1]) fields[index+1].focus();
        }
    });
});

// ── Dashboard: Recent activity feed (admin actions) ──
const actionStyles = {
    added:   { icon: '&#10133;', color: '#22c55e', bg: 'rgba(34,197,94,0.12)',  border: 'rgba(34,197,94,0.25)'  },
    edited:  { icon: '&#9998;',  color: '#38bdf8', bg: 'rgba(56,189,248,0.12)', border: 'rgba(56,189,248,0.25)' },
    deleted: { icon: '&#128465;',color: '#ef4444', bg: 'rgba(239,68,68,0.12)',  border: 'rgba(239,68,68,0.25)'  },
};

function loadRecentFeed(){
    const feed = document.getElementById('recentFeed');
    if(!feed) return;
    fetch('fetch_activity.php', {cache:'no-store'})
    .then(r => {
        if(!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
    })
    .then(rows=>{
        if(!rows || rows.length === 0){
            feed.innerHTML = `<div style="padding:32px 0;text-align:center;color:var(--text-muted);font-size:13px;">
                &#128203; No admin actions yet.
            </div>`;
            return;
        }
        let html='';
        rows.forEach(r=>{
            const s    = actionStyles[r.action] || actionStyles.edited;
            const dt   = new Date(r.created_at.replace(' ','T'));
            const time = isNaN(dt) ? r.created_at : dt.toLocaleString('en-PH',{month:'short',day:'numeric',hour:'2-digit',minute:'2-digit'});
            const verb = r.action.charAt(0).toUpperCase() + r.action.slice(1);
            html += `
            <div class="activity-item">
                <div style="width:34px;height:34px;border-radius:50%;background:${s.bg};border:1px solid ${s.border};
                     display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;">
                    ${s.icon}
                </div>
                <div style="flex:1;min-width:0;">
                    <div class="activity-name"><span style="color:${s.color};font-weight:bold;">${verb}</span>
                        <span style="color:var(--text);font-weight:normal;"> ${r.emp_name}</span>
                    </div>
                    <div class="activity-time">by ${r.admin_name} &bull; ${time}</div>
                </div>
            </div>`;
        });
        feed.innerHTML = html;
    })
    .catch(err=>{
        feed.innerHTML = '<div style="padding:24px 0;text-align:center;color:#ef4444;font-size:12px;">Could not load activity log.</div>';
        console.error('loadRecentFeed error:', err);
    });
}
loadRecentFeed();
setInterval(loadRecentFeed, 5000);

// ── Attendance log filter ──
let allAtt = [];

function loadAttendance(){
    fetch('fetch_all.php')
    .then(r=>r.json())
    .then(rows=>{ allAtt=rows; renderAtt(); });
}

function renderAtt(){
    const search = (document.getElementById('attSearch')?.value||'').toLowerCase();
    const status = document.getElementById('attStatus')?.value||'';
    const date   = document.getElementById('attDate')?.value||'';

    const filtered = allAtt.filter(r=>{
        const ms = !search || (r.name||'').toLowerCase().includes(search) || (r.position||'').toLowerCase().includes(search) || (r.department||'').toLowerCase().includes(search);
        const mv = !status || r.status===status;
        const md = !date   || r.time.startsWith(date);
        return ms && mv && md;
    });

    const body  = document.getElementById('attBody');
    const count = document.getElementById('attCount');
    if(!body) return;

    if(filtered.length===0){ body.innerHTML='<tr><td colspan="6" style="text-align:center;padding:32px;color:#475569;">No records found.</td></tr>'; count.innerText=''; return; }

    count.innerText = `Showing ${filtered.length} of ${allAtt.length} records`;
    let html='';
    filtered.forEach(r=>{
        const isIn = r.status==='IN';
        const dt   = new Date(r.time);
        const ds   = dt.toLocaleDateString('en-PH',{year:'numeric',month:'short',day:'numeric'});
        const ts   = dt.toLocaleTimeString('en-PH',{hour:'2-digit',minute:'2-digit',second:'2-digit'});
        html+=`<tr>
            <td><div style="display:flex;align-items:center;gap:10px;">
                <img src="${r.photo||'default.png'}" class="emp-photo" onerror="this.src='default.png'">
                <span style="font-weight:bold;color:var(--text-strong);">${r.name||'—'}</span>
            </div></td>
            <td>${r.position||'—'}</td>
            <td>${r.department||'—'}</td>
            <td>${ds}</td><td>${ts}</td>
            <td><span class="${isIn?'badge-in':'badge-out'}">${r.status}</span></td>
        </tr>`;
    });
    body.innerHTML=html;
}

function clearAttFilters(){
    const s=document.getElementById('attSearch'); if(s) s.value='';
    const v=document.getElementById('attStatus'); if(v) v.value='';
    const d=document.getElementById('attDate');   if(d) d.value='';
    renderAtt();
}

function exportAttendance(){
    const date   = document.getElementById('attDate')?.value   || '';
    const status = document.getElementById('attStatus')?.value || '';
    const search = document.getElementById('attSearch')?.value || '';

    if(!date){
        alert('Please select a date to export.');
        return;
    }

    // Build export URL with current filters
    const params = new URLSearchParams({ date, status, search });
    window.open('export_attendance.php?' + params.toString(), '_blank');
}

['attSearch','attStatus','attDate'].forEach(id=>{
    const el=document.getElementById(id);
    if(el) el.addEventListener('input', renderAtt);
});

loadAttendance();
setInterval(loadAttendance, 5000);
</script>

<?php include 'includes/footer.php'; ?>