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

// Handle new submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_submission'])) {
    $file = $_FILES['file'] ?? null;
    $submission_time = $_POST['created_at'] ?? date('Y-m-d H:i:s');
    $supervisor_id = $_POST['supervisor_id'] ?? null;
    $personnel_id = $_POST['personnel_id'] ?? null;
    $student_remarks = $_POST['student_remarks'] ?? [];

    if ($file && $file['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $group_id . '_' . time() . '.' . $ext;
        $target = __DIR__ . '/uploads/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $target)) {
            $stmt = $pdo->prepare("INSERT INTO submissions (group_id, file_name, supervisor_id, personnel_id, created_at) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$group_id, $filename, $supervisor_id, $personnel_id, $submission_time]);
            $submission_id = $pdo->lastInsertId();

            foreach ($student_remarks as $student_id => $remark) {
                $pdo->prepare("INSERT INTO submission_students (submission_id, student_id, remark) VALUES (?, ?, ?)")
                    ->execute([$submission_id, $student_id, $remark]);
            }

            $message = "✅ File uploaded successfully!";
        } else {
            $message = "❌ Failed to move uploaded file.";
        }
    }
}

// Handle updates for previous submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_submission_id'])) {
    $sub_id = $_POST['edit_submission_id'];
    $submission_time = $_POST['created_at'] ?? date('Y-m-d H:i:s');
    $supervisor_id = $_POST['supervisor_id'] ?? null;
    $personnel_id = $_POST['personnel_id'] ?? null;
    $student_remarks = $_POST['student_remarks'] ?? [];

    $stmt = $pdo->prepare("UPDATE submissions SET supervisor_id = ?, personnel_id = ?, created_at = ? WHERE id = ? AND group_id = ?");
    $stmt->execute([$supervisor_id, $personnel_id, $submission_time, $sub_id, $group_id]);

    foreach ($student_remarks as $student_id => $remark) {
        $stmt_check = $pdo->prepare("SELECT * FROM submission_students WHERE submission_id = ? AND student_id = ?");
        $stmt_check->execute([$sub_id, $student_id]);
        if ($stmt_check->rowCount() > 0) {
            $pdo->prepare("UPDATE submission_students SET remark = ? WHERE submission_id = ? AND student_id = ?")
                ->execute([$remark, $sub_id, $student_id]);
        } else {
            $pdo->prepare("INSERT INTO submission_students (submission_id, student_id, remark) VALUES (?, ?, ?)")
                ->execute([$sub_id, $student_id, $remark]);
        }
    }

    $message = "✅ Submission updated successfully!";
}

// Fetch students
$students = $pdo->prepare("SELECT id, name, regno FROM students WHERE group_id = ?");
$students->execute([$group_id]);
$students = $students->fetchAll(PDO::FETCH_ASSOC);

// Fetch submissions
$subs = $pdo->prepare("SELECT s.*, sp.name AS supervisor, p.name AS personnel FROM submissions s LEFT JOIN supervisors sp ON s.supervisor_id = sp.id LEFT JOIN personnel p ON s.personnel_id = p.id WHERE s.group_id = ? ORDER BY s.created_at DESC");
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
                    <label class="form-label">Student Remarks</label>
                    <?php foreach ($students as $st): ?>
                        <div class="mb-2 border p-2 rounded">
                            <strong><?= htmlspecialchars($st['name']) ?><?= !empty($st['regno']) ? " ({$st['regno']})" : '' ?></strong>
                            <select name="student_remarks[<?= $st['id'] ?>]" class="form-select form-select-sm mt-1">
                                <option value="">-- Remark --</option>
                                <option value="Clear">Clear</option>
                                <option value="Not Clear">Not Clear</option>
                            </select>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="mb-3">
                    <label class="form-label">Submission Date & Time</label>
                    <input type="datetime-local" name="created_at" class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required>
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
                <?php foreach ($subs as $s): ?>
                    <?php
                    $st_query = $pdo->prepare("SELECT st.id, st.name, st.regno, ss.remark AS student_remark FROM submission_students ss JOIN students st ON ss.student_id = st.id WHERE ss.submission_id = ?");
                    $st_query->execute([$s['id']]);
                    $sub_students = $st_query->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <form method="post" class="mb-4 p-3 border rounded">
                        <input type="hidden" name="edit_submission_id" value="<?= $s['id'] ?>">
                        
                        <div class="mb-2"><strong>File:</strong> <a href="uploads/<?= htmlspecialchars($s['file_name']) ?>" target="_blank"><?= htmlspecialchars($s['file_name']) ?></a></div>

                        <div class="mb-2">
                            <label class="form-label"><strong>Supervisor</strong></label>
                            <select name="supervisor_id" class="form-select">
                                <?php foreach ($supervisors as $sup): ?>
                                    <option value="<?= $sup['id'] ?>" <?= $sup['id'] == $s['supervisor_id'] ? 'selected' : '' ?>><?= htmlspecialchars($sup['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-2">
                            <label class="form-label"><strong>Personnel</strong></label>
                            <select name="personnel_id" class="form-select">
                                <?php foreach ($personnel as $p): ?>
                                    <option value="<?= $p['id'] ?>" <?= $p['id'] == $s['personnel_id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-2">
                            <label class="form-label"><strong>Students & Remarks</strong></label>
                            <?php foreach ($sub_students as $st): ?>
                                <div class="mb-2 border p-2 rounded">
                                    <strong><?= htmlspecialchars($st['name']) ?><?= !empty($st['regno']) ? " ({$st['regno']})" : '' ?></strong>
                                    <select name="student_remarks[<?= $st['id'] ?>]" class="form-select form-select-sm mt-1">
                                        <option value="">-- Remark --</option>
                                        <option value="Clear" <?= ($st['student_remark'] ?? '') === 'Clear' ? 'selected' : '' ?>>Clear</option>
                                        <option value="Not Clear" <?= ($st['student_remark'] ?? '') === 'Not Clear' ? 'selected' : '' ?>>Not Clear</option>
                                    </select>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="mb-2">
                            <label class="form-label"><strong>Submission Date & Time</strong></label>
                            <input type="datetime-local" name="created_at" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime($s['created_at'])) ?>" required>
                        </div>

                        <button type="submit" class="btn btn-sm btn-primary">Update</button>
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
