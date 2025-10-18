<?php
require 'includes/db.php';
require 'includes/auth.php';
require_leader(); // ensures only group leaders can access

$group = $_SESSION['group_id'];

// Fetch students
$stmt = $pdo->prepare('SELECT * FROM students WHERE group_id = ?');
$stmt->execute([$group]);
$members = $stmt->fetchAll();

// Fetch supervisors and personnel
$sup = $pdo->query('SELECT * FROM supervisors ORDER BY name')->fetchAll();
$pers = $pdo->query('SELECT * FROM personnel ORDER BY name')->fetchAll();

// Message feedback
$msg = $_GET['m'] ?? '';

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Group <?=htmlspecialchars($group)?> — Coursework Submission</h3>
  <a class="btn btn-sm btn-secondary" href="logout.php">Logout</a>
</div>

<?php if($msg): ?>
  <div class="alert alert-success"><?=htmlspecialchars($msg)?></div>
<?php endif; ?>

<form action="handle_submit.php" method="post" enctype="multipart/form-data" class="card p-3 mb-4">
  <input type="hidden" name="group_id" value="<?=htmlspecialchars($group)?>">

  <div class="row mb-3">
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
  <table class="table table-striped">
    <thead>
      <tr>
        <th>SN</th>
        <th>Reg No</th>
        <th>Name</th>
        <th>Remark</th>
        <th>Date & Time</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach($members as $i => $m): ?>
      <tr>
        <td><?=($i+1)?></td>
        <td><?=htmlspecialchars($m['reg_no'])?></td>
        <td><?=htmlspecialchars($m['name'])?></td>
        <td>
          <input type="hidden" name="student_ids[]" value="<?=$m['id']?>">
          <select name="remark_<?=$m['id']?>" class="form-select form-select-sm">
            <option value="Not Cleared">Not Cleared</option>
            <option value="Cleared">Cleared</option>
          </select>
        </td>
        <td>
          <input type="datetime-local" name="created_at_<?=$m['id']?>" class="form-control form-control-sm" value="<?=date('Y-m-d\TH:i')?>" required>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>

  <div class="text-center">
    <button class="btn btn-success">Submit Coursework</button>
  </div>
</form>

<?php include 'includes/footer.php'; ?>
