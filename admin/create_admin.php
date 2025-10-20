<?php
session_start();
require_once '../includes/db_connect.php';

// ✅ Default admin credentials
$defaultUsername = 'admin';
$defaultPassword = 'admin123';

// 1. Ensure the table exists
$pdo->exec("
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
");

// 2. Check if admin already exists
$stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
$stmt->execute([$defaultUsername]);
$admin = $stmt->fetch();

if ($admin) {
    // 3. Update password if admin exists
    $hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);
    $update = $pdo->prepare("UPDATE admins SET password=? WHERE username=?");
    $update->execute([$hashedPassword, $defaultUsername]);
    echo "✅ Admin password reset successfully.<br>";
} else {
    // 4. Insert new admin
    $hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);
    $insert = $pdo->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
    $insert->execute([$defaultUsername, $hashedPassword]);
    echo "✅ Admin account created successfully.<br>";
}

echo "Username: <strong>$defaultUsername</strong><br>";
echo "Password: <strong>$defaultPassword</strong><br>";
echo "<br>Delete this file after running for security!";
