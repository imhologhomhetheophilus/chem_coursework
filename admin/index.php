<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// ✅ Use an absolute-safe path (works everywhere)
require_once __DIR__ . '/../includes/db_connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && hash('sha256', $password) === $admin['password']) {
            $_SESSION['admin'] = $admin['username'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Please fill in both fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">

<div class="card shadow-lg p-4" style="max-width: 400px; width: 100%;">
    <h3 class="text-center text-primary mb-3">Admin Login</h3>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger text-center py-2"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="mt-3">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" name="username" id="username" class="form-control" placeholder="Enter username" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" name="password" id="password" class="form-control" placeholder="Enter password" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
</div>

</body>
</html>
