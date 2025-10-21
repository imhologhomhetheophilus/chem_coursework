<?php
session_start();
require_once '../includes/db_connect.php';

// Only allow logged-in admins
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

// Handle Add Admin form submission
$admin_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_admin_username'], $_POST['new_admin_email'], $_POST['new_admin_pass'])) {
    $username = trim($_POST['new_admin_username']);
    $email = trim($_POST['new_admin_email']);
    $password = password_hash($_POST['new_admin_pass'], PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $password]);
        $admin_msg = "✅ New admin added successfully!";
    } catch (PDOException $e) {
        $admin_msg = "❌ Failed to add admin. Username or email may already exist.";
    }
}

// Include header
include '../includes/header.php';
?>

<div class="container my-5">
    <h3 class="text-center mb-3">Admin Dashboard</h3>

    <?php if ($admin_msg): ?>
        <div class="alert alert-success text-center"><?= htmlspecialchars($admin_msg) ?></div>
    <?php endif; ?>

    <!-- Add Admin Modal Trigger Button -->
    <div class="text-center mb-3">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addAdminModal">Add Admin</button>
    </div>
</div>

<!-- ================= Add Admin Modal ================= -->
<div class="modal fade" id="addAdminModal" tabindex="-1" aria-labelledby="addAdminModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addAdminModalLabel">Add New Admin</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <div class="mb-3">
              <label class="form-label">Username</label>
              <input type="text" name="new_admin_username" class="form-control" required>
          </div>
          <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="new_admin_email" class="form-control" required>
          </div>
          <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" name="new_admin_pass" class="form-control" required>
          </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Add Admin</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </form>
  </div>
</div>

<?php
// Include footer
include '../includes/footer.php';
?>
