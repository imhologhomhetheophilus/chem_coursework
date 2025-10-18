<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/db_connect.php';

// Ensure group leader is logged in
if (!isset($_SESSION['group_id'])) {
    header('Location: group_login.php');
    exit;
}

$group = $_SESSION['group_id'];
$message = '';

// Handle file submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supervisor_id = $_POST['supervisor_id'] ?? null;
    $personnel_id = $_POST['personnel_id'] ?? null;
    $remark = $_POST['remark'] ?? '';
    $file_name = null;

    // Handle file upload
    if (!empty($_FILES['file']['name'])) {
        $upload_dir = __DIR__ . '/uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $basename = basename($_FILES['file']['name']);
        $file_name = time() . '_' . preg_replace('/\s+/', '_', $basename);
        $target = $upload_dir . $file_name;

        if (!move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
            $message = "❌ Failed to upload file.";
        }
    }

    if (!$message) {
        $stmt = $pdo->prepare("INSERT INTO submissions (group_id, supervisor_id, personnel_id, file_name, remark, created_at)
                               VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$group, $supervisor_id, $personnel_id, $file_name, $remark]);
        $message = "✅ Coursework uploaded successfully!";
    }
}

// Fetch supervisors and personnel
$supervisors = $pdo->query("SELECT id, name FROM supervisors ORDER BY name")->fetchAll();
$personnel = $pdo->query("SELECT id, name FROM personnel ORDER BY name")->fetchAll();

// Fetch students in this group (with optional regno column)
$students_stmt = $pdo->prepare("SHOW COLUMNS FROM students LIKE 'regno'");
$students_stmt->execute();
$has_regno = $students_stmt->rowCount() > 0;

if ($has_regno) {
    $students_query = "SELECT id, name, regno FROM students WHERE group_id = ?";
} else {
    $students_query = "SELECT id, name FROM students WHERE group_id = ?";
}

$students = $pdo->prepare($students_query);
$students->execute([$group]);
$students = $students->fetchAll(PDO::FETCH_ASSOC);

// Fetch previous submissions
$stmt = $pdo->prepare("SELECT s.*, sp.name AS supervisor, p.name AS personnel 
                       FROM submissions s
                       LEFT JOIN supervisors sp ON s.supervisor_id = sp.id
                       LEFT JOIN personnel p ON s.personnel_id = p.id
                       WHERE s.group_id = ?
                       ORDER BY s.created_at DESC");
$stmt->execute([$group]);
$subs = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<div class="container mt-4">
  <h3 class="text-center text-primary mb-4">Group Coursework Submission</h3>

  <?php if ($message): ?>
    <div class="alert alert-info text-center"><?= htmlspecialchars($message, ENT_QUOTES) ?></div>
  <?php endif; ?>

  <!-- Group Members Section -->
  <div class="card mb-4 shadow-sm">
    <div class="card-header bg-dark text-white">
      <h5 class="mb-0">👥 Group Members (<?= htmlspecialchars($group) ?>)</h5>
    </div>
    <div class="card-body p-0">
      <table class="table table-striped mb-0">
        <thead class="table-secondary">
          <tr>
            <th>#</th>
            <th>Name</th>
            <?php if ($has_regno): ?><th>Reg. No</th><?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php if (count($students) > 0): ?>
            <?php foreach ($students as $i => $st): ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($st['name'] ?? 'N/A') ?></td>
                <?php if ($has_regno): ?>
                  <td><?= htmlspecialchars($st['regno'] ?? 'N/A') ?></td>
                <?php endif; ?>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="<?= $has_regno ? 3 : 2 ?>" class="text-center text-muted">No students found under this group.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Coursework Upload Section -->
  <form method="post" enctype="multipart/form-data" class="card p-3 shadow-sm mb-4">
    <h5 class="mb-3 text-secondary">📤 Upload Coursework</h5>

    <div class="row mb-3">
      <div class="col-md-6">
        <label class="form-label">Supervisor</label>
        <select name="supervisor_id" class="form-select" required>
          <option value="">-- Select Supervisor --</option>
          <?php foreach ($supervisors as $sp): ?>
            <option value="<?= $sp['id'] ?>"><?= htmlspecialchars($sp['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Personnel</label>
        <select name="personnel_id" class="form-select" required>
          <option value="">-- Select Personnel --</option>
          <?php foreach ($personnel as $p): ?>
            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Upload Coursework File</label>
      <input type="file" name="file" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Remark</label>
      <select name="remark" class="form-select">
        <option value="">-- Select Remark --</option>
        <option value="Clear">Clear</option>
        <option value="Not Clear">Not Clear</option>
      </select>
    </div>

    <div class="text-center">
      <button class="btn btn-primary px-4">Submit</button>
    </div>
  </form>

  <!-- Previous Submissions -->
  <h4 class="mb-3 text-secondary">📚 Previous Submissions</h4>
  <table class="table table-bordered table-striped align-middle">
    <thead class="table-dark">
      <tr>
        <th>#</th>
        <th>Supervisor</th>
        <th>Personnel</th>
        <th>File</th>
        <th>Remark</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($subs)): ?>
        <?php foreach ($subs as $i => $s): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td><?= htmlspecialchars($s['supervisor'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($s['personnel'] ?? 'N/A') ?></td>
            <td>
              <?php if (!empty($s['file_name'])): ?>
                <a href="uploads/<?= htmlspecialchars($s['file_name']) ?>" target="_blank">View</a>
              <?php else: ?>
                No file
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($s['remark'] ?? '—') ?></td>
            <td><?= htmlspecialchars($s['created_at'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="6" class="text-center text-muted">No submissions yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php include 'includes/footer.php'; ?>
