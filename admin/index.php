<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';

// Redirect if not logged in as admin
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$msg = '';

// ================= ADD NEW ADMIN =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_admin'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($username && $password) {
        // Hash password
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Insert into DB
        $stmt = $pdo->prepare("INSERT INTO admins (username, password, email) VALUES (?, ?, ?)");
        try {
            $stmt->execute([$username, $hash, $email]);
            $msg = "✅ Admin '$username' added successfully!";
        } catch (PDOException $e) {
            $msg = "❌ Error: " . $e->getMessage();
        }
    } else {
        $msg = "❌ Username and password are required.";
    }
}

// Fetch all admins
$admins = $pdo->query("SELECT id, username, email, created_at FROM admins ORDER BY created_at DESC")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="container my-5">
    <h3 class="mb-4">Admin Management</h3>

    <?php if ($msg): ?>
        <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <!-- Add New Admin Form -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h5 class="mb-3">Add New Admin</h5>
            <form method="post">
                <input type="hidden" name="add_admin" value="1">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email (optional)</label>
                    <input type="email" name="email" class="form-control">
                </div>
                <button class="btn btn-success">Add Admin</button>
            </form>
        </div>
    </div>

    <!-- Existing Admins Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="mb-3">Existing Admins</h5>
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($admins as $i => $a): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= htmlspecialchars($a['username']) ?></td>
                            <td><?= htmlspecialchars($a['email'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($a['created_at']) ?></td>
                            <td>
                                <a href="reset_admin.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-warning">Reset Password</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($admins)): ?>
                        <tr><td colspan="5" class="text-center text-muted">No admins found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
