<?php
require '../includes/db_connect.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$submission_id = $_GET['id'] ?? 0;

// Fetch Submission with supervisor/personnel names
$stmt = $pdo->prepare("
    SELECT s.*, sp.name AS supervisor, p.name AS personnel
    FROM submissions s
    LEFT JOIN supervisors sp ON s.supervisor_id = sp.id
    LEFT JOIN personnel p ON s.personnel_id = p.id
    WHERE s.id = ?
");
$stmt->execute([$submission_id]);
$submission = $stmt->fetch();
if (!$submission) die("Submission not found.");

// Fetch students for this submission
$students_stmt = $pdo->prepare("
    SELECT st.id, st.name, st.regno, ss.remark AS leader_remark,
           ss.admin_remark, ss.score
    FROM students st
    LEFT JOIN submission_students ss
        ON st.id = ss.student_id AND ss.submission_id = ?
    WHERE st.group_id = ?
    ORDER BY st.id
");
$students_stmt->execute([$submission_id, $submission['group_id']]);
$students = $students_stmt->fetchAll();

$msg = '';

// =================== Handle Admin Remark + Score Update ===================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'] ?? 0;
    $admin_remark = $_POST['admin_remark'] ?? '';
    $score = $_POST['score'] ?? null;

    $update = $pdo->prepare("
        UPDATE submission_students
        SET admin_remark = ?, score = ?
        WHERE submission_id = ? AND student_id = ?
    ");
    $update->execute([$admin_remark, $score, $submission_id, $student_id]);

    $msg = "✅ Student record updated successfully!";
}

include '../includes/header.php';
?>

<a href="dashboard.php" class="btn btn-link mb-3">« Back to Dashboard</a>
<h3>Submission — <?= htmlspecialchars($submission['group_id']) ?></h3>
<p>
    Supervisor: <?= htmlspecialchars($submission['supervisor'] ?? '—') ?> | 
    Personnel: <?= htmlspecialchars($submission['personnel'] ?? '—') ?> | 
    Date: <?= htmlspecialchars($submission['created_at'] ?? '—') ?>
</p>

<p>
    <?php if (!empty($submission['file_name'])): ?>
        <a href="../uploads/<?= rawurlencode($submission['file_name']) ?>" class="btn btn-primary" download>Download File</a>
    <?php else: ?>
        <span class="text-muted">No file uploaded</span>
    <?php endif; ?>
</p>

<?php if ($msg): ?>
    <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<!-- ================= Student Table ================= -->
<div class="table-responsive">
    <table class="table table-bordered table-striped align-middle text-center">
        <thead class="table-secondary">
            <tr>
                <th>#</th>
                <th>Student Name</th>
                <th>Reg No</th>
                <th>Leader Remark</th>
                <th>Admin Remark</th>
                <th>Score</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($students): ?>
                <?php foreach ($students as $i => $st): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($st['name']) ?></td>
                        <td><?= htmlspecialchars($st['regno']) ?></td>
                        <td><?= htmlspecialchars($st['leader_remark'] ?? '—') ?></td>
                        <form method="post">
                            <input type="hidden" name="student_id" value="<?= $st['id'] ?>">
                            <td>
                                <select name="admin_remark" class="form-select form-select-sm">
                                    <option value="">--Select--</option>
                                    <option value="Clear" <?= ($st['admin_remark'] ?? '') === 'Clear' ? 'selected' : '' ?>>Clear</option>
                                    <option value="Not Clear" <?= ($st['admin_remark'] ?? '') === 'Not Clear' ? 'selected' : '' ?>>Not Clear</option>
                                </select>
                            </td>
                            <td>
                                <input type="number" name="score" class="form-control form-control-sm" placeholder="Score" value="<?= htmlspecialchars($st['score'] ?? '') ?>">
                            </td>
                            <td>
                                <button type="submit" class="btn btn-sm btn-primary">Update</button>
                            </td>
                        </form>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" class="text-center text-muted">No students found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="container py-5"></div>
<?php include '../includes/footer.php'; ?>
