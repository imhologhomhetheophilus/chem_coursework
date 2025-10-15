<?php
$servername = "db4free.net";
$username   = "chem_admin";
$password   = "yourpassword";
$database   = "chem_coursework";
$port       = 3306;

$conn = new mysqli($servername, $username, $password, $database, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
