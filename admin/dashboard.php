<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Redirect if not logged in
if (!isset($_SESSION['admin'])) {
    header('Location: index.php');
    exit;
}

require_once '../includes/db_connect.php';

$msg = '';

// =================== HANDLE ADMIN UPDATES ===================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submission_id'])) {
    $sub_id = $_POST['submission_id'];
    $admin_remark = $_POST['admin_remark'] ?? '';
    $score = $_POST['score'] ?? null;

    // ✅ Ensure the columns exist (admin_remark, score)
    $stmt = $pdo->prepare("UPDATE submissions SET admin_remark = ?, score = ? WHERE id = ?");
    $stmt->execute([$admin_remark, $score, $sub_id]);
    $msg = "✅ Submission updated successfully!";
}

// =================== HANDLE SEARCH ===================
$search = $_GET['search'] ?? '';
$sql = "
    SELECT s.*, g.group_id, sp.name AS supervisor, p.name AS personnel, s.leader_remark
    FROM submissions s
    LEFT JOIN groups g ON s.group_id = g.group_id
    LEFT JOIN supervisors sp ON s.supervisor_id = sp.id
    LEFT JOIN personnel p ON s.personnel_id = p.id
    WHERE 1
";

if (!empty($search)) {
    $searchTerm = '%' . $search . '%';
    $sql .= " AND (
        g.group_id LIKE :search 
        OR sp.name LIKE :search 
        OR s.leader_remark LIKE :search 
        OR s.admin_remark LIKE :search 
        OR EXISTS (
            SELECT 1 FROM students st 
            WHERE st.group_id = s.group_id 
            AND (st.name LIKE :search OR st.regno LIKE :search)
        )
    )";
}

$sql .= " ORDER BY s.created_at DESC";
$stmt = $pdo->prepare($sql);
if (!empty($search)) {
    $stmt->bindParam(':search', $searchTerm);
}
$stmt->execute();
$subs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$adminName = $_SESSION['admin'] ?? 'Admin';
include('../includes/header.php');
?>

<div class="container mt-4">
    <div class="text-center mb-4">
        <h1 class="display-6 fw-bold text-primary">🧭 Admin Dashboard</h1>
        <p class="lead mb-0">Welcome, <strong><?= htmlspecialchars($adminName) ?></strong></p>
    </div>

    <?php if (!empty($msg)): ?>
        <div class="alert alert-success text-center" id="successMsg"><?= htmlspecialchars($msg) ?></div>
        <script>
            setTimeout(() => document.getElementById('successMsg').style.display = 'none', 3000);
        </script>
    <?php endif; ?>

    <!-- Navigation Buttons -->
    <div class="row g-2 mb-4 justify-content-center text-center">
        <?php
        $buttons = [
            'Manage Students' => 'manage_students.php',
            'Manage Groups' => 'manage_groups.php',
            'Manage Supervisors' => 'manage_supervisors.php',
            'Manage Personnel' => 'manage_personnel.php',
            'All Submissions' => 'view_submissions.php',
            'Logout' => 'logout.php'
        ];
        foreach ($buttons as $label => $link):
        ?>
            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                <a href="<?= $link ?>" class="btn btn-outline-primary w-100"><?= htmlspecialchars($label) ?></a>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Search Bar -->
    <form method="get" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" class="form-control" 
                   placeholder="🔍 Search by Student Name, Reg No, Group, or Supervisor"
                   value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-primary" type="submit">Search</button>
            <?php if (!empty($search)): ?>
                <a href="dashboard.php" class="btn btn-outline-secondary">Clear</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Uploaded Coursework Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">📂 Uploaded Coursework</h5>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle text-center mb-0">
                    <thead class="table-secondary">
                        <tr>
                            <th>#</th>
                            <th>Group ID</th>
                            <th>Student Names & Reg. Nos</th>
                            <th>Supervisor</th>
                            <th>Personnel</th>
                            <th>File</th>
                            <th>Leader Remark</th>
                            <th>Admin Remark</th>
                            <th>Score</th>
                            <th>Uploaded Date/Time</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($subs)): ?>
                            <?php foreach ($subs as $i => $s): ?>
                                <?php
                                // Fetch students for this group
                                $st_query = $pdo->prepare("SELECT name, regno FROM students WHERE group_id = ?");
                                $st_query->execute([$s['group_id']]);
                                $students = $st_query->fetchAll(PDO::FETCH_ASSOC);
                                ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td class="fw-bold text-primary"><?= htmlspecialchars($s['group_id'] ?? 'N/A') ?></td>
                                    <td class="text-start">
                                        <?php if ($students): ?>
                                            <?php foreach ($students as $st): ?>
                                                <div>
                                                    <?= htmlspecialchars($st['name']) ?> 
                                                    <small class="text-muted">(<?= htmlspecialchars($st['regno']) ?>)</small>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <em class="text-muted">No students found</em>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($s['supervisor'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($s['personnel'] ?? '—') ?></td>
                                    <td>
                                        <?php if (!empty($s['file_name'])): ?>
                                            <a href="../uploads/<?= htmlspecialchars($s['file_name']) ?>" 
                                               target="_blank" class="btn btn-sm btn-outline-info">View File</a>
                                        <?php else: ?>
                                            <span class="text-muted">No file</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($s['leader_remark'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($s['admin_remark'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($s['score'] ?? '—') ?></td>
                                    <td><?= !empty($s['created_at']) ? date('d M Y, h:i A', strtotime($s['created_at'])) : '—' ?></td>
                                    <td>
                                        <form method="post" class="d-flex flex-column gap-2">
                                            <input type="hidden" name="submission_id" value="<?= htmlspecialchars($s['id']) ?>">
                                            <select name="admin_remark" class="form-select form-select-sm">
                                                <option value="">--Select Remark--</option>
                                                <option value="Clear" <?= ($s['admin_remark'] ?? '') === 'Clear' ? 'selected' : '' ?>>Clear</option>
                                                <option value="Not Clear" <?= ($s['admin_remark'] ?? '') === 'Not Clear' ? 'selected' : '' ?>>Not Clear</option>
                                            </select>
                                            <input type="number" name="score" class="form-control form-control-sm" 
                                                   placeholder="Score" value="<?= htmlspecialchars($s['score'] ?? '') ?>">
                                            <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="11" class="text-center text-muted py-3">No submissions found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="container py-5"></div>
<?php include('../includes/footer.php'); ?>
