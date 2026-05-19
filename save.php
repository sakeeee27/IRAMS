<?php
require_once 'includes/auth.php';
require_once 'db.php';
require_once 'includes/functions.php';

require_admin();

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header("Location: admin.php?page=employees");
    exit;
}

require_csrf();

// Legacy endpoint kept for compatibility. New employee creation happens in admin.php.
$name = trim($_POST['name'] ?? '');
$rfid = trim($_POST['rfid_uid'] ?? '');
$position = trim($_POST['position'] ?? '');
$department_name = trim($_POST['department'] ?? '');

if($name === '' || $rfid === '' || $position === '' || $department_name === ''){
    header("Location: admin.php?page=employees&msg=error");
    exit;
}

if(rfid_exists($conn, $rfid)){
    header("Location: admin.php?page=employees&msg=duplicate");
    exit;
}

$department_id = null;
$stmt = $conn->prepare("SELECT id FROM departments WHERE name = ? LIMIT 1");
$stmt->bind_param("s", $department_name);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();
$department_id = $row['id'] ?? null;
if(!$department_id){
    header("Location: admin.php?page=employees&msg=error");
    exit;
}

$parts = preg_split('/\s+/', $name);
$surname = count($parts) > 1 ? array_pop($parts) : '';
$first = trim(implode(' ', $parts));
if($first === ''){
    $first = $name;
}

insert_employee($conn, [
    'rfid_uid'      => $rfid,
    'employee_id'   => null,
    'biometric_id'  => null,
    'first_name'    => $first,
    'middle_name'   => null,
    'surname'       => $surname,
    'name'          => $name,
    'position'      => $position,
    'department_id' => $department_id,
    'photo'         => 'default.png',
]);

log_activity($conn, 'added', $name, admin_name());

header("Location: admin.php?page=employees&msg=registered");
exit;
?>
