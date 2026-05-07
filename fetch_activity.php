<?php
include 'db.php';

header('Content-Type: application/json');

$result = $conn->query("
    SELECT action, emp_name, admin_name, created_at
    FROM activity_log
    ORDER BY created_at DESC
    LIMIT 10
");

if(!$result){
    echo json_encode([]);
    exit;
}

$data = [];
while($row = $result->fetch_assoc()){
    $data[] = $row;
}

echo json_encode($data);
?>