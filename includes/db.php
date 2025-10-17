<?php
// --- IMPORTANT ---
// There must be NOTHING (no spaces, no newlines) before this opening tag!

$host = 'mysql-chemcoursework.alwaysdata.net';
$dbname = 'chemcoursework_chemcoursework';
$username = '435841_chemuser';
$password = 'jobjacob123@';
$port = 3306;

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Log errors silently instead of echoing or dying
    error_log("DB Connection failed: " . $e->getMessage(), 3, __DIR__ . '/db_error.log');
    // Do NOT echo or print anything here!
    exit;
}

// --- IMPORTANT ---
// Do NOT close PHP tag here (no "?>"), this prevents accidental output.
