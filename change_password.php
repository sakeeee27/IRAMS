<?php
require_once 'includes/auth.php';
require_once 'db.php';
require_once 'includes/functions.php';

require_admin();

$success = '';
$error   = '';

if(isset($_POST['change_password'])) {
    require_csrf();

    $current  = $_POST['current_password']  ?? '';
    $new_pw   = $_POST['new_password']       ?? '';
    $confirm  = $_POST['confirm_password']   ?? '';
    $admin_id = (int)$_SESSION['admin_id'];

    if(empty($current) || empty($new_pw) || empty($confirm)) {
        $error = 'All fields are required.';
    } elseif(strlen($new_pw) < 6) {
        $error = 'New password must be at least 6 characters.';
    } elseif($new_pw !== $confirm) {
        $error = 'New passwords do not match.';
    } else {
        // Fetch current hash — prepared statement
        $stmt = $conn->prepare("SELECT password FROM admin_users WHERE id = ?");
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $stmt->bind_result($hashed);
        $stmt->fetch();
        $stmt->close();

        if(!$hashed || !password_verify($current, $hashed)) {
            $error = 'Current password is incorrect.';
        } else {
            $new_hash = password_hash($new_pw, PASSWORD_DEFAULT);
            $upd = $conn->prepare("UPDATE admin_users SET password = ? WHERE id = ?");
            $upd->bind_param("si", $new_hash, $admin_id);
            $upd->execute();
            $upd->close();

            log_activity($conn, 'edited', '[Password Changed]', admin_name());

            $success = 'Password changed successfully.';
        }
    }
}

