<?php
session_start();
require_once(__DIR__ . '/includes/db_connect.php');

$active_time = date("Y-m-d H:i:s", strtotime("-1 minute"));
$stmt = $pdo->prepare("SELECT username, profile_pic, role, is_typing FROM users WHERE last_activity >= :active_time ORDER BY username ASC");
$stmt->bindParam(':active_time', $active_time);
$stmt->execute();
$users = $stmt->fetchAll();

foreach($users as $u){
    $badge='';
    if($u['role']=='admin') $badge=' 🛡';
    if($u['role']=='group_leader') $badge=' ⭐';
    $profile = !empty($u['profile_pic']) ? 'uploads/'.htmlspecialchars($u['profile_pic']) : 'uploads/default.png';
    $typing = $u['is_typing'] ? ' <span class="text-gray-400 text-xs">typing...</span>' : '';

    echo "<div class='flex items-center gap-2 mb-2'>
            <img src='$profile' class='w-6 h-6 rounded-full object-cover'>
            <span>".htmlspecialchars($u['username'])."$badge$typing</span>
          </div>";
}
?>
