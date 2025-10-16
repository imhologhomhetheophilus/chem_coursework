<?php
include '../includes/db_connect.php';
require '../includes/auth.php';
if (session_status() === PHP_SESSION_NONE) session_start();
require_admin();

if (!isset($_GET['id'])) {
    die("No submission specified.");
}

$id = intval($_GET['id']);

// Fetch the file name first
$stmt = $pdo->prepare("SELECT file_name FROM submissions WHERE id=?");
$stmt->execute([$id]);
$file = $stmt->fetchColumn();

// Delete the file from the server if it exists
if ($file) {
    $filepath = __DIR__ . '/../uploads/' . $file;
    if (file_exists($filepath)) {
        unlink($filepath);
    }
}

// Delete the database record
$del = $pdo->prepare("DELETE FROM submissions WHERE id=?");
$del->execute([$id]);

header("Location: admin_submissions.php?m=Deleted successfully");
exit;
