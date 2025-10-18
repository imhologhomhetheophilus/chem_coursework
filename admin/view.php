<?php
include('../includes/db_connect.php');
require('../includes/auth.php');
if (session_status() === PHP_SESSION_NONE) session_start();
require_admin();
$id = $_GET['id'] ?? 0;
$st = $pdo->prepare('SELECT s.*, sp.name as supervisor, p.name as personnel FROM submissions s LEFT JOIN supervisors sp ON s.supervisor_id=sp.id LEFT JOIN personnel p ON s.personnel_id=p.id WHERE s.id = ?');
$st->execute([$id]);
$s = $st->fetch();
$remarks = $pdo->prepare('SELECT r.*, st.reg_no, st.name FROM remarks r JOIN students st ON r.student_id = st.id WHERE r.submission_id = ?');
$remarks->execute([$id]);
$rows = $remarks->fetchAll();
include('../includes/header.php');
?>
<a href="dashboard.php" class="btn btn-link">« Back</a>
<h3>Submission — <?=htmlspecialchars($s['group_id'])?></h3>
<p>Supervisor: <?=htmlspecialchars($s['supervisor'])?> | Personnel: <?=htmlspecialchars($s['personnel'])?> | Date: <?=htmlspecialchars($s['date'])?></p>
<p><a class="btn btn-primary" href="../uploads/<?=rawurlencode($s['file_name'])?>" download>Download File</a></p>
<h4>Remarks</h4>
<table class="table table-bordered"><thead><tr><th>Reg No</th><th>Name</th><th>Remark</th></tr></thead><tbody>
  <?php foreach($rows as $r): ?>
    <tr><td><?=htmlspecialchars($r['reg_no'])?></td><td><?=htmlspecialchars($r['name'])?></td><td><?=htmlspecialchars($r['remark'])?></td></tr>
  <?php endforeach; ?>
</tbody></table>
<div class="container py-5" style="margin-bottom: 10rem;"></div>
<?php include('../includes/footer.php'); ?>