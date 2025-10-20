<?php
session_start();
require 'includes/db_connect.php';

$message = '';

// Redirect to submission page if already logged in
if (isset($_SESSION['group_id'])) {
    header('Location: submission.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $group = strtoupper(trim($_POST['group_id'] ?? ''));
    $password = $_POST['password'] ?? '';

    try {
        // Fetch the group
        $stmt = $pdo->prepare('SELECT * FROM groups WHERE group_id = ?');
        $stmt->execute([$group]);
        $g = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($g) {
            $stored = $g['password'] ?? '';
            $ok = false;

            // Check hashed password
            if (!empty($stored) && password_verify($password, $stored)) {
                $ok = true;
            } elseif ($password === $stored) {
                // Plain-text password: rehash and update
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $update = $pdo->prepare('UPDATE groups SET password = ? WHERE id = ?');
                $update->execute([$hash, $g['id']]);
                $ok = true;
            }

            if ($ok) {
                $_SESSION['group_id'] = $group;
                header('Location: submission.php');
                exit;
            }
        }

        $message = 'Invalid Group ID or password.';
    } catch (PDOException $e) {
        $message = "Database error: " . $e->getMessage();
    }
}

include 'includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-12 col-sm-8 col-md-6 col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="card-title text-center mb-3">Group Leader Login</h4>

                    <?php if ($message): ?>
                        <div class="alert alert-danger text-center"><?= htmlspecialchars($message) ?></div>
                    <?php endif; ?>

                    <form method="post">
                        <div class="mb-3">
                            <label for="group_id" class="form-label">Group ID</label>
                            <input id="group_id" name="group_id" class="form-control" placeholder="e.g. GP1" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input id="password" name="password" type="password" class="form-control" required>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary w-50 me-2">Login</button>
                            <a class="btn btn-outline-secondary w-50" href="index.php">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
