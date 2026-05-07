<?php
include 'db.php';

// ── Latest scan (for card display on all devices) ──
$latest = $conn->query("
    SELECT users.name, users.position, users.photo,
           departments.name AS department, departments.logo,
           attendance.status, attendance.id AS attendance_id
    FROM attendance
    JOIN users ON users.id = attendance.user_id
    LEFT JOIN departments ON users.department_id = departments.id
    ORDER BY attendance.time DESC LIMIT 1
");
$latestScan = $latest->num_rows > 0 ? $latest->fetch_assoc() : null;

// ── Last 10 activity feed ──
$result = $conn->query("
    SELECT users.name, attendance.time, attendance.status
    FROM attendance
    JOIN users ON users.id = attendance.user_id
    ORDER BY attendance.time DESC LIMIT 10
");
$feed = [];
while($row = $result->fetch_assoc()){
    $feed[] = $row;
}

header('Content-Type: application/json');
echo json_encode([
    "latest" => $latestScan,
    "feed"   => $feed
]);
?>