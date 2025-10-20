<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once './includes/db_connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $regno = trim($_POST['regno'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($regno) && !empty($password)) {
        // Query uses regno, not group_id
        $stmt = $pdo->prepare("SELECT * FROM students WHERE regno = ? AND is_leader = 1");
        $stmt->execute([$regno]);
        $leader = $stmt->fetch(PDO::FETCH_ASSOC);

        // Password check
        if ($leader && hash('sha256', $password) === $leader['password']) {
            $_SESSION['leader_id'] = $leader['id'];
            $_SESSION['group_id'] = $leader['group_id'];
            $_SESSION['leader_name'] = $leader['name'];
            header('Location: leader_dashboard.php');
            exit;
        } else {
            $error = "Invalid registration number or password.";
        }
    } else {
        $error = "Please enter both registration number and password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Group Leader Login | Coursework</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">

<div class="card shadow-lg p-4" style="max-width: 400px; width: 100%;">
    <h3 class="text-center text-primary mb-3">Group Leader Login</h3>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger text-center py-2"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="mt-3">
        <div class="mb-3">
            <label for="regno" class="form-label">Registration Number</label>
            <input type="text" name="regno" id="regno" class="form-control" placeholder="Enter reg no" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" name="password" id="password" class="form-control" placeholder="Enter password" required>
        </div>

        <button type="submit" class="btn btn-success w-100">Login</button>
    </form>
</div>

</body>
</html>
