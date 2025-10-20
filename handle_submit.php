<?php
session_start();
require 'includes/db_connect.php';

if (!isset($_SESSION['group_id'])) {
    header("Location: group_login.php");
    exit;
}

$group_id = $_SESSION['group_id'];
$supervisor_id = $_POST['supervisor_id'] ?? null;
$personnel_id = $_POST['personnel_id'] ?? null;
$leader_remark = $_POST['leader_remark'] ?? null; // ✅ added
$created_at = $_POST['created_at'] ?? date('Y-m-d H:i:s');
$file_name = '';

try {
    // Ensure uploads folder exists
    $upload_dir = __DIR__ . '/uploads/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    // Handle file upload
    if (!empty($_FILES['file']['name'])) {
        $original_name = basename($_FILES['file']['name']);
        $safe_name = preg_replace("/[^A-Za-z0-9._-]/", "_", $original_name);
        $unique_name = time() . "_" . $safe_name;
        $target_path = $upload_dir . $unique_name;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $target_path)) {
            $file_name = $unique_name;
        } else {
            throw new Exception("File upload failed. Check folder permissions.");
        }
    } else {
        throw new Exception("No file selected for upload.");
    }

    // ✅ Insert submission record with leader_remark
    $insert = $pdo->prepare("
        INSERT INTO submissions (group_id, supervisor_id, personnel_id, file_name, created_at, leader_remark)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $insert->execute([$group_id, $supervisor_id, $personnel_id, $file_name, $created_at, $leader_remark]);

    $submission_id = $pdo->lastInsertId();

    // Insert each student's remark
    if (!empty($_POST['student_ids'])) {
        $stmt = $pdo->prepare("
            INSERT INTO submission_students (submission_id, student_id, remark)
            VALUES (?, ?, ?)
        ");

        foreach ($_POST['student_ids'] as $student_id) {
            $remark_key = "remark_" . $student_id;
            $remark = $_POST[$remark_key] ?? "Not Cleared";
            $stmt->execute([$submission_id, $student_id, $remark]);
        }
    }

    // Redirect to submission page
    $_SESSION['success'] = "Submission uploaded successfully!";
    header("Location: submission.php");
    exit;

} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("Location: submission.php");
    exit;
}
?>
