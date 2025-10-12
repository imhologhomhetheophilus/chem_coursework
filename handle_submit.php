<?php
require 'includes/db.php';
require 'includes/auth.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if(empty($_SESSION['group_id'])) { header('Location: group_login.php'); exit; }

$group = $_SESSION['group_id'];
$supervisor_id = $_POST['supervisor_id'] ?? null;
$personnel_id = $_POST['personnel_id'] ?? null;
$student_ids = $_POST['student_ids'] ?? [];

if(!isset($_FILES['file']) || $_FILES['file']['error'] != UPLOAD_ERR_OK){
    header('Location: submission.php?m=' . urlencode('File upload failed')); exit;
}
$allowed = ['application/pdf','application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $_FILES['file']['tmp_name']);
if(!in_array($mime, $allowed)){
    header('Location: submission.php?m=' . urlencode('Invalid file type. Use PDF or DOCX')); exit;
}
$uploadDir = __DIR__ . '/uploads';
if(!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
$orig = basename($_FILES['file']['name']);
$target = $uploadDir . '/' . time() . '_' . preg_replace('/[^A-Za-z0-9_.-]/','_', $orig);
if(!move_uploaded_file($_FILES['file']['tmp_name'], $target)){
    header('Location: submission.php?m=' . urlencode('Failed to save file')); exit;
}
$fileName = basename($target);

$stmt = $pdo->prepare('INSERT INTO submissions (group_id, supervisor_id, personnel_id, file_name, date) VALUES (?, ?, ?, ?, NOW())');
$stmt->execute([$group, $supervisor_id, $personnel_id, $fileName]);
$submission_id = $pdo->lastInsertId();

foreach($student_ids as $sid){
    $remark = $_POST['remark_'.$sid] ?? 'Not Cleared';
    $ins = $pdo->prepare('INSERT INTO remarks (submission_id, student_id, remark) VALUES (?, ?, ?)');
    $ins->execute([$submission_id, $sid, $remark]);
}

header('Location: submission.php?m=' . urlencode('Submission saved successfully'));
exit;
?>