$page_title = "Change Password";
$page_type  = "admin";
$extra_css  = <<<'ADMINCSS'
    :root {
        --bg:#0f172a; --surface:#1e293b; --surface2:#0f172a; --border:#334155;
        --text:#e2e8f0; --text-muted:#64748b; --text-sub:#94a3b8;
        --text-strong:#f1f5f9; --nav-hover:#0f172a; --input-bg:#0f172a;
        --shadow:0 4px 24px rgba(0,0,0,0.4);
    }
    html.light {
        --bg:#f1f5f9; --surface:#ffffff; --surface2:#f8fafc; --border:#e2e8f0;
        --text:#1e293b; --text-muted:#94a3b8; --text-sub:#64748b;
        --text-strong:#0f172a; --nav-hover:#f1f5f9; --input-bg:#f8fafc;
        --shadow:0 4px 24px rgba(0,0,0,0.08);
    }
    *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
    body { background:var(--bg); color:var(--text); font-family:Arial,sans-serif; }

    /* Sidebar */
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
    .theme-toggle-label { font-size:12px; color:var(--text-sub); }
    .toggle-switch { position:relative; width:40px; height:22px; cursor:pointer; }
    .toggle-switch input { opacity:0; width:0; height:0; }
    .toggle-track { position:absolute; inset:0; background:#334155; border-radius:999px; transition:background 0.3s; }
    .toggle-switch input:checked + .toggle-track { background:#0ea5e9; }
    .toggle-thumb { position:absolute; top:3px; left:3px; width:16px; height:16px; background:white; border-radius:50%; transition:transform 0.3s; }
    .toggle-switch input:checked ~ .toggle-thumb { transform:translateX(18px); }

    /* Main */
    .main { margin-left:240px; padding:28px; min-height:100vh; display:flex; align-items:flex-start; justify-content:center; }
    .pw-card { background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:36px; width:100%; max-width:480px; box-shadow:var(--shadow); margin-top:20px; }
    .pw-card-title { font-size:18px; font-weight:bold; color:var(--text-strong); margin-bottom:4px; }
    .pw-card-sub { font-size:13px; color:var(--text-muted); margin-bottom:28px; }

    .form-group { margin-bottom:20px; }
    .form-label { display:block; font-size:11px; font-weight:bold; color:var(--text-sub); text-transform:uppercase; letter-spacing:1px; margin-bottom:8px; }
    .form-input { width:100%; padding:11px 14px; background:var(--input-bg); border:1px solid var(--border); border-radius:8px; color:var(--text); font-size:14px; outline:none; transition:border-color 0.2s,background 0.3s; }
    .form-input:focus { border-color:#3b82f6; }
    .form-input::placeholder { color:var(--text-muted); }

    .pw-input-wrap { position:relative; }
    .pw-toggle-btn { position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--text-muted); cursor:pointer; font-size:16px; padding:4px; }
    .pw-toggle-btn:hover { color:var(--text); }

    .strength-wrap { height:4px; background:var(--border); border-radius:2px; margin-top:8px; overflow:hidden; }
    .strength-bar { height:100%; width:0%; border-radius:2px; transition:width 0.3s,background 0.3s; }
    .strength-label { font-size:11px; margin-top:4px; color:var(--text-muted); min-height:16px; }

    .match-hint { font-size:11px; margin-top:5px; min-height:16px; }

    .btn-submit { width:100%; padding:13px; background:linear-gradient(135deg,#1e40af,#0ea5e9); border:none; border-radius:8px; color:white; font-size:15px; font-weight:bold; cursor:pointer; transition:opacity 0.2s; }
    .btn-submit:hover { opacity:0.9; }

    .alert { padding:12px 16px; border-radius:10px; font-size:13px; margin-bottom:20px; }
    .alert-success { background:rgba(34,197,94,0.1);  border:1px solid rgba(34,197,94,0.25);  color:#22c55e; }
    .alert-danger  { background:rgba(239,68,68,0.1);  border:1px solid rgba(239,68,68,0.25);  color:#ef4444; }

    .divider { height:1px; background:var(--border); margin:24px 0; }
    .rules-list { font-size:12px; color:var(--text-muted); padding-left:18px; line-height:1.9; }
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
        <a href="summary_report.php"        class="nav-item"><span class="nav-icon">&#128196;</span> Summary Report</a>
        <div class="nav-label" style="margin-top:8px;">System</div>
        <a href="index.php" target="_blank"     class="nav-item"><span class="nav-icon">&#127760;</span> Live Dashboard</a>
        <a href="change_password.php"           class="nav-item active"><span class="nav-icon">&#128274;</span> Change Password</a>
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
<div class="pw-card">

    <div class="pw-card-title">&#128274; Change Password</div>
    <div class="pw-card-sub">Update the password for <strong><?= htmlspecialchars($_SESSION['admin_name'] ?? $_SESSION['admin_user']) ?></strong></div>

    <?php if($success): ?>
    <div class="alert alert-success">&#10003; <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if($error): ?>
    <div class="alert alert-danger">&#9888; <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" id="pwForm" autocomplete="off">
        <?= csrf_field() ?>

        <div class="form-group">
            <label class="form-label">Current Password</label>
            <div class="pw-input-wrap">
                <input type="password" name="current_password" id="current_password" class="form-input" placeholder="Enter your current password" required>
                <button type="button" class="pw-toggle-btn" onclick="toggleVis('current_password', this)">&#128065;</button>
            </div>
        </div>

        <div class="divider"></div>

        <div class="form-group">
            <label class="form-label">New Password</label>
            <div class="pw-input-wrap">
                <input type="password" name="new_password" id="new_password" class="form-input" placeholder="Min. 6 characters" required>
                <button type="button" class="pw-toggle-btn" onclick="toggleVis('new_password', this)">&#128065;</button>
            </div>
            <div class="strength-wrap"><div class="strength-bar" id="strengthBar"></div></div>
            <div class="strength-label" id="strengthLabel"></div>
        </div>

        <div class="form-group">
            <label class="form-label">Confirm New Password</label>
            <div class="pw-input-wrap">
                <input type="password" name="confirm_password" id="confirm_password" class="form-input" placeholder="Re-enter new password" required>
                <button type="button" class="pw-toggle-btn" onclick="toggleVis('confirm_password', this)">&#128065;</button>
            </div>
            <div class="match-hint" id="matchHint"></div>
        </div>

        <button type="submit" name="change_password" class="btn-submit" id="submitBtn">&#128274; Update Password</button>
    </form>

    <div class="divider"></div>
    <div style="font-size:12px;color:var(--text-muted);">Password requirements:</div>
    <ul class="rules-list">
        <li>At least 6 characters long</li>
        <li>Use a mix of uppercase, lowercase, numbers, and symbols for a stronger password</li>
        <li>Do not reuse old or easily guessable passwords</li>
    </ul>

</div>
</div>

<script>
// ── Show/hide password ──
function toggleVis(id, btn) {
    const input = document.getElementById(id);
    if(input.type === 'password') {
        input.type = 'text';
        btn.innerHTML = '&#128683;';
    } else {
        input.type = 'password';
        btn.innerHTML = '&#128065;';
    }
}

// ── Password strength ──
document.getElementById('new_password').addEventListener('input', function(){
    const val = this.value;
    let score = 0;
    if(val.length >= 6)           score++;
    if(val.length >= 10)          score++;
    if(/[A-Z]/.test(val))         score++;
    if(/[0-9]/.test(val))         score++;
    if(/[^A-Za-z0-9]/.test(val))  score++;

    const levels = [
        {w:'0%',   bg:'#ef4444', text:''},
        {w:'25%',  bg:'#ef4444', text:'Weak'},
        {w:'50%',  bg:'#f97316', text:'Fair'},
        {w:'75%',  bg:'#eab308', text:'Good'},
        {w:'90%',  bg:'#22c55e', text:'Strong'},
        {w:'100%', bg:'#22c55e', text:'Very Strong'},
    ];
    const l = levels[Math.min(score, 5)];
    document.getElementById('strengthBar').style.width      = l.w;
    document.getElementById('strengthBar').style.background = l.bg;
    const lbl = document.getElementById('strengthLabel');
    lbl.style.color    = l.bg;
    lbl.innerText      = l.text;
    checkMatch();
});

document.getElementById('confirm_password').addEventListener('input', checkMatch);

function checkMatch(){
    const pw   = document.getElementById('new_password').value;
    const conf = document.getElementById('confirm_password').value;
    const hint = document.getElementById('matchHint');
    const btn  = document.getElementById('submitBtn');
    if(!conf){ hint.innerText = ''; btn.disabled = false; return; }
    if(pw === conf){
        hint.style.color  = '#22c55e';
        hint.innerText    = '✓ Passwords match';
        btn.disabled      = false;
    } else {
        hint.style.color  = '#ef4444';
        hint.innerText    = '✗ Passwords do not match';
        btn.disabled      = true;
    }
}

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
</script>

<?php include 'includes/footer.php'; ?>
