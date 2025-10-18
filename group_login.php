<?php
// Start session first before anything else
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection (before any HTML)
require_once 'includes/db_connect.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $group = strtoupper(trim($_POST['group_id'] ?? ''));
    $password = $_POST['password'] ?? '';

    try {
        // Use the correct connection variable ($conn)
        $stmt = $pdo->prepare('SELECT * FROM groups WHERE group_id = ?');
        $stmt->execute([$group]);
        $g = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($g) {
            $stored = $g['password'] ?? '';
            $ok = false;

            if (password_verify($password, $stored)) {
                $ok = true;
            } elseif ($password === $stored) {
                // Plain-text stored; re-hash and update for security
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
    } catch (PDOException $e) {
        $message = "Database error: " . $e->getMessage();
    }
}

// Include header only AFTER session & logic
include 'includes/header.php';
?>

<div class="card mx-auto animate__animated animate__backInUp" style="max-width:480px;">
  <div class="card-body">
    <h4 class="card-title text-center mb-3">Group Leader Login</h4>

    <?php if ($message): ?>
      <div class="alert alert-danger text-center"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="post">
      <div class="mb-3">
        <label class="form-label">Group ID</label>
        <input name="group_id" class="form-control" placeholder="e.g. GP1" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Password</label>
        <input name="password" type="password" class="form-control" required>
      </div>

      <div class="d-flex justify-content-between">
        <button class="btn btn-primary w-50 me-2">Login</button>
        <a class="btn btn-outline-secondary w-50" href="index.php">Back</a>
      </div>
    </form>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
