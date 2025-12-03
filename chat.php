<?php
session_start();
if(!isset($_SESSION['username'])){
    header('Location: login.php');
    exit;
}
$username = $_SESSION['username'];
$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Chat Room</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
#chat-messages { overflow-y: auto; flex: 1; }
</style>
</head>
<body class="bg-gray-100 p-4">
<div class="max-w-2xl mx-auto bg-white rounded shadow p-6 flex flex-col h-[80vh]">
<h2 class="text-2xl font-bold mb-4">Chat Room</h2>

<div id="chat-messages" class="mb-4 space-y-2 p-2 border rounded bg-gray-50"></div>

<form id="chat-form" class="flex gap-2">
<input type="text" id="message" placeholder="Type your message..." class="border p-2 rounded flex-1" required>
<button type="submit" class="bg-blue-600 text-white p-2 rounded hover:bg-blue-700">Send</button>
</form>

<p class="mt-4"><a href="logout.php" class="text-red-600 underline">Logout</a></p>
</div>

<script>
let ws = new WebSocket('ws://localhost:8080'); // Replace localhost with your server IP

ws.onmessage = function(event){
    const chat = document.getElementById('chat-messages');
    chat.innerHTML += event.data;
    chat.scrollTop = chat.scrollHeight;
};

document.getElementById('chat-form').onsubmit = function(e){
    e.preventDefault();
    let message = document.getElementById('message').value.trim();
    if(!message) return;
    let data = {
        username: "<?php echo $username; ?>",
        role: "<?php echo $role; ?>",
        message: message
    };
    ws.send(JSON.stringify(data));
    document.getElementById('message').value = '';
};
</script>
</body>
</html>
