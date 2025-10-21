<?php
session_start();
require_once '../includes/db_connect.php';

// Redirect if not logged in
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

// Include header
include '../includes/header.php';

$msg = '';

// Handle Add Admin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username && $email && $password) {
        // Hash password securely
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert new admin
        $stmt = $pdo->prepare("INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
        try {
            $stmt->execute([$username, $email, $hashedPassword]);
            $msg = "✅ Admin '{$username}' added successfully!";
        } catch (PDOException $e) {
            // Duplicate username/email handling
            if ($e->getCode() == 23000) {
                $msg = "❌ Username or email already exists!";
            } else {
                $msg = "❌ Database error: " . htmlspecialchars($e->getMessage());
            }
        }
    } else {
        $msg = "⚠️ Please fill in all fields.";
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

                <form method="post" autocomplete="off">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                        <div class="form-text">Password will be securely hashed before storage.</div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-success">Add Admin</button>
                    </div>
                </form>

                <p class="mt-3 text-center">
                    <a href="dashboard.php">Back to Dashboard</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include '../includes/footer.php';
?>
