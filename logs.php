<?php
$page_title = "Attendance Logs";
$page_type  = "public";
$extra_css  = <<<'PAGECSS'
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, sans-serif;
            background: #0f172a;
            color: white;
            min-height: 100vh;
            padding: 32px 24px;
        }

        /* ── HEADER ── */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .back-btn {
            font-size: 13px;
            color: #38bdf8;
            text-decoration: none;
            padding: 8px 16px;
            border: 1px solid #1e40af;
            border-radius: 8px;
            background: rgba(30,64,175,0.15);
            transition: background 0.2s;
        }
        .back-btn:hover { background: rgba(30,64,175,0.35); }

        .page-title {
            font-size: 20px;
            font-weight: bold;
            color: #f1f5f9;
        }

        .page-subtitle {
            font-size: 12px;
            color: #64748b;
            margin-top: 2px;
        }

        /* live dot */
        .live-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: #22c55e;
            border-radius: 50%;
            margin-right: 6px;
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.3; }
        }

        /* ── STAT CARDS ── */
        .stats-row {
            display: flex;
            gap: 16px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .stat-card {
            flex: 1;
            min-width: 140px;
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 14px;
            padding: 18px 20px;
        }

        .stat-label {
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: bold;
            color: #f1f5f9;
        }

        .stat-value.in-color  { color: #22c55e; }
        .stat-value.out-color { color: #ef4444; }

        /* ── FILTERS ── */
        .filters-row {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            align-items: center;
        }

        .filters-row input,
        .filters-row select {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 8px;
            color: #e2e8f0;
            padding: 9px 14px;
            font-size: 13px;
            outline: none;
        }

        .filters-row input { min-width: 260px; }
        .filters-row input::placeholder { color: #475569; }
        .filters-row select option { background: #1e293b; }

        .clear-btn {
            font-size: 12px;
            color: #94a3b8;
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 8px;
            padding: 9px 14px;
            cursor: pointer;
        }
        .clear-btn:hover { color: #f1f5f9; border-color: #475569; }

        /* ── TABLE WRAP ── */
        .table-wrap {
            background: #1e293b;
            border-radius: 16px;
            border: 1px solid #334155;
            overflow: hidden;
        }

        .table-scroll { overflow-x: auto; }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead tr { background: #0f172a; }

        th {
            padding: 13px 18px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            text-align: left;
            white-space: nowrap;
        }

        td {
            padding: 12px 18px;
            font-size: 13px;
            color: #cbd5e1;
            border-top: 1px solid #0f172a;
            vertical-align: middle;
        }

        tbody tr:hover { background: rgba(255,255,255,0.03); }

        /* photo in table */
        .row-photo {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #334155;
        }

        .row-name { font-weight: bold; color: #f1f5f9; }
        .row-pos  { font-size: 11px; color: #64748b; margin-top: 2px; }

        /* status badge */
        .badge {
            font-size: 11px;
            font-weight: bold;
            padding: 4px 12px;
            border-radius: 999px;
            letter-spacing: 0.5px;
        }
        .badge-in {
            background: rgba(34,197,94,0.15);
            color: #22c55e;
            border: 1px solid rgba(34,197,94,0.25);
        }
        .badge-out {
            background: rgba(239,68,68,0.15);
            color: #ef4444;
            border: 1px solid rgba(239,68,68,0.25);
        }

        /* empty state */
        .empty-state {
            text-align: center;
            padding: 48px 20px;
            color: #475569;
            font-size: 14px;
        }

        /* ── FOOTER COUNT ── */
        .table-footer {
            padding: 12px 18px;
            font-size: 12px;
            color: #475569;
            border-top: 1px solid #0f172a;
            text-align: right;
        }
PAGECSS;
include 'includes/header.php';
?>

<!-- PAGE HEADER -->
<div class="page-header">
    <div class="header-left">
        <a href="index.php" class="back-btn">&#8592; Back</a>
        <div>
            <div class="page-title">Attendance Logs</div>
            <div class="page-subtitle">
                <span class="live-dot"></span>Auto-refreshes every 5 seconds
            </div>
        </div>
    </div>
    <div id="lastUpdated" style="font-size:12px;color:#475569;"></div>
</div>

<!-- STAT CARDS -->
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-label">Total Records</div>
        <div class="stat-value" id="statTotal">—</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Checked IN</div>
        <div class="stat-value in-color" id="statIn">—</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Checked OUT</div>
        <div class="stat-value out-color" id="statOut">—</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Today's Records</div>
        <div class="stat-value" id="statToday">—</div>
    </div>
</div>

<!-- FILTERS -->
<div class="filters-row">
    <input type="text" id="searchBox" placeholder="&#128269;  Search name, position, department...">
    <select id="statusFilter">
        <option value="">All Status</option>
        <option value="IN">IN</option>
        <option value="OUT">OUT</option>
    </select>
    <input type="date" id="dateFilter">
    <button class="clear-btn" onclick="clearFilters()">&#10005; Clear</button>
</div>

<!-- TABLE -->
<div class="table-wrap">
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Position</th>
                    <th>Department</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="logBody"></tbody>
        </table>
        <div class="empty-state" id="emptyState" style="display:none;">
            No records found.
        </div>
    </div>
    <div class="table-footer" id="rowCount"></div>
</div>

<script>
let allRows = [];

// Set today's date as default in date filter
const today = new Date().toISOString().split('T')[0];
document.getElementById('dateFilter').value = today;

function loadLogs(){
    fetch("fetch_all.php")
    .then(res => res.json())
    .then(rows => {
        allRows = rows;
        updateStats(rows);
        renderTable();

        const now = new Date().toLocaleTimeString("en-PH", {
            hour:"2-digit", minute:"2-digit", second:"2-digit"
        });
        document.getElementById("lastUpdated").innerText = "Last updated: " + now;
    });
}

function updateStats(rows){
    const todayStr = new Date().toISOString().split('T')[0];
    document.getElementById("statTotal").innerText = rows.length;
    document.getElementById("statIn").innerText    = rows.filter(r => r.status === "IN").length;
    document.getElementById("statOut").innerText   = rows.filter(r => r.status === "OUT").length;
    document.getElementById("statToday").innerText = rows.filter(r => r.time.startsWith(todayStr)).length;
}

function renderTable(){
    const search = document.getElementById("searchBox").value.toLowerCase().trim();
    const status = document.getElementById("statusFilter").value;
    const date   = document.getElementById("dateFilter").value;

    const filtered = allRows.filter(r => {
        const matchSearch = !search ||
            (r.name       || '').toLowerCase().includes(search) ||
            (r.position   || '').toLowerCase().includes(search) ||
            (r.department || '').toLowerCase().includes(search);

        const matchStatus = !status || r.status === status;
        const matchDate   = !date   || r.time.startsWith(date);

        return matchSearch && matchStatus && matchDate;
    });

    const empty = document.getElementById("emptyState");
    const count = document.getElementById("rowCount");

    if(filtered.length === 0){
        document.getElementById("logBody").innerHTML = "";
        empty.style.display = "block";
        count.innerText = "";
        return;
    }

    empty.style.display = "none";
    count.innerText = `Showing ${filtered.length} of ${allRows.length} records`;

    let html = "";
    filtered.forEach(r => {
        const isIn   = r.status === "IN";
        const photo  = r.photo ? r.photo : "default.png";
        const dt     = new Date(r.time);
        const dateStr = dt.toLocaleDateString("en-PH", {
            year:"numeric", month:"short", day:"numeric"
        });
        const timeStr = dt.toLocaleTimeString("en-PH", {
            hour:"2-digit", minute:"2-digit", second:"2-digit"
        });

        html += `
        <tr>
            <td>
                <div style="display:flex;align-items:center;gap:10px;">
                    <img src="${photo}" class="row-photo" onerror="this.src='default.png'">
                    <div>
                        <div class="row-name">${r.name || '—'}</div>
                    </div>
                </div>
            </td>
            <td>${r.position || '—'}</td>
            <td>${r.department || '—'}</td>
            <td>${dateStr}</td>
            <td>${timeStr}</td>
            <td><span class="badge ${isIn ? 'badge-in' : 'badge-out'}">${r.status}</span></td>
        </tr>`;
    });

    document.getElementById("logBody").innerHTML = html;
}

function clearFilters(){
    document.getElementById("searchBox").value    = "";
    document.getElementById("statusFilter").value = "";
    document.getElementById("dateFilter").value   = "";
    renderTable();
}

// Attach filter listeners
document.getElementById("searchBox").addEventListener("input", renderTable);
document.getElementById("statusFilter").addEventListener("change", renderTable);
document.getElementById("dateFilter").addEventListener("change", renderTable);

// Load and auto-refresh
setInterval(loadLogs, 5000);
loadLogs();
</script>

<?php include 'includes/footer.php'; ?>