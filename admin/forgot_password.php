<?php
session_start();
require_once '../includes/db_connect.php';

// Include site header
include '../includes/header.php';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if ($email) {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE email=?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin) {
            // Generate temporary password
            $temp_pass = bin2hex(random_bytes(4));
            $hashed = password_hash($temp_pass, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE admins SET password=? WHERE email=?");
            $update->execute([$hashed, $email]);
            $msg = "✅ Temporary password: <strong>$temp_pass</strong> (Change after login)";
        } else {
            $msg = "❌ Email not found.";
        }
    } else {
        $msg = "⚠️ Enter your email.";
    }
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm p-4 border-0">
                <h3 class="text-center text-primary mb-4">Reset Admin Password</h3>

                <?php if ($msg): ?>
                    <div class="alert alert-info text-center"><?= $msg ?></div>
                <?php endif; ?>

                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="Enter your registered email" required>
                    </div>
                    <div class="d-grid">
                        <button class="btn btn-warning">Reset Password</button>
                    </div>
                    <p class="mt-3 text-center">
                        <a href="index.php" class="text-decoration-none">Back to Login</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Include site footer
include '../includes/footer.php';
?>
