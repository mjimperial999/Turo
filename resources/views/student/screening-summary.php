<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
$title = $screening_name;
include __DIR__ . '/../partials/head.php'; ?>

<style>
    table,
    th,
    td {
        border: 0.04em solid #C9C9C9;
        border-collapse: collapse;
    }

    table {
        width: 100%;
    }



    @keyframes anim {
        100% {
            stroke-dashoffset: var(--num);
        }
    }
</style>

</head>

<body>
    <?php
    include __DIR__ . '/../partials/nav.php';

    /* ----- colour-ring settings ----- */
    $percentage = $result ? $result->score_percentage : null;      // was $assessment
    $circle_display = ($percentage !== null)
        ? (450 - (450 * $percentage) / 100)
        : 450;

    /* colour band */
    if ($percentage === null) {
        $color = '#999999';
        $percentage_display = '--';
    } elseif ($percentage >= 80) {
        $color = '#01EE2C';
        $percentage_display = round($percentage);
    } elseif ($percentage >= 75) {
        $color = '#caee01';
        $percentage_display = round($percentage);
    } elseif ($percentage >= 50) {
        $color = '#ee8301';
        $percentage_display = round($percentage);
    } else {
        $color = '#ee0101';
        $percentage_display = round($percentage);
    }

    ?>

    <div class="home-tutor-screen">
        <div class="home-tutor-main">
            <table>
                <tr class="module-title">
                    <th class="table-left-padding"></th>
                    <th class="table-right-padding">
                        <div class="first-th">
                            <div class="module-heading">
                                <div class="module-logo">
                                    <img class="svg" src="/icons/screener.svg" width="50em" height="auto" />
                                </div>
                                <div class="heading-context">
                                    <?= $screening_name  ?><br>
                                    Screening Exam
                                </div>
                            </div>
                            <br class="mobile-display-only">
                            <div class="return-prev-cont">
                                <?= '<a class="activity-link" href="/home-tutor/course/' . $courseId . '/' . $screeningId . '"> ' ?>
                                <div class="return-prev"><- BACK to Screening Page </div>
                                        </a>
                                </div>
                            </div>
                    </th>
                </tr>
                <tr>
                    <td class="table-left-padding"></td>
                    <td class="table-right-padding">
                        <?php if (session()->has('error')): ?>
                            <div class="alert alert-danger alert-message" role="alert">
                                <?= session('error') ?>
                            </div>
                        <?php elseif (session()->has('success')): ?>
                            <div class="alert alert-success alert-message" role="alert">
                                <?= session('success') ?>
                            </div>
                        <?php endif; ?>
                        <div class="module-section quiz-background screening-exam">
                            <div class="module-section quiz-header summary">
                                <div class="quiz-summary-container">
                                    <div class="quiz-summary-score-details">
                                        <div class="quiz-summary-logo-container">
                                            <img class="svg" src="/icons/screener.svg" width="90em" height="auto" />
                                        </div>
                                        <div class="quiz-summary-score">
                                            <p class="description"><b>SCORE: </b></p>
                                            <p class="description summary-score"><b>
                                                    <?= $result->earned_points . ' / ' . $screening->number_of_questions ?>
                                                </b></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="quiz-graphics">
                                    <div class="percentage-container">
                                        <div class="percent" style="--clr:<?= $color ?>; --num:<?= $circle_display ?>">
                                            <svg>
                                                <circle cx="70" cy="70" r="70"></circle>
                                                <circle cx="70" cy="70" r="70"></circle>
                                            </svg>
                                        </div>
                                        <div class="percent-number">
                                            <h1><?= $percentage_display ?><span>%</span></h1>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr style="width: 100%;">
                            <?php
                            /* ---------- decide what we have ---------- */
                            $hasAttempt      = ! is_null($result);                // any attempt row in DB?
                            $isFirstAttempt  = $hasAttempt && ($result->tier_id == 1);

                            /* ---------- no attempt yet ---------- */
                            if (! $hasAttempt): ?>
                                <div class="screening-results-container">
                                    <h5 class="description"><b>SCREENING DIAGNOSTICS</b></h5>
                                    <p class="description italic">
                                        You haven’t taken this diagnostic yet. Start the exam to see your
                                        strengths and the resources we’ll recommend for weak areas.
                                    </p>
                                </div>

                            <?php
                            /* ---------- first attempt ---------- */
                            elseif ($isFirstAttempt): ?>
                                <div class="screening-results-container">
                                    <h5 class="description"><b>SCREENING DIAGNOSTICS: Tier 1</b></h5>
                                    <p class="description italic">
                                        If you get a low score on a certain topic, resources will be given to you for improvement.
                                    </p>
                                    <div class="screening-results-container-results">
                                        <?php foreach ($conceptData as $c): ?>
                                            <?php include __DIR__ . '/../partials/screening-resource-t1-hero.php'; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                            <?php
                            /* ---------- second attempt and beyond ---------- */
                            else: ?>
                                <div class="screening-results-container">
                                    <h5 class="description"><b>SCREENING DIAGNOSTICS: Tier 2</b></h5>
                                    <p class="description italic">
                                        Low scores under topics will get more specific study materials.
                                    </p>
                                    <div class="screening-results-container-results">
                                        <?php foreach ($conceptData as $cid => $c): ?>
                                            <?php include __DIR__ . '/../partials/screening-resource-t2-hero.php'; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    </div>
                                </div>
                        </div>
                    </td>
                </tr>
            </table>

        </div>
        <?php include __DIR__ . '/../partials/right-side-notifications.php';  ?>
    </div>
    <?php include __DIR__ . '/../partials/footer.php'; ?>