<?php
session_start();
require 'includes/db_connect.php';

if (!isset($_SESSION['group_id'])) {
    header('Location: group_login.php');
    exit;
}

$submission_id = $_GET['sub_id'] ?? 0;
$student_id = $_GET['student_id'] ?? 0;

// Delete the student remark
$del_stmt = $pdo->prepare("DELETE FROM submission_students WHERE submission_id=? AND student_id=?");
$del_stmt->execute([$submission_id, $student_id]);

// Optional: Check if submission has no more students, then delete file & main submission
$stmt = $pdo->prepare("SELECT COUNT(*) FROM submission_students WHERE submission_id=?");
$stmt->execute([$submission_id]);
$count = $stmt->fetchColumn();
if ($count == 0) {
    $file_stmt = $pdo->prepare("SELECT file_name FROM submissions WHERE id=?");
    $file_stmt->execute([$submission_id]);
    $file = $file_stmt->fetchColumn();
    if ($file && file_exists("uploads/$file")) unlink("uploads/$file");

    $pdo->prepare("DELETE FROM submissions WHERE id=?")->execute([$submission_id]);
}

header("Location: submission_page.php"); // back to main page
exit;
