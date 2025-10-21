<?php
require '../includes/db_connect.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Redirect if not logged in
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$submission_id = $_GET['id'] ?? 0;

// Fetch submission details
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

// =================== HANDLE ADMIN REMARK + SCORE UPDATE ===================
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

<div class="container my-5">

    <!-- ====== PAGE HEADER ====== -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-primary mb-0">📘 Submission Details</h3>
        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">« Back to Dashboard</a>
    </div>

    <!-- ====== NAVIGATION BUTTONS ====== -->
    <div class="row g-2 mb-4 text-center">
        <?php
        $buttons = [
            'Manage Students' => 'manage_students.php',
            'Manage Groups' => 'manage_groups.php',
            'Manage Supervisors' => 'manage_supervisors.php',
            'Manage Personnel' => 'manage_personnel.php',
            'All Submissions' => 'dashboard.php',
            'View Reports' => 'reports.php',
            'Logout' => 'logout.php'
        ];
        foreach ($buttons as $label => $link): ?>
            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                <a href="<?= $link ?>" class="btn btn-outline-primary w-100"><?= htmlspecialchars($label) ?></a>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- ====== SUBMISSION INFO CARD ====== -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">📄 Submission Info</h5>
        </div>
        <div class="card-body">
            <p><strong>Group ID:</strong> <?= htmlspecialchars($submission['group_id']) ?></p>
            <p><strong>Supervisor:</strong> <?= htmlspecialchars($submission['supervisor'] ?? '—') ?></p>
            <p><strong>Personnel:</strong> <?= htmlspecialchars($submission['personnel'] ?? '—') ?></p>
            <p><strong>Date Submitted:</strong> <?= htmlspecialchars($submission['created_at'] ?? '—') ?></p>

            <?php if (!empty($submission['file_name'])): ?>
                <div class="d-flex gap-2">
                    <a href="../uploads/<?= rawurlencode($submission['file_name']) ?>" 
                       target="_blank" class="btn btn-outline-primary btn-sm">
                       👁️ View File
                    </a>
                    <a href="../uploads/<?= rawurlencode($submission['file_name']) ?>" 
                       download class="btn btn-success btn-sm">
                       ⬇️ Download File
                    </a>
                </div>
            <?php else: ?>
                <span class="text-muted">No file uploaded</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- ====== SUCCESS MESSAGE ====== -->
    <?php if ($msg): ?>
        <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
            <?= htmlspecialchars($msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- ====== STUDENT TABLE ====== -->
    <div class="card shadow-sm">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">👩‍🎓 Student Records</h5>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped align-middle text-center">
                <thead class="table-dark">
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
                                        <input type="number" name="score" class="form-control form-control-sm"
                                               placeholder="Score"
                                               value="<?= htmlspecialchars($st['score'] ?? '') ?>">
                                    </td>
                                    <td>
                                        <button type="submit" class="btn btn-sm btn-success">💾 Update</button>
                                    </td>
                                </form>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No students found for this group</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<div class="container py-5"></div>

<?php include '../includes/footer.php'; ?>
