<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) session_start();

require_once 'includes/db_connect.php';

// Ensure group leader is logged in
if (!isset($_SESSION['group_id'])) {
    header('Location: group_login.php');
    exit;
}

$group_id = $_POST['group_id'] ?? null;
$supervisor_id = $_POST['supervisor_id'] ?? null;
$personnel_id = $_POST['personnel_id'] ?? null;
$student_ids = $_POST['student_ids'] ?? [];

if (!$group_id || !$supervisor_id || !$personnel_id || empty($student_ids)) {
    die("Missing required information.");
}

// Handle file upload
$upload_dir = __DIR__ . '/uploads/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

$file_name = null;
if (!empty($_FILES['file']['name'])) {
    $basename = basename($_FILES['file']['name']);
    $file_name = time() . '_' . preg_replace('/\s+/', '_', $basename);
    $target = $upload_dir . $file_name;

    if (!move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
        die("Error uploading file. Please check folder permissions.");
    }
} else {
    die("No file uploaded.");
}

// Insert into submissions table
$stmt = $pdo->prepare("
    INSERT INTO submissions (group_id, supervisor_id, personnel_id, file_name, created_at)
    VALUES (?, ?, ?, ?, NOW())
");
$stmt->execute([$group_id, $supervisor_id, $personnel_id, $file_name]);

$submission_id = $pdo->lastInsertId();

// Insert remarks for each student
$remark_stmt = $pdo->prepare("
    INSERT INTO student_remarks (submission_id, student_id, remark)
    VALUES (?, ?, ?)
");

foreach ($student_ids as $student_id) {
    $remark_field = 'remark_' . $student_id;
    $remark = $_POST[$remark_field] ?? 'Not Cleared';
    $remark_stmt->execute([$submission_id, $student_id, $remark]);
}

// Redirect back to submission page
header("Location: submission.php?m=Coursework uploaded successfully!");
exit;
?>
