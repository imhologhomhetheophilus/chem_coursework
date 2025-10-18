<?php
session_start();
include('../includes/db_connect.php');

// Redirect if not logged in
if (!isset($_SESSION['admin'])) {
    header('Location: index.php');
    exit;
}

include('../includes/header.php');
?>

<div class="container mt-4">
  <h3 class="text-center mb-4 text-primary">Admin Dashboard</h3>

  <div class="alert alert-success text-center">
    Welcome, <strong><?= htmlspecialchars($_SESSION['admin']); ?></strong> 🎉
  </div>

  <div class="row text-center">
    <div class="col-md-4 mb-3">
      <a href="manage_students.php" class="btn btn-outline-info w-100">Manage Students</a>
    </div>
    <div class="col-md-4 mb-3">
      <a href="manage_groups.php" class="btn btn-outline-primary w-100">Manage Groups</a>
    </div>
    <div class="col-md-4 mb-3">
      <a href="manage_personnel.php" class="btn btn-outline-secondary w-100">Manage Personnel</a>
    </div>
    <div class="col-md-4 mb-3">
      <a href="manage_supervisors.php" class="btn btn-outline-success w-100">Manage Supervisors</a>
    </div>
    <div class="col-md-4 mb-3">
      <a href="view_submissions.php" class="btn btn-outline-warning w-100">View Course Work Uploads</a>
    </div>
    <div class="col-md-4 mb-3">
      <a href="logout.php" class="btn btn-outline-danger w-100">Logout</a>
    </div>
  </div>
</div>

<?php include('../includes/footer.php'); ?>
