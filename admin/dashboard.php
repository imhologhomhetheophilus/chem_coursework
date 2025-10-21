<?php
session_start();
require_once '../includes/db_connect.php';

// Redirect if not logged in
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$msg = '';
$admin_msg = '';

// =================== HANDLE STUDENT ADMIN REMARK UPDATES ===================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submission_id'], $_POST['student_id'])) {
        $sub_id = $_POST['submission_id'];
        $student_id = $_POST['student_id'];
        $admin_remark = $_POST['admin_remark'] ?? '';
        $score = $_POST['score'] ?? null;

        $stmt = $pdo->prepare("UPDATE submission_students SET admin_remark = ?, score = ? WHERE submission_id = ? AND student_id = ?");
        $stmt->execute([$admin_remark, $score, $sub_id, $student_id]);
        $msg = "✅ Student record updated successfully!";
    }

    // =================== HANDLE ADD ADMIN ===================
    if (isset($_POST['new_admin_username'], $_POST['new_admin_email'], $_POST['new_admin_pass'])) {
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
    <?php if ($admin_msg): ?>
        <div class="alert alert-success text-center"><?= htmlspecialchars($admin_msg) ?></div>
    <?php endif; ?>

    <!-- ================= Navigation Buttons ================= -->
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

    <!-- ================= Search Form ================= -->
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
                        <th>Student Name</th>
                        <th>Reg No</th>
                        <th>Supervisor</th>
                        <th>Personnel</th>
                        <th>Leader Remark</th>
                        <th>Admin Remark</th>
                        <th>Score</th>
                        <th>Update</th>
                    </tr>
                </thead>
                <tbody>
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
                        ?>
                            <?php if ($students): ?>
                                <?php foreach ($students as $j => $st): ?>
                                    <tr>
                                        <td><?= $i+1 ?>.<?= $j+1 ?></td>
                                        <td><?= htmlspecialchars($s['group_id']) ?></td>
                                        <td><?= htmlspecialchars($st['name']) ?></td>
                                        <td><?= htmlspecialchars($st['regno']) ?></td>
                                        <?php if ($j === 0): ?>
                                            <td rowspan="<?= count($students) ?>"><?= $sup ?></td>
                                            <td rowspan="<?= count($students) ?>"><?= $per ?></td>
                                            <td rowspan="<?= count($students) ?>"><?= htmlspecialchars($st['leader_remark'] ?? '—') ?></td>
                                        <?php endif; ?>
                                        <form method="post">
                                            <input type="hidden" name="submission_id" value="<?= $s['id'] ?>">
                                            <input type="hidden" name="student_id" value="<?= $st['id'] ?>">
                                            <td>
                                                <select name="admin_remark" class="form-select form-select-sm">
                                                    <option value="">--Select--</option>
                                                    <option value="Clear" <?= ($st['admin_remark'] ?? '') === 'Clear' ? 'selected' : '' ?>>Clear</option>
                                                    <option value="Not Clear" <?= ($st['admin_remark'] ?? '') === 'Not Clear' ? 'selected' : '' ?>>Not Clear</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" name="score" class="form-control form-control-sm" placeholder="Score" value="<?= htmlspecialchars($st['score'] ?? '') ?>">
                                            </td>
                                            <td>
                                                <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                            </td>
                                        </form>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td><?= $i+1 ?></td>
                                    <td><?= htmlspecialchars($s['group_id']) ?></td>
                                    <td colspan="8" class="text-center text-muted">No students found</td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="10" class="text-center text-muted">No submissions found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ================= Add Admin Modal ================= -->
<div class="modal fade" id="addAdminModal" tabindex="-1" aria-labelledby="addAdminModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addAdminModalLabel">Add New Admin</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <div class="mb-3">
              <label class="form-label">Username</label>
              <input type="text" name="new_admin_username" class="form-control" required>
          </div>
          <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="new_admin_email" class="form-control" required>
          </div>
          <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" name="new_admin_pass" class="form-control" required>
          </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Add Admin</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </form>
  </div>
</div>

<div class="container py-5"></div>
<?php include('../includes/footer.php'); ?>
