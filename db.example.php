<?php
// Copy this file to db.php and fill in your credentials
$conn = new mysqli("192.168.1.241", "rfiduser", "Local@dm1n@db26", "rfiddb");
if($conn->connect_error) die("Connection failed: " . $conn->connect_error);
$conn->set_charset("utf8mb4");
?>