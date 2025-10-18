<?php
session_start();
require 'includes/db_connect.php';

// Redirect if not logged in as group leader
if (!isset($_SESSION['group_id'])) {
    header('Location: group_login.php');
    exit;
}

$group_id = $_SESSION['group_id'];
$message = '';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $group_id . '_' . time() . '.' . $ext;
        $target = __DIR__ . '/uploads/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $target)) {
            $stmt = $pdo->prepare("INSERT INTO submissions (group_id, file_name, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$group_id, $filename]);
            $message = "✅ File uploaded successfully!";
        } else {
            $message = "❌ Failed to move uploaded file.";
        }
    } else {
        $message = "❌ File upload error.";
    }
}

// Handle remark and datetime update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submission_id'], $_POST['remark'], $_POST['created_at'])) {
    $sub_id = $_POST['submission_id'];
    $remark = $_POST['remark'];
    $created_at = $_POST['created_at'];

    $stmt = $pdo->prepare("UPDATE submissions SET remark = ?, created_at = ? WHERE id = ? AND group_id = ?");
    $stmt->execute([$remark, $created_at, $sub_id, $group_id]);
    $message = "✅ Remark and submission time updated!";
}

// Fetch students of this group
$students = $pdo->prepare("SELECT name, regno FROM students WHERE group_id = ?");
$students->execute([$group_id]);
$students = $students->fetchAll(PDO::FETCH_ASSOC);

// Fetch previous submissions with supervisor and personnel info
$subs = $pdo->prepare("
    SELECT s.*, sp.name AS supervisor, p.name AS personnel
    FROM submissions s
    LEFT JOIN supervisors sp ON s.supervisor_id = sp.id
    LEFT JOIN personnel p ON s.personnel_id = p.id
    WHERE s.group_id = ?
    ORDER BY s.created_at DESC
");
$subs->execute([$group_id]);
$subs = $subs->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="text-center mb-4">
        <h3>Group <?= htmlspecialchars($group_id) ?> – Coursework Submission</h3>
        <?php if ($message): ?>
            <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
    </div>

    <!-- Students List -->
    <div class="mb-4">
        <h5>Group Members:</h5>
        <?php if ($students): ?>
            <ul>
                <?php foreach ($students as $st): ?>
                    <li><?= htmlspecialchars($st['name']) ?><?= !empty($st['regno']) ? " ({$st['regno']})" : '' ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-muted">No students found for this group.</p>
        <?php endif; ?>
    </div>

    <!-- File Upload Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Upload Coursework</label>
                    <input type="file" name="file" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Submit</button>
            </form>
        </div>
    </div>

    <!-- Previous Submissions -->
    <div class="card mb-4">
        <div class="card-body">
            <h5>Previous Submissions</h5>
            <?php if ($subs): ?>
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-secondary text-center">
                        <tr>
                            <th>#</th>
                            <th>File</th>
                            <th>Supervisor</th>
                            <th>Personnel</th>
                            <th>Remark & Submission Time</th>
                            <th>Submitted At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subs as $i => $s): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <a href="uploads/<?= htmlspecialchars($s['file_name']) ?>" target="_blank">
                                        <?= htmlspecialchars($s['file_name']) ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($s['supervisor'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($s['personnel'] ?? '—') ?></td>
                                <td>
                                    <form method="post" class="d-flex flex-column gap-2">
                                        <input type="hidden" name="submission_id" value="<?= $s['id'] ?>">
                                        <!-- Remark Dropdown -->
                                        <select name="remark" class="form-select form-select-sm">
                                            <option value="">--Remark--</option>
                                            <option value="Clear" <?= ($s['remark'] ?? '') === 'Clear' ? 'selected' : '' ?>>Clear</option>
                                            <option value="Not Clear" <?= ($s['remark'] ?? '') === 'Not Clear' ? 'selected' : '' ?>>Not Clear</option>
                                        </select>
                                        <!-- Datetime Picker -->
                                        <input type="datetime-local" name="created_at" class="form-control form-control-sm"
                                               value="<?= date('Y-m-d\TH:i', strtotime($s['created_at'])) ?>">
                                        <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                    </form>
                                </td>
                                <td><?= date('d-m-Y H:i', strtotime($s['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">No submissions yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="mt-4 text-center">
        <a class="btn btn-outline-secondary" href="logout.php">Logout</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
