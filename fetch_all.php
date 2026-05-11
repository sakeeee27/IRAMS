<?php
include 'db.php';

// Optional department filter
$dept = isset($_GET['dept']) ? (int)$_GET['dept'] : 0;

$where = '';
if($dept > 0) {
    $where = "WHERE users.department_id = $dept";
}

$result = $conn->query("
    SELECT
        users.name,
        users.position,
        users.photo,
        departments.name AS department,
        attendance.time,
        attendance.status
    FROM attendance
    JOIN users ON users.id = attendance.user_id
    LEFT JOIN departments ON users.department_id = departments.id
    $where
    ORDER BY attendance.time DESC
");

$data = [];
while($row = $result->fetch_assoc()){
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
?>