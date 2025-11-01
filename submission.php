<?php
session_start();
require 'includes/db_connect.php';
require 'includes/auth.php';

if (!isset($_SESSION['group_id'])) {
    header('Location: group_login.php');
    exit;
}

$group_id = $_SESSION['group_id'];

// Ensure uploads folder exists
$upload_dir = __DIR__ . '/uploads/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

// Fetch supervisors and personnel
$supervisors = $pdo->query("SELECT * FROM supervisors ORDER BY name")->fetchAll();
$personnel = $pdo->query("SELECT * FROM personnel ORDER BY name")->fetchAll();

// Fetch students in the group
$students_stmt = $pdo->prepare("SELECT id, name, regno FROM students WHERE group_id = ?");
$students_stmt->execute([$group_id]);
$students = $students_stmt->fetchAll();

// Fetch previous submissions
$subs_stmt = $pdo->prepare("SELECT * FROM submissions WHERE group_id = ? ORDER BY created_at DESC");
$subs_stmt->execute([$group_id]);
$subs = $subs_stmt->fetchAll();

include 'includes/header.php';
?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Group <?= htmlspecialchars($group_id) ?> — Coursework Submission</h3>
        <a class="btn btn-outline-secondary" href="logout.php">Logout</a>
    </div>

    <!-- ================= Submission Form ================= -->
    <form action="handle_submit.php" method="post" enctype="multipart/form-data" class="card p-4 shadow-sm mb-5">
        <input type="hidden" name="group_id" value="<?= htmlspecialchars($group_id) ?>">

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Supervisor</label>
                <select name="supervisor_id" class="form-select" required>
                    <option value="">-- Select Supervisor --</option>
                    <?php foreach ($supervisors as $sup): ?>
                        <option value="<?= $sup['id'] ?>"><?= htmlspecialchars($sup['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Lab Personnel</label>
                <select name="personnel_id" class="form-select" required>
                    <option value="">-- Select Personnel --</option>
                    <?php foreach ($personnel as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Upload Coursework (PDF/DOCX)</label>
            <input type="file" name="file" class="form-control" accept=".pdf,.docx" required>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Submission Date & Time</label>
            <input type="datetime-local" name="created_at" class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Experiment Date & Time</label>
            <input type="datetime-local" name="experiment_datetime" class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required>
        </div>

        <h5 class="fw-bold mb-3">Group Members & Remarks</h5>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead class="table-dark text-center">
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
                            <td>
                                <input type="hidden" name="student_ids[]" value="<?= $st['id'] ?>">
                                <select name="remark_<?= $st['id'] ?>" class="form-select form-select-sm">
                                    <option value="Not Cleared">Not Cleared</option>
                                    <option value="Cleared">Cleared</option>
                                </select>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="text-center mt-3">
            <button class="btn btn-success px-4">Submit Coursework</button>
        </div>
    </form>

    <!-- ================= Previous Submissions ================= -->
    <div class="card shadow-sm mb-5">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Previous Submissions</h5>
            <?php if ($subs): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-dark text-center">
                            <tr>
                                <th>#</th>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Reg No</th>
                                <th>Remark</th>
                                <th>Submission Date</th>
                                <th>Experiment Date</th>
                                <th>Admin Remark</th>
                                <th>Admin Score</th>
                                <th>Edit</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $row_no = 1;
                            foreach ($subs as $sub):
                                $st_query = $pdo->prepare("
                                    SELECT st.id, st.name, st.regno, ss.remark 
                                    FROM submission_students ss
                                    JOIN students st ON ss.student_id = st.id
                                    WHERE ss.submission_id = ?
                                ");
                                $st_query->execute([$sub['id']]);
                                $sub_students = $st_query->fetchAll();
                                foreach ($sub_students as $st):
                            ?>
                            <tr>
                                <td><?= $row_no++ ?></td>
                                <td><?= htmlspecialchars($st['id']) ?></td>
                                <td><?= htmlspecialchars($st['name']) ?></td>
                                <td><?= htmlspecialchars($st['regno']) ?></td>
                                <td><?= htmlspecialchars($st['remark']) ?></td>
                                <td><?= date('Y-m-d H:i', strtotime($sub['created_at'])) ?></td>
                                <td><?= date('Y-m-d H:i', strtotime($sub['experiment_datetime'])) ?></td>
                                <td><?= htmlspecialchars($sub['admin_remark'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($sub['admin_score'] ?? '-') ?></td>
                                <td>
                                    <a href="edit_submission.php?sub_id=<?= $sub['id'] ?>&student_id=<?= $st['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                </td>
                               
                            </tr>
                            <?php endforeach; endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">No submissions yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
