<?php
session_start();
require 'includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $group = $_SESSION['group_id'] ?? '';
    $supervisor = $_POST['supervisor_id'] ?? null;
    $personnel = $_POST['personnel_id'] ?? null;

    // ✅ Use Render's writable folder
    $uploadDir = '/tmp/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $file_name = null;

    if (!empty($_FILES['file']['name'])) {
        $originalName = basename($_FILES['file']['name']);
        $uniqueName = time() . '_' . preg_replace('/\s+/', '_', $originalName);
        $targetFile = $uploadDir . $uniqueName;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
            $file_name = $uniqueName;
        } else {
            die("❌ File upload failed. Render allows uploads only to /tmp.");
        }
    }

    // ✅ Save record in DB
    $stmt = $pdo->prepare("
        INSERT INTO submissions (group_id, supervisor_id, personnel_id, file_name)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$group, $supervisor, $personnel, $file_name]);

    echo "✅ Submission saved successfully!";
}
?>
