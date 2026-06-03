<?php
session_start();
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/auth.php';
if (!isset($_SESSION['admin'])) {
    header('Location: group_login.php');
    exit;
}

$adminName = $_SESSION['admin'];

// Fetch submissions with optional search
$search = $_GET['search'] ?? '';
$sql = "
    SELECT 
        s.*,
        g.group_id,
        sup.name AS supervisor,
        p.name AS personnel
    FROM submissions s
    LEFT JOIN groups g ON s.group_id = g.group_id
    LEFT JOIN supervisors sup ON s.supervisor_id = sup.id
    LEFT JOIN personnel p ON s.personnel_id = p.id
    WHERE 1
";

if (!empty($search)) {
    $searchTerm = "%$search%";
    $sql .= " AND (g.group_id LIKE :search OR sup.name LIKE :search OR p.name LIKE :search)";
}

$sql .= " ORDER BY s.created_at DESC";
$stmt = $pdo->prepare($sql);
if (!empty($search)) $stmt->bindParam(':search', $searchTerm);
$stmt->execute();
$subs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch submission students
$submissionStudents = [];
if ($subs) {
    $submissionIds = array_column($subs, 'id');
    $placeholders = implode(',', array_fill(0, count($submissionIds), '?'));
    $stmt2 = $pdo->prepare("SELECT * FROM submission_students WHERE submission_id IN ($placeholders)");
    $stmt2->execute($submissionIds);
    $students = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    foreach ($students as $stu) {
        $submissionStudents[$stu['submission_id']][] = $stu;
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="container mt-4">
    <h1 class="text-center text-primary mb-4">🧭 Admin Dashboard</h1>
    <p class="text-center">Welcome, <strong><?= htmlspecialchars($adminName) ?></strong></p>

    <!-- Submissions Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">📂 Uploaded Coursework & Student Remarks</h5>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-bordered table-striped align-middle text-center mb-0">
                <thead class="table-secondary">
                    <tr>
                        <th>#</th>
                        <th>Group</th>
                        <th>Supervisor</th>
                        <th>Personnel</th>
                        <th>Experiment Date & Time</th>
                        <th>Submission Date & Time</th>
                        <th>File</th>
                        <th>Students & Remarks</th>
                        <th>Admin Remark</th>
                        <th>Admin Score</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($subs):
                        $counter = 1;
                        foreach ($subs as $s):
                            $filePath = ltrim($s['file_path'], '/'); // remove leading slash if exists
                            $fullPath = __DIR__ . '/../' . $filePath; // absolute path
                            $students = $submissionStudents[$s['id']] ?? [];
                    ?>
                    <tr>
                        <td><?= $counter++ ?></td>
                        <td><?= htmlspecialchars($s['group_id']) ?></td>
                        <td><?= htmlspecialchars($s['supervisor'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($s['personnel'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($s['experiment_datetime'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($s['created_at'] ?? '—') ?></td>
                        <td>
                            <?php if (!empty($s['file_path']) && file_exists($fullPath)): ?>
                                <a href="/<?= $filePath ?>" target="_blank" class="btn btn-sm btn-outline-success">View File</a>
                            <?php else: ?>
                                <span class="text-muted">No File</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($students): ?>
                                <ul class="list-unstyled mb-0">
                                    <?php foreach ($students as $stu): ?>
                                        <li><?= htmlspecialchars($stu['student_id']) ?>: <?= htmlspecialchars($stu['remark'] ?? 'Not Cleared') ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <span class="text-muted">No Students</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <select class="form-select form-select-sm admin-remark" data-sub-id="<?= $s['id'] ?>">
                                <option value="">--Select--</option>
                                <option value="Clear" <?= ($s['admin_remark'] ?? '') === 'Clear' ? 'selected' : '' ?>>Clear</option>
                                <option value="Not Clear" <?= ($s['admin_remark'] ?? '') === 'Not Clear' ? 'selected' : '' ?>>Not Clear</option>
                            </select>
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm admin-score" data-sub-id="<?= $s['id'] ?>" value="<?= htmlspecialchars($s['admin_score'] ?? '') ?>" placeholder="Score">
                        </td>
                        <td>
                            <button class="btn btn-sm btn-success update-btn" data-sub-id="<?= $s['id'] ?>">Update</button>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="11" class="text-center text-muted">No submissions found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="updateMsg" class="alert alert-success text-center mt-3 d-none"></div>
</div>

<script>
// Update remark & score
document.querySelectorAll('.update-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
        const subId = btn.dataset.subId;
        const remark = document.querySelector(`.admin-remark[data-sub-id="${subId}"]`).value;
        const score = document.querySelector(`.admin-score[data-sub-id="${subId}"]`).value;

        const res = await fetch('update_remark.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
                submission_id: subId,
                admin_remark: remark,
                admin_score: score
            })
        });

        const text = await res.text();
        const msgBox = document.getElementById('updateMsg');
        msgBox.textContent = text;
        msgBox.classList.remove('d-none');
        setTimeout(() => msgBox.classList.add('d-none'), 3000);
    });
});
</script>

<div class="container py-5" style="margin-bottom:10rem"></div>

<?php include  'includes/footer.php'; ?>
