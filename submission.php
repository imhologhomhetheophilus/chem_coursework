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

// Fetch students, supervisors, personnel
$students = $pdo->prepare("SELECT id, name, regno FROM students WHERE group_id = ?");
$students->execute([$group_id]);
$students = $students->fetchAll(PDO::FETCH_ASSOC);

$supervisors = $pdo->query("SELECT id, name FROM supervisors ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$personnel = $pdo->query("SELECT id, name FROM personnel ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $submission_time = $_POST['created_at'] ?? date('Y-m-d H:i:s');
    $supervisor_id = $_POST['supervisor_id'] ?? null;
    $personnel_id = $_POST['personnel_id'] ?? null;
    $remark = $_POST['remark'] ?? null;
    $student_ids = $_POST['student_ids'] ?? [];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $group_id . '_' . time() . '.' . $ext;
        $target = __DIR__ . '/uploads/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $target)) {
            $stmt = $pdo->prepare("
                INSERT INTO submissions (group_id, file_name, created_at, supervisor_id, personnel_id, remark) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$group_id, $filename, $submission_time, $supervisor_id, $personnel_id, $remark]);
            $submission_id = $pdo->lastInsertId();

            foreach ($student_ids as $sid) {
                $pdo->prepare("INSERT INTO submission_students (submission_id, student_id) VALUES (?, ?)")
                    ->execute([$submission_id, $sid]);
            }

            $message = "✅ File uploaded successfully!";
        } else {
            $message = "❌ Failed to move uploaded file.";
        }
    } else {
        $message = "❌ File upload error.";
    }
}

// Handle updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_submission_id'])) {
    $sub_id = $_POST['edit_submission_id'];
    $submission_time = $_POST['created_at'] ?? date('Y-m-d H:i:s');
    $supervisor_id = $_POST['supervisor_id'] ?? null;
    $personnel_id = $_POST['personnel_id'] ?? null;
    $remark = $_POST['remark'] ?? null;
    $student_ids = $_POST['student_ids'] ?? [];

    // Update submission
    $stmt = $pdo->prepare("
        UPDATE submissions 
        SET supervisor_id = ?, personnel_id = ?, created_at = ?, remark = ? 
        WHERE id = ? AND group_id = ?
    ");
    $stmt->execute([$supervisor_id, $personnel_id, $submission_time, $remark, $sub_id, $group_id]);

    // Update students
    $pdo->prepare("DELETE FROM submission_students WHERE submission_id = ?")->execute([$sub_id]);
    foreach ($student_ids as $sid) {
        $pdo->prepare("INSERT INTO submission_students (submission_id, student_id) VALUES (?, ?)")
            ->execute([$sub_id, $sid]);
    }

    $message = "✅ Submission updated successfully!";
}

// Fetch previous submissions
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

    <!-- New Submission Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Select Students</label>
                    <select name="student_ids[]" class="form-select" multiple required>
                        <?php foreach ($students as $st): ?>
                            <option value="<?= $st['id'] ?>"><?= htmlspecialchars($st['name']) ?><?= !empty($st['regno']) ? " ({$st['regno']})" : '' ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple students.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Supervisor</label>
                    <select name="supervisor_id" class="form-select" required>
                        <option value="">-- Select Supervisor --</option>
                        <?php foreach ($supervisors as $sup): ?>
                            <option value="<?= $sup['id'] ?>"><?= htmlspecialchars($sup['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Personnel</label>
                    <select name="personnel_id" class="form-select" required>
                        <option value="">-- Select Personnel --</option>
                        <?php foreach ($personnel as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Submission Time</label>
                    <input type="datetime-local" name="created_at" class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Remark</label>
                    <select name="remark" class="form-select" required>
                        <option value="">-- Select Remark --</option>
                        <option value="Clear">Clear</option>
                        <option value="Not Clear">Not Clear</option>
                    </select>
                </div>

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
                            <th>Students</th>
                            <th>Supervisor</th>
                            <th>Personnel</th>
                            <th>Remark</th>
                            <th>Submission Time</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subs as $i => $s): ?>
                            <?php
                            // Fetch students for this submission
                            $st_query = $pdo->prepare("
                                SELECT st.id, st.name, st.regno 
                                FROM submission_students ss
                                JOIN students st ON ss.student_id = st.id
                                WHERE ss.submission_id = ?
                            ");
                            $st_query->execute([$s['id']]);
                            $sub_students = $st_query->fetchAll(PDO::FETCH_ASSOC);
                            ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><a href="uploads/<?= htmlspecialchars($s['file_name']) ?>" target="_blank"><?= htmlspecialchars($s['file_name']) ?></a></td>
                                <td>
                                    <form method="post">
                                        <input type="hidden" name="edit_submission_id" value="<?= $s['id'] ?>">
                                        <select name="student_ids[]" class="form-select form-select-sm" multiple required>
                                            <?php foreach ($students as $st): ?>
                                                <option value="<?= $st['id'] ?>" <?= in_array($st['id'], array_column($sub_students, 'id')) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($st['name']) ?><?= !empty($st['regno']) ? " ({$st['regno']})" : '' ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                </td>
                                <td>
                                    <select name="supervisor_id" class="form-select form-select-sm" required>
                                        <option value="">-- Supervisor --</option>
                                        <?php foreach ($supervisors as $sup): ?>
                                            <option value="<?= $sup['id'] ?>" <?= $sup['id'] == $s['supervisor_id'] ? 'selected' : '' ?>><?= htmlspecialchars($sup['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <select name="personnel_id" class="form-select form-select-sm" required>
                                        <option value="">-- Personnel --</option>
                                        <?php foreach ($personnel as $p): ?>
                                            <option value="<?= $p['id'] ?>" <?= $p['id'] == $s['personnel_id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <select name="remark" class="form-select form-select-sm" required>
                                        <option value="">-- Remark --</option>
                                        <option value="Clear" <?= ($s['remark'] ?? '') === 'Clear' ? 'selected' : '' ?>>Clear</option>
                                        <option value="Not Clear" <?= ($s['remark'] ?? '') === 'Not Clear' ? 'selected' : '' ?>>Not Clear</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="datetime-local" name="created_at" class="form-control form-control-sm" value="<?= date('Y-m-d\TH:i', strtotime($s['created_at'])) ?>" required>
                                </td>
                                <td>
                                    <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                    </form>
                                </td>
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
