<?php
session_start();
require 'includes/db_connect.php';

// Redirect if not logged in
if (!isset($_SESSION['group_id'])) {
    header('Location: group_login.php');
    exit;
}

$group_id = $_SESSION['group_id'];

// Fetch submissions
$subs_query = $pdo->prepare("
    SELECT s.*, g.group_id 
    FROM submissions s
    JOIN groups g ON s.group_id = g.group_id
    WHERE s.group_id = ?
    ORDER BY s.created_at DESC
");
$subs_query->execute([$group_id]);
$subs = $subs_query->fetchAll(PDO::FETCH_ASSOC);

// Fetch supervisors
$supervisors = $pdo->query("SELECT id, name FROM supervisors ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Fetch personnel
$personnel = $pdo->query("SELECT id, name FROM personnel ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<div class="container my-5">
    <h3 class="text-center mb-4">Submission Records - Group <?= htmlspecialchars($group_id) ?></h3>

    <?php if (empty($subs)): ?>
        <div class="alert alert-info text-center">No submissions found for this group.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>File Name</th>
                        <th>Supervisor</th>
                        <th>Lab Personnel</th>
                        <th>Students & Remarks</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subs as $i => $s): ?>
                        <?php
                        // Fetch students for this submission
                        $st_query = $pdo->prepare("
                            SELECT st.id, st.name, st.reg_no, ss.remark 
                            FROM submission_students ss
                            JOIN students st ON ss.student_id = st.id
                            WHERE ss.submission_id = ?
                        ");
                        $st_query->execute([$s['id']]);
                        $sub_students = $st_query->fetchAll(PDO::FETCH_ASSOC);

                        // Get supervisor name
                        $supervisor_name = $pdo->prepare("SELECT name FROM supervisors WHERE id = ?");
                        $supervisor_name->execute([$s['supervisor_id']]);
                        $supervisor_name = $supervisor_name->fetchColumn() ?: '—';

                        // Get personnel name
                        $personnel_name = $pdo->prepare("SELECT name FROM personnel WHERE id = ?");
                        $personnel_name->execute([$s['personnel_id']]);
                        $personnel_name = $personnel_name->fetchColumn() ?: '—';
                        ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td>
                                <a href="uploads/<?= htmlspecialchars($s['file_name']) ?>" target="_blank">
                                    <?= htmlspecialchars($s['file_name']) ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($supervisor_name) ?></td>
                            <td><?= htmlspecialchars($personnel_name) ?></td>
                            <td>
                                <?php foreach ($sub_students as $st): ?>
                                    <?= htmlspecialchars($st['name']) ?> (<?= htmlspecialchars($st['reg_no']) ?>) - 
                                    <strong><?= htmlspecialchars($st['remark']) ?></strong><br>
                                <?php endforeach; ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($s['created_at']) ?><br>
                                <button class="btn btn-sm btn-warning mt-1" data-bs-toggle="modal" data-bs-target="#editModal<?= $s['id'] ?>">Edit</button>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModal<?= $s['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <form action="edit_submission.php" method="post" enctype="multipart/form-data">
                                        <div class="modal-header bg-dark text-white">
                                            <h5 class="modal-title">Edit Submission #<?= $i + 1 ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="submission_id" value="<?= $s['id'] ?>">

                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Supervisor</label>
                                                    <select name="supervisor_id" class="form-select" required>
                                                        <?php foreach ($supervisors as $sup): ?>
                                                            <option value="<?= $sup['id'] ?>" <?= $sup['id'] == $s['supervisor_id'] ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($sup['name']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Lab Personnel</label>
                                                    <select name="personnel_id" class="form-select" required>
                                                        <?php foreach ($personnel as $p): ?>
                                                            <option value="<?= $p['id'] ?>" <?= $p['id'] == $s['personnel_id'] ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($p['name']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Replace File (optional)</label>
                                                <input type="file" name="file" class="form-control" accept=".pdf,.docx">
                                            </div>

                                            <h6>Student Remarks</h6>
                                            <table class="table table-sm table-bordered">
                                                <thead>
                                                    <tr><th>Name</th><th>Reg No</th><th>Remark</th></tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($sub_students as $st): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($st['name']) ?></td>
                                                            <td><?= htmlspecialchars($st['reg_no']) ?></td>
                                                            <td>
                                                                <input type="hidden" name="student_ids[]" value="<?= $st['id'] ?>">
                                                                <select name="remark_<?= $st['id'] ?>" class="form-select form-select-sm">
                                                                    <option value="Not Cleared" <?= $st['remark'] == 'Not Cleared' ? 'selected' : '' ?>>Not Cleared</option>
                                                                    <option value="Cleared" <?= $st['remark'] == 'Cleared' ? 'selected' : '' ?>>Cleared</option>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-success">Save Changes</button>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
