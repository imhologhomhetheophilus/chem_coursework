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

if (
    empty($supervisor_id) ||
    empty($personnel_id) ||
    !isset($_FILES['file'])
) {
    die("Please complete all required fields.");
}

/* ======================
   FILE UPLOAD
====================== */

$uploadDir = 'uploads/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$file = $_FILES['file'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    die("File upload failed.");
}

$allowed = ['pdf', 'doc', 'docx'];

$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($extension, $allowed)) {
    die("Only PDF, DOC and DOCX files are allowed.");
}

$newFileName = time() . '_' . preg_replace('/[^A-Za-z0-9\.\-_]/', '_', $file['name']);

$filePath = $uploadDir . $newFileName;

if (!move_uploaded_file($file['tmp_name'], $filePath)) {
    die("Unable to save uploaded file.");
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

            $remark =
                $_POST['remark_' . $student_id]
                ?? 'Not Cleared';

            $studentStmt->execute([
                $submission_id,
                $student_id,
                $remark
            ]);
        }
    }

    header('Location: submission.php?success=1');
    exit;

} catch (PDOException $e) {

    die("Database Error: " . $e->getMessage());

}
?>