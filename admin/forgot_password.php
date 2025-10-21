<?php
session_start();
require_once '../includes/db_connect.php';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');

    if ($username) {
        // Check if admin exists
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin) {
            // Generate a new random password
            $new_password = bin2hex(random_bytes(4)); // 8-character password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Update admin password
            $update = $pdo->prepare("UPDATE admins SET password = ? WHERE username = ?");
            $update->execute([$hashed_password, $username]);

            $msg = "✅ Password reset successfully. Your new password is: <strong>$new_password</strong>";
            // You can also email $new_password to admin if you have email column later
        } else {
            $msg = "❌ Username not found.";
        }
    } else {
        $msg = "❌ Please enter your username.";
    }
}

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm p-4">
                <h3 class="text-center mb-3">Forgot Password</h3>

                <?php if($msg): ?>
                    <div class="alert alert-info"><?= $msg ?></div>
                <?php endif; ?>

                <form method="post">
                    <div class="mb-3">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Reset Password</button>
                    </div>
                </form>

                <p class="mt-3 text-center">
                    <a href="index.php">Back to Login</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
