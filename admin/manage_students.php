<?php
require '../includes/db.php';
require '../includes/auth.php';
if (session_status() === PHP_SESSION_NONE) session_start();
require_admin();

if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['add'])){
  $group_id = $_POST['group_id'] ?? ''; $reg_no = $_POST['reg_no'] ?? ''; $name = $_POST['name'] ?? '';
  if($group_id && $reg_no && $name){ $pdo->prepare('INSERT INTO students (group_id, reg_no, name) VALUES (?,?,?)')->execute([$group_id,$reg_no,$name]); header('Location: manage_students.php'); exit; }
}
if($_GET['del'] ?? false){ $id = (int)$_GET['del']; $pdo->prepare('DELETE FROM students WHERE id=?')->execute([$id]); header('Location: manage_students.php'); exit; }

$groups = $pdo->query('SELECT * FROM groups')->fetchAll();
$rows = $pdo->query('SELECT * FROM students ORDER BY group_id, reg_no')->fetchAll();

include '../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3"><h3>Students</h3><a class="btn btn-sm btn-secondary" href="dashboard.php">Back</a></div>
<div class="card p-3 mb-3">
  <form method="post" class="row g-2">
    <div class="col-md-3"><select name="group_id" class="form-select" required><option value="">Select group</option><?php foreach($groups as $g): ?><option value="<?=$g['group_id']?>"><?=htmlspecialchars($g['group_id'])?></option><?php endforeach; ?></select></div>
    <div class="col-md-3"><input name="reg_no" class="form-control" placeholder="Reg No" required></div>
    <div class="col-md-4"><input name="name" class="form-control" placeholder="Full name" required></div>
    <div class="col-md-2"><button class="btn btn-primary" name="add">Add</button></div>
  </form>
</div>
<table class="table table-striped"><thead><tr><th>#</th><th>Group</th><th>Reg No</th><th>Name</th><th>Action</th></tr></thead><tbody>
<?php foreach($rows as $i=>$r): ?><tr><td><?=$i+1?></td><td><?=htmlspecialchars($r['group_id'])?></td><td><?=htmlspecialchars($r['reg_no'])?></td><td><?=htmlspecialchars($r['name'])?></td><td><a class="btn btn-sm btn-danger" href="?del=<?=$r['id']?>" onclick="return confirm('Delete?')">Delete</a></td></tr><?php endforeach; ?>
</tbody></table>
<?php include '../includes/footer.php'; ?>