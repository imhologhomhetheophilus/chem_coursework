<?php
include '../includes/db_connect.php';
require '../includes/auth.php';
if (session_status() === PHP_SESSION_NONE) session_start();
require_admin();

$id = $_GET['id'] ?? null;
if($id){
    $stmt = $pdo->prepare("SELECT file_name FROM submissions WHERE id=?");
    $stmt->execute([$id]);
    $file = $stmt->fetchColumn();

    if($file && file_exists("../uploads/$file")){
        unlink("../uploads/$file");
    }

    $pdo->prepare("DELETE FROM submissions WHERE id=?")->execute([$id]);
}

header("Location: submissions.php");
exit;
?>
