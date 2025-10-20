<?php
session_start();
require_once '../includes/db_connect.php';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if ($email) {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE email=?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin) {
            $temp_pass = bin2hex(random_bytes(4));
            $hashed = password_hash($temp_pass, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE admins SET password=? WHERE email=?");
            $update->execute([$hashed, $email]);
            $msg = "Temporary password: <strong>$temp_pass</strong> (Change after login)";
        } else {
            $msg = "Email not found.";
        }
    } else {
        $msg = "Enter your email.";
    }
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm p-4">
                <h3 class="text-center mb-3">Reset Password</h3>
                <?php if($msg): ?>
                    <div class="alert alert-info"><?= $msg ?></div>
                <?php endif; ?>
                <form method="post">
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="d-grid">
                        <button class="btn btn-warning">Reset Password</button>
                    </div>
                    <p class="mt-3 text-center"><a href="login.php">Back to Login</a></p>
                </form>
            </div>
        </div>
    </div>
</div>
