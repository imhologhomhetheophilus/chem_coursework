<?php
require '../includes/db_connect.php';
session_start();

// Only admins can delete
if (!($_SESSION['role'] ?? '') === 'admin') die("Unauthorized");

$id = $_GET['id'] ?? null;
if (!$id) die("Invalid ID");

$stmt = $pdo->prepare("SELECT file_name FROM submissions WHERE id = ?");
$stmt->execute([$id]);
$file = $stmt->fetchColumn();

if ($file) {
    $path = __DIR__ . '/uploads/' . $file;
    if (file_exists($path)) unlink($path);
}

$pdo->prepare("DELETE FROM submissions WHERE id = ?")->execute([$id]);

header("Location: submissions_admin.php?m=Submission deleted!");
exit;
?>
