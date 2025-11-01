<?php
session_start();
require 'includes/db_connect.php';
require 'includes/auth.php';

if (!isset($_SESSION['group_id'])) {
    header('Location: group_login.php');
    exit;
}

$group_id = $_SESSION['group_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supervisor_id = $_POST['supervisor_id'];
    $personnel_id = $_POST['personnel_id'];
    $created_at = $_POST['created_at'];
    $experiment_datetime = $_POST['experiment_datetime'];
    $student_ids = $_POST['student_ids'];

    // Handle file upload
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['file']['tmp_name'];
        $file_name = basename($_FILES['file']['name']);
        move_uploaded_file($file_tmp, __DIR__ . '/uploads/' . $file_name);
    } else {
        $file_name = null;
    }

    // Insert into submissions
    $stmt = $pdo->prepare("
        INSERT INTO submissions (group_id, supervisor_id, personnel_id, file_name, created_at, experiment_datetime)
        VALUES (:group_id, :supervisor_id, :personnel_id, :file_name, :created_at, :experiment_datetime)
    ");
    $stmt->execute([
        ':group_id' => $group_id,
        ':supervisor_id' => $supervisor_id,
        ':personnel_id' => $personnel_id,
        ':file_name' => $file_name,
        ':created_at' => $created_at,
        ':experiment_datetime' => $experiment_datetime
    ]);

    $submission_id = $pdo->lastInsertId();

    // Insert leader remarks for each student
    foreach ($student_ids as $st_id) {
        $stmt2 = $pdo->prepare("
            INSERT INTO submission_students (submission_id, student_id, remark)
            VALUES (:submission_id, :student_id, :remark)
        ");
        $stmt2->execute([
            ':submission_id' => $submission_id,
            ':student_id' => $st_id,
            ':remark' => $_POST['remark_'.$st_id] ?? 'Not Cleared'
        ]);
    }

    header("Location: submission.php");
    exit;
}
