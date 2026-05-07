<?php
include 'db.php';

$name = $_POST['name'];
$rfid = $_POST['rfid_uid'];
$position = $_POST['position'];
$department = $_POST['department'];
$photo = $_POST['photo'];
$logo = $_POST['logo'];

$conn->query("INSERT INTO users 
(name, rfid_uid, position, department, photo, logo)
VALUES 
('$name','$rfid','$position','$department','$photo','$logo')");

header("Location: admin.php");
?>