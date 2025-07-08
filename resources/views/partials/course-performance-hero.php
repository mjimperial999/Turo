<div class="performance-course">
    <?php foreach ($courses as $c): ?>
        <div class="performance-course-details">
            <div class="performance-course-name">
                <b><?= htmlspecialchars($c->course?->course_name) ?></b>
            </div>

            <span class="score <?= round($c->average_score, 2) < 70 ? 'failed' : 'passed' ?>">
                <b><?= is_null($c->average_score) ? '—' : round($c->average_score, 2) ?>%</b>
            </span>
        </div>

        <?php if (!empty($modules[$c->course?->course_id])): ?>
            <div class="performance-module">
                <?php foreach ($modules[$c->course?->course_id] as $m): ?>
                    <div class="performance-module-title"><b><?= htmlspecialchars($m->module?->module_name) ?></b></div>

                    <!-- ───── PRACTICE QUIZZES ───── -->
                    <?php
                    $pRow = $practice[$m->module?->module_id]   ?? null;       // row (avg + quizzes)
                    $pHas = $pRow;
                    ?>
                    <?php if ($pHas): ?>
                        <div class="subcat-row">
                            Practices
                            <span class="score"><?= round($pRow->avg('avg'), 2) ?>%</span>
                        </div>
                        <?php foreach ($pRow as $pq): ?>
                            <div class="quiz-row">
                                - <?= htmlspecialchars($pq->quiz_name) ?>
                                <span class="score"><?= round($pq->avg, 2) ?>%</span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; /* practice */ ?>

                    <!-- ───── SHORT QUIZZES ───── -->
                    <?php
                    $sRow = $short[$m->module?->module_id]      ?? null;
                    $sHas = $sRow;
                    ?>
                    <?php if ($sHas): ?>
                        <div class="subcat-row">
                            Short Quizzes
                            <span class="score"><?= round($sRow->avg('avg'), 2) ?>%</span>
                        </div>
                        <?php foreach ($sRow as $sq): ?>
                            <div class="quiz-row">
                                - <?= htmlspecialchars($sq->quiz_name) ?>
                                <span class="score"><?= round($sq->avg, 2) ?>%</span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; /* short */ ?>

                <?php endforeach; /* modules */ ?>
            </div>
        <?php endif; /* module list */ ?>

        <!-- ───── LONG QUIZZES (course scope) ───── -->
        <?php
        $lRow = $long[$c->course?->course_id]         ?? null;
        $lHas = $lRow;
        ?>
        <?php if ($lHas): ?>
            <div class="subcourse-row">
                <b>Long Quizzes</b>
                <span class="score"><b><?= round($lRow->avg('avg'), 2) ?>%</b></span>
            </div>
            <?php foreach ($lRow as $lq): ?>
                <div class="longquiz-row">
                    <?= htmlspecialchars($lq->quiz_name) ?>
                    <span class="score"><?= round($lq->avg, 2) ?>%</span>
                </div>
            <?php endforeach; ?>
        <?php endif; /* long */ ?>

        <!-- ───── SCREENING EXAMS ───── -->
        <?php if (!empty($screening[$c->course_id])): ?>
            <?php foreach ($screening[$c->course_id] as $se): ?>
                <div class="subcourse-row">
                    <b><?= htmlspecialchars($se->screening_name) ?></b>
                    <span class="score"><b><?= round($se->best_score, 2) ?>%</b></span>
                </div>
            <?php endforeach; ?>
        <?php endif; /* screening */ ?>

    <?php endforeach; ?>
</div>