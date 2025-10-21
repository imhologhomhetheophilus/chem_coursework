<?php
session_start();

// ===== Require DB connection =====
require_once __DIR__ . '/includes/db_connect.php';

// ===== Only allow admin =====
if (!isset($_SESSION['admin'])) {
    header('Location: index.php'); // Adjust if your login page is elsewhere
    exit;
}

// ===== Get submission ID =====
$submission_id = $_GET['id'] ?? 0;
if (!$submission_id) {
    die("Invalid submission ID.");
}

// ===== Delete submission file (if exists) =====
$stmt = $pdo->prepare("SELECT file_name FROM submissions WHERE id = ?");
$stmt->execute([$submission_id]);
$file = $stmt->fetchColumn();

if ($file) {
    $file_path = __DIR__ . '/uploads/' . $file;
    if (file_exists($file_path)) {
        unlink($file_path);
    }
}

// ===== Delete related student remarks =====
$delete_students = $pdo->prepare("DELETE FROM submission_students WHERE submission_id = ?");
$delete_students->execute([$submission_id]);

// ===== Delete the submission record =====
$delete_submission = $pdo->prepare("DELETE FROM submissions WHERE id = ?");
$delete_submission->execute([$submission_id]);

// ===== Redirect back to dashboard with message =====
header('Location: dashboard.php?msg=deleted');
exit;
?>
