<?php
session_start();
require_once '../includes/db_connect.php';
if(!isset($_SESSION['username'])) header('Location: login.php');

$username = $_SESSION['username'];
$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-100 flex flex-col">

<header class="bg-blue-600 text-white p-4 shadow-md flex justify-between items-center">
    <h1 class="text-2xl font-bold">Dashboard</h1>
    <div>
        <span><?php echo htmlspecialchars($username); ?> (<?php echo $role; ?>)</span>
        <a href="logout.php" class="ml-4 underline hover:text-gray-200">Logout</a>
    </div>
</header>

<main class="flex-grow container mx-auto p-6">
<?php if($role == 'admin'): ?>
    <a href="chat_list.php" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">View Group Leaders Chat</a>
<?php else: ?>
    <a href="chat.php" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Chat with Admin</a>
<?php endif; ?>
</main>

<footer class="bg-gray-800 text-white py-4 text-center">
<p class="text-sm">&copy; <?php echo date('Y'); ?> Chem Coursework Project</p>
</footer>

</body>
</html>
