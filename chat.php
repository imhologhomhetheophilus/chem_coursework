<?php
session_start();
require_once '../includes/db_connect.php';
if(!isset($_SESSION['username'])) header('Location: login.php');

$user = $_SESSION['username'];
$role = $_SESSION['role'];
$chatWith = ($role=='admin') ? $_GET['user'] ?? null : 'admin';

// Mark messages as read
$stmt = $conn->prepare("UPDATE messages SET is_read=1 WHERE sender=? AND receiver=?");
$stmt->bind_param("ss", $chatWith, $user);
$stmt->execute();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Live Chat</title>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex flex-col min-h-screen bg-gray-100">

<header class="bg-blue-600 text-white p-4 shadow-md flex justify-between items-center">
  <h1 class="text-2xl font-bold">Chat</h1>
  <div>
    <span>You: <strong><?php echo htmlspecialchars($user); ?></strong></span>
    <a href="<?php echo $role=='admin' ? 'chat_list.php' : 'dashboard.php'; ?>" class="ml-4 underline">Back</a>
  </div>
</header>

<main class="flex-grow container mx-auto p-4 flex flex-col">
  <div id="chat-box" class="flex-grow bg-white border rounded-lg shadow-md p-4 overflow-y-auto flex flex-col space-y-2 h-[500px]"></div>
  <form id="chat-form" class="mt-4 flex gap-2">
    <input type="text" id="message" placeholder="Type a message..." class="flex-grow border border-gray-300 rounded-full px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-full hover:bg-blue-700 transition">Send</button>
  </form>
</main>

<footer class="bg-gray-800 text-white py-4 text-center mt-auto">
  <p class="text-sm">&copy; <?php echo date('Y'); ?> Chem Coursework Project</p>
</footer>

<script>
$(document).ready(function(){
    function loadMessages() {
        $.get("fetch_messages.php?user=<?php echo urlencode($chatWith); ?>", function(data){
            $("#chat-box").html(data);
            $("#chat-box").scrollTop($("#chat-box")[0].scrollHeight);
        });
    }
    loadMessages();
    setInterval(loadMessages, 2000);

    $("#chat-form").on("submit", function(e){
        e.preventDefault();
        var msg = $("#message").val();
        $.post("send_message.php", { message: msg, chatWith: "<?php echo $chatWith; ?>" }, function(){
            $("#message").val('');
            loadMessages();
        });
    });
});
</script>

</body>
</html>
