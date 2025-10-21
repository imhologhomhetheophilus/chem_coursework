<?php
require '../includes/db_connect.php';
session_start();

if (!isset($_SESSION['admin'])) {
    exit('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submission_id = $_POST['submission_id'] ?? 0;
    $student_id = $_POST['student_id'] ?? 0;
    $admin_remark = $_POST['admin_remark'] ?? '';
    $score = $_POST['score'] ?? null;

    if ($submission_id && $student_id) {
        $stmt = $pdo->prepare("
            UPDATE submission_students 
            SET admin_remark = ?, score = ? 
            WHERE submission_id = ? AND student_id = ?
        ");
        $stmt->execute([$admin_remark, $score, $submission_id, $student_id]);
        echo "✅ Record updated successfully!";
    } else {
        echo "❌ Invalid input data.";
    }
}
?>
