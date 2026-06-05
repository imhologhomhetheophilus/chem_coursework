<?php
session_start();
require_once 'includes/db_connect.php';

if (!isset($_SESSION['group_id'])) {
    header('Location: group_login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: submission.php');
    exit;
}

$group_id = $_SESSION['group_id'];

$supervisor_id = $_POST['supervisor_id'] ?? null;
$personnel_id = $_POST['personnel_id'] ?? null;
$experiment_datetime = $_POST['experiment_datetime'] ?? date('Y-m-d H:i:s');

/* ======================
   VALIDATION
====================== */
if (
    empty($supervisor_id) ||
    empty($personnel_id) ||
    empty($_FILES['coursework_file']['name'])
) {
    header("Location: submission.php?error=missing_fields");
    exit;
}

/* ======================
   FILE UPLOAD
====================== */
$uploadDir = 'uploads/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$file = $_FILES['coursework_file'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    header("Location: submission.php?error=file_upload_failed");
    exit;
}

$allowed = ['pdf', 'doc', 'docx'];

$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($extension, $allowed)) {
    header("Location: submission.php?error=invalid_file_type");
    exit;
}

$newFileName = time() . '_' . preg_replace('/[^A-Za-z0-9\.\-_]/', '_', $file['name']);

$filePath = $uploadDir . $newFileName;

if (!move_uploaded_file($file['tmp_name'], $filePath)) {
    header("Location: submission.php?error=upload_failed");
    exit;
}

/* ======================
   SAVE SUBMISSION
====================== */

try {

    $stmt = $pdo->prepare("
        INSERT INTO submissions
        (
            group_id,
            supervisor_id,
            personnel_id,
            file_name,
            file_path,
            created_at,
            experiment_datetime
        )
        VALUES
        (
            ?, ?, ?, ?, ?, NOW(), ?
        )
    ");

    $stmt->execute([
        $group_id,
        $supervisor_id,
        $personnel_id,
        $file['name'],
        $filePath,
        $experiment_datetime
    ]);

    $submission_id = $pdo->lastInsertId();

    /* ======================
       SAVE STUDENT REMARKS
    ====================== */

    if (!empty($_POST['student_ids'])) {

        $studentStmt = $pdo->prepare("
            INSERT INTO submission_students
            (
                submission_id,
                student_id,
                remark
            )
            VALUES
            (
                ?, ?, ?
            )
        ");

        foreach ($_POST['student_ids'] as $student_id) {

            $remark = $_POST['remark_' . $student_id] ?? 'Not Cleared';

            $studentStmt->execute([
                $submission_id,
                $student_id,
                $remark
            ]);
        }
    }

    /* ======================
       SUCCESS REDIRECT
    ====================== */

    header("Location: submission.php?success=1");
    exit;

} catch (PDOException $e) {

    header("Location: submission.php?error=db_error");
    exit;
}
?>