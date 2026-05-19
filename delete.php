<?php
require_once 'includes/auth.php';
require_once 'db.php';
require_once 'includes/functions.php';

require_admin();

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header("Location: admin.php?page=employees&msg=error");
    exit;
}

require_csrf();

$id = (int)($_POST['id'] ?? 0);
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
