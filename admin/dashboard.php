<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../includes/db_connect.php';

// Redirect if not logged in
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$msg = '';

// =================== HANDLE ADMIN UPDATES ===================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submission_id'])) {
    $sub_id = $_POST['submission_id'];
    $admin_remark = $_POST['admin_remark'] ?? '';
    $score = $_POST['score'] ?? null;

    $stmt = $pdo->prepare("UPDATE submissions SET admin_remark = ?, score = ? WHERE id = ?");
    $stmt->execute([$admin_remark, $score, $sub_id]);
    $msg = "✅ Submission updated successfully!";
}

// =================== FETCH SUBMISSIONS ===================
$search = $_GET['search'] ?? '';
$sql = "
    SELECT s.*, sp.name AS supervisor, p.name AS personnel
    FROM submissions s
    LEFT JOIN supervisors sp ON s.supervisor_id = sp.id
    LEFT JOIN personnel p ON s.personnel_id = p.id
    WHERE 1
";

if (!empty($search)) {
    $searchTerm = "%$search%";
    $sql .= " AND (s.group_id LIKE :search OR sp.name LIKE :search OR p.name LIKE :search)";
}

$sql .= " ORDER BY s.created_at DESC";
$stmt = $pdo->prepare($sql);
if (!empty($search)) $stmt->bindParam(':search', $searchTerm);
$stmt->execute();
$subs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$adminName = $_SESSION['admin'];
include('../includes/header.php');
?>

<div class="container mt-4">
    <h1 class="text-center text-primary mb-4">🧭 Admin Dashboard</h1>
    <p class="text-center">Welcome, <strong><?= htmlspecialchars($adminName) ?></strong></p>

    <?php if ($msg): ?>
        <div class="alert alert-success text-center"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <!-- Search Form -->
    <form method="get" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Search by group, supervisor, or personnel" value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-primary" type="submit">Search</button>
            <?php if (!empty($search)): ?>
                <a href="dashboard.php" class="btn btn-outline-secondary">Clear</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- ================= Submissions Table ================= -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">📂 Uploaded Coursework</h5>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-bordered table-striped align-middle text-center mb-0">
                <thead class="table-secondary">
                    <tr>
                        <th>#</th>
                        <th>Group ID</th>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Reg No</th>
                        <th>Supervisor</th>
                        <th>Personnel</th>
                        <th>File</th>
                        <th>Leader Remark</th>
                        <th>Admin Remark</th>
                        <th>Score</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($subs): ?>
                        <?php foreach ($subs as $i => $s):
                            // Fetch students with leader remarks
                            $st_query = $pdo->prepare("
                                SELECT st.id, st.name, st.regno, ss.remark AS leader_remark
                                FROM students st
                                LEFT JOIN submission_students ss
                                    ON st.id = ss.student_id AND ss.submission_id = ?
                                WHERE st.group_id = ?
                            ");
                            $st_query->execute([$s['id'], $s['group_id']]);
                            $students = $st_query->fetchAll(PDO::FETCH_ASSOC);

                            $sup = htmlspecialchars($s['supervisor'] ?? '—');
                            $per = htmlspecialchars($s['personnel'] ?? '—');
                            $file_btn = !empty($s['file_name'])
                                ? '<a href="../uploads/'.htmlspecialchars($s['file_name']).'" target="_blank" class="btn btn-sm btn-outline-info">View</a>'
                                : '<span class="text-muted">No file</span>';
                        ?>
                            <?php if ($students): ?>
                                <?php foreach ($students as $j => $st): ?>
                                    <tr>
                                        <td><?= $i+1 ?>.<?= $j+1 ?></td>
                                        <td><?= htmlspecialchars($s['group_id']) ?></td>
                                        <td><?= htmlspecialchars($st['id']) ?></td>
                                        <td><?= htmlspecialchars($st['name']) ?></td>
                                        <td><?= htmlspecialchars($st['regno']) ?></td>
                                        <?php if ($j === 0): ?>
                                            <td rowspan="<?= count($students) ?>"><?= $sup ?></td>
                                            <td rowspan="<?= count($students) ?>"><?= $per ?></td>
                                            <td rowspan="<?= count($students) ?>"><?= $file_btn ?></td>
                                            <td rowspan="<?= count($students) ?>"><?= htmlspecialchars($s['leader_remark'] ?? '—') ?></td>
                                            <td rowspan="<?= count($students) ?>">
                                                <form method="post" class="d-flex gap-2">
                                                    <input type="hidden" name="submission_id" value="<?= $s['id'] ?>">
                                                    <select name="admin_remark" class="form-select form-select-sm">
                                                        <option value="">--Select--</option>
                                                        <option value="Clear" <?= ($s['admin_remark'] ?? '') === 'Clear' ? 'selected' : '' ?>>Clear</option>
                                                        <option value="Not Clear" <?= ($s['admin_remark'] ?? '') === 'Not Clear' ? 'selected' : '' ?>>Not Clear</option>
                                                    </select>
                                                    <input type="number" name="score" class="form-control form-control-sm" placeholder="Score" value="<?= htmlspecialchars($s['score'] ?? '') ?>">
                                                    <button type="submit" class="btn btn-sm btn-primary">Edit</button>
                                                </form>
                                            </td>
                                            <td rowspan="<?= count($students) ?>"></td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td><?= $i+1 ?></td>
                                    <td><?= htmlspecialchars($s['group_id']) ?></td>
                                    <td colspan="10" class="text-center text-muted">No students found</td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="12" class="text-center text-muted">No submissions found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="container py-5"></div>
<?php include('../includes/footer.php'); ?>
