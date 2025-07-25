<?php
$title = $activity->activity_name;
include __DIR__ . '/../partials/head.php';
?>
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

    .table-left-padding {
        width: 2em;
    }

    .table-right-padding {
        padding: 1em 1.5em;
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
    include __DIR__ . '/../partials/quiz-type-check.php';
    include __DIR__ . '/../partials/time-lock-check.php';
    include __DIR__ . '/../partials/time-limit-conversion.php';
    include __DIR__ . '/../partials/score-calc.php';
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
                        <h6><a href="/home-tutor/course/<?= $course->course_id ?>/module/<?= $module->module_id ?>/"><?= $module->module_name ?></a></h6>
                        <div class="line"></div>
                    </div>
                    <div class="divider">
                        <h6> > </h6>
                    </div>
                    <div class="text title">
                        <h6>(QUIZ) <?= $activity->activity_name ?></a></h6>
                        <div class="line active"></div>
                    </div>
                </div>
            </div>

            <div class="content-container box-gold">

                <div class="content padding">
                    <div class="header logo-sub">
                        <div class="logo-and-title">
                            <div class="logo">
                                <img class="svg" src="/icons/<?= $class ?>.svg" width="50em" height="auto" />
                            </div>
                            <div class="text title">
                                <h4><?= $activity->activity_name ?></h4>
                                <h6><?= $quiz_type ?></h6>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="content-container">
                <div class="content padding <?= $class ?>">
                    <div class="module-section quiz-background">
                        <div class="module-section quiz-header">
                            <div class="quiz-description">
                                <div class="quiz-categories-top">
                                    <div class="quiz-categories">
                                        <div class="quiz-categories-desc">
                                            <p class="description"><b>QUESTIONS: </b><?= $activity->quiz->number_of_questions ?></p>

                                            <p class="description"><b>TOTAL ATTEMPTS: </b>
                                            <?php if ($activity->quiz->quiz_type_id == 2): ?>
                                                Unlimited
                                            <?php else: ?>
                                                <?= $activity->quiz->number_of_attempts ?>
                                            <?php endif; ?>
                                            </p>
                                            <p class="description"><b>TIME LIMIT: </b><?= $fTimeLimit ?> min/s</p>
                                        </div>
                                    </div>
                                    <div class="quiz-categories">
                                        <div class="quiz-categories-desc">
                                            <p class="description"><b>OPENS: </b><?= $formattedUnlockDate ?></p>
                                            <p class="description"><b>DUE: </b><?= $formattedDeadline ?></p>
                                        </div>
                                    </div>
                                    <br>
                                </div>
                                <hr class="divider-hr">
                                <p class="description">Instructions: <?= $activity->activity_description ?></p>
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
                                <div class="points-container">
                                    <br>
                                    <p class="description" style="text-align: center;">Total Points Garnered</p>
                                    <p class="description points" style="text-align: center;"><b><?= ($assessment?->earned_points * 100) ?? 0 ?></b></p>
                                </div>
                            </div>
                        </div>
                        <div class="module-section quiz-button-section">
                            <?= '<a class="activity-link" href="/home-tutor/course/' . $course->course_id . '/module/' . $module->module_id . '/quiz/' . $activity->activity_id . '/s"> ' ?>
                            <div class="quiz-button activity-button <?= $buttonClass ?>">TAKE QUIZ</div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-container box-page">
                <div class="content padding <?= $class ?>">
                    <?php if (!$assessDisplay->isEmpty()): ?>
                        <div class="module-section quiz-button-section">
                            <?= '<a class="activity-link" href="/home-tutor/course/' . $course->course_id . '/module/' . $module->module_id . '/quiz/' . $activity->activity_id . '/summary"> ' ?>
                            <div class="quiz-button activity-button <?= $buttonClass ?>">VIEW LATEST RESULT</div>
                            </a>
                        </div>
                    <?php endif ?>
                    <div class="module-section">
                        <p class="description" style="color: #492C2C;"><b>ANALYSIS</b></p>
                        <p class="description" style="color: #492C2C;"><b>ATTEMPTS TAKEN: </b><?= $attempts ?></p>
                        <table class="attempts-table">
                            <thead>
                                <tr class="attempts-table-header" style="background-color: rgba(180, 180, 180, 0.92);">
                                    <th>ATTEMPT</th>
                                    <th>SCORE</th>
                                    <th>PERCENTAGE</th>
                                    <th>DATE TAKEN</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php include __DIR__ . '/../partials/module-quiz-assessments.php'; ?>
                            </tbody>
                        </table>
                    </div>
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
                                    <img class="svg" src="/icons/<?= $class ?>.svg" width="50em" height="auto" />
                                </div>
                                <div class="heading-context">
                                    <h5><b><?= $activity->activity_name ?></b></h5>
                                    <p><?= $quiz_type ?></p>
                                </div>
                            </div>
                            <div class="return-prev-cont">
                                <?= '<a class="activity-link" href="/home-tutor/module/' . $activity->module_id . '/">
                                <div class="return-prev">BACK to Module Page</div>
                                        </a> ' ?>
                                </div>
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
                        <div class="module-section quiz-background <?= $class ?>">
                            <div class="module-section quiz-header">
                                <div class="quiz-description">
                                    <div class="quiz-categories-top">
                                        <div class="quiz-categories">
                                            <div class="quiz-categories-desc">
                                                <p class="description"><b>QUESTIONS: </b><?= $activity->quiz->number_of_questions ?></p>
                                                <p class="description"><b>TOTAL ATTEMPTS: </b><?= $activity->quiz->number_of_attempts ?></p>
                                                <p class="description"><b>TIME LIMIT: </b><?= $fTimeLimit ?> min/s</p>
                                            </div>
                                        </div>
                                        <div class="quiz-categories">
                                            <div class="quiz-categories-desc">
                                                <p class="description"><b>OPENS: </b><?= $formattedUnlockDate ?></p>
                                                <p class="description"><b>DUE: </b><?= $formattedDeadline ?></p>
                                            </div>
                                        </div>
                                        <br>
                                    </div>
                                    <hr>
                                    <p class="description">Instructions: <?= $activity->activity_description ?></p>
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
                                    <div class="points-container">
                                        <br>
                                        <p class="description" style="text-align: center;">Total Points Garnered</p>
                                        <p class="description points" style="text-align: center;"><b><?= ($assessment->earned_points * 10) ?? 0 ?></b></p>
                                    </div>
                                </div>
                            </div>
                            <div class="module-section quiz-button-section">
                                <?= '<a class="activity-link" href="/home-tutor/quiz/' . $activity->activity_id . '/s"> ' ?>
                                <div class="quiz-button activity-button <?= $buttonClass ?>">TAKE QUIZ</div>
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="table-left-padding"></td>
                    <td class="table-right-padding">
                        <div class="module-section">
                            <p class="description" style="color: #492C2C;"><b>ANALYSIS</b></p>
                            <p class="description" style="color: #492C2C;"><b>ATTEMPTS TAKEN: </b><?= $attempts ?></p>
                            <table class="attempts-table">
                                <thead>
                                    <tr class="attempts-table-header" style="background-color: rgba(176, 176, 176, 0.4);">
                                        <th>ATTEMPT</th>
                                        <th>SCORE</th>
                                        <th>PERCENTAGE</th>
                                        <th>DATE TAKEN</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php include __DIR__ . '/../partials/module-quiz-assessments.php'; ?>
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>

        </div>
        <?php include __DIR__ . '/../partials/right-side-notifications.php'; ?>
    </div>
</body>
</html> */ ?>