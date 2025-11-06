<?php
session_start();
require_once '../includes/db_connect.php';
if(!isset($_SESSION['username'])) exit;

$user=$_SESSION['username'];
$chatWith=$_GET['user']??'';
if($chatWith=='') exit;

$stmt=$conn->prepare("SELECT * FROM messages WHERE (sender=? AND receiver=?) OR (sender=? AND receiver=?) ORDER BY timestamp ASC");
$stmt->bind_param("ssss",$user,$chatWith,$chatWith,$user);
$stmt->execute();
$result=$stmt->get_result();

while($row=$result->fetch_assoc()){
    $isSender=$row['sender']==$user;
    $align=$isSender?'self-end bg-blue-600 text-white':'self-start bg-gray-200 text-gray-900';
    $time=date('H:i',strtotime($row['timestamp']));
    echo "<div class='flex flex-col max-w-[70%] $align px-4 py-2 rounded-2xl'>
            <p class='text-sm'><strong>{$row['sender']}:</strong> {$row['message']}</p>
            <span class='text-xs text-gray-700 self-end mt-1'>{$time}</span>
          </div>";
}
?>
