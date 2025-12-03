<?php
session_start();
require_once(__DIR__ . '/includes/db_connect.php');

if(!isset($_SESSION['username'])) exit;

$currentUser = $_SESSION['username'];

$stmt = $pdo->prepare("SELECT c.username, c.message, c.created_at, u.profile_pic, u.role 
                       FROM chat c 
                       JOIN users u ON c.username = u.username 
                       ORDER BY c.created_at ASC LIMIT 50");
$stmt->execute();
$messages = $stmt->fetchAll();

foreach($messages as $m){
    $time = date("H:i", strtotime($m['created_at']));
    $isSelf = $m['username'] === $currentUser;
    $align = $isSelf ? 'justify-end' : 'justify-start';
    $bg = $isSelf ? 'bg-blue-100' : 'bg-gray-100';
    echo "<div class='flex $align mb-2 animate-fade'>
            <img src='uploads/".htmlspecialchars($m['profile_pic'])."' class='w-8 h-8 rounded-full ".($isSelf ? 'order-2 ml-2' : 'mr-2')."'>
            <div class='p-2 rounded-lg $bg max-w-xs'>
                <strong>".htmlspecialchars($m['username']);
    if($m['role'] == 'admin') echo " 🛡";
    if($m['role'] == 'group_leader') echo " ⭐";
    echo ":</strong> ".htmlspecialchars($m['message'])." 
                <div class='text-xs text-gray-500 text-right'>$time</div>
            </div>
          </div>";
}
?>
