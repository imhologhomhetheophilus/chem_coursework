<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../includes/db_connect.php';

// Redirect if not logged in
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$msg = '';

// Handle Admin Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submission_id'])) {
    $sub_id = $_POST['submission_id'];
    $admin_remark = $_POST['admin_remark'] ?? '';
    $score = $_POST['score'] ?? null;

    $stmt = $pdo->prepare("UPDATE submissions SET admin_remark = ?, score = ? WHERE id = ?");
    $stmt->execute([$admin_remark, $score, $sub_id]);
    $msg = "✅ Submission updated successfully!";
}

// Handle search
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
    $sql .= " AND (
        s.group_id LIKE :search 
        OR sp.name LIKE :search 
        OR p.name LIKE :search
    )";
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

    <!-- Navigation Buttons -->
    <div class="row g-2 mb-4 justify-content-center text-center">
        <?php
        $buttons = [
            'Manage Students' => 'manage_students.php',
            'Manage Groups' => 'manage_groups.php',
            'Manage Supervisors' => 'manage_supervisors.php',
            'Manage Personnel' => 'manage_personnel.php',
            'All Submissions' => 'dashboard.php',
            'Logout' => 'logout.php'
        ];
        foreach ($buttons as $label => $link): ?>
            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                <a href="<?= $link ?>" class="btn btn-outline-primary w-100"><?= htmlspecialchars($label) ?></a>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Search Form -->
    <form method="get" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Search by group, supervisor, or personnel" value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-primary" type="submit">Search</button>
            <?php if(!empty($search)): ?>
                <a href="dashboard.php" class="btn btn-outline-secondary">Clear</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Submissions Table -->
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
                        <th>Students</th>
                        <th>Supervisor</th>
                        <th>Personnel</th>
                        <th>File</th>
                        <th>Leader Remark</th>
                        <th>Admin Remark</th>
                        <th>Score</th>
                        <th>Uploaded At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($subs): ?>
                        <?php foreach($subs as $i => $s):
                            $st_query = $pdo->prepare("SELECT name, regno FROM students WHERE group_id = ?");
                            $st_query->execute([$s['group_id']]);
                            $students = $st_query->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td><?= htmlspecialchars($s['group_id']) ?></td>
                            <td class="text-start">
                                <?php if($students):
                                    foreach($students as $st): ?>
                                        <div><?= htmlspecialchars($st['name']) ?> <small>(<?= htmlspecialchars($st['regno']) ?>)</small></div>
                                    <?php endforeach; 
                                else: ?>
                                    <em class="text-muted">No students</em>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($s['supervisor'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($s['personnel'] ?? '—') ?></td>
                            <td>
                                <?php if(!empty($s['file_name'])): ?>
                                    <a href="../uploads/<?= htmlspecialchars($s['file_name']) ?>" target="_blank" class="btn btn-sm btn-outline-info">View</a>
                                <?php else: ?>
                                    <span class="text-muted">No file</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($s['leader_remark'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($s['admin_remark'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($s['score'] ?? '—') ?></td>
                            <td><?= $s['created_at'] ? date('d M Y, h:i A', strtotime($s['created_at'])) : '—' ?></td>
                            <td>
                                <a href="view_submission.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-primary">View</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="11" class="text-center text-muted">No submissions found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="container py-5"></div>
<?php include('../includes/footer.php'); ?>
