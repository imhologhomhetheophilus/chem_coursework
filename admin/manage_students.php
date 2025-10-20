<?php
include('../includes/db_connect.php');
require('../includes/auth.php');

if (session_status() === PHP_SESSION_NONE) session_start();
require_admin();

// Add new student
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {
    $group_id = $_POST['group_id'] ?? '';
    $regno = $_POST['regno'] ?? '';
    $name = $_POST['name'] ?? '';

    if ($group_id && $regno && $name) {
        $stmt = $pdo->prepare('INSERT INTO students (group_id, regno, name) VALUES (?, ?, ?)');
        $stmt->execute([$group_id, $regno, $name]);
        header('Location: manage_students.php');
        exit;
    }
}

// Delete student
if (isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    $stmt = $pdo->prepare('DELETE FROM students WHERE id = ?');
    $stmt->execute([$id]);
    header('Location: manage_students.php');
    exit;
}

// Fetch groups and students
$groups = $pdo->query('SELECT * FROM groups')->fetchAll();
$rows = $pdo->query('SELECT * FROM students ORDER BY group_id, regno')->fetchAll();

include('../includes/header.php');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Students</h3>
  <a class="btn btn-sm btn-secondary" href="dashboard.php">Back</a>
</div>

<div class="card p-3 mb-3">
  <form method="post" class="row g-2">
    <div class="col-md-3">
      <select name="group_id" class="form-select" required>
        <option value="">Select group</option>
        <?php foreach ($groups as $g): ?>
          <option value="<?= htmlspecialchars($g['group_id']) ?>"><?= htmlspecialchars($g['group_id']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3">
      <input name="regno" class="form-control" placeholder="Reg No" required>
    </div>
    <div class="col-md-4">
      <input name="name" class="form-control" placeholder="Full name" required>
    </div>
    <div class="col-md-2">
      <button class="btn btn-primary" name="add">Add</button>
    </div>
  </form>
</div>

<table class="table table-striped">
  <thead>
    <tr>
      <th>#</th>
      <th>Group</th>
      <th>Reg No</th>
      <th>Name</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($rows as $i => $r): ?>
      <tr>
        <td><?= $i + 1 ?></td>
        <td><?= htmlspecialchars($r['group_id']) ?></td>
        <td><?= htmlspecialchars($r['regno']) ?></td>
        <td><?= htmlspecialchars($r['name']) ?></td>
        <td>
          <a class="btn btn-sm btn-danger" href="?del=<?= $r['id'] ?>" onclick="return confirm('Delete this student?')">Delete</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<div class="container py-5" style="margin-bottom: 10rem;"></div>
<?php include('../includes/footer.php'); ?>
