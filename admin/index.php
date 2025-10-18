<?php
session_start();
include('../includes/db_connect.php');

$msg = '';

// Redirect if already logged in
if (isset($_SESSION['admin'])) {
    header('Location: dashboard.php');
    exit;
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

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
}

include('../includes/header.php');
?>

<div class="card mx-auto mt-5 shadow-sm" style="max-width:480px;">
  <div class="card-body">
    <h4 class="card-title text-center text-primary mb-3">Admin Login</h4>

    <?php if ($msg): ?>
      <div class="alert alert-danger text-center"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <form method="post">
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input name="username" class="form-control" required autofocus>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input name="password" type="password" class="form-control" required>
      </div>
      <div class="text-center">
        <button class="btn btn-primary px-4">Login</button>
      </div>
    </form>
  </div>
</div>

<?php include('../includes/footer.php'); ?>
