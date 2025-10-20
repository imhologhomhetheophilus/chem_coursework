<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$msg = '';
$admin_id = $_GET['id'] ?? null;

if (!$admin_id) {
    header('Location: admin_manage.php');
    exit;
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_pass = trim($_POST['password'] ?? '');
    if ($new_pass) {
        $hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
        $stmt->execute([$hash, $admin_id]);
        $msg = "✅ Password reset successfully!";
    } else {
        $msg = "❌ Password cannot be empty.";
    }
}

// Fetch admin info
$stmt = $pdo->prepare("SELECT username FROM admins WHERE id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();

include __DIR__ . '/../includes/header.php';
?>

<div class="container my-5">
    <h3>Reset Password for <?= htmlspecialchars($admin['username'] ?? '') ?></h3>

    <?php if ($msg): ?>
        <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label class="form-label">New Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button class="btn btn-success">Reset Password</button>
        <a href="admin_manage.php" class="btn btn-secondary">Back</a>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
