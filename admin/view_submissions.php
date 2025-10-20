<?php
require '../includes/db_connect.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Only allow admin
if (!isset($_SESSION['admin'])) {
    header('Location: ../admin/login.php');
    exit;
}

$submission_id = $_GET['id'] ?? 0;

// ================= Fetch Submission =================
$stmt = $pdo->prepare("
    SELECT s.*, sp.name AS supervisor, p.name AS personnel
    FROM submissions s
    LEFT JOIN supervisors sp ON s.supervisor_id = sp.id
    LEFT JOIN personnel p ON s.personnel_id = p.id
    WHERE s.id = ?
");
$stmt->execute([$submission_id]);
$submission = $stmt->fetch();

if (!$submission) {
    die("Submission not found.");
}

// ================= Fetch Students & Remarks =================
$students_stmt = $pdo->prepare("
    SELECT st.id, st.regno, st.name, ss.remark AS student_remark
    FROM students st
    LEFT JOIN submission_students ss 
        ON st.id = ss.student_id AND ss.submission_id = ?
    WHERE st.group_id = ?
    ORDER BY st.id
");
$students_stmt->execute([$submission_id, $submission['group_id']]);
$students = $students_stmt->fetchAll();

// ================= Handle Admin Remark Update =================
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_remark = $_POST['admin_remark'] ?? '';
    $score = $_POST['score'] ?? null;

    $update = $pdo->prepare("UPDATE submissions SET admin_remark = ?, score = ? WHERE id = ?");
    $update->execute([$admin_remark, $score, $submission_id]);
    $msg = "✅ Submission updated successfully!";
}

// ================== Include Header ==================
include '../includes/header.php';
?>

<a href="dashboard.php" class="btn btn-link">« Back</a>
<h3>Submission — <?= htmlspecialchars($submission['group_id']) ?></h3>
<p>
    Supervisor: <?= htmlspecialchars($submission['supervisor'] ?? '—') ?> | 
    Personnel: <?= htmlspecialchars($submission['personnel'] ?? '—') ?> | 
    Date: <?= htmlspecialchars($submission['created_at'] ?? '—') ?>
</p>

<p>
    <?php if (!empty($submission['file_name'])): ?>
        <a class="btn btn-primary" href="../uploads/<?= rawurlencode($submission['file_name']) ?>" download>Download File</a>
    <?php else: ?>
        <span class="text-muted">No file uploaded</span>
    <?php endif; ?>
</p>

<!-- ================= Student Remarks ================= -->
<h4>Student Remarks</h4>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>#</th>
            <th>Reg No</th>
            <th>Name</th>
            <th>Remark</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($students as $i => $st): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($st['regno']) ?></td>
                <td><?= htmlspecialchars($st['name']) ?></td>
                <td><?= htmlspecialchars($st['student_remark'] ?? '—') ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($students)): ?>
            <tr><td colspan="4" class="text-center text-muted">No students found</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- ================= Admin Remark & Score ================= -->
<h4>Admin Remark</h4>
<?php if ($msg): ?>
    <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<form method="post" class="card p-3 mb-4 shadow-sm">
    <div class="mb-3">
        <label class="form-label">Admin Remark</label>
        <select name="admin_remark" class="form-select" required>
            <option value="">-- Select Remark --</option>
            <option value="Clear" <?= ($submission['admin_remark'] ?? '') === 'Clear' ? 'selected' : '' ?>>Clear</option>
            <option value="Not Clear" <?= ($submission['admin_remark'] ?? '') === 'Not Clear' ? 'selected' : '' ?>>Not Clear</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Score</label>
        <input type="number" name="score" class="form-control" value="<?= htmlspecialchars($submission['score'] ?? '') ?>" min="0">
    </div>
    <button type="submit" class="btn btn-success">Update Submission</button>
</form>

<div class="container py-5" style="margin-bottom: 10rem;"></div>

<?php include '../includes/footer.php'; ?>
