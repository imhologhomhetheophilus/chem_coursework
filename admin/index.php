<?php
// Always start with the correct database connection
require(__DIR__ . '/../includes/db.php');

// Include your header
include(__DIR__ . '/../includes/header.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard | Chemical Engineering Department</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
  <h1 class="text-center text-primary mb-4">Admin Dashboard</h1>
  <p class="lead text-center">Welcome to the Department of Chemical Engineering Technology admin portal.</p>

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

<?php include(__DIR__ . '/../includes/footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
