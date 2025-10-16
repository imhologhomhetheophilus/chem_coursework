<?php
include '../includes/db_connect.php';
require '../includes/auth.php';
if (session_status() === PHP_SESSION_NONE) session_start();
require_admin();

// Fetch submissions with supervisor & personnel names
$st = $pdo->query('
    SELECT s.*, sp.name AS supervisor, p.name AS personnel 
    FROM submissions s 
    LEFT JOIN supervisors sp ON s.supervisor_id = sp.id 
    LEFT JOIN personnel p ON s.personnel_id = p.id 
    ORDER BY s.created_at DESC
');
$subs = $st->fetchAll();

include '../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Admin — Submissions</h3>
    <div>
        <a class="btn btn-sm btn-primary" href="manage_supervisors.php">Supervisors</a>
        <a class="btn btn-sm btn-primary" href="manage_personnel.php">Personnel</a>
        <a class="btn btn-sm btn-primary" href="manage_groups.php">Groups</a>
        <a class="btn btn-sm btn-primary" href="manage_students.php">Students</a>
        <a class="btn btn-sm btn-secondary m-2" href="logout.php">Logout</a>
    </div>
</div>

<table class="table table-striped table-bordered">
    <thead class="table-primary">
        <tr>
            <th>#</th>
            <th>Group</th>
            <th>Supervisor</th>
            <th>Personnel</th>
            <th>File</th>
            <th>Date</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($subs as $i=>$s): ?>
        <tr>
            <td><?= ($i + 1) ?></td>
            <td><?= htmlspecialchars($s['group_id'] ?? '') ?></td>
            <td><?= htmlspecialchars($s['supervisor'] ?? '') ?></td>
            <td><?= htmlspecialchars($s['personnel'] ?? '') ?></td>
            <td>
                <?php if(!empty($s['file_name'])): ?>
                    <a href="../uploads/<?= htmlspecialchars($s['file_name']) ?>" target="_blank">View File</a>
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($s['created_at'] ?? '') ?></td>
            <td>
                <a class="btn btn-sm btn-danger" href="delete_submission.php?id=<?= $s['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="container py-5" style="margin-bottom: 10rem;"></div>
<?php include '../includes/footer.php'; ?>
