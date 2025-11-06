<?php
session_start();
require_once '../includes/db_connect.php';
if(!isset($_SESSION['username'])) exit;

$sender = $_SESSION['username'];
$receiver = $_POST['chatWith'] ?? '';
$message = trim($_POST['message']);

if($message!='' && $receiver!=''){
    $stmt = $conn->prepare("INSERT INTO messages (sender, receiver, message) VALUES (?,?,?)");
    $stmt->bind_param("sss", $sender, $receiver, $message);
    $stmt->execute();
}
?>
