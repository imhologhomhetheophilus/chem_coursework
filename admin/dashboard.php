<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Redirect if not logged in
if (!isset($_SESSION['admin'])) {
    header('Location: index.php');
    exit;
}

require_once '../includes/db_connect.php';

// Handle remark or score update
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submission_id'])) {
    $sub_id = $_POST['submission_id'];
    $remark = $_POST['remark'] ?? '';
    $score = $_POST['score'] ?? null;

    $stmt = $pdo->prepare("UPDATE submissions SET remark = ?, score = ? WHERE id = ?");
    $stmt->execute([$remark, $score, $sub_id]);
    $msg = "✅ Submission updated successfully!";
}

// Fetch all data
$groups = $pdo->query("SELECT * FROM groups ORDER BY group_id")->fetchAll(PDO::FETCH_ASSOC);
$supervisors = $pdo->query("SELECT * FROM supervisors ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$personnel = $pdo->query("SELECT * FROM personnel ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Fetch submissions
$subs = $pdo->query("
    SELECT s.*, g.group_id, sp.name AS supervisor, p.name AS personnel
    FROM submissions s
    LEFT JOIN groups g ON s.group_id = g.group_id
    LEFT JOIN supervisors sp ON s.supervisor_id = sp.id
    LEFT JOIN personnel p ON s.personnel_id = p.id
    ORDER BY s.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

$adminName = $_SESSION['admin'] ?? 'Admin';
include('../includes/header.php');
?>

<div class="container mt-3">

    <!-- Responsive Header -->
    <div class="text-center mb-4">
        <h1 class="display-6 display-md-4 text-primary fw-bold">🧭 Admin Dashboard</h1>
        <p class="lead mb-0">Welcome, <strong><?= htmlspecialchars($adminName) ?></strong> 🎉</p>
    </div>

    <?php if (!empty($msg)): ?>
        <div class="alert alert-success text-center"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <!-- Management Buttons -->
    <div class="row g-2 mb-4 justify-content-center text-center">
        <?php
        $buttons = [
            'Manage Students' => 'manage_students.php',
            'Manage Groups' => 'manage_groups.php',
            'Manage Supervisors' => 'manage_supervisors.php',
            'Manage Personnel' => 'manage_personnel.php',
            'View Submissions' => 'view_submissions.php',
            'All Submissions' => 'view_submissions.php',
            'Logout' => 'logout.php'
        ];
        foreach ($buttons as $label => $link):
        ?>
            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                <a href="<?= $link ?>" class="btn btn-outline-primary w-100 mb-1"><?= $label ?></a>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Submissions Table / Mobile Cards -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">📚 Uploaded Coursework</h5>
        </div>
        <div class="card-body p-0">

            <!-- Desktop Table -->
            <div class="d-none d-md-block table-responsive">
                <table class="table table-bordered table-striped align-middle mb-0">
                    <thead class="table-secondary text-center">
                        <tr>
                            <th>#</th>
                            <th>Group</th>
                            <th>Students</th>
                            <th>Supervisor</th>
                            <th>Personnel</th>
                            <th>File</th>
                            <th>Remark</th>
                            <th>Score</th>
                            <th>Uploaded</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($subs)): ?>
                            <?php foreach ($subs as $i => $s): ?>
                                <?php
                                $st_query = $pdo->prepare("SELECT name, regno FROM students WHERE group_id = ?");
                                $st_query->execute([$s['group_id']]);
                                $students = $st_query->fetchAll(PDO::FETCH_ASSOC);
                                ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td class="fw-bold text-primary"><?= htmlspecialchars($s['group_id'] ?? 'N/A') ?></td>
                                    <td>
                                        <?php if ($students): ?>
                                            <ul class="mb-0 text-start">
                                                <?php foreach ($students as $st): ?>
                                                    <li><?= htmlspecialchars($st['name']) ?> (<?= htmlspecialchars($st['regno'] ?? '—') ?>)</li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php else: ?>
                                            <em class="text-muted">No students</em>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($s['supervisor'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($s['personnel'] ?? '—') ?></td>
                                    <td>
                                        <?php if (!empty($s['file_name'])): ?>
                                            <a href="../uploads/<?= htmlspecialchars($s['file_name']) ?>" target="_blank">View</a>
                                        <?php else: ?>
                                            <span class="text-muted">No file</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($s['remark'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($s['score'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($s['created_at']) ?></td>
                                    <td>
                                        <form method="post" class="d-flex flex-column gap-2">
                                            <input type="hidden" name="submission_id" value="<?= htmlspecialchars($s['id']) ?>">
                                            <select name="remark" class="form-select form-select-sm">
                                                <option value="">--Remark--</option>
                                                <option value="Clear" <?= ($s['remark'] ?? '') === 'Clear' ? 'selected' : '' ?>>Clear</option>
                                                <option value="Not Clear" <?= ($s['remark'] ?? '') === 'Not Clear' ? 'selected' : '' ?>>Not Clear</option>
                                            </select>
                                            <input type="number" name="score" class="form-control form-control-sm" placeholder="Score" value="<?= htmlspecialchars($s['score'] ?? '') ?>">
                                            <button class="btn btn-sm btn-primary mt-1">Update</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="10" class="text-center text-muted">No submissions found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="d-md-none">
                <?php if (!empty($subs)): ?>
                    <?php foreach ($subs as $i => $s): ?>
                        <?php
                        $st_query = $pdo->prepare("SELECT name, regno FROM students WHERE group_id = ?");
                        $st_query->execute([$s['group_id']]);
                        $students = $st_query->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="card-title fw-bold text-primary">Group: <?= htmlspecialchars($s['group_id'] ?? 'N/A') ?></h6>
                                <p class="mb-1"><strong>Supervisor:</strong> <?= htmlspecialchars($s['supervisor'] ?? '—') ?></p>
                                <p class="mb-1"><strong>Personnel:</strong> <?= htmlspecialchars($s['personnel'] ?? '—') ?></p>
                                <p class="mb-1"><strong>Students:</strong>
                                    <?php if ($students): ?>
                                        <ul class="mb-0">
                                            <?php foreach ($students as $st): ?>
                                                <li><?= htmlspecialchars($st['name']) ?> (<?= htmlspecialchars($st['regno'] ?? '—') ?>)</li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <em class="text-muted">No students</em>
                                    <?php endif; ?>
                                </p>
                                <p class="mb-1"><strong>File:</strong>
                                    <?php if (!empty($s['file_name'])): ?>
                                        <a href="../uploads/<?= htmlspecialchars($s['file_name']) ?>" target="_blank">View</a>
                                    <?php else: ?>
                                        <span class="text-muted">No file</span>
                                    <?php endif; ?>
                                </p>
                                <p class="mb-1"><strong>Remark:</strong> <?= htmlspecialchars($s['remark'] ?? '—') ?></p>
                                <p class="mb-1"><strong>Score:</strong> <?= htmlspecialchars($s['score'] ?? '—') ?></p>
                                <p class="mb-1"><strong>Uploaded:</strong> <?= htmlspecialchars($s['created_at']) ?></p>
                                <form method="post" class="d-flex flex-column gap-2 mt-2">
                                    <input type="hidden" name="submission_id" value="<?= htmlspecialchars($s['id']) ?>">
                                    <select name="remark" class="form-select form-select-sm">
                                        <option value="">--Remark--</option>
                                        <option value="Clear" <?= ($s['remark'] ?? '') === 'Clear' ? 'selected' : '' ?>>Clear</option>
                                        <option value="Not Clear" <?= ($s['remark'] ?? '') === 'Not Clear' ? 'selected' : '' ?>>Not Clear</option>
                                    </select>
                                    <input type="number" name="score" class="form-control form-control-sm" placeholder="Score" value="<?= htmlspecialchars($s['score'] ?? '') ?>">
                                    <button class="btn btn-sm btn-primary mt-1">Update</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center text-muted">No submissions found.</div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>
