<?php
session_start();
require 'includes/db_connect.php';
require 'includes/auth.php';

if (!isset($_POST['group_id'])) {
    header('Location: submission.php');
    exit;
}

$group_id = $_POST['group_id'];

// Ensure uploads folder exists
$upload_dir = __DIR__ . '/uploads/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

// Handle file upload
$file_path = '';
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $filename = basename($_FILES['file']['name']);
    $target_file = $upload_dir . time() . '_' . $filename;
    if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
        $file_path = 'uploads/' . basename($target_file);
    } else {
        die("Failed to upload file.");
    }
} else {
    die("File is required.");
}

// Insert into submissions table
$sql = "INSERT INTO submissions 
        (group_id, supervisor_id, personnel_id, file_path, created_at, experiment_datetime)
        VALUES (:group_id, :supervisor_id, :personnel_id, :file_path, :created_at, :experiment_datetime)";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':group_id' => $group_id,
    ':supervisor_id' => $_POST['supervisor_id'],
    ':personnel_id' => $_POST['personnel_id'],
    ':file_path' => $file_path,
    ':created_at' => $_POST['created_at'],
    ':experiment_datetime' => $_POST['experiment_datetime']
]);

$submission_id = $pdo->lastInsertId();

// Insert student remarks
if (isset($_POST['student_ids']) && is_array($_POST['student_ids'])) {
    $insert_remark = $pdo->prepare("INSERT INTO submission_students (submission_id, student_id, remark) VALUES (:submission_id, :student_id, :remark)");
    foreach ($_POST['student_ids'] as $sid) {
        $remark = $_POST['remark_' . $sid] ?? 'Not Cleared';
        $insert_remark->execute([
            ':submission_id' => $submission_id,
            ':student_id' => $sid,
            ':remark' => $remark
        ]);
    }
}

header('Location: submission.php?success=1');
exit;
