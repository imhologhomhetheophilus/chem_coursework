<?php
session_start();
require 'includes/db_connect.php';

if (!isset($_SESSION['group_id'])) {
    header("Location: group_login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submission_id = $_POST['submission_id'];
    $supervisor_id = $_POST['supervisor_id'];
    $personnel_id = $_POST['personnel_id'];
    $group_id = $_SESSION['group_id'];
    $file_name = null;

    // Handle file upload (if user uploaded a new file)
    $upload_dir = __DIR__ . '/uploads/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    if (!empty($_FILES['file']['name'])) {
        $original_name = basename($_FILES['file']['name']);
        $safe_name = preg_replace("/[^A-Za-z0-9._-]/", "_", $original_name);
        $unique_name = time() . "_" . $safe_name;
        $target_path = $upload_dir . $unique_name;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $target_path)) {
            $file_name = $unique_name;

            // Delete old file if exists
            $old_file = $pdo->prepare("SELECT file_name FROM submissions WHERE id = ?");
            $old_file->execute([$submission_id]);
            $old_name = $old_file->fetchColumn();
            if ($old_name && file_exists($upload_dir . $old_name)) {
                unlink($upload_dir . $old_name);
            }
        }
    }

    // Update main submission
    $update_query = "UPDATE submissions 
                     SET supervisor_id = ?, personnel_id = ?" . ($file_name ? ", file_name = ?" : "") . " 
                     WHERE id = ? AND group_id = ?";
    $params = [$supervisor_id, $personnel_id];
    if ($file_name) $params[] = $file_name;
    $params[] = $submission_id;
    $params[] = $group_id;

    $stmt = $pdo->prepare($update_query);
    $stmt->execute($params);

    // Update remarks
    if (!empty($_POST['student_ids'])) {
        foreach ($_POST['student_ids'] as $student_id) {
            $remark_key = "remark_" . $student_id;
            $remark = $_POST[$remark_key] ?? "Not Cleared";

            $pdo->prepare("
                UPDATE submission_students 
                SET remark = ? 
                WHERE submission_id = ? AND student_id = ?
            ")->execute([$remark, $submission_id, $student_id]);
        }
    }

    $_SESSION['success'] = "Submission updated successfully!";
    header("Location: submission.php");
    exit;
}
?>
