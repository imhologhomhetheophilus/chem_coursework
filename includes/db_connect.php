<?php
$servername = "127.0.0.1";   // Use 127.0.0.1 instead of localhost
$username   = "root";         // Default username for local MySQL
$password   = "";             // Leave empty if you didn't set a password
$database   = "chem_coursework"; // Your database name
$port       = 3306;           // Default MySQL port

// Create connection
$conn = new mysqli($servername, $username, $password, $database, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
