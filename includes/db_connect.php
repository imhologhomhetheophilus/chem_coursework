<?php
$host = "mysql-chemcoursework.alwaysdata.net";
$db   = "chemcoursework_chemcoursework";
$user = "435841_chemuser";
$pass = "jobjacob123@";
$port = 3306;

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}
?>
