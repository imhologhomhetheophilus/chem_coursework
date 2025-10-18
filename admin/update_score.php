<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['admin'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['submission_id'] ?? null;
    $score = $_POST['score'] ?? null;

    if ($id && $score !== null) {
        $stmt = $pdo->prepare("UPDATE submissions SET score = ? WHERE id = ?");
        $stmt->execute([$score, $id]);
        header("Location: view_submissions.php?m=Score+updated+successfully!");
        exit;
    }
}
header("Location: view_submissions.php?m=Invalid+request");
exit;
?>
