<?php
session_start();
require_once '../includes/db_connect.php';
if(!isset($_SESSION['username']) || $_SESSION['role']!='admin') header('Location: login.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Chat Users</title>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex flex-col min-h-screen bg-gray-50">

<header class="bg-blue-600 text-white p-4 shadow-md">
  <div class="container mx-auto flex justify-between items-center">
    <h1 class="text-2xl font-bold">Chat Users</h1>
    <a href="dashboard.php" class="text-sm underline hover:text-gray-200">Dashboard</a>
  </div>
</header>

<main class="flex-grow container mx-auto p-6">
  <h2 class="text-xl font-semibold mb-4">Select a user to chat</h2>
  <div id="chat-users" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4"></div>
</main>

<footer class="bg-gray-800 text-white py-4 text-center mt-auto">
  <p class="text-sm">&copy; <?php echo date('Y'); ?> Chem Coursework Project</p>
</footer>

<script>
function loadUsers() {
    $.getJSON('fetch_unread.php', function(data){
        let html = '';
        for (let user in data) {
            let badge = data[user]>0 ? `<span class="absolute top-2 right-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full">${data[user]}</span>` : '';
            html += `<a href="chat.php?user=${encodeURIComponent(user)}" class="relative block bg-white shadow-md rounded-lg p-4 hover:shadow-lg transition">
                        <h3 class="text-lg font-bold text-blue-600">${user}</h3>
                        ${badge}
                     </a>`;
        }
        $('#chat-users').html(html);
    });
}
loadUsers();
setInterval(loadUsers, 3000);
</script>

</body>
</html>
