<?php
include 'db.php';
include 'includes/functions.php';

header('Content-Type: application/json');

$rfid = trim($_POST['rfid_uid'] ?? '');
if(empty($rfid)){
    echo json_encode(["status" => "error", "message" => "No RFID provided"]);
    exit;
}

// Get user — prepared statement
$user = get_user_by_rfid($conn, $rfid);
if(!$user){
    echo json_encode(["status" => "error", "message" => "RFID not registered"]);
    exit;
}

// Toggle IN/OUT — prepared statement
$last   = get_last_attendance($conn, $user['id']);
$status = ($last && $last['status'] === 'IN') ? 'OUT' : 'IN';

// Insert attendance — prepared statement
insert_attendance($conn, $user['id'], $status);

echo json_encode([
    "status"     => "success",
    "user_id"    => $user['id'],
    "name"       => $user['name'],
    "position"   => $user['position'],
    "department" => $user['department'],
    "photo"      => $user['photo'],
    "logo"       => $user['logo'],
    "log"        => $status
]);
?>