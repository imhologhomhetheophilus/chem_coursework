<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) session_start();

require_once 'includes/db_connect.php'; // Use your current connection file

// Ensure group leader is logged in
if (!isset($_SESSION['group_id'])) {
    header('Location: group_login.php');
    exit;
}

$group_id = $_SESSION['group_id'];

// Fetch group members
$stmt = $pdo->prepare("SELECT * FROM students WHERE group_id = ?");
$stmt->execute([$group_id]);
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch supervisors and personnel
$supervisors = $pdo->query("SELECT * FROM supervisors ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$personnel = $pdo->query("SELECT * FROM personnel ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Optional success message
$msg = $_GET['m'] ?? '';

include 'includes/header.php';
?>

<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="text-primary">Group <?= htmlspecialchars($group_id) ?> — Coursework Submission</h3>
    <a class="btn btn-sm btn-secondary" href="logout.php">Logout</a>
  </div>

  <?php if ($msg): ?>
    <div class="alert alert-success text-center"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>

  <form action="handle_submit.php" method="post" enctype="multipart/form-data" class="card p-4 shadow-sm mb-4">
    <input type="hidden" name="group_id" value="<?= htmlspecialchars($group_id) ?>">

    <!-- Supervisor & Personnel -->
    <div class="row mb-3">
      <div class="col-md-6">
        <label class="form-label fw-semibold">Supervisor</label>
        <select name="supervisor_id" class="form-select" required>
          <option value="">-- Select Supervisor --</option>
          <?php foreach ($supervisors as $s): ?>
            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label fw-semibold">Lab Personnel</label>
        <select name="personnel_id" class="form-select" required>
          <option value="">-- Select Personnel --</option>
          <?php foreach ($personnel as $p): ?>
            <option value="<?= $p['id'] ?>">
              <?= htmlspecialchars($p['name']) ?>
              <?= !empty($p['code']) ? ' (' . htmlspecialchars($p['code']) . ')' : '' ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <!-- File Upload -->
    <div class="mb-3">
      <label class="form-label fw-semibold">Upload Coursework (PDF/DOCX)</label>
      <input class="form-control" type="file" name="file" accept=".pdf,.docx" required>
    </div>

    <!-- Group Members -->
    <h5 class="mt-4 mb-3">👥 Group Members</h5>
    <table class="table table-striped align-middle text-center">
      <thead class="table-light">
        <tr>
          <th>SN</th>
          <th>Reg No</th>
          <th>Name</th>
          <th>Remark</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($members): ?>
          <?php foreach ($members as $i => $m): ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td><?= htmlspecialchars($m['regno'] ?? 'N/A') ?></td>
              <td><?= htmlspecialchars($m['name'] ?? 'Unnamed') ?></td>
              <td>
                <input type="hidden" name="student_ids[]" value="<?= $m['id'] ?>">
                <select name="remark_<?= $m['id'] ?>" class="form-select form-select-sm">
                  <option value="Not Cleared">Not Cleared</option>
                  <option value="Cleared">Cleared</option>
                </select>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="4" class="text-muted">No members found for this group.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <!-- Submit Button -->
    <div class="text-center mt-3">
      <button type="submit" class="btn btn-success px-4">Submit Coursework</button>
    </div>
  </form>
</div>

<?php include 'includes/footer.php'; ?>
