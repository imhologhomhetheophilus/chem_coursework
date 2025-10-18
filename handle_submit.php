<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/db_connect.php';

// Ensure group leader is logged in
if (!isset($_SESSION['group_id'])) {
    header('Location: group_login.php');
    exit;
}

// Validate POST data
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: submission.php');
    exit;
}

$group_id = $_POST['group_id'] ?? null;
$supervisor_id = $_POST['supervisor_id'] ?? null;
$personnel_id = $_POST['personnel_id'] ?? null;
$student_ids = $_POST['student_ids'] ?? [];

if (!$group_id || !$supervisor_id || !$personnel_id) {
    die("Missing required fields. Please go back and complete the form.");
}

// Handle file upload
$file_name = null;
if (!empty($_FILES['file']['name'])) {
    $upload_dir = __DIR__ . '/uploads/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    $original_name = basename($_FILES['file']['name']);
    $safe_name = preg_replace('/\s+/', '_', $original_name);
    $file_name = time() . '_' . $safe_name;
    $target_path = $upload_dir . $file_name;

    if (!move_uploaded_file($_FILES['file']['tmp_name'], $target_path)) {
        die("File upload failed. Please check folder permissions.");
    }
}

// Insert submission record
try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO submissions (group_id, supervisor_id, personnel_id, file_name, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$group_id, $supervisor_id, $personnel_id, $file_name]);

    $submission_id = $pdo->lastInsertId();

    // Save each student's remark
    $stmt_remark = $pdo->prepare("
        INSERT INTO submission_remarks (submission_id, student_id, remark)
        VALUES (?, ?, ?)
    ");

    foreach ($student_ids as $sid) {
        $remark_key = 'remark_' . $sid;
        $remark = $_POST[$remark_key] ?? 'Not Cleared';
        $stmt_remark->execute([$submission_id, $sid, $remark]);
    }

    $pdo->commit();

    // Redirect with success message
    header("Location: submission.php?m=Coursework+submitted+successfully!");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    die("❌ Submission failed: " . htmlspecialchars($e->getMessage()));
}
?>
