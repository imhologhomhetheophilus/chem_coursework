<?php
session_start();
require_once '../includes/db_connect.php';
$chatWith=$_GET['user']??'';
$stmt=$conn->prepare("SELECT is_typing FROM users WHERE username=?");
$stmt->bind_param("s",$chatWith);
$stmt->execute();
$stmt->bind_result($typing);
$stmt->fetch();
echo $typing;
?>
