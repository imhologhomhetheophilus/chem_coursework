<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../includes/db_connect.php';

// Redirect if not logged in
if (!isset($_SESSION['admin'])) {
    header('Location: index.php');
    exit;
}

// Handle remark or score update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submission_id'])) {
    $sub_id = $_POST['submission_id'];
    $remark = $_POST['remark'] ?? '';
    $score = $_POST['score'] ?? null;

    $stmt = $pdo->prepare("UPDATE submissions SET remark = ?, score = ? WHERE id = ?");
    $stmt->execute([$remark, $score, $sub_id]);
    $msg = "✅ Submission updated successfully!";
}

// Fetch all data for admin
$groups = $pdo->query("SELECT * FROM groups ORDER BY group_id")->fetchAll(PDO::FETCH_ASSOC);
$supervisors = $pdo->query("SELECT * FROM supervisors ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$personnel = $pdo->query("SELECT * FROM personnel ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Fetch submissions with relationships
$subs = $pdo->query("
    SELECT s.*, g.group_id, sp.name AS supervisor, p.name AS personnel
    FROM submissions s
    LEFT JOIN groups g ON s.group_id = g.group_id
    LEFT JOIN supervisors sp ON s.supervisor_id = sp.id
    LEFT JOIN personnel p ON s.personnel_id = p.id
    ORDER BY s.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

include('../includes/header.php');
?>

<div class="container mt-4">
  <h3 class="text-center text-primary mb-4">🧭 Admin Dashboard</h3>

  <?php if (!empty($msg)): ?>
    <div class="alert alert-success text-center"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>

  <div class="alert alert-info text-center">
    Welcome, <strong><?= htmlspecialchars($_SESSION['admin']); ?></strong> 🎉
  </div>

  <!-- Management Buttons -->
  <div class="row text-center mb-4">
    <div class="col-md-2 mb-2"><a href="manage_students.php" class="btn btn-outline-secondary w-100">Manage Students</a></div>
    <div class="col-md-2 mb-2"><a href="manage_groups.php" class="btn btn-outline-primary w-100">Manage Groups</a></div>
    <div class="col-md-2 mb-2"><a href="manage_supervisors.php" class="btn btn-outline-success w-100">Manage Supervisors</a></div>
    <div class="col-md-2 mb-2"><a href="manage_personnel.php" class="btn btn-outline-warning w-100">Manage Personnel</a></div>

    <!-- ✅ Added new "View Submissions" button -->
    <div class="col-md-2 mb-2"><a href="view_submissions.php" class="btn btn-outline-info w-100">View Submissions</a></div>

    <div class="col-md-2 mb-2"><a href="view.php" class="btn btn-outline-dark w-100">All Submissions</a></div>
    <div class="col-md-2 mb-2"><a href="logout.php" class="btn btn-outline-danger w-100">Logout</a></div>
  </div>

  <!-- Coursework Table -->
  <div class="card shadow-sm">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
      <h5 class="mb-0">📚 Uploaded Coursework</h5>
    </div>
    <div class="card-body p-0">
      <table class="table table-bordered table-striped align-middle mb-0">
        <thead class="table-secondary text-center">
          <tr>
            <th>#</th>
            <th>Group</th>
            <th>Students</th>
            <th>Supervisor</th>
            <th>Personnel</th>
            <th>File</th>
            <th>Remark</th>
            <th>Score</th>
            <th>Uploaded</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
        <?php if (!empty($subs)): ?>
          <?php foreach ($subs as $i => $s): ?>
            <?php
              // Fetch students under this group
              $st_query = $pdo->prepare("SELECT name, regno FROM students WHERE group_id = ?");
              $st_query->execute([$s['group_id']]);
              $students = $st_query->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td class="fw-bold text-primary"><?= htmlspecialchars($s['group_id'] ?? 'N/A') ?></td>
              <td>
                <?php if ($students): ?>
                  <ul class="mb-0">
                    <?php foreach ($students as $st): ?>
                      <li><?= htmlspecialchars($st['name']) ?> (<?= htmlspecialchars($st['regno'] ?? '—') ?>)</li>
                    <?php endforeach; ?>
                  </ul>
                <?php else: ?>
                  <em class="text-muted">No students</em>
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($s['supervisor'] ?? '—') ?></td>
              <td><?= htmlspecialchars($s['personnel'] ?? '—') ?></td>
              <td>
                <?php if (!empty($s['file_name'])): ?>
                  <a href="../uploads/<?= htmlspecialchars($s['file_name']) ?>" target="_blank">View</a>
                <?php else: ?>
                  <span class="text-muted">No file</span>
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($s['remark'] ?? '—') ?></td>
              <td><?= htmlspecialchars($s['score'] ?? '—') ?></td>
              <td><?= htmlspecialchars($s['created_at']) ?></td>
              <td>
                <form method="post" class="d-flex flex-column gap-2">
                  <input type="hidden" name="submission_id" value="<?= htmlspecialchars($s['id']) ?>">
                  <select name="remark" class="form-select form-select-sm">
                    <option value="">--Remark--</option>
                    <option value="Clear" <?= ($s['remark'] ?? '') === 'Clear' ? 'selected' : '' ?>>Clear</option>
                    <option value="Not Clear" <?= ($s['remark'] ?? '') === 'Not Clear' ? 'selected' : '' ?>>Not Clear</option>
                  </select>
                  <input type="number" name="score" class="form-control form-control-sm" placeholder="Score" value="<?= htmlspecialchars($s['score'] ?? '') ?>">
                  <button class="btn btn-sm btn-primary mt-1">Update</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="10" class="text-center text-muted">No submissions found.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include('../includes/footer.php'); ?>
