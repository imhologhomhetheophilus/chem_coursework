<?php
require '../includes/db_connect.php';
require '../includes/auth.php';
if (session_status() === PHP_SESSION_NONE) session_start();
require_leader();

$group = $_SESSION['group_id'];

// Fetch supervisors and personnel
$sup = $pdo->query('SELECT * FROM supervisors')->fetchAll();
$pers = $pdo->query('SELECT * FROM personnel')->fetchAll();

$msg = $_GET['m'] ?? '';
include '../includes/header.php';
?>
<div class="container py-5">
    <h3>Group <?=htmlspecialchars($group)?> — Submission</h3>
    <?php if($msg): ?><div class="alert alert-success"><?=$msg?></div><?php endif; ?>

    <form method="POST" action="handle_submit.php" enctype="multipart/form-data" class="card p-3">
        <div class="row mb-3">
            <div class="col-md-6">
                <label>Supervisor</label>
                <select name="supervisor_id" class="form-select" required>
                    <option value="">-- Select Supervisor --</option>
                    <?php foreach($sup as $s): ?>
                        <option value="<?=$s['id']?>"><?=htmlspecialchars($s['name'])?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label>Lab Personnel</label>
                <select name="personnel_id" class="form-select" required>
                    <option value="">-- Select Personnel --</option>
                    <?php foreach($pers as $p): ?>
                        <option value="<?=$p['id']?>"><?=htmlspecialchars($p['name'])?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label>Upload Coursework</label>
            <input type="file" name="file" accept=".pdf,.docx" class="form-control" required>
        </div>

        <button class="btn btn-primary">Submit Coursework</button>
    </form>
</div>
<?php include '../includes/footer.php'; ?>
