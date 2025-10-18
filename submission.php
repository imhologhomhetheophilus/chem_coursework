<?php
session_start();
require 'includes/db_connect.php';

// Only allow logged-in group leader
if (!isset($_SESSION['group_id'])) {
    header('Location: group_login.php');
    exit;
}

$groupID = $_SESSION['group_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['coursework'])) {
    $file = $_FILES['coursework'];
    $filename = basename($file['name']);
    $targetDir = 'uploads/';
    $targetFile = $targetDir . time() . "_" . $filename;

    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        $stmt = $pdo->prepare("INSERT INTO submissions (group_id, file_name, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$groupID, $targetFile]);
        $message = "✅ Coursework uploaded successfully!";
    } else {
        $message = "❌ Failed to upload file.";
    }
}

include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="card mx-auto" style="max-width:500px;">
        <div class="card-body">
            <h4 class="card-title text-center mb-3">Submit Coursework (<?= htmlspecialchars($groupID) ?>)</h4>

            <?php if ($message): ?>
                <div class="alert alert-info text-center"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Choose File</label>
                    <input type="file" name="coursework" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success w-100">Upload</button>
            </form>

            <a href="logout.php" class="btn btn-link w-100 mt-2">Logout</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
