<?php
$title = $screening->screening_name;
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
    include __DIR__ . '/../partials/flash-stack.php';

    $studentId = session('user_id');

    $latest = \App\Models\ScreeningResult::where([
        ['student_id',   $studentId],
        ['screening_id', $screening->screening_id],
    ])->orderByDesc('attempt_number')->first();

    $attempts = $latest ? $latest->attempt_number : 0;
    $passed   = $latest && $latest->score_percentage >= 70;

    $canRetake = !$passed && $attempts < 3;

    $percentage = $latestResult ? $latestResult->score_percentage : null;
    $circle_display = $percentage !== null ? (450 - (450 * $percentage) / 100) : 450;

    // Color based on percentage
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

    $seconds = $screening->time_limit;
    $minutes = floor($seconds / 60);
    $fTimeLimit = sprintf("%2d", $minutes);
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
                        <h6>(SCREENER) <?= $screening->screening_name ?></a></h6>
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
                        <div class="module-section quiz-header">
                            <div class="quiz-description">
                                <div class="quiz-categories-top">
                                    <div class="quiz-categories">
                                        <div class="quiz-categories-desc">
                                            <p class="description"><b>QUESTIONS: </b><?= $screening->number_of_questions ?></p>
                                            <p class="description"><b>TIME LIMIT: </b><?= $fTimeLimit ?> min/s</p>
                                        </div>
                                    </div>
                                    <br>
                                </div>
                                <hr class="divider-hr">
                                <p class="description">Instructions:<br> <?= $screening->screening_instructions ?></p><br>
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
                        <div class="module-section quiz-button-section">
                            <?php if ($canRetake): ?>
                            <form method="POST"
                                action="/home-tutor/course/<?= $course->course_id ?>/<?= $screening->screening_id ?>/start">
                                <?= csrf_field() ?>
                                <button class="quiz-button activity-button screening-button">Start&nbsp;Exam</button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-container screening-results">
                <div class="content padding">
                    <div class="summary-goto">
                        <?php if (!$latestResult): ?>
                            <div class="summary-icon-none"><img class="svg" src="/icons/nothing.svg" width="50em" height="auto" />
                                No attempts yet.</div>
                        <?php else: ?>
                            <h5 class="description">View Your Results Here:</h5>
                            <div class="activity">
                                <a class="activity-link" href="/home-tutor/course/<?= $course->course_id ?>/<?= $screening->screening_id ?>/summary">
                                    <div class="activity-button screening-resources unlocked">
                                        <div class="activity-logo">
                                            <img class="svg" src="/icons/bulb.svg" width="30em" height="auto" />
                                        </div>
                                        <div class="activity-name">SUMMARY</div>
                                    </div>
                                </a>
                            </div>
                        <?php endif; ?>
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
                                    <img class="svg" src="/icons/screener.svg" width="50em" height="auto" />
                                </div>
                                <div class="heading-context">
                                    <h5><b><?= $screening->screening_name ?></b></h5>
                                    <p>Screening Exam</p>
                                </div>
                            </div>
                            <div class="return-prev-container">
                                <?= '<a class="activity-link" href="/home-tutor/course/' . $screening->course_id . '/"> ' ?>
                                <div class="return-prev"><- Back to Course</div>
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
                            <div class="module-section quiz-header">
                                <div class="quiz-description">
                                    <div class="quiz-categories-top">
                                        <div class="quiz-categories">
                                            <div class="quiz-categories-desc">
                                                <p class="description"><b>QUESTIONS: </b><?= $screening->number_of_questions ?></p>
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
                                    <p class="description">Instructions: <?= $screening->screening_instructions ?></p>
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
                            <div class="module-section quiz-button-section">
                                <form method="POST"
                                    action="/home-tutor/course/<?= $courseId ?>/<?= $screening->screening_id ?>/start">
                                    <?= csrf_field() ?>
                                    <button class="quiz-button activity-button screening-button">Start&nbsp;Exam</button>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="table-left-padding"></td>
                    <td class="table-right-padding">
                        <div class="summary-goto">
                            <?php if (!$latestResult): ?>
                                <div class="summary-icon-none"><img class="svg" src="/icons/nothing.svg" width="50em" height="auto" />
                                No attempts yet.</div>
                            <?php else: ?>
                                <h5 class="description">View Your Results Here:</h5>
                                <div class="activity">
                                    <a class="activity-link" href="/home-tutor/course/<?= $courseId ?>/<?= $screening->screening_id ?>/summary">
                                        <div class="activity-button screening-resources unlocked">
                                            <div class="activity-logo">
                                                <img class="svg" src="/icons/bulb.svg" width="30em" height="auto" />
                                            </div>
                                            <div class="activity-name">SUMMARY</div>
                                        </div>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            </table>

        </div>
        <?php include __DIR__ . '/../partials/right-side-notifications.php'; ?>
    </div>
    <?php include __DIR__ . '/../partials/footer.php'; ?>


    */ ?>