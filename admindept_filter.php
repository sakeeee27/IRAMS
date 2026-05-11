<?php
/**
 * ══════════════════════════════════════════════════════════════════
 *  PATCH: Department Filter for Attendance Log  (admin.php)
 *  Apply the two changes below to your existing admin.php
 * ══════════════════════════════════════════════════════════════════
 *
 * ── CHANGE 1: Add department dropdown inside the panel-header filters ──
 *    Find this block in the attendance section (around line ~430):
 *
 *        <button class="btn btn-outline-secondary btn-sm" onclick="clearAttFilters()">&#10005; Clear</button>
 *
 *    ADD this <select> immediately BEFORE that button:
 */

/*  HTML TO INSERT:
    <select id="attDept" class="ctrl-input">
        <option value="">All Departments</option>
        <?php
        $dlist = $conn->query("SELECT id, name FROM departments ORDER BY name");
        while($dd = $dlist->fetch_assoc()):
        ?>
        <option value="<?= $dd['id'] ?>"><?= htmlspecialchars($dd['name']) ?></option>
        <?php endwhile; ?>
    </select>
*/

/**
 * ── CHANGE 2: Update the JavaScript in admin.php ──
 *
 *  A) In renderAtt(), find the filter block:
 *        const search = ...
 *        const status = ...
 *        const date   = ...
 *
 *     ADD this line after the date line:
 *        const dept   = document.getElementById('attDept')?.value   || '';
 *
 *  B) In the filtered = allAtt.filter(...) call, find:
 *        const md = !date   || r.time.startsWith(date);
 *        return ms && mv && md;
 *
 *     REPLACE with:
 *        const md = !date   || r.time.startsWith(date);
 *        const mdept = !dept || r.department === dept;
 *        return ms && mv && md && mdept;
 *
 *     NOTE: r.department must equal the department NAME string returned
 *     by fetch_all.php — which it does (the LEFT JOIN returns departments.name AS department).
 *     The <option value> should hold the department NAME, not the ID.
 *     Update the HTML option accordingly:
 *
 *        <option value="<?= htmlspecialchars($dd['name']) ?>">
 *            <?= htmlspecialchars($dd['name']) ?>
 *        </option>
 *
 *  C) In clearAttFilters(), add:
 *        const dp = document.getElementById('attDept'); if(dp) dp.value = '';
 *
 *  D) Register the event listener alongside the others at the bottom:
 *        const attDeptEl = document.getElementById('attDept');
 *        if(attDeptEl) attDeptEl.addEventListener('change', renderAtt);
 *
 * ══════════════════════════════════════════════════════════════════
 *  Below is the complete REPLACEMENT renderAtt + clearAttFilters
 *  JavaScript block you can paste directly over the existing ones:
 * ══════════════════════════════════════════════════════════════════
 */
?>
<!-- ═══ REPLACEMENT JS BLOCK — paste into admin.php, replacing renderAtt() and clearAttFilters() ═══ -->
<script>
function renderAtt(){
    const search = (document.getElementById('attSearch')?.value||'').toLowerCase();
    const status = document.getElementById('attStatus')?.value||'';
    const date   = document.getElementById('attDate')?.value||'';
    const dept   = document.getElementById('attDept')?.value||'';      // ← NEW

    const filtered = allAtt.filter(r=>{
        const ms   = !search || (r.name||'').toLowerCase().includes(search) || (r.position||'').toLowerCase().includes(search) || (r.department||'').toLowerCase().includes(search);
        const mv   = !status || r.status===status;
        const md   = !date   || r.time.startsWith(date);
        const mdept= !dept   || (r.department||'') === dept;            // ← NEW
        return ms && mv && md && mdept;
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
    const dp=document.getElementById('attDept');  if(dp) dp.value='';  // ← NEW
    renderAtt();
}

// Register listeners (replace the existing forEach block too)
['attSearch','attStatus','attDate'].forEach(id=>{
    const el=document.getElementById(id);
    if(el) el.addEventListener('input', renderAtt);
});
// ← NEW
const attDeptEl = document.getElementById('attDept');
if(attDeptEl) attDeptEl.addEventListener('change', renderAtt);
</script>