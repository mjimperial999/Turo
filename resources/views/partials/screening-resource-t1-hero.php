<div class="screening-results-container-concept">
    <?php if (($c['percent'] ?? 0) < 60): /* below course pass-mark */ ?>
        <div class="screening-results-container-concept-header">
            <div class="screening-results-container-concept-title">
                <p>
                    <?= htmlspecialchars($c['name']) ?>
                </p>
            </div>
            <div class="screening-results-container-concept-score failed">
                <p>
                <?= $c['percent'] ?? 0 ?>%
                </p>
            </div>
        </div>

        <?php if (!empty($c['resource_id'])): ?>
            <div class="screening-results-container-concept-link">
                <div class="activity">
                    <a class="activity-link" href="/home-tutor/course/<?= $course->course_id ?>/<?= $screeningId ?>/resources/<?= $c['resource_id'] ?>">
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

    <?php else: /* passed */ ?>
        <div class="screening-results-container-concept-header">
            <div class="screening-results-container-concept-title">
                <p>
                    <?= htmlspecialchars($c['name']) ?>
                </p>
            </div>
            <div class="screening-results-container-concept-score passed">
                <p>
                <?= $c['percent'] ?? 0 ?>%
                </p>
            </div>
        </div>
    <?php endif; ?>
</div>