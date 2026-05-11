<?php
$page_title = "Admin Login";
$page_type  = "auth";
$extra_css  = <<<'PAGECSS'
/* ══ DARK (default) ══ */
        :root {
            --bg:       #0f172a;
            --surface:  #1e293b;
            --border:   #334155;
            --input-bg: #0f172a;
            --text:     #f1f5f9;
            --text-sub: #94a3b8;
            --text-muted: #64748b;
            --divider:  #334155;
            --btn-signup-border: #334155;
            --btn-signup-color:  #94a3b8;
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
            --divider:  #e2e8f0;
            --btn-signup-border: #cbd5e1;
            --btn-signup-color:  #475569;
        }

        *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }

        body {
            font-family: Arial, sans-serif;
            background: var(--bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
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
        .login-wrap {
            width: 100%;
            max-width: 400px;
            padding: 24px;
        }

        .login-title {
            text-align: center;
            margin-bottom: 28px;
        }

        .login-title img {
            width: 180px;
            margin-bottom: 16px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .login-title h1 {
            font-size: 20px;
            font-weight: bold;
            color: var(--text);
            margin-bottom: 4px;
            transition: color 0.3s;
        }

        .login-title p {
            font-size: 12px;
            color: var(--text-muted);
        }

        .login-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 32px;
            transition: background 0.3s, border-color 0.3s;
            box-shadow: 0 8px 32px rgba(0,0,0,0.15);
        }

        .form-group { margin-bottom: 20px; }

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
        input[type="text"]:focus,
        input[type="password"]:focus { border-color: #3b82f6; }
        input::placeholder { color: var(--text-muted); }

        .btn-login {
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
        .btn-login:hover { opacity: 0.9; }

        .btn-signup {
            width: 100%;
            padding: 12px;
            background: transparent;
            border: 1px solid var(--btn-signup-border);
            border-radius: 8px;
            color: var(--btn-signup-color);
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
            text-align: center;
            text-decoration: none;
            display: block;
            transition: border-color 0.2s, color 0.2s;
        }
        .btn-signup:hover { border-color: #0ea5e9; color: #38bdf8; }

        .divider {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 20px 0 0;
            color: var(--text-muted);
            font-size: 12px;
        }
        .divider::before,
        .divider::after { content:''; flex:1; height:1px; background:var(--divider); }

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

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: #38bdf8;
            text-decoration: none;
        }
        .back-link:hover { text-decoration: underline; }
PAGECSS;
include 'includes/header.php';
?>

<!-- Theme toggle rendered by includes/header.php -->
        <div class="toggle-thumb"></div>
    </label>
    <span class="theme-btn-label" id="themeLabel">&#127769; Dark</span>
</div>

<div class="login-wrap">

    <div class="login-title">
        <img id="siteLogo" src="iramswhite.png" style="width: 350px; height: auto; padding-right: 15px; display: flex;" alt="IRAMS Logo">
        <h1>Admin Login</h1>
        <p>Inspi RFID Attendance Monitoring System</p>
    </div>

    <div class="login-card">

        <?php if($error): ?>
        <div class="error-box">&#9888; <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                       placeholder="Enter username" autocomplete="off" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password"
                       placeholder="Enter password" required>
            </div>
            <button type="submit" name="login" class="btn-login">Login</button>
        </form>

        <div class="divider">or</div>
        <a href="signup.php" class="btn-signup">&#43; Create Admin Account</a>

    </div>

    <a href="index.php" class="back-link">← Back to Dashboard</a>

</div>

<script>
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
</script>

<?php include 'includes/footer.php'; ?>