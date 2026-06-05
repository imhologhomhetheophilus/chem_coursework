<?php
session_start();
require_once 'includes/db_connect.php';

if (!isset($_SESSION['group_id'])) {
    header('Location: group_login.php');
    exit;
}

$group = $_SESSION['group_id'];

$stmt = $pdo->prepare("
    SELECT * FROM submissions
    WHERE group_id = ?
    ORDER BY created_at DESC
");

$stmt->execute([$group]);
$subs = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<div class="container mt-4">

<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success">
        Submission successful!
    </div>
<?php endif; ?>

<h3>Your Submissions</h3>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>#</th>
            <th>File</th>
            <th>Admin Remark</th>
            <th>Admin Score</th>
            <th>Date</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach($subs as $i => $s): ?>
        <tr>
            <td><?= $i + 1 ?></td>

            <td>
                <a href="<?= $s['file_path'] ?>" target="_blank">
                    View File
                </a>
            </td>

            <td>
                <?= $s['admin_remark'] ?? 'Pending' ?>
            </td>

            <td>
                <?= $s['admin_score'] ?? 'Pending' ?>
            </td>

            <td>
                <?= $s['created_at'] ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</div>

<?php include 'includes/footer.php'; ?>