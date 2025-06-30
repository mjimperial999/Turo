<?php if ($bestResults->isEmpty()): ?>
    <div class="no-items">
        <img class="svg" src="/icons/nothing.svg" width="50em" height="auto" />
        No student has taken this long quiz.
    </div>
<?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>Student</th>
                <th>Score</th>
                <th>Percentage</th>
                <th>Attempt #</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bestResults as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r->student->user->first_name . ' ' .
                            $r->student->user->last_name) ?></td>

                    <td><?= $r->earned_points ?> / <?= $longquiz->overall_points ?></td>

                    <td><?= $r->score_percentage ?>%</td>

                    <td><?= $r->attempt_number ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>