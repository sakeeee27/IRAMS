<?php
$conn = new mysqli("localhost", "root", "", "rfid_attendance2");

$result = $conn->query("
SELECT users.name, attendance.time, attendance.status 
FROM attendance
JOIN users ON users.id = attendance.user_id
ORDER BY attendance.time DESC
");
?>

<table border="1">
<tr>
    <th>Name</th>
    <th>Time</th>
    <th>Status</th>
</tr>

<?php while($row = $result->fetch_assoc()) { ?>
<tr>
    <td><?php echo $row['name']; ?></td>
    <td><?php echo $row['time']; ?></td>
    <td><?php echo $row['status']; ?></td>
</tr>
<?php } ?>

</table>