<?php
require_once 'includes/db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $group  = trim($_POST['group_id'] ?? '');
    $file   = $_FILES['file'] ?? null;
    $supervisor_id = $_POST['supervisor_id'] ?? null;
    $personnel_id  = $_POST['personnel_id'] ?? null;

    if (!$group || !$file || $file['error'] !== UPLOAD_ERR_OK || !$supervisor_id || !$personnel_id) {
        die("All fields are required and file must be uploaded.");
    }

    $filename = time() . "_" . preg_replace("/[^a-zA-Z0-9_\-\.]/", "_", $file['name']);
    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true); // create folder if missing
    $target = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        die("Failed to move uploaded file. Check folder permissions.");
    }

    // Insert into database
    $stmt = $pdo->prepare("INSERT INTO submissions (group_id, supervisor_id, personnel_id, file_name, created_at) 
                           VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$group, $supervisor_id, $personnel_id, $filename]);

    header("Location: submission.php?m=File uploaded successfully!");
    exit;
}
?>
