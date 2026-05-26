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

// ── MCN employees: display only at entrance terminal ──
// This file (process.php) is used by the INSIDE terminal (index.php)
// so MCN employees should record attendance here normally.
// The entrance terminal (display.php) uses process_display.php instead,
// which handles the MCN display-only logic.
// Both terminals record attendance for all departments via process.php.

$last   = get_last_attendance($conn, $user['id']);
$status = ($last && $last['status'] === 'IN') ? 'OUT' : 'IN';

insert_attendance($conn, $user['id'], $status, 'inside');

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