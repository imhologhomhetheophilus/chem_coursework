<?php
session_start();
require_once '../includes/db_connect.php';

// Only allow admin
if (!isset($_SESSION['admin'])) {
    http_response_code(403);
    echo "Unauthorized";
    exit;
}

// Get POST data
$submission_id = $_POST['submission_id'] ?? null;
$student_id = $_POST['student_id'] ?? null;
$admin_remark = $_POST['admin_remark'] ?? null;
$score = $_POST['score'] ?? null;

if (!$submission_id || !$student_id) {
    http_response_code(400);
    echo "Invalid data.";
    exit;
}

// Update or insert admin remark and score
try {
    // Check if record exists
    $check = $pdo->prepare("SELECT * FROM submission_students WHERE submission_id = ? AND student_id = ?");
    $check->execute([$submission_id, $student_id]);
    $exists = $check->fetch();

    if ($exists) {
        // Update existing record
        $stmt = $pdo->prepare("UPDATE submission_students 
            SET admin_remark = ?, score = ? 
            WHERE submission_id = ? AND student_id = ?");
        $stmt->execute([$admin_remark, $score, $submission_id, $student_id]);
    } else {
        // Insert new record if not exists
        $stmt = $pdo->prepare("INSERT INTO submission_students 
            (submission_id, student_id, admin_remark, score) VALUES (?, ?, ?, ?)");
        $stmt->execute([$submission_id, $student_id, $admin_remark, $score]);
    }

    echo "✅ Updated successfully!";
} catch (PDOException $e) {
    http_response_code(500);
    echo "❌ Update failed: " . $e->getMessage();
}
