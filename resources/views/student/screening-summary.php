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

    .screening-results-container-concept {
        width: 15rem;
        gap: 0.5rem;
    }


    .screening-results-container-concept-header {
        padding: 0 0 0 0.5rem;
        border-radius: 0.25rem;
        background: linear-gradient(135deg, rgb(52, 52, 52) 0%, rgb(35, 35, 35) 100%);
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
    }

    .screening-results-container-concept-score {
        border-radius: 0.25rem;
        padding: 0.25rem;
    }

    .screening-results-container-concept-score.failed {
        background: rgb(255, 88, 88);
        background: linear-gradient(135deg, rgb(255, 134, 134) 0%, rgb(255, 88, 88) 100%);
    }

    .screening-results-container-concept-score.passed {
        background: rgb(174, 255, 88);
        background: linear-gradient(135deg, rgb(179, 238, 117) 0%, rgb(133, 220, 41) 100%);
    }

    .screening-results-container-topic-material {
        gap: 0.5rem;
    }

    .screening-results-container-topic-material .screening-results-container-concept-link{
        width: 100%;
        font-size: 0.9rem;
    }

    .screening-results-container-concept-link .activity .activity-button {
        width: 14rem;
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
    include __DIR__ . '/../partials/flash-stack.php';

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
    <div class="screen">
        <div class="spacing main">
            <div class="content-container box-page">
                <div class="mini-navigation">
                    <div class="text title">
                        <h6><a href="/home-tutor">Courses</a></h6>
                        <div class="line"></div>
                    </div>
                    <div class="divider">
                        <h6> > </h6>
                    </div>
                    <div class="text title">
                        <h6><a href="/home-tutor/course/<?= $course->course_id ?>"><?= $course->course_name ?></a></h6>
                        <div class="line"></div>
                    </div>
                    <div class="divider">
                        <h6> > </h6>
                    </div>
                    <div class="text title">
                        <h6><a href="/home-tutor/course/<?= $course->course_id ?>/<?= $screening->screening_id ?>">(SCREENER) <?= $screening->screening_name ?></a></h6>
                        <div class="line"></div>
                    </div>
                    <div class="divider">
                        <h6> > </h6>
                    </div>
                    <div class="text title">
                        <h6>SUMMARY: <?= $screening->screening_name ?></a></h6>
                        <div class="line active"></div>
                    </div>
                </div>
            </div>

            <div class="content-container box-gold">

                <div class="content padding">
                    <div class="header logo-sub">
                        <div class="logo-and-title">
                            <div class="logo">
                                <img class="svg" src="/icons/screener.svg" width="50em" height="auto" />
                            </div>
                            <div class="text title">
                                <h4><?= $screening->screening_name ?></h4>
                                <h6>Screening Exam</h6>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="content-container screening-exam">
                <div class="content padding">
                    <div class="module-section quiz-background">
                        <div class="module-section quiz-header summary">
                            <div class="quiz-summary-container">
                                <div class="quiz-summary-score-details">
                                    <div class="quiz-summary-logo-container">
                                        <img class="svg" src="/icons/screener.svg" width="90em" height="auto" />
                                    </div>
                                    <div class="quiz-summary-score">
                                        <p class="description"><b>BEST SCORE: </b></p>
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
                    </div>

                </div>
            </div>

            <div class="content-container screening-results">


                <div class="content padding heading box-dark">
                    <div class="header">
                        <div class="text title">
                            <h4><b>SCREENING DIAGNOSTICS</b></h4>
                        </div>
                    </div>
                </div>

                <div class="content padding">
                    <?php
                    // ---------- decide what we have ---------- 
                    $hasAttempt      = ! is_null($result);                // any attempt row in DB?
                    $isFirstAttempt  = $hasAttempt && ($result->tier_id == 1);

                    // ---------- no attempt yet ---------- 
                    if (! $hasAttempt): ?>
                        <div class="screening-results-container">
                            <div class="content padding">
                                <div class="no-items">
                                    <img class="svg" src="/icons/nothing.svg" width="50em" height="auto" />
                                    No modules available for this course.
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($passed): ?>
                        <div class="content padding">
                            <div class="no-items">
                                <img class="svg" src="/icons/nothing.svg" width="50em" height="auto" />
                                Congratulations – you passed!
                            </div>
                        </div>


                    <?php elseif ($attempts == 1): ?>
                        <div class="screening-results-container">
                            <div class="content">
                                <div class="header logo-sub">
                                    <div class="logo-and-title" style="padding: 0rem 1rem;">
                                        <div class="logo">
                                            <img class="svg" src="/icons/screener.svg" width="50em" height="auto" />
                                        </div>
                                        <div class="text title">
                                            <h5><b>Tier 1</b></h5>
                                            <p>Attempt <?= $attempts ?> / 3</p>
                                            <p class="italic-albert">If you get a low score on a certain topic, resources will be given to you for improvement.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="content padding">
                                <div class="screening-results-container-results">
                                    <?php foreach ($conceptData as $c): ?>
                                        <?php include __DIR__ . '/../partials/screening-resource-t1-hero.php'; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                        </div>

                    <?php elseif ($attempts == 2): ?>
                        <div class="screening-results-container">

                            <div class="content">
                                <div class="header logo-sub">
                                    <div class="logo-and-title" style="padding: 0rem 1rem;">
                                        <div class="logo">
                                            <img class="svg" src="/icons/screener.svg" width="50em" height="auto" />
                                        </div>
                                        <div class="text title">
                                            <h5><b>Tier 2</b></h5>
                                            <p>Attempt <?= $attempts ?> / 3</p>
                                            <p class="italic-albert">Low scores under topics will get more specific study materials. Use it to improve your areas that you are struggling.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="content padding">
                                <div class="screening-results-container-results">
                                    <?php foreach ($conceptData as $cid => $c): ?>
                                        <?php include __DIR__ . '/../partials/screening-resource-t2-hero.php'; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                        </div>

                    <?php else: ?>
                        <div class="screening-results-container">

                            <div class="content">
                                <div class="header logo-sub">
                                    <div class="logo-and-title" style="padding: 0rem 1rem;">
                                        <div class="logo">
                                            <img class="svg" src="/icons/screener.svg" width="50em" height="auto" />
                                        </div>
                                        <div class="text title">
                                            <h5><b>Tier 3</b></h5>
                                            <p>Attempt <?= $attempts ?> / 3</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="content padding">
                                <div class="screening-results-container-results">
                                    <p> You have used all three attempts.<br>
                                        You will now enter the personalised catch-up program.</p>
                                </div>
                                <a class="btn edit" href="/home-tutor">Begin Catch-up Program</a>
                            </div>

                        </div>
                    <?php endif; ?>
                </div>
                </div>
                </div>
            <div class="spacing side">
                <?php include __DIR__ . '/../partials/right-side-notifications.php';  ?>
            </div>
        </div>





        <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>
<?php /*
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
                            // ---------- decide what we have ---------- 
                            $hasAttempt      = ! is_null($result);                // any attempt row in DB?
                            $isFirstAttempt  = $hasAttempt && ($result->tier_id == 1);

                            // ---------- no attempt yet ---------- 
                            if (! $hasAttempt): ?>
                                <div class="screening-results-container">
                                    <h5 class="description"><b>SCREENING DIAGNOSTICS</b></h5>
                                    <p class="description italic">
                                        You haven’t taken this diagnostic yet. Start the exam to see your
                                        strengths and the resources we’ll recommend for weak areas.
                                    </p>
                                </div>

                            <?php
                            // ---------- first attempt ---------- 
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
                            // ---------- second attempt and beyond ---------- 
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

*/ ?>