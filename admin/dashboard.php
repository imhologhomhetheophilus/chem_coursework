<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['admin'])) {
    header('Location: index.php');
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
?>

<?php include('../includes/header.php'); ?>

<div class="container mt-4">
    <h1 class="text-center text-primary mb-4">🧭 Admin Dashboard</h1>
    <p class="text-center">Welcome, <strong><?= htmlspecialchars($adminName) ?></strong></p>

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
            <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-2">
                <a href="<?= $link ?>" class="btn btn-outline-primary w-100"><?= htmlspecialchars($label) ?></a>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Add Admin Button -->
    <div class="text-center mb-3">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addAdminModal">➕ Add Admin</button>
    </div>

    <!-- Chat with Group Leaders Button -->
    <div class="text-center mb-3">
        <a href="group/chat_list.php" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Chat with Group Leaders</a>
    </div>

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
                        <th>Group</th>
                        <th>Supervisor</th>
                        <th>Personnel</th>
                        <th>Experiment Date & Time</th>
                        <th>Submission Date & Time</th>
                        <th>File</th>
                        <th>Admin Remark</th>
                        <th>Admin Score</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($subs):
                        $counter = 1;
                        foreach ($subs as $s):
                            $filePath = ltrim($s['file_path'], '/'); // Remove leading slash
                            $fullPath = __DIR__ . '/../' . $filePath;
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
                        <tr><td colspan="10" class="text-center text-muted">No submissions found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="updateMsg" class="alert alert-success text-center mt-3 d-none"></div>
</div>

<!-- Add Admin Modal -->
<div class="modal fade" id="addAdminModal" tabindex="-1" aria-labelledby="addAdminModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" id="addAdminForm">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="addAdminModalLabel">Add New Admin</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="adminFormMsg" class="alert d-none"></div>
        <div class="mb-3">
          <label for="admin_name" class="form-label">Admin Name</label>
          <input type="text" name="admin_name" id="admin_name" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="admin_email" class="form-label">Email</label>
          <input type="email" name="admin_email" id="admin_email" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="admin_password" class="form-label">Password</label>
          <input type="password" name="admin_password" id="admin_password" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Add Admin</button>
      </div>
    </form>
  </div>
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

// Add Admin via AJAX
document.getElementById('addAdminForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const msgBox = document.getElementById('adminFormMsg');

    try {
        const res = await fetch('add_admin.php', { method: 'POST', body: formData });
        const data = await res.json();

        msgBox.classList.remove('d-none','alert-success','alert-danger');
        if(data.status === 'success'){
            msgBox.classList.add('alert-success');
            msgBox.textContent = data.message;
            form.reset();
            const modalEl = document.getElementById('addAdminModal');
            const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            modal.hide();
            setTimeout(() => msgBox.classList.add('d-none'), 2000);
        } else {
            msgBox.classList.add('alert-danger');
            msgBox.textContent = data.message;
        }
    } catch(err){
        msgBox.classList.remove('d-none');
        msgBox.classList.add('alert-danger');
        msgBox.textContent = 'Something went wrong!';
    }
});
</script>

<div class="container py-5" style="margin-bottom:10rem"></div>

<?php include('../includes/footer.php'); ?>
