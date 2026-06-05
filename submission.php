<?php
session_start();
require_once 'includes/db_connect.php';

if (!isset($_SESSION['group_id'])) {
    header('Location: group_login.php');
    exit;
}

$group = $_SESSION['group_id'];

/* Fetch group members */
$stmt = $pdo->prepare("SELECT * FROM students WHERE group_id = ?");
$stmt->execute([$group]);
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Fetch supervisors */
$stmt = $pdo->query("SELECT * FROM supervisors ORDER BY name");
$supervisors = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Fetch personnel */
$stmt = $pdo->query("SELECT * FROM personnel ORDER BY name");
$personnel = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0">Coursework Submission</h3>
            <small class="text-muted">
                Logged in as Group <?= htmlspecialchars($group) ?>
            </small>
        </div>

        <a href="logout.php" class="btn btn-danger">
            Logout
        </a>
    </div>

    <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success">
            Coursework submitted successfully.
        </div>
    <?php endif; ?>

    <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            Submit Coursework
        </div>

        <div class="card-body">

            <form action="upload_submission.php"
                  method="POST"
                  enctype="multipart/form-data">

                <input type="hidden"
                       name="group_id"
                       value="<?= htmlspecialchars($group) ?>">

                <div class="row mb-3">

                    <div class="col-md-6">
                        <label class="form-label">
                            Supervisor
                        </label>

                        <select name="supervisor_id"
                                class="form-select"
                                required>

                            <option value="">
                                Select Supervisor
                            </option>

                            <?php foreach($supervisors as $sup): ?>
                                <option value="<?= $sup['id'] ?>">
                                    <?= htmlspecialchars($sup['name']) ?>
                                </option>
                            <?php endforeach; ?>

                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            Lab Personnel
                        </label>

                        <select name="personnel_id"
                                class="form-select"
                                required>

                            <option value="">
                                Select Personnel
                            </option>

                            <?php foreach($personnel as $person): ?>
                                <option value="<?= $person['id'] ?>">
                                    <?= htmlspecialchars($person['name']) ?>
                                </option>
                            <?php endforeach; ?>

                        </select>
                    </div>

                </div>

                <div class="mb-3">
                    <label class="form-label">
                        Experiment Date & Time
                    </label>

                    <input type="datetime-local"
                           name="experiment_datetime"
                           class="form-control"
                           required>
                </div>

                <div class="mb-4">
                    <label class="form-label">
                        Upload Coursework (PDF or DOCX)
                    </label>

                    <input type="file"
                           name="coursework_file"
                           class="form-control"
                           accept=".pdf,.doc,.docx"
                           required>
                </div>

                <h5 class="mb-3">
                    Group Members Remarks
                </h5>

                <div class="table-responsive">

                    <table class="table table-bordered table-striped">

                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Reg Number</th>
                            <th>Name</th>
                            <th>Remark</th>
                        </tr>
                        </thead>

                        <tbody>

                        <?php if($members): ?>

                            <?php foreach($members as $index => $member): ?>

                                <tr>

                                    <td><?= $index + 1 ?></td>

                                    <td>
                                        <?= htmlspecialchars($member['reg_no']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($member['name']) ?>
                                    </td>

                                    <td>

                                        <input type="hidden"
                                               name="student_ids[]"
                                               value="<?= $member['id'] ?>">

                                        <select
                                            name="remark_<?= $member['id'] ?>"
                                            class="form-select">

                                            <option value="Not Cleared">
                                                Not Cleared
                                            </option>

                                            <option value="Cleared">
                                                Cleared
                                            </option>

                                        </select>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="4"
                                    class="text-center text-danger">
                                    No students found for this group.
                                </td>
                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

                <div class="text-center mt-4">

                    <button type="submit"
                            class="btn btn-success btn-lg">
                        Submit Coursework
                    </button>

                </div>

            </form>

        </div>
    </div>

</div>

<?php include 'includes/footer.php'; ?>