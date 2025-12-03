<?php
session_start();
require_once(__DIR__ . '/includes/db_connect.php');

$msg = '';

if(isset($_POST['signup'])){
    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    // Handle profile picture upload
    $profile_pic = 'default.png'; // default image
    if(isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK){
        $ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
        $profile_pic = $username . '.' . $ext;
        move_uploaded_file($_FILES['profile_pic']['tmp_name'], __DIR__ . '/uploads/' . $profile_pic);
    }

    // Check if username exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->execute(['username'=>$username]);

    if($stmt->rowCount() > 0){
        $msg = "Username already taken!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (username, fullname, password, role, profile_pic) VALUES (:username, :fullname, :password, :role, :profile_pic)");
        if($stmt->execute([
            'username'=>$username,
            'fullname'=>$fullname,
            'password'=>$password,
            'role'=>$role,
            'profile_pic'=>$profile_pic
        ])){
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
<form method="POST" enctype="multipart/form-data" class="flex flex-col gap-4">
<input type="text" name="fullname" placeholder="Full Name" class="border p-2 rounded" required>
<input type="text" name="username" placeholder="Username" class="border p-2 rounded" required>
<input type="password" name="password" placeholder="Password" class="border p-2 rounded" required>
<select name="role" class="border p-2 rounded" required>
<option value="">Select Role</option>
<option value="admin">Admin</option>
<option value="group_leader">Group Leader</option>
<option value="user">User</option>
</select>
<label class="flex flex-col gap-1">
<span>Profile Picture</span>
<input type="file" name="profile_pic" accept="image/*" class="border p-2 rounded">
</label>
<button type="submit" name="signup" class="bg-blue-600 text-white p-2 rounded hover:bg-blue-700">Signup</button>
</form>
<p class="mt-4 text-red-600"><?php echo $msg; ?></p>
<p class="mt-2 text-sm">Already have an account? <a href="login.php" class="underline text-blue-600">Login</a></p>
</div>
</body>
</html>
