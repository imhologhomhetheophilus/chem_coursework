<?php

$servername = "435841_chemuser.mysql.db"; // Replace with AlwaysData host
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





























// $host = "mysql-chemcoursework.alwaysdata.net";
// $dbname ="chemcoursework_chemcoursework";                   // your actual DB name
// $user = "435841_chemuser";                           // your DB username
// $pass = "jobjacob123@";                       // your DB password
// $port = "3306";                               // default MySQL port

// try {
//     $pdo= new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $user, $pass);
//     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//     // echo "Connected successfully!";
// } catch (PDOException $e) {
//     die("DB Connection failed: " . $e->getMessage());
// }
?>
