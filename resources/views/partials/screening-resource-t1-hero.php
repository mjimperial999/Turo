<div class="screening-results-container-concept">
    <?php if (($c['percent'] ?? 0) < 60): /* below course pass-mark */ ?>
        <p class="description">
            <?= htmlspecialchars($c['name']) ?> –
            (<?= $c['percent'] ?? 0 ?>%)
            <span class="failed">(Failed)</span>
        </p>

        <?php if (!empty($c['resource_id'])): ?>
            <div class="activity">
                <a class="activity-link" href="/home-tutor/course/<?= $courseId ?>/<?= $screeningId ?>/resources/<?= $c['resource_id'] ?>">
                    <div class="activity-button screening-resources unlocked">
                        <div class="activity-logo">
                            <img class="svg" src="/icons/bulb.svg" width="30em" height="auto" />
                        </div>
                        <div class="activity-name">COURSE&nbsp;MATERIALS&nbsp;→</div>
                    </div>
                </a>
            </div>
        <?php endif; ?>

    <?php else: /* passed */ ?>
        <p class="description">
            <?= htmlspecialchars($c['name']) ?> –
            (<?= $c['percent'] ?? 0 ?>%)
            <span class="passed">(Passed)</span>
        </p>
    <?php endif; ?>
</div>
