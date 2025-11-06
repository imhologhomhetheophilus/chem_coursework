<?php
session_start();
require_once '../includes/db_connect.php';

$msg = '';

if(isset($_POST['signup'])){
    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    // Check if username exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if($stmt->num_rows > 0){
        $msg = "Username already taken!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, fullname, password, role) VALUES (?,?,?,?)");
        $stmt->bind_param("ssss", $username, $fullname, $password, $role);
        if($stmt->execute()){
            $msg = "Signup successful! <a href='login.php'>Login now</a>";
        } else {
            $msg = "Signup failed!";
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
    <input type="text" name="fullname" placeholder="Full Name" class="border p-2 rounded" required>
    <input type="text" name="username" placeholder="Username" class="border p-2 rounded" required>
    <input type="password" name="password" placeholder="Password" class="border p-2 rounded" required>
    <select name="role" class="border p-2 rounded" required>
        <option value="">Select Role</option>
        <option value="admin">Admin</option>
        <option value="group_leader">Group Leader</option>
    </select>
    <button type="submit" name="signup" class="bg-blue-600 text-white p-2 rounded hover:bg-blue-700">Signup</button>
</form>
<p class="mt-4 text-red-600"><?php echo $msg; ?></p>
<p class="mt-2 text-sm">Already have an account? <a href="login.php" class="underline text-blue-600">Login</a></p>
</div>

</body>
</html>
