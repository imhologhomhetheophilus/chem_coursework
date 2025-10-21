<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/includes/db_connect.php';

// Redirect if not logged in (optional, if needed)
if (!isset($_SESSION['group_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $group_id = $_SESSION['group_id'] ?? $_POST['group_id'] ?? null;
    $supervisor_id = $_POST['supervisor_id'] ?? null;
    $personnel_id = $_POST['personnel_id'] ?? null;

    // Upload directory
    $uploadDir = __DIR__ . '/uploads/';
    $fileName = '';

    // Ensure uploads folder exists
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    if (!empty($_FILES['submission_file']['name'])) {
        $originalName = basename($_FILES['submission_file']['name']);
        $safeName = preg_replace('/[^A-Za-z0-9_\.-]/', '_', $originalName);
        $fileName = time() . '_' . $safeName;
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['submission_file']['tmp_name'], $targetPath)) {
            // ✅ File uploaded successfully
        } else {
            die("❌ Failed to move uploaded file. Please check folder permissions.");
        }
    }

    // Save submission to database
    $stmt = $pdo->prepare("
        INSERT INTO submissions (group_id, supervisor_id, personnel_id, file_name, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$group_id, $supervisor_id, $personnel_id, $fileName]);

    $_SESSION['success'] = "✅ File uploaded successfully!";
    header('Location: dashboard.php');
    exit;
} else {
    echo "Invalid request.";
}
?>
