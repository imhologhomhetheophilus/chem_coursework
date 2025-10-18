<?php
// Start session and include DB connection
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/db_connect.php';

// Make sure group leader is logged in
if (!isset($_SESSION['group_id'])) {
    header('Location: group_login.php'); // or your actual login file
    exit;
}

$group = $_SESSION['group_id'] ?? '';

try {
    // ✅ FIXED: added alias 's' after 'FROM submissions'
    $stmt = $pdo->prepare("
        SELECT s.*, sp.name AS supervisor, p.name AS personnel
        FROM submissions s
        LEFT JOIN supervisors sp ON s.supervisor_id = sp.id
        LEFT JOIN personnel p ON s.personnel_id = p.id
        WHERE s.group_id = ?
        ORDER BY s.created_at DESC
    ");
    $stmt->execute([$group]);
    $subs = $stmt->fetchAll();
} catch (PDOException $e) {
    die('Database error: ' . $e->getMessage());
}

include 'includes/header.php';
?>

<h3 class="text-center mt-4">My Submissions</h3>

<div class="container mt-3">
  <table class="table table-striped table-bordered">
    <thead class="table-dark">
      <tr>
        <th>#</th>
        <th>Supervisor</th>
        <th>Personnel</th>
        <th>File</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($subs)): ?>
        <tr>
          <td colspan="5" class="text-center text-muted">No submissions found.</td>
        </tr>
      <?php else: ?>
        <?php foreach($subs as $i => $s): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td><?= htmlspecialchars($s['supervisor'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($s['personnel'] ?? 'N/A') ?></td>
            <td>
              <?php if (!empty($s['file_name'])): ?>
                <a href="uploads/<?= htmlspecialchars($s['file_name']) ?>" target="_blank">View File</a>
              <?php else: ?>
                No file
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($s['created_at']) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php include 'includes/footer.php'; ?>
