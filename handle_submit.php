<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once __DIR__ . '/includes/db_connect.php';

// Handle submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form fields
    $group_id = $_POST['group_id'] ?? '';
    $supervisor_id = $_POST['supervisor_id'] ?? null;
    $personnel_id = $_POST['personnel_id'] ?? null;
    $created_at = $_POST['created_at'] ?? date('Y-m-d H:i:s');
    $student_ids = $_POST['student_ids'] ?? [];

    // Check required fields
    if (empty($group_id) || !$supervisor_id || !$personnel_id) {
        $_SESSION['error'] = "Please fill all required fields.";
        header("Location: submission.php");
        exit;
    }

    // ✅ Upload directory (in the root folder)
    $upload_dir = __DIR__ . '/uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // ✅ Handle file upload
    $file_name = null;
    if (!empty($_FILES['file']['name'])) {
        $original_name = preg_replace('/\s+/', '_', basename($_FILES['file']['name']));
        $file_name = time() . '_' . $original_name;
        $target_file = $upload_dir . $file_name;

        $allowed_extensions = ['pdf', 'docx'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed_extensions)) {
            $_SESSION['error'] = "Invalid file type. Only PDF and DOCX allowed.";
            header("Location: submission.php");
            exit;
        }

        if (!move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
            $_SESSION['error'] = "Failed to upload file. Please check folder permissions.";
            header("Location: submission.php");
            exit;
        }
    }

    try {
        // ✅ Insert into submissions table
        $stmt = $pdo->prepare("
            INSERT INTO submissions (group_id, supervisor_id, personnel_id, file_name, created_at)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$group_id, $supervisor_id, $personnel_id, $file_name, $created_at]);
        $submission_id = $pdo->lastInsertId();

        // ✅ Insert remarks for each student
        foreach ($student_ids as $sid) {
            $remark = $_POST['remark_' . $sid] ?? 'Not Cleared';
            $insert_student = $pdo->prepare("
                INSERT INTO submission_students (submission_id, student_id, remark)
                VALUES (?, ?, ?)
            ");
            $insert_student->execute([$submission_id, $sid, $remark]);
        }

        $_SESSION['success'] = "✅ Coursework submitted successfully!";
        header("Location: submission.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: submission.php");
        exit;
    }
}
?>
