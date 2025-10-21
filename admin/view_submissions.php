<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/db_connect.php';

// Check admin login
if (!isset($_SESSION['admin'])) {
    header('Location: index.php');
    exit;
}

// Fetch all submissions
$stmt = $pdo->query("
    SELECT s.id, s.group_id, s.file_name, s.created_at,
           g.group_name, g.supervisor, g.personnel
    FROM submissions s
    LEFT JOIN groups g ON s.group_id = g.id
    ORDER BY s.created_at DESC
");
$subs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - View Submissions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php include 'header.php'; ?>

<div class="container mt-4">
    <h3 class="mb-4 text-center">📂 Submitted Files</h3>

    <div class="d-flex justify-content-end mb-3">
        <a href="dashboard.php" class="btn btn-secondary btn-sm">⬅ Back to Dashboard</a>
    </div>

    <table class="table table-bordered table-hover bg-white">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Group</th>
                <th>Supervisor</th>
                <th>Personnel</th>
                <th>File</th>
                <th>Submitted At</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($subs): ?>
                <?php foreach ($subs as $i => $s): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($s['group_id']) ?></td>
                        <td><?= htmlspecialchars($s['supervisor'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($s['personnel'] ?? '—') ?></td>
                        <td>
                            <?php if (!empty($s['file_name'])): ?>
                                <a href="/uploads/<?= rawurlencode($s['file_name']) ?>" 
                                   target="_blank" 
                                   class="btn btn-sm btn-success">
                                   👁️ View File
                                </a>
                            <?php else: ?>
                                <span class="text-muted">No file</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($s['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center text-muted">No submissions yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
