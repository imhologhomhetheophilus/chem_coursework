<?php
session_start();
require_once(__DIR__ . '/includes/db_connect.php');

$users = glob("typing_*.txt");
foreach($users as $u){
    $name = str_replace(['typing_', '.txt'], '', $u);
    echo htmlspecialchars($name) . " is typing...<br>";
}
?>
