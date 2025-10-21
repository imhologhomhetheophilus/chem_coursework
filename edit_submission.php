<?php
session_start();
require 'includes/db_connect.php';

if (!isset($_SESSION['group_id'])) {
    header('Location: group_login.php');
    exit;
}

$group_id = $_SESSION['group_id'];

$submission_id = $_GET['sub_id'] ?? 0;
$student_id = $_GET['student_id'] ?? 0;

// Fetch current remark
$stmt = $pdo->prepare("SELECT remark FROM submission_students WHERE submission_id = ? AND student_id = ?");
$stmt->execute([$submission_id, $student_id]);
$current = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$current) {
    die("Student remark not found for this submission.");
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_remark = $_POST['remark'] ?? '';
    $update = $pdo->prepare("UPDATE submission_students SET remark=? WHERE submission_id=? AND student_id=?");
    $update->execute([$new_remark, $submission_id, $student_id]);
    $msg = "✅ Remark updated successfully!";
}

include 'includes/header.php';
?>

<div class="container my-5">
    <h3>Edit Student Remark</h3>
    <?php if ($msg): ?>
        <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <form method="post" class="card p-4 shadow-sm" style="max-width:500px;">
        <div class="mb-3">
            <label class="form-label">Remark</label>
            <select name="remark" class="form-select" required>
                <option value="Not Cleared" <?= $current['remark']=='Not Cleared'?'selected':'' ?>>Not Cleared</option>
                <option value="Cleared" <?= $current['remark']=='Cleared'?'selected':'' ?>>Cleared</option>
            </select>
        </div>
        <div class="d-flex justify-content-between">
            <button class="btn btn-success">Update Remark</button>
            <a href="submission.php" class="btn btn-secondary">Back</a>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
