<?php
session_start();
require_once '../includes/db_connect.php';

// Only allow admin
if (!isset($_SESSION['admin'])) {
    http_response_code(403);
    echo "Unauthorized";
    exit;
}

// Get POST data
$submission_id = $_POST['submission_id'] ?? null;
$admin_remark = $_POST['admin_remark'] ?? null;
$admin_score = $_POST['admin_score'] ?? null;

if (!$submission_id) {
    http_response_code(400);
    echo "Invalid data.";
    exit;
}

// Update the submission record
try {
    $stmt = $pdo->prepare("UPDATE submissions SET admin_remark = ?, admin_score = ? WHERE id = ?");
    $stmt->execute([$admin_remark, $admin_score, $submission_id]);
    echo "✅ Updated successfully!";
} catch (PDOException $e) {
    http_response_code(500);
    echo "❌ Update failed: " . $e->getMessage();
}
