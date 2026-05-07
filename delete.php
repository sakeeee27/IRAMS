<?php
session_start();
include 'db.php';
include 'includes/auth.php';
include 'includes/functions.php';

require_admin();

$id = (int)($_GET['id'] ?? 0);
if($id <= 0){
    header("Location: admin.php?page=employees&msg=error");
    exit;
}

// Get employee before deleting
$user = get_user_by_id($conn, $id);
if($user){
    delete_photo($user['photo']);
}

// Delete employee + attendance — prepared statements
delete_employee($conn, $id);

// Log the activity — prepared statement
log_activity($conn, 'deleted', $user['name'] ?? '—', admin_name());

header("Location: admin.php?page=employees&msg=deleted");
exit;
?>