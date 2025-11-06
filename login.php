<?php
session_start();
require_once '../includes/db_connect.php';
$msg='';

if(isset($_POST['login'])){
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt=$conn->prepare("SELECT id,password,role FROM users WHERE username=?");
    $stmt->bind_param("s",$username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id,$hash,$role);

    if($stmt->num_rows>0){
        $stmt->fetch();
        if(password_verify($password,$hash)){
            $_SESSION['username']=$username;
            $_SESSION['role']=$role;
            header('Location: dashboard.php');
            exit;
        }else{
            $msg="Incorrect password!";
        }
    }else{
        $msg="User not found!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100">
<div class="bg-white p-8 rounded shadow-md w-full max-w-md">
<h2 class="text-2xl font-bold mb-4">Login</h2>
<form method="POST" class="flex flex-col gap-4">
<input type="text" name="username" placeholder="Username" class="border p-2 rounded" required>
<input type="password" name="password" placeholder="Password" class="border p-2 rounded" required>
<button type="submit" name="login" class="bg-blue-600 text-white p-2 rounded hover:bg-blue-700">Login</button>
</form>
<p class="mt-4 text-red-600"><?php echo $msg; ?></p>
<p class="mt-2 text-sm">No account? <a href="signup.php" class="underline text-blue-600">Signup</a></p>
</div>
</body>
</html>
