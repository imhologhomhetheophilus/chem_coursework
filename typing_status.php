<?php
session_start();
require_once '../includes/db_connect.php';
$user=$_SESSION['username'];
$typing=$_POST['typing']?1:0;

$stmt=$conn->prepare("UPDATE users SET is_typing=? WHERE username=?");
$stmt->bind_param("is",$typing,$user);
$stmt->execute();
?>
