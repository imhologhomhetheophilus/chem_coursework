<?php
$host = "mysql-chemcoursework.alwaysdata.net";  // your actual host
$dbname "chemcoursework_chemcoursework";                   // your actual DB name
$user = "435841_chemuser";                           // your DB username
$pass = "jobjacob123@";                       // your DB password
$port = "3306";                               // default MySQL port

try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Connected successfully!";
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}
?>
