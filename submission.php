<?php
session_start();
require_once 'includes/db_connect.php';

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

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

include 'includes/header.php';
?>

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0">My Coursework Submissions</h3>
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

    <div class="card shadow-sm">

        <div class="card-header bg-primary text-white">
            Submission History & Admin Feedback
        </div>

        <div class="card-body p-0 table-responsive">

            <table class="table table-bordered table-striped text-center mb-0">

                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Supervisor</th>
                        <th>Personnel</th>
                        <th>Experiment Date</th>
                        <th>Submitted On</th>
                        <th>File</th>
                        <th>Admin Remark</th>
                        <th>Admin Score</th>
                    </tr>
                </thead>

                <tbody>

                <?php if($subs): ?>
                    <?php foreach($subs as $i => $s): ?>

                        <tr>

                            <td><?= $i + 1 ?></td>

                            <td><?= htmlspecialchars($s['supervisor_name'] ?? '—') ?></td>

                            <td><?= htmlspecialchars($s['personnel_name'] ?? '—') ?></td>

                            <td><?= htmlspecialchars($s['experiment_datetime'] ?? '—') ?></td>

                            <td><?= htmlspecialchars($s['created_at']) ?></td>

                            <td>
                                <?php if(!empty($s['file_path'])): ?>
                                    <a href="<?= htmlspecialchars($s['file_path']) ?>"
                                       target="_blank"
                                       class="btn btn-sm btn-success">
                                        View File
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">No File</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if(!empty($s['admin_remark'])): ?>
                                    <span class="badge bg-info">
                                        <?= htmlspecialchars($s['admin_remark']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">Pending</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if($s['admin_score'] !== null): ?>
                                    <strong><?= htmlspecialchars($s['admin_score']) ?></strong>
                                <?php else: ?>
                                    <span class="text-muted">Pending</span>
                                <?php endif; ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>
                <?php else: ?>

                    <tr>
                        <td colspan="8" class="text-center text-muted">
                            No submissions yet. Please upload your coursework.
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>
    </div>

</div>

<?php include 'includes/footer.php'; ?>