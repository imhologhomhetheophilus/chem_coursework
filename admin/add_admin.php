<?php
session_start();
require_once '../includes/db_connect.php';

// Set JSON response
header('Content-Type: application/json');

if (!isset($_SESSION['admin'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_name = trim($_POST['admin_name'] ?? '');
    $admin_email = trim($_POST['admin_email'] ?? '');
    $admin_password = $_POST['admin_password'] ?? '';

    if (empty($admin_name) || empty($admin_email) || empty($admin_password)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
        exit;
    }

    // Hash password
    $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

    try {
        // Insert into database
        $stmt = $pdo->prepare("INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$admin_name, $admin_email, $hashed_password]);

        echo json_encode(['status' => 'success', 'message' => 'Admin added successfully!']);
    } catch (PDOException $e) {
        // Handle duplicate email/username
        if ($e->getCode() == 23000) {
            echo json_encode(['status' => 'error', 'message' => 'Username or email already exists']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
