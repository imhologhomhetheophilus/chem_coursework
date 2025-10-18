<?php
session_start();
require 'includes/db.php';
require 'includes/auth.php';
require_leader();

$group_id = $_SESSION['group_id'];
$message = $_GET['m'] ?? '';

// Fetch supervisors and personnel
$supervisors = $pdo->query("SELECT * FROM supervisors ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$personnel = $pdo->query("SELECT * FROM personnel ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Fetch students
$students = $pdo->prepare("SELECT id, name, regno FROM students WHERE group_id = ?");
$students->execute([$group_id]);
$students = $students->fetchAll(PDO::FETCH_ASSOC);

// Fetch previous submissions
$subs = $pdo->prepare("SELECT * FROM submissions WHERE group_id = ? ORDER BY created_at DESC");
$subs->execute([$group_id]);
$subs = $subs->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Group <?=htmlspecialchars($group_id)?> — Coursework Submission</h3>
        <a class="btn btn-outline-secondary" href="logout.php">Logout</a>
    </div>

    <?php if($message): ?>
        <div class="alert alert-success"><?=$message?></div>
    <?php endif; ?>

    <!-- New Submission Form -->
    <form action="handle_submit.php" method="post" enctype="multipart/form-data" class="card p-3 mb-4">
        <input type="hidden" name="group_id" value="<?=htmlspecialchars($group_id)?>">

        <div class="row mb-2">
            <div class="col-md-6">
                <label class="form-label">Supervisor</label>
                <select name="students[0][supervisor_id]" class="form-select" required>
                    <option value="">-- Select Supervisor --</option>
                    <?php foreach($supervisors as $s): ?>
                        <option value="<?=$s['id']?>"><?=htmlspecialchars($s['name'])?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Lab Personnel</label>
                <select name="students[0][personnel_id]" class="form-select" required>
                    <option value="">-- Select Personnel --</option>
                    <?php foreach($personnel as $p): ?>
                        <option value="<?=$p['id']?>"><?=htmlspecialchars($p['name'].' ('.$p['code'].')')?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Upload Coursework (PDF/DOCX)</label>
            <input class="form-control" type="file" name="file" accept=".pdf,.docx" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Submission Date & Time</label>
            <input type="datetime-local" name="created_at" class="form-control" value="<?=date('Y-m-d\TH:i')?>" required>
        </div>

        <h5>Group Members</h5>
        <table class="table table-striped">
            <thead><tr><th>SN</th><th>Reg No</th><th>Name</th><th>Remark</th><th>Supervisor</th><th>Personnel</th><th>Date & Time</th></tr></thead>
            <tbody>
            <?php foreach($students as $i=>$m): ?>
                <tr>
                    <td><?=($i+1)?></td>
                    <td><?=htmlspecialchars($m['regno'])?></td>
                    <td><?=htmlspecialchars($m['name'])?></td>
                    <td>
                        <input type="hidden" name="students[<?=$m['id']?>][id]" value="<?=$m['id']?>">
                        <select name="students[<?=$m['id']?>][remark]" class="form-select form-select-sm">
                            <option value="Not Cleared">Not Cleared</option>
                            <option value="Cleared">Cleared</option>
                        </select>
                    </td>
                    <td>
                        <select name="students[<?=$m['id']?>][supervisor_id]" class="form-select form-select-sm">
                            <option value="">-- Supervisor --</option>
                            <?php foreach($supervisors as $s): ?>
                                <option value="<?=$s['id']?>"><?=htmlspecialchars($s['name'])?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <select name="students[<?=$m['id']?>][personnel_id]" class="form-select form-select-sm">
                            <option value="">-- Personnel --</option>
                            <?php foreach($personnel as $p): ?>
                                <option value="<?=$p['id']?>"><?=htmlspecialchars($p['name'].' ('.$p['code'].')')?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <input type="datetime-local" name="students[<?=$m['id']?>][created_at]" class="form-control form-control-sm" value="<?=date('Y-m-d\TH:i')?>">
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <div class="text-center"><button class="btn btn-success">Submit Coursework</button></div>
    </form>

    <!-- Previous Submissions -->
    <h5>Previous Submissions</h5>
    <?php if($subs): ?>
        <?php foreach($subs as $s): ?>
            <?php
                $st_query = $pdo->prepare("SELECT ss.*, st.name, st.regno FROM submission_students ss JOIN students st ON ss.student_id = st.id WHERE ss.submission_id = ?");
                $st_query->execute([$s['id']]);
                $sub_students = $st_query->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <form action="handle_submit.php" method="post" class="card p-3 mb-3">
                <input type="hidden" name="edit_submission_id" value="<?=$s['id']?>">

                <div class="mb-2"><strong>File:</strong> <a href="uploads/<?=htmlspecialchars($s['file_name'])?>" target="_blank"><?=htmlspecialchars($s['file_name'])?></a></div>

                <table class="table table-sm table-bordered">
                    <thead><tr><th>SN</th><th>Name</th><th>Reg No</th><th>Remark</th><th>Supervisor</th><th>Personnel</th><th>Date & Time</th></tr></thead>
                    <tbody>
                        <?php foreach($sub_students as $i=>$st): ?>
                            <tr>
                                <td><?=($i+1)?></td>
                                <td><?=htmlspecialchars($st['name'])?></td>
                                <td><?=htmlspecialchars($st['regno'])?></td>
                                <td>
                                    <select name="students[<?=$st['student_id']?>][remark]" class="form-select form-select-sm">
                                        <option value="Not Cleared" <?=($st['remark']=='Not Cleared'?'selected':'')?>>Not Cleared</option>
                                        <option value="Cleared" <?=($st['remark']=='Cleared'?'selected':'')?>>Cleared</option>
                                    </select>
                                </td>
                                <td>
                                    <select name="students[<?=$st['student_id']?>][supervisor_id]" class="form-select form-select-sm">
                                        <option value="">-- Supervisor --</option>
                                        <?php foreach($supervisors as $sup): ?>
                                            <option value="<?=$sup['id']?>" <?=($sup['id']==$st['supervisor_id']?'selected':'')?>><?=htmlspecialchars($sup['name'])?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <select name="students[<?=$st['student_id']?>][personnel_id]" class="form-select form-select-sm">
                                        <option value="">-- Personnel --</option>
                                        <?php foreach($personnel as $p): ?>
                                            <option value="<?=$p['id']?>" <?=($p['id']==$st['personnel_id']?'selected':'')?>><?=htmlspecialchars($p['name'].' ('.$p['code'].')')?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="datetime-local" name="students[<?=$st['student_id']?>][created_at]" class="form-control form-control-sm" value="<?=date('Y-m-d\TH:i', strtotime($st['created_at']))?>">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="text-center"><button class="btn btn-primary btn-sm">Update Submission</button></div>
            </form>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-muted">No previous submissions found.</p>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
