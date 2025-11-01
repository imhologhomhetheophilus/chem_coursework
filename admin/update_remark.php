<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['admin'])) {
    http_response_code(403);
    echo "Unauthorized";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['submission_id'] ?? null;
    $remark = $_POST['admin_remark'] ?? null;
    $score = $_POST['admin_score'] ?? null;

    if (!$id) {
        http_response_code(400);
        echo "Invalid data.";
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE submissions SET admin_remark = ?, admin_score = ? WHERE id = ?");
        $stmt->execute([$remark, $score, $id]);
        echo "✅ Updated successfully!";
    } catch (PDOException $e) {
        http_response_code(500);
        echo "❌ Update failed: " . $e->getMessage();
    }
}
?>
