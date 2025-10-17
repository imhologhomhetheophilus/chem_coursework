<?php
// AlwaysData MySQL connection configuration
$host = 'mysql-chemcoursework.alwaysdata.net'; // ✅ Use your actual AlwaysData MySQL host
$dbname = 'chemcoursework_chemcoursework';         // ✅ Replace with your real database name
$username = '435841_chemuser';                // ✅ Replace with your AlwaysData username
$password = 'jobjacob123@';  // ✅ Replace with your actual database password
$port = 3306;                          // AlwaysData default MySQL port

try {
    // Create a new PDO connection
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);

    // Enable error reporting (throws exceptions)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Optional: echo a message for debugging (remove in production)
    // echo "✅ Database connection successful!";
} catch (PDOException $e) {
    // Display a user-friendly message if connection fails
    die("DB Connection failed: " . $e->getMessage());
}
?>

