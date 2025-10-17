<?php
// AlwaysData MySQL connection configuration
$host = 'mysql-chemcoursework.alwaysdata.net'; // ✅ Your actual AlwaysData MySQL host
$dbname = 'chemcoursework_chemcoursework';     // ✅ Your real database name
$username = '435841_chemuser';                 // ✅ Your AlwaysData username
$password = 'jobjacob123@';                    // ✅ Your actual database password
$port = 3306;                                  // AlwaysData default MySQL port

try {
    // Create a new PDO connection
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);

    // Enable error reporting (throws exceptions)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    // Log the error silently instead of sending output
    error_log("Database connection failed: " . $e->getMessage(), 3, __DIR__ . '/db_error.log');
    
    // Optional: show a clean error message without breaking headers
    // You can redirect to an error page if you like
    // header("Location: /error_page.php");
    exit;
}
?>
