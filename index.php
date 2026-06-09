<?php
$page_title = "Live Dashboard";
$page_type  = "public";
$extra_css  = <<<'PAGECSS'
/* ══ CSS VARIABLES — DARK (default) ══ */
        :root {
            --bg:                #5c6983;
            --surface:           #1e293b;
            --surface2:          #0f172a;
            --border:            #334155;
            --text:              #e2e8f0;
            --text-muted:        #64748b;
            --text-sub:          #94a3b8;
            --text-strong:       #f1f5f9;
            --title-color:       #d5d9df;
            --card-bg:           linear-gradient(160deg, #1e293b 0%, #0f172a 100%);
            --card-foot-border:  #1e293b;
            --card-photo-border: #0f172a;
            --scan-line:         #334155;
            --activity-sep:      #0f172a;
            --clock-bg:          #1e293b;
            --clock-color:       #64748b;
            --shadow:            0 25px 60px rgba(0,0,0,0.5);
        }

        /* ══ CSS VARIABLES — LIGHT ══ */
        html.light {
            --bg:                #e2e8f0;
            --surface:           #ffffff;
            --surface2:          #f8fafc;
            --border:            #cbd5e1;
            --text:              #1e293b;
            --text-muted:        #64748b;
            --text-sub:          #475569;
            --text-strong:       #0f172a;
            --title-color:       #1e293b;
            --card-bg:           linear-gradient(160deg, #ffffff 0%, #f1f5f9 100%);
            --card-foot-border:  #e2e8f0;
            --card-photo-border: #f1f5f9;
            --scan-line:         #cbd5e1;
            --activity-sep:      #f1f5f9;
            --clock-bg:          #ffffff;
            --clock-color:       #475569;
            --shadow:            0 25px 60px rgba(0,0,0,0.12);
        }

        /* ── RESET & BASE ── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            transition: background 0.3s, color 0.3s;
        }

        /* Hidden RFID input */
        #rfid {
            position: fixed;
            opacity: 0;
            pointer-events: none;
            width: 1px;
            height: 1px;
        }

        /* ── THEME TOGGLE ── */
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
            z-index: 999;
            transition: background 0.3s, border-color 0.3s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        .theme-btn:hover { border-color: #38bdf8; }

        .theme-btn-label {
            font-size: 12px;
            color: var(--text-sub);
            white-space: nowrap;
        }

        .toggle-switch {
            position: relative;
            width: 36px;
            height: 20px;
            flex-shrink: 0;
        }
        .toggle-switch input { opacity:0; width:0; height:0; position:absolute; }

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

        /* ── PAGE LAYOUT ── */
        .page-wrapper {
            display: flex;
            gap: 0;
            align-items: flex-start;
            justify-content: flex-start;
            min-height: 100vh;
            width: 100%;
        }

        /* ── LEFT: CARD PANEL ── */
        .card-panel {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 14px;
            flex: 0 0 60%;
            width: 60%;
            padding: 26px 20px;
            min-height: 100vh;
        }

        .system-title {
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--title-color);
            transition: color 0.3s;
            text-align: center;
        }

        /* ── ID CARD ── */
        .id-card {
            width: 100%;
            max-width: 950px;
            background: var(--card-bg);
            border-radius: 24px;
            border: 1px solid var(--border);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: border-color 0.3s, box-shadow 0.3s, background 0.3s;
        }

        .id-card.card-flash {
            border-color: #38bdf8;
            box-shadow: var(--shadow), 0 0 30px rgba(56,189,248,0.3);
        }

        /* CARD HEADER — kept as original dark blue, not themed */
        .card-header {
            background: linear-gradient(135deg, #032288, #043349);
            padding: 16px 18px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .card-logo {
            width: 70px;
            height: 70px;
            object-fit: contain;
            background: whitesmoke;
            border-radius: 10px;
            padding: 4px;
            flex-shrink: 0;
        }

        .card-dept {
            font-size: 15px;
            font-weight: bold;
            color: #fff;
        }

        .card-subtitle {
            font-size: 10px;
            color: rgba(255,255,255,0.65);
            margin-top: 2px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* CARD BODY */
        .card-body {
            padding: 40px 32px 32px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 18px;
        }

        .photo-ring {
            width: 450px;
            height: 520px;
            border-radius: 24px;
            padding: 4px;
            background: linear-gradient(135deg, #0ea5e9, #6366f1);
        }

        .card-photo {
            width: 100%;
            height: 100%;
            border-radius: 24px;
            object-fit: cover;
            object-position: top;
            border: 3px solid var(--card-photo-border);
            display: block;
            transition: border-color 0.3s;
        }

        .card-info { text-align: center; width: 100%; }

        .card-name {
            font-size: 32px;
            font-weight: bold;
            color: var(--text-strong);
            line-height: 1.2;
            margin-bottom: 5px;
            transition: color 0.3s;
        }

        .card-position {
            font-size: 18px;
            color: var(--text-sub);
            margin-bottom: 16px;
            transition: color 0.3s;
        }

        .status-badge-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .status-label {
            font-size: 11px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .status-badge {
            font-size: 12px;
            font-weight: bold;
            padding: 5px 18px;
            border-radius: 999px;
            background: var(--border);
            color: var(--text-sub);
            letter-spacing: 1px;
            transition: background 0.3s;
        }

        .badge-in {
            background: rgba(34,197,94,0.15) !important;
            color: #22c55e !important;
            border: 1px solid rgba(34,197,94,0.3);
        }

        .badge-out {
            background: rgba(239,68,68,0.15) !important;
            color: #ef4444 !important;
            border: 1px solid rgba(239,68,68,0.3);
        }

        /* CARD FOOTER */
        .card-footer {
            padding: 12px 20px 16px;
            border-top: 1px solid var(--card-foot-border);
            transition: border-color 0.3s;
        }

        .scan-line {
            height: 3px;
            background: repeating-linear-gradient(
                90deg,
                var(--scan-line) 0px, var(--scan-line) 4px,
                transparent 4px, transparent 8px
            );
            border-radius: 2px;
            margin-bottom: 8px;
        }

        .footer-text {
            font-size: 10px;
            color: var(--text-muted);
            text-align: center;
            letter-spacing: 0.5px;
        }

        /* ── CLOCK ── */
        .clock-bar {
            font-size: 13px;
            color: var(--clock-color);
            background: var(--clock-bg);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 10px 14px;
            width: 100%;
            text-align: center;
            transition: background 0.3s, border-color 0.3s, color 0.3s;
        }

        /* ── LOGS LINK ── */
        .logs-link {
            display: block;
            font-size: 13px;
            color: #38bdf8;
            text-decoration: none;
            padding: 10px 16px;
            border: 1px solid #1e40af;
            border-radius: 8px;
            background: rgba(30,64,175,0.15);
            text-align: center;
            width: 100%;
            transition: background 0.2s;
        }
        .logs-link:hover { background: rgba(30,64,175,0.35); }

        /* ── RIGHT: ACTIVITY PANEL ── */
        .activity-panel {
            flex: 0 0 32%;
            width: 32%;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 28px;
            margin: 57px 10px 36px 0px;
            max-height: calc(100vh - 72px);
            overflow: hidden;
            position: sticky;
            top: 57px;
            display: flex;
            flex-direction: column;
            transition: background 0.3s, border-color 0.3s;
            box-shadow: var(--shadow);
        }

        /* Activity feed — no forced stretch */
        #activityFeed {
            overflow-y: auto;
            min-height: 0;
        }

        /* Clock + logs pinned at bottom */
        .panel-bottom {
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            gap: 10px;
            border-top: 1px solid var(--border);
            padding-top: 16px;
            margin-top: 16px;
        }

        .activity-title {
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 24px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border);
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 0;
            border-bottom: 1px solid var(--activity-sep);
        }
        .activity-item:last-child { border-bottom: none; }

        .activity-dot { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; }
        .dot-in  { background: #22c55e; box-shadow: 0 0 6px #22c55e; }
        .dot-out { background: #ef4444; box-shadow: 0 0 6px #ef4444; }

        .activity-details { flex: 1; }

        .activity-name {
            font-size: 14px;
            font-weight: bold;
            color: var(--text);
            transition: color 0.3s;
        }

        .activity-time {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 3px;
        }

        .activity-badge {
            font-size: 11px;
            font-weight: bold;
            padding: 4px 12px;
            border-radius: 999px;
            letter-spacing: 0.5px;
        }
        .activity-badge.badge-in  { background: rgba(34,197,94,0.15); color: #22c55e; }
        .activity-badge.badge-out { background: rgba(239,68,68,0.15);  color: #ef4444; }
PAGECSS;
include 'includes/header.php';
?>

<!-- Hidden input only active on the host with the RFID scanner plugged in -->
<input type="text" id="rfid" autofocus autocomplete="off">

<!-- Theme toggle rendered by includes/header.php -->

<div class="page-wrapper">

    <!-- LEFT: ID CARD -->
    <div class="card-panel">

        <div class="system-title">IRAMS - 8TH FLOOR</div>

        <div class="id-card" id="idCard">

            <!-- HEADER -->
            <div class="card-header">
                <img id="logo" src="logos/default.png" class="card-logo">
                <div>
                    <div id="department" class="card-dept">---</div>
                    <div class="card-subtitle">Employee ID Card</div>
                </div>
            </div>

            <!-- BODY -->
            <div class="card-body">
                <div class="photo-ring">
                    <img id="photo" src="default.png" class="card-photo">
                </div>
                <div class="card-info">
                    <div id="name" class="card-name">Waiting for scan...</div>
                    <div id="position" class="card-position">---</div>
                    <div class="status-badge-wrap">
                        <span class="status-label">Status</span>
                        <span id="status" class="status-badge">---</span>
                    </div>
                </div>
            </div>

            <!-- FOOTER -->
            <div class="card-footer">
                <div class="scan-line"></div>
                <div class="footer-text">Tap RFID card to register attendance</div>
            </div>

        </div>

    </div>

    <!-- RIGHT: ACTIVITY FEED -->
    <div class="activity-panel">
        <div class="activity-title">MCN Recent Activity</div>
        <div id="activityFeed"></div>
        <div class="panel-bottom">
            <div class="clock-bar"><span id="clock"></span></div>
        </div>
    </div>

</div>

<script>
// ── RFID scanner input ──
const input = document.getElementById("rfid");

function escapeHtml(value){
    return String(value ?? '').replace(/[&<>"']/g, ch => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    }[ch]));
}

// ── Robust RFID capture via keydown on document ──
let rfidBuffer   = "";
let rfidTimer    = null;
let isSending    = false;

const RFID_MIN_LEN = 8;
const RFID_IDLE_MS = 80;

function flushRFID(){
    clearTimeout(rfidTimer);
    rfidTimer = null;

    const uid = rfidBuffer.trim().replace(/[\r\n]/g, "");
    rfidBuffer = "";

    if(uid.length < RFID_MIN_LEN) return;
    if(isSending) return;

    sendRFID(uid);
}

document.addEventListener("keydown", function(e){
    if(e.ctrlKey || e.altKey || e.metaKey) return;
    if(e.key.length > 1 && e.key !== "Enter") return;

    if(e.key === "Enter"){
        if(rfidBuffer.length >= RFID_MIN_LEN){
            flushRFID();
        } else {
            rfidBuffer = "";
            clearTimeout(rfidTimer);
        }
        return;
    }

    rfidBuffer += e.key;
    clearTimeout(rfidTimer);
    rfidTimer = setTimeout(flushRFID, RFID_IDLE_MS);
});

input.setAttribute("readonly", "readonly");
setInterval(() => {
    if(document.activeElement === document.body || document.activeElement === null){
        input.focus();
    }
}, 300);

function sendRFID(uid){
    isSending = true;
    fetch("process.php", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: "rfid_uid=" + encodeURIComponent(uid)
    })
    .then(res => {
        if(!res.ok) throw new Error("HTTP " + res.status);
        return res.json();
    })
    .then(data => {
        isSending = false;
        if(data.status === "success"){
            updateCard(data.name, data.position, data.department, data.log, data.photo, data.logo);
        } else {
            alert(data.message);
        }
    })
    .catch(err => {
        isSending = false;
        console.warn("RFID fetch error:", err);
    });
}

// ── Shared card updater ──
let lastAttendanceId = null;
let clearCardTimer   = null;

function clearCard(){
    document.getElementById("name").innerText     = "Waiting for scan...";
    document.getElementById("position").innerText = "---";
    document.getElementById("department").innerText = "---";
    const badge = document.getElementById("status");
    badge.innerText   = "---";
    badge.className   = "status-badge";
    document.getElementById("photo").src = "default.png";
    document.getElementById("logo").src  = "logos/default.png";
}

function resetClearTimer(){
    if(clearCardTimer) clearTimeout(clearCardTimer);
    clearCardTimer = setTimeout(clearCard, 60000); // 1 minute
}

function updateCard(name, position, department, log, photo, logo){
    document.getElementById("name").innerText       = name;
    document.getElementById("position").innerText   = position;
    document.getElementById("department").innerText = department;

    const badge = document.getElementById("status");
    badge.innerText = log;
    badge.className = "status-badge " + (log === "IN" ? "badge-in" : "badge-out");

    document.getElementById("photo").src =
        (photo ? photo : "default.png") + "?t=" + Date.now();
    document.getElementById("logo").src =
        logo ? logo : "logos/default.png";

    const card = document.getElementById("idCard");
    card.classList.add("card-flash");
    setTimeout(() => card.classList.remove("card-flash"), 800);

    resetClearTimer(); // start/restart the 1-minute idle countdown
}

// ── Live clock ──
function updateClock(){
    document.getElementById("clock").innerText = new Date().toLocaleString("en-PH", {
        weekday:"long", year:"numeric", month:"long",
        day:"numeric", hour:"2-digit", minute:"2-digit", second:"2-digit"
    });
}
setInterval(updateClock, 1000);
updateClock();

// ── Poll fetch.php every 2 seconds ──
function loadActivity(){
    fetch("fetch.php?dept=mcn_only")
    .then(res => res.json())
    .then(data => {

        // Update card if new scan
        if(data.latest){
            const newId = data.latest.attendance_id;
            if(newId !== lastAttendanceId){
                lastAttendanceId = newId;
                updateCard(
                    data.latest.name,
                    data.latest.position,
                    data.latest.department,
                    data.latest.status,
                    data.latest.photo,
                    data.latest.logo
                );
            }
        }

        // Update activity feed — only overwrite when records exist
        if(data.feed && data.feed.length > 0){
            let html = "";
            data.feed.forEach(r => {
                const isIn = r.status === "IN";
                const time = new Date(r.time.replace(' ','T')).toLocaleTimeString("en-PH", {
                    hour:"2-digit", minute:"2-digit", second:"2-digit"
                });
                const safeName   = escapeHtml(r.name   || '');
                const safeStatus = escapeHtml(r.status || '');
                html += `
                <div class="activity-item">
                    <div class="activity-dot ${isIn ? 'dot-in' : 'dot-out'}"></div>
                    <div class="activity-details">
                        <div class="activity-name">${safeName}</div>
                        <div class="activity-time">${time}</div>
                    </div>
                    <span class="activity-badge ${isIn ? 'badge-in' : 'badge-out'}">${safeStatus}</span>
                </div>`;
            });
            document.getElementById("activityFeed").innerHTML = html;
        }
    })
    .catch(err => console.error('loadActivity error:', err));
}
setInterval(loadActivity, 2000);
loadActivity();

// ── THEME TOGGLE ──
function applyTheme(theme){
    const html      = document.documentElement;
    const toggle    = document.getElementById("themeToggle");
    const label     = document.getElementById("themeLabel");
    const siteLogo  = document.getElementById("siteLogo");
    const cardLogo  = document.getElementById("logo"); // the card header logo

    if(theme === "light"){
        html.classList.add("light");
        toggle.checked  = true;
        label.innerHTML = "&#9728;&#65039; Light";
        if(siteLogo) siteLogo.src = "irams.png";
        // Only swap card logo if still on default (no employee scanned)
        if(cardLogo && cardLogo.src.includes("iramswhite")) cardLogo.src = "irams.png";
    } else {
        html.classList.remove("light");
        toggle.checked  = false;
        label.innerHTML = "&#127769; Dark";
        if(siteLogo) siteLogo.src = "iramswhite.png";
        if(cardLogo && cardLogo.src.includes("irams.png") && !cardLogo.src.includes("logos/")) cardLogo.src = "iramswhite.png";
    }
    localStorage.setItem("rfid_theme", theme);
}

function toggleTheme(){
    const current = localStorage.getItem("rfid_theme") || "dark";
    applyTheme(current === "dark" ? "light" : "dark");
}

// Apply saved theme instantly on load
applyTheme(localStorage.getItem("rfid_theme") || "dark");
</script>

<?php include 'includes/footer.php'; ?>