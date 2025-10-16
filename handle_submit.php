<?php
require_once 'includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $file  = $_FILES['file'] ?? null;

    if ($name === '' || !$file || $file['error'] !== UPLOAD_ERR_OK) {
        die("Upload failed! Please provide a name and a file.");
    }

    // Sanitize filename
    $filename = time() . "_" . preg_replace("/[^a-zA-Z0-9_\-\.]/", "_", $file['name']);
    $uploadDir = __DIR__ . '/uploads/';
    $target = $uploadDir . $filename;

    // Move the uploaded file
    if (!move_uploaded_file($file['tmp_name'], $target)) {
        die("Upload failed! Cannot move file. Check folder permissions.");
    }

    // Insert into database
    try {
        $sql = "INSERT INTO submissions (name, file_name, created_at) VALUES (:name, :file_name, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':name' => $name,
            ':file_name' => $filename
        ]);

        echo "File uploaded successfully!";
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}
?>
