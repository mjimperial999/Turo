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
    include __DIR__ . '/../partials/nav-teach.php';
    include __DIR__ . '/../partials/time-lock-check.php';

    $seconds = $activity->quiz->time_limit;
    $minutes = floor($seconds / 60);
    $fTimeLimit = sprintf("%2d", $minutes);
    ?>

    <div class="screen">
        <div class="spacing main">
            <div class="content-container box-page">
                <div class="mini-navigation">
                    <div class="text title">
                        <h6><a href="/teachers-panel">Courses</a></h6>
                        <div class="line"></div>
                    </div>
                    <div class="divider">
                        <h6> > </h6>
                    </div>
                    <div class="text title">
                        <h6><a href="/teachers-panel/course/<?= $course->course_id ?>"><?= $course->course_name ?></a></h6>
                        <div class="line"></div>
                    </div>
                    <div class="divider">
                        <h6> > </h6>
                    </div>
                    <div class="text title">
                        <h6><a href="/teachers-panel/course/<?= $course->course_id ?>/module/<?= $module->module_id ?>"><?= $module->module_name ?></a></h6>
                        <div class="line"></div>
                    </div>
                    <div class="divider">
                        <h6> > </h6>
                    </div>
                    <div class="text title">
                        <h6>(SHORT QUIZ) <?= $activity->activity_name ?></a></h6>
                        <div class="line active"></div>
                    </div>
                </div>
            </div>

            <div class="content-container box-gold">

                <div class="content padding">
                    <div class="header logo-sub">
                        <div class="logo-and-title">
                            <div class="logo">
                                <img class="svg" src="/icons/short-quiz.svg" width="50em" height="auto" />
                            </div>
                            <div class="text title">
                                <h4><?= $activity->activity_name ?></h4>
                                <h6>Short Quiz</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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

            </div>

            <div class="content-container short-quiz">
                <div class="content flex-column">

                    <div class="form-box" >
                        <div class="form-label" style="margin: 0; width: 50%;">
                            <p class="description"><b>QUESTIONS: </b></p>
                        </div>
                        <div class="form-input" style="margin: 0; width: 50%;">
                            <p class="description"><?= $activity->quiz->number_of_questions ?></p>
                        </div>
                    </div>

                    <div class="form-box">
                        <div class="form-label" style="margin: 0; width: 50%;">
                            <p class="description"><b>TOTAL ATTEMPTS: </b></p>
                        </div>
                        <div class="form-input" style="margin: 0; width: 50%;">
                            <p class="description"><?= $activity->quiz->number_of_attempts ?></p>
                        </div>
                    </div>

                    <div class="form-box">
                        <div class="form-label" style="margin: 0; width: 50%;">
                            <p class="description"><b>TIME LIMIT: </b></p>
                        </div>
                        <div class="form-input" style="margin: 0; width: 50%;">
                            <p class="description"><?= $activity->quiz->time_limit / 60 ?> minutes</p>
                        </div>
                    </div>

                    <div class="form-box">
                        <div class="form-label" style="margin: 0; width: 50%;">
                            <p class="description"><b>OPENS: </b></p>
                        </div>
                        <div class="form-input"style="margin: 0; width: 50%;">
                            <p class="description"><?= $formattedUnlockDate ?></p>
                        </div>
                    </div>

                    <div class="form-box">
                        <div class="form-label" style="margin: 0; width: 50%;">
                            <p class="description"><b>DUE: </b></p>
                        </div>
                        <div class="form-input" style="margin: 0; width: 50%;">
                            <p class="description"><?= $formattedDeadline ?></p>
                        </div>
                    </div>
                    
                    <hr class="divider-hr">

                    <p class="description"><b>INSTUCTIONS:</b> <?= $activity->activity_description ?></p>
                        
                    
                   
                </div>

            </div>

            <div class="content-container box-page">
                <div class="content padding heading box-gray">
                    <div class="header">
                        <div class="text title">
                            <h5> Students' Assessment </h5>
                        </div>
                    </div>
                </div>

                <div class="content padding">
                    <?php include __DIR__ . '/../partials/teacher-results-quiz.php'; ?>
                </div>

            </div>


            <div class="content-container box-page">
                <div class="content padding heading box-gray">
                    <div class="header">
                        <div class="text title">
                            <h5> Quiz Answers </h5>
                        </div>
                    </div>
                </div>

                <div class="content padding">
                    Hi
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
                                    <img class="svg" src="/icons/long-quiz.svg" width="50em" height="auto" />
                                </div>
                                <div class="heading-context">
                                    <h5><b><?= $longquiz->long_quiz_name ?></b></h5>
                                    <p>Long Quiz</p>
                                </div>
                            </div>
                            <div class="return-prev-container">
                                <?= '<a class="activity-link" href="/home-tutor/course/' . $course->course_id . '/"> ' ?>
                                <div class="return-prev"><- BACK to Course: <?= $course->course_name ?> Page</div>
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
                        <div class="module-section quiz-background long-quiz">
                            <div class="module-section quiz-header">
                                <div class="quiz-description">
                                    <div class="quiz-categories-top">
                                        <div class="quiz-categories">
                                            <div class="quiz-categories-desc">
                                                <p class="description"><b>QUESTIONS: </b><?= $longquiz->number_of_questions ?></p>
                                                <p class="description"><b>TOTAL ATTEMPTS: </b><?= $longquiz->number_of_attempts ?></p>
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
                                    <p class="description">Instructions: <?= $longquiz->long_quiz_instructions ?></p>
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
                                <?= '<a class="activity-link" href="/home-tutor/long-quiz/' . $course->course_id . '/' . $longquiz->long_quiz_id . '/s"> ' ?>
                                <div class="quiz-button activity-button quiz-long-activity">TAKE QUIZ</div>
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
                                    <?php include __DIR__ . '/../partials/long-quiz-assessments.php'; ?>
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

</html>

*/ ?>