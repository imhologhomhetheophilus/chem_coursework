<?php
session_start();
require_once(__DIR__ . '/includes/db_connect.php');

$msg = '';

if(isset($_POST['signup'])){
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = 'user'; // default role

    // Check if username already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    if($stmt->rowCount() > 0){
        $msg = "Username already taken!";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, :role)");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $hash);
        $stmt->bindParam(':role', $role);

        if($stmt->execute()){
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;
            header('Location: dashboard.php');
            exit;
        } else {
            $msg = "Signup failed, try again!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Signup</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100">
<div class="bg-white p-8 rounded shadow-md w-full max-w-md">
<h2 class="text-2xl font-bold mb-4">Signup</h2>
<form method="POST" class="flex flex-col gap-4">
<input type="text" name="username" placeholder="Username" class="border p-2 rounded" required>
<input type="password" name="password" placeholder="Password" class="border p-2 rounded" required>
<button type="submit" name="signup" class="bg-green-600 text-white p-2 rounded hover:bg-green-700">Signup</button>
</form>
<p class="mt-4 text-red-600"><?php echo $msg; ?></p>
<p class="mt-2 text-sm">Already have an account? <a href="login.php" class="underline text-blue-600">Login</a></p>
</div>
</body>
</html>
