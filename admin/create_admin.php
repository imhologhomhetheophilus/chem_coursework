<?php
require_once '../includes/db_connect.php';

$username = 'admin';
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO admins (username, password) VALUES (?, ?) 
                       ON DUPLICATE KEY UPDATE password = VALUES(password)");
$stmt->execute([$username, $hash]);

echo "✅ Admin created or updated successfully.<br>";
echo "Username: admin<br>Password: admin123";
