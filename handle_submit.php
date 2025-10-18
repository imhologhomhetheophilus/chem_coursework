<?php
session_start();
require 'includes/db.php';
require 'includes/auth.php';
require_leader();

$group_id = $_SESSION['group_id'];

// Ensure uploads folder exists
$upload_dir = __DIR__ . '/uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// New submission
if (isset($_POST['group_id']) && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $created_at = $_POST['created_at'] ?? date('Y-m-d H:i:s');
    $students_data = $_POST['students'] ?? [];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $group_id . '_' . time() . '.' . $ext;
        $target = $upload_dir . $filename;

        if (move_uploaded_file($file['tmp_name'], $target)) {
            // Insert submission
            $stmt = $pdo->prepare("INSERT INTO submissions (group_id, file_name, created_at) VALUES (?, ?, ?)");
            $stmt->execute([$group_id, $filename, $created_at]);
            $submission_id = $pdo->lastInsertId();

            // Insert student remarks
            foreach ($students_data as $student_id => $data) {
                $remark = $data['remark'] ?? '';
                $sup_id = $data['supervisor_id'] ?? null;
                $per_id = $data['personnel_id'] ?? null;
                $time = $data['created_at'] ?? $created_at;

                $stmt2 = $pdo->prepare("INSERT INTO submission_students (submission_id, student_id, supervisor_id, personnel_id, remark, created_at) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt2->execute([$submission_id, $student_id, $sup_id, $per_id, $remark, $time]);
            }

            header("Location: group_dashboard.php?m=Submission Successful!");
            exit;

        } else {
            die("❌ Failed to move uploaded file.");
        }
    } else {
        die("❌ File upload error.");
    }
}

// Update previous submission
if (isset($_POST['edit_submission_id'])) {
    $submission_id = $_POST['edit_submission_id'];
    $students_data = $_POST['students'] ?? [];

    foreach ($students_data as $student_id => $data) {
        $remark = $data['remark'] ?? '';
        $sup_id = $data['supervisor_id'] ?? null;
        $per_id = $data['personnel_id'] ?? null;
        $time = $data['created_at'] ?? date('Y-m-d H:i:s');

        // Check if record exists
        $stmt_check = $pdo->prepare("SELECT * FROM submission_students WHERE submission_id = ? AND student_id = ?");
        $stmt_check->execute([$submission_id, $student_id]);
        if ($stmt_check->rowCount() > 0) {
            $stmt2 = $pdo->prepare("UPDATE submission_students SET remark=?, supervisor_id=?, personnel_id=?, created_at=? WHERE submission_id=? AND student_id=?");
            $stmt2->execute([$remark, $sup_id, $per_id, $time, $submission_id, $student_id]);
        } else {
            $stmt2 = $pdo->prepare("INSERT INTO submission_students (submission_id, student_id, supervisor_id, personnel_id, remark, created_at) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt2->execute([$submission_id, $student_id, $sup_id, $per_id, $remark, $time]);
        }
    }

    header("Location: group_dashboard.php?m=Submission Updated!");
    exit;
}

die("❌ Invalid Request.");
