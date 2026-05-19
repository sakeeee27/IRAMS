<?php
require_once 'includes/auth.php';
require_once 'db.php';

require_admin();

// Optional department filter
$dept = isset($_GET['dept']) ? (int)$_GET['dept'] : 0;

$where = '';
$types = '';
$params = [];
if($dept > 0) {
    $where = "WHERE users.department_id = ?";
    $types = 'i';
    $params[] = $dept;
}

$stmt = $conn->prepare("
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
if($types){
    $stmt->bind_param($types, $params[0]);
}
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while($row = $result->fetch_assoc()){
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
?>
