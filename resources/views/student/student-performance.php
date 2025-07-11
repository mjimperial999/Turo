<?php
$title = 'Performance';
include __DIR__ . '/../partials/head.php';
?>
<style>
    .performance-container {
        width: 100%;
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        gap: 2rem;

        font-family: Albert-Sans, sans-serif;
    }

    .performance-details {
        width: 75%;
        display: flex;
        flex-direction: column;

        font-size: 1rem;
    }

    .performance-course {
        width: 100%;
        padding: 0.25rem;
        border-radius: 0.25rem;
        display: flex;
        flex-direction: column;
        background-color: rgb(228, 222, 212);
    }

    .performance-course-details {
        width: 100%;
        padding: 0.5rem;
        border-radius: 0.25rem;
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        color: white;

        font-size: 1.2rem;
        background-color: rgb(79, 76, 71);

    }

    .performance-course-details span.score {
        padding: 0 0.25rem;
        border-radius: 0.25rem;

        font-size: 1.2rem;
        background-color: #ffffff;
        color: white;
    }

    .performance-module {
        width: 100%;
        display: flex;
        flex-direction: column;

        font-size: 1rem;
        background-color: rgb(199, 193, 183);
    }

    .performance-module:nth-child(even) {
        background-color: rgb(198, 190, 181);
    }

    .performance-module-title {
        width: 100%;
        padding: 0.25rem 0.25rem 0.25rem 0.5rem;
    }

    .subcat-row {
        color: black;
        width: 100%;
        padding: 0 0.5rem 0 2.4rem;
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        background-color: rgba(255, 255, 255, 0.28);
    }

    .quiz-row {
        color: black;
        width: 100%;
        padding: 0 0.5rem 0 3.6rem;
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        font-size: 0.9rem;
        font-family: Albert-Sans-IT, 'sans-serif';
        color: rgb(52, 52, 52);
        background-color: rgba(173, 173, 173, 0.22);
    }

    .quiz-row:nth-child(even) {
        background-color: rgba(147, 147, 147, 0.22);
    }

    .subcourse-row {
        color: black;
        width: 100%;
        padding: 0.25rem 0.25rem 0.25rem 0.5rem;
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        background-color: rgba(255, 255, 255, 0.28);
    }

    .longquiz-row {
        color: black;
        width: 100%;
        padding: 0 0.5rem 0 1.2rem;
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        color: rgb(52, 52, 52);
        background-color: rgba(173, 173, 173, 0.22);
    }

    .score.failed {
        background: rgb(255, 88, 88);
        background: linear-gradient(135deg, rgb(255, 134, 134) 0%, rgb(255, 88, 88) 100%);
    }

    .score.passed {
        background: rgb(174, 255, 88);
        background: linear-gradient(135deg, rgb(179, 238, 117) 0%, rgb(133, 220, 41) 100%);
    }

    .performance-graphics {
        width: 25%;
        display: flex;
        flex-direction: column;
    }

    .performance-points {
        display: flex;
        flex-direction: column;
        align-items: center;

        color: #492C2C;
        text-align: center;
        line-height: 1.2;
    }

    .performance-points img {
        margin-bottom: 1rem;
    }

    p.points-total-header {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 600;
    }

    p.points-total-points {
        margin: 0;
        font-size: 2.5rem;
        font-weight: 800;
    }
</style>
</head>

<body>
    <?php

    include __DIR__ . '/../partials/nav.php';
    use Carbon\Carbon;
    ?>

    <div class="screen">
        <div class="spacing main">

            <div class="content-container box-page">

                <div class="content padding heading box-gold">
                    <div class="header logo-sub">
                        <div class="logo-and-title">
                            <div class="logo">
                                <img class="svg" src="/icons/graph.svg" width="50em" height="auto" />
                            </div>
                            <div class="text title">
                                <h4>Performance Analytics</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="content padding quiz-background box-page">
                    <div class="performance-container">
                        <div class="performance-details">
                            <b>ALL COURSES</b>
                            <?php foreach ($courses as $c): ?>
                                <b><?= htmlspecialchars($c->course_name) ?></b>
                            <?php endforeach; ?>
                            <?php include __DIR__ . '/../partials/course-performance-hero.php'; ?>
                        </div>
                        <div class="performance-graphics">
                            <div class="performance-points">
                                <img class="svg" src="/icons/points.svg" width="100em" height="auto" />
                                <p class="points-total-header">Total Points Gained</p>
                                <p class="points-total-points"><?= $overall->total_points ?? 0 ?></p>
                                <hr class="divider-hr">
                                <p class="points-total-header">Current Points Stored</p>
                                <p class="points-total-points"><?= $overall->total_points ?? 0 ?></p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="content-container">

                <div class="content padding heading box-gold">
                    <div class="header logo-sub">
                        <div class="logo-and-title">
                            <div class="logo">
                                <img class="svg" src="/icons/achievements.svg" width="50em" height="auto" />
                            </div>
                            <div class="text title">
                                <h4>Achievements</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="content padding">
                    <?php foreach ($achievements as $ach): ?>
                        <div class="achievement <?= $ach->owned ? 'owned' : 'locked' ?>">
                            <div class="achievement-image">
                                <img src="/achievements/<?= e($ach->achievement_image) ?>.svg" width="50em" height="auto" alt="">
                            </div>
                            <div class="achievement-details">
                                <h6><?= e($ach->achievement_name) ?></h6>
                                <small><?= e($ach->achievement_description) ?></small>
                                <?php if ($ach->owned):
                                    $unlockDate = Carbon::parse($ach->unlocked_at)
                                    ->format('M j, Y g:i A'); ?>
                                    <small>Obtained On: <?= $unlockDate ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            </div>

            <div class="content-container">

                <div class="content padding heading box-gold">
                    <div class="header logo-sub">
                        <div class="logo-and-title">
                            <div class="logo">
                                <img class="svg" src="/icons/achievements.svg" width="50em" height="auto" />
                            </div>
                            <div class="text title">
                                <h4>Badges</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="content padding">
                    <?php foreach ($badges as $b): ?>
                        <div class="achievement <?= $b->owned ? 'owned' : 'locked' ?>">
                            <div class="achievement-image">
                                <img src="/achievements/<?= e($b->badge_image) ?>.svg" width="50em" height="auto" alt="">
                            </div>
                            <div class="achievement-details">
                                <h6><?= e($b->badge_name) ?></h6>
                                <small><?= e($b->badge_description) ?></small>
                                <small>Points required: <?= $b->points_required * 100 ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            </div>


        </div>

        <div class="spacing side">
            <?php include __DIR__ . '/../partials/right-side-notifications.php';  ?>
        </div>
    </div>