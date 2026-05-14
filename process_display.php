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

// ── MCN: display only, no attendance write ──
if(strtoupper(trim($user['department'])) === 'MCN'){
    echo json_encode([
        "status"     => "success",
        "mode"       => "display_only",
        "name"       => $user['name'],
        "position"   => $user['position'],
        "department" => $user['department'],
        "photo"      => $user['photo'],
        "logo"       => $user['logo'],
    ]);
    exit;
}

// ── Non-MCN: record attendance as normal ──
$last   = get_last_attendance($conn, $user['id']);
$status = ($last && $last['status'] === 'IN') ? 'OUT' : 'IN';

insert_attendance($conn, $user['id'], $status);

echo json_encode([
    "status"     => "success",
    "mode"       => "record",
    "user_id"    => $user['id'],
    "name"       => $user['name'],
    "position"   => $user['position'],
    "department" => $user['department'],
    "photo"      => $user['photo'],
    "logo"       => $user['logo'],
    "log"        => $status
]);
?>