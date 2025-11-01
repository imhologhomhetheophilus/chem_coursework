<?php
session_start();
require 'includes/db_connect.php';
require 'includes/auth.php';

if (!isset($_SESSION['group_id'])) {
    header('Location: group_login.php');
    exit;
}

$group_id = $_SESSION['group_id'];
$upload_dir = __DIR__ . '/uploads/';

if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supervisor_id = $_POST['supervisor_id'];
    $personnel_id = $_POST['personnel_id'];
    $created_at = $_POST['created_at'];
    $experiment_datetime = $_POST['experiment_datetime'];

    // Handle file upload
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $file_name = time() . "_" . basename($_FILES['file']['name']);
        $file_path = 'uploads/' . $file_name;
        move_uploaded_file($_FILES['file']['tmp_name'], __DIR__ . '/' . $file_path);
    } else {
        $file_name = null;
        $file_path = null;
    }

    // Insert submission
    $stmt = $pdo->prepare("INSERT INTO submissions (group_id, supervisor_id, personnel_id, file_name, file_path, created_at, experiment_datetime) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$group_id, $supervisor_id, $personnel_id, $file_name, $file_path, $created_at, $experiment_datetime]);

    $submission_id = $pdo->lastInsertId();

    // Insert remarks for students
    foreach ($_POST['student_ids'] as $student_id) {
        $remark = $_POST['remark_' . $student_id] ?? 'Not Cleared';
        $stmt2 = $pdo->prepare("INSERT INTO submission_students (submission_id, student_id, remark) VALUES (?, ?, ?)");
        $stmt2->execute([$submission_id, $student_id, $remark]);
    }

    header('Location: group_dashboard.php?m=Submission+successful');
    exit;
}
?>
