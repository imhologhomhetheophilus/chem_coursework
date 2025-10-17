<?php
$host = "mysql-435841.alwaysdata.net"; // your AlwaysData MySQL host
$user = "435841_chemuser";             // your AlwaysData username
$pass = "jobjacob123@";                // your AlwaysData password
$dbname = "chemcoursework_chemcoursework"; // your AlwaysData DB name

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}

?>
