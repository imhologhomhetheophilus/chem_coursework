<?php
require 'includes/db_connect.php';
require 'includes/auth.php';
require_leader();

$group = $_SESSION['group_id'];

// Fetch group members
$stmt = $pdo->prepare('SELECT * FROM students WHERE group_id = ?');
$stmt->execute([$group]);
$members = $stmt->fetchAll();

// Fetch supervisors and personnel
$sup = $pdo->query('SELECT * FROM supervisors')->fetchAll();
$pers = $pdo->query('SELECT * FROM personnel')->fetchAll();

$msg = $_GET['m'] ?? '';
include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Group <?=htmlspecialchars($group)?> — Submission</h3>
  <div><a class="btn btn-sm btn-primary" href="logout.php">Logout</a></div>
</div>

<?php if($msg): ?>
<div class="alert alert-success"><?=htmlspecialchars($msg)?></div>
<?php endif; ?>

<form method="POST" action="handle_submit.php" enctype="multipart/form-data" class="card p-3 mb-4">
  <input type="hidden" name="group_id" value="<?=htmlspecialchars($group)?>">

  <div class="row mb-2">
    <div class="col-md-6">
      <label class="form-label">Supervisor</label>
      <select name="supervisor_id" class="form-select" required>
        <option value="">-- Select Supervisor --</option>
        <?php foreach($sup as $s): ?>
          <option value="<?=$s['id']?>"><?=htmlspecialchars($s['name'])?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-6">
      <label class="form-label">Lab Personnel</label>
      <select name="personnel_id" class="form-select" required>
        <option value="">-- Select Personnel --</option>
        <?php foreach($pers as $p): ?>
          <option value="<?=$p['id']?>"><?=htmlspecialchars($p['name'] . ' (' . $p['code'] . ')')?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="mb-3">
    <label class="form-label">Upload Coursework (PDF/DOCX)</label>
    <input class="form-control" type="file" name="file" accept=".pdf,.docx" required>
  </div>

  <h5>Group Members</h5>
 <table class="table table-striped table-bordered">
    <thead class="table-primary">
        <tr>
            <th>#</th>
            <th>Group</th>
            <th>Supervisor</th>
            <th>Personnel</th>
            <th>File</th>
            <th>Date</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($subs as $i => $s): ?>
        <tr>
            <td><?= ($i + 1) ?></td>
            <td><?= htmlspecialchars($s['group_id'] ?? '') ?></td>
            <td><?= htmlspecialchars($s['supervisor'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($s['personnel'] ?? 'N/A') ?></td>
            <td>
                <?php if(!empty($s['file_name'])): ?>
                    <a href="download_file.php?id=<?= $s['id'] ?>" target="_blank">View File</a>
                <?php else: ?>
                    No file
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($s['created_at'] ?? '') ?></td>
            <td>
                <a href="delete_submission.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

  <div class="text-center">
    <button class="btn btn-primary">Submit Coursework</button>
  </div>
</form>

<div class="container py-5" style="margin-bottom: 10rem;"></div>
<?php include 'includes/footer.php'; ?>
