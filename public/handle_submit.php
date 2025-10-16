<?php
require '../includes/db_connect.php';
require '../includes/auth.php';
if (session_status() === PHP_SESSION_NONE) session_start();
require_leader(); // Only group leaders

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $group_id = $_SESSION['group_id'];
    $supervisor_id = $_POST['supervisor_id'] ?? null;
    $personnel_id = $_POST['personnel_id'] ?? null;
    $file = $_FILES['file'] ?? null;

    if (!$supervisor_id || !$personnel_id || !$file || $file['error'] !== UPLOAD_ERR_OK) {
        die("Upload failed. Please fill all required fields and select a file.");
    }

    // Sanitize filename
    $filename = time() . "_" . preg_replace("/[^a-zA-Z0-9_\-\.]/", "_", $file['name']);
    $uploadDir = __DIR__ . '/uploads/';
    $target = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        die("Upload failed. Cannot move file. Check folder permissions.");
    }

    // Insert into DB
    $stmt = $pdo->prepare("INSERT INTO submissions (group_id, supervisor_id, personnel_id, file_name, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$group_id, $supervisor_id, $personnel_id, $filename]);

    header("Location: submission.php?m=File uploaded successfully!");
    exit;
}
?>
