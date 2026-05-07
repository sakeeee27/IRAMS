<?php
include 'db.php';

$rfid = $_POST['rfid_uid'];

$user = $conn->query("
SELECT users.*, departments.name AS department, departments.logo
FROM users
LEFT JOIN departments ON users.department_id = departments.id
WHERE users.rfid_uid='$rfid'
");

if ($user->num_rows == 0) {
    echo json_encode(["status"=>"error","message"=>"RFID not registered"]);
    exit;
}

$userData = $user->fetch_assoc();
$user_id = $userData['id'];

// Toggle IN/OUT
$last = $conn->query("SELECT * FROM attendance 
WHERE user_id='$user_id' ORDER BY time DESC LIMIT 1");

$status = "IN";
if ($last->num_rows > 0) {
    $lastData = $last->fetch_assoc();
    $status = ($lastData['status'] == 'IN') ? 'OUT' : 'IN';
}

$conn->query("INSERT INTO attendance (user_id, status) 
VALUES ('$user_id','$status')");

echo json_encode([
    "status"=>"success",
    "name"=>$userData['name'],
    "position"=>$userData['position'],
    "department"=>$userData['department'],
    "photo"=>$userData['photo'],
    "logo"=>$userData['logo'],
    "log"=>$status
]);
?>