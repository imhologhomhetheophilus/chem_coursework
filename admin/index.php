<?php
// ✅ Start session first before anything else
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Use correct path and include database connection
require_once __DIR__ . '/../includes/db.php';

// (Optional) Check if admin is logged in
// if (!isset($_SESSION['admin_logged_in'])) {
//     header('Location: login.php');
//     exit;
// }

// ✅ Include header AFTER backend logic (safe order)
include __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
  <h1 class="text-center text-primary mb-4">Admin Dashboard</h1>
  <p class="lead text-center">
    Welcome to the Department of Chemical Engineering Technology admin portal.
  </p>

  <div class="row justify-content-center mt-5">
    <div class="col-md-4 mb-3">
      <a href="upload_result.php" class="btn btn-primary w-100">Upload Result</a>
    </div>
    <div class="col-md-4 mb-3">
      <a href="view_results.php" class="btn btn-success w-100">View Results</a>
    </div>
    <div class="col-md-4 mb-3">
      <a href="logout.php" class="btn btn-danger w-100">Logout</a>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<!-- ✅ Scripts loaded last for better performance -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
