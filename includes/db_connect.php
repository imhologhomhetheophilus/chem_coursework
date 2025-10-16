<?php
$host = "mydb-xxxx.render.com";  // change this
$user = "chem_user";             // your Render username
$pass = "your_password";         // your Render password
$dbname = "chem_coursework";     // your database name
$port = "3306";                  // port number

try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}
?>
