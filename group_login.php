<?php
require_once 'db_connect.php';
require 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$message = '';
if($_SERVER['REQUEST_METHOD']=='POST'){
    $group = strtoupper(trim($_POST['group_id'] ?? ''));
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM groups WHERE group_id = ?');
    $stmt->execute([$group]);
    $g = $stmt->fetch();
    if($g){
        $stored = $g['password'] ?? '';
        $ok = false;
        if (password_verify($password, $stored)) {
            $ok = true;
        } elseif ($password === $stored) {
            // plain-text stored; re-hash and update
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $u = $pdo->prepare('UPDATE groups SET password = ? WHERE id = ?');
            $u->execute([$hash, $g['id']]);
            $ok = true;
        }
        if($ok){
            $_SESSION['group_id'] = $group;
            header('Location: submission.php'); exit;
        }
    }
    $message = 'Invalid Group ID or password.';
}
include 'includes/header.php';
?>
<div class="card mx-auto animate__animated animate__backInUp" style="max-width:480px ">
  <div class="card-body">
    <h4 class="card-title">Group Leader Login</h4>
    <?php if($message): ?><div class="alert alert-danger"><?=$message?></div><?php endif; ?>
    <form method="post">
      <div class="mb-3">
        <label class="form-label">Group ID</label>
        <input name="group_id" class="form-control" placeholder="e.g. GP1" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input name="password" type="password" class="form-control" required>
      </div>
      <button class="btn btn-primary">Login</button>
      <a class="btn btn-link" href="index.php">Back</a>
    </form>
  </div>
</div>
<?php include 'includes/footer.php'; ?>