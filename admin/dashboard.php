<?php
// =================== SESSION + DB CONNECTION ===================
session_start();
require_once '../includes/db_connect.php';

// Redirect if not logged in
if (!isset($_SESSION['admin'])) {
    header('Location: index.php');
    exit;
}

$msg = '';
$admin_msg = '';

// =================== HANDLE ADD ADMIN ===================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_admin_username'])) {
    $username = trim($_POST['new_admin_username']);
    $email = trim($_POST['new_admin_email']);
    $password = password_hash($_POST['new_admin_pass'], PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $password]);
        $admin_msg = "✅ New admin added successfully!";
    } catch (PDOException $e) {
        $admin_msg = "❌ Failed to add admin. Username or email may already exist.";
    }
}

// =================== FETCH SUBMISSIONS ===================
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

$adminName = $_SESSION['admin'];
?>

<?php include('../includes/header.php'); ?>

<div class="container mt-4">
    <h1 class="text-center text-primary mb-4">🧭 Admin Dashboard</h1>
    <p class="text-center">Welcome, <strong><?= htmlspecialchars($adminName) ?></strong></p>

    <?php if ($admin_msg): ?>
        <div class="alert alert-success text-center"><?= htmlspecialchars($admin_msg) ?></div>
    <?php endif; ?>

    <!-- ================= NAVIGATION BUTTONS ================= -->
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
            <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-2">
                <a href="<?= $link ?>" class="btn btn-outline-primary w-100"><?= htmlspecialchars($label) ?></a>
            </div>
        <?php endforeach; ?>

        <!-- Add Admin Button -->
        <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-2">
            <button class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#addAdminModal">Add Admin</button>
        </div>
    </div>

    <!-- ================= TABLE ================= -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">📂 Uploaded Coursework</h5>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-bordered table-striped align-middle text-center mb-0">
                <thead class="table-secondary">
                    <tr>
                        <th>#</th>
                        <th>Group</th>
                        <th>Student Name</th>
                        <th>Reg No</th>
                        <th>Supervisor</th>
                        <th>Personnel</th>
                        <th>Leader Remark</th>
                        <th>File</th>
                        <th>Admin Remark</th>
                        <th>Score</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="submissionTable">
                    <?php if ($subs): ?>
                        <?php foreach ($subs as $i => $s):
                            $st_query = $pdo->prepare("
                                SELECT st.id, st.name, st.regno, ss.remark AS leader_remark, ss.admin_remark, ss.score
                                FROM students st
                                LEFT JOIN submission_students ss
                                    ON st.id = ss.student_id AND ss.submission_id = ?
                                WHERE st.group_id = ?
                            ");
                            $st_query->execute([$s['id'], $s['group_id']]);
                            $students = $st_query->fetchAll(PDO::FETCH_ASSOC);

                            $sup = htmlspecialchars($s['supervisor'] ?? '—');
                            $per = htmlspecialchars($s['personnel'] ?? '—');
                            $file = !empty($s['file_name']) ? "/uploads/" . rawurlencode($s['file_name']) : null;
                        ?>
                            <?php if ($students): ?>
                                <?php foreach ($students as $j => $st): ?>
                                    <tr id="row-<?= $st['id'] ?>">
                                        <td><?= $i+1 ?>.<?= $j+1 ?></td>
                                        <td><?= htmlspecialchars($s['group_id']) ?></td>
                                        <td><?= htmlspecialchars($st['name']) ?></td>
                                        <td><?= htmlspecialchars($st['regno']) ?></td>
                                        <td><?= $sup ?></td>
                                        <td><?= $per ?></td>
                                        <td><?= htmlspecialchars($st['leader_remark'] ?? '—') ?></td>
                                        <td>
                                            <?php if ($file): ?>
                                                <a href="<?= $file ?>" target="_blank" class="btn btn-sm btn-outline-success">View File</a>
                                            <?php else: ?>
                                                <span class="text-muted">No File</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <select class="form-select form-select-sm admin-remark" data-sub-id="<?= $s['id'] ?>" data-student-id="<?= $st['id'] ?>">
                                                <option value="">--Select--</option>
                                                <option value="Clear" <?= ($st['admin_remark'] ?? '') === 'Clear' ? 'selected' : '' ?>>Clear</option>
                                                <option value="Not Clear" <?= ($st['admin_remark'] ?? '') === 'Not Clear' ? 'selected' : '' ?>>Not Clear</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm score-input" data-sub-id="<?= $s['id'] ?>" data-student-id="<?= $st['id'] ?>" value="<?= htmlspecialchars($st['score'] ?? '') ?>" placeholder="Score">
                                        </td>
                                        <td>
                                            <!-- Save Button -->
                                            <button class="btn btn-sm btn-success save-btn mb-1" data-sub-id="<?= $s['id'] ?>" data-student-id="<?= $st['id'] ?>">💾 Save</button>
                                            <!-- Edit Button -->
                                            <a href="edit_submission.php?submission_id=<?= $s['id'] ?>&student_id=<?= $st['id'] ?>" class="btn btn-sm btn-primary mb-1">✏️ Edit</a>
                                            <!-- Delete Button -->
                                            <a href="delete_submission.php?submission_id=<?= $s['id'] ?>&student_id=<?= $st['id'] ?>" class="btn btn-sm btn-danger mb-1" onclick="return confirm('Are you sure you want to delete this submission?')">🗑️ Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="11" class="text-center text-muted">No submissions found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="updateMsg" class="alert alert-success text-center mt-3 d-none"></div>
</div>

<script>
document.querySelectorAll('.save-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
        const subId = btn.dataset.subId;
        const studentId = btn.dataset.studentId;
        const remark = document.querySelector(`.admin-remark[data-sub-id="${subId}"][data-student-id="${studentId}"]`).value;
        const score = document.querySelector(`.score-input[data-sub-id="${subId}"][data-student-id="${studentId}"]`).value;

        const res = await fetch('update_remark.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
                submission_id: subId,
                student_id: studentId,
                admin_remark: remark,
                score: score
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

<?php include('../includes/footer.php'); ?>
