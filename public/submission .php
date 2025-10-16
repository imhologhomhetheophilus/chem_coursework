<?php
require '../includes/db_connect.php';
session_start();

$group = $_SESSION['group_id'] ?? '';
$stmt = $pdo->prepare("SELECT s.*, sp.name AS supervisor, p.name AS personnel 
                       FROM submissions s
                       LEFT JOIN supervisors sp ON s.supervisor_id = sp.id
                       LEFT JOIN personnel p ON s.personnel_id = p.id
                       WHERE s.group_id = ?
                       ORDER BY s.created_at DESC");
$stmt->execute([$group]);
$subs = $stmt->fetchAll();

include '../includes/header.php';
?>

<h3>My Submissions</h3>
<table class="table table-striped table-bordered">
<thead>
<tr>
    <th>#</th>
    <th>Supervisor</th>
    <th>Personnel</th>
    <th>File</th>
    <th>Date</th>
</tr>
</thead>
<tbody>
<?php foreach($subs as $i => $s): ?>
<tr>
    <td><?= $i+1 ?></td>
    <td><?= htmlspecialchars($s['supervisor'] ?? 'N/A') ?></td>
    <td><?= htmlspecialchars($s['personnel'] ?? 'N/A') ?></td>
    <td>
        <?php if($s['file_name']): ?>
            <a href="uploads/<?= htmlspecialchars($s['file_name']) ?>" target="_blank">View File</a>
        <?php else: ?>
            No file
        <?php endif; ?>
    </td>
    <td><?= $s['created_at'] ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<?php include '../includes/footer.php'; ?>
