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
    ?>

    <div class="screen">
        <div class="spacing main">

            <div class="content-container box-page">

                <?php if (session()->has('error')): ?>
                    <div class="content padding">
                        <div class="alert alert-danger alert-message" role="alert">
                            <?= session('error') ?>
                        </div>
                    </div>
                <?php elseif (session()->has('success')): ?>
                    <div class="content padding">
                        <div class="alert alert-success alert-message" role="alert">
                            <?= session('success') ?>
                        </div>
                    </div>
                <?php endif; ?>


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

                </div>

            </div>


        </div>

        <div class="spacing side">
            <?php include __DIR__ . '/../partials/right-side-notifications.php';  ?>
        </div>
    </div>

    <?php /*
    <div class="home-tutor-screen">
        <div class="home-tutor-main">
            <table>
                <tr class="module-title">
                    <th class="table-left-padding"></th>
                    <th class="table-right-padding">
                        <div class="module-heading">
                            <div class="module-logo">
                                <img class="svg" src="/icons/graph.svg" width="50em" height="auto" />
                            </div>
                            <div class="heading-context">
                                <h5><b>PERFORMANCE ANALYTICS</b></h5>
                            </div>
                        </div>
                    </th>
                </tr>
                <tr class="module-subtitle">
                    <td class="table-left-padding"></td>
                    <td class="table-right-padding">
                        <div class="module-section quiz-background profile-color">
                            <div class="performance-container">
                                <div class="performance-details">
                                    <b>ALL COURSES</b>
                                    <?php
                                    foreach ($courses as $course) {
                                        $shortAvg = $shortAverages[$course->course_id]->short_avg ?? null;
                                        $longAvg = $longAverages[$course->course_id]->long_avg ?? null;
                                        $combinedAvg = null;

                                        if (!is_null($shortAvg) && !is_null($longAvg)) {
                                            $combinedAvg = ($shortAvg + $longAvg) / 2;
                                        } elseif (!is_null($shortAvg)) {
                                            $combinedAvg = $shortAvg;
                                        } elseif (!is_null($longAvg)) {
                                            $combinedAvg = $longAvg;
                                        }
                                        echo '<div class="performance-course-element"><table class="performance-table">';
                                        echo '<tr>
                                                <th colspan="2" class="performance-course">' . $course->course_name . '</th>
                                            </tr>
                                            <tr>
                                                <th class="performance-overall">Short Quiz Average</th>
                                                <th class="results">' . (!is_null($shortAvg) ? round($shortAvg, 2) . "%" : "No data") . '</th>
                                            </tr>';

                                        foreach ($moduleAverages as $m) {
                                            if ($m->course_id === $course->course_id) {
                                                echo '<tr>
                                                        <th class="performance-module span">> ' . $m->module_name . '</th>
                                                        <th class="results-sub">' . round($m->average_score, 2) . '%</th>
                                                    </tr>';
                                            }
                                        }

                                        echo '<tr>               
                                            <th class="performance-overall">Long Quiz Average:</th>
                                            <th class="results">' . (!is_null($longAvg) ? round($longAvg) . "%" : "No data") . '</th>
                                        </tr>';

                                        foreach ($longQuizzes as $lq) {
                                            if ($lq->course_id === $course->course_id) {
                                                echo '<tr>
                                                        <th class="performance-module span">> '.$lq->long_quiz_name.'</th>
                                                        <th class="results-sub">'.round($lq->average_score, 2).'%</th>
                                                    </tr>';
                                            }
                                        }
                                        
                                        echo '<tr>               
                                            <th class="performance-overall-both">Course Average:</th>
                                            <th class="results-main">' . (!is_null($percentage) ? round($percentage) . "%" : "No data") . '</th>
                                        </tr>
                                    </table></div>';
                                    }

                                    ?>
                                </div>
                                <div class="performance-graphics">
                                    <div class="performance-points">
                                        <img class="svg" src="/icons/points.svg" width="100em" height="auto" />
                                        <p class="points-total-header">Total Points Gained</p>
                                        <p class="points-total-points"><?= $progress->total_points ?? 0 ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="module-title">
                    <td class="table-left-padding"></td>
                    <td class="table-right-padding">
                        <div class="module-heading">
                            <div class="module-logo">
                                <img class="svg" src="/icons/achievements.svg" width="50em" height="auto" style="filter: drop-shadow(0 0.2rem 0.25rem rgba(0, 0, 0, 0.2));" />
                            </div>
                            <div class="heading-context">
                                <h5><b>ACHIEVEMENTS</b></h5>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="module-subtitle">
                    <td class="table-left-padding"></td>
                    <td class="table-right-padding">
                    </td>
            </table>
        </div>
        <?php include __DIR__ . '/../partials/right-side-notifications.php';  ?>
    </div>
</body>

</html>

*/ ?>