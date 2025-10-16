<?php
include '../includes/db_connect.php';
require '../includes/auth.php';
if (session_status() === PHP_SESSION_NONE) session_start();
require_admin();

if(!isset($_GET['id'])) {
    die("No submission ID specified.");
}

$id = intval($_GET['id']);

// Fetch the file first so we can delete it from server
$stmt = $pdo->prepare("SELECT file_name FROM submissions WHERE id=?");
$stmt->execute([$id]);
$file = $stmt->fetchColumn();

if($file && file_exists(__DIR__ . '/../uploads/' . $file)) {
    unlink(__DIR__ . '/../uploads/' . $file);
}

// Delete the database record
$stmt = $pdo->prepare("DELETE FROM submissions WHERE id=?");
$stmt->execute([$id]);

header("Location: admin_submissions.php?msg=Submission+deleted+successfully");
exit;
