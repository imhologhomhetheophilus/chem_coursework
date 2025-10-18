<?php
include('../includes/db_connect.php');
require('../includes/auth.php');
if (session_status() === PHP_SESSION_NONE) session_start();
require_admin();

// handle add
if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['add'])){
  $name = $_POST['name'] ?? '';
  if($name){ $pdo->prepare('INSERT INTO supervisors (name) VALUES (?)')->execute([$name]); header('Location: manage_supervisors.php'); exit; }
}
// handle delete
if($_GET['del'] ?? false){
  $id = (int)$_GET['del']; $pdo->prepare('DELETE FROM supervisors WHERE id=?')->execute([$id]); header('Location: manage_supervisors.php'); exit;
}

$rows = $pdo->query('SELECT * FROM supervisors')->fetchAll();

include('../includes/header.php');
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Supervisors</h3>
  <a class="btn btn-sm btn-secondary" href="dashboard.php">Back</a>
</div>
<div class="card p-3 mb-3">
  <form method="post" class="row g-2">
    <div class="col"><input name="name" class="form-control" placeholder="Supervisor name" required></div>
    <div class="col-auto"><button class="btn btn-primary" name="add">Add</button></div>
  </form>
</div>
<table class="table table-striped"><thead><tr><th>#</th><th>Name</th><th>Action</th></tr></thead><tbody>
  <?php foreach($rows as $i=>$r): ?>
    <tr><td><?=$i+1?></td><td><?=htmlspecialchars($r['name'])?></td><td><a class="btn btn-sm btn-danger" href="?del=<?=$r['id']?>" onclick="return confirm('Delete?')">Delete</a></td></tr>
  <?php endforeach; ?>
</tbody></table>
<div class="container py-5" style="margin-bottom: 10rem;"></div>
<?php include('../includes/footer.php'); ?>