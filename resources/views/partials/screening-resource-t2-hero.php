<div class="screening-results-container-concept">
    <p class="description">
        <b><?= htmlspecialchars($c['name']) ?></b> –
        <?= ($c['correct'] ?? 0) . ' / ' . ($c['total'] ?? 0) ?>
        (<?= $c['percent'] ?? 0 ?>%)
        <span class="<?= ($c['percent'] ?? 0) < 50 ? 'failed' : 'passed' ?>">
            (<?= ($c['percent'] ?? 0) < 50 ? 'Failed' : 'Passed' ?>)
        </span>
    </p>

    <?php foreach ($topicData as $t): if ($t['concept_id'] !== $cid) continue; ?>
        <div class="screening-results-container-topic">
            <?php if (($t['percent'] ?? 0) < 50): ?>
                <div class="screening-results-container-topic-material">
                    <p class="description">
                        <?= htmlspecialchars($t['name']) ?> –
                        (<?= $t['percent'] ?? 0 ?>%)
                        <span class="failed">(Failed)</span>
                    </p>

                    <?php if (!empty($t['resource_id'])): ?>
                        <div class="activity">
                            <a class="activity-link" href="/home-tutor/course/<?= $courseId ?>/<?= $screeningId ?>/resources/<?= $t['resource_id'] ?>">
                                <div class="activity-button screening-resources unlocked">
                                    <div class="activity-logo">
                                        <img class="svg" src="/icons/bulb.svg" width="30em" height="auto" />
                                    </div>
                                    <div class="activity-name">COURSE&nbsp;MATERIALS&nbsp;→</div>
                                </div>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p class="description">
                    <?= htmlspecialchars($t['name']) ?> –
                    (<?= $t['percent'] ?? 0 ?>%)
                    <span class="passed">(Passed)</span>
                </p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
