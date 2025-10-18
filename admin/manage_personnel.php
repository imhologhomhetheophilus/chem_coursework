<?php
// Start session
if (session_status() === PHP_SESSION_NONE) session_start();
require('../includes/db_connect.php');
require('../includes/auth.php');
require_admin();

// Initialize message variable
$msg = '';
$msg_type = 'success'; // can be 'success' or 'danger'

// Handle add personnel
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $name = trim($_POST['name'] ?? '');
    $code = trim($_POST['code'] ?? '');

    if ($name) {
        $stmt = $pdo->prepare('INSERT INTO personnel (name, code) VALUES (?, ?)');
        $stmt->execute([$name, $code]);
        $msg = "Personnel '$name' added successfully!";
        $msg_type = 'success';
    } else {
        $msg = "Name is required!";
        $msg_type = 'danger';
    }
}

// Handle delete personnel
if (isset($_GET['del'])) {
    $id = (int)$_GET['del'];

    // Fetch name before deleting
    $stmt = $pdo->prepare('SELECT name FROM personnel WHERE id=?');
    $stmt->execute([$id]);
    $person = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($person) {
        $stmt = $pdo->prepare('DELETE FROM personnel WHERE id=?');
        $stmt->execute([$id]);
        $msg = "Personnel '" . htmlspecialchars($person['name']) . "' deleted successfully!";
        $msg_type = 'success';
    } else {
        $msg = "Personnel not found!";
        $msg_type = 'danger';
    }
}

// Fetch all personnel
$rows = $pdo->query('SELECT * FROM personnel ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);

include('../includes/header.php');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Personnel</h3>
    <a class="btn btn-sm btn-secondary" href="dashboard.php">Back</a>
</div>

<!-- Show message -->
<?php if ($msg): ?>
    <div class="alert alert-<?= $msg_type ?> alert-dismissible fade show" role="alert" id="msgAlert">
        <?= $msg ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Add Personnel Form -->
<div class="card p-3 mb-3">
    <form method="post" class="row g-2">
        <div class="col">
            <input name="name" class="form-control" placeholder="Name" required>
        </div>
        <div class="col">
            <input name="code" class="form-control" placeholder="Code e.g. LAB01">
        </div>
        <div class="col-auto">
            <button class="btn btn-primary" name="add">Add</button>
        </div>
    </form>
</div>

<!-- Personnel Table -->
<table class="table table-striped">
    <thead>
        <tr>
            <th>#</th>
            <th>Name</th>
            <th>Code</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($rows as $i => $r): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($r['name']) ?></td>
                <td><?= htmlspecialchars($r['code'] ?? '—') ?></td>
                <td>
                    <a class="btn btn-sm btn-danger" href="?del=<?= $r['id'] ?>" onclick="return confirm('Delete?')">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="container py-5" style="margin-bottom: 10rem;"></div>

<?php include('../includes/footer.php'); ?>

<script>
// Auto-hide alert after 4 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alertBox = document.getElementById('msgAlert');
    if(alertBox){
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alertBox);
            bsAlert.close();
        }, 4000);
    }
});
</script>
