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

// Handle remark update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submission_id'], $_POST['remark'])) {
    $sub_id = $_POST['submission_id'];
    $remark = $_POST['remark'];

    $stmt = $pdo->prepare("UPDATE submissions SET remark = ? WHERE id = ? AND group_id = ?");
    $stmt->execute([$remark, $sub_id, $group_id]);
    $message = "✅ Remark updated!";
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
