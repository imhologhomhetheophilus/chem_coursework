<?php
$host = 'localhost';
$db   = 'chem_coursework';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (Exception $e) {
    exit('DB Connection failed: ' . $e->getMessage());
}



// $servername = "db4free.net";
// $username   = "chem_admin";
// $password   = "yourpassword";
// $database   = "chem_coursework";
// $port       = 3306;

// $conn = new mysqli($servername, $username, $password, $database, $port);

// if ($conn->connect_error) {
//     die("Connection failed: " . $conn->connect_error);
// }
?>
