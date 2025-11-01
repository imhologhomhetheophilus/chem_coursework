<?php
session_start();
require 'includes/db_connect.php';
require 'includes/admin_auth.php';

// Fetch all submissions
$subs = $pdo->query("SELECT s.*, g.group_name FROM submissions s JOIN groups g ON s.group_id=g.id ORDER BY s.created_at DESC")->fetchAll();

// Process update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE submissions SET admin_remark=:remark, admin_score=:score WHERE id=:id");
    $stmt->execute([
        ':remark' => $_POST['admin_remark'],
        ':score' => $_POST['admin_score'],
        ':id' => $_POST['submission_id']
    ]);
    header("Location: admin_review.php");
    exit;
}

include 'includes/header.php';
?>

<div class="container my-4">
<h3>Admin Review</h3>
<table class="table table-bordered">
<thead>
<tr>
<th>#</th>
<th>Group</th>
<th>Submission Date</th>
<th>Experiment Date</th>
<th>Admin Remark</th>
<th>Admin Score</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php foreach ($subs as $i => $sub): ?>
<tr>
<td><?= $i+1 ?></td>
<td><?= htmlspecialchars($sub['group_name']) ?></td>
<td><?= date('Y-m-d H:i', strtotime($sub['created_at'])) ?></td>
<td><?= date('Y-m-d H:i', strtotime($sub['experiment_datetime'])) ?></td>
<td><?= htmlspecialchars($sub['admin_remark'] ?? '') ?></td>
<td><?= htmlspecialchars($sub['admin_score'] ?? '') ?></td>
<td>
<form method="post" class="d-flex gap-2">
<input type="hidden" name="submission_id" value="<?= $sub['id'] ?>">
<input type="text" name="admin_remark" class="form-control form-control-sm" placeholder="Remark" required>
<input type="number" name="admin_score" class="form-control form-control-sm" placeholder="Score" step="0.01" required>
<button class="btn btn-primary btn-sm" type="submit">Update</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<?php include 'includes/footer.php'; ?>
