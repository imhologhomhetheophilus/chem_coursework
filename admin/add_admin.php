<?php
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['admin_name']);
    $email = trim($_POST['admin_email']);
    $password = password_hash($_POST['admin_password'], PASSWORD_BCRYPT);

    $check = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
        echo "⚠️ Admin with this email already exists.";
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO admins (name, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $password]);

    echo "✅ Admin added successfully!";
    exit;
}
?>
