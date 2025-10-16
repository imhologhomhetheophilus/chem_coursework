<?php
$host = "mysql-chemcoursework.alwaysdata.net";  // AlwaysData host
$db   = "chemcoursework_chemcoursework";       // Database name
$user = "435841_chemuser";                     // DB username
$pass = "jobjacob123@";                        // DB password
$port = 3306;

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}
?>
