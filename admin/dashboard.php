<?php
require '../includes/db.php';
require '../includes/auth.php';
if (session_status() === PHP_SESSION_NONE) session_start();
require_admin();

$st = $pdo->query('SELECT s.*, sp.name as supervisor, p.name as personnel FROM submissions s LEFT JOIN supervisors sp ON s.supervisor_id=sp.id LEFT JOIN personnel p ON s.personnel_id=p.id ORDER BY s.date DESC');
$subs = $st->fetchAll();
include '../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Admin — Submissions</h3>
  <div>
    <a class="btn btn-sm btn-success" href="manage_supervisors.php">Supervisors</a>
    <a class="btn btn-sm btn-success" href="manage_personnel.php">Personnel</a>
    <a class="btn btn-sm btn-success" href="manage_groups.php">Groups</a>
    <a class="btn btn-sm btn-success" href="manage_students.php">Students</a>
    <a class="btn btn-sm btn-secondary" href="logout.php">Logout</a>
  </div>
</div>
<table class="table table-striped">
  <thead><tr><th>#</th><th>Group</th><th>Supervisor</th><th>Personnel</th><th>File</th><th>Date</th><th>Action</th></tr></thead>
  <tbody>
    <?php foreach($subs as $i=>$s): ?>
      <tr>
        <td><?= $i+1 ?></td>
        <td><?=htmlspecialchars($s['group_id'])?></td>
        <td><?=htmlspecialchars($s['supervisor'])?></td>
        <td><?=htmlspecialchars($s['personnel'])?></td>
        <td><a href="../uploads/<?=rawurlencode($s['file_name'])?>" download><?=htmlspecialchars($s['file_name'])?></a></td>
        <td><?=htmlspecialchars($s['date'])?></td>
        <td><a class="btn btn-sm btn-info" href="view.php?id=<?=$s['id']?>">View</a></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php include '../includes/footer.php'; ?>