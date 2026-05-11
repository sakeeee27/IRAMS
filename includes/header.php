<?php
/**
 * includes/header.php
 * Shared HTML <head> section for all pages.
 *
 * Usage — at the top of any page:
 *
 *   <?php
 *   $page_title  = "Admin Panel";        // Tab title (required)
 *   $page_type   = "admin";              // "admin" | "public" | "auth"
 *   $extra_css   = "";                   // Optional extra inline CSS
 *   include 'includes/header.php';
 *   ?>
 *
 * $page_type controls which CSS variables and Bootstrap are loaded:
 *   "admin"  → sidebar admin layout (loads Bootstrap)
 *   "public" → index.php / logs.php dark/light card layout
 *   "auth"   → login.php / signup.php centered card
 */

$page_title = $page_title ?? 'IRAMS';
$page_type  = $page_type  ?? 'public';
$extra_css  = $extra_css  ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IRAMS — <?= htmlspecialchars($page_title) ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= $page_type === 'admin' ? '' : '' ?>irams.png">

    <?php if($page_type === 'admin'): ?>
    <!-- Bootstrap (admin pages only) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
          rel="stylesheet"
          onerror="this.href='bootstrap/bootstrap.min.css'">
    <?php endif; ?>

    <style>
        /* ══════════════════════════════════════
           SHARED CSS VARIABLES — DARK / LIGHT
           Used across admin, public, and auth pages
        ══════════════════════════════════════ */
        :root {
            /* Backgrounds */
            --bg:           #0f172a;
            --surface:      #1e293b;
            --surface2:     #0f172a;

            /* Borders */
            --border:       #334155;

            /* Text */
            --text:         #e2e8f0;
            --text-strong:  #f1f5f9;
            --text-sub:     #94a3b8;
            --text-muted:   #64748b;
            --text-td:      #cbd5e1;

            /* Inputs */
            --input-bg:     #0f172a;

            /* Misc */
            --nav-hover:    #0f172a;
            --row-hover:    rgba(255,255,255,0.02);
            --shadow:       0 4px 24px rgba(0,0,0,0.4);
            --shadow-lg:    0 25px 60px rgba(0,0,0,0.5);

            /* Auth pages */
            --divider:            #334155;
            --btn-signup-border:  #334155;
            --btn-signup-color:   #94a3b8;

            /* Index / card */
            --card-bg:            linear-gradient(160deg, #1e293b 0%, #0f172a 100%);
            --card-foot-border:   #1e293b;
            --card-photo-border:  #0f172a;
            --scan-line:          #334155;
            --activity-sep:       #0f172a;
            --clock-bg:           #1e293b;
            --clock-color:        #64748b;
            --title-color:        #d5d9df;

            /* Strength meter */
            --strength-bg:  #334155;
            --hint:         #475569;
        }

        /* ══ LIGHT MODE OVERRIDES ══ */
        html.light {
            --bg:           #f1f5f9;
            --surface:      #ffffff;
            --surface2:     #f8fafc;
            --border:       #e2e8f0;
            --text:         #1e293b;
            --text-strong:  #0f172a;
            --text-sub:     #475569;
            --text-muted:   #94a3b8;
            --text-td:      #334155;
            --input-bg:     #f8fafc;
            --nav-hover:    #f1f5f9;
            --row-hover:    rgba(0,0,0,0.02);
            --shadow:       0 4px 24px rgba(0,0,0,0.08);
            --shadow-lg:    0 25px 60px rgba(0,0,0,0.12);
            --divider:            #e2e8f0;
            --btn-signup-border:  #cbd5e1;
            --btn-signup-color:   #475569;
            --card-bg:            linear-gradient(160deg, #ffffff 0%, #f1f5f9 100%);
            --card-foot-border:   #e2e8f0;
            --card-photo-border:  #f1f5f9;
            --scan-line:          #cbd5e1;
            --activity-sep:       #f1f5f9;
            --clock-bg:           #ffffff;
            --clock-color:        #475569;
            --title-color:        #1e293b;
            --strength-bg:  #e2e8f0;
            --hint:         #94a3b8;
        }

        /* ══════════════════════════════════════
           GLOBAL RESET & BASE
        ══════════════════════════════════════ */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            transition: background 0.3s, color 0.3s;
        }

        /* ══════════════════════════════════════
           SHARED THEME TOGGLE BUTTON
           Works on all pages
        ══════════════════════════════════════ */
        .theme-btn {
            position: fixed;
            top: 16px;
            right: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: 6px 14px 6px 10px;
            cursor: pointer;
            z-index: 9999;
            transition: background 0.3s, border-color 0.3s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        .theme-btn:hover { border-color: #38bdf8; }
        .theme-btn-label {
            font-size: 12px;
            color: var(--text-sub);
            white-space: nowrap;
        }

        /* Toggle pill */
        .toggle-switch {
            position: relative;
            width: 36px;
            height: 20px;
            flex-shrink: 0;
        }
        .toggle-switch input { opacity: 0; width: 0; height: 0; position: absolute; }

        .toggle-track {
            position: absolute;
            inset: 0;
            background: #334155;
            border-radius: 999px;
            transition: background 0.3s;
        }
        html.light .toggle-track { background: #0ea5e9; }

        .toggle-thumb {
            position: absolute;
            top: 2px; left: 2px;
            width: 16px; height: 16px;
            background: white;
            border-radius: 50%;
            transition: transform 0.3s;
            pointer-events: none;
        }
        html.light .toggle-thumb { transform: translateX(16px); }

        /* ══════════════════════════════════════
           SHARED BADGES
        ══════════════════════════════════════ */
        .badge-in {
            background: rgba(34,197,94,0.15);
            color: #22c55e;
            border: 1px solid rgba(34,197,94,0.25);
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: bold;
        }
        .badge-out {
            background: rgba(239,68,68,0.15);
            color: #ef4444;
            border: 1px solid rgba(239,68,68,0.25);
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: bold;
        }

        /* ══════════════════════════════════════
           SHARED ALERT BARS
        ══════════════════════════════════════ */
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
        .alert-close   { cursor:pointer; background:none; border:none; color:inherit; font-size:16px; }

        /* ══════════════════════════════════════
           SHARED FORM ELEMENTS (auth pages)
        ══════════════════════════════════════ */
        .form-group { margin-bottom: 18px; }

        .form-label {
            display: block;
            font-size: 11px;
            font-weight: bold;
            color: var(--text-sub);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
            transition: color 0.3s;
        }

        .form-input {
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
        .form-input:focus { border-color: #3b82f6; }
        .form-input::placeholder { color: var(--text-muted); }

        /* ══════════════════════════════════════
           SHARED BUTTONS
        ══════════════════════════════════════ */
        .btn-primary-grad {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #1e40af, #0ea5e9);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        .btn-primary-grad:hover { opacity: 0.9; }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: #38bdf8;
            text-decoration: none;
        }
        .back-link:hover { text-decoration: underline; }

        /* ══════════════════════════════════════
           SHARED ERROR / SUCCESS BOXES
        ══════════════════════════════════════ */
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

        /* ══════════════════════════════════════
           BOOTSTRAP LIGHT MODE OVERRIDES (admin)
        ══════════════════════════════════════ */
        html.light .modal-content  { background: var(--surface) !important; color: var(--text) !important; border-color: var(--border) !important; }
        html.light .modal-header,
        html.light .modal-footer   { border-color: var(--border) !important; }
        html.light .form-control,
        html.light .form-select    { background: var(--input-bg) !important; color: var(--text) !important; border-color: var(--border) !important; }
        html.light .form-control::placeholder { color: var(--text-muted) !important; }
        html.light .btn-close-white { filter: invert(1); }

        /* ══════════════════════════════════════
           EXTRA CSS (passed from page)
        ══════════════════════════════════════ */
        <?= $extra_css ?>
    </style>
</head>
<body>

<!-- ══ THEME TOGGLE ══ -->
<div class="theme-btn" onclick="toggleTheme()">
    <label class="toggle-switch">
        <input type="checkbox" id="themeToggle">
        <div class="toggle-track"></div>
        <div class="toggle-thumb"></div>
    </label>
    <span class="theme-btn-label" id="themeLabel">&#127769; Dark</span>
</div>

<!-- ══ INLINE THEME SCRIPT — runs before page renders to prevent flash ══ -->
<script>
(function(){
    const saved = localStorage.getItem('rfid_theme') || 'dark';
    if(saved === 'light') document.documentElement.classList.add('light');
    const toggle = document.getElementById('themeToggle');
    const label  = document.getElementById('themeLabel');
    if(toggle) toggle.checked = (saved === 'light');
    if(label)  label.innerHTML = saved === 'light' ? '&#9728;&#65039; Light' : '&#127769; Dark';
})();

function applyTheme(theme){
    const html   = document.documentElement;
    const toggle = document.getElementById('themeToggle');
    const label  = document.getElementById('themeLabel');
    const logos  = document.querySelectorAll('[data-logo]');

    if(theme === 'light'){
        html.classList.add('light');
        if(toggle) toggle.checked  = true;
        if(label)  label.innerHTML = '&#9728;&#65039; Light';
        logos.forEach(lg => lg.src = lg.dataset.logoLight || 'irams.png');
    } else {
        html.classList.remove('light');
        if(toggle) toggle.checked  = false;
        if(label)  label.innerHTML = '&#127769; Dark';
        logos.forEach(lg => lg.src = lg.dataset.logoDark || 'iramswhite.png');
    }
    localStorage.setItem('rfid_theme', theme);
}

function toggleTheme(){
    applyTheme(localStorage.getItem('rfid_theme') === 'light' ? 'dark' : 'light');
}
</script>