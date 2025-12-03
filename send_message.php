<?php
session_start();
require_once(__DIR__ . '/includes/db_connect.php');

if(!isset($_SESSION['username'])) exit;

if(isset($_POST['message'])){
    $message = trim($_POST['message']);
    if($message === '') exit;

    $stmt = $pdo->prepare("INSERT INTO chat (username, message) VALUES (:username, :message)");
    $stmt->bindParam(':username', $_SESSION['username']);
    $stmt->bindParam(':message', $message);
    $stmt->execute();
}
?>
