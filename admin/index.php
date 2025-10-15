<?php
require '../includes/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();
$msg = '';
if($_SERVER['REQUEST_METHOD']=='POST'){
    $u = $_POST['username'] ?? '';
    $p = $_POST['password'] ?? '';
    $st = $pdo->prepare('SELECT * FROM admins WHERE username = ?');
    $st->execute([$u]);
    $a = $st->fetch();
    if($a){
        $stored = $a['password'] ?? '';
        $ok = false;
        if (password_verify($p, $stored)) $ok = true;
        elseif ($p === $stored) {
            $hash = password_hash($p, PASSWORD_DEFAULT);
            $u2 = $pdo->prepare('UPDATE admins SET password = ? WHERE id = ?');
            $u2->execute([$hash, $a['id']]);
            $ok = true;
        }
        if($ok){
            $_SESSION['admin'] = $u;
            header('Location: dashboard.php'); exit;
        }
    }
    $msg = 'Invalid credentials';
}
include '../includes/header.php';
?>
<div class="card mx-auto animate__animated animate__backInUp" style="max-width:480px">
  <div class="card-body">
    <h4 class="card-title">Admin Login</h4>
    <?php if($msg): ?><div class="alert alert-danger"><?=$msg?></div><?php endif; ?>
    <form method="post">
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input name="username" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input name="password" type="password" class="form-control" required>
      </div>
      <div class="text-center">
        <button class="btn btn-primary">Login</button>
         <a href="chem_coursework/index.php" >Back</a>
      </div>
    </form>
  </div>
</div>
<?php include '../includes/footer.php'; ?>