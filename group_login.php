<?php
require 'includes/db_connect.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $group = strtoupper(trim($_POST['group_id'] ?? ''));
    $password = $_POST['password'] ?? '';

    // Fetch group from database
    $stmt = $pdo->prepare('SELECT * FROM groups WHERE group_id = ?');
    $stmt->execute([$group]);
    $g = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($g) {
        $stored = $g['password'] ?? '';
        $ok = false;

        // Check hashed password first
        if (!empty($stored) && password_verify($password, $stored)) {
            $ok = true;
        } elseif ($password === $stored) {
            // Legacy plain-text password: hash and update
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $u = $pdo->prepare('UPDATE groups SET password = ? WHERE id = ?');
            $u->execute([$hash, $g['id']]);
            $ok = true;
        }

        if ($ok) {
            $_SESSION['group_id'] = $group;
            header('Location: submission.php');
            exit;
        }
    }

    $message = 'Invalid Group ID or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Group Leader Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-12 col-sm-8 col-md-6 col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="card-title text-center mb-3">Group Leader Login</h4>

                    <?php if ($message): ?>
                        <div class="alert alert-danger text-center"><?= htmlspecialchars($message) ?></div>
                    <?php endif; ?>

                    <form method="post">
                        <div class="mb-3">
                            <label for="group_id" class="form-label">Group ID</label>
                            <input id="group_id" name="group_id" class="form-control" placeholder="e.g. GP1" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input id="password" name="password" type="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                        <a class="btn btn-link w-100 mt-2" href="index.php">Back</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS (optional) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
