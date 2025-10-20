<!-- ===== SUBMISSIONS TABLE ===== -->
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0 text-center">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Group ID</th>
                        <?php
                        // Get max number of students per group to dynamically create columns
                        $max_students = 0;
                        foreach ($subs as $s) {
                            $st_query = $pdo->prepare("SELECT id FROM students WHERE group_id = ?");
                            $st_query->execute([$s['group_id']]);
                            $count = $st_query->rowCount();
                            if ($count > $max_students) $max_students = $count;
                        }
                        for ($j = 1; $j <= $max_students; $j++) {
                            echo "<th>Student $j Name</th><th>Reg No</th><th>Remark</th>";
                        }
                        ?>
                        <th>Supervisor</th>
                        <th>Personnel</th>
                        <th>File</th>
                        <th>Admin Remark</th>
                        <th>Score</th>
                        <th>Uploaded</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($subs)): ?>
                        <?php foreach ($subs as $i => $s): ?>
                            <?php
                            $st_query = $pdo->prepare("
                                SELECT st.name, st.regno, ss.remark 
                                FROM students st
                                LEFT JOIN submission_students ss ON st.id = ss.student_id AND ss.submission_id = ?
                                WHERE st.group_id = ?
                                ORDER BY st.id
                            ");
                            $st_query->execute([$s['id'], $s['group_id']]);
                            $students = $st_query->fetchAll(PDO::FETCH_ASSOC);
                            ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td class="fw-bold text-primary"><?= htmlspecialchars($s['group_id'] ?? 'N/A') ?></td>

                                <?php
                                // Print each student's Name, Reg No, and Remark
                                foreach ($students as $st) {
                                    echo "<td>" . htmlspecialchars($st['name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($st['regno']) . "</td>";
                                    echo "<td>" . htmlspecialchars($st['remark'] ?? '—') . "</td>";
                                }
                                // Fill empty columns if some groups have fewer students
                                $empty_cols = $max_students - count($students);
                                for ($e = 0; $e < $empty_cols; $e++) {
                                    echo "<td>—</td><td>—</td><td>—</td>";
                                }
                                ?>

                                <td><?= htmlspecialchars($s['supervisor'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($s['personnel'] ?? '—') ?></td>
                                <td>
                                    <?php if (!empty($s['file_name'])): ?>
                                        <a href="../uploads/<?= htmlspecialchars($s['file_name']) ?>" target="_blank">View</a>
                                    <?php else: ?>
                                        <span class="text-muted">No file</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($s['admin_remark'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($s['score'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($s['created_at'] ?? '—') ?></td>
                                <td>
                                    <form method="post" class="d-flex flex-column gap-2">
                                        <input type="hidden" name="submission_id" value="<?= htmlspecialchars($s['id']) ?>">
                                        <select name="remark" class="form-select form-select-sm">
                                            <option value="">-- Remark --</option>
                                            <option value="Clear" <?= ($s['admin_remark'] ?? '') === 'Clear' ? 'selected' : '' ?>>Clear</option>
                                            <option value="Not Clear" <?= ($s['admin_remark'] ?? '') === 'Not Clear' ? 'selected' : '' ?>>Not Clear</option>
                                        </select>
                                        <input type="number" name="score" class="form-control form-control-sm" placeholder="Score" value="<?= htmlspecialchars($s['score'] ?? '') ?>">
                                        <button class="btn btn-sm btn-primary mt-1">Update</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="<?= 5 + ($max_students * 3) ?>" class="text-center text-muted">No submissions found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
