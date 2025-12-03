<?php
session_start();
require_once(__DIR__ . '/includes/db_connect.php');
if(isset($_SESSION['username'])){
    $stmt = $pdo->prepare("UPDATE users SET is_typing=0 WHERE username=:username");
    $stmt->bindParam(':username', $_SESSION['username']);
    $stmt->execute();
}
?>
