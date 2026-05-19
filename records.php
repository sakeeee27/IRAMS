<?php
require_once 'includes/auth.php';
require_once 'db.php';

require_admin();

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
    <td><?= htmlspecialchars($row['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
    <td><?= htmlspecialchars($row['time'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
    <td><?= htmlspecialchars($row['status'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
</tr>
<?php } ?>

</table>
