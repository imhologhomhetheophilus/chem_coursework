<?php
session_start();
require 'includes/db_connect.php';

if (!isset($_SESSION['group_id'])) {
    header('Location: group_login.php');
    exit;
}

$group_id = $_SESSION['group_id'];
$message = '';

// Fetch supervisors and personnel
$supervisors = $pdo->query("SELECT * FROM supervisors ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$personnel = $pdo->query("SELECT * FROM personnel ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Fetch students
$students = $pdo->prepare("SELECT id, name, regno FROM students WHERE group_id = ?");
$students->execute([$group_id]);
$students = $students->fetchAll(PDO::FETCH_ASSOC);

// Handle new submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_submission'])) {
    $submission_time = $_POST['created_at'] ?? date('Y-m-d H:i:s');
    $student_data = $_POST['students'] ?? [];

    $file = $_FILES['file'] ?? null;

    if ($file && $file['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $group_id . '_' . time() . '.' . $ext;
        $target = __DIR__ . '/uploads/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $target)) {
            $stmt = $pdo->prepare("INSERT INTO submissions (group_id, file_name, created_at) VALUES (?, ?, ?)");
            $stmt->execute([$group_id, $filename, $submission_time]);
            $submission_id = $pdo->lastInsertId();

            foreach ($student_data as $student_id => $data) {
                $remark = $data['remark'] ?? '';
                $sup_id = $data['supervisor_id'] ?? null;
                $per_id = $data['personnel_id'] ?? null;
                $time = $data['created_at'] ?? $submission_time;

                $pdo->prepare("INSERT INTO submission_students (submission_id, student_id, supervisor_id, personnel_id, remark, created_at) VALUES (?, ?, ?, ?, ?, ?)")
                    ->execute([$submission_id, $student_id, $sup_id, $per_id, $remark, $time]);
            }

            $message = "✅ Submission successful!";
        } else {
            $message = "❌ Failed to move uploaded file.";
        }
    }
}

// Fetch previous submissions
$subs = $pdo->prepare("SELECT * FROM submissions WHERE group_id = ? ORDER BY created_at DESC");
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

    <!-- New Submission Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="new_submission" value="1">

                <div class="mb-3">
                    <label class="form-label">Upload Coursework</label>
                    <input type="file" name="file" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Submission Date & Time</label>
                    <input type="datetime-local" name="created_at" class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required>
                </div>

                <h5 class="mb-3">Students</h5>
                <?php foreach ($students as $st): ?>
                    <div class="mb-3 border p-3 rounded">
                        <strong><?= htmlspecialchars($st['name']) ?> (<?= htmlspecialchars($st['regno']) ?>)</strong>

                        <div class="mt-2">
                            <label class="form-label">Supervisor</label>
                            <select name="students[<?= $st['id'] ?>][supervisor_id]" class="form-select" required>
                                <option value="">-- Select Supervisor --</option>
                                <?php foreach ($supervisors as $sup): ?>
                                    <option value="<?= $sup['id'] ?>"><?= htmlspecialchars($sup['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mt-2">
                            <label class="form-label">Personnel</label>
                            <select name="students[<?= $st['id'] ?>][personnel_id]" class="form-select" required>
                                <option value="">-- Select Personnel --</option>
                                <?php foreach ($personnel as $p): ?>
                                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mt-2">
                            <label class="form-label">Remark</label>
                            <select name="students[<?= $st['id'] ?>][remark]" class="form-select">
                                <option value="">-- Select Remark --</option>
                                <option value="Clear">Clear</option>
                                <option value="Not Clear">Not Clear</option>
                            </select>
                        </div>

                        <div class="mt-2">
                            <label class="form-label">Date & Time</label>
                            <input type="datetime-local" name="students[<?= $st['id'] ?>][created_at]" class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required>
                        </div>
                    </div>
                <?php endforeach; ?>

                <button type="submit" class="btn btn-primary w-100">Submit</button>
            </form>
        </div>
    </div>

    <!-- Previous Submissions -->
    <div class="card mb-4">
        <div class="card-body">
            <h5>Previous Submissions</h5>
            <?php if ($subs): ?>
                <?php foreach ($subs as $s): ?>
                    <?php
                    $st_query = $pdo->prepare("SELECT ss.*, st.name, st.regno FROM submission_students ss JOIN students st ON ss.student_id = st.id WHERE ss.submission_id = ?");
                    $st_query->execute([$s['id']]);
                    $sub_students = $st_query->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <form method="post" class="mb-4 p-3 border rounded">
                        <input type="hidden" name="edit_submission_id" value="<?= $s['id'] ?>">

                        <div class="mb-2"><strong>File:</strong> <a href="uploads/<?= htmlspecialchars($s['file_name']) ?>" target="_blank"><?= htmlspecialchars($s['file_name']) ?></a></div>

                        <h6>Students</h6>
                        <?php foreach ($sub_students as $st): ?>
                            <div class="mb-3 border p-2 rounded">
                                <strong><?= htmlspecialchars($st['name']) ?> (<?= htmlspecialchars($st['regno']) ?>)</strong>

                                <div class="mt-2">
                                    <label class="form-label">Supervisor</label>
                                    <select name="students[<?= $st['student_id'] ?>][supervisor_id]" class="form-select">
                                        <option value="">-- Select Supervisor --</option>
                                        <?php foreach ($supervisors as $sup): ?>
                                            <option value="<?= $sup['id'] ?>" <?= $sup['id'] == $st['supervisor_id'] ? 'selected' : '' ?>><?= htmlspecialchars($sup['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mt-2">
                                    <label class="form-label">Personnel</label>
                                    <select name="students[<?= $st['student_id'] ?>][personnel_id]" class="form-select">
                                        <option value="">-- Select Personnel --</option>
                                        <?php foreach ($personnel as $p): ?>
                                            <option value="<?= $p['id'] ?>" <?= $p['id'] == $st['personnel_id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mt-2">
                                    <label class="form-label">Remark</label>
                                    <select name="students[<?= $st['student_id'] ?>][remark]" class="form-select">
                                        <option value="">-- Select Remark --</option>
                                        <option value="Clear" <?= ($st['remark'] ?? '') === 'Clear' ? 'selected' : '' ?>>Clear</option>
                                        <option value="Not Clear" <?= ($st['remark'] ?? '') === 'Not Clear' ? 'selected' : '' ?>>Not Clear</option>
                                    </select>
                                </div>

                                <div class="mt-2">
                                    <label class="form-label">Date & Time</label>
                                    <input type="datetime-local" name="students[<?= $st['student_id'] ?>][created_at]" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime($st['created_at'])) ?>" required>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <button type="submit" class="btn btn-sm btn-primary">Update Submission</button>
                    </form>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted">No submissions yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="text-center">
        <a class="btn btn-outline-secondary" href="logout.php">Logout</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
