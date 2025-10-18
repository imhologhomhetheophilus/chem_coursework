<?php
// ✅ Start session safely before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Include DB connection using full path
require_once __DIR__ . 'includes/db_connect.php';

// Initialize message variable
$msg = '';

// ✅ Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';

    if ($u !== '' && $p !== '') {
        $st = $pdo->prepare('SELECT * FROM admins WHERE username = ?');
        $st->execute([$u]);
        $a = $st->fetch(PDO::FETCH_ASSOC);

        if ($a) {
            $stored = $a['password'] ?? '';
            $ok = false;

            // Verify password (hashed or plain)
            if (password_verify($p, $stored)) {
                $ok = true;
            } elseif ($p === $stored) {
                // Legacy plaintext password — rehash it
                $hash = password_hash($p, PASSWORD_DEFAULT);
                $u2 = $pdo->prepare('UPDATE admins SET password = ? WHERE id = ?');
                $u2->execute([$hash, $a['id']]);
                $ok = true;
            }

            if ($ok) {
                $_SESSION['admin'] = $u;
                header('Location: dashboard.php');
                exit;
            }
        }

        $msg = 'Invalid credentials.';
    } else {
        $msg = 'Please enter both username and password.';
    }
}

// ✅ Include header AFTER all PHP logic
include __DIR__ . 'includes/header.php';
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

<?php include __DIR__ . 'includes/footer.php'; ?>
