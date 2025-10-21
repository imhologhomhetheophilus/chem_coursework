<?php
session_start();

// ===== Require DB connection =====
// Make sure the path is correct relative to this file
require_once __DIR__ . '/includes/db_connect.php';

// ===== Only allow admin =====
if (!isset($_SESSION['admin'])) {
    header('Location: index.php'); // Adjust path if login is in /admin/
    exit;
}

// ===== Get submission ID =====
$submission_id = $_GET['id'] ?? 0;
if (!$submission_id) {
    die("Invalid submission ID.");
}

// ===== Delete submission file first (optional) =====
$stmt = $pdo->prepare("SELECT file_name FROM submissions WHERE id = ?");
$stmt->execute([$submission_id]);
$file = $stmt->fetchColumn();

if ($file) {
    $file_path = __DIR__ . '/uploads/' . $file;
    if (file_exists($file_path)) {
        unlink($file_path);
    }
}

// ===== Delete submission record =====
$delete = $pdo->prepare("DELETE FROM submissions WHERE id = ?");
$delete->execute([$submission_id]);

// ===== Redirect back to dashboard =====
// Make sure this path is correct relative to your project structure
header('Location: dashboard.php?msg=deleted');
exit;
?>
