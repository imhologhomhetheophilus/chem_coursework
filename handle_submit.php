<?php
require 'includes/db_connect.php';
require 'includes/auth.php';
require_leader();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}

// Required fields
$group_id       = $_POST['group_id'] ?? null;
$supervisor_id  = $_POST['supervisor_id'] ?? null;
$personnel_id   = $_POST['personnel_id'] ?? null;
$file           = $_FILES['file'] ?? null;

if (!$group_id || !$supervisor_id || !$personnel_id || !$file || $file['error'] !== UPLOAD_ERR_OK) {
    die("Please fill all required fields and upload a valid file.");
}

// Validate file type
$allowed = ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
if (!in_array($file['type'], $allowed)) {
    die("Invalid file type. Only PDF or DOCX allowed.");
}

// Prepare upload folder
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Sanitize and move file
$filename = time() . "_" . preg_replace("/[^a-zA-Z0-9_\-\.]/", "_", $file['name']);
$target   = $uploadDir . $filename;

if (!move_uploaded_file($file['tmp_name'], $target)) {
    die("Upload failed! Check folder permissions.");
}

// Insert submission record
try {
    $sql = "INSERT INTO submissions (group_id, supervisor_id, personnel_id, file_name, created_at)
            VALUES (:group, :sup, :pers, :file, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':group' => $group_id,
        ':sup'   => $supervisor_id,
        ':pers'  => $personnel_id,
        ':file'  => $filename
    ]);

    header("Location: submission.php?m=File uploaded successfully!");
    exit;
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
