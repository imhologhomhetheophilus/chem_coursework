<?php
session_start();
require 'includes/db_connect.php'; // Ensure $pdo is available

// Redirect if not logged in
if (!isset($_SESSION['group_id'])) {
    header('Location: group_login.php');
    exit;
}

$group_id = $_SESSION['group_id'];

// Ensure uploads folder exists
$upload_dir = __DIR__ . '/uploads/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

// ---------------------
// New Submission
// ---------------------
if (isset($_POST['group_id']) && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $created_at = $_POST['created_at'] ?? date('Y-m-d H:i:s');
    $student_ids = $_POST['student_ids'] ?? [];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $group_id . '_' . time() . '.' . $ext;
        $target = $upload_dir . $filename;

        if (move_uploaded_file($file['tmp_name'], $target)) {
            // Insert submission
            $stmt = $pdo->prepare("INSERT INTO submissions (group_id, file_name, supervisor_id, personnel_id, created_at) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $group_id,
                $filename,
                $_POST['supervisor_id'] ?? null,
                $_POST['personnel_id'] ?? null,
                $created_at
            ]);
            $submission_id = $pdo->lastInsertId();

            // Insert student remarks
            foreach ($student_ids as $student_id) {
                $remark = $_POST['remark_' . $student_id] ?? 'Not Cleared';
                $stmt2 = $pdo->prepare("INSERT INTO submission_students (submission_id, student_id, remark, created_at) VALUES (?, ?, ?, ?)");
                $stmt2->execute([$submission_id, $student_id, $remark, $created_at]);
            }

            header("Location: submission.php?m=Submission Successful!");
            exit;

        } else {
            die("❌ Failed to move uploaded file.");
        }
    } else {
        die("❌ File upload error.");
    }
}

// ---------------------
// Update Previous Submission
// ---------------------
if (isset($_POST['edit_submission_id'])) {
    $submission_id = $_POST['edit_submission_id'];
    $student_ids = $_POST['student_ids'] ?? [];
    $created_at = $_POST['created_at'] ?? date('Y-m-d H:i:s');

    // Update submission info
    $stmt = $pdo->prepare("UPDATE submissions SET supervisor_id=?, personnel_id=?, created_at=? WHERE id=? AND group_id=?");
    $stmt->execute([
        $_POST['supervisor_id'] ?? null,
        $_POST['personnel_id'] ?? null,
        $created_at,
        $submission_id,
        $group_id
    ]);

    // Update student remarks
    foreach ($student_ids as $student_id) {
        $remark = $_POST['remark_' . $student_id] ?? 'Not Cleared';

        // Check if record exists
        $stmt_check = $pdo->prepare("SELECT * FROM submission_students WHERE submission_id=? AND student_id=?");
        $stmt_check->execute([$submission_id, $student_id]);

        if ($stmt_check->rowCount() > 0) {
            $stmt2 = $pdo->prepare("UPDATE submission_students SET remark=?, created_at=? WHERE submission_id=? AND student_id=?");
            $stmt2->execute([$remark, $created_at, $submission_id, $student_id]);
        } else {
            $stmt2 = $pdo->prepare("INSERT INTO submission_students (submission_id, student_id, remark, created_at) VALUES (?, ?, ?, ?)");
            $stmt2->execute([$submission_id, $student_id, $remark, $created_at]);
        }
    }

    header("Location: submission.php?m=Submission Updated!");
    exit;
}

die("❌ Invalid Request.");
