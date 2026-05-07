<?php
session_start();
if(isset($_SESSION['admin_id'])){ header("Location: admin.php"); exit; }
include 'db.php';

$error = ''; $success = '';

if(isset($_POST['signup'])){
    $full_name = trim($_POST['full_name']);
    $username  = trim($_POST['username']);
    $password  = $_POST['password'];
    $confirm   = $_POST['confirm_password'];

    if(empty($full_name) || empty($username) || empty($password) || empty($confirm)){
        $error = "All fields are required.";
    } elseif(strlen($username) < 4){
        $error = "Username must be at least 4 characters.";
    } elseif(strlen($password) < 6){
        $error = "Password must be at least 6 characters.";
    } elseif($password !== $confirm){
        $error = "Passwords do not match.";
    } else {
        $u     = $conn->real_escape_string($username);
        $check = $conn->query("SELECT id FROM admin_users WHERE username='$u' LIMIT 1");
        if($check && $check->num_rows > 0){
            $error = "Username '$username' is already taken.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $fn = $conn->real_escape_string($full_name);
            $h  = $conn->real_escape_string($hashed);
            $conn->query("INSERT INTO admin_users (username, password, full_name) VALUES ('$u','$h','$fn')");
            $success = "Account created! You can now log in.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>IRAMS — Sign Up</title>
    <link rel="icon" type="image/png" href="irams.png">
    <style>
        /* ══ DARK (default) ══ */
        :root {
            --bg:       #0f172a;
            --surface:  #1e293b;
            --border:   #334155;
            --input-bg: #0f172a;
            --text:     #f1f5f9;
            --text-sub: #94a3b8;
            --text-muted: #64748b;
            --hint:     #475569;
            --strength-bg: #334155;
        }
        /* ══ LIGHT ══ */
        html.light {
            --bg:       #f1f5f9;
            --surface:  #ffffff;
            --border:   #e2e8f0;
            --input-bg: #f8fafc;
            --text:     #0f172a;
            --text-sub: #475569;
            --text-muted: #94a3b8;
            --hint:     #94a3b8;
            --strength-bg: #e2e8f0;
        }

        *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }

        body {
            font-family: Arial, sans-serif;
            background: var(--bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            transition: background 0.3s;
        }

        /* ── THEME TOGGLE ── */
        .theme-btn {
            position: fixed;
            top: 16px; right: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: 6px 14px 6px 10px;
            cursor: pointer;
            transition: background 0.3s, border-color 0.3s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        .theme-btn:hover { border-color: #38bdf8; }
        .theme-btn-label { font-size:12px; color:var(--text-sub); white-space:nowrap; }

        .toggle-switch { position:relative; width:36px; height:20px; flex-shrink:0; }
        .toggle-switch input { opacity:0; width:0; height:0; position:absolute; }
        .toggle-track { position:absolute; inset:0; background:#334155; border-radius:999px; transition:background 0.3s; }
        html.light .toggle-track { background:#0ea5e9; }
        .toggle-thumb { position:absolute; top:2px; left:2px; width:16px; height:16px; background:white; border-radius:50%; transition:transform 0.3s; pointer-events:none; }
        html.light .toggle-thumb { transform:translateX(16px); }

        /* ── CARD ── */
        .wrap { width:100%; max-width:420px; }

        .page-title { text-align:center; margin-bottom:28px; }

        .page-title img {
            width: 180px;
            margin: 0 auto 16px;
            display: block;
        }

        .page-title h1 {
            font-size: 20px;
            font-weight: bold;
            color: var(--text);
            margin-bottom: 4px;
            transition: color 0.3s;
        }

        .page-title p { font-size:12px; color:var(--text-muted); }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 32px;
            transition: background 0.3s, border-color 0.3s;
            box-shadow: 0 8px 32px rgba(0,0,0,0.15);
        }

        .form-group { margin-bottom: 18px; }

        label {
            display: block;
            font-size: 11px;
            font-weight: bold;
            color: var(--text-sub);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
            transition: color 0.3s;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 14px;
            background: var(--input-bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text);
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s, background 0.3s, color 0.3s;
        }
        input:focus { border-color: #3b82f6; }
        input::placeholder { color: var(--text-muted); }

        .hint { font-size:11px; color:var(--hint); margin-top:5px; transition:color 0.3s; }

        .strength-bar-wrap {
            height: 4px;
            background: var(--strength-bg);
            border-radius: 2px;
            margin-top: 8px;
            overflow: hidden;
            transition: background 0.3s;
        }
        .strength-bar { height:100%; width:0%; border-radius:2px; transition:width 0.3s, background 0.3s; }
        .strength-label { font-size:11px; margin-top:4px; color:var(--hint); }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #1e40af, #0ea5e9);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 8px;
            transition: opacity 0.2s;
        }
        .btn-submit:hover { opacity: 0.9; }

        .error-box {
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.3);
            color: #ef4444;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13px;
            margin-bottom: 20px;
            text-align: center;
        }

        .success-box {
            background: rgba(34,197,94,0.1);
            border: 1px solid rgba(34,197,94,0.3);
            color: #22c55e;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13px;
            margin-bottom: 20px;
            text-align: center;
        }

        .bottom-links {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: var(--text-muted);
        }
        .bottom-links a { color:#38bdf8; text-decoration:none; margin:0 8px; }
        .bottom-links a:hover { text-decoration:underline; }
    </style>
</head>
<body>

<!-- THEME TOGGLE -->
<div class="theme-btn" onclick="toggleTheme()">
    <label class="toggle-switch">
        <input type="checkbox" id="themeToggle">
        <div class="toggle-track"></div>
        <div class="toggle-thumb"></div>
    </label>
    <span class="theme-btn-label" id="themeLabel">&#127769; Dark</span>
</div>

<div class="wrap">

    <div class="page-title">
        <img id="siteLogo" src="iramswhite.png" style="width: 350px; height: auto; display: block; margin: 0 auto; padding-right: 15px;" alt="IRAMS Logo">
        <h1>Create Admin Account</h1>
        <p>Inspi RFID Attendance Monitoring System</p>
    </div>

    <div class="card">

        <?php if($error): ?>
        <div class="error-box">&#9888; <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if($success): ?>
        <div class="success-box">&#10003; <?= htmlspecialchars($success) ?>
            <br><a href="login.php" style="color:#22c55e;font-weight:bold;">Click here to login</a>
        </div>
        <?php endif; ?>

        <?php if(!$success): ?>
        <form method="POST" id="signupForm">

            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name"
                       value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
                       placeholder="e.g. Juan dela Cruz" required>
            </div>

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" id="username"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                       placeholder="min. 4 characters" autocomplete="off" required>
                <div class="hint">Letters, numbers, and underscores only.</div>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" id="password"
                       placeholder="min. 6 characters" required>
                <div class="strength-bar-wrap">
                    <div class="strength-bar" id="strengthBar"></div>
                </div>
                <div class="strength-label" id="strengthLabel"></div>
            </div>

            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm"
                       placeholder="Re-enter password" required>
                <div class="hint" id="matchHint"></div>
            </div>

            <button type="submit" name="signup" class="btn-submit">&#43; Create Account</button>

        </form>
        <?php endif; ?>

    </div>

    <div class="bottom-links">
        Already have an account? <a href="login.php">Login</a>
        &nbsp;|&nbsp;
        <a href="index.php">← Dashboard</a>
    </div>

</div>

<script>
// ── THEME ──
function applyTheme(theme){
    const html   = document.documentElement;
    const toggle = document.getElementById('themeToggle');
    const label  = document.getElementById('themeLabel');
    const logo   = document.getElementById('siteLogo');

    if(theme === 'light'){
        html.classList.add('light');
        toggle.checked  = true;
        label.innerHTML = '&#9728;&#65039; Light';
        if(logo) logo.src = 'irams.png';
    } else {
        html.classList.remove('light');
        toggle.checked  = false;
        label.innerHTML = '&#127769; Dark';
        if(logo) logo.src = 'iramswhite.png';
    }
    localStorage.setItem('rfid_theme', theme);
}

function toggleTheme(){
    applyTheme(localStorage.getItem('rfid_theme') === 'light' ? 'dark' : 'light');
}

applyTheme(localStorage.getItem('rfid_theme') || 'dark');

// ── PASSWORD STRENGTH ──
document.getElementById('password').addEventListener('input', function(){
    const val = this.value;
    let score = 0;
    if(val.length >= 6)  score++;
    if(val.length >= 10) score++;
    if(/[A-Z]/.test(val)) score++;
    if(/[0-9]/.test(val)) score++;
    if(/[^A-Za-z0-9]/.test(val)) score++;

    const levels = [
        {w:'0%',   bg:'#ef4444', text:''},
        {w:'25%',  bg:'#ef4444', text:'Weak'},
        {w:'50%',  bg:'#f97316', text:'Fair'},
        {w:'75%',  bg:'#eab308', text:'Good'},
        {w:'90%',  bg:'#22c55e', text:'Strong'},
        {w:'100%', bg:'#22c55e', text:'Very Strong'},
    ];

    const l = levels[Math.min(score, 5)];
    const bar   = document.getElementById('strengthBar');
    const label = document.getElementById('strengthLabel');
    bar.style.width      = l.w;
    bar.style.background = l.bg;
    label.style.color    = l.bg;
    label.innerText      = l.text;
    checkMatch();
});

document.getElementById('confirm').addEventListener('input', checkMatch);

function checkMatch(){
    const pw   = document.getElementById('password').value;
    const conf = document.getElementById('confirm').value;
    const hint = document.getElementById('matchHint');
    if(!conf){ hint.innerText = ''; return; }
    if(pw === conf){ hint.style.color = '#22c55e'; hint.innerText = '✓ Passwords match'; }
    else           { hint.style.color = '#ef4444'; hint.innerText = '✗ Passwords do not match'; }
}
</script>

</body>
</html>