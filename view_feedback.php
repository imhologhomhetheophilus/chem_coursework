<?php
session_start();
require_once '../includes/db_connect.php'; // adjust path as needed

// Redirect if not logged in
if (!isset($_SESSION['group_leader_id'])) {
    header("Location: login.php");
    exit;
}

$leader_id = $_SESSION['group_leader_id'];

// Fetch submissions with admin remark and score
$stmt = $conn->prepare("SELECT id, experiment_title, file_name, remark, score, date_submitted 
                        FROM submissions 
                        WHERE group_leader_id = ?");
$stmt->bind_param("i", $leader_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<?php include 'includes/head.php'; ?> <!-- ✅ Your reusable <head> section -->

<body class="bg-light">
    <?php include 'includes/navbar.php'; ?> <!-- ✅ Optional: navigation bar -->

    <div class="container mt-5 mb-5">
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Your Submitted Experiments & Admin Feedback</h4>
            </div>
            <div class="card-body bg-white">
                <?php if ($result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Experiment Title</th>
                                    <th>File</th>
                                    <th>Remark</th>
                                    <th>Score</th>
                                    <th>Date Submitted</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $count = 1;
                                while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $count++; ?></td>
                                        <td><?= htmlspecialchars($row['experiment_title']); ?></td>
                                        <td>
                                            <a href="../uploads/<?= htmlspecialchars($row['file_name']); ?>" 
                                               class="btn btn-sm btn-outline-primary" target="_blank">View File</a>
                                        </td>
                                        <td><?= htmlspecialchars($row['remark'] ?? 'Not yet graded'); ?></td>
                                        <td><?= htmlspecialchars($row['score'] ?? 'N/A'); ?></td>
                                        <td><?= htmlspecialchars($row['date_submitted']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center">No submissions found.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?> <!-- ✅ Your reusable footer section -->
</body>
</html>
