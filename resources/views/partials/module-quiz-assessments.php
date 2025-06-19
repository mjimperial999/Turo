<?php if ($assessDisplay->isEmpty()): ?>
    <tr>
        <td colspan="4" style="text-align: center; padding: 1rem">
            <img class="svg" src="/icons/nothing.svg" width="50em" height="auto" />
            No attempts yet.
        </td>
    </tr>
<?php else: ?>
    <?php foreach ($assessDisplay as $index => $a): ?>
        <tr>
            <td><?= 'Attempt ' . ($index + 1) ?></td>
            <td><?= $a->earned_points . ' / ' . $activity->quiz->number_of_questions ?></td>
            <td><?= $a->score_percentage ?>%</td>
            <td><?= date('F j, Y h:i A', strtotime($a->date_taken)) ?></td>
        </tr>
    <?php endforeach; ?>
<?php endif; ?>