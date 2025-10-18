<?php
session_start();
require 'includes/db.php';
require 'includes/auth.php';
require_leader();

$group_id = $_SESSION['group_id'];

// Ensure uploads folder exists
$upload_dir = __DIR__ . '/uploads';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Handle submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = $_FILES['file'] ?? null;
    $supervisor_id = $_POST['supervisor_id'] ?? null;
    $personnel_id = $_POST['personnel_id'] ?? null;
    $student_ids = $_POST['student_ids'] ?? [];

    // Validate
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        die("❌ File upload failed. Check the file.");
    }
    if (!$supervisor_id || !$personnel_id) {
        die("❌ Supervisor and Personnel are required.");
    }

    // Generate unique filename
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $group_id . '_' . time() . '.' . $ext;
    $target = $upload_dir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        die("❌ Failed to move uploaded file. Check folder permissions.");
    }

    // Insert submission record
    $stmt = $pdo->prepare("INSERT INTO submissions (group_id, file_name, supervisor_id, personnel_id, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$group_id, $filename, $supervisor_id, $personnel_id]);
    $submission_id = $pdo->lastInsertId();

    // Insert student remarks
    foreach ($student_ids as $sid) {
        $remark = $_POST['remark_' . $sid] ?? '';
        $pdo->prepare("INSERT INTO submission_students (submission_id, student_id, remark) VALUES (?, ?, ?)")
            ->execute([$submission_id, $sid, $remark]);
    }

    // Redirect with success message
    header("Location: group_submission.php?m=Submission+successful");
    exit;
}
