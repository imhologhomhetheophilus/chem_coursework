<?php
session_start();
require_once '../includes/db_connect.php';
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

// Include site header
include '../includes/header.php';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username && $email && $password) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admins (username,email,password) VALUES (?,?,?)");
        try {
            $stmt->execute([$username, $email, $hashed]);
            $msg = "Admin added successfully!";
        } catch (Exception $e) {
            $msg = "Error: " . $e->getMessage();
        }
    } else {
        $msg = "Fill all fields.";
    }
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm p-4">
                <h3 class="text-center mb-3">Add New Admin</h3>
                <?php if ($msg): ?>
                    <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
                <?php endif; ?>
                <form method="post">
                    <div class="mb-3">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="d-grid">
                        <button class="btn btn-success">Add Admin</button>
                    </div>
                </form>
                <p class="mt-3 text-center"><a href="dashboard.php">Back to Dashboard</a></p>
            </div>
        </div>
    </div>
</div>

<?php
// Include site footer
include '../includes/footer.php';
?>
