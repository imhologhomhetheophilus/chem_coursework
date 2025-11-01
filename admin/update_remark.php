<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['admin'])) {
    exit('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submission_id = $_POST['submission_id'];
    $student_id = $_POST['student_id'];
    $admin_remark = $_POST['admin_remark'];
    $score = $_POST['score'];

    $stmt = $pdo->prepare("
        UPDATE submission_students 
        SET admin_remark = :admin_remark, score = :score 
        WHERE submission_id = :submission_id AND student_id = :student_id
    ");
    $stmt->execute([
        ':admin_remark' => $admin_remark,
        ':score' => $score,
        ':submission_id' => $submission_id,
        ':student_id' => $student_id
    ]);

    echo "Saved successfully!";
}
