<?php
session_start();
include 'db.php';

$id = $conn->real_escape_string($_GET['id']);

// Get employee name and photo before deleting
$result   = $conn->query("SELECT name, photo FROM users WHERE id='$id'");
$emp_name = '—';
if($result && $row = $result->fetch_assoc()){
    $emp_name = $row['name'];
    // Delete photo file if not default
    if($row['photo'] && $row['photo'] !== 'default.png' && file_exists(__DIR__ . '/' . $row['photo'])){
        unlink(__DIR__ . '/' . $row['photo']);
    }
}

// Step 1: Delete attendance records first (child rows)
$conn->query("DELETE FROM attendance WHERE user_id='$id'");

// Step 2: Now safe to delete the employee (parent row)
$conn->query("DELETE FROM users WHERE id='$id'");

// Step 3: Log the activity
$admin_n  = $conn->real_escape_string($_SESSION['admin_name'] ?? $_SESSION['admin_user'] ?? 'Admin');
$emp_name = $conn->real_escape_string($emp_name);
$conn->query("INSERT INTO activity_log (action, emp_name, admin_name)
              VALUES ('deleted', '$emp_name', '$admin_n')");

header("Location: admin.php?page=employees&msg=deleted");
exit;
?>