<?php
include 'db.php';

// Optional terminal filter: ?terminal=inside | ?terminal=entrance
$terminal = $_GET['terminal'] ?? '';
$allowed  = ['inside', 'entrance'];
if(!in_array($terminal, $allowed, true)) $terminal = '';

// Optional dept filter: ?dept=mcn_only | ?dept=non_mcn
$dept_filter = $_GET['dept'] ?? '';

// Check if terminal column exists
$col_check        = $conn->query("SHOW COLUMNS FROM attendance LIKE 'terminal'");
$has_terminal_col = ($col_check && $col_check->num_rows > 0);

$t_where = ($terminal && $has_terminal_col)
    ? "AND attendance.terminal = '" . $conn->real_escape_string($terminal) . "'"
    : '';

$d_where = '';
if($dept_filter === 'mcn_only') $d_where = "AND UPPER(TRIM(departments.name)) = 'MCN'";
if($dept_filter === 'non_mcn')  $d_where = "AND (departments.name IS NULL OR UPPER(TRIM(departments.name)) != 'MCN')";

// ── Latest scan ──
$latest_q = $conn->query("
    SELECT users.name, users.position, users.photo,
           departments.name AS department, departments.logo,
           attendance.status, attendance.id AS attendance_id
    FROM attendance
    JOIN users ON users.id = attendance.user_id
    LEFT JOIN departments ON users.department_id = departments.id
    WHERE 1=1 $t_where $d_where
    ORDER BY attendance.time DESC LIMIT 1
");
$latestScan = ($latest_q && $latest_q->num_rows > 0) ? $latest_q->fetch_assoc() : null;

// ── Last 10 activity feed ──
$result_q = $conn->query("
    SELECT users.name, attendance.time, attendance.status
    FROM attendance
    JOIN users ON users.id = attendance.user_id
    LEFT JOIN departments ON users.department_id = departments.id
    WHERE 1=1 $t_where $d_where
    ORDER BY attendance.time DESC LIMIT 10
");
$feed = [];
if($result_q){
    while($row = $result_q->fetch_assoc()) $feed[] = $row;
}

header('Content-Type: application/json');
echo json_encode(["latest" => $latestScan, "feed" => $feed]);
?>