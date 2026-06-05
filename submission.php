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

$stmt = $pdo->prepare("
    SELECT 
        s.*,
        sup.name AS supervisor_name,
        p.name AS personnel_name
    FROM submissions s
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
            <h3>Submission Dashboard (Pro)</h3>
            <small class="text-muted">Group <?= htmlspecialchars($group) ?></small>
        </div>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>

    <div id="submissionGrid" class="row">

        <?php foreach ($subs as $s): ?>

            <?php
                $score = (float)($s['admin_score'] ?? 0);
                $progress = min(100, max(0, $score));

                $status =
                    empty($s['admin_remark']) ? 'Pending' :
                    ($score > 0 ? 'Graded' : 'Reviewed');
            ?>

            <div class="col-md-6 mb-4">

                <div class="card shadow-sm border-0">

                    <div class="card-body">

                        <!-- HEADER -->
                        <div class="d-flex justify-content-between">
                            <h5>📄 Submission #<?= $s['id'] ?></h5>

                            <span class="badge 
                                <?= $status === 'Pending' ? 'bg-warning' : 'bg-success' ?>">
                                <?= $status ?>
                            </span>
                        </div>

                        <hr>

                        <!-- DETAILS -->
                        <p><strong>Supervisor:</strong> <?= htmlspecialchars($s['supervisor_name'] ?? '—') ?></p>
                        <p><strong>Personnel:</strong> <?= htmlspecialchars($s['personnel_name'] ?? '—') ?></p>
                        <p><strong>Submitted:</strong> <?= htmlspecialchars($s['created_at']) ?></p>
                        <p><strong>Experiment:</strong> <?= htmlspecialchars($s['experiment_datetime'] ?? '—') ?></p>

                        <!-- FILE -->
                        <div class="mb-2">
                            <?php if (!empty($s['file_path'])): ?>
                                <a href="<?= htmlspecialchars($s['file_path']) ?>"
                                   target="_blank"
                                   class="btn btn-sm btn-outline-primary">
                                    View File
                                </a>

                                <a href="<?= htmlspecialchars($s['file_path']) ?>"
                                   download
                                   class="btn btn-sm btn-outline-secondary">
                                    Download
                                </a>
                            <?php endif; ?>
                        </div>

                        <!-- ADMIN FEEDBACK -->
                        <div class="bg-light p-2 rounded mb-2">
                            <strong>Admin Feedback</strong><br>

                            <?php if (!empty($s['admin_remark'])): ?>
                                <span><?= htmlspecialchars($s['admin_remark']) ?></span>
                            <?php else: ?>
                                <span class="text-muted">Awaiting review...</span>
                            <?php endif; ?>
                        </div>

                        <!-- SCORE -->
                        <div class="mb-2">
                            <strong>Score: <?= $score ?>/100</strong>

                            <div class="progress mt-1">
                                <div class="progress-bar"
                                     role="progressbar"
                                     style="width: <?= $progress ?>%">
                                </div>
                            </div>
                        </div>

                        <!-- TIMELINE STYLE -->
                        <small class="text-muted">
                            Last updated: <?= date('d M Y H:i', strtotime($s['created_at'])) ?>
                        </small>

                    </div>
                </div>

            </div>

        <?php endforeach; ?>

    </div>

</div>

<!-- PRO LIVE UPDATE SYSTEM -->
<script>
async function refreshSubmissions() {
    const res = await fetch('fetch_submissions.php');
    const html = await res.text();
    document.getElementById('submissionGrid').innerHTML = html;
}

// refresh every 10 seconds (PRO behavior)
setInterval(refreshSubmissions, 10000);
</script>

<?php include 'includes/footer.php'; ?>