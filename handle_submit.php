<?php
require 'includes/db.php';
require 'includes/auth.php';
require_leader(); // only group leaders can submit

$group = $_SESSION['group_id'];

// Check form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supervisor_id = $_POST['supervisor_id'] ?? null;
    $personnel_id = $_POST['personnel_id'] ?? null;
    $student_ids = $_POST['student_ids'] ?? [];

    if (!$supervisor_id || !$personnel_id || empty($student_ids)) {
        header("Location: group_submission.php?m=Please fill all required fields");
        exit;
    }

    // Handle file upload
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['file'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $group.'_'.time().'.'.$ext;
        $target = __DIR__.'/uploads/'.$filename;

        if (move_uploaded_file($file['tmp_name'], $target)) {
            // Insert submission
            $stmt = $pdo->prepare("INSERT INTO submissions (group_id, file_name, supervisor_id, personnel_id, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$group, $filename, $supervisor_id, $personnel_id]);
            $submission_id = $pdo->lastInsertId();

            // Insert each student's remark and datetime
            foreach ($student_ids as $sid) {
                $remark = $_POST['remark_'.$sid] ?? '';
                $created_at = $_POST['created_at_'.$sid] ?? date('Y-m-d H:i:s');

                $stmt2 = $pdo->prepare("
                    INSERT INTO submission_students 
                    (submission_id, student_id, remark, created_at) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt2->execute([$submission_id, $sid, $remark, $created_at]);
            }

            header("Location: group_submission.php?m=✅ Submission successful!");
            exit;
        } else {
            header("Location: group_submission.php?m=❌ Failed to move uploaded file.");
            exit;
        }
    } else {
        header("Location: group_submission.php?m=❌ File upload error.");
        exit;
    }
} else {
    header("Location: group_submission.php");
    exit;
}
