<?php
foreach ($courses as $course): ?>
    <?php
    $shortAvg = $shortAverages[$course->course_id]->short_avg ?? null;
    $longAvg = $longAverages[$course->course_id]->long_avg ?? null;
    $combinedAvg = null;

    if (!is_null($shortAvg) && !is_null($longAvg)) {
        $combinedAvg = ($shortAvg + $longAvg) / 2;
    } elseif (!is_null($shortAvg)) {
        $combinedAvg = $shortAvg;
    } elseif (!is_null($longAvg)) {
        $combinedAvg = $longAvg;
    } ?>

    <div class="performance-course-element">
        <table class="performance-table">
            <tr>
                <th colspan="2" class="performance-course"><?= $course->course_name ?></th>
            </tr>
            <tr>
                <th class="performance-overall">Short Quiz Average</th>
                <th class="results"><?= (!is_null($shortAvg) ? round($shortAvg, 2) . "%" : "No data") ?></th>
            </tr>

            <?php foreach ($moduleAverages as $m): ?>
                <?php if ($m->course_id === $course->course_id): ?>
                    <tr>
                        <th class="performance-module span"><?= $m->module_name ?></th>
                        <th class="results-sub"><?= round($m->average_score, 2) ?>%</th>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>

            <tr>
                <th class="performance-overall">Long Quiz Average:</th>
                <th class="results"><?= (!is_null($longAvg) ? round($longAvg) . "%" : "No Data") ?></th>
            </tr>

            <?php foreach ($longQuizzes as $lq): ?>
                <?php if ($lq->course_id === $course->course_id): ?>
                    <tr>
                        <th class="performance-module span"><?= $lq->long_quiz_name ?></th>
                        <th class="results-sub"><?= round($lq->average_score, 2) ?> %</th>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>

            <tr>
                <th class="performance-overall-both">Course Average:</th>
                <th class="results-main"><?= (!is_null($percentage) ? round($percentage) . "%" : "No data") ?></th>
            </tr>
        </table>
    </div>
<?php endforeach; ?>