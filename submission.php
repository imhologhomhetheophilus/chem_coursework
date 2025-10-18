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
            // Insert submission into DB
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

// Fetch students of this group
$students = $pdo->prepare("SELECT name, regno FROM students WHERE group_id = ?");
$students->execute([$group_id]);
$students = $students->fetchAll(PDO::FETCH_ASSOC);

// Fetch previous submissions of this group
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
    <div class="card">
        <div class="card-body">
            <h5>Previous Submissions</h5>
            <?php if ($subs): ?>
                <ul class="list-group">
                    <?php foreach ($subs as $s): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="uploads/<?= htmlspecialchars($s['file_name']) ?>" target="_blank"><?= htmlspecialchars($s['file_name']) ?></a>
                            <span><?= htmlspecialchars($s['created_at']) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
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
