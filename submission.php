<?php
session_start();
require 'includes/db_connect.php';  // DB connection
require 'includes/auth.php';        // optional authentication helper

// Redirect if not logged in
if (!isset($_SESSION['group_id'])) {
    header('Location: group_login.php');
    exit;
}

$group_id = $_SESSION['group_id'];
$message = '';

// Ensure uploads folder exists
$upload_dir = __DIR__ . '/uploads/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

// Fetch supervisors, personnel, and students
$supervisors = $pdo->query("SELECT * FROM supervisors ORDER BY name")->fetchAll();
$personnel = $pdo->query("SELECT * FROM personnel ORDER BY name")->fetchAll();

// ✅ fixed: reg_no → regno
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
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <h3 class="text-center text-md-start mb-3 mb-md-0">Group <?= htmlspecialchars($group_id) ?> — Coursework Submission</h3>
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

        <div class="mb-4">
            <label class="form-label fw-semibold">Submission Date & Time</label>
            <input type="datetime-local" name="created_at" class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required>
        </div>

        <!-- ✅ Added Leader Remark -->
        <div class="mb-4">
            <label class="form-label fw-semibold">Leader Remark</label>
            <select name="leader_remark" class="form-select" required>
                <option value="">-- Select Remark --</option>
                <option value="Clear">Clear</option>
                <option value="Not Clear">Not Clear</option>
            </select>
        </div>

        <h5 class="fw-bold mb-3">Group Members & Remarks</h5>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead class="table-dark text-center">
                    <tr><th>#</th><th>Reg No</th><th>Name</th><th>Remark</th></tr>
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
                                <th>File</th>
                                <th>Supervisor</th>
                                <th>Personnel</th>
                                <th>Leader Remark</th> <!-- ✅ Added column -->
                                <th>Students & Remarks</th>
                                <th>Uploaded</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subs as $i => $s): ?>
                                <?php
                                // ✅ fixed: reg_no → regno
                                $st_query = $pdo->prepare("
                                    SELECT st.name, st.regno, ss.remark 
                                    FROM submission_students ss
                                    JOIN students st ON ss.student_id = st.id
                                    WHERE ss.submission_id = ?
                                ");
                                $st_query->execute([$s['id']]);
                                $sub_students = $st_query->fetchAll();

                                // Get supervisor name
                                $supervisor_name = '—';
                                if (!empty($s['supervisor_id'])) {
                                    $sup_stmt = $pdo->prepare("SELECT name FROM supervisors WHERE id = ?");
                                    $sup_stmt->execute([$s['supervisor_id']]);
                                    $supervisor_name = $sup_stmt->fetchColumn() ?: '—';
                                }

                                // Get personnel name
                                $personnel_name = '—';
                                if (!empty($s['personnel_id'])) {
                                    $per_stmt = $pdo->prepare("SELECT name FROM personnel WHERE id = ?");
                                    $per_stmt->execute([$s['personnel_id']]);
                                    $personnel_name = $per_stmt->fetchColumn() ?: '—';
                                }
                                ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td>
                                        <?php if (!empty($s['file_name'])): ?>
                                            <a href="uploads/<?= htmlspecialchars($s['file_name']) ?>" target="_blank">
                                                <?= htmlspecialchars($s['file_name']) ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">No file</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($supervisor_name) ?></td>
                                    <td><?= htmlspecialchars($personnel_name) ?></td>
                                    <td><?= htmlspecialchars($s['leader_remark'] ?? '—') ?></td> <!-- ✅ Show leader remark -->
                                    <td>
                                        <?php if ($sub_students): ?>
                                            <?php foreach ($sub_students as $st): ?>
                                                <?= htmlspecialchars($st['name']) ?> (<?= htmlspecialchars($st['regno']) ?>) 
                                                — <strong><?= htmlspecialchars($st['remark']) ?></strong><br>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <em class="text-muted">No student remarks</em>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($s['created_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
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
