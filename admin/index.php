<?php
// Always start session at the very top
if (session_status() === PHP_SESSION_NONE) session_start();

// Include DB connection
require_once __DIR__ . '/../includes/db_connect.php';

$msg = '';

// ======================
// Handle logout if requested (optional)
// ======================
// if you want to force logout via GET parameter
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

// ======================
// Redirect if already logged in
// ======================
if (!empty($_SESSION['admin'])) {
    header('Location: dashboard.php');
    exit;
}

// ======================
// Handle login form submission
// ======================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username !== '' && $password !== '') {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password'])) {
            // Successful login
            $_SESSION['admin'] = $admin['username'];
            header('Location: dashboard.php');
            exit;
        } else {
            $msg = 'Invalid username or password.';
        }
    } else {
        $msg = 'Please fill in both fields.';
    }
}

// Include site header
include __DIR__ . '/../includes/header.php';
?>

<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-12 col-sm-10 col-md-8 col-lg-5">
      <div class="card shadow-sm">
        <div class="card-body">
          <h3 class="text-center text-primary mb-3">Admin Login</h3>

          <?php if (!empty($msg)): ?>
            <div class="alert alert-danger text-center py-2"><?= htmlspecialchars($msg) ?></div>
          <?php endif; ?>

          <form method="post" autocomplete="off">
            <div class="mb-3">
              <label for="username" class="form-label">Username</label>
              <input id="username" name="username" class="form-control" placeholder="Enter username" required autofocus>
            </div>

            <div class="mb-3">
              <label for="password" class="form-label">Password</label>
              <input id="password" name="password" type="password" class="form-control" placeholder="Enter password" required>
            </div>

            <div class="d-grid">
              <button type="submit" class="btn btn-primary">Login</button>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>
</div>

<?php
// Include site footer
include __DIR__ . '/../includes/footer.php';
?>
