<?php
session_start();
require_once '../includes/db_connect.php';
$user = $_SESSION['username'];
$role = $_SESSION['role'];

if($role=='admin'){
    $result = $conn->query("SELECT sender, COUNT(*) AS unread_count FROM messages WHERE receiver='admin' AND is_read=0 GROUP BY sender");
} else {
    $result = $conn->query("SELECT sender, COUNT(*) AS unread_count FROM messages WHERE receiver='$user' AND is_read=0 GROUP BY sender");
}

$unread = [];
while($row=$result->fetch_assoc()){
    $unread[$row['sender']]=$row['unread_count'];
}
echo json_encode($unread);
?>
