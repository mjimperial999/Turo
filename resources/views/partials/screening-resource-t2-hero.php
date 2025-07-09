<div class="screening-results-container-concept">
    <div class="screening-results-container-concept-header">
        <div class="screening-results-container-concept-title">
            <p>
                <?= htmlspecialchars($c['name']) ?>
            </p>
            <p>
                <?= ($c['correct'] ?? 0) . ' / ' . ($c['total'] ?? 0) ?>
            </p>
        </div>
        <div class="screening-results-container-concept-score <?= ($c['percent'] ?? 0) < 50 ? 'failed' : 'passed' ?>">

            <p>
                <?= $c['percent'] ?? 0 ?>%
            </p>
        </div>
    </div>

    <?php foreach ($topicData as $t): if ($t['concept_id'] !== $cid) continue; ?>
        <?php if (($t['percent'] ?? 0) < 50): ?>
            <div class="screening-results-container-topic">
                <div class="screening-results-container-topic-material">
                    <div class="screening-results-container-concept-header">
                        <div class="screening-results-container-concept-title">
                            <p>
                                <?= htmlspecialchars($t['name']) ?>
                            </p>
                        </div>
                        <div class="screening-results-container-concept-score <?= ($t['percent'] ?? 0) < 50 ? 'failed' : 'passed' ?>">
                            <p class="description">
                                <?= $t['percent'] ?? 0 ?>%
                            </p>
                        </div>
                    </div>
                    <?php if (!empty($t['resource_id'])): ?>
                        <div class="screening-results-container-concept-link">
                            <div class="activity">
                                <a class="activity-link" href="/home-tutor/course/<?= $course->course_id ?>/<?= $screeningId ?>/resources/<?= $t['resource_id'] ?>">
                                    <div class="activity-button screening-resources unlocked">
                                        <div class="activity-logo">
                                            <img class="svg" src="/icons/bulb.svg" width="30em" height="auto" />
                                        </div>
                                        <div class="activity-name">COURSE&nbsp;MATERIALS&nbsp;→</div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php else: ?>
            <div class="screening-results-container-topic">
                <div class="screening-results-container-topic-material">
                    <div class="screening-results-container-concept-header">
                        <div class="screening-results-container-concept-title">
                            <p>
                                <?= htmlspecialchars($t['name']) ?>
                            </p>
                        </div>
                        <div class="screening-results-container-concept-score <?= ($t['percent'] ?? 0) < 50 ? : 'passed' ?>">
                            <p class="description">
                                <?= $t['percent'] ?? 0 ?>%
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    <?php endforeach; ?>

</div>