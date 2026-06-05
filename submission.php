<?php
session_start();
require_once 'includes/db_connect.php';

if (!isset($_SESSION['group_id'])) {
    header('Location: group_login.php');
    exit;
}

$group = $_SESSION['group_id'];

/* ======================
   FETCH GROUP SUBMISSIONS
====================== */

$stmt = $pdo->prepare("
    SELECT 
        s.*,
        g.group_id,
        sup.name AS supervisor_name,
        p.name AS personnel_name
    FROM submissions s
    LEFT JOIN groups g ON s.group_id = g.group_id
    LEFT JOIN supervisors sup ON s.supervisor_id = sup.id
    LEFT JOIN personnel p ON s.personnel_id = p.id
    WHERE s.group_id = ?
    ORDER BY s.created_at DESC
");

$stmt->execute([$group]);
$subs = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Check if user already submitted */
$hasSubmission = !empty($subs);

include 'includes/header.php';
?>

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0">Group Coursework Portal</h3>
            <small class="text-muted">
                Group <?= htmlspecialchars($group) ?>
            </small>
        </div>

        <a href="logout.php" class="btn btn-danger">
            Logout
        </a>
    </div>

    <!-- SUCCESS MESSAGE -->
    <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success">
            ✔ Submission uploaded successfully
        </div>
    <?php endif; ?>

    <?php if(!$hasSubmission): ?>

        <!-- ======================
             SHOW UPLOAD FORM ONLY
        ======================= -->

        <div class="alert alert-info">
            You have not submitted any coursework yet.
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                Submit Coursework
            </div>

            <div class="card-body">

                <form action="upload_submission.php"
                      method="POST"
                      enctype="multipart/form-data">

                    <input type="hidden" name="group_id" value="<?= htmlspecialchars($group) ?>">

                    <div class="mb-3">
                        <label>Supervisor</label>
                        <input type="text" name="supervisor_id" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Personnel</label>
                        <input type="text" name="personnel_id" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Experiment Date</label>
                        <input type="datetime-local" name="experiment_datetime" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Upload File</label>
                        <input type="file" name="coursework_file" class="form-control" required>
                    </div>

                    <button class="btn btn-success w-100">
                        Submit Coursework
                    </button>

                </form>

            </div>
        </div>

    <?php else: ?>

        <!-- ======================
             SHOW SUBMISSION + ADMIN FEEDBACK ONLY
        ======================= -->

        <div class="card shadow-sm">

            <div class="card-header bg-primary text-white">
                My Submission & Admin Feedback
            </div>

            <div class="card-body p-0 table-responsive">

                <table class="table table-bordered table-striped text-center mb-0">

                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Supervisor</th>
                            <th>Personnel</th>
                            <th>Date</th>
                            <th>File</th>
                            <th>Admin Remark</th>
                            <th>Admin Score</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php foreach($subs as $i => $s): ?>

                        <tr>

                            <td><?= $i + 1 ?></td>

                            <td><?= htmlspecialchars($s['supervisor_name'] ?? '—') ?></td>

                            <td><?= htmlspecialchars($s['personnel_name'] ?? '—') ?></td>

                            <td><?= htmlspecialchars($s['experiment_datetime'] ?? '—') ?></td>

                            <td>
                                <?php if(!empty($s['file_path'])): ?>
                                    <a href="<?= htmlspecialchars($s['file_path']) ?>" target="_blank" class="btn btn-success btn-sm">
                                        View File
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">No File</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($s['admin_remark'] ?? 'Pending') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($s['admin_score'] ?? 'Pending') ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </div>

    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>