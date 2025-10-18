<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../includes/db_connect.php';

// Ensure admin logged in
if (!isset($_SESSION['admin'])) {
    header('Location: index.php');
    exit;
}

// Fetch all submissions with group, supervisor, and personnel info
$stmt = $pdo->query("
    SELECT s.id, s.group_id, s.file_name, s.created_at,
           sp.name AS supervisor, p.name AS personnel
    FROM submissions s
    LEFT JOIN supervisors sp ON s.supervisor_id = sp.id
    LEFT JOIN personnel p ON s.personnel_id = p.id
    ORDER BY s.created_at DESC
");
$submissions = $stmt->fetchAll();

include('../includes/header.php');
?>

<div class="container mt-4">
  <h3 class="text-center text-primary mb-4">📚 Coursework Submissions</h3>

  <?php if (isset($_GET['m'])): ?>
    <div class="alert alert-success text-center"><?= htmlspecialchars($_GET['m']) ?></div>
  <?php endif; ?>

  <?php if (count($submissions) === 0): ?>
    <div class="alert alert-info text-center">No submissions found yet.</div>
  <?php else: ?>
    <div class="accordion" id="submissionList">
      <?php foreach ($submissions as $i => $s): ?>
        <div class="accordion-item mb-2 shadow-sm">
          <h2 class="accordion-header" id="heading<?= $s['id'] ?>">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapse<?= $s['id'] ?>" aria-expanded="false">
              <strong>Group:</strong> <?= htmlspecialchars($s['group_id']) ?>
              &nbsp;|&nbsp; <strong>Supervisor:</strong> <?= htmlspecialchars($s['supervisor'] ?? 'N/A') ?>
              &nbsp;|&nbsp; <strong>Personnel:</strong> <?= htmlspecialchars($s['personnel'] ?? 'N/A') ?>
              &nbsp;|&nbsp; <strong>Date:</strong> <?= htmlspecialchars($s['created_at']) ?>
            </button>
          </h2>
          <div id="collapse<?= $s['id'] ?>" class="accordion-collapse collapse"
               aria-labelledby="heading<?= $s['id'] ?>" data-bs-parent="#submissionList">
            <div class="accordion-body">

              <p>
                <strong>File:</strong>
                <?php if ($s['file_name']): ?>
                  <a href="../uploads/<?= htmlspecialchars($s['file_name']) ?>" target="_blank">View File</a>
                <?php else: ?>
                  <span class="text-muted">No file</span>
                <?php endif; ?>
              </p>

              <?php
              // Fetch group members + remarks for this submission
              $r = $pdo->prepare("
                  SELECT st.reg_no, st.name, sr.remark
                  FROM submission_remarks sr
                  JOIN students st ON sr.student_id = st.id
                  WHERE sr.submission_id = ?
                  ORDER BY st.name
              ");
              $r->execute([$s['id']]);
              $members = $r->fetchAll();
              ?>

              <h6 class="text-secondary mt-3 mb-2">Group Members & Remarks</h6>
              <table class="table table-sm table-striped">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Reg No</th>
                    <th>Name</th>
                    <th>Remark</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (count($members) > 0): ?>
                    <?php foreach ($members as $j => $m): ?>
                      <tr>
                        <td><?= $j + 1 ?></td>
                        <td><?= htmlspecialchars($m['reg_no']) ?></td>
                        <td><?= htmlspecialchars($m['name']) ?></td>
                        <td><?= htmlspecialchars($m['remark']) ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr><td colspan="4" class="text-center text-muted">No remarks found.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>

              <!-- Score update form -->
              <form method="post" action="update_score.php" class="d-flex justify-content-between align-items-center">
                <input type="hidden" name="submission_id" value="<?= $s['id'] ?>">
                <div class="d-flex align-items-center gap-2">
                  <label for="score_<?= $s['id'] ?>" class="form-label mb-0">Score:</label>
                  <input type="number" name="score" id="score_<?= $s['id'] ?>"
                         class="form-control form-control-sm" style="width:100px;" min="0" max="100" required>
                </div>
                <div>
                  <button type="submit" class="btn btn-sm btn-success">✅ Save Score</button>
                  <a href="send_back.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-warning">↩ Send Back</a>
                </div>
              </form>

            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php include('../includes/footer.php'); ?>
