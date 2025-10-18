<?php
// ===== PHP LOGIC: MUST BE AT THE VERY TOP =====
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../includes/db_connect.php';

// Redirect if not logged in
if (!isset($_SESSION['admin'])) {
    header('Location: index.php');
    exit;
}

// Handle inline update
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submission_id'])) {
    $sub_id = $_POST['submission_id'];
    $remark = $_POST['remark'] ?? '';
    $score = $_POST['score'] ?? null;

    $stmt = $pdo->prepare("UPDATE submissions SET remark = ?, score = ? WHERE id = ?");
    $stmt->execute([$remark, $score, $sub_id]);
    $msg = "✅ Submission updated successfully!";
}

// Fetch groups and supervisors for filters
$groups = $pdo->query("SELECT group_id FROM groups ORDER BY group_id")->fetchAll(PDO::FETCH_ASSOC);
$supervisors = $pdo->query("SELECT id, name FROM supervisors ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Filter values
$filter_group = $_GET['group'] ?? '';
$filter_supervisor = $_GET['supervisor'] ?? '';
$filter_start = $_GET['start_date'] ?? '';
$filter_end = $_GET['end_date'] ?? '';

// Build query dynamically
$query = "
    SELECT s.*, g.group_id, sp.name AS supervisor, p.name AS personnel
    FROM submissions s
    LEFT JOIN groups g ON s.group_id = g.group_id
    LEFT JOIN supervisors sp ON s.supervisor_id = sp.id
    LEFT JOIN personnel p ON s.personnel_id = p.id
    WHERE 1=1
";
$params = [];

if ($filter_group) {
    $query .= " AND g.group_id = ?";
    $params[] = $filter_group;
}
if ($filter_supervisor) {
    $query .= " AND sp.id = ?";
    $params[] = $filter_supervisor;
}
if ($filter_start) {
    $query .= " AND DATE(s.created_at) >= ?";
    $params[] = $filter_start;
}
if ($filter_end) {
    $query .= " AND DATE(s.created_at) <= ?";
    $params[] = $filter_end;
}

$query .= " ORDER BY s.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$subs = $stmt->fetchAll(PDO::FETCH_ASSOC);

include('../includes/header.php');
?>

<!-- ===== BUTTONS ===== -->
<div class="row text-center mb-4 g-2">
    <div class="col-md-4 col-6"><a href="manage_students.php" class="btn btn-outline-secondary w-100">Manage Students</a></div>
    <div class="col-md-4 col-6"><a href="manage_groups.php" class="btn btn-outline-primary w-100">Manage Groups</a></div>
    <div class="col-md-4 col-6"><a href="manage_supervisors.php" class="btn btn-outline-success w-100">Manage Supervisors</a></div>
    <div class="col-md-4 col-6"><a href="manage_personnel.php" class="btn btn-outline-warning w-100">Manage Personnel</a></div>
    <div class="col-md-4 col-6"><a href="view_submissions.php" class="btn btn-outline-info w-100">View Submissions</a></div>
    <div class="col-md-4 col-6"><a href="logout.php" class="btn btn-outline-danger w-100">Logout</a></div>
</div>

<?php if ($msg): ?>
<div class="alert alert-success text-center"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<!-- ===== FILTER FORM ===== -->
<form class="card p-3 mb-4" method="get">
    <div class="row g-3">
        <div class="col-md-3">
            <label class="form-label fw-semibold">Group</label>
            <select name="group" class="form-select">
                <option value="">-- All Groups --</option>
                <?php foreach ($groups as $g): ?>
                    <option value="<?= htmlspecialchars($g['group_id']) ?>" <?= ($g['group_id'] == $filter_group ? 'selected' : '') ?>>
                        <?= htmlspecialchars($g['group_id']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Supervisor</label>
            <select name="supervisor" class="form-select">
                <option value="">-- All Supervisors --</option>
                <?php foreach ($supervisors as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= ($s['id'] == $filter_supervisor ? 'selected' : '') ?>>
                        <?= htmlspecialchars($s['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Start Date</label>
            <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($filter_start) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">End Date</label>
            <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($filter_end) ?>">
        </div>
    </div>
    <div class="text-end mt-3">
        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="view_submissions.php" class="btn btn-secondary">Reset</a>
    </div>
</form>

<!-- ===== SUBMISSIONS TABLE ===== -->
<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-bordered table-striped align-middle mb-0">
            <thead class="table-dark text-center">
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
                    <?php foreach ($subs as $i => $s): 
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
                                    <option value="">-- Remark --</option>
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
</div>
<div class="container py-5" style="margin-bottom: 10rem;"></div>

<?php include('../includes/footer.php'); ?>
