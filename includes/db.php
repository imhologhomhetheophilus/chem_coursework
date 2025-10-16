<?php
$servername = "mysql-chemcoursework.alwaysdata.net"; // Replace with AlwaysData host
$username   = "435841_chemuser";            // Your DB username
$password   = "jobjacob123@";          // Your DB password
$database   = "chemcoursework_chemcoursework";            // Your DB name
$port       = 3306;

try {
    $pdo = new PDO(
        "mysql:host=$servername;port=$port;dbname=$database;charset=utf8",
        $username,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}
?>
