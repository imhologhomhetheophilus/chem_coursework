<?php
require_once '../includes/db_connect.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_name = trim($_POST['admin_name']);
    $admin_email = trim($_POST['admin_email']);
    $admin_password = password_hash($_POST['admin_password'], PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$admin_name, $admin_email, $admin_password]);
        echo json_encode(['status' => 'success', 'message' => 'Admin added successfully!']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>
