<?php
session_start();

// Require DB connection (corrected path)
require 'includes/db_connect.php';

// Only allow admin
if (!isset($_SESSION['admin'])) {
    header('Location: admin/login.php');
    exit;
}

// Get submission ID
$submission_id = $_GET['id'] ?? 0;
if (!$submission_id) {
    die("Invalid submission ID.");
}

// Delete submission file first (optional)
$stmt = $pdo->prepare("SELECT file_name FROM submissions WHERE id = ?");
$stmt->execute([$submission_id]);
$file = $stmt->fetchColumn();
if ($file && file_exists(__DIR__ . '/uploads/' . $file)) {
    unlink(__DIR__ . '/uploads/' . $file);
}

// Delete submission record
$delete = $pdo->prepare("DELETE FROM submissions WHERE id = ?");
$delete->execute([$submission_id]);

// Redirect back
header('Location: dashboard.php');
exit;
?>
