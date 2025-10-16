<?php
$host = "mydb-xxxx.render.com";
$user = "render_user";
$pass = "render_password";
$dbname = "render_dbname";
$port = "3306";

try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "✅ Database connected successfully";
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}
?>
