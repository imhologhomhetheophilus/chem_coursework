<?php
session_start();
require 'includes/db.php';      // Make sure this defines $pdo
require 'includes/auth.php';
require_leader();               // Ensure only group leaders can access

$group_id = $_SESSION['group_id'];
$message = '';

// Create uploads folder if it doesn't exist
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Handle submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = $_FILES['file'] ?? null;
    $submission_time = $_POST['created_at'] ?? date('Y-m-d H:i:s');
    $student_data = $_POST['students'] ?? [];

    if ($file && $file['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $group_id . '_' . time() . '.' . $ext;
        $target = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $target)) {
            // Insert submission
            $stmt = $pdo->prepare("INSERT INTO submissions (group_id, file_name, created_at) VALUES (?, ?, ?)");
            $stmt->execute([$group_id, $filename, $submission_time]);
            $submission_id = $pdo->lastInsertId();

            // Insert student remarks
            foreach ($student_data as $student_id => $data) {
                $remark = $data['remark'] ?? '';
                $supervisor_id = $data['supervisor_id'] ?? null;
                $personnel_id = $data['personnel_id'] ?? null;
                $student_time = $data['created_at'] ?? $submission_time;

                $stmt2 = $pdo->prepare("
                    INSERT INTO submission_students 
                    (submission_id, student_id, supervisor_id, personnel_id, remark, created_at)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt2->execute([$submission_id, $student_id, $supervisor_id, $personnel_id, $remark, $student_time]);
            }

            header("Location: group_submission.php?m=" . urlencode("✅ Submission successful!"));
            exit;
        } else {
            $message = "❌ Failed to move uploaded file.";
        }
    } else {
        $message = "❌ No file uploaded or file upload error.";
    }
}

// If not redirecting, show error message
if ($message) {
    echo "<div class='alert alert-danger'>$message</div>";
}
?>
