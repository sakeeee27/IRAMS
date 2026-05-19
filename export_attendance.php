<?php
require_once 'includes/auth.php';
require_once 'db.php';

require_admin();

function bind_stmt_params($stmt, $types, &$params){
    $refs = [$types];
    foreach($params as $key => $value){
        $refs[] = &$params[$key];
    }
    return call_user_func_array([$stmt, 'bind_param'], $refs);
}

// Get filters from request
$date = $_GET['date'] ?? date('Y-m-d');
$dt   = DateTime::createFromFormat('Y-m-d', $date);
if(!$dt || $dt->format('Y-m-d') !== $date){
    $date = date('Y-m-d');
}

$status = $_GET['status'] ?? '';
if(!in_array($status, ['IN', 'OUT'], true)){
    $status = '';
}

$search = trim($_GET['search'] ?? '');

// Build query
$where  = ["DATE(attendance.time) = ?"];
$types  = "s";
$params = [$date];

if($status !== ''){
    $where[]  = "attendance.status = ?";
    $types   .= "s";
    $params[] = $status;
}

if($search !== ''){
    $where[] = "(users.name LIKE ? OR users.position LIKE ? OR departments.name LIKE ?)";
    $like = '%' . $search . '%';
    $types .= "sss";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$where_sql = implode(' AND ', $where);

$stmt = $conn->prepare("
    SELECT
        users.employee_id,
        users.biometric_id,
        users.name,
        users.position,
        departments.name AS department,
        DATE(attendance.time)     AS date,
        TIME(attendance.time)     AS time,
        attendance.status
    FROM attendance
    JOIN users       ON users.id = attendance.user_id
    LEFT JOIN departments ON users.department_id = departments.id
    WHERE $where_sql
    ORDER BY attendance.time ASC
");
bind_stmt_params($stmt, $types, $params);
$stmt->execute();
$result = $stmt->get_result();

// Format filename
$filename = "Attendance_" . $date . ".xls";

// Output as Excel (XLS via HTML table — opens in Excel natively)
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Cache-Control: max-age=0");
?>
<html>
<head>
<meta charset="UTF-8">
<style>
    body  { font-family: Arial, sans-serif; font-size: 11pt; }
    table { border-collapse: collapse; width: 100%; }
    th {
        background-color: #1e40af;
        color: white;
        padding: 8px 12px;
        border: 1px solid #1e40af;
        text-align: left;
        font-size: 11pt;
    }
    td {
        padding: 7px 12px;
        border: 1px solid #cbd5e1;
        font-size: 10pt;
        vertical-align: middle;
    }
    tr:nth-child(even) td { background-color: #f1f5f9; }
    tr:nth-child(odd)  td { background-color: #ffffff; }
    .badge-in  { color: #16a34a; font-weight: bold; }
    .badge-out { color: #dc2626; font-weight: bold; }

    .report-title {
        font-size: 16pt;
        font-weight: bold;
        color: #1e293b;
        margin-bottom: 4px;
    }
    .report-sub {
        font-size: 10pt;
        color: #64748b;
        margin-bottom: 16px;
    }
</style>
</head>
<body>

<!-- REPORT HEADER -->
<p class="report-title">Attendance Report</p>
<p class="report-sub">
    Date: <strong><?= date('F j, Y', strtotime($date)) ?></strong>
    &nbsp;|&nbsp;
    Generated: <strong><?= date('F j, Y h:i A') ?></strong>
    &nbsp;|&nbsp;
    Exported by: <strong><?= htmlspecialchars($_SESSION['admin_name'] ?? $_SESSION['admin_user']) ?></strong>
    <?php if($status): ?>
    &nbsp;|&nbsp; Status: <strong><?= htmlspecialchars($status) ?></strong>
    <?php endif; ?>
</p>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Employee ID</th>
            <th>Biometric ID</th>
            <th>Name</th>
            <th>Position</th>
            <th>Department</th>
            <th>Date</th>
            <th>Time</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $count = 0;
    $total_in  = 0;
    $total_out = 0;

    if($result && $result->num_rows > 0):
        while($row = $result->fetch_assoc()):
            $count++;
            $isIn = $row['status'] === 'IN';
            if($isIn) $total_in++; else $total_out++;
            $time_fmt = date('h:i:s A', strtotime($row['time']));
            $date_fmt = date('F j, Y', strtotime($row['date']));
    ?>
        <tr>
            <td><?= $count ?></td>
            <td><?= htmlspecialchars($row['employee_id'] ?? '—') ?></td>
            <td><?= htmlspecialchars($row['biometric_id'] ?? '—') ?></td>
            <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
            <td><?= htmlspecialchars($row['position'] ?? '—') ?></td>
            <td><?= htmlspecialchars($row['department'] ?? '—') ?></td>
            <td><?= $date_fmt ?></td>
            <td><?= $time_fmt ?></td>
            <td class="<?= $isIn ? 'badge-in' : 'badge-out' ?>"><?= $row['status'] ?></td>
        </tr>
    <?php
        endwhile;
    else:
    ?>
        <tr><td colspan="9" style="text-align:center;color:#94a3b8;padding:20px;">No records found for this date.</td></tr>
    <?php endif; ?>
    </tbody>

    <!-- SUMMARY ROW -->
    <tfoot>
        <tr>
            <td colspan="7" style="text-align:right;font-weight:bold;background:#f8fafc;">Total Records: <?= $count ?></td>
            <td style="font-weight:bold;color:#16a34a;background:#f8fafc;">IN: <?= $total_in ?></td>
            <td style="font-weight:bold;color:#dc2626;background:#f8fafc;">OUT: <?= $total_out ?></td>
        </tr>
    </tfoot>
</table>

</body>
</html>
