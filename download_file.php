<?php
include '../includes/db_connect.php';
require '../includes/auth.php';
if (session_status() === PHP_SESSION_NONE) session_start();
require_admin();

if (!isset($_GET['id'])) {
    die("No file specified.");
}

$id = intval($_GET['id']);

// Fetch the file name from the database
$stmt = $pdo->prepare("SELECT file_name FROM submissions WHERE id=?");
$stmt->execute([$id]);
$file = $stmt->fetchColumn();

if (!$file) {
    die("File not found in database.");
}

$filepath = __DIR__ . '/../uploads/' . $file;

// Security check: prevent path traversal
$realBase = realpath(__DIR__ . '/../uploads/');
$realFile = realpath($filepath);

if ($realFile === false || strpos($realFile, $realBase) !== 0) {
    die("Unauthorized access.");
}

if (!file_exists($filepath)) {
    die("File does not exist on server.");
}

// Serve the file
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filepath));
readfile($filepath);
exit;
