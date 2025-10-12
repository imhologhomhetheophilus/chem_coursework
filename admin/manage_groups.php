<?php
require '../includes/db.php';
require '../includes/auth.php';
if (session_status() === PHP_SESSION_NONE) session_start();
require_admin();

if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['add'])){
  $gid = strtoupper(trim($_POST['group_id'] ?? '')); $pw = $_POST['password'] ?? '';
  if($gid && $pw){ $hash = password_hash($pw, PASSWORD_DEFAULT); $pdo->prepare('INSERT INTO groups (group_id,password) VALUES (?,?)')->execute([$gid,$hash]); header('Location: manage_groups.php'); exit; }
}
if($_GET['del'] ?? false){ $id = (int)$_GET['del']; $pdo->prepare('DELETE FROM groups WHERE id=?')->execute([$id]); header('Location: manage_groups.php'); exit; }

$rows = $pdo->query('SELECT * FROM groups')->fetchAll();
include '../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3"><h3>Groups</h3><a class="btn btn-sm btn-secondary" href="dashboard.php">Back</a></div>
<div class="card p-3 mb-3"><form method="post" class="row g-2"><div class="col"><input name="group_id" class="form-control" placeholder="GP1" required></div><div class="col"><input name="password" class="form-control" placeholder="password" required></div><div class="col-auto"><button class="btn btn-primary" name="add">Create</button></div></form></div>
<table class="table table-striped"><thead><tr><th>#</th><th>Group ID</th><th>Action</th></tr></thead><tbody>
<?php foreach($rows as $i=>$r): ?><tr><td><?=$i+1?></td><td><?=htmlspecialchars($r['group_id'])?></td><td><a class="btn btn-sm btn-danger" href="?del=<?=$r['id']?>" onclick="return confirm('Delete?')">Delete</a></td></tr><?php endforeach; ?>
</tbody></table>
<?php include '../includes/footer.php'; ?>