<?php
// Force browser not to cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>

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

    .quiz-header {
        padding: 1rem 0;
        align-items: center;
    }

    .quiz-summary-container {
        display: flex;
        flex-direction: column;
    }

    .quiz-points-container {
        display: flex;
        flex-direction: column;
    }

    .quiz-summary-score-details {
        width: 100%;
        display: flex;
        flex-direction: row;
    }

    .quiz-summary-logo-container {
        padding-right: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .quiz-summary-logo-container img {
        transform: rotate(6deg);
        filter: drop-shadow(0 0rem 0.1rem rgba(0, 0, 0, 0.2));
    }

    .quiz-summary-score {
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .quiz-summary-score .description {
        line-height: 1.2;
        font-size: 1.4rem;
    }

    .description.summary-score {
        font-size: 2.7rem;
    }

    .description.italic {
        font-family: Albert-Sans-IT, sans-serif;
    }

    .quiz-summary-attempts {
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
                        <h6><a href="/home-tutor/course/<?= $course->course_id ?>/module/<?= $module->module_id ?>/quiz/<?= $activity->activity_id ?>"><?= $activity->activity_name ?></a></h6>
                        <div class="line"></div>
                    </div>
                    <div class="divider">
                        <h6> > </h6>
                    </div>
                    <div class="text title">
                        <h6>SUMMARY: <?= $activity->activity_name ?></a></h6>
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
                            <div class="quiz-summary-container">
                                <div class="quiz-summary-score-details">
                                    <div class="quiz-summary-logo-container">
                                        <img class="svg" src="/icons/<?= $class ?>.svg" width="90em" height="auto" />
                                    </div>
                                    <div class="quiz-summary-score">
                                        <p class="description"><b>SCORE: </b></p>
                                        <p class="description summary-score"><b><?= $assessment->earned_points . ' / ' . $activity->quiz->number_of_questions ?></b></p>
                                    </div>
                                </div>
                            </div>
                            <div class="quiz-points-container">
                                <div class="quiz-points-details">
                                    <p class="description">POINTS GARNERED: <b><?= ($assessment->earned_points * 100) ?></b></p>
                                    <p class="description italic">You got <?= ($assessment->earned_points * 100) ?> points for getting <?= $assessment->earned_points ?> correct answers.</p>
                                    <hr>
                                    <p class="description"><b>SCORE CONDITIONS: </b></p>
                                    <p class="description"><b>SCORE X 100 = POINTS </b></p>
                                    <p class="description"><b><?= ($assessment->earned_points) ?></b> X 100 = <b><?= ($assessment->earned_points * 100) ?></b></p>
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

            <div class="content-container box-page">
                <div class="content padding <?= $class ?>">
                    <div class="module-section quiz-summary-attempts">
                        <p class="description"><b>ATTEMPT HISTORY</b></p>
                        <table class="attempts-table">
                            <?php foreach ($assessDisplay as $index => $a): ?>
                                <tr>
                                    <td><b><?= 'Attempt ' . ($index + 1) ?></b></td>
                                    <td><?= $a->earned_points . ' / ' . $activity->quiz->number_of_questions ?></td>
                                    <td><?= $a->score_percentage ?>%</td>
                                    <td><?= date('F j, Y h:i A', strtotime($a->date_taken)) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                </div>
            </div>

            <div class="content-container box-page">

                <?php if ($answeredQuestions->isNotEmpty()): ?>
                    <div class="content padding <?= $class ?>">
                        <p class="description"><b>YOUR ANSWERS</b></p>

                        <?php foreach ($answeredQuestions as $index => $q): ?>
                            <?php $num = $index + 1; ?>
                            <div class="question-card">
                                <h6><b>Q<?= $num ?>. <?= htmlspecialchars($q->question_text) ?></b></h6>

                                <?php /* ── optional image ── */ ?>
                                <?php if ($q->questionimage && $q->questionimage->image): ?>
                                    <?php
                                    $blob = $q->questionimage->image;
                                    $mime = getMimeTypeFromBlob($blob);
                                    $img  = "data:$mime;base64," . base64_encode($blob);
                                    ?>
                                    <img src="<?= $img ?>" style="max-width:10em;margin:.4rem 0">
                                <?php endif; ?>

                                <?php foreach ($q->options as $opt):
                                    $chosen = $q->selected_option_id === $opt->option_id;
                                    $isCorrect = $opt->is_correct == 1;

                                    /* colour rules */
                                    $bg = '#fff';
                                    $txt = '#333';
                                    if ($chosen && $isCorrect) {
                                        $bg = '#d4f7d4';
                                        $txt = '#205420';
                                    }     // green
                                    elseif ($chosen && !$isCorrect) {
                                        $bg = '#ffe4e4';
                                        $txt = '#9e1a1a';
                                    }   // red
                                    elseif ($isCorrect) {
                                        $bg = '#e8e8e8';
                                        $txt = '#273341';
                                    }     // grey
                                ?>
                                    <div style="
                        padding:.35rem .6rem;
                        border:1px solid #ccc;
                        border-radius:.3rem;
                        margin:.2rem 0;
                        background:<?= $bg ?>;
                        color:<?= $txt ?>;
                    ">
                                        <?= htmlspecialchars($opt->option_text) ?>
                                        <?php if ($chosen): ?>
                                            <span style="font-size:.75rem;font-weight:700;margin-left:.4rem">
                                                <?= $isCorrect ? '&#10004;' : '&#10008;' ?>
                                            </span>
                                        <?php elseif ($isCorrect): ?>
                                            <span style="font-size:.75rem;font-weight:700;margin-left:.4rem">&#10004;</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <hr class="divider-hr">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>


        <div class="spacing side">
            <?php include __DIR__ . '/../partials/right-side-notifications.php';  ?>
        </div>

    </div>
    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>
<?php  /*

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
                            <div class="return-prev-container">
                                <?= '<a class="activity-link" href="/home-tutor/quiz/' . $activity->activity_id . '/"> ' ?>
                                <div class="return-prev"><- BACK to <?= $activity->activity_name ?> page</div>
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
                        <div class="module-section quiz-background <?= $class ?>">
                            <div class="module-section quiz-header">
                                <div class="quiz-summary-container">
                                    <div class="quiz-summary-score-details">
                                        <div class="quiz-summary-logo-container">
                                            <img class="svg" src="/icons/<?= $class ?>.svg" width="90em" height="auto" />
                                        </div>
                                        <div class="quiz-summary-score">
                                            <p class="description"><b>SCORE: </b></p>
                                            <p class="description summary-score"><b><?= $assessment->earned_points . ' / ' . $activity->quiz->number_of_questions ?></b></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="quiz-points-container">
                                    <div class="quiz-points-details">
                                        <p class="description">POINTS GARNERED: <b><?= ($assessment->earned_points * 10) ?></b></p>
                                        <p class="description italic">You got <?= ($assessment->earned_points * 10) ?> points for getting <?= $assessment->earned_points ?> correct answers.</p>
                                        <hr>
                                        <p class="description"><b>SCORE CONDITIONS: </b></p>
                                        <p class="description"><b>SCORE X 10 = POINTS </b></p>
                                        <p class="description"><b><?= ($assessment->earned_points) ?></b> X 10 = <b><?= ($assessment->earned_points * 10) ?></b></p>
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
                            <div class="module-section quiz-summary-attempts">
                                <p class="description"><b>ATTEMPT HISTORY</b></p>
                                <table class="attempts-table">
                                    <?php foreach ($assessDisplay as $index => $a): ?>
                                        <tr>
                                            <td><b><?= 'Attempt ' . ($index + 1) ?></b></td>
                                            <td><?= $a->earned_points . ' / ' . $activity->quiz->number_of_questions ?></td>
                                            <td><?= $a->score_percentage ?>%</td>
                                            <td><?= date('F j, Y h:i A', strtotime($a->date_taken)) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </table>
                            </div>
                            <hr style="width: 100%;">
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