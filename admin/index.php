<?php
session_start();
require_once '../includes/db_connect.php';

// Redirect if already logged in
if (isset($_SESSION['admin'])) {
    header('Location: dashboard.php');
    exit;
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin'] = $admin['username'];
            header('Location: dashboard.php');
            exit;
        } else {
            $msg = 'Invalid username or password.';
        }
    } else {
        $msg = 'Please enter both username and password.';
    }
}

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm p-4">
                <h3 class="text-center mb-3">Admin Login</h3>

                <?php if($msg): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($msg) ?></div>
                <?php endif; ?>

                <form method="post">
                    <div class="mb-3">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                </form>

                <p class="mt-3 text-center">
                    <a href="forgot_password.php">Forgot Password?</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
