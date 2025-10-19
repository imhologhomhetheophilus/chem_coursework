<?php
session_start();
require 'includes/db_connect.php';  // Matches your login
require 'includes/auth.php';        // Optional if you have auth functions

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
$students = $pdo->prepare("SELECT id, name, reg_no FROM students WHERE group_id = ?");
$students->execute([$group_id]);
$students = $students->fetchAll();

// Fetch previous submissions
$subs = $pdo->prepare("SELECT * FROM submissions WHERE group_id = ? ORDER BY created_at DESC");
$subs->execute([$group_id]);
$subs = $subs->fetchAll();

include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Group <?= htmlspecialchars($group_id) ?> — Coursework Submission</h3>
        <a class="btn btn-outline-secondary" href="logout.php">Logout</a>
    </div>

    <!-- Submission Form -->
    <form action="handle_submit.php" method="post" enctype="multipart/form-data" class="card p-3 mb-4">
        <input type="hidden" name="group_id" value="<?= htmlspecialchars($group_id) ?>">

        <div class="row mb-2">
            <div class="col-md-6">
                <label class="form-label">Supervisor</label>
                <select name="supervisor_id" class="form-select" required>
                    <option value="">-- Select Supervisor --</option>
                    <?php foreach ($supervisors as $sup): ?>
                        <option value="<?= $sup['id'] ?>"><?= htmlspecialchars($sup['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Lab Personnel</label>
                <select name="personnel_id" class="form-select" required>
                    <option value="">-- Select Personnel --</option>
                    <?php foreach ($personnel as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Upload Coursework (PDF/DOCX)</label>
            <input type="file" name="file" class="form-control" accept=".pdf,.docx" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Submission Date & Time</label>
            <input type="datetime-local" name="created_at" class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required>
        </div>

        <h5>Group Members & Remarks</h5>
        <table class="table table-striped">
            <thead><tr><th>#</th><th>Reg No</th><th>Name</th><th>Remark</th></tr></thead>
            <tbody>
                <?php foreach ($students as $i => $st): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($st['reg_no']) ?></td>
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

        <div class="text-center mt-3">
            <button class="btn btn-success">Submit Coursework</button>
        </div>
    </form>

    <!-- Previous Submissions -->
    <div class="card mb-4">
        <div class="card-body">
            <h5>Previous Submissions</h5>
            <?php if ($subs): ?>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>File</th>
                            <th>Supervisor</th>
                            <th>Personnel</th>
                            <th>Students & Remarks</th>
                            <th>Uploaded</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subs as $i => $s): ?>
                            <?php
                            $st_query = $pdo->prepare("SELECT st.name, st.reg_no, ss.remark 
                                                       FROM submission_students ss
                                                       JOIN students st ON ss.student_id = st.id
                                                       WHERE ss.submission_id = ?");
                            $st_query->execute([$s['id']]);
                            $sub_students = $st_query->fetchAll();
                            ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><a href="uploads/<?= htmlspecialchars($s['file_name']) ?>" target="_blank"><?= htmlspecialchars($s['file_name']) ?></a></td>
                                <td>
                                    <?= htmlspecialchars($pdo->query("SELECT name FROM supervisors WHERE id={$s['supervisor_id']}")->fetchColumn() ?? '—') ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($pdo->query("SELECT name FROM personnel WHERE id={$s['personnel_id']}")->fetchColumn() ?? '—') ?>
                                </td>
                                <td>
                                    <?php foreach ($sub_students as $st): ?>
                                        <?= htmlspecialchars($st['name']) ?> (<?= htmlspecialchars($st['reg_no']) ?>) - <strong><?= htmlspecialchars($st['remark']) ?></strong><br>
                                    <?php endforeach; ?>
                                </td>
                                <td><?= htmlspecialchars($s['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">No submissions yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
