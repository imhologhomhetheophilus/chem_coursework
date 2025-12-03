<?php
session_start();
require_once(__DIR__ . '/includes/db_connect.php');

if(!isset($_SESSION['username'])){
    header('Location: login.php');
    exit;
}

if(isset($_POST['message'])){
    $message = trim($_POST['message']);
    $username = $_SESSION['username'];

    $stmt = $pdo->prepare("INSERT INTO chat (username, message) VALUES (:username, :message)");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':message', $message);
    $stmt->execute();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Chat</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-4">
<div class="max-w-2xl mx-auto bg-white rounded shadow p-6">
<h2 class="text-2xl font-bold mb-4">Chat Room</h2>

<div class="mb-4">
<?php
$stmt = $pdo->prepare("SELECT username, message, created_at FROM chat ORDER BY created_at DESC LIMIT 50");
$stmt->execute();
$messages = $stmt->fetchAll();
foreach(array_reverse($messages) as $m){
    echo "<p><strong>".htmlspecialchars($m['username']).":</strong> ".htmlspecialchars($m['message'])."</p>";
}
?>
</div>

<form method="POST" class="flex gap-2">
<input type="text" name="message" placeholder="Type your message..." class="border p-2 rounded flex-1" required>
<button type="submit" class="bg-blue-600 text-white p-2 rounded hover:bg-blue-700">Send</button>
</form>

<p class="mt-4"><a href="logout.php" class="text-red-600 underline">Logout</a></p>
</div>
</body>
</html>
