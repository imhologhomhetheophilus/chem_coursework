<?php
session_start();
require_once 'includes/db_connect.php';

if (!isset($_SESSION['group_id'])) {
    header('Location: group_login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: submission.php');
    exit;
}

$group_id = $_SESSION['group_id'];

$supervisor_id = $_POST['supervisor_id'] ?? null;
$personnel_id = $_POST['personnel_id'] ?? null;
$experiment_datetime = $_POST['experiment_datetime'] ?? null;

if (
    empty($supervisor_id) ||
    empty($personnel_id) ||
    empty($_FILES['coursework_file']['name'])
) {
    die("Please complete all required fields.");
}

/* upload */
$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$file = $_FILES['coursework_file'];
$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

$allowed = ['pdf', 'doc', 'docx'];
if (!in_array($extension, $allowed)) {
    die("Invalid file type.");
}

$newFileName = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $file['name']);
$filePath = $uploadDir . $newFileName;

move_uploaded_file($file['tmp_name'], $filePath);

/* save */
$stmt = $pdo->prepare("
    INSERT INTO submissions
    (group_id, supervisor_id, personnel_id, file_name, file_path, created_at, experiment_datetime)
    VALUES (?, ?, ?, ?, ?, NOW(), ?)
");

$stmt->execute([
    $group_id,
    $supervisor_id,
    $personnel_id,
    $file['name'],
    $filePath,
    $experiment_datetime
]);

$submission_id = $pdo->lastInsertId();

/* save remarks */
if (!empty($_POST['student_ids'])) {
    $stmt2 = $pdo->prepare("
        INSERT INTO submission_students (submission_id, student_id, remark)
        VALUES (?, ?, ?)
    ");

    foreach ($_POST['student_ids'] as $student_id) {
        $remark = $_POST['remark_' . $student_id] ?? 'Not Cleared';
        $stmt2->execute([$submission_id, $student_id, $remark]);
    }
}

/* 🔥 IMPORTANT FIX HERE */
header("Location: view_submission.php?success=1");
exit;
?